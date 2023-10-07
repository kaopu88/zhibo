<?php

namespace app\push\controller;

use bxkj_common\RedisClient;
use bxkj_common\SectionManager;
use bxkj_module\service\UserRedis;
use think\Db;

class User extends Api
{
    //每日以用户角度检查数据
    public function check()
    {
        $worker = new \app\push\section\UserCheck();
        $manager = new SectionManager([
            'name' => 'user_check:' . date('Y-m-d'),
            'length' => 100,
            'exclusivity' => false,
            'thread' => 3,
            'debug' => false
        ]);
        $manager->setSectionExecuter($worker)->start();
    }

    //检查用户最后支付时间
    public function loss_check()
    {
        $this->persistent();
        $agentId = input('agent_id');
        $promoterUid = input('promoter_uid');
        $userIds = input('user_ids');
        $aid = input('aid');
        if (empty($agentId) && empty($promoterUid) && empty($userIds)) {
            return json_error('缺少参数');
        }
        $where = [['delete_time', 'null']];
        $db = Db::name('user');
        if (!empty($userIds)) {
            $tmpIds = explode(',', $userIds);
            $db->whereIn('user_id', $tmpIds);
        } else if (!empty($promoterUid)) {
            $where[] = ['promoter_uid', 'eq', $promoterUid];
        } else if (!empty($agentId)) {
            $where[] = ['agent_id', 'eq', $agentId];
        }
        $users = $db->where($where)->order('user_id asc')->select();
        $worker = new \app\push\section\UserCheck();
        foreach ($users as $user) {
            $worker->lossCheck($user, $aid);
        }
        return json_success('检测成功');
    }

    public function update_agent_by_promoter()
    {
        $this->persistent();
        $agent_id = input('agent_id');
        $userId = input('user_id');//这里的user_id实际上是指推广员的user_id
        if (!isset($agent_id) || empty($userId)) return json_error('参数不全');
        $agent_id = $agent_id ? $agent_id : 0;
        $update = ['agent_id' => $agent_id];
        $where = [
            ['promoter_uid', '=', $userId],
            ['agent_id', '<>', $agent_id]
        ];
        $users = Db::name('user')->where($where)->field('user_id,agent_id')->select();
        $total = 0;
        foreach ($users as $user) {
            $num = Db::name('user')->where('user_id', $user['user_id'])->update($update);
            if (!$num) {
                \bxkj_module\service\User::updateRedis($user['user_id'], $update);
                $total++;
            }
        }
        if ($total > 0) {
            $clientNum = Db::name('user')->where(['promoter_uid' => $userId, 'delete_time' => null])->count();
            Db::name('promoter')->where(['user_id' => $userId, 'delete_time' => null])->update([
                'client_num' => (int)$clientNum
            ]);
        }
        return json_success($total, 'ok');
    }

    public function data_check()
    {
        exit();
        $offset = 0;
        $redis = RedisClient::getInstance();
        $rebuild = 0;

        $dfak = "data_check:cache:fans";
        $dfok = "data_check:cache:follow";
        $redis->del($dfak,$dfok);

        while (true) {
            $relations = Db::name('follow')->limit($offset, 1000)->select();
            if (empty($relations)) break;
            $offset += count($relations);
            foreach ($relations as $relation) {
                $fansKey = "fans:{$relation['follow_id']}";

                if (!$redis->sIsMember($dfak, $fansKey)) {
                    $redis->del($fansKey);
                    $redis->sAdd($dfak, $fansKey);
                }


                $redis->zAdd($fansKey, $relation['create_time'], $relation['user_id']);
                $followKey = "follow:{$relation['user_id']}";

                if (!$redis->sIsMember($dfok, $followKey)) {
                    $redis->del($followKey);
                    $redis->sAdd($dfok, $followKey);
                }

                $redis->zAdd($followKey, $relation['create_time'], $relation['follow_id']);
                $rebuild++;
            }
            sleep(1);
        }

        $offset = 0;
        $updateTotal = 0;
        while (true) {
            $users = Db::name('user')->field('user_id,fans_num,follow_num')->limit($offset, 1000)->select();
            if (empty($users)) break;
            $offset += count($users);
            foreach ($users as $user) {
                $fansKey = "fans:{$user['user_id']}";
                $followKey = "follow:{$user['user_id']}";
                $redis->zRem($fansKey, '0');
                $redis->zRem($followKey, '0');
                $fans_num = $redis->zCount($fansKey, '-inf', '+inf');
                $follow_num = $redis->zCount($followKey, '-inf', '+inf');
                $update = [];
                if ($fans_num != $user['fans_num']) $update['fans_num'] = $fans_num;
                if ($follow_num != $user['follow_num']) $update['follow_num'] = $follow_num;
                if (!empty($update)) {
                    $num = Db::name('user')->where(['user_id' => $user['user_id']])->update($update);
                    if ($num) {
                        UserRedis::updateData($user['user_id'], $update);
                        $updateTotal++;
                    }
                }
            }
        }
        echo "rebuild:{$rebuild} updateTotal:{$updateTotal}";
    }


}

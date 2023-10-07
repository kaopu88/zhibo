<?php

namespace app\push\section;

use bxkj_common\RedisClient;
use bxkj_common\SectionMarkExecuter;
use bxkj_module\service\User;
use bxkj_module\service\UserCreditLog;
use bxkj_module\service\Work;
use think\Db;

class UserCheck extends SectionMarkExecuter
{
    protected $lockName = 'user_check';
    protected $userCreditLog;

    public function __construct()
    {
        parent::__construct();
        $this->userCreditLog = new UserCreditLog();
    }

    public function complete($data)
    {
        if ($data['status'] == 0) {
            $this->pointer(0);
            $this->clearHisKeys();
        }
    }

    public function clearHisKeys()
    {
        $redis = RedisClient::getInstance();
        $pattern = "secexe:{$this->lockName}:*";
        $keys = $redis->keys($pattern);
        if ($keys) {
            foreach ($keys as $key) {
                $arr = explode(':', $key);
                if ($arr[0] == 'secexe' && $arr[1] == $this->lockName) {
                    $time = strtotime($arr[2]);
                    if ($time + 604800 < time()) {
                        $redis->del($key);
                    }
                }
            }
        }
    }

    public function handler($length = 10)
    {
        $total = 0;
        $this->wait();
        $pointer = $this->pointer();
        $where = [['user_id', '>', (int)$pointer], ['delete_time', 'null']];
        $users = Db::name('user')->where($where)->order('user_id asc')->limit($length)->select();
        if (empty($users)) {
            $this->unlock();
            return $this->success(true, $total);
        }
        $maxId = 0;
        foreach ($users as $user) {
            $maxId = max($maxId, $user['user_id']);
        }
        $this->pointer($maxId);
        $this->unlock();
        foreach ($users as $user) {
            $this->handlerOne($user);
            $total++;
        }
        return $this->success(false, $total);
    }

    protected function handlerOne($user)
    {
        if (!empty($user['delete_time'])) return false;
        //信用值自动回血
        $this->returnBlood($user);
        //代理商和推广员的代理商信息是否一致检查
        $this->repairAgent($user);
        //检查是否两个内没有消费了
        //$this->lossCheck($user);//不需要自动了 20190111
        //校正粉丝、收藏等数量
        $this->correctionNum($user);
    }

    protected function returnBlood($user)
    {
        $creditScore = $user['credit_score'];
        $score = 1;
        if ($creditScore > 0 && $creditScore < 60) {
            $this->userCreditLog->record('return_blood', [
                'user_id' => $user['user_id'],
                'change_type' => 'inc',
                'score' => $score
            ]);
        }
    }

    protected function repairAgent(&$user)
    {
        $promoterUid = $user['promoter_uid'];
        if (!empty($promoterUid)) {
            $promoter = $this->getPromoter($promoterUid);
            $update = [];
            if ($promoter) {
                if ($promoter['agent_id'] != $promoterUid) {
                    $update = ['agent_id' => $promoter['agent_id']];
                }
            } else {
                $update = ['agent_id' => 0, 'promoter_uid' => 0];
            }
            if (!empty($update)) {
                $num = Db::name('user')->where('user_id', $user['user_id'])->update($update);
                if ($num) {
                    User::updateRedis($user['user_id'], $update);
                    $user = array_merge($user, $update);
                }
            }
        }
    }

    protected function getPromoter($promoterUid)
    {
        $key = "user_check:promoter:{$promoterUid}";
        $promoter = cache($key);
        if (empty($promoter)) {
            $promoter = Db::name('promoter')->where(['user_id' => $promoterUid, 'delete_time' => null])->find();
            if ($promoter) cache($key, $promoter, 3600);
        }
        return $promoter;
    }

    public function lossCheck($user, $aid = '')
    {
        if (empty($user['agent_id']) && empty($user['promoter_uid'])) return true;
        $bean = Db::name('bean')->where('user_id', $user['user_id'])->find();
        $loss_after_months = config('app.loss_after_months');
        if (empty($loss_after_months)) return true;
        $time = strtotime("-{$loss_after_months} months");//两个月前
        $loss_min_bean = config('app.loss_min_bean');
        if ($bean && $bean['last_pay_time'] < $time && $bean['bean'] >= $loss_min_bean && $bean['bean'] > $bean['loss_bean']) {
            $has = Db::name('loss')->where(['user_id' => $user['user_id'], 'audit_status' => '0'])->count();
            if ($has) return true;
            $workService = new Work();
            $data = [
                'user_id' => $user['user_id'],
                'bean' => $bean['bean'] - $bean['loss_bean'],
                'audit_status' => '0',
                'audit_aid' => 0,
                'agent_id' => $user['agent_id'],
                'promoter_uid' => $user['promoter_uid'],
                'create_time' => time()
            ];
            $id = Db::name('loss')->insertGetId($data);
            if ($id) {
                if (empty($aid)) {
                    $aid = $workService->allocation('audit_clear_agent', $user['user_id'], $id);
                }
                Db::name('loss')->where('id', $id)->update(['audit_aid' => $aid]);
            }
        }
    }

    protected function correctionNum($user)
    {
        $update = [];
        $fans_num = Db::name('follow')->where(['follow_id' => $user['user_id']])->count();
        $follow_num = Db::name('follow')->where(['user_id' => $user['user_id']])->count();
        $collection_num = Db::name('collection')->where(['user_id' => $user['user_id']])->count();
        if ($user['fans_num'] != $fans_num) $update['fans_num'] = $fans_num;
        if ($user['follow_num'] != $follow_num) $update['follow_num'] = $follow_num;
        if ($user['collection_num'] != $collection_num) $update['collection_num'] = $collection_num;
        if (!empty($update)) {
            Db::name('user')->where('user_id', $user['user_id'])->update($update);
            User::updateRedis($user['user_id'], $update);
        }
    }


}
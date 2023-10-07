<?php

namespace app\agent\service;

use bxkj_module\service\ExpLevel;
use bxkj_module\service\Service;
use think\Db;

class Promoter extends \bxkj_module\service\Promoter
{
    public function getIndexTotal($get)
    {
        $this->db = Db::name('promoter');
        $this->setIndexWhere($get);
        $total = $this->db->count();
        return (int)$total;
    }

    public function getIndex($get, $offset = 0, $length = 20)
    {
        $fields = 'promoter.user_id,promoter.agent_id,promoter.total_cons,promoter.total_fans,promoter.create_time,promoter.client_num,
        user.nickname,user.username,user.phone,user.avatar,user.status,user.type,user.live_status,user.level';
        $this->db = Db::name('promoter');
        $this->setIndexWhere($get);
        $this->setIndexOrder($get);
        $index = $this->db->field($fields)->limit($offset, $length)->select();
        return $index ? $index : [];
    }

    protected function setIndexWhere($get)
    {
        $this->db->alias('promoter');
        $where = [];
        Agent::agentWhere($where, ['agent_id' => AGENT_ID], 'promoter.');
        $this->db->join('__USER__ user', 'user.user_id=promoter.user_id', 'LEFT');
        if ($get['status'] != '') {
            $where[] = ['user.status', '=', $get['status']];
        }
        $this->db->where($where);
        $this->db->setKeywords(trim($get['keyword']), 'phone user.phone', 'number promoter.user_id', 'number user.phone,user.nickname');
        return $this;
    }

    protected function setIndexOrder($get)
    {
        $this->db->order('promoter.create_time desc,promoter.user_id desc');
    }

    public function getSuggests($keyword, $length = 10)
    {
        $this->db = Db::name('promoter');
        $this->db->alias('promoter');
        Agent::agentWhere($where, ['agent_id' => AGENT_ID], 'promoter.');
        $this->db->join('__USER__ user', 'user.user_id=promoter.user_id', 'LEFT');
        $this->db->setKeywords($keyword, 'phone user.phone', 'number promoter.user_id', 'number user.phone,user.nickname,user.remark_name');
        $this->db->where($where);
        $this->db->field('promoter.user_id,user.nickname,user.username,user.phone,user.remark_name');
        $this->db->order(['promoter.create_time' => 'desc', 'promoter.id' => 'desc']);
        $result = $this->db->limit(0, $length)->select();
        $arr = [];
        foreach ($result as $item) {
            $arr[] = [
                'value' => $item['user_id'],
                'name' => user_name($item) . ($item['phone'] ? "({$item['phone']})" : '')
            ];
        }
        return $arr;
    }

    public function getInfo($userId)
    {
        if (empty($userId)) return $this->setError(config('app.agent_setting.promoter_name').'不存在');
        $where = [
            ['user_id', '=', $userId]
        ];
        $agentIds = Agent::getAgentIds(AGENT_ID);
        $promoter = Db::name('promoter')->where($where)->whereIn('agent_id', $agentIds)->find();
        return $promoter;
    }

    //创建推广员
    public function create($inputData)
    {
        $agentId = $inputData['agent_id'];
        $userId = $inputData['user_id'];
        //强制创建，如果已经是其他代理商名下的了，则先解除，解除时将客户也一并转移到新代理商名下
        $force = $inputData['force'];
        if (empty($userId)) return $this->setError('请选择用户');
        if (empty($agentId)) return $this->setError('请选择'.config('app.agent_setting.agent_name'));
        if (!in_array($force, ['0', '1'])) return $this->setError('force参数错误');
        $agent = Db::name('agent')->where(['id' => $agentId, 'delete_time' => null])->find();
        if (empty($agent)) return $this->setError(config('app.agent_setting.agent_name').'不存在');
        $max_promoter_num = $agent['max_promoter_num'];
        if ($agent['promoter_num'] >= $max_promoter_num) return $this->setError(config('app.agent_setting.promoter_name').'人数已达到最大限额');
        Service::startTrans();
        $user = Db::name('user')->where([['user_id', '=', $userId], ['delete_time', 'null']])->find();
        if (empty($user)) {
            Service::rollback();
            return $this->setError('用户不存在');
        }
        //老的推广员身份
        $previous = Db::name('promoter')->where(['user_id' => $userId])->find();
        if ($previous) {
            if ($force != '1') {
                Service::rollback();
                return $this->setError('用户已是'.config('app.agent_setting.promoter_name'));
            }
            if ($previous['agent_id'] == $agentId) {
                Service::rollback();
                return $this->setError(config('app.agent_setting.promoter_name').'身份重复');
            }
            //解除老的推广员身份
            $removeNum = $this->remove($previous, null, $inputData['admin']);
            if (!$removeNum) {
                Service::rollback();
                return $this->setError('强制创建失败');
            }
        }
        //创建新的推广员身份
        $promoterData = [
            'agent_id' => $agentId,
            'user_id' => $userId,
            'create_time' => time(),
            'client_num' => 0
        ];
        $res = Db::name('promoter')->insert($promoterData);
        if (!$res) {
            Service::rollback();
            return $this->setError('创建失败');
        }
        //记录操作日志
        $log = [
            'type' => 'add',
            'user_id' => $userId,
            'agent_id' => $promoterData['agent_id'],
            'aid' => $inputData['admin']['id'],
            'admin_type' => $inputData['admin']['type'],
            'act_time' => time()
        ];
        Db::name('promoter_log')->insertGetId($log);
        $update = ['is_promoter' => '1'];
        $num = Db::name('user')->where('user_id', $userId)->update($update);
        if ($num) {
            Db::name('agent')->where('id', $promoterData['agent_id'])->setInc('promoter_num', 1);
            \bxkj_module\service\User::updateRedis($userId, $update);
        }
        Service::commit();
        return $promoterData;
    }
}
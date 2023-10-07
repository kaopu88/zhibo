<?php

namespace app\promoter\service;

use bxkj_module\service\ExpLevel;
use bxkj_module\service\Service;
use think\Db;

class Loss extends Service
{
    public function getTotal($get)
    {
        $this->db = Db::name('loss');
        $this->setWhere($get);
        $total = $this->db->count();
        return (int)$total;
    }

    public function getList($get, $offset = 0, $length = 20)
    {
        $this->db = Db::name('loss');
        $this->setWhere($get);
        $this->setOrder($get);
        $fields = 'user.user_id,user.nickname,user.remark_name,user.phone,user.isvirtual,user.avatar,user.sign,user.level,user.gender,user.vip_expire,user.millet,user.fre_millet,user.millet_status,user.live_status,user.status';
        $this->db->field('loss.id,loss.bean,loss.audit_status,loss.audit_aid,loss.audit_time,loss.promoter_uid,loss.agent_id,loss.create_time,loss.reason');
        $list = $this->db->field($fields)->limit($offset, $length)->select();
        $this->parseList($list);
        return $list ? $list : [];
    }

    protected function setWhere($get)
    {
        $this->db->alias('loss');
        $where = [];
        $where[] = ['loss.agent_id', '=', AGENT_ID];
        if ($get['promoter_uid'] != '') {
            $where[] = ['loss.promoter_uid', '=', $get['promoter_uid']];
        }
        if ($get['audit_status'] != '') {
            $where[] = ['loss.audit_status', '=', $get['audit_status']];
        }
        $this->db->setKeywords(trim($get['keyword']), 'phone user.phone', 'number loss.user_id', 'number user.phone,user.nickname');
        $this->db->join('__USER__ user', 'user.user_id=loss.user_id', 'LEFT');
        $this->db->where($where);
        return $this;
    }

    protected function setOrder($get)
    {
        if ($get['audit_status'] == '1' || $get['audit_status'] == '2') {
            $this->db->order('loss.audit_time desc,loss.id desc');
        } else {
            $this->db->order('loss.create_time desc,loss.id desc');
        }
    }

    public function parseList(&$list)
    {
        $agentService = new Agent();
        $userService = new User();
        list($agentIds, $promoterIds, $userIds) = self::getIdsByList($list, 'agent_id|promoter_uid|user_id', true);
        $promoterList = $userService->getUsersByIds($promoterIds);
        $agentList = $agentService->getAgentsByIds($agentIds);
        $beans = $userIds ? Db::name('bean')->field('user_id,last_pay_time')->whereIn('user_id', $userIds)->select() : [];
        $admins = $this->getRelList($list, function ($aids) {
            $admins = Db::name('admin')->whereIn('id', $aids)->select();
            return $admins ? $admins : [];
        }, 'audit_aid');
        $loss_after_months = config('app.loss_after_months');
        $time = strtotime("-{$loss_after_months} months");//两个月前
        foreach ($list as $i => &$item) {
            $item = array_merge($item, ExpLevel::getLevelInfo($item['level']));
            $item['bean_info'] = self::getItemByList($item['user_id'], $beans, 'user_id');
            if (!empty($item['audit_aid'])) {
                $item['audit_admin'] = self::getItemByList($item['audit_aid'], $admins, 'id');
            }
            $item['promoter_info'] = self::getItemByList($item['promoter_uid'], $promoterList, 'user_id');
            $item['agent_info'] = self::getItemByList($item['agent_id'], $agentList, 'id');
            $item['invalid'] = '0';
            if ($item['bean_info']['last_pay_time'] > $time) {
                $item['invalid'] = '1';
                $invalidIds[] = $item['id'];
            }
        }
    }

}
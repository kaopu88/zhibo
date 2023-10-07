<?php

namespace app\promoter\service;

use bxkj_module\service\Bean;
use bxkj_module\service\ExpLevel;
use bxkj_common\CoreSdk;
use think\Db;

class User extends \bxkj_module\service\User
{

    public function getTotal($get)
    {
        $this->db = Db::name('user');
        $this->setWhere($get);
        $count = $this->db->count();
        return (int)$count;
    }

    public function getList($get, $offset = 0, $length = 20)
    {
        $this->db = Db::name('user');
        $this->setWhere($get)->setOrder($get);
        $result = $this->db->limit($offset, $length)->select();
        foreach ($result as &$item) {
            $item = array_merge($item, ExpLevel::getLevelInfo($item['level']));
            self::parseUser($item);
            $promoter_uid = Db::name('promotion_relation')->where(array('agent_id'=>$item['agent_id'],'user_id'=>$item['user_id']))->value('promoter_uid');
            $item['promoter_info'] = Db::name('user')->where(array('user_id'=>$promoter_uid))->find();
        }
        Bean::parseBeanAccForUsers($result);
        return $result;
    }

    protected function setOrder($get)
    {
        $order = array();
        if (empty($get['sort'])) {
            $order['user.create_time'] = 'DESC';
        }
        $this->db->order($order);
        return $this;
    }

    protected function setWhere($get)
    {
        $this->db->alias('user');
        $this->db->join('__PROMOTION_RELATION__ pr', 'user.user_id=pr.user_id');
        $where = [['user.delete_time', 'null']];
        if ($get['promoter_uid']) {
            $promoter_uids = explode(',', $get['promoter_uid']);
            if (count($promoter_uids) > 1)
            {
                $where[] = ['pr.promoter_uid', 'in', $get['promoter_uid']];
            }else{
                $where[] = ['pr.promoter_uid', '=', $get['promoter_uid']];
            }
        }
        Agent::agentWhere($where, ['agent_id' => AGENT_ID], 'pr.');
        if ($get['status'] != '') {
            $where[] = ['user.status', '=', $get['status']];
        }
        $this->vipStatusWhere($where, $get);
        $this->userTypeWhere($where, $get);
        if ($get['level'] != '') {
            $where[] = ['user.level', '=', $get['level']];
        }
        if ($get['district'] != '') {
            $where[] = ['user.district_id', '=', $get['district']];
        } else if ($get['city'] != '') {
            $where[] = ['user.city_id', '=', $get['city']];
        }
        $this->db->setKeywords(trim($get['keyword']), 'phone user.phone', 'number user.user_id', 'user.username,user.nickname,number user.phone');
        $this->db->where($where);
        return $this;
    }


    public function getClientTotal($get)
    {
        $this->db = Db::name('user');
        $this->setClientWhere($get);
        $count = $this->db->count();
        return (int)$count;
    }

    public function getClientList($get, $offset = 0, $length = 20)
    {
        $this->db = Db::name('user');
        $this->setClientWhere($get)->setClientOrder($get);
        $result = $this->db->limit($offset, $length)->select();
        list($agentIds, $promoterIds) = self::getIdsByList($result, 'agent_id|promoter_uid', true);
        $userService = new User();
        $promoterList = $userService->getUsersByIds($promoterIds);
        $agentService = new Agent();
        $agentList = $agentService->getAgentsByIds($agentIds);
        foreach ($result as &$item) {
            $item = array_merge($item, ExpLevel::getLevelInfo($item['level']));
            $item['promoter_info'] = self::getItemByList($item['promoter_uid'], $promoterList, 'user_id');
            $item['agent_info'] = self::getItemByList($item['agent_id'], $agentList, 'id');
            self::parseUser($item);
        }
        Bean::parseBeanAccForUsers($result);
        return $result;
    }

    protected function setClientWhere($get)
    {
        $this->db->alias('user');
        $this->db->join('__PROMOTION_RELATION__ pr', 'user.user_id=pr.user_id');
        $where = [['user.delete_time', 'null']];
        if ($get['promoter_uid']) {
            $promoter_uids = explode(',', $get['promoter_uid']);
            if (count($promoter_uids) > 1)
            {
                $where[] = ['pr.promoter_uid', 'in', $get['promoter_uid']];
            }else{
                $where[] = ['pr.promoter_uid', '=', $get['promoter_uid']];
            }
        }
        if ($get['status'] != '') {
            $where[] = ['user.status', '=', $get['status']];
        }
        $this->vipStatusWhere($where, $get);
        $this->userTypeWhere($where, $get);
        if ($get['level'] != '') {
            $where[] = ['user.level', '=', $get['level']];
        }
        $this->db->setKeywords(trim($get['keyword']), 'phone user.phone', 'number user.user_id', 'user.remark_name,user.nickname,number user.phone');
        $this->db->where($where);
        return $this;
    }

    protected function setClientOrder($get)
    {
        $order = array();
        if (empty($get['sort'])) {
            $order['user.create_time'] = 'DESC';
        }
        $this->db->order($order);
        return $this;
    }

    public function getInfo($userId)
    {
        $coreSdk = new CoreSdk();
        $user = $coreSdk->getUser($userId);
        if (!empty($user)) {
            $agentService = new Agent();
            $info = Db::name('user')->where('user_id', $userId)->field('remark_name,remark')->find();
            $levelInfo = ExpLevel::getLevelInfo($user['level']);
            $user = array_merge($user, $info, $levelInfo);
            $beanInfo = Db::name('bean')->where(['user_id' => $user['user_id']])
                ->field('bean,fre_bean,pay_status,total_bean,last_change_time bean_change_time,recharge_total,last_pay_time,loss_bean')->find();
            $user = array_merge($user, $beanInfo ? $beanInfo : []);
            $cashInfo = Db::name('millet_cash')->where(['user_id' => $user['user_id ']])->whereIn('status', ['wait', 'success'])
                ->order('create_time desc')->find();
            $user['millet_change_time'] = !empty($cashInfo) ? $cashInfo['create_time'] : null;
        }
        return $user;
    }


}
<?php

namespace app\agent\service;

use bxkj_module\service\Service;
use think\Db;

class MilletCash extends Service
{
   public function getList($get = array(), $offset = 0, $length = 10)
    {
        $this->db = Db::name('millet_cash');
        $this->setWhere($get)->setOrder($get);
        $list = $this->db->field('cash.id,cash.cash_no,cash.millet,cash.rmb,cash.status,cash.aid,cash.cash_account,cash.create_time')
            ->limit($offset, $length)->select();
        $cashAccountIds = $this->getIdsByList($list, 'cash_account');
        $cashAccounts = [];
        if (!empty($cashAccountIds)) {
            $cashAccounts = Db::name('cash_account')->where(array('id' => $cashAccountIds))->limit(count($cashAccountIds))->select();
        }
        foreach ($list as &$item) {
            $item['create_time'] = date('Y-m-d H:i:s', $item['create_time']);
            $cashAcc = $this->getItemByList($item['cash_account'], $cashAccounts, 'id');
            $cardNameArr = explode('-', $cashAcc['card_name']);

            $item['anchor'] = Db::name('anchor')->alias('anchor')
                ->field('anchor.user_id,agent.name agent_name,agent.logo agent_logo,agent.id agent_id')
                ->where(['anchor.user_id' => $item['user_id']])
                ->join('__AGENT__ agent', 'agent.id=anchor.agent_id')->find();

            $item['title'] = "提现到" . $cardNameArr[0];
            $item['accout_num'] =  $cashAcc['account'];
            $item['descr'] = "【{$item['cash_no']}】 提现{$item['millet']}".APP_MILLET_NAME;
            $item['handler_time'] = isset($item['handler_time']) ? date('Y-m-d', $item['handler_time']) : '';
        }

        return $list;
    }

    public function getTotal($get){
        $this->db = Db::name('millet_cash');
        $this->setWhere($get);
        return $this->db->count();
    }

    protected function setWhere($get)
    {
        if (!empty($get['user_id'])) {
            $where = array('cash.user_id' => $get['user_id']);
            $this->db->where($where);
        }
        if (!empty($get['status'])) {
            $where = array('cash.status' => $get['status']);
            $this->db->where($where);
        }

        if (!empty($get['start_time']) && !empty($get['end_time']))
        {
            $start_time = strtotime($get['start_time']);
            $end_time = strtotime( $get['end_time']);
            $where1[] = ['cash.create_time', 'between', [$start_time, $end_time]];
            $this->db->where($where1);
        }

        $this->db->where(['agent_id' => AGENT_ID]);
        $this->db->alias('cash');
        $this->db->join('__USER__ user', 'cash.user_id=user.user_id', 'LEFT');
        $this->db->field('user.user_id,user.nickname,user.avatar,user.remark_name,user.level,user.phone');

        return $this;
    }

    protected function setOrder($get)
    {
        if (empty($get['sort'])) {
            $this->db->order('cash.create_time desc');
        }
        return $this;
    }

    public function getSummary($get)
    {
        if (!empty($get['start_time']) && !empty($get['end_time']))
        {
            $start_time = strtotime($get['start_time']);
            $end_time = strtotime( $get['end_time']);
            $where[] = ['cash.create_time', 'between', [$start_time, $end_time]];
        }

        $wait = Db::name('millet_cash')->alias('cash')->where(['agent_id' => AGENT_ID])->where($where)->where(['cash.status' => 'wait'])->sum('cash.rmb');
        $success = Db::name('millet_cash')->alias('cash')->where(['agent_id' => AGENT_ID])->where($where)->where(['cash.status' => 'success'])->sum('cash.rmb');
        $failed = Db::name('millet_cash')->alias('cash')->where(['agent_id' => AGENT_ID])->where($where)->where(['cash.status' => 'failed'])->sum('cash.rmb');
        $result['summary'] = ['wait' => $wait, 'success' => $success, 'failed' => $failed];
        return $result;
    }

    public function update($post)
    {
        if (empty($post['id']))return ['code' => 102, 'msg' => '请选择记录'];
        if ($post['status'] === '1') {
            $status = 'success';
        } elseif ($post['status'] === '0') {
            $status = 'failed';
        } else {
            return ['code' => 101, 'msg' => '操作不当'];
        }

        $num = Db::name('millet_cash')->where(['id' => $post['id'], 'agent_id' => AGENT_ID])->update(['status' => $status, 'who_use' => AGENT_ID, 'handler_time' => time(), 'admin_remark' => $post['describe']]);
        if (!$num) return ['code' => 102, 'msg' => '操作失败'];

        if ($status == 'failed') {
            $cashRes = Db::name('millet_cash')->where(['id' => $post['id'], 'agent_id' => AGENT_ID])->find();;
            if (empty($cashRes)) ['code' => 103, 'msg' => '提现记录不存在'];
            $userService = new User();
            $user = $userService->getBasicInfo($cashRes['user_id']);
            $fre_millet = $user['fre_millet'];
            $total = $cashRes['millet'];
            $millet = $user['millet'] + $total;
            $total_millet = $millet + $fre_millet;
            $update['millet'] = $millet;
            $update['fre_millet'] = $fre_millet;
            $update['total_millet'] = $total_millet;
            $update['his_millet'] = $user['his_millet'] + $total;
            $num = Db::name('user')->where(array('user_id' => $user['user_id']))->update($update);
            $userService->updateRedis($user['user_id'], $update);
        }

        return ['code' => 200];
    }
}

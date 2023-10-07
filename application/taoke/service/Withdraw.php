<?php
/**
 * Created by PhpStorm.
 * User: 李成
 * Date: 2020/5/15
 * Time: 11:34
 */
namespace app\taoke\service;

use app\admin\service\User;
use bxkj_module\service\Service;
use think\Db;

class Withdraw extends Service
{

    public function getTotal($get)
    {
        $this->db = Db::name('collect');
        $this->setWhere($get);
        $count = $this->db->count();
        return (int)$count;
    }

    public function getList($get, $offset=0, $length=20)
    {
        $list = [];
        $this->db = Db::name('user_withdraw');
        $this->setWhere($get)->setOrder($get);
        $this->db->field('uw.id,uw.cash_no,uw.user_id,uw.money,uw.status,uw.admin_remark,uw.handler_time,uw.cash_account,uw.create_time,user.nickname');
        $list = $this->db->limit($offset, $length)->select();
        $cashAccountIds = $this->getIdsByList($list, 'cash_account');
        $cashAccounts = [];
        if (!empty($cashAccountIds)) {
            $cashAccounts = Db::name('cash_account')->where(array('id' => $cashAccountIds))->limit(count($cashAccountIds))->select();
        }
        foreach ($list as &$item) {
            $item['create_time'] = date('Y-m-d H:i:s', $item['create_time']);
            $cashAcc = $this->getItemByList($item['cash_account'], $cashAccounts, 'id');
            $cardNameArr = explode('-', $cashAcc['card_name']);

            $item['title'] = "提现到" . $cardNameArr[0];
            $item['accout_num'] =  $cashAcc['account'];
            $item['descr'] = "【{$item['cash_no']}】 提现 {$item['money']} 元";
            $item['handler_time'] = !empty($item['handler_time']) ? date('Y-m-d H:i:s', $item['handler_time']) : '';
        }
        return $list;
    }


    protected function setWhere($get)
    {
        $this->db->alias('uw');
        $where = array();
        $where1 = array();
        if (isset($get['keyword']) && $get['keyword'] != '') {
            $where1[] = ['user.nickname','like','%'.$get['keyword'].'%'];
        }
        if (isset($get['status']) && $get['status'] != '') {
            $where['uw.status'] = $get['status'];
        }
        if (isset($get['user_id']) && $get['user_id'] != '') {
            $where['uw.user_id'] = $get['user_id'];
        }
        if (isset($get['type']) && $get['type'] != '') {
            $where['uw.type'] = $get['type'];
        }
        $this->db->where($where)->where($where1);
        $this->db->join('__USER__ user', 'user.user_id=uw.user_id', 'LEFT');
        return $this;
    }

    protected function setOrder($get)
    {
        $order = array();
        if (empty($get['sort'])) {
            $order = 'uw.id DESC';
        }
        $this->db->order($order);
        return $this;
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
        $num = Db::name('user_withdraw')->where(['id' => $post['id']])->update(['status' => $status, 'admin_remark' => $post['describe'], "handler_time" => time()]);
        if (!$num) return ['code' => 102, 'msg' => '操作失败'];
        return ['code' => 200];
    }


    /**
     * 淘客申请提现
     * @param $inputData
     * @return array|bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function apply($inputData)
    {
        $userService = new User();
        if (empty($inputData['user_id'])) return $this->setError('USER_ID不能为空');
        $user = $userService->getBasicInfo($inputData['user_id']);
        $taokeMoney = $inputData['take_money'];
        $cashAccount = $inputData['cash_account'];
        if (!validate_regex($taokeMoney, '/^[0-9]+$/') || $taokeMoney <= 0) return $this->setError('金额不正确');
        if (empty($cashAccount)) return $this->setError('请选择提现账号');
        if (empty($user)) return $this->setError('用户不存在');
        self::startTrans();
        if ($user['taoke_money_status'] != '1') {
            self::rollback();
            return $this->setError('提现功能已禁用');
        }
        if ($taokeMoney > $user['taoke_money']) {
            self::rollback();
            return $this->setError('淘客余额不足');
        }
        $cashAccountInfo = Db::name('cash_account')->where(array('user_id' => $user['user_id'], 'id' => $cashAccount, 'delete_time' => null))->find();
        if (!$cashAccountInfo) {
            self::rollback();
            return $this->setError('提现账号不存在');
        }
        if ($cashAccountInfo['verify_status'] == '2') {
            self::rollback();
            return $this->setError('提现账号无效');
        }
        $data['cash_no'] = get_order_no('cash');
        $data['user_id'] = $user['user_id'];
        $data['status'] = 'wait';
        $data['type'] = 'taoke';
        $data['money'] = $taokeMoney;
        $data['aid'] = 1;
        $data['cash_account'] = $cashAccountInfo['id'];
        $data['create_time'] = time();
        $id = Db::name('user_withdraw')->insertGetId($data);
        if (!$id) {
            self::rollback();
            return $this->setError('提现失败[01]');
        }
        $data['id'] = $id;

        $leftMoney = $user['taoke_money'] - $taokeMoney;
        $res = $userService->updateData($user['user_id'], ["taoke_money" => $leftMoney]);//剩余金额
        if (!$res) {
            self::rollback();
            return $this->setError($this->getError());
        }
        $userService->updateRedis($user['user_id'], ["taoke_money" => $leftMoney]);
        self::commit();
        return array(
            'taoke_money' => $leftMoney,
        );
    }
}
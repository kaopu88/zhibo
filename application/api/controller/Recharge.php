<?php

namespace app\api\controller;

use app\common\controller\UserController;
use app\common\service\RechargeBean;
use app\common\service\AppleReceipt;
use app\common\service\Recharge as RechargeModel;
use app\common\service\RechargeOrder;
use think\Db;

class Recharge extends UserController
{
    public function index()
    {
        $params = input();
        $params['update_bean'] = isset($params['update_bean']) ? $params['update_bean'] : '0';
        $params['recharge_channel'] = $params['recharge_channel'] ? $params['recharge_channel'] : APP_OS_NAME;
        $info = Db::name('bean')->field('bean,pay_total,fre_bean')
            ->where(array('user_id' => USERID))->find();
        if (empty($info)) return $this->jsonError(APP_BEAN_NAME . '账号不存在');
        if ($params['update_bean'] == '0') {
            $recBean = new RechargeBean();
            $list = $recBean->getList($params, 0, 20);
            $info['list'] = $list;
            $info['url'] = '';
        }
        return $this->success($info,'获取成功');
    }

    public function create()
    {
        $params = input();
        $params['user_id'] = USERID;
        $params['client_ip'] = get_client_ip();
        $params['app_v'] = APP_V;
        $params['quantity'] = 1;
        $rec = new RechargeModel();
        $result = $rec->create($params);
        if (!$result) return $this->jsonError($rec->getError());
        return $this->success($result,'获取成功');
    }

    public function appleCreate()
    {
        $params = input();
        $receipt = $params['receipt'];
        if (empty($receipt)) return $this->jsonError('支付收据不能为空');
        $appleRec = new AppleReceipt();
        $result = $appleRec->writeOff(USERID, $receipt, [
            'client_ip' => get_client_ip(),
            'v' => APP_V
        ]);
        if (!$result) return $this->jsonError('支付失败' . (string)$appleRec->getError());
        $newInfo = Db::name('bean')->field('bean,pay_total,fre_bean')->where(array('user_id' => USERID))->find();
        return $this->success(array_merge($newInfo, array(
            'order_no' => $result['order_nos'][0],
            'order_type' => 'recharge',
            'order_nos' => implode(',', $result['order_nos'])
        )), '充值成功');
    }

    public function getOrders()
    {
        $params = input();
        $params['user_id'] = USERID;
        $recOrder = new RechargeOrder();
        $offset = isset($params['offset']) ? $params['offset'] : 0;
        $length = isset($params['length']) ? ($params['length'] > 100 ? 100 : $params['length']) : PAGE_LIMIT;
        $result = $recOrder->getList($params, $offset, $length);
        return $this->success($result ? $result : [], '获取成功');
    }

}

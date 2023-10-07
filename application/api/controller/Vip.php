<?php

namespace app\api\controller;
use app\common\controller\UserController;
use app\common\service\Vip AS VipModel;
use app\common\service\AppleReceipt;
use app\api\service\VipOrder;
use bxkj_common\Console;
use bxkj_common\RabbitMqChannel;
use bxkj_module\service\User;
use app\common\service\DsSession;

class Vip extends UserController
{
    public function index()
    {
        $user = DsSession::get('user');
        $menu = array(
            ['name' => '专属标识', 'url' => '', 'icon' => DOMAIN_URL . '/static/common/image/default/my_vip_sign_3x.png'],
            ['name' => '经验加速', 'url' => '', 'icon' => DOMAIN_URL . '/static/common/image/default/my_vip_experience_3x.png'],
            ['name' => '免费观看付费视频', 'url' => '', 'icon' => DOMAIN_URL . '/static/common/image/default/my_vip_Freewatch_3x.png'],
            ['name' => 'VIP专属礼物', 'url' => '', 'icon' => DOMAIN_URL . '/static/common/image/default/my_vip_gift_3x.png'],
            ['name' => '推荐位优先推荐权', 'url' => '', 'icon' => DOMAIN_URL . '/static/common/image/default/my_vip_Recommend_3x.png']
        );
        $model = new VipModel();
        $get = ['os' => APP_OS_NAME];
        $vips = $model->getList($get);
        $userPart = '';
        if ($user) $userPart = copy_array($user, 'user_id,nickname,avatar,vip_status,vip_expire,vip_expire_str,level,balance,phone');
        $res = array(
            'vips' => $vips ? $vips : [],
            'menus' => $menu,
            'user' => $userPart
        );
        return $this->success($res);
    }

    public function create()
    {
        $params = request()->param();
        $params['user_id'] = USERID;
        $params['client_ip'] = get_client_ip();
        $params['app_v'] = APP_V;
        $params['quantity'] = 1;
        $rec = new VipModel();
        $result = $rec->create($params);
        if (!$result) return $this->jsonError($rec->getError());
        return $this->success($result);
    }

    public function appleCreate()
    {
        $params = request()->param();
        $receipt = $params['receipt'];
        if (empty($receipt)) return $this->jsonError('支付收据不能为空');
        $appleRec = new AppleReceipt();
        $result = $appleRec->writeOff(USERID, $receipt, [
            'client_ip' => get_client_ip(),
            'v' => APP_V
        ]);
        if (!$result) return $this->jsonError('支付失败');
        $userModel = new user();
        $user = $userModel->getUser(USERID);
        if (empty($user)) return $this->jsonError('用户登录异常');
        $res = [
            'order_no' => $result['order_nos'][0],
            'order_type' => 'vip',
            'order_nos' => implode(',', $result['order_nos']),
            'vip_status' => $user['vip_status'],
            'vip_expire' => $user['vip_expire'],
            'vip_expire_str' => $user['vip_expire_str']
        ];
        return $this->success($res);
    }

    public function buy()
    {
        $params = request()->param();
        $vip = new VipModel();
        $params['user_id'] = USERID;
        $params['client_ip'] = get_client_ip();
        $params['app_v'] = APP_V;
        $result = $vip->buy($params);
        if (!$result) return $this->jsonError($vip->getError());
        //对接rabbitMQ
        $rabbitChannel = new RabbitMqChannel(['user.credit']);
        $rabbitChannel->exchange('main')->sendOnce('user.credit.purchase_vip', ['user_id' => USERID]);
        return $this->success($result, '购买成功');
    }

    public function buu()
    {
        return $this->buy();
    }

    public function order()
    {
        $params = request()->param();
        $params['user_id'] = USERID;
        $vip = new VipOrder();
        $offset = isset($params['offset']) ? $params['offset'] : 0;
        $length = isset($params['length']) ? ($params['length'] > 100 ? 100 : $params['length']) : PAGE_LIMIT;
        $result = $vip->getList($params, $offset, $length);
        return $this->success($result ? $result : [], '购买成功');
    }
}
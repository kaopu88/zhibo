<?php

namespace app\h5\controller;


use bxkj_common\ClientInfo;
use bxkj_module\service\RechargeOrder;
use bxkj_common\CoreSdk;
use think\Db;
use think\Request;

class WxCharge extends WxController
{
    protected $wxUser;

    public function wx()
    {
        $this->wxUser = $this->getWxUserInfo();

        if (!$this->wxUser) {
            $this->authorize([
                'redirect' => url('wx_charge/index')
            ]);
            exit();
        }
    }


    public function index()
    {
        if (!$this->wxUser) $this->wx();
        $list = Db::name('recharge_bean')->where([
            ['apple_id', 'eq', ''],
            ['status', 'eq', '1']
        ])->order('sort desc,create_time desc')->select();
        $this->assign('list', $list);
        return $this->fetch();
    }


    public function get_user_info(Request $request)
    {
        if (!$this->wxUser) $this->wx();

        $params = $request->param();

        $coreSdk = new CoreSdk();

        $res = $coreSdk->post('/user/get_user', ['user_id' => $params['user_id']]);
        $data = [];
        if ($res) {
            $data['user_id'] = $res['user_id'];
            $data['avatar'] = $res['avatar'];
            $data['nickname'] = $res['nickname'];
            $data['phone'] = $res['phone']?str_hide($res['phone'], 3, 3):'未绑定';
            $data['level'] = $res['level'];
        }
        return $this->success('ok', $data);
    }

    public function wx_pay_order(Request $request)
    {
        if (!$this->wxUser) $this->wx();

        $params = $request->param();

        $rec = new RechargeOrder();

        $order = $rec->create([
            'id' => $params['change_id'],
            'user_id' => $params['user_id'],
            'quantity' => 1,
            'client_ip' => get_client_ip(),
            'app_v' => ''
        ]);

        if (!$order) return $this->error($rec->getError());

        $notify_url = H5_URL . '/pay_callback/recharge_notify';

        $coreSdk = new CoreSdk();
        ClientInfo::refreshByUserAgent(null, [
            'client_type' => 'h5',
            'client_object' => 'user'
        ]);
        $res = $coreSdk->post('/third_order/unifiedorder', [
            'user_id' => $params['user_id'],
            'pay_method' => 'wxpay_h5',
            'rel_type' => 'recharge',
            'rel_no' => $order['order_no'],
            'notify_url' => $notify_url,
            'openid' => $this->wxUser['openid'],
            'client_seri' => ClientInfo::encode()
        ]);

        /*        dump($coreSdk->getError());
        die;*/
        if ($res == false) return $this->error('下单错误' . $coreSdk->getError());

        return $this->success('ok', $res);
    }


    public function pay(Request $request)
    {
        if (!$this->wxUser) $this->wx();

        $params = $request->param();

        if (empty($params)) $this->redirect('/wxcharge');

        $params["package"] = "prepay_id=u802345jgfjsdfgsdg888";

        $params["signType"] = "MD5";

        $total_fee = $params['total_fee'];

        unset($params['total_fee']);

        $this->view->config('default_filter', '')->assign('order', json_encode($params));

        $this->assign('money', $total_fee);

        return $this->fetch();
    }


}
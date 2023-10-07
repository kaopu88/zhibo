<?php

namespace app\h5\controller;

use bxkj_common\ClientInfo;
use bxkj_common\CoreSdk;
use bxkj_module\service\RechargeOrder;
use think\Db;
use think\facade\Request;

class Recharge extends WxController
{
    protected $wxUser;

    public function __construct()
    {   
        parent::__construct();
        if (strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'micromessenger') !== false) {
            if (!$this->wxUser) $this->wx();
        }
    }

    public function index()
    {
        $rel_no = input('rel_no');
        $order = ['rel_no' => ''];
        if (!empty($rel_no) && input('pay_method') != 'wxwap') {
            $get = Request::get();
            if (!verify_payment_return($get)) {
                $this->error('支付验证失败', 1, url('index'));
            }
            $order = Db::name('recharge_order')->where(['order_no' => $rel_no, 'pay_status' => '1'])->find();
            if (empty($order)) $this->error('充值错误', 1, url('index'));
        }
        
        
        //查询出第一，定为默认的值
        // $payment_one = Db::name('payments')->where('status',1)->order('list_order desc')->field('id,class_name,online_pay,name,alias,thumb,coin_type')->limit(1)->find();
        
        
      
        
        

        // $list = Db::name('recharge_bean')->where([
        //     ['apple_id', 'eq', ''],
        //     ['status', 'eq', '1']
            
        // ])->whereIn('id',$payment_one['coin_type'])->order('sort desc')->select();
        
     
    
        
        $this->assign('product_name', config('app.product_setting.name'));
        $this->assign('h5_image', config('upload.image_defaults'));
        $this->assign('prefix_name', APP_PREFIX_NAME);
        $this->assign('bean_name', APP_BEAN_NAME);
        
        $this->assign('order', $order);
        
        
        $payments = Db::name('payments')->where('status',1)->order('list_order desc')->field('id,class_name,online_pay,name,alias,thumb,coin_type')->select();
        foreach ($payments as &$v){
            $v['taocan'] = Db::name('recharge_bean')->where([
                ['apple_id', 'eq', ''],
                ['status', 'eq', '1']
            ])->whereIn('id',$v['coin_type'])->order('sort asc')->select();
        }
        
        $list = isset($payments[0]['taocan'])?$payments[0]['taocan']:[];
        $this->assign('_payments', $payments);
        $this->assign('_list', $list);
        $this->assign('_info', [
            'user_id' => input('user_id', ''),
            'bean_id' => input('bean_id', ''),
            'pay_method' => input('pay_method', '')
        ]);
        $this->assign('self_url', url("recharge/index", ['user_id' => input('user_id', '')]));
        $this->assign('is_wxwap', input('pay_method') == 'wxwap' ? 1 : 0);
        $this->assign('rel_no', input('rel_no'));
        return $this->fetch();
    }

    public function get_user_info()
    {
        $userId = input('user_id');
        $prefix_name = APP_PREFIX_NAME;
     
        if (empty($userId)) $this->error('用户不存在，请检查用户ID是否正确');
        $coreSdk = new CoreSdk();
        $res = $coreSdk->post('/user/get_user', ['user_id' => $userId]);
        if (!$res) $this->error('用户不存在，请检查用户ID是否正确');
        $data = [
            'user_id' => $res['user_id'],
            'avatar' => $res['avatar'],
            'nickname' => $res['nickname'],
            'phone' => $res['phone'] ? str_hide($res['phone'], 3, 3) : '未绑定',
            'level' => $res['level']
        ];
        return $this->success('ok', $data);
    }

    public function pay_order()
    {
        $payMethod = strtolower(input('pay_method'));
        $beanId = input('bean_id');
        $userId = input('user_id');
        if (empty($userId)) $this->error('请输入'.APP_PREFIX_NAME.'ID');
        if (empty($beanId)) $this->error('请选择充值套餐');
        if (empty($payMethod)) $this->error('请选择支付方式');
        $payInfo = Db::name('payments')->where('status',1)->where('class_name',$payMethod)->find();
        if (!$payInfo) $this->error($payMethod . '不支持');
        // if (!in_array($payMethod, ['alipay', 'wxpay', 'wxwap','hjpay'])) $this->error($payMethod . '不支持');
        $payArr = explode(',',$payInfo['coin_type']);
        if(!in_array("$beanId",$payArr))$this->error('支付套餐不支持');
        
        if ($payMethod == 'wxpay' && !$this->wxUser) $this->wx();
        $rec = new RechargeOrder();
        $order = $rec->create([
            'id' => $beanId,
            'user_id' => $userId,
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
        // $newPayMethod = $payMethod == 'wxpay' ? 'wxpay_h5' : 'alipay_wap';
        // if ($payMethod == 'wxwap') {
        //     $newPayMethod = 'wxpay_wxwap';
        // }
        // if($payMethod=='hjpay'){
        //     $newPayMethod = 'hjpay';
        // }
        
        $newPayMethod = $payMethod;
        $data = [
            'user_id' => $userId,
            'pay_method' => $newPayMethod,
            'rel_type' => 'recharge',
            'rel_no' => $order['order_no'],
            'notify_url' => $notify_url,
            'return_url' => H5_URL . '/recharge/index',
            'client_seri' => ClientInfo::encode()
        ];
        if ($payMethod == 'wxpay') {
            $data['openid'] = $this->wxUser['openid'];
        }
        $res = $coreSdk->post('/third_order/unifiedorder', $data);
        if ($res == false) return $this->error('下单错误' . $coreSdk->getError());
        // var_dump($res);die;
        return $this->success('ok', $res);
    }

    public function wx()
    {
        $this->wxUser = $this->getWxUserInfo();
        if (!$this->wxUser) {
            $this->authorize([
                'redirect' => url('recharge/index')
            ]);
            exit();
        }
    }

    public function wxwapquery()
    {
        $post = Request::post();
        $order = Db::name('recharge_order')->where(['order_no' => $post['rel_no'], 'pay_status' => '1'])->find();
        if (empty($order)) $this->error('充值错误', 1,'', ['status' => 'ERROR']);
        $this->success('充值成功', ['status' => 'SUCCESS', 'bean_num' => $order['bean_num']]);
    }
    
    public function pay_type()
    {   
        $data = Db::name('payments')->where('status',1)->order('list_order desc')->field('id,class_name,online_pay,name,alias,thumb,coin_type')->select();
        foreach ($data as &$v){
            $v['taocan'] = Db::name('recharge_bean')->whereIn('id',$v['coin_type'])->order('sort asc')->select();
        }
        $this->success('ok', $data);
    }
     public function paymentid()
    {   
         $id =  input('id');
         
          $data = Db::name('payments')->where('status',1)->where("id",$id)->order('list_order desc')->field('id,class_name,online_pay,name,alias,thumb,coin_type')->find();
        
            $data['taocan'] = Db::name('recharge_bean')->whereIn('id',$data['coin_type'])->where([['apple_id', 'eq', '']])->order('sort asc')->select();
      
        
        $this->success('ok', $data);
    }
    
}
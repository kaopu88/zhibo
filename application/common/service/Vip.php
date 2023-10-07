<?php

namespace app\common\service;

use bxkj_common\ClientInfo;
use bxkj_common\CoreSdk;
use think\Db;

class Vip extends Service
{
    protected $redis;

    public function getList($get = array(), $offset = 0, $length = 20)
    {
        $where = [['status', 'eq', '1']];
        if ($get['os'] == 'ios') {
            $where[] = ['apple_id', 'neq', ''];
        } else if ($get['os'] == 'android') {
            $where[] = ['apple_id', 'eq', ''];
        } else {
            return [];
        }
        $list = Db::name('vip')->where($where)
            ->field('id,name,length,unit,sales,status,sort,create_time,price,thumb,rmb,apple_id')
            ->order('sort desc,create_time desc')->limit($offset, $length)->select();
        foreach ($list as &$item) {
            $item['create_time'] = date('Y-m-d', $item['create_time']);
        }
        return $list;
    }

    //购买VIP
    public function buy($inputData)
    {
        $id = $inputData['id'];
        $user_id = $inputData['user_id'];
        if (empty($id)) return $this->setError('请选择VIP');
        $vipInfo = Db::name('vip')->where(array('status' => '1', 'id' => $id))->find();
        if (!$vipInfo) return $this->setError('VIP不存在或已下线');
        $userService = new User();
        $user = $userService->getBasicInfo($user_id);
        if (empty($user)) return $this->setError('用户不存在');
        $price = $vipInfo['price'];
        $rmb = $vipInfo['rmb'];
        if ($user['pay_status'] != '1') return $this->setError(APP_BEAN_NAME . '禁止支付');
        if ($price > $user['bean']) return $this->setError(APP_BEAN_NAME . '不足', 1005);
        $data['order_no'] = get_order_no('vip');
        $data['vip_id'] = $vipInfo['id'];
        $data['thumb'] = $vipInfo['thumb'];
        $data['name'] = $vipInfo['name'];
        $data['length'] = $vipInfo['length'];
        $data['unit'] = $vipInfo['unit'];
        $data['price'] = $price;
        $data['rmb'] = $rmb;
        $data['settlement'] = 'bean';
        $data['user_id'] = $user['user_id'];
        $data['pay_status'] = '0';
        $data['client_ip'] = $inputData['client_ip'];
        $data['app_v'] = $inputData['app_v'];
        $data['create_time'] = time();
        $data['apple_id'] = $vipInfo['apple_id'] ? $vipInfo['apple_id'] : '';
        $orderId = Db::name('vip_order')->insertGetId($data);
        if (!$orderId) return $this->setError('开通失败');
        $data['id'] = $orderId;
        $payRes = $this->pay($data, $user);
        if (!$payRes) return false;
        return array(
            'order_no' => $data['order_no'],
            'vip_id' => $data['vip_id'],
            'pay_status' => '1',
            'vip_status' => $payRes['vip_status'],
            'vip_expire' => $payRes['vip_expire'],
            'vip_expire_str' => $payRes['vip_expire_str'],
        );
    }
    
    //购买VIP
    public function buyVideo($inputData)
    {
        $id = $inputData['id'];
        $user_id = $inputData['user_id'];
        if (empty($id)) return $this->setError('请选择需要支付的视频');
        $vipInfo = Db::name('video')->where(array('id' => $id))->find();
        if (!$vipInfo) return $this->setError('视频不存在或已下线');
        if($vipInfo['is_pay'] != 1)return $this->setError('免费视频不需要支付');
        $userService = new User();
        $user = $userService->getBasicInfo($user_id);
        if (empty($user)) return $this->setError('用户不存在');
        $price = $vipInfo['price'];
        $rmb = $vipInfo['rmb'];
        if ($user['pay_status'] != '1') return $this->setError(APP_BEAN_NAME . '禁止支付');
        if ($price > $user['bean']) return $this->setError(APP_BEAN_NAME . '不足', 1005);
        $data['order_no'] = get_order_no('vip');
        $data['days'] = date('Y-m-d');
        $data['video_id'] = $vipInfo['id'];
        $data['price'] = $price;
        $data['settlement'] = 'bean';
        $data['user_id'] = $user['user_id'];
        $data['pay_status'] = '0';
        $data['client_ip'] = $inputData['client_ip'];
        $data['create_time'] = time();
        $orderId = Db::name('video_pay_log')->insertGetId($data);
        if (!$orderId) return $this->setError('开通失败');
        $data['id'] = $orderId;
        $payRes = $this->video_pay($data, $user);
        if (!$payRes) return false;
        return array(
            'order_no' => $data['order_no'],
            'vip_id' => $data['video_id'],
            'pay_status' => '1',
        );
    }
    //视频支付
    protected function video_pay($order, $user = null, $payBean = true)
    {
        $orderNo = $order['order_no'];
        $user_id = $order['user_id'];
        if (!isset($user)) {
            $userModel = new User();
            $user = $userModel->getBasicInfo($user_id);
            if (!$user) return $this->setError('用户不存在');
        }
        $coreSdk = new CoreSdk();
        if ($payBean) {
            $payRes = $coreSdk->payBean(array(
                'user_id' => $order['user_id'],
                //整数类型的
                'total' => (int)$order['price'],
                'trade_type' => 'video',
                'trade_no' => $orderNo,
                'client_seri' => ClientInfo::encode()
            ));
            if (!$payRes) return $this->setError($coreSdk->getError());
        }
        //更新订单
        $updateData['pay_status'] = '1';
        $updateData['pay_time'] = time();
        $num = Db::name('video_pay_log')->where(array('order_no' => $orderNo))->update($updateData);
        if (!$num) return $this->setError('视频支付更新失败');
        return $order;
    }
    
    
    //支付
    protected function pay($order, $user = null, $payBean = true)
    {
        $orderNo = $order['order_no'];
        $user_id = $order['user_id'];
        if (!isset($user)) {
            $userModel = new User();
            $user = $userModel->getBasicInfo($user_id);
            if (!$user) return $this->setError('用户不存在');
        }
        $coreSdk = new CoreSdk();
        if ($payBean) {
            $payRes = $coreSdk->payBean(array(
                'user_id' => $order['user_id'],
                //整数类型的
                'total' => (int)$order['price'],
                'trade_type' => 'vip',
                'trade_no' => $orderNo,
                'client_seri' => ClientInfo::encode()
            ));
            if (!$payRes) return $this->setError($coreSdk->getError());
        }
        //延长用户VIP时间
        $result = $coreSdk->post('user/extended_vip_expire', array(
            'user_id' => $user['user_id'],
            'unit' => $order['unit'],
            'length' => $order['length'],
        ));
        $result = $result ? $result : array();
        $updateData['vip_expire'] = $user['vip_expire'] ? $user['vip_expire'] : 0;
        $updateData['vip_status'] = $user['vip_status'] ? $user['vip_status'] : '0';
        $updateData['new_vip_expire'] = $result ? $result['vip_expire'] : 0;
        $updateData['pay_status'] = '1';
        $updateData['pay_time'] = time();
        $num = Db::name('vip_order')->where(array('order_no' => $orderNo))->update($updateData);
        if (!$num) return $this->setError('VIP更新失败');
        return $result;
    }

    //支付成功
    public function paySuccess($thirdData, $payBean = true)
    {
        $orderNo = $thirdData['rel_no'];
        $where = array('order_no' => $orderNo, 'pay_status' => '0');
        $thirdTradeNo = $thirdData['trade_no'];
        if (empty($thirdTradeNo)) return $this->setError('第三方订单号不存在');
        $order = Db::name('vip_order')->where($where)->find();
        if (!$order) return $this->setError('VIP订单不存在');
        $where2 = ['third_trade_no' => $thirdTradeNo, 'pay_method' => $thirdData['pay_method']];
        $num2 = Db::name('vip_order')->where($where2)->count();
        if ($num2 > 0) return $this->setError('第三方订单号已存在');
        $userModel = new User();
        $user = $userModel->getBasicInfo($order['user_id']);
        if (empty($user)) return $this->setError('用户不存在');
        $payRes = $this->pay($order, $user, $payBean);
        if (!$payRes) return false;
        $updateData['pay_status'] = '1';
        $updateData['pay_time'] = time();
        $updateData['pay_method'] = $thirdData['pay_method'];
        $updateData['third_trade_no'] = $thirdTradeNo;
        $num = Db::name('vip_order')->where(array('order_no' => $orderNo))->update($updateData);
        if (!$num) return $this->setError('支付失败');
        return array(
            'order_no' => $order['order_no'],
            'vip_id' => $order['vip_id'],
            'pay_status' => '1',
            'vip_status' => $payRes['vip_status'],
            'vip_expire' => $payRes['vip_expire'],
            'vip_expire_str' => $payRes['vip_expire_str'],
        );
    }

    //创建VIP订单
    public function create($inputData)
    {
        $id = $inputData['id'];
        $appleId = $inputData['apple_id'];
        $user_id = $inputData['user_id'];
        $quantity = $inputData['quantity'];
        if (empty($id) && empty($appleId)) return $this->setError('请选择VIP');
        $where = array('status' => '1');
        if (!empty($id)) {
            $where['id'] = $id;
        } else {
            $where['apple_id'] = $appleId;
        }
        $vipInfo = Db::name('vip')->where($where)->find();
        if (!$vipInfo) return $this->setError('VIP不存在或已下线');
        $userModel = new User();
        $user = $userModel->getBasicInfo($user_id);
        if (empty($user)) return $this->setError('用户不存在');
        $num = Db::name('vip_order')->where(array(
            'user_id' => $user['user_id'],
            'pay_status' => '0'
        ))->where([['create_time', 'gt', mktime(0, 0, 0)]])->count();
        if ($num > 50) return $this->setError('今日订单超过50笔未支付');
        $rmb = $vipInfo['rmb'];
        $price = $vipInfo['price'];
        $data['order_no'] = get_order_no('vip');
        $data['vip_id'] = $vipInfo['id'];
        $data['thumb'] = $vipInfo['thumb'];
        $data['name'] = $vipInfo['name'];
        $data['length'] = $vipInfo['length'];
        $data['unit'] = $vipInfo['unit'];
        $data['rmb'] = $rmb;
        $data['price'] = $price;
        $data['settlement'] = 'rmb';
        $data['user_id'] = $user['user_id'];
        $data['pay_status'] = '0';
        $data['client_ip'] = $inputData['client_ip'];
        $data['app_v'] = $inputData['app_v'];
        $data['create_time'] = time();
        $data['apple_id'] = $vipInfo['apple_id'];
        $orderId = Db::name('vip_order')->insertGetId($data);
        if (!$orderId) return $this->setError('创建VIP订单失败');
        return [
            'id' => $orderId,
            'order_type' => 'vip',
            'order_no' => $data['order_no'],
            'user_id' => $data['user_id'],
            'apple_id' => $data['apple_id'],
            'price' => $data['price'],
            'rmb' => $data['rmb'],
            'pay_status' => '0'
        ];
    }

}
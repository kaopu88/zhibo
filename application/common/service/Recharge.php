<?php
namespace app\common\service;
use bxkj_common\ClientInfo;
use bxkj_common\CoreSdk;
use bxkj_common\DateTools;
use bxkj_common\RabbitMqChannel;
use bxkj_module\service\Task;
use think\Db;

class Recharge extends Service
{
    //创建充值订单
    public function create($inputData)
    {
        $id = $inputData['id'];
        $appleId = $inputData['apple_id'];
        $user_id = $inputData['user_id'];
        $quantity = $inputData['quantity'];
        if (empty($id) && empty($appleId)) return $this->setError('请选择充值套餐');
        if ($quantity <= 0) return $this->setError('套餐数量不能小于等于0');
        $where = array('status' => '1');
        if (!empty($id)) {
            $where['id'] = $id;
        } else {
            $where['apple_id'] = $appleId;
        }
        $beanInfo = Db::name('recharge_bean')->where($where)->find();
        if (!$beanInfo) return $this->setError('充值套餐不存在或已下线');
        $now = time();
        $data['order_no'] = get_order_no('recharge');
        $data['bean_id'] = $beanInfo['id'];
        $data['bean_num'] = $beanInfo['bean_num'];
        $data['name'] = $beanInfo['name'];
        $data['price'] = $beanInfo['price'];
        $data['total_fee'] = round($beanInfo['price'] * $quantity, 2);
        $data['quantity'] = $quantity;
        $data['apple_id'] = $beanInfo['apple_id'] ? $beanInfo['apple_id'] : '';
        $userModel = new User();
        $user = $userModel->getBasicInfo($user_id);
        if (empty($user)) return $this->setError('用户不存在');
        $num = Db::name('recharge_order')->where(array(
            'user_id' => $user['user_id'],
            'pay_status' => '0'
        ))->where([['create_time', 'gt', mktime(0, 0, 0)]])->count();
        if ($num > 50) return $this->setError('今日订单超过50笔未支付');
        $data['user_id'] = $user['user_id'];
        $data['isvirtual'] = $user['isvirtual'];
        $data['pay_method'] = '';
        $data['pay_status'] = '0';
        $data['client_ip'] = $inputData['client_ip'];
        $data['app_v'] = $inputData['app_v'];
        $data['create_time'] = $now;
        $data['year'] = date('Y', $now);
        $data['month'] = date('Ym', $now);
        $data['day'] = date('Ymd', $now);
        $data['fnum'] = DateTools::getFortNum($now);
        // var_dump($data);die;
        $orderId = Db::name('recharge_order')->insertGetId($data);
        if (!$orderId) return $this->setError('创建充值订单失败');
        return [
            'id' => $orderId,
            'order_type' => 'recharge',
            'order_no' => $data['order_no'],
            'user_id' => $data['user_id'],
            'price' => $data['price'],
            'total_fee' => $data['total_fee'],
            'pay_status' => '0',
            'name' => $data['name']
        ];
    }

    //支付成功
    public function paySuccess($thirdData)
    {

        $orderNo = $thirdData['rel_no'];
        $where = array('order_no' => $orderNo, 'pay_status' => '0');
        $thirdTradeNo = $thirdData['trade_no'];
        if (empty($thirdTradeNo)) return $this->setError('第三方订单号不存在');
        $order = Db::name('recharge_order')->where($where)->find();
        if (!$order) return $this->setError('充值订单不存在');
        $where2 = ['third_trade_no' => $thirdTradeNo, 'pay_method' => $thirdData['pay_method']];
        self::startTrans();
        $num2 = Db::name('recharge_order')->where($where2)->count();
        if ($num2 > 0) {
            self::rollback();
            return $this->setError('第三方订单号已存在');
        }
        $updateData['pay_status'] = '1';
        $updateData['pay_time'] = time();
        $updateData['pay_method'] = $thirdData['pay_method'];
        $updateData['third_trade_no'] = $thirdTradeNo;
        $num = Db::name('recharge_order')->where(array('order_no' => $orderNo))->update($updateData);
        if (!$num) {
            self::rollback();
            return $this->setError('支付失败');
        }
        $coreSdk = new CoreSdk();
        $beanNum = (int)$order['bean_num'];

        $incRes = $coreSdk->incBean(array(
            'user_id' => $order['user_id'],
            'total' => $beanNum * $order['quantity'],
            //'total' => $beanNum * $order['quantity'],
            'trade_type' => 'recharge',
            'trade_no' => $order['order_no'],
            'client_seri' => ClientInfo::encode()
        ));
        if (!$incRes) {
            self::rollback();
            return $this->setError($coreSdk->getError());
        }
        self::commit();
        //充值任务
        $taskMod = new Task();
        $data = [
            'user_id' => $order['user_id'],
            'task_type' => 'dayRecharge',
            'task_value' => 1,
            'status' => 0
        ];
        $taskMod->subTask($data);
        //对接rabbitMQ
        $rabbitChannel = new RabbitMqChannel(['user.credit']);
        $rabbitChannel->exchange('main')->sendOnce('user.credit.user_recharge', ['user_id' => $order['user_id'], 'pay_method' => $order['pay_method'], 'value' => $order['total_fee']]);
        return true;
    }

}
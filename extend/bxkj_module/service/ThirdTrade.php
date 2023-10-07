<?php

namespace bxkj_module\service;

use bxkj_common\ClientInfo;
use bxkj_common\HttpClient;
use think\Db;
use think\Exception;
use bxkj_payment\ThirdPayMethod;

class ThirdTrade extends Service
{
    protected $driver;
    protected $payMethod;
    protected $defaultValidPeriod = 3600;//默认有效时间一个小时

    public function __construct()
    {
        parent::__construct();
        if (false) $this->driver = new ThirdPayMethod();//便于IDE提示
    }

    //下单
    public function unifiedorder($inputData)
    {
        ClientInfo::refreshByParams($inputData);
        unset($inputData['client_seri']);
        $data = $this->df->process('unifiedorder@third_trade', $inputData)->output(false);
        if (!$data) return $this->setError($this->df->getError());
        $userId = $data['user_id'];
        $relType = $data['rel_type'];
        $fun = parse_name($relType, 1, false);
        $myRel = new ThirdTradeRelType();
        if (!method_exists($myRel, "{$fun}Handler")) return $this->setError('订单类型不支持');
        $relRes = call_user_func_array(array($myRel, "{$fun}Handler"), array(&$data));
        if (!$relRes) return $this->setError($myRel->getError());
        $pay_method = $data['pay_method'];//支付方式
        if (!$this->checkPayMethod($data['rel_type'], $pay_method)) return $this->setError('支付方式不支持');
        $where = array('rel_type' => $data['rel_type'], 'rel_no' => $data['rel_no']);
        $current = $this->db()->where($where)->find();
        if (!empty($current)) {
            if ($userId != $current['user_id']) return $this->setError(APP_ACCOUNT_NAME . '错误');
            if ($current['pay_status'] == '1') return $this->setError('订单已支付，请勿重复');
        }
        $data['extra_data'] = empty($data['extra_data']) ? '' : (is_array($data['extra_data']) ? json_encode($data['extra_data']) : $data['extra_data']);
        list($data['pay_platform'], $tmpPayMthod) = explode('_', $pay_method);
        $this->loadPayMethodDriver($pay_method);
        if (!$this->driver->check($current, $data)) {
            return $this->setError($this->driver->getError());
        }
        $tradeData = array(
            'subject' => $data['subject'],
            'body' => $data['body'] ? $data['body'] : '',
            'extra_data' => $data['extra_data'] ? $data['extra_data'] : '',
            'notify_url' => $data['notify_url'] ? $data['notify_url'] : '',
            'return_url' => $data['return_url'] ? $data['return_url'] : '',
            'total_fee' => $data['total_fee'],
            'app_v' => ClientInfo::get('v'),
            'client_ip' => ClientInfo::getClientIp(),
            'pay_method' => $pay_method,
            'pay_platform' => $data['pay_platform']
        );
        if ($current) {
            //更新订单
            $tradeData['update_time'] = time();
            $updateNum = $this->db()->where(array('id' => $current['id']))->update($tradeData);
            if (!$updateNum) return $this->setError('更新订单失败');
            $tradeData = array_merge($current, $tradeData);
        } else {
            //创建订单
            if (empty($userId)) return $this->setError(APP_ACCOUNT_NAME . '不可用');
            $user = Db::name('user')->where(array('user_id' => $userId, 'delete_time' => null))->find();
            if (empty($user)) return $this->setError(APP_ACCOUNT_NAME . '不可用');
            $tradeData['user_id'] = $userId;
            $tradeNo = self::getTradeNo($pay_method, $data['rel_type']);
            $tradeData['trade_no'] = $tradeNo;
            $tradeData['rel_type'] = $data['rel_type'];
            $tradeData['rel_no'] = $data['rel_no'];
            $tradeData['pay_status'] = '0';
            $tradeData['trade_status'] = 'WAIT_BUYER_PAY';
            $tradeData['pay_result'] = '';
            $tradeData['finish_result'] = '';
            $tradeData['third_trade_no'] = '';
            $tradeData['third_user_id'] = '';//不是third_uid
            $tradeData['third_app_key'] = $data['app_id'];
            $tradeData['create_time'] = time();
            $tradeData['valid_period'] = isset($data['valid_period']) ? $data['valid_period'] : $this->defaultValidPeriod;
            $tradeId = $this->db()->insertGetId($tradeData);
            if (!$tradeId) return $this->setError('创建订单失败');
            $tradeData['id'] = $tradeId;
        }
        $result = $this->driver->unifiedorder($data, $tradeData);
        if (!$result) return $this->setError($this->driver->getError());
        $tRes = array(
            'third_data' => $result,
            'trade_data' => array(
                'trade_no' => $tradeData['trade_no'],
                'rel_no' => $tradeData['rel_no'],
                'rel_type' => $tradeData['rel_type'],
                'pay_method' => $tradeData['pay_method'],
                'total_fee' => $tradeData['total_fee']
            )
        );

        return $tRes;
    }

    //检查订单号的支付方式是否可用
    public function checkAvailablePayMethod($data)
    {
        $where = array('rel_type' => $data['rel_type'], 'rel_no' => $data['rel_no']);
        $current = Db::name('third_trade')->where($where)->find();
        if (!$current) return true;
        return $current['pay_method'] == $data['pay_method'];
    }

    //生成订单号
    public static function getTradeNo($pay_method = null, $rel_type = null)
    {
        return get_order_no('third');
    }

    //加载支付方式驱动
    protected function loadPayMethodDriver($payMethod)
    {
        $className = parse_name($payMethod, 1, true) . 'PayMethod';
        $className2 = "\\bxkj_payment\\{$className}";
        if (!class_exists($className2)) {
            \exception('支付驱动不存在', 1);
        }
        $this->driver = new $className2();
        return $this->driver;
    }

    //检查支付方式是否支持
    public function checkPayMethod($relType, $payMethod)
    {
        if (!enum_in($payMethod, 'pay_methods')) return false;
        list($pay, $method) = explode('_', strtolower($payMethod));
        if ($relType == 'recharge' && $pay == 'payment') return false;
        if ($relType == 'cash' && $pay != 'payment') return false;
        return true;
    }

    //切换支付方式
    public function setPayMethod($payMethod)
    {
        if (!enum_in($payMethod, 'pay_methods')) return false;
        $this->payMethod = $payMethod;
        $this->loadPayMethodDriver($this->payMethod);
        return $this->driver;
    }

    //检查异步通知
    public function checkNotify($data)
    {
        return $this->driver->checkNotify($data);
    }

    //检查同步通知
    public function checkReturn($data)
    {
        return $this->driver->checkReturn($data);
    }

    //获取异步通知参数
    public function getNotifyData($data)
    {
        return $this->driver->getNotifyData($data);
    }

    //获取同步通知参数
    public function getReturnData($data)
    {
        return $this->driver->getReturnData($data);
    }

    //响应通知
    public function responseNotify($data)
    {
        $this->driver->responseNotify($data);
    }

}
<?php
namespace bxkj_payment;

class PcPayMethod extends PayMethod
{
    protected $payMethod = 'payment_pc';

    public function __construct()
    {
        parent::__construct();
    }

    public function check($tradeData, &$inputData)
    {
        $inputData['app_id'] = $this->config['app_id'];
        if (!isset($inputData['receipt_account'])) {
            $inputData['receipt_account'] = $this->config['receipt_account'];
        }
        return true;
    }

    //下单
    public function unifiedorder($inputData, $tradeData)
    {
        $params = array(
            'client_ip' => $tradeData['client_ip'] ? $tradeData['client_ip'] : '',
            'client_key' => $tradeData['client_key'] ? $tradeData['client_key'] : '',
            'subject' => $tradeData['subject'],
            'body' => $tradeData['body'] ? $tradeData['body'] : '',
            'out_trade_no' => $tradeData['trade_no'],
            'rel_type' => $tradeData['rel_type'],
            'rel_no' => $tradeData['rel_no'],
            'total_fee' => $tradeData['total_fee'],
            'notify_url' => $this->getNotifyUrl(),
            'return_url' => $this->getReturnUrl(),
            'payment_account' => $tradeData['account'],
            'receipt_account' => $inputData['receipt_account']
        );
        if (!empty($tradeData['valid_period'])) $params['valid_period'] = $tradeData['valid_period'];
        $orderInfo = $this->unifiedorderApi($params);
        if (!$orderInfo) return $this->setError('创建订单失败');
        $payData['trade_no'] = $orderInfo['trade_no'];
        $payData['trade_type'] = $orderInfo['trade_type'];
        $payData['url'] = KDBPAY_BASE . '/kdb/settlement';
        $payData['nonce_str'] = md5(uniqid() . get_ucode());//随机字符串
        $payData['account'] = sha1($payData['trade_no'] . $tradeData['account'] . $this->config['key']);
        $payData['pay_key'] = strtoupper(md5($payData['trade_no'] . $payData['nonce_str'] . $this->config['key']));
        $payData['pay_iv'] = strtoupper(md5($payData['nonce_str'] . $this->config['key'] . 'PAY_IV'));
        return $payData;
    }

    protected function unifiedorderApi($params)
    {
        $time = time();
        $url = KDBPAY_BASE . '/kdb/unifiedorder';
        $tmp['app_id'] = $this->config['app_id'];
        $tmp['nonce_str'] = md5(uniqid() . get_ucode());
        $tmp['subject'] = $params['subject'];
        $tmp['body'] = $params['body'] ? $params['body'] : '';
        $tmp['out_trade_no'] = $params['out_trade_no'];
        $tmp['rel_type'] = $params['rel_type'];
        $tmp['rel_no'] = $params['rel_no'];
        $tmp['total_fee'] = $params['total_fee'];
        $tmp['client_ip'] = $params['client_ip'];
        $tmp['client_key'] = $params['client_key'];
        $tmp['notify_url'] = $params['notify_url'];
        $tmp['return_url'] = $params['return_url'];
        $tmp['trade_type'] = 'app';
        $tmp['payment_account'] = $params['payment_account'];
        $tmp['receipt_account'] = $params['receipt_account'];
        $tmp['nonce_str'] = md5(uniqid() . get_ucode());//随机字符串
        if (isset($params['valid_period'])) {
            $tmp['expire_time'] = $time + $params['valid_period'];
        }
        $tmp['sign'] = generate_sign($tmp, $this->config['key']);
        $result = $this->curlClient->post($url, $tmp)->getData('json');
        if (!$result || $result['status'] != '0') return false;
        return $result['data'];
    }

    public function checkNotify($post)
    {
        $result = is_sign($post['sign'], $post, $this->config['key']);
        if (!$result) return array('status' => 'failed', 'message' => 'sign error');
        return true;
    }

    public function getNotifyData($post)
    {
        $params['out_trade_no'] = $post['out_trade_no'];
        $params['trade_no'] = $post['trade_no'];
        $params['trade_status'] = $post['trade_status'];
        $params['total_fee'] = $post['total_fee'];
        $params['uid'] = $post['account'];
        $params['pay_method'] = $this->payMethod;
        $params['app_key'] = $post['app_id'];
        return array('params' => $params, 'raw' => $post);
    }

    public function checkReturn($get)
    {
        $result = is_sign($get['sign'], $get, $this->config['key']);
        if (!$result) return array('status' => 'failed', 'message' => 'sign error');
        return $result;
    }

    public function getReturnData($get)
    {
        $params = array(
            'out_trade_no' => $get['out_trade_no'],
            'trade_no' => $get['trade_no'],
            'pay_method' => $this->payMethod,
            'total_fee' => $get['total_fee'],
            'app_key' => $get['app_id'],
            'uid' => $get['account'],
        );
        return $params;
    }
}
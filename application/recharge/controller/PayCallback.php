<?php

namespace app\recharge\controller;

use app\recharge\service\SyncHandler;
use app\recharge\service\ThirdTrade;
use bxkj_common\HttpClient;
use bxkj_common\RedisClient;
use bxkj_module\service\Service;
use think\Db;
use think\facade\Request;

class PayCallback extends Controller
{
    protected $pay;
    protected $errorMsg = 'handler error';

    public function success_return($pay_method)
    {
        $this->pay = new ThirdTrade();
        $driver = $this->pay->setPayMethod($pay_method);
        if (!$driver) exit('unsupported payment method');
        $get = Request::get();
        unset($get['pay_method']);
        $isTest = $this->isTest($get);
        $result = $isTest ? true : $this->pay->checkReturn($get);
        if ($result !== true) return $this->showReturnError($result['message'] ? $result['message'] : '支付参数错误');
        $params = $this->pay->getReturnData($get);
        return $this->payReturn($pay_method, $params);
    }

    //支付后返回商家页面
    protected function payReturn($payMethod, $data)
    {
        $tradeNo = $data['out_trade_no'];
        $trade = $this->verifyTrade($payMethod, $tradeNo, $data, true);
        if (!$trade) return $this->showReturnError('支付订单错误');
        $params['rel_no'] = $trade['rel_no'];
        $params['rel_type'] = $trade['rel_type'];
        $params['trade_no'] = $trade['trade_no'];
        $params['user_id'] = $trade['user_id'];
        $params['third_trade_no'] = $data['trade_no'];
        $params['third_app_key'] = $data['app_key'];
        if (!empty($data['uid'])) {
            $params['third_user_id'] = $data['uid'];
        }
        $params['pay_method'] = $data['pay_method'];
        $params['total_fee'] = $trade['total_fee'];
        $params['create_time'] = $trade['create_time'];
        $params['extra_data'] = $trade['extra_data'];
        $params['sign_time'] = time();
        $params['nonce_str'] = get_ucode(6, '1a');
        $PAY_VERIFY_TOKEN = config('payment.pay_verify_token');
        $params['sign'] = generate_sign($params, $PAY_VERIFY_TOKEN);
        if (empty($trade['return_url'])) return $this->showReturnError('支付成功，商家未设置返回页');
        list($url, $query) = explode('?', $trade['return_url']);
        $query = http_build_query($params);
        return redirect(rtrim($url, '?') . '?' . $query);
    }

    //验证本地订单
    protected function verifyTrade($payMethod, $tradeNo, $data, $returnMode = false)
    {
        $trade = Db::name('third_trade')->where(array('trade_no' => $tradeNo))->find();
        if (empty($trade)) return false;
        //应用ID不一致
        if ($trade['third_app_key'] != $data['app_key']) return false;
        if ($trade['pay_method'] != $payMethod) return false;
        if (!$returnMode) {
            if ($trade['pay_status'] == '1') return false;
        }
        //支付金额不一致
        if (bccomp($trade['total_fee'], $data['total_fee']) !== 0) return false;
        return $trade;
    }

    public function success_notify($pay_method)
    {
        $this->pay = new ThirdTrade();
        $driver = $this->pay->setPayMethod($pay_method);
        if (!$driver) exit('unsupported payment method');
        $post = Request::post();
        // bxkj_console([$pay_method, $post]);
        $isTest = $this->isTest($post);//测试模式
        $result = $isTest ? true : $this->pay->checkNotify($post);
        //验证失败
        if ($result !== true) {
            $this->pay->responseNotify($result);
            exit();
        }
        $notifyData = $this->pay->getNotifyData($post);//获取标准的通知参数
        $params = $notifyData['params'] ? $notifyData['params'] : array();
    
        $res = false;
        if (strtoupper($params['trade_status']) == 'TRADE_SUCCESS') {
            $res = $this->paySuccess($pay_method, $notifyData);
        } else if (strtoupper($params['trade_status']) == 'TRADE_FINISHED') {
            $res = $this->payFinished($pay_method, $notifyData);
        } else {
            $this->pay->responseNotify(array(
                'status' => 'failed',
                'message' => 'trade status is wrong'
            ));
            exit();
        }
        if (!$res) {
            $this->pay->responseNotify(array(
                'status' => 'failed',
                'message' => $params['trade_status'].':'.$this->errorMsg
            ));
            exit();
        }
        $this->pay->responseNotify(array('status' => 'success', 'message' => 'OK'));
    }

    //支付成功
    protected function paySuccess($payMethod, $notifyData)
    {
        $raw = $notifyData['raw'];//原始参数
        $params = $notifyData['params'];//标准参数
        $tradeNo = $params['out_trade_no'];
        Service::startTrans();
        $trade = $this->verifyTrade($payMethod, $tradeNo, $params, false);
        if (!$trade) return false;
        $syncHandler = new SyncHandler();
        $funName = parse_name($trade['rel_type'], 1, false) . 'Handler';
        $has = method_exists($syncHandler, $funName);
        $updateData['pay_result'] = json_encode($raw);
        $updateData['pay_status'] = '1';
        $updateData['trade_status'] = strtoupper($params['trade_status']);
        $updateData['pay_time'] = time();
        $updateData['third_trade_no'] = $params['trade_no'];
        if (isset($params['uid'])) {
            $updateData['third_user_id'] = $params['uid'];
        }
        $num = Db::name('third_trade')->where(array('trade_no' => $tradeNo))->update($updateData);
        if (!$num) {
            Service::rollback();
            return false;
        }
        $redis = RedisClient::getInstance();
        $redis->zAdd("pay_check:{$trade['rel_type']}", time(), $trade['trade_no'] . ',' . $trade['rel_no']);
        $trade = array_merge($trade, $updateData);
        if ($has) {
            $res = call_user_func_array([$syncHandler, $funName], [$trade]);
            if (!$res) {
                Service::rollback();
                return false;
            }
        } else {
            $this->notify($trade);
        }
        Service::commit();
        return true;
    }

    //订单完成
    protected function payFinished($payMethod, $notifyData)
    {
        $raw = $notifyData['raw'];//原始参数
        $params = $notifyData['params'];//标准参数
        $tradeNo = $params['out_trade_no'];
        $trade = Db::name('third_trade')->where(array('trade_no' => $tradeNo, 'pay_status' => '1', 'pay_method' => $payMethod))->find();
        if ($trade && $trade['trade_status'] != 'TRADE_FINISHED') {
            $updateData['trade_status'] = strtoupper($params['trade_status']);
            $updateData['finish_result'] = json_encode($raw);
            $updateData['finish_time'] = time();
            $num = Db::name('third_trade')->where(array('trade_no' => $tradeNo))->update($updateData);
            if (!$num) return false;
        }
        return true;
    }

    //通知内部业务逻辑
    protected function notify($trade)
    {
        if (empty($trade['notify_url'])) return false;
        $noticeRes = $this->notifyNext($trade['notify_url'], $trade);
        $where = array('id' => $trade['id']);
        if ($noticeRes) {
            Db::name('third_trade')->where($where)->update(array(
                'notify_time' => time(),
                'notify_status' => '1',
                'notify_num' => 1
            ));
        } else {
            //五分钟后再次通知
            Db::name('third_trade')->where($where)->update(array('notify_num' => 1));
            $this->addTimer(0, $trade);
        }
    }

    private function notifyNext($notifyUrl, $trade)
    {
        $curl = new HttpClient();
        $data['rel_no'] = $trade['rel_no'];
        $data['rel_type'] = $trade['rel_type'];
        $data['trade_no'] = $trade['trade_no'];
        $data['user_id'] = $trade['user_id'];
        $data['third_trade_no'] = $trade['third_trade_no'];
        $data['third_user_id'] = $trade['third_user_id'];
        $data['third_app_key'] = $trade['third_app_key'];
        $data['pay_method'] = $trade['pay_method'];
        $data['total_fee'] = $trade['total_fee'];
        $data['create_time'] = $trade['create_time'];
        $data['extra_data'] = $trade['extra_data'] ? $trade['extra_data'] : '';
        $PAY_VERIFY_TOKEN = config('payment.pay_verify_token');
        $data['sign_time'] = time();
        $data['nonce_str'] = get_ucode(6, '1a');
        $data['sign'] = generate_sign($data, $PAY_VERIFY_TOKEN);
        $result = $curl->post($notifyUrl, $data, 20)->getData();
        if ($result) {
            if (strtoupper(trim($result)) === 'SUCCESS') return true;
            bxkj_console('notify:' . $result);
        } else {
            bxkj_console('notify:' . ((string)$curl->getReqError()));
        }
        return false;
    }

    private function addTimer($num, $trade)
    {
        /*$time = time();
        $later = ($num > 0 ? 0 : 300) + ($num * 3600);
        $timerData = array(
            'trade_no' => $trade['trade_no'],
            'nonce_str' => get_ucode(6, '1a'),
            'time' => $time
        );
        $timerData['sign'] = generate_sign($timerData, 'CHECK_NOTIFY@658PAYKDB');
        $timerManager->add($time + $later, 'pay_check_notify', $timerData, sha1('pay_check_notify_' . $trade['trade_no']));*/
    }

    //没有通知成功的再次通知
    public function check_notify()
    {
        $tradeNo = Request::post('trade_no');
        $sign = Request::post('sign');
        $params = array(
            'trade_no' => $tradeNo,
            'nonce_str' => Request::post('nonce_str'),
            'time' => Request::post('time')
        );
        if (!is_sign($sign, $params, 'CHECK_NOTIFY@658PAYKDB')) exit('sign error');
        $trade = Db::name('third_trade')->where(array(
            'trade_no' => $tradeNo,
            'pay_status' => '1',
            'notify_status' => '0'
        ))->find();
        if (!$trade) exit('order does not exist');
        if (empty($trade['notify_url'])) exit('notify_url is empty');
        $num = (int)$trade['notify_num'];
        $noticeRes = $this->notifyNext($trade['notify_url'], $trade);
        $where = array('id' => $trade['id']);
        if ($noticeRes) {
            Db::name('third_trade')->where($where)->update(array(
                'notify_time' => time(),
                'notify_status' => '1',
                'notify_num' => $num + 1
            ));
            exit('success');
        } else {
            Db::name('third_trade')->where($where)->update(array('notify_num' => $num + 1));
            if ($num < 3) {
                $this->addTimer($num, $trade);
                exit('failed');
            } else {
                exit('over');
            }
        }
    }

    //支付返回页面错误
    protected function showReturnError($message)
    {
        $this->assign('title', config('app.product_info.name').'支付提示');
        $this->assign('site_url', 'http://www.ihuanyu.vip');
        $this->assign('company_name', config('app.product_info.name').'-首页');
        $this->assign('message', $message);
        return $this->fetch('return');
    }

    //是否是测试模式
    private function isTest(&$post)
    {
        if ($post['pay_test'] != '1') return false;
        $isTest = sha1($post['pay_test'] . $post['test_code'] . $post['test_time'] . 'Xly@TestM3') === $post['test_sign'];
        if (!$isTest) return false;
        if (time() > $post['test_time'] + 300) return false;
        unset($post['pay_test'], $post['test_code'], $post['test_time'], $post['test_sign']);
        return true;
    }
}
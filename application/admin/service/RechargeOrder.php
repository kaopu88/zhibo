<?php

namespace app\admin\service;

use bxkj_common\ClientInfo;
use bxkj_common\DateTools;
use bxkj_module\service\ExpLevel;
use bxkj_module\service\Service;
use bxkj_common\CoreSdk;
use think\Db;

class RechargeOrder extends Service
{
    //通过后台审核记录创建充值订单
    public function createByLog($logNo)
    {
        if (is_array($logNo)) {
            $log = $logNo;
            $logNo = $log['order_no'];
        } else {
            $log = Db::name('recharge_log')->where(['log_no' => $logNo])->find();
        }
        if (empty($log)) return $this->setError('审核记录不存在');
        if ($log['rec_type'] != 'user') return $this->setError('不支持的审核记录');
        $num = Db::name('recharge_order')->where('log_no', $logNo)->count();
        if ($num > 0) return $this->setError('请勿重复创建');
        $user_id = $log['rec_account'];
        $user = Db::name('user')->where(['user_id' => $user_id])->find();
        if (empty($user)) return $this->setError('用户不存在');
        $now = time();
        $data['order_no'] = get_order_no('recharge');
        $data['bean_id'] = 0;
        $data['bean_num'] = $log['bean'];
        $data['log_no'] = $logNo;
        $data['name'] = APP_BEAN_NAME;
        $data['price'] = $log['total_fee'];
        $data['quantity'] = 1;
        $data['total_fee'] = round($data['quantity'] * $data['price'], 2);
        $data['apple_id'] = '';
        $data['user_id'] = $user_id;
        $data['pay_method'] = '';
        $data['pay_status'] = '0';
        $data['client_ip'] = $log['client_ip'];
        $data['app_v'] = $log['app_v'] ? $log['app_v'] : '';
        $data['isvirtual'] = $user['isvirtual'];
        $data['create_time'] = $now;
        $data['year'] = date('Y', $now);
        $data['month'] = date('Ym', $now);
        $data['day'] = date('Ymd', $now);
        $data['fnum'] = DateTools::getFortNum($now);
        $id = Db::name('recharge_order')->insertGetId($data);
        if (!$id) return $this->setError('创建充值订单失败');
        $data['id'] = $id;
        return [
            'id' => $data['id'],
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
        $num2 = Db::name('recharge_order')->where($where2)->count();
        if ($num2 > 0) return $this->setError('第三方订单号已存在');
        $coreSdk = new CoreSdk();
        $beanNum = (int)$order['bean_num'];
        ClientInfo::refresh([
            'client_ip' => $order['client_ip'],
            'app_v' => $order['app_v'],
        ]);
        $incRes = $coreSdk->incBean(array(
            'user_id' => $order['user_id'],
            'total' => $beanNum * $order['quantity'],
            'trade_type' => 'recharge',
            'trade_no' => $order['order_no'],
            'client_seri' => ClientInfo::encode()
        ));
        if (!$incRes) return $this->setError($coreSdk->getError());
        $updateData['pay_status'] = '1';
        $updateData['pay_time'] = time();
        $updateData['pay_method'] = $thirdData['pay_method'];
        $updateData['third_trade_no'] = $thirdTradeNo;
        $num = Db::name('recharge_order')->where(array('order_no' => $orderNo))->update($updateData);
        if (!$num) return $this->setError('支付失败');
        return true;
    }

    public function getTotal($get)
    {
        $this->db = Db::name('recharge_order');
        $this->setWhere($get);
        $count = $this->db->count();
        return (int)$count;
    }

    public function getList($get, $offset = 0, $length = 20)
    {
        $this->db = Db::name('recharge_order');
        $this->setWhere($get)->setOrder($get);
        $result = $this->db->limit($offset, $length)->select();
        foreach ($result as &$item) {
            $levelInfo = ExpLevel::getLevelInfo($item['level']);
            $item = array_merge($item, $levelInfo ? $levelInfo : []);
            $item['total_bean'] = $item['bean_num'] * $item['quantity'];
        }
        return $result;
    }

    protected function setOrder($get)
    {
        $order = array();
        if (empty($get['sort'])) {
            $order['recharge.create_time'] = 'DESC';
        }
        $this->db->order($order);
        return $this;
    }

    protected function setWhere($get)
    {
        $where = [];
        $this->db->alias('recharge');
        $this->db->join('__USER__ user', 'recharge.user_id=user.user_id', 'LEFT');
        $this->db->field('user.user_id,user.nickname,user.avatar,user.remark_name,user.level,user.phone');
        $this->db->field('recharge.id,recharge.order_no,recharge.bean_id,recharge.apple_id,recharge.bean_num,recharge.price,recharge.name,recharge.user_id,recharge.pay_method,recharge.third_trade_no,recharge.pay_status,recharge.pay_time,recharge.client_ip,recharge.app_v,recharge.log_no,recharge.create_time,recharge.quantity,recharge.total_fee,recharge.isvirtual');
        if ($get['pay_method'] != '') {
            $where[] = ['recharge.pay_method', '=', $get['pay_method']];
        }
        if ($get['pay_status'] != '') {
            $where[] = ['recharge.pay_status', '=', $get['pay_status']];
        }
        if ($get['user_id'] != '') {
            $where[] = ['recharge.user_id', '=', $get['user_id']];
        }
        if ($get['isvirtual'] != '') {
            $where[] = ['recharge.isvirtual', '=', $get['isvirtual']];
        }
        if (!empty($get['start_time']) && !empty($get['end_time']))
        {
            $start_time = str_replace('-', '', $get['start_time']);
            $end_time = str_replace('-', '', $get['end_time']);
            $where[] = ['recharge.day', 'between', [$start_time, $end_time]];
        }
        $this->db->setKeywords(trim($get['keyword']), 'phone user.phone', 'number user.user_id', 'number user.phone,user.nickname');
        $this->db->setKeywords(trim($get['order_no']), '', '', 'number recharge.order_no');
        $this->db->setKeywords(trim($get['third_trade_no']), '', '', 'recharge.third_trade_no');
        $this->db->where($where);
        return $this;
    }
    
    public function getSummary($get)
    {
        $db = Db::name('recharge_order');
        $db->alias('recharge');
        $db->join('__USER__ user', 'recharge.user_id=user.user_id', 'LEFT');
        $where[] = ['recharge.pay_status', '=', '1'];
        $where[] = ['recharge.isvirtual', '=', '0'];
        if (!empty($get['pay_method'])) $where[] = ['recharge.pay_method', '=', $get['pay_method']];
        if (!empty($get['user_id'])) $where[] = ['recharge.user_id', '=', $get['user_id']];
        if (!empty($get['start_time']) && !empty($get['end_time']))
        {
            $start_time = str_replace('-', '', $get['start_time']);
            $end_time = str_replace('-', '', $get['end_time']);
            $where[] = ['recharge.day', 'between', [$start_time, $end_time]];
        }
        $db->setKeywords(trim($get['keyword']), 'phone user.phone', 'number user.user_id', 'number user.phone,user.nickname');
        $db->setKeywords(trim($get['order_no']), '', '', 'number recharge.order_no');
      
        $result = $db->where($where)->field('sum(recharge.price) price_total, recharge.pay_method')->group('recharge.pay_method')->select();

        if (!empty($result))
        {
            $result = array_column($result, 'price_total', 'pay_method');
            $result['summary'] = array_sum($result);
        }
        return $result;
    }
}
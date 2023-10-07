<?php

namespace app\admin\service;

use bxkj_common\RedisClient;
use bxkj_module\service\Service;
use think\Db;

class Recharge extends Service
{

    public function getSum($unit, $num, $pay_method)
    {
        $where = array('isvirtual'=>'0', 'pay_status'=>'1');
        if ($pay_method){
            $prefix = "custom:all:recharge:".$pay_method;
            $where['pay_method'] = $pay_method;
        }else{
            $prefix = "custom:all:recharge";
        }
        $sum = self::getCache($prefix, $unit, $num);
        if (!isset($sum)) {
            $db = Db::name('recharge_order');
            $this->setTimeRange($db, $unit, $num);
            $db->where($where);
            $sum = $db->sum('total_fee');
            self::setCache($prefix, $unit, $num, $sum);
        }
        return $sum;
    }

    public static function setCache($prefix, $unit, $num, $value)
    {
        $redis = RedisClient::getInstance();
        $num = trim($num ? str_replace('-', '', $num) : '');
        $key = "{$prefix}:{$unit}:{$num}";
        $now = date('Ymd',time());
        if ($now == $num){
            return $redis->set($key, (string)$value, 300);
        }else{
            return $redis->set($key, (string)$value);
        }
    }

    public static function getCache($prefix, $unit, $num)
    {
        $num = trim($num ? str_replace('-', '', $num) : '');
        $redis = RedisClient::getInstance();
        $key = "{$prefix}:{$unit}:{$num}";
        $now = date('Ymd',time());
        if ($now == $num){
            // $redis->delete($key);
            $redis->del($key);
        }
        $value = $redis->get($key);
        if ($value === false) return null;
        return $value;
    }

    public static function setTimeRange(&$db, $unit, $num)
    {
        $num = $num ? str_replace('-', '', $num) : '';
        if (!empty($num)) {
            if ($unit == 'm') {
                $db->where('month', $num);
            } else if ($unit == 'f') {
                $db->where('fnum', $num);
            } else if ($unit == 'd') {
                $db->where('day', $num);
            } else if ($unit == 'w') {
                $db->where('week', $num);
            }
        }
    }


}

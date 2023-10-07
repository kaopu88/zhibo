<?php

namespace app\admin\service;

use bxkj_common\RedisClient;
use bxkj_module\service\Service;
use think\Db;

class Kpi extends Service
{

    public function getSum($unit, $num, $rel_type)
    {
        $where = array();
        if ($rel_type){
            $prefix = "custom:all:cons:".$rel_type;
            $where['rel_type'] = $rel_type;
        }else{
            $prefix = "custom:all:cons";
        }
        $sum = self::getCache($prefix, $unit, $num);
        if (!isset($sum)) {
            $db = Db::name('kpi_cons');
            $this->setTimeRange($db, $unit, $num);
            if ($where) $db->where($where);
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


<?php

if (!function_exists('number_format'))
{
    //数字格式化
    function number_format($num){
        if($num<10000){
            $num = (string) $num;
        }else if($num<1000000){
            $num = round($num/10000,2).'w';
        }else if($num<100000000){
            $num = round($num/10000,1).'w';
        }else if($num<10000000000){
            $num = round($num/100000000,2).'e';
        }else{
            $num = round($num/100000000,1).'e';
        }
        return $num;
    }
}



if (!function_exists('week'))
{
    /**
     * 返回本周开始和结束的时间戳
     *
     * @return array
     */
    function week()
    {
        list($y, $m, $d, $w) = explode('-', date('Y-m-d-w'));
        if($w == 0) $w = 7; //修正周日的问题
        return [
            mktime(0, 0, 0, $m, $d - $w + 1, $y), mktime(23, 59, 59, $m, $d - $w + 7, $y)
        ];
    }
}



if (!function_exists('parse_name'))
{
    /**
     * 字符串命名风格转换
     * type 0 将Java风格转换为C的风格 1 将C风格转换为Java的风格
     * @param string $name 字符串
     * @param integer $type 转换类型
     * @param bool $ucfirst 首字母是否大写（驼峰规则）
     * @return string
     */
    function parse_name($name, $type = 0, $ucfirst = true)
    {
        if ($type) {
            $name = preg_replace_callback('/_([a-zA-Z])/', function ($match) {
                return strtoupper($match[1]);
            }, $name);

            return $ucfirst ? ucfirst($name) : lcfirst($name);
        } else {
            return strtolower(trim(preg_replace("/[A-Z]/", "_\\0", $name), "_"));
        }
    }
}



if (!function_exists('get_activity_config'))
{
    /**
     * 字符串命名风格转换
     * type 0 将Java风格转换为C的风格 1 将C风格转换为Java的风格
     * @param string $name 字符串
     * @param integer $type 转换类型
     * @param bool $ucfirst 首字母是否大写（驼峰规则）
     * @return string
     */
    function get_activity_config($name)
    {
        return \app\service\Activity::getActConfig($name);
    }
}



function str_to_fields($str)
{
    $str = str_replace(array("\r\n", "\r", "\n"), "", $str);
    $arr = explode(',', trim($str, ','));
    foreach ($arr as &$value) {
        $value = trim($value);
    }
    return $arr;
}

function redis_lock($lockName, $timeout = null)
{
    global $redis;
    $key = "lock:{$lockName}";
    $totalSleep = 0;
    while (!$redis->setnx($key, time())) {
        if (isset($timeout)) {
            if ($totalSleep > 1000000 * $timeout)
                throw new Exception("wait unlock {$lockName} timeout", 1);
        }
        usleep(500);//休眠0.5毫秒
        $totalSleep += 500;
    }
}

function redis_unlock($lockName)
{
    global $redis;
    $key = "lock:{$lockName}";
    $redis->del($key);
}

function get_order_no($type, $time = null)
{
    global $redis;
    $map = array(
        'vip' => '01',
        'third' => '02',
        'recharge' => '03',
        'log' => '04',
        'cash' => '05',
        'pay_per_view' => '06',
        'gift' => '07',
        'barrage' => '08',
        'live' => '09',
        'raise' => '10',
        'recharge_log' => '11',
        'liudanji' => '12',
        'props' => '13',
        'cover_star_vote' => '14',
        'taoke_shop'      => '15',
        'mall'            => '16',
        'agent_cash'      => '17',
        'week_star'       => '18',
        'red_packet'      => '19',
        'distribute'      => '20'
    );
    $time = isset($time) ? $time : time();
    $date = date('ymd', $time);
    $newTime = mktime(0, 0, 0, 11, 27, 2018);
    $isNew = $time >= $newTime ? true : false;
    $typeNum = isset($map[$type]) ? $map[$type] : '00';
    $lockName = 'order_no:' . $date . ($isNew ? ":{$typeNum}" : "");
    redis_lock($lockName, 10);
    $key = 'order_no:autoinc' . ($isNew ? ":{$typeNum}" : "");
    $autoinc = $redis->zScore($key, $date);
    $autoinc = $autoinc ? $autoinc : 1000;
    $rand = rand(1, 40);//最少得有1个
    $autoinc += $rand;
    $redis->zAdd($key, $autoinc, $date);
    redis_unlock($lockName);
    return $date . $typeNum . str_pad($autoinc, 9, '0', STR_PAD_LEFT);
}

function bg_get_config($name)
{
    global $config;

    if (isset($config[$name])) return $config[$name];

    return '';
}


function duration_format($number)
{
    if (empty($number)) {
        $time = '00:00';
    } else if ($number < 3600) {
        $time = gmdate('i:s', $number);
    } else if ($number < 86400) {
        $time = gmdate('h:i:s', $number);
    } else {
        $time = $number;
    }

    return (string)$time;
}
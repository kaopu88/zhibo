<?php

use app\admin\service\SysConfig;
use app\api\service\Follow as FollowModel;
use app\friend\service\FriendCircleCircleFollow;
use app\friend\service\FriendCircleMessage;
use app\friend\service\FriendCircleMessageFilter;
use app\friend\service\FriendCircleTopic;
use bxkj_common\RedisClient;
use bxkj_module\service\Bean;
use bxkj_module\service\Cash;
use bxkj_module\service\Millet;
use think\Db;
use think\facade\Env;
use think\facade\Request;

define('RUNTIME_ENVIROMENT', Env::get('RUN_ENV'));

function myprint($arr)
{
    echo '<pre>';
    print_r($arr);
}

function get_sensitive_config($type)
{
    if (empty($type)) return '';
    $type      = strtolower(trim($type)) . '_sensitive_config';
    $allConfig = config('app.sensitive');
    if (empty($allConfig[$type])) return $allConfig['default_sensitive_config'];
    return array_merge($allConfig['default_sensitive_config'], $allConfig[$type]);
}

function get_live_config()
{
    $all_live_config            = config('app.live_setting');
    $rtc_config = !empty(config('app.vod.trtc_config')) ? config('app.vod.trtc_config') : [];
    $config                     = array_merge($all_live_config['message_server'], $all_live_config['game_server'], $all_live_config['platform_config'], $rtc_config);

    $config['service_platform'] = $all_live_config['platform'];
    $config['chat_server']      = $all_live_config['message_server']['host'];
    $config['chat_server_port'] = $all_live_config['message_server']['port'];
    $config['game_server']      = $all_live_config['game_server']['host'];
    $config['game_server_port'] = $all_live_config['game_server']['port'];
    $config['barrage']          = $all_live_config['barrage_fee'];
    $config['live_room_name']   = $all_live_config['live_room_name'];

    return $config;
}

function getActConfig($act_name)
{
    $redis  = \bxkj_common\RedisClient::getInstance();
    $update = $redis->get('cache:update_activity');
    $update && $redis->del('cache:update_activity');
    $salesActRes = $redis->exists('cache:activity_config');
    if (empty($salesActRes)) {
        $arr         = [];
        $salesActRes = \think\Db::name('activity')->where('status', 1)->field('id, mark, name, link, icon, rule, start_time, end_time, create_time')->select();
        if (empty($salesActRes)) return [];
        $time = time();
        foreach ($salesActRes as $key => &$val) {
            if (!empty($val['start_time']) || !empty($val['end_time'])) {
                //未开始
                if ($time < $val['start_time']) continue;
                //结束
                if ($time > $val['end_time']) {
                    \think\Db::name('activity')->where('id', $val['id'])->update(['status' => 0]);
                    continue;
                }
            }
            $mark        = parse_name($val['mark'], 1);
            $val['rule'] = json_decode($val['rule'], true);
            $arr[$mark]  = $val;
        }
        $redis->set('cache:activity_config', json_encode($arr));
        $redis->expire('cache:activity_config', 4 * 3600);
    }
    $salesActRes = $redis->get('cache:activity_config');
    $salesActRes = json_decode($salesActRes, true);
    $act_name    = parse_name($act_name, 1);
    return array_key_exists($act_name, $salesActRes) ? $salesActRes[$act_name] : [];
}

//生日转换成年龄
function birthday_to_age($birthday)
{
    $byear  = date('Y', $birthday);
    $bmonth = date('m', $birthday);
    $bday   = date('d', $birthday);
    //格式化当前时间年月日
    $tyear  = date('Y');
    $tmonth = date('m');
    $tday   = date('d');
    //开始计算年龄
    $age = $tyear - $byear;
    if ($bmonth > $tmonth || $bmonth == $tmonth && $bday > $tday) {
        $age--;
    }
    return $age;
}

//将时间戳解释成1天14时23分...
function time_str($time, $minUnit = 's')
{
    if ($minUnit == 'd' && $time < (24 * 3600)) {
        return '不足1天';
    }
    $str = '';
    $tmp = $time;
    if (in_array($minUnit, array('s', 'i', 'h', 'd', 'm', 'y'))) {
        $y = floor($tmp / (365 * 24 * 3600));
        if ($y > 0) {
            $str .= "{$y}年";
            $tmp = $tmp - $y * (365 * 24 * 3600);
        }
    }
    if (in_array($minUnit, array('s', 'i', 'h', 'd', 'm'))) {
        $m = floor($tmp / (30 * 24 * 3600));
        if ($m > 0) {
            $str .= $m . '月';
            $tmp = $tmp - $m * (30 * 24 * 3600);
        } elseif ($y > 0) {
            $str .= '零';
        }
    }
    if (in_array($minUnit, array('s', 'i', 'h', 'd'))) {
        $d = floor($tmp / (24 * 3600));
        if ($d > 0) {
            $str .= $d . '天';
            $tmp = $tmp - $d * (24 * 3600);
        } elseif ($m > 0) {
            $str .= '零';
        }
    }
    if (in_array($minUnit, array('s', 'i', 'h'))) {
        $h = floor($tmp / (3600));
        if ($h > 0) {
            $str .= $h . '时';
            $tmp = $tmp - $h * (3600);
        } elseif ($d > 0) {
            $str .= '零';
        }
    }
    if (in_array($minUnit, array('s', 'i'))) {
        $i = floor($tmp / (60));
        if ($i > 0) {
            $str .= $i . '分';
            $tmp = $tmp - $i * (60);
        } elseif ($h > 0) {
            $str .= '零';
        }
    }
    if (in_array($minUnit, array('s'))) {
        if ($tmp > 0) {
            $str .= $tmp . '秒';
        }
    }
    return preg_replace('/零$/', '', $str);
}

//以前 如 1小时前
function time_before($time, $suffix = "", $num = null)
{
    if (!isset($time) && !isset($num)) return "";
    $num = isset($num) ? $num : time() - $time;
    $num = $num < 0 ? -$num : $num;
    $val = '';
    if ($num < 60) $val = $num . '秒';
    if ($num / 60 < 60 && $num >= 60) $val = floor($num / 60) . '分钟';
    if ($num / 3600 < 24 && $num / 60 >= 60) $val = floor($num / 3600) . '小时';
    if ($num / 86400 < 30 && $num / 3600 >= 24) $val = floor($num / 86400) . '天';
    if ($num / 2592000 < 12 && $num / 86400 >= 30) $val = floor($num / 2592000) . '月';
    if ($num > 31536000 && $num / 2592000 >= 12) $val = floor($num / 31536000) . '年';
    return $val . $suffix;
}

//判断一个时间戳是否在今天以内
function is_in_today($time)
{
    $time = (int)$time;
    return ($time >= mktime(0, 0, 0) && $time <= mktime(23, 59, 59));
}

//判断是否是昨天的日期
function is_in_yestoday($time)
{
    return date('Y-m-d', strtotime($time)) == date('Y-m-d', strtotime('-1 day'));
}

function time_detail($time, $suffix = "", $format = 'H:i')
{
    if (!isset($time)) return "";
    $now = time();
    if ($time >= strtotime('today')) {
        if ($time > mktime(12, 0, 0, date("m"), date("d"), date("Y"))) {
            $suffix = '下午';
        } else {
            $suffix = '早上';
        }
    } elseif ($time >= strtotime('yesterday') && $time < strtotime('today')) {
        $suffix = '昨天';
    } elseif ($time >= mktime(0, 0, 0, 1, 1, date("Y", $now)) && $time < strtotime('yesterday')) {
        $format = 'm-d';
    } elseif ($time < mktime(0, 0, 0, 1, 1, date("Y", $now))) {
        $format = 'Y-m-d';
    }
    return $suffix . date($format, $time);
}

//时间戳转为字符串,时间戳，时间戳未设置时显示的文字
function time_format($time, $default = "", $format = "com")
{
    if (!isset($time)) return $default;
    if ($format == 'com') {
        $format = 'Y-m-d H:i';
    } elseif ($format == 'date') {
        $format = 'Y-m-d';
    } elseif ($format == 'time') {
        $format = 'H:i:s';
    } elseif ($format == 'datetime') {
        $format = 'Y-m-d H:i:s';
    }
    return date($format, $time);
}

function number_format2($numer)
{
    if ($numer >= 10000000) {
        $real  = sprintf("%.2f", $numer / 100000000);
        $numer = $real . 'e';
    }
    if ($numer >= 10000) {
        $real  = sprintf("%.1f", $numer / 10000);
        $numer = $real . 'w';
    }
    $numer = (string)$numer;
    return $numer;
}

//时长格式化
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
    /* switch (true) {
         case $number < 60 :
             $number < 10 && $number = '0' . $number;
             $time = "00:{$number}";
             break;
         case $number < 3600 :
             $time = number_format($number / 60, 2, ':', '');
             $lengh = strlen($time);
             $lengh < 5 && $time = "0{$time}";
             break;
         case $number < 86400 :
             $time = number_format($number / 3600, 2, ':', ':');
             break;
     }*/
    return (string)$time;
}

function durationstrtotime($str)
{
    if (empty($str)) return 0;
    $arr = explode(':', trim($str));
    if (count($arr) == 1) {
        return (int)$arr[0];
    } else if (count($arr) == 2) {
        return ($arr[0] * 60) + $arr[1];
    } else {
        return ($arr[0] * 3600) + ($arr[1] * 60) + $arr[2];
    }
}

/***************************************************
 * 枚举
 ***************************************************/
function enum_attr($key, $value, $attr = 'name')
{
    $list = config("enum.{$key}");
    if (is_array($list)) {
        foreach ($list as $item) {
            if ($item['value'] == $value) return $item[$attr];
        }
    }
    return null;
}

function enum_name($value, $key)
{
    return enum_attr($key, $value, 'name');
}

function enum_array($key, $value = null, $field = 'value')
{
    $list = config("enum.{$key}");
    $list = $list ? $list : array();
    if (isset($value)) {
        foreach ($list as $item) {
            if ($item[$field] == $value) {
                return $item;
            }
        }
    } else {
        return $list;
    }
}

function enum_has($key, $value, $field = 'value')
{
    $list = config("enum.{$key}");
    foreach ($list as $item) {
        if ($item[$field] == $value) return true;
    }
    return false;
}

function enum_in($value, $key)
{
    $list = config("enum.{$key}");
    if (empty($list)) return false;
    foreach ($list as $item) {
        if ($item['value'] == $value) return true;
    }
    return false;
}

/***************************************************
 * 字符串
 ***************************************************/
//msubstr方法的简洁方式
function short($str = '', $length = 10)
{
    $suffix = false;
    $total  = mb_strlen($str, "utf-8");
    if ($length >= $total) return $str;
    if ($length > 3) {
        $suffix = true;
        $length -= 3;
    }
    return msubstr($str, 0, $length, 'utf-8', $suffix);
}

//隐藏中间部分 如 187****0874
function str_hide($str, $head = null, $end = null, $hide = '*', $abb = false)
{
    $center  = $total = mb_strlen($str, 'utf-8');
    $center  -= (isset($head) ? $head : 0) + (isset($end) ? $end : 0);
    $center2 = $center;
    if ($abb && $center2 > 3) $center2 = 3;
    $hide = str_pad($hide, $center2, $hide);
    $pat  = '/^' . (isset($head) ? '([\w-]{' . $head . '})' : '') . '[\w-]{' . $center . '}' . (isset($end) ? '([\w-]{' . $end . '})' : '') . '$/';
    $e    = isset($head) ? 2 : 1;
    return preg_replace($pat, (isset($head) ? '$1' : '') . $hide . (isset($end) ? '$' . $e : ''), $str);
}

/**
 * 字符串截取，支持中文和其他编码
 * @static
 * @access public
 * @param string $str 需要转换的字符串
 * @param string $start 开始位置
 * @param string $length 截取长度
 * @param string $charset 编码格式
 * @param string $suffix 截断显示字符
 * @return string
 */
function msubstr($str, $start = 0, $length, $charset = "utf-8", $suffix = true)
{
    if (function_exists("mb_substr"))
        $slice = mb_substr($str, $start, $length, $charset);
    elseif (function_exists('iconv_substr')) {
        $slice = iconv_substr($str, $start, $length, $charset);
        if (false === $slice) {
            $slice = '';
        }
    } else {
        $re['utf-8']  = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
        $re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
        $re['gbk']    = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
        $re['big5']   = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
        preg_match_all($re[$charset], $str, $match);
        $slice = join("", array_slice($match[0], $start, $length));
    }
    return $suffix ? $slice . '...' : $slice;
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

/**
 * 去除代码中的空白和注释
 * @param string $content 代码内容
 * @return string
 */
function strip_whitespace($content)
{
    $stripStr = '';
    //分析php源码
    $tokens     = token_get_all($content);
    $last_space = false;
    for ($i = 0, $j = count($tokens); $i < $j; $i++) {
        if (is_string($tokens[$i])) {
            $last_space = false;
            $stripStr   .= $tokens[$i];
        } else {
            switch ($tokens[$i][0]) {
                //过滤各种PHP注释
                case T_COMMENT:
                case T_DOC_COMMENT:
                    break;
                //过滤空格
                case T_WHITESPACE:
                    if (!$last_space) {
                        $stripStr   .= ' ';
                        $last_space = true;
                    }
                    break;
                case T_START_HEREDOC:
                    $stripStr .= "<<<THINK\n";
                    break;
                case T_END_HEREDOC:
                    $stripStr .= "THINK;\n";
                    for ($k = $i + 1; $k < $j; $k++) {
                        if (is_string($tokens[$k]) && $tokens[$k] == ';') {
                            $i = $k;
                            break;
                        } else if ($tokens[$k][0] == T_CLOSE_TAG) {
                            break;
                        }
                    }
                    break;
                default:
                    $last_space = false;
                    $stripStr   .= $tokens[$i][1];
            }
        }
    }
    return $stripStr;
}

/***************************************************
 * 解析、转换与格式化
 ***************************************************/
/**
 * 格式化字节大小
 * @param number $size 字节数
 * @param string $delimiter 数字和单位分隔符
 * @return string            格式化后的带单位的大小
 */
function format_bytes($size, $delimiter = '')
{
    $units = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');
    for ($i = 0; $size >= 1024 && $i < 5; $i++) $size /= 1024;
    return round($size, 2) . $delimiter . $units[$i];
}

function object_to_array($array)
{
    if (is_object($array)) {
        $array = (array)$array;
    }
    if (is_array($array)) {
        foreach ($array as $key => $value) {
            $array[$key] = object_to_array($value);
        }
    }
    return $array;
}

function xml_to_array($xml)
{
    libxml_disable_entity_loader(true);
    $values = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
    return $values;
}

//将索引数组转为关联数组
function array_to_access($arr, $key = 'id')
{
    $obj = array();
    for ($i = 0; $i < count($arr); $i++) {
        $obj[$arr[$i][$key]] = $arr[$i];
    }
    return $obj;
}

function array_to_xml($arr)
{
    $xml = "<?xml version=\"1.0\" encoding=\"utf-8\"?><xml>";
    foreach ($arr as $key => $val) {
        if (is_numeric($val)) {
            $xml .= "<" . $key . ">" . $val . "</" . $key . ">";
        } else {
            $xml .= "<" . $key . "><![CDATA[" . $val . "]]></" . $key . ">";
        }
    }
    $xml .= "</xml>";
    return $xml;
}

//递归合并数组
function array_merge_recursion($arr1, $arr2)
{
    foreach ($arr2 as $key => $value) {
        if (is_array($arr1[$key]) && is_array($arr2[$key])) {
            $arr1[$key] = array_merge_recursion($arr1[$key], $arr2[$key]);
        } else {
            $arr1[$key] = $value;
        }
    }
    return $arr1;
}

/**
 * @param $name 模板名称或模板{$aa}
 * @param $data array('aa'=>'替换的字符');
 * @return mixed|null|string|string[]
 */
function parse_tpl($name, $data)
{
    $tpls = config('app.text_tpl');
    $tpl  = isset($tpls[$name]) ? $tpls[$name] : $name;
    foreach ($data as $key => $value) {
        $tpl = preg_replace('/\{\$' . $key . '\}/', $value, $tpl);
    }
    $tpl = preg_replace('/\{\$.+\}/U', '', $tpl);
    return $tpl;
}

function parse_headers()
{
}

//解析主机地址
function parse_host($host = null)
{
    $host      = isset($host) ? $host : $_SERVER['HTTP_HOST'];
    $urlSuffix = '';
    preg_match('/\.[^.]{1,}(\.cn)?$/', $host, $meatches);
    if (!empty($meatches)) {
        $urlSuffix = $meatches[0];
        $host      = preg_replace('/' . $urlSuffix . '$/', '', $host);
    }
    $hostArr = $host ? array_reverse(explode('.', $host)) : array();
    $data    = array(
        'suffix' => $urlSuffix,
        'names'  => $hostArr
    );
    return $data;
}

//用户代理分析
function user_agent($userAgent = null)
{
    $tmp    = new \bxkj_common\UserAgent($userAgent);
    $result = $tmp->getInfo();
    return $result;
}

function chinese_to_pinyin($str, $code = "UTF8")
{
    return \bxkj_common\Pinyin::chinese_to_pinyin($str, $code);
}

/***************************************************
 * 生成与验证
 ***************************************************/
//预置regex验证
function validate_regex($value, $param)
{
    if ($param == 'phone' || $param == 'mobile') {
        return validate_phone($value);
    }
    $list  = config('app.regex_pattern');
    $param = !empty($list) && isset($list[$param]) ? $list[$param] : $param;
    return preg_match($param, $value);
}

function validate_regex_pattern($value, $param)
{
    $list  = config('app.regex_pattern');
    $param = !empty($list) && isset($list[$param]) ? $list[$param] : $param;
    return preg_match($param, $value);
}

function bean_to_rmb($value)
{
    return round($value / 10, 2);
}

//验证范围
function validate_range($value, $range, $not = false)
{
    if (is_array($range) || strpos($range, ',') !== false) {
        //取值范围
        list($min, $max) = is_array($range) ? $range : explode(',', $range);
        if ($not) {
            $result = false;
            if ($min !== '' && !is_null($min) && $value < $min) $result = true;
            if ($max !== '' && !is_null($max) && $value > $max) $result = true;
        } else {
            $result = true;
            if ($min !== '' && !is_null($min) && $value < $min) $result = false;
            if ($max !== '' && !is_null($max) && $value > $max) $result = false;
        }
    } else {
        $result = $not ? $value != $range : $value == $range;
    }
    return $result;
}

//验证七牛上传文件地址
function validate_qiniu_url($url, $type, $vars = array())
{
    $filename = basename($url);
    $base     = config('upload.platform_config.base_url');
    $key      = generate_upload_url($filename, $type, $base, $vars);
    //$dirname = substr($key, 0, strripos($key, '/'));
    $key = rtrim(dirname($key), '/') . '/' . $filename;
    return $key == $url;
}

//生成七牛上传文件地址
function generate_upload_url($filename, $config, $base, $vars = array())
{
    if (is_string($config)) {
        $allConfig = config('upload.upload_config');
        $config    = array_merge($allConfig['_default'], isset($allConfig[$config]) ? $allConfig[$config] : array());
    }
    $root           = config('upload.platform_config.root_path');
    $path           = $config['path'];
    $vars['uniqid'] = sha1(uniqid() . get_ucode(6, '1aA'));
    $vars['ext']    = substr($filename, strripos($filename, '.') + 1);
    $vars['name']   = substr($filename, 0, 0 - (strlen($vars['ext']) + 1));
    $key            = parse_tpl($path, $vars);
    return $base . '/' . $root . ltrim($key, '/');
}

//验证手机号（支持国外手机号）
function validate_phone($phone)
{
    if (!preg_match('/^\d{5,16}$/', $phone)) return false;
    return true;
}

//生成签名
function generate_sign($data, $token = null, $field = 'sign')
{
    $token         = isset($token) ? $token : config('app.app_setting.data_token');
    $data['token'] = $token;
    $keys          = array();
    foreach ($data as $key => $value) {
        if ($key != $field) {
            $keys[] = $key;
        }
    }
    sort($keys, SORT_STRING);
    $str = '';
    for ($i = 0; $i < count($keys); $i++) {
        $str .= $keys[$i] . $data[$keys[$i]];
    }
    return sha1($str);
}

function sha1_array($arr)
{
    $index = [];
    $keys  = [];
    foreach ($arr as $key => $value) {
        $arr[$key] = is_array($value) ? sha1_array($value) : $value;
        if (is_int($key)) {
            $index[] = $arr[$key];
        } else {
            $keys[] = $key;
        }
    }
    sort($keys, SORT_STRING);
    //sort($index, SORT_STRING);
    $str = implode(',', $index);
    for ($i = 0; $i < count($keys); $i++) {
        $str .= $keys[$i] . $arr[$keys[$i]];
    }
    return sha1($str);
}

function generate_wx_sign($data, $token = null)
{
    $stringA = '';
    ksort($data);
    foreach ($data as $key => $val) {
        if (!empty($val)) {
            $stringA .= $key . '=' . $val . '&';
        }
    }
    $stringSignTemp = $stringA . "key={$token}";
    return strtoupper(md5($stringSignTemp));
}

//验证签名
function is_sign($sign, $data, $token = null, $field = 'sign')
{
    return $sign == generate_sign($data, $token, $field);
}

/***************************************************
 * 文件导入导出与缓存
 ***************************************************/
//导出csv
function csv_export($data, $filename)
{
    for ($i = 0; $i < count($data); $i++) {
        $tmp = $data[$i];
        for ($mn = 0; $mn < count($tmp); $mn++) {
            $val      = trim(str_replace(',', '，', $tmp[$mn]));
            $tmp[$mn] = (isset($val) && $val !== '') ? $val : '';
        }
        $data[$i] = $tmp;
    }
    $filename = iconv("UTF-8", "gbk", $filename . '.csv');
    header('Content-Type: text/csv; charset=gbk');
    header('Content-Disposition: attachment; filename=' . $filename);
    $output = fopen('php://output', 'w');
    $num    = null;
    foreach ($data as $k => $item) {
        $item = is_array($item) ? $item : explode(',', $item);
        if (!isset($num)) $num = count($item);
        if ($num != count($item)) {
            continue;
        }
        foreach ($item as &$value) {
            $value = iconv("UTF-8", "gbk", $value);
        }
        fputcsv($output, $item);
    }
    return true;
}

//通过文件获取一个GUID
function get_guid($path = '')
{
    $file = fopen($path, "a+");
    if (!$file) {
        return false;
    }
    $uid = 0;
    // 排它性的锁定
    if (flock($file, LOCK_EX)) {
        $uid = fread($file, filesize($path));
        ftruncate($file, 0);
        fwrite($file, (int)$uid + 1);
        flock($file, LOCK_UN);
    }
    fclose($file);
    return $uid;
}

//删除文件夹及其所有文件
function del_dir($dir, $delRoot = true, &$tmp = array())
{
    $dh   = opendir($dir);
    $file = readdir($dh);
    while ($file) {
        if ($file != "." && $file != "..") {
            $fullpath = $dir . "/" . $file;
            is_dir($fullpath) ? del_dir($fullpath, $delRoot, $tmp) : array_push($tmp, array('result' => unlink($fullpath), 'type' => 'file', 'path' => $fullpath));
        }
        $file = readdir($dh);
    }
    closedir($dh);
    if ($delRoot) array_push($tmp, array('result' => rmdir($dir), 'type' => 'dir', 'path' => $dir));
}

/***************************************************
 * 图片与URL
 ***************************************************/
function image_base64_encode($image_file)
{
    $base64_image = '';
    $image_info   = getimagesize($image_file);
    $image_data   = file_get_contents($image_file);
    $base64_image = 'data:' . $image_info['mime'] . ';base64,' . chunk_split(base64_encode($image_data));
    return $base64_image;
}

//生成符合规范的img 地址
function img_url($path, $version = null, $default = null)
{
    if (empty($path)) $path = config('upload.image_defaults.' . $default);
    //按照版本修改路径
    if (!empty($version)) {
        $versionConfig = config('upload.image_versions.' . $version);
        $imageStorer   = config('upload.platform');
        if ($imageStorer == 'aliyun' && !empty($path)) {
            $type = $versionConfig['type'];
            $opts = $versionConfig;
            $src  = $path;
            preg_match('/x\-oss\-process\=([^\&\.]+)/', $src, $matches);
            $param = ($matches && $matches[1]) ? ($matches[1] . ',') : '';
            $param .= ('image/' . $type . ',');
            foreach ($opts as $key => $value) {
                if ($key != 'type') $param .= ($key . '_' . $value . ',');
            }
            $param = preg_replace('/\,$/', '', $param);
            $src   = preg_replace('/x\-oss\-process\=([^\&\.]+)/', '', $src);
            $src   = str_replace('?&', '?', $src);
            $src   = preg_replace('/\?$/', '', $src);
            if ($param != '') {
                $src .= (strpos($src, '?') !== false) ? ('&x-oss-process=' . $param) : ('?x-oss-process=' . $param);
            }
            $path = $src;
        } else if (!empty($path)) {
            $qiniu_baseStr = config('upload.platform_config.base_url');
            if ($qiniu_baseStr) {
                $qiniu_bases = str_to_fields($qiniu_baseStr);
                $src         = $path;
                foreach ($qiniu_bases as $qiniu_base) {
                    if (strpos($src, $qiniu_base) === 0) {
                        $srcArray = explode('?', $src);
                        $url = isset($srcArray[0]) ? $srcArray[0] : '';
                        $queryStr = isset($srcArray[1]) ? $srcArray[1] : '';
                        $queryStr = $queryStr ? $queryStr : '';
                        $queryArr = [];
                        $fields   = [
                            'width'  => 'w',
                            'height' => 'h'
                        ];
                        $tmpArr   = ['imageView2', $versionConfig['mode']];
                        foreach ($versionConfig as $key => $value) {
                            if (isset($fields[$key])) {
                                $tmpArr[] = $fields[$key];
                                $tmpArr[] = $value;
                            }
                        }
                        $queryArr[] = implode('/', $tmpArr);
                        if (!empty($queryArr)) {
                            $str      = implode('%7C', $queryArr);
                            $queryStr = $queryStr ? preg_replace('/\%7C$/', '', $queryStr) . '%7C' . $str : $str;
                        }
                        $src  = $url . '?' . $queryStr;
                        $path = $src;
                        break;
                    }
                }
            }
        }
    } else {
        return $path;
    }
    return preg_replace('/^\./', '/', $path);
}

/***************************************************
 * 简单的CURL
 ***************************************************/
//curl get
function curl_get($url, $getinfo = false)
{
    //初始化
    $ch = curl_init();
    //设置选项，包括URL
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    //执行并获取HTML文档内容
    $output = curl_exec($ch);
    if ($getinfo) {
        $info   = curl_getinfo($ch);
        $output = array('content' => $output, 'info' => $info);
    }
    //释放curl句柄
    curl_close($ch);
    return $output;
}

//curl post
function curl_post($url, $data = array(), $getinfo = false)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    //post数据
    curl_setopt($ch, CURLOPT_POST, 1);
    //post的变量
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    $output = curl_exec($ch);
    if ($getinfo) {
        $info   = curl_getinfo($ch);
        $output = array('content' => $output, 'info' => $info);
    }
    curl_close($ch);
    return $output;
}

/***************************************************
 * 加密、解密、签名和编码
 ***************************************************/
//安全的base64位编码
function urlsafe_base64_encode($string)
{   
    $find = array('+', '/');
    $replace = array('-', '_');
    return str_replace($find, $replace, base64_encode($string));
    // $data = base64_encode($string);
    // $data = str_replace(array('+', '/', '='), array('-', '_', ''), $data);
    // return $data;
}

//安全的base64解码
function urlsafe_base64_decode($string)
{
    $data = str_replace(array('-', '_'), array('+', '/'), $string);
    $mod4 = strlen($data) % 4;
    if ($mod4) {
        $data .= substr('====', $mod4);
    }
    return base64_decode($data);
}

/**
 * 系统非常规MD5加密方法
 * @param string $str 要加密的字符串
 * @return string
 */
function sys_md5($str, $key = 'UserMd5Pwd')
{
    return '' === $str ? '' : md5(sha1($str) . $key);
}

/**
 * 系统加密方法
 * @param string $data 要加密的字符串
 * @param string $key 加密密钥
 * @param int $expire 过期时间 单位 秒
 * @return string
 */
function sys_encrypt($data, $key = '', $expire = 0)
{
    $key  = md5(empty($key) ? config('app.app_setting.data_auth') : $key);
    $data = base64_encode($data);
    $x    = 0;
    $len  = strlen($data);
    $l    = strlen($key);
    $char = '';
    for ($i = 0; $i < $len; $i++) {
        if ($x == $l) $x = 0;
        $char .= substr($key, $x, 1);
        $x++;
    }
    $str = sprintf('%010d', $expire ? $expire + time() : 0);
    for ($i = 0; $i < $len; $i++) {
        $str .= chr(ord(substr($data, $i, 1)) + (ord(substr($char, $i, 1))) % 256);
    }
    return str_replace(array('+', '/', '='), array('-', '_', ''), base64_encode($str));
}

/**
 * 系统解密方法
 * @param string $data 要解密的字符串 （必须是sys_encrypt方法加密的字符串）
 * @param string $key 加密密钥
 * @return string
 */
function sys_decrypt($data, $key = '')
{
    $key  = md5(empty($key) ? config('app.app_setting.data_auth') : $key);
    $data = str_replace(array('-', '_'), array('+', '/'), $data);
    $mod4 = strlen($data) % 4;
    if ($mod4) {
        $data .= substr('====', $mod4);
    }
    $data   = base64_decode($data);
    $expire = substr($data, 0, 10);
    $data   = substr($data, 10);
    if ($expire > 0 && $expire < time()) {
        return '';
    }
    $x    = 0;
    $len  = strlen($data);
    $l    = strlen($key);
    $char = $str = '';
    for ($i = 0; $i < $len; $i++) {
        if ($x == $l) $x = 0;
        $char .= substr($key, $x, 1);
        $x++;
    }
    for ($i = 0; $i < $len; $i++) {
        if (ord(substr($data, $i, 1)) < ord(substr($char, $i, 1))) {
            $str .= chr((ord(substr($data, $i, 1)) + 256) - ord(substr($char, $i, 1)));
        } else {
            $str .= chr(ord(substr($data, $i, 1)) - ord(substr($char, $i, 1)));
        }
    }
    return base64_decode($str);
}

/***************************************************
 * LIST TREE
 ***************************************************/
/**
 * 把返回的数据集转换成Tree
 * @param array $list 要转换的数据集
 * @param string $pid parent标记字段
 * @param string $level level标记字段
 * @return array
 */
function list_to_tree($list, $pk = 'id', $pid = 'pid', $child = '_child', $root = 0)
{
    // 创建Tree
    $tree = array();
    if (is_array($list)) {
        // 创建基于主键的数组引用
        $refer = array();
        foreach ($list as $key => $data) {
            $refer[$data[$pk]] =& $list[$key];
        }
        foreach ($list as $key => $data) {
            // 判断是否存在parent
            $parentId = $data[$pid];
            if ($root == $parentId) {
                $tree[] =& $list[$key];
            } else {
                if (isset($refer[$parentId])) {
                    $parent           =& $refer[$parentId];
                    $parent[$child][] =& $list[$key];
                }
            }
        }
    }
    return $tree;
}

/**
 * 将list_to_tree的树还原成列表
 * @param array $tree 原来的树
 * @param string $child 孩子节点的键
 * @param string $order 排序显示的键，一般是主键 升序排列
 * @param array $list 过渡用的中间数组，
 * @return array        返回排过序的列表数组
 */
function tree_to_list($tree, $child = '_child', $order = 'id', &$list = array())
{
    if (is_array($tree)) {
        $refer = array();
        foreach ($tree as $key => $value) {
            $reffer = $value;
            if (isset($reffer[$child])) {
                unset($reffer[$child]);
                tree_to_list($value[$child], $child, $order, $list);
            }
            $list[] = $reffer;
        }
        $list = list_sort_by($list, $order, $sortby = 'asc');
    }
    return $list;
}

/**
 * 对查询结果集进行排序
 * @access public
 * @param array $list 查询结果
 * @param string $field 排序的字段名
 * @param array $sortby 排序类型
 * asc正向排序 desc逆向排序 nat自然排序
 * @return array
 */
function list_sort_by($list, $field, $sortby = 'asc')
{
    if (is_array($list)) {
        $refer = $resultSet = array();
        foreach ($list as $i => $data)
            $refer[$i] = &$data[$field];
        switch ($sortby) {
            case 'asc': // 正向排序
                asort($refer);
                break;
            case 'desc':// 逆向排序
                arsort($refer);
                break;
            case 'nat': // 自然排序
                natcasesort($refer);
                break;
        }
        foreach ($refer as $key => $val)
            $resultSet[] = &$list[$key];
        return $resultSet;
    }
    return false;
}

/***************************************************
 * 其他
 ***************************************************/
/**
 * 获取随机码
 * @static
 * @access public
 * @param int $count 需要生成的位数
 * @param string $type 类型，1代表数字，A代表大写字母，a代表小写字母
 * @return string 随机码
 */
function get_ucode($count = 6, $type = '1')
{
    $code = $tmp = '';
    $abc  = 'abcdefghijklmnopqrstuvwxyz';
    $ABC  = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $num  = '0123456789';
    if (strstr($type, 'a')) {
        $code .= $abc;
    }
    if (strstr($type, 'A')) {
        $code .= $ABC;
    }
    if (strstr($type, '1')) {
        $code .= $num;
    }
    for ($i = 0; $i < $count; $i++) {
        $index = rand(0, strlen($code) - 1);
        $tmp   .= $code[$index];
    }
    return $tmp;
}

//获取客户端IP
function get_client_ip($type = 0, $adv = false)
{
    if (defined('ADV_CLIENT_IP')) {
        $ADV_CLIENT_IP = ADV_CLIENT_IP;
        $adv           = $ADV_CLIENT_IP == '1' ? true : false;
    }
    $type = $type ? 1 : 0;
    static $ip = NULL;
    if ($ip !== NULL) return $ip[$type];
    if ($adv) {
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $pos = array_search('unknown', $arr);
            if (false !== $pos) unset($arr[$pos]);
            $ip = trim($arr[0]);
        } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
    } elseif (isset($_SERVER['REMOTE_ADDR'])) {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    // IP地址合法验证
    $long = sprintf("%u", ip2long($ip));
    $ip   = $long ? array($ip, $long) : array('0.0.0.0', 0);
    return $ip[$type];
}

//复制一个数组(可选择字段)
function copy_array($arr, $fields = '')
{
    $arr    = is_array($arr) ? $arr : array();
    $tmp    = array();
    $fields = is_array($fields) ? $fields : str_to_fields($fields);
    foreach ($fields as $field) {
        if (isset($arr[$field])) {
            $tmp[$field] = $arr[$field];
        }
    }
    return $tmp;
}

//从数组中剪切一个值
function arr_shear(&$data, $name)
{
    $tmp = $data[$name];
    unset($data[$name]);
    return $tmp;
}

function br2nl($text)
{
    return preg_replace('/<br\\s*?\/??>/i', '', $text);
}

//获取请求里面的id
function get_request_ids($name = 'id')
{
    $arr   = array();
    $name2 = \bxkj_common\Pluralize::convert($name);
    $input = input();
    if (isset($input[$name2])) {
        $arr = is_string($input[$name2]) ? array_merge($arr, explode(",", $input[$name2])) : array_merge($arr, $input[$name2]);
    }
    if (isset($input[$name])) {
        array_push($arr, $input[$name]);
    }
    return $arr;
}

function redis_lock($lockName, $timeout = null)
{
    $redis      = \bxkj_common\RedisClient::getInstance();
    $key        = "lock:{$lockName}";
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
    $redis = \bxkj_common\RedisClient::getInstance();
    $key   = "lock:{$lockName}";
    $redis->del($key);
}

function get_order_no($type, $time = null)
{
    $map      = array(
        'vip'             => '01',
        'third'           => '02',
        'recharge'        => '03',
        'log'             => '04',
        'cash'            => '05',
        'pay_per_view'    => '06',
        'gift'            => '07',
        'barrage'         => '08',
        'live'            => '09',
        'raise'           => '10',
        'recharge_log'    => '11',
        'props'           => '13',
        'cover_star_vote' => '14',
        'taoke_shop'      => '15',
        'mall'            => '16',
        'agent_cash'      => '17',
        'week_star'       => '18',
        'red_packet'      => '19',
        'distribute'      => '20',
        'score'           => '21',
        'egg'             => '22'
    );
    $time     = isset($time) ? $time : time();
    $date     = date('ymd', $time);
    $newTime  = mktime(0, 0, 0, 11, 27, 2018);
    $isNew    = $time >= $newTime ? true : false;
    $typeNum  = isset($map[$type]) ? $map[$type] : '00';
    $lockName = 'order_no:' . $date . ($isNew ? ":{$typeNum}" : "");
    $redis    = \bxkj_common\RedisClient::getInstance();
    redis_lock($lockName, 10);
    $key     = 'order_no:autoinc' . ($isNew ? ":{$typeNum}" : "");
    $autoinc = $redis->zScore($key, $date);
    $autoinc = $autoinc ? $autoinc : 1000;
    $rand    = rand(1, 40);//最少得有1个
    $autoinc += $rand;
    $redis->zAdd($key, $autoinc, $date);
    redis_unlock($lockName);
    return $date . $typeNum . str_pad($autoinc, 9, '0', STR_PAD_LEFT);
}

//验证支付异步通知
function verify_payment_notify1($post = null)
{
    $post             = isset($post) ? $post : $_POST;
    $tmp              = copy_array($post, 'rel_no,rel_type,trade_no,user_id,third_trade_no,third_app_key,third_user_id,pay_method,
    total_fee,create_time,extra_data,sign_time,nonce_str,sign');
    $PAY_VERIFY_TOKEN = config('payment.pay_verify_token');
    if (!is_sign($tmp['sign'], $tmp, $PAY_VERIFY_TOKEN)) return false;
    return true;
}

//验证支付异步通知
function verify_payment_notify($post = null)
{
    $post             = isset($post) ? $post : $_POST;
    $tmp              = copy_array($post, 'rel_no,rel_type,trade_no,user_id,third_trade_no,third_app_key,third_user_id,pay_method,
    total_fee,create_time,extra_data,sign_time,nonce_str,sign');
    $PAY_VERIFY_TOKEN = config('payment.pay_verify_token');
    if (!is_sign($tmp['sign'], $tmp, $PAY_VERIFY_TOKEN)) return false;
    return true;
}

//验证支付同步通知
function verify_payment_return($get = null)
{
    $get = isset($get) ? $get : $_GET;
    $tmp = copy_array($get, 'rel_no,rel_type,trade_no,user_id,third_trade_no,third_app_key,third_user_id,pay_method,
    total_fee,create_time,extra_data,sign_time,nonce_str,sign');
    if (empty($tmp) || !is_array($tmp)) return false;
    $PAY_VERIFY_TOKEN = config('payment.pay_verify_token');
    if (!is_sign($tmp['sign'], $tmp, $PAY_VERIFY_TOKEN)) return false;
    return true;
}

/***************************************************
 * 根据经纬度获取位置信息
 ***************************************************/
/**
 * 格式化距离
 */
function format_distance($num)
{
    if ($num > 1000)
        $num = round($num / 1000, 2) . 'km';
    else
        $num = $num . 'm';
    return $num;
}

function get_position_Lng_lat($lng, $lat, $extensions = 'base')
{
    if (empty((int)$lng) || empty((int)$lat)) return [];
    $key       = config('app.map_setting.web_service_key');
    $location  = $lng . ',' . $lat;
    $radius    = 1000;
    $poitype   = '';
    $batch     = false;
    $roadlevel = 0;
    $url       = "http://restapi.amap.com/v3/geocode/regeo?output=json&key={$key}&location={$location}&poitype={$poitype}&radius={$radius}&extensions={$extensions}&batch={$batch}&roadlevel={$roadlevel}";
    $Http      = new \bxkj_common\HttpClient();
    $rst       = $Http->get($url)->getData('json');
    if ($rst['status'] != 1) return [];
    return $rst;
}

//银行卡信息
function bank_card_info($cardNum)
{
    $bankList = require ROOT_PATH . 'data/bank_list.php';
    $card_8   = substr($cardNum, 0, 8);
    if (isset($bankList[$card_8])) {
        return $bankList[$card_8];
    }
    $card_6 = substr($cardNum, 0, 6);
    if (isset($bankList[$card_6])) {
        return $bankList[$card_6];
    }
    $card_5 = substr($cardNum, 0, 5);
    if (isset($bankList[$card_5])) {
        return $bankList[$card_5];
    }
    $card_4 = substr($cardNum, 0, 4);
    if (isset($bankList[$card_4])) {
        return $bankList[$card_4];
    }
    return '';
}

function validate_idcard($id)
{
    $id        = strtoupper($id);
    $regx      = "/(^\d{15}$)|(^\d{17}([0-9]|X)$)/";
    $arr_split = array();
    if (!preg_match($regx, $id)) return FALSE;
    //检查15位
    if (15 == strlen($id)) {
        $regx = "/^(\d{6})+(\d{2})+(\d{2})+(\d{2})+(\d{3})$/";
        @preg_match($regx, $id, $arr_split);
        //检查生日日期是否正确
        $dtm_birth = "19" . $arr_split[2] . '/' . $arr_split[3] . '/' . $arr_split[4];
        return strtotime($dtm_birth);
    } else {
        //检查18位
        $regx = "/^(\d{6})+(\d{4})+(\d{2})+(\d{2})+(\d{3})([0-9]|X)$/";
        @preg_match($regx, $id, $arr_split);
        $dtm_birth = $arr_split[2] . '/' . $arr_split[3] . '/' . $arr_split[4];
        //检查生日日期是否正确
        if (!strtotime($dtm_birth)) return FALSE;
        //检验18位身份证的校验码是否正确。
        //校验位按照ISO 7064:1983.MOD 11-2的规定生成，X可以认为是数字10。
        $arr_int = array(7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2);
        $arr_ch  = array('1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2');
        $sign    = 0;
        for ($i = 0; $i < 17; $i++) {
            $b    = (int)$id{$i};
            $w    = $arr_int[$i];
            $sign += $b * $w;
        }
        $n       = $sign % 11;
        $val_num = $arr_ch[$n];
        if ($val_num != substr($id, 17, 1)) {
            return FALSE;
        } else {
            return TRUE;
        }
    }
}

function array_merge_notrepeat($arr, $arr2, $prefix = 'rep_', $check = true)
{
    foreach ($arr2 as $key => $value) {
        if (!$check || array_key_exists($key, $arr)) {
            $arr[$prefix . $key] = $value;
        } else {
            $arr[$key] = $value;
        }
    }
    return $arr;
}

function get_millisecond()
{
    list($t1, $t2) = explode(' ', microtime());
    return (float)sprintf('%.0f', (floatval($t1) + floatval($t2)) * 1000);
}

//地区名称替换
function replace_place_name($name, $parentName = null)
{
    $reg      = '/(自治区)|(自治州)|(自治县)$/';
    $expArr   = array('兰州市', '滨海新区', '浦东新区', '雄安新区');
    $zzSuffix = array('自治区', '自治州', '自治县');
    if (!empty($parentName)) {
    } elseif (preg_match('/區$/', $name)) {
        $name    = preg_replace('/區\s*$/', '区', $name);
        $nameRep = array('灣' => '湾', '東' => '东', '觀' => '观', '離島' => '离岛', '龍' => '龙', '貢' => '贡', '門' => '门', '黃' => '黄');
        foreach ($nameRep as $key => $value) {
            $name = preg_replace('/' . $key . '/', $value, $name);
        }
        return $name;
    }
    if (!preg_match($reg, $name)) {
        $reg2  = '/省|市|区|(地区)|县|(特别行政区)|盟|旗|(林区)\s*$/';
        $nName = $name;
        if (preg_match($reg2, $name) && !in_array($name, $expArr)) {
            $nName = preg_replace($reg2, '', $name);
        }
        return mb_strlen($nName, 'utf-8') < 2 ? $name : $nName;
    } else {
        $zzArr = array('蒙古族藏族', '黎族苗族', '哈尼族彝族', '傣族景颇族', '藏族羌族', '土家族苗族', '苗族土家族', '苗族侗族', '布依族苗族', '壮族苗族', '朝鲜族', '苗族', '土家族', '彝族', '蒙古', '布依族', '侗族', '黎族', '维吾尔族', '回族', '壮族', '景颇族', '蒙古族', '傈傈族', '傈僳族', '白族', '藏族', '羌族', '哈萨克族', '哈萨克', '柯尔克孜族', '柯尔克孜', '傣族');
        for ($i = 0; $i < count($zzSuffix); $i++) {
            for ($j = 0; $j < count($zzArr); $j++) {
                $tmp = '/' . $zzArr[$j] . $zzSuffix[$i] . '\s*$/';
                if (preg_match($tmp, $name)) {
                    return preg_replace($tmp, '', $name);
                }
            }
        }
    }
    return $name;
}

function make_qiniu_key($filename, $queryStr, $config, $uid = 0, $userKey = 'uid')
{
    $queryArr = [$userKey => $uid];
    if (!empty($queryStr)) {
        $tmp      = upload_query_decode($queryStr);
        $queryArr = array_merge($tmp, $queryArr);
    }
    $queryArr            = array_merge($queryArr, $config['vars'] ? $config['vars'] : []);
    $queryArr['uniqid']  = sha1(uniqid() . get_ucode(6, '1aA'));
    $queryArr['uniqid2'] = get_ucode(6, '1aA');
    $queryArr['date']    = date('Y-m-d');
    $root                = config('upload.platform_config.root_path');
    $ext                 = substr($filename, strripos($filename, '.') + 1);
    $name                = substr($filename, 0, 0 - (strlen($ext) + 1));
    $queryArr['ext']     = ltrim($ext, '.');
    $queryArr['name']    = $name;
    $path                = $root . ltrim($config['path'], '/\\');
    $key                 = parse_tpl($path, $queryArr);
    return $key;
}

function make_ws_key($filename, $queryStr, $config, $uid = 0, $userKey = 'uid')
{
    $queryArr = [$userKey => $uid];
    if (!empty($queryStr)) {
        $tmp      = upload_query_decode($queryStr);
        $queryArr = array_merge($tmp, $queryArr);
    }
    $queryArr            = array_merge($queryArr, $config['vars'] ? $config['vars'] : []);
    $queryArr['uniqid']  = sha1(uniqid() . get_ucode(6, '1aA'));
    $queryArr['uniqid2'] = get_ucode(6, '1aA');
    $queryArr['date']    = date('Y-m-d');
    $root                = config('upload.platform_config.root_path');
    $ext                 = substr($filename, strripos($filename, '.') + 1);
    $name                = substr($filename, 0, 0 - (strlen($ext) + 1));
    $queryArr['ext']     = ltrim($ext, '.');
    $queryArr['name']    = $name;
    $path                = $root . ltrim($config['path'], '/\\');
    $key                 = parse_tpl($path, $queryArr);
    $arr = explode('/',$key);
    $str = end($arr);
    return $str;
}
function upload_query_encode($data)
{
    $data = is_array($data) ? http_build_query($data) : $data;
    return $data;
    return urlsafe_base64_encode(sys_encrypt($data));
}

function upload_query_decode($data)
{
    if (empty($data)) return [];
    $str = $data;
    /*$str = sys_decrypt(urlsafe_base64_decode($data));
    if (empty($str)) return [];*/
    $tmp = [];
    parse_str($str, $tmp);
    return $tmp ? $tmp : [];
}

function number_2_rmb($num)
{
    $c1  = "零壹贰叁肆伍陆柒捌玖";
    $c2  = "分角元拾佰仟万拾佰仟亿";
    $num = round($num, 2);//精确到分后面就不要了，所以只留两个小数位
    //将数字转化为整数
    $num = $num * 100;
    if (strlen($num) > 10) return false;
    $i = 0;
    $c = "";
    while (1) {
        if ($i == 0) {
            //获取最后一位数字
            $n = substr($num, strlen($num) - 1, 1);
        } else {
            $n = $num % 10;
        }
        //每次将最后一位数字转化为中文
        $p1 = substr($c1, 3 * $n, 3);
        $p2 = substr($c2, 3 * $i, 3);
        if ($n != '0' || ($n == '0' && ($p2 == '亿' || $p2 == '万' || $p2 == '元'))) {
            $c = $p1 . $p2 . $c;
        } else {
            $c = $p1 . $c;
        }
        $i = $i + 1;
        //去掉数字最后一位了
        $num = $num / 10;
        $num = (int)$num;
        //结束循环
        if ($num == 0) break;
    }
    $j    = 0;
    $slen = strlen($c);
    while ($j < $slen) {
        //utf8一个汉字相当3个字符
        $m = substr($c, $j, 6);
        //处理数字中很多0的情况,每次循环去掉一个汉字“零”
        if ($m == '零元' || $m == '零万' || $m == '零亿' || $m == '零零') {
            $left  = substr($c, 0, $j);
            $right = substr($c, $j + 3);
            $c     = $left . $right;
            $j     = $j - 3;
            $slen  = $slen - 3;
        }
        $j = $j + 3;
    }
    //这个是为了去掉类似23.0中最后一个“零”字
    if (substr($c, strlen($c) - 3, 3) == '零') {
        $c = substr($c, 0, strlen($c) - 3);
    }
    return empty($c) ? "零元整" : $c . "整";
}

function transform_topic($str, $replacement)
{
    if (!is_array($replacement)) $replacement = explode(',', $replacement);
    $pattern = '/#(.*?)#+/';
    preg_match_all($pattern, $str, $match);
    if (empty($match[0])) return $str;
    return preg_replace($match[0], $replacement, $str);
}

function transform_at($str, $replacement)
{
    if (!is_array($replacement)) $replacement = explode(',', $replacement);
    $pattern = '/(@.*?)\s+/';
    preg_match_all($pattern, $str, $match);
    if (empty($match[1])) return $str;
    $match[1] = array_map(function ($value) {
        $value = $value . '@';
        return $value;
    }, $match[1]);
    return preg_replace($match[1], $replacement, $str);
}

function check_auth($rules, $uid = null, $type = 1)
{
    $authOn = config('app.auth_on');
    if (!$authOn) return true;
    $uid = isset($uid) ? $uid : (defined('AUTH_UID') ? AUTH_UID : '');
    if (empty($uid)) return false;
    if (defined('ROOT_UID') && ($uid == ROOT_UID)) return true;//超级管理员
    static $auth;
    if (!isset($auth)) {
        $auth = new \bxkj_module\service\Auth();
    }
    return $auth->check($uid, $rules, $type);
}

function menu_url($url, $paramStr = '')
{
    if (empty($url)) return 'javascript:;';
    $params = [];
    if (!empty($paramStr)) {
        if (is_string($paramStr)) {
            parse_str($paramStr, $params);
        } else {
            $params = $paramStr;
        }
    }
    if (strpos($url, '/') !== 0 && !preg_match('/^(http|ftp|https|javascript|mailto|\/):/', $url)) {
        $url = url($url, $params);
    } else if (!empty($params)) {
        $str = http_build_query($params);
        $url = strpos($url, '?') === false ? "{$url}?$str" : "{$url}&$str";
    }
    return $url;
}

//白名单方法
function is_allow($allows, $controller = null, $action = null)
{
    $controller = parse_name(isset($controller) ? $controller : \think\facade\Request::controller(), 0, false);
    $action     = parse_name(isset($action) ? $action : \think\facade\Request::action(), 0, false);
    foreach ($allows as $key => $val) {
        $un = in_array('__un__', $val);//取反变黑名单
        $c  = $key;
        if ($c == $controller) {
            if (($un && !in_array($action, $val)) || (!$un && in_array($action, $val))) return true;
        }
    }
    return false;
}

function user_name($user)
{
    if (!is_array($user)) return $user;
    $username_order = config('app.username_order');
    $username_order = str_to_fields($username_order ? $username_order : 'remark_name,real_name,nickname');
    foreach ($username_order as $item) {
        if (!empty($user[$item])) {
            return $user[$item];
        }
    }
    return 'NULL';
}

function json_success($data, $message = '')
{
    return json(array(
        'status'  => 0,
        'data'    => $data,
        'message' => $message
    ));
}

function json_error($message, $status = 1, $url = null, $data = null)
{
    if (is_error($message)) {
        $tmp = $message->toArray();
    } else {
        $tmp = [
            'message' => $message,
            'status'  => $status
        ];
    }
    if (isset($url)) $tmp['url'] = $url;
    if (isset($data)) $tmp['data'] = $data;
    return json($tmp);
}

function make_error($message, $status = 1, $data = [])
{
    if (is_error($message)) {
        $status  = $message->status;
        $data    = $message->data;
        $message = $message->message;
    }
    return \bxkj_common\BaseError::getInstance($message, $status, $data);
}

function is_error(&$obj)
{
    if (!is_object($obj)) return false;
    $className = get_class($obj);
    if ($className == 'bxkj_common\BaseError') return true;
    return is_subclass_of($obj, 'bxkj_common\BaseError');
}

function weak_password($salt, $password, $username = '')
{
    $simplePwds = ['123456', '12345678', '123456789', '1234567890',
        '555555', 'admin', 'admin888', '111111', '666666',
        'abc123', 'abcdef', '000000', '888888', '888666',
        'test123', 'test123456', 'qwerty', 'password', '987654321'];
    if (!empty($username)) {
        $simplePwds[] = $username;
        $simplePwds[] = ucfirst(strtolower($username));
        $simplePwds[] = strtolower($username);
        $simplePwds[] = $username . '123';
        $simplePwds[] = $username . '888';
    }
    foreach ($simplePwds as $pwd) {
        if (sha1($pwd . $salt) == $password) {
            return true;
        }
    }
    return false;
}

//获取地址ID名称
function region_name($ids = array())
{
    $ids       = is_string($ids) ? explode(',', $ids) : $ids;
    $selectIds = array();
    if ($ids) {
        foreach ($ids as $id) {
            $idArr = is_string($id) ? explode('-', $id) : $id;
            foreach ($idArr as $value) {
                if (!in_array($value, $selectIds)) $selectIds[] = $value;
            }
        }
    }
    $selected = array();
    if (!empty($selectIds)) {
        $index = array_search('0', $selectIds);
        if (is_int($index)) array_slice($selectIds, $index, 1);
        $result = \think\Db::name('region')->whereIn('id', $selectIds)->field('id,name')->limit(count($selectIds))->select();
        if (is_int($index)) {
            $result   = $result ? $result : array();
            $result[] = array('id' => 0, 'name' => '中国');
        }
        if (!empty($result)) $selected = array_to_object($result, 'id');
    }
    $nameArr = array();
    if ($ids) {
        foreach ($ids as $id) {
            $idArr   = is_string($id) ? explode('-', $id) : $id;
            $pathArr = array();
            foreach ($idArr as $value) {
                $pathArr[] = $selected[(string)$value] ? $selected[(string)$value]['name'] : '';
            }
            $nameArr[] = implode('-', $pathArr);
        }
    }
    return implode(',', $nameArr);
}

//将索引数组转为object
function array_to_object($arr, $key = 'id')
{
    $obj = array();
    for ($i = 0; $i < count($arr); $i++) {
        $obj[$arr[$i][$key]] = $arr[$i];
    }
    return $obj;
}

function equ_rmb($value)
{
    return round($value / 10, 2);
}

//返回当前的毫秒时间戳
function msectime()
{
    list($msec, $sec) = explode(' ', microtime());
    $msectime = (float)sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);
    return $msectime;
}

function diff_time($time, $suffix = '')
{
    switch (true) {
        case $time < 60 :
            $res = $time . '秒';
            break;
        case $time < 3600 :
            $res = floor($time / 60) . '分钟';
            break;
        case $time < 86400 :
            $res = floor($time / 3600) . '小时' . floor(floor($time / 60) % 60) . '分钟';
            break;
        default :
            $res = floor($time / 86400) . '天' . floor(floor($time / 3600) % 24) . '小时' . floor(floor($time / 60) % 60) . '分钟';
            break;
    }
    return $res . $suffix;
}

/**
 * 返回今日开始和结束的时间戳
 *
 * @return array
 */
function today()
{
    list($y, $m, $d) = explode('-', date('Y-m-d'));
    return [
        mktime(0, 0, 0, $m, $d, $y),
        mktime(23, 59, 59, $m, $d, $y)
    ];
}

/**
 * 返回昨日开始和结束的时间戳
 *
 * @return array
 */
function yesterday()
{
    $yesterday = date('d') - 1;
    return [
        mktime(0, 0, 0, date('m'), $yesterday, date('Y')),
        mktime(23, 59, 59, date('m'), $yesterday, date('Y'))
    ];
}

/**
 * 返回本周开始和结束的时间戳
 *
 * @return array
 */
function week()
{
    list($y, $m, $d, $w) = explode('-', date('Y-m-d-w'));
    if ($w == 0) $w = 7; //修正周日的问题
    return [
        mktime(0, 0, 0, $m, $d - $w + 1, $y), mktime(23, 59, 59, $m, $d - $w + 7, $y)
    ];
}

/**
 * 返回上周开始和结束的时间戳
 *
 * @return array
 */
function lastWeek()
{
    $timestamp = time();
    return [
        strtotime(date('Y-m-d', strtotime("last week Monday", $timestamp))),
        strtotime(date('Y-m-d', strtotime("last week Sunday", $timestamp))) + 24 * 3600 - 1
    ];
}

function getJump($protocol, array $params = [])
{
    !empty($params) && $params = http_build_query($params);
    $baseUrl = 'bx://router.bxtv.com/';
    switch ($protocol) {
        case 'personal' :
            $baseUrl .= 'personal?' . $params;
            break;
        case 'app_home' :
            $baseUrl .= 'app_home';
            break;
        case 'enter_room' :
            $baseUrl .= 'enter_room?' . $params;
            break;
        case 'topic' :
            $baseUrl .= 'topic?' . $params;
            break;
        case 'post_video' :
            $baseUrl .= 'post_video?' . $params;
            break;
        case 'video_detail' :
            $baseUrl .= 'video_detail?' . $params;
            break;
        case 'login' :
            $baseUrl .= 'login';
            break;
        case 'message_list' :
            $baseUrl .= 'message_list?' . $params;
            break;
        case 'recharge' :
            $baseUrl .= 'recharge';
            break;
        case 'live_detail' :
            $baseUrl .= 'live_detail?' . $params;
            break;
        case 'vip' :
            $baseUrl .= 'vip?' . $params;
            break;
        case 'charm_rank':
            $baseUrl .= 'charm_rank';
            break;
        case 'heroes_rank':
            $baseUrl .= 'heroes_rank';
            break;
        case 'follow':
            $baseUrl .= 'follow';
            break;
        case 'taoke':
            $baseUrl .= 'taoke';
            break;
    }
    return $baseUrl;
}

function hexTobin($data, $types = false)
{
    if (!is_string($data)) return false;
    if ($types === false) {
        $len = strlen($data);
        if ($len % 2 || strspn($data, '0123456789abcdefABCDEF') != $len) return false;
        return hex2bin($data);
    } else {
        return bin2hex($data);
    }
}

//推送调试信息
function bxkj_console($data, $type = 'log')
{
    if (RUNTIME_ENVIROMENT !== 'pro') {
        call_user_func_array(['\bxkj_common\Console', $type], [$data]);
    }
}

/**
 * @param $data
 * @return array
 * 数组键值转换小写
 */
function bxkj_lcfirst($data)
{
    $tmp = [];
    if (!is_array($data)) return $tmp;
    foreach ($data as $key => $value) {
        $tmp[lcfirst($key)] = $value;
    }
    return $tmp;
}

/**
 * 往数组中某一个位置，插入一个新的数组
 * @param 原数组 $arrayOne
 * @param 要插入的数组 $arrayTwo
 * @return 新数组
 */
function add_rand_array($arrayOne, $arrayTwo)
{
    $count = count($arrayOne);
    if ($count == 0) return $arrayTwo;
    $position = rand(1, $count);
    array_splice($arrayOne, $position, 0, $arrayTwo);
    return $arrayOne;
}

function day_thirty($num = 30)
{
    if (empty($num)) return false;
    $dayArray = [];
    for ($i = 1; $i <= $num; $i++) {
        $dayArray[] = $i;
    }
    return $dayArray;
}

function get_agent($agentId)
{
    if (empty($agentId)) return;
    $res = \think\db::name('agent')->where(['id' => $agentId])->find();
    if (empty($res)) return '无';
    return $res['name'];
}

/**
 * 判断是否为链接
 * @param $url
 * @return bool
 */
function is_url($url)
{
    $preg = "/^http(s)?:\\/\\/.+/";
    if (preg_match($preg, $url)) {
        return true;
    } else {
        return false;
    }
}

function submit_verify($redis_key, $time = 2)
{
    $redis   = \bxkj_common\RedisClient::getInstance();
    $is_lock = $redis->setnx($redis_key, time());
    if ($is_lock == true) {
        $redis->expireAt($redis_key, time() + $time);
        return true;
    } else {
        if ($redis->get($redis_key) + $time < time()) {
            $redis->del($redis_key);
            return true;
        } else {
            return false;
        }
    }
}

function checkImgLength($imgs, $val)
{
    $imgLength = explode(',', trim($imgs));
    if (count($imgLength) <= $val) {
        return true;
    } else {
        return false;
    }
}

/**
 * 检查表单是否被重复提交
 * 相同内容的表单在设定时间内只能提交1次
 * @param int $iTimeOffset
 * @return bool
 */
function checkFormSubmit($iTimeOffset = 60)
{
    // 取得表单的标识
    $idForm = md5(serialize($_POST));
    // 是否需要表单提交检察
    $iFormCheck = true;
    if (isset($_SESSION['formSubmitCheck'])) {
        // 删除过期的表单标识
        foreach (array_keys($_SESSION['formSubmitCheck']) as $val) {
            if (time() > $val) {
                unset($_SESSION['formSubmitCheck'][$val]);
            }
        }
    } else {
        $_SESSION['formSubmitCheck'] = array();
        $iFormCheck                  = false;
    }
    if ($iFormCheck == true) {
        // 检查是否有重复标识的提交记录
        foreach ($_SESSION['formSubmitCheck'] as $val) {
            if ($val == $idForm) {
                return false;
            }
        }
    }
    // 保存表单标识
    $_SESSION['formSubmitCheck'][(time() + $iTimeOffset)] = $idForm;
    return true;
}

/**
 * 检查用户的相关信息
 *
 * @return array
 *
 */
function userMsg($uid, $field)
{
    $redis      = \bxkj_common\RedisClient::getInstance();
    $key        = "user:{$uid}";
    $userDetail = json_decode($redis->get($key), true);
    if (empty($userDetail)) {
        $userDetail = \think\db::name('user')->where(['user_id' => $uid])->find();
    }
    $fields = str_to_fields($field);
    foreach ($fields as $field) {
        if (is_array($userDetail) && isset($userDetail[$field])) {
            $userDe[$field] = emoji_decode($userDetail[$field]);
        }
    }

    return $userDe;
}

/**
 *  返还@信息
 *
 * @return array
 *
 */
function prviateMsg($privetid)
{
    $privetMsg       = [];
    $privetidtoArray = explode(',', $privetid);
    foreach ($privetidtoArray as $k => $v) {
        $userMsg = userMsg($v, 'user_id,avatar,nickname,gender');
        if(!empty($userMsg)) $privetMsg[] = $userMsg;
    }
    return $privetMsg;
}

/**
 *  返还@话题信息
 *
 * @return array
 *
 */
function titleMsg($title)
{
    $titleM       = [];
    $titletoArray = explode(',', trim($title, ','));
    array_filter($titletoArray);
    foreach ($titletoArray as $k => $v) {
        $redis = \bxkj_common\RedisClient::getInstance();
        $rest  = $redis->hGet("cache:friend_msg_Topic_lists", $v);
        if (empty($rest)) {
            $find = \think\db::name('friend_circle_topic')->where(['topic_id' => $v])->find();
            if (!empty($find)) {
                $find['userMsg'] = userMsg($find['uid'], 'user_id,avatar,nickname,gender');
                $redis->hSet("cache:friend_msg_Topic_lists", $v, json_encode($find));
                $rest = $redis->hGet("cache:friend_msg_Topic_lists", $v);
            }
        }
        $titleM[] = [
            'topic_id'   => $v,
            'topic_name' => json_decode($rest, true)['topic_name'],
        ];
    }
    return $titleM;
}

//PHP 计算两个时间戳之间相差的时间
//功能：计算两个时间戳之间相差的日时分秒
//$begin_time  开始时间戳
//$end_time 结束时间戳
function timediff($begin_time, $end_time)
{
    if ($begin_time < $end_time) {
        $starttime = $begin_time;
        $endtime   = $end_time;
    } else {
        $starttime = $end_time;
        $endtime   = $begin_time;
    }
    //计算天数
    $timediff = $endtime - $starttime;
    $days     = intval($timediff / 86400);
    //计算小时数
    $remain = $timediff % 86400;
    $hours  = intval($remain / 3600);
    //计算分钟数
    $remain = $remain % 3600;
    $mins   = intval($remain / 60);
    //计算秒数
    $secs = $remain % 60;
    $res  = array("day" => $days, "hour" => $hours, "min" => $mins, "sec" => $secs);
    return $res;
}

/**
 * 过滤信息
 *
 * @return array
 *
 */
function filterMsg($old, $field)
{
    $fields = str_to_fields($field);
    foreach ($fields as $field) {
        if (is_array($old) && isset($old[$field])) {
            $userDe[$field] = $old[$field];
        }
    }
    return $userDe;
}

/**
 * 计算经纬度间距离
 * @param $latitude1 第一个经度
 * @param $latitude2 第二个经度
 * @param $longitude1 第二个纬度
 * @param $longitude2 第二个纬度
 * @return array
 *
 */
function getDistanceBetweenPointsNew($latitude1, $longitude1, $latitude2, $longitude2)
{
    $theta      = $longitude1 - $longitude2;
    $miles      = (sin(deg2rad($latitude1)) * sin(deg2rad($latitude2))) + (cos(deg2rad($latitude1)) * cos(deg2rad($latitude2)) * cos(deg2rad($theta)));
    $miles      = acos($miles);
    $miles      = rad2deg($miles);
    $miles      = $miles * 60 * 1.1515;
    $feet       = $miles * 5280;
    $yards      = $feet / 3;
    $kilometers = $miles * 1.609344;
    $meters     = $kilometers * 1000;
    return compact('miles', 'feet', 'yards', 'kilometers', 'meters');
}

/**
 * 简化api if接口使用
 * @param $condtion
 * @param $msg
 */
function apiAsserts($condtion, $msg, $code = 1)
{
    if ($condtion === true) {
        throw new \think\Exception($msg, $code);
    }
}

/**
 * 系统发布动态函数
 * @return array
 * @uid  用户id
 * @content 发布的内容
 * @picture  发出的图片链接，多图使用‘，’分割
 * @video   发出的视频链接
 * @voice   发出的音频链接
 * @location 坐标信息
 * @type   发布的归类  发布信息类型2：话题3，圈子6表白
 * @msg_type 发给人描述   发给类型1：所有人2：好友3：陌生人4:私密
 * @title   话题 传入话题id串，用‘,’分割
 * @extend_type 扩展分类:1综合2视频3：声音
 * @privateid  @好友id(多个好友用“”','分割),发表白时这个就是对方的id
 * @systemtype  系统信息类型0：非系统发1：更新头像2：分享商品3：开播公共
 * @systemplus  发布附加渲染数据 传入json数据
 * @extend_talk
 * @extend_circle  圈子id(只有一个)
 * @render_type    渲染参数0：为系统保留
 * @cover_url    封面
 * @dynamic_title  标题
 * @ address       地址 （汉字）
 * @status          是否可用1：可用
 */
function systemSend($uid, $content = '', $picture = '', $video = '', $voice = '', $location, $type = 2, $msg_type = 1, $title = '',
                    $extend_type, $privateid = '', $systemtype = 0, $systemplus = '', $extend_talk = '', $extend_circle = ''
    , $render_type = 0, $cover_url = '', $dynamic_title = '', $address = '', $status = 1, $sing_title = '', $sing_author = '',$voice_time = 0)
{
    try {
        $sensitives = file_get_contents( DOMAIN_URL.'/core/filter/check?content='.$content.$dynamic_title);
        $sensitives = json_decode($sensitives, true);
        $sensitive = $sensitives['data'];
    } catch (\Exception $e) {
        $sensitive = '';
    }
    if (!empty($sensitive)){
        return rError(implode(',',$sensitive).'这些词为违禁词');
    }
    $redis       = new RedisClient();
    $cacheFriend = $redis->exists('cache:friend_config');
    if (empty($cacheFriend)) {
        $arr  = [];
        $ser  = new SysConfig();
        $info = $ser->getConfig("friend");
        if (empty($info)) return [];
        $redis->setex('cache:friend_config', 4 * 3600, $info['value']);
    }
    $friendConfigRes = json_decode($redis->get('cache:friend_config'), true);
    $toArray         = explode(',', $location);
    $lat             = trim($toArray[0]);
    $lng             = trim($toArray[1]);
    if(!empty($picture)){
        $imgs_info = [];
        $imgsAarray = explode(',',$picture);
        foreach ($imgsAarray as $key=>$val){
            $imgsdetail = file_get_contents( $val.'?imageInfo');
            $imgstemp= json_decode($imgsdetail, true);
            $imgstemp['badge'] = 'static';
            if($imgstemp['format']=='gif'||$imgstemp['format']=='GIF'){
                $imgstemp['badge'] = 'gif';
            }
            if($imgstemp['width']>0 && $imgstemp['height']/$imgstemp['width']>3){
                $imgstemp['badge'] = 'long';
            }
            $imgstemp['smallpicture'] = $val.'?imageView2/1/w/300/h/300';

           $imgs_info[] = $imgstemp;
                }
    }
    $imgs_inst = json_encode($imgs_info);
    $data            = [
        'uid'           => $uid,
        'content'       => emoji_encode($content),
        'picture'       => $picture,
        'video'         => $video,
        'voice'         => $voice,
        'location'      => $location,
        'create_time'   => time(),
        'type'          => $type,
        'msg_type'      => $msg_type,
        'title'         => $title,
        'extend_type'   => $extend_type,
        'privateid'     => $privateid,
        'systemtype'    => $systemtype,
        'systemplus'    => $systemplus,
        'extend_talk'   => $extend_talk,
        'extend_circle' => $extend_circle ? $extend_circle : '',
        'render_type'   => $render_type,
        'cover_url'     => $cover_url,
        'dynamic_title' => $dynamic_title,
        'address'       => $address,
        'lat'           => $lat ? $lat : 0,
        'lng'           => $lng ? $lng : 0,
        'status'        => $status,
        'sing_title'    => $sing_title,
        'sing_author'   => $sing_author,
        'voice_time'    => $voice_time,
        'imgs_detail'   => $imgs_inst,
    ];
    if (!checkImgLength($picture, $friendConfigRes['msg_img_length'])) {
        return rError('图片张数超过系统限制');
    };
    if (substr_count(title, ',') >= $friendConfigRes['create_dynamic_num']) {
        return rError('话题选择的个数超出系统限制');
    };
    if ($type == 6) {
        $followModel = new FollowModel();
        $followInfo  = $followModel->getFollowInfo($uid, $privateid);
        if (empty($followInfo['is_follow'])) {
            return rError('您还未关注该用户');
        }
    }
    //判断是否被圈子禁言了
    if (!empty($extend_circle)) {
        $circleFollow = new FriendCircleCircleFollow();
        $count        = $circleFollow->countTotal(['uid' => $uid, 'circle_id' => $extend_circle, 'status' => [1, 2]]);
        if ($count) {
            return rError('您已经被禁止在此圈子内发言');
        }
    }
    $friend = new  FriendCircleMessage();
    $rest   = $friend->add($data);
    $code   = 1;
    $msg    = '发布成功';
    return compact("code", "msg", "rest");
}

function rError($msg)
{
    $code = -1;
    $msg  = $msg;
    return compact("code", "msg");
}

/**
 * 本月日期
 *
 * @return array
 *
 */
function getMonth($date)
{
    $firstday = date("Y-m-01", strtotime($date));
    $lastday  = date("Y-m-d", strtotime("$firstday +1 month -1 day"));
    return array($firstday, $lastday);
}

/**
 * 上月开始和结束日期
 * @return array
 *
 */
function getlastMonthDays($date)
{
    $timestamp = strtotime($date);
    $firstday  = date('Y-m-01', strtotime(date('Y', $timestamp) . '-' . (date('m', $timestamp) - 1) . '-01'));
    $lastday   = date('Y-m-d', strtotime("$firstday +1 month -1 day"));
    return array($firstday, $lastday);
}

function filerMsg($data)
{
    $filter          = new FriendCircleMessageFilter();
    $filterUserArray = $filter->filterUserArray(USERID, 2);
    foreach ($data as $k => $v) {
        //是否已经设置了不显示此动态
        if (!empty($filterUserArray)) {
            if (in_array($v['id'], $filterUserArray)) {
                unset($data[$k]);
            }
        }
        switch ($v['msg_type']) {
            case 2:  //好友 ：判断这条消息的查看着是否为好友，不是就过滤掉
                $followModel   = new FollowModel();
                $myFriendsList = $followModel->mutualArray($v['uid']);
                if (!in_array(USERID, $myFriendsList)) {
                    unset($data[$k]);
                }
                break;
            case 3:  //陌生人
                $followModel   = new FollowModel();
                $myFriendsList = $followModel->mutualArrayNoMY($v['uid']);
                if (in_array(USERID, $myFriendsList)) {
                    unset($data[$k]);
                }
                break;
            case 4:  //私密
                if (!empty($v['privateid'])) {
                    $priveArray = explode(',', $v['privateid']);
                }
                if (count($priveArray) > 0) {
                    if (!in_array(USERID, $priveArray)) {
                        unset($data[$k]);
                    }
                }
                break;
            default: //所有人
                ;
        }
    }
    return $data;
}

function changeRedisMsg($fcmid)
{
    $redis     = new RedisClient();
    $friendMsg = new FriendCircleMessage();
    $rest1     = $friendMsg->getQuery(['id' => $fcmid], '*', 'id');
    $redis->set("bx_friend_msg:" . $fcmid, json_encode($rest1));
}

/**
 * 截取文字
 * @param $str 字符串
 * @param $s    开始
 * @param $e    结束
 * @return string
 */
function get_word($str, $s, $e)
{
    $text = '';
    $preg = '/' . $s . '(.*?)' . $e . '/s';
    if (!preg_match_all($preg, $str, $matches)) {
    } else {
        $text = $matches[1][0];
    }
    return $text;
}

/**
 * 创建目录
 * @param $name
 * @return bool
 */
function mk_dir($name)
{
    if (is_dir($name)) {
        return false;
    } else {
        $res = @mkdir(iconv('GBK', 'UTF-8', $name), 511, !0);
        if ($res) {
            return true;
        } else {
            return false;
        }
    }
}

function getSign($data, $secret)
{
    ksort($data);
    $str = '';
    foreach ($data as $k => $v) {
        $str .= "$k=$v&";
    }
    $str = substr($str, 0, -1);
    return md5($str . $secret);
}

function curlMob($url, $post_data, $app_secret)
{
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HEADER, 0);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_POST, 0);
    $jsonStr = json_encode($post_data);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $jsonStr);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json; charset=utf-8',
            'Content-Length: ' . strlen($jsonStr)
        )
    );
    $data = curl_exec($curl);
    curl_close($curl);
    $json = json_decode($data, true);
    if ($json['status'] == 200) {
        $decrept_data = openssl_decrypt($json['res'], "DES-CBC", $app_secret, 0, "00000000");
        $json['res']  = json_decode($decrept_data, true);
        return ['code' => 200, 'data' => $json['res']];
    } else {
        //$json['error'] = json_decode($json['error'], true);
        return ['code' => 101, 'msg' => $json['error']];
    }
}

/**
 * 销量格式化
 * @param $numer
 * @return string
 */
function volume_format($numer)
{
    if ($numer >= 10000000) {
        $real  = intval($numer / 100000000);
        $numer = $real . 'E+';
    }
    if ($numer >= 10000) {
        $real  = intval($numer / 10000);
        $numer = $real . 'W+';
    }
    $numer = (string)$numer;
    return $numer;
}

/**
 * 生成快站签名
 * @param $data
 * @param $secret
 * @return string
 */
function getKzSign($data, $secret)
{
    ksort($data);
    $str = '';
    foreach ($data as $k => $v) {
        $str .= $k . $v;
    }
    return md5($secret . $str . $secret);
}

function curl_kz_post($url, $data = array())
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    $output = curl_exec($ch);
    curl_close($ch);
    return $output;
}

function strTojson($data)
{
    $RewardstoArray = explode(',', $data);
    return json_encode($RewardstoArray, JSON_FORCE_OBJECT);
}

//php获取中文字符拼音首字母
function getFirstCharter($str)
{
    if (empty($str)) {
        return '';
    }
    $fchar = ord($str{0});
    if ($fchar >= ord('A') && $fchar <= ord('z')) return strtoupper($str{0});
    $s1  = iconv('UTF-8', 'gb2312', $str);
    $s2  = iconv('gb2312', 'UTF-8', $s1);
    $s   = $s2 == $str ? $s1 : $str;
    $asc = ord($s{0}) * 256 + ord($s{1}) - 65536;
    if ($asc >= -20319 && $asc <= -20284) return 'A';
    if ($asc >= -20283 && $asc <= -19776) return 'B';
    if ($asc >= -19775 && $asc <= -19219) return 'C';
    if ($asc >= -19218 && $asc <= -18711) return 'D';
    if ($asc >= -18710 && $asc <= -18527) return 'E';
    if ($asc >= -18526 && $asc <= -18240) return 'F';
    if ($asc >= -18239 && $asc <= -17923) return 'G';
    if ($asc >= -17922 && $asc <= -17418) return 'H';
    if ($asc >= -17417 && $asc <= -16475) return 'J';
    if ($asc >= -16474 && $asc <= -16213) return 'K';
    if ($asc >= -16212 && $asc <= -15641) return 'L';
    if ($asc >= -15640 && $asc <= -15166) return 'M';
    if ($asc >= -15165 && $asc <= -14923) return 'N';
    if ($asc >= -14922 && $asc <= -14915) return 'O';
    if ($asc >= -14914 && $asc <= -14631) return 'P';
    if ($asc >= -14630 && $asc <= -14150) return 'Q';
    if ($asc >= -14149 && $asc <= -14091) return 'R';
    if ($asc >= -14090 && $asc <= -13319) return 'S';
    if ($asc >= -13318 && $asc <= -12839) return 'T';
    if ($asc >= -12838 && $asc <= -12557) return 'W';
    if ($asc >= -12556 && $asc <= -11848) return 'X';
    if ($asc >= -11847 && $asc <= -11056) return 'Y';
    if ($asc >= -11055 && $asc <= -10247) return 'Z';
    return '';
}

/**
 * 序列化数据转数组
 * @param $str
 * @return string
 */
function s2array($data)
{
    $lyricsArray = [];
    $restArray   = unserialize($data);
    foreach ($restArray as $k1 => $v1) {
        $lyricsArray[] = [
            'lyrics_key' => $k1 + 1,
            'value'      => $v1,
        ];
    }
    return $lyricsArray;
}

/**
 * 对象转数组
 * @param $str
 * @return string
 */
function object_array($array)
{
    if (is_object($array)) {
        $array = (array)$array;
    }
    if (is_array($array)) {
        foreach ($array as $key => $value) {
            $array[$key] = object_array($value);
        }
    }
    return $array;
}

function exchangeTime($time)
{
    $timeStr = "";
    if (is_in_today($time)) {
        $timeStr .= "今天 " . date("H:i", $time);
        return $timeStr;
    }
    if (is_in_yestoday($time)) {
        $timeStr .= "昨天 " . date("H:i", $time);
        return $timeStr;
    }
    $timeStr .= date("m-d H:i");
    return $timeStr;
}

/**
 * 判断是否需要向kpi_millet表加入收益
 * @param $agentId
 * @return bool true 加入 false不加入
 */
function isagent_kpi_prifit($agentId)
{
    if (empty($agentId) || !is_numeric($agentId)) return false;
    $redis = new RedisClient();
    $data  = $redis->get("agent_prift:agent:{$agentId}");
    if ($data == 1) return true;
    if ($data == 2) return false;
    $cashType = config('app.cash_setting.cash_type');
    $agent    = \think\Db::name('agent')->where(['id' => $agentId])->find();
    if (empty($agent)) return false;
    if ((empty($cashType) && $agent['cash_type'] == 1) || (!empty($cashType) && $agent['cash_type'] != 2)) {
        $redis->set("agent_prift:agent:{$agentId}", 1, 3600);
        return true;
    }
    if ((!empty($cashType) && $agent['cash_type'] == 2) || ($agent['cash_type'] != 1 && $cashType == 0)) {
        $cashMilletType = config('app.cash_setting.cash_millet_type') ? config('app.cash_setting.cash_millet_type') : 0;
        if ($cashMilletType == 0) {
            $redis->set("agent_prift:agent:{$agentId}", 2, 3600);
            return false;
        }
        if ($cashMilletType == 1) $redis->set("agent_prift:agent:{$agentId}", 1, 3600);
        return true;
    }
}

function alog($rule, $opera, $aid = 0)
{
    $adminLog = new \app\admin\service\AdminLog();
    $path     = \think\facade\Request::path();
    $route    = \think\facade\Request::route();
    if (!empty($route)) {
        $pathArr = explode("/", $path);
        foreach ($route as $key => $value) {
            foreach ($pathArr as $k => $para) {
                if ($key == $para) {
                    unset($pathArr[$k]);
                }
                if ($value == $para) {
                    unset($pathArr[$k]);
                }
            }
        }
        $path = implode("/", $pathArr);
    }
    $adminLog->add($rule, $opera, $path, $aid);
}

/**
 * 获取多重分段奖励的下一个值
 * @param $agentId
 * @return array
 */
function getmultiple($taskset, $user_id, $task_type)
{
    $rest = Db::name('user_task')->where(['status' => 1, 'task_type' => $task_type])->find();
    switch ($rest['reward_type']) {
        case 2:
            $rewoard = '金币';
            break;
        case 3:
            $rewoard = '钻石';
            break;
        case 4:
            $rewoard = '现金';
            break;
        default:
            $rewoard = '积分';
    }
    $task_setting = json_decode($taskset, true);
    if ($rest['type'] == 1) {
        //这里要判断用户完成到哪里了，如果完成了前面的就显示后面的数据
        $finish_value = Db::name('user_task_log')->where(['task_type' => $task_type, 'user_id' => $user_id])->whereBetweenTime('create_time', date("Y-m-d"))->value('task_value');
        $arrayKey = array_keys($task_setting);
        if ($finish_value) {
            sort($arrayKey);
            $task_valueNew = current($task_setting);
            $key           = $arrayKey[0];
            foreach ($arrayKey as $k => $v) {
                if ($finish_value <= $v) {
                    $task_valueNew = $task_setting[$v];
                    $key           = $v;
                    break;
                }
            }
        } else {
            $task_valueNew = array_shift($task_setting);
            $key           = $arrayKey[0];
        }
        $rest = [
            'task_value' => $key,
            'point'      => $task_valueNew,
            'rewoard'    => $rewoard,
        ];
    } else {
        $rest = [
            'task_value' => $task_setting['task_value'],
            'point'      => $task_setting['point'],
            'rewoard'    => $rewoard,
        ];
    }
    return $rest;
}

/**
 * 判断链接是否有效
 * @param string $url
 * @return array
 */
function url_isactive($url = '')
{
    try {
        $res = get_headers($url);
    } catch (Exception $e) {
        return ['status' => 101, 'message' => '链接不合法'];
    }
    if (preg_match('/200/', $res[0])) {
        return ['status' => 200];
    } else {
        return ['status' => 102, 'message' => '链接无法正常访问'];
    }
}

/**
 * 完成任务函数
 *
 * @return array
 */
function finish_task($user_id, $task_type, $task_value, $status = 0)
{
    $taskMod = new \bxkj_module\service\Task();
    $data    = [
        'user_id'    => $user_id,
        'task_type'  => $task_type,
        'task_value' => $task_value,
        'status'     => $status ? $status : 0,
    ];
    return $taskMod->subTask($data);
}

function actPicture($link, $type = 0)
{
    $explodeArray = explode(',', $link);
    foreach (array_filter($explodeArray) as $k => $v) {
        $explodeArray[$k] = $v . '?imageView2/1/w/300/h/300';
    }
    if ($type == 0) {
        return $explodeArray;
    } else {
        return implode(",", $explodeArray);
    }
}


//对emoji表情转义
function emoji_encode($str){
    $strEncode = '';

    $length = mb_strlen($str,'utf-8');
    for ($i=0; $i < $length; $i++) {
        $_tmpStr = mb_substr($str,$i,1,'utf-8');
        if (strlen($_tmpStr) >= 4){
            $strEncode .= '[[EMOJI:'.rawurlencode($_tmpStr).']]';
        } else {
            $strEncode .= $_tmpStr;
        }
    }

    return $strEncode;
}

//对emoji表情转反义
function emoji_decode($str){
    $strDecode = preg_replace_callback('|\[\[EMOJI:(.*?)\]\]|', function($matches){
        return rawurldecode($matches[1]);
    }, $str);

    return $strDecode;
}

// 计算身份证校验码，根据国家标准GB 11643-1999
function idcard_verify_number($idcard_base)
{
    if (strlen($idcard_base) != 17) {
        return false;
    }
    //加权因子
    $factor = array(7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2);
    //校验码对应值
    $verify_number_list = array('1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2');
    $checksum           = 0;
    for ($i = 0; $i < strlen($idcard_base); $i++) {
        $checksum += substr($idcard_base, $i, 1) * $factor[$i];
    }
    $mod           = $checksum % 11;
    $verify_number = $verify_number_list[$mod];
    return $verify_number;
}

// 将15位身份证升级到18位
function idcard_15to18($idcard)
{
    if (strlen($idcard) != 15) {
        return false;
    } else {
        // 如果身份证顺序码是996 997 998 999，这些是为百岁以上老人的特殊编码
        if (array_search(substr($idcard, 12, 3), array('996', '997', '998', '999')) !== false) {
            $idcard = substr($idcard, 0, 6) . '18' . substr($idcard, 6, 9);
        } else {
            $idcard = substr($idcard, 0, 6) . '19' . substr($idcard, 6, 9);
        }
    }
    $idcard = $idcard . idcard_verify_number($idcard);
    return $idcard;
}

// 18位身份证校验码有效性检查
function idcard_checksum18($idcard)
{
    if (strlen($idcard) != 18) {
        return false;
    }
    $idcard_base = substr($idcard, 0, 17);
    if (idcard_verify_number($idcard_base) != strtoupper(substr($idcard, 17, 1))) {
        return false;
    } else {
        return true;
    }
}

//本日任务完成情况1:完成2:继续完成
function todayFinish($task,$today)
{

  switch ($task['reward_type']){
      case 1:
          $pint  = new \bxkj_module\service\UserPoint();
          $find = $pint->QueryTodayfind(['user_id'=>$today['user_id'],'type' => $today['task_type'], 'point' => $today['point']]);
          if ($find) {
              return  2;
          }else{
              return  1;
          }
          break;
      case 2:
          $millet = new Millet();
          $find = $millet->QueryTodayfind(['user_id'=>$today['user_id'],'trade_type' => $today['task_type'], 'total' => $today['point']]);
          if ($find) {
              return  2;
          }else{
              return  1;
          }
          break;
      case 3:
          $bean = new Bean();
          $find = $bean->QueryTodayfind(['user_id'=>$today['user_id'],'trade_type' => $today['task_type'], 'total' => $today['point']]);
          if ($find) {
              return  2;
          }else{
              return  1;
          }
          break;

      case 4:
          $cash = new Cash();
          $find = $cash->QueryTodayfind(['user_id'=>$today['user_id'],'trade_type' => $today['task_type'], 'total' => $today['point']]);
          if ($find) {
              return  2;
          }else{
              return  1;
          }
          break;
  }
}

/**
 * 判断客户端是否可以去登录
 * @param string $username 账号
 */
function ipCheck($username = 'admin')
{
    if (empty($username)) return false;
    if ($username == 'admin') return true;
    $ip = Request::ip();
    $res = Db::name('ip')->where(['ip_adress' => $ip])->find();
    if (empty($res)) return false;
    if ($res['status'] == 1) return true;
    if ($res['status'] == -1) return false;
    if ($res['expire_time'] < time()) return false;
    return true;
}

/**
 * @param $data 需要处理的数据
 * @param int $precision 保留几位小数
 * @return array|string
 */
function fix_number_precision($data, $precision = 2)
{
    if(is_array($data)){
        foreach ($data as $key => $value) {
            $data[$key] = fix_number_precision($value, $precision);
        }
        return $data;
    }
    if(is_numeric($data)){
        $precision = is_float($data) ? $precision : 0;
        return number_format($data, $precision, '.', '');
    }
    return $data;
}

/**
 * 替换一段文字中的手机号码
 */
function replace_phone($str = '')
{
    $str = preg_replace('/(1[34578]\d{1})\d{4}(\d{4})/', '$1****$2', $str);
    return $str;
}

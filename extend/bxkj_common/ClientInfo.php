<?php

namespace bxkj_common;

class ClientInfo
{
    protected static $info = [
        'client_type' => 'app',
        'client_object' => 'user',
        'v_code' => 0,//内部版本号
        'network_status' => '',
        'os_name' => '',
        'os_version' => '',//
        'channel' => '',//common
        'longitude' => 0,
        'latitude' => 0,
        'client_ip' => '',//客户端IP 127.0.0.1
        'brand_name' => '',//品牌，如Iphone
        'device_model' => '',//型号，如Iphone 6、m3
        'meid' => '',
        'device_type' => '',//设备类型
    ];

    public static function refresh($data)
    {
        $data = is_array($data) ? $data : [];
        foreach ($data as $key => $val) {
            if (array_key_exists($key, self::$info)) {
                if (in_array($key, ['v_code', 'longitude', 'latitude'])) {
                    self::$info[$key] = (int)$val;
                } else {
                    self::$info[$key] = $val ? (string)$val : '';
                }
            } else if ($val && $key == 'v') {
                $val = rtrim($val);
                if (preg_match('/^\d+$/', $val)) {
                    self::$info['os_name'] = 'android';
                    self::$info['v_code'] = $val;
                } else {
                    $vArr = explode('_', $val);
                    if (!empty($vArr[0])) self::$info['os_name'] = (string)$vArr[0];
                    if (!empty($vArr[1])) self::$info['v_code'] = (int)$vArr[1];
                }
            } else if ($val && $key == 'os') {
                $osArr = explode('_', $val);
                if (!empty($osArr[0])) self::$info['os_name'] = (string)$osArr[0];
                if (!empty($osArr[1])) self::$info['os_version'] = (string)$osArr[1];
            } else if ($val && $key == 'device_brand') {
                $brandArr = explode('.', $val);
                if (!empty($brandArr[0])) self::$info['brand_name'] = (string)$brandArr[0];
                if (!empty($brandArr[1])) self::$info['device_model'] = (string)$brandArr[1];
            }
        }
        self::$info['os_name'] = strtolower(self::$info['os_name']);
        self::$info['network_status'] = strtolower(self::$info['network_status']);
        self::$info['channel'] = strtolower(self::$info['channel']);
        if (!empty(self::$info['os_name'])) {
            self::$info['device_type'] = in_array(self::$info['os_name'], ['ios', 'android']) ? 'mobile' : 'pc';
        }
        if (empty(self::$info['client_ip'])) self::$info['client_ip'] = get_client_ip();
    }

    public static function refreshByParams($params)
    {
        if (is_array($params)) {
            if (isset($params['client_seri'])) {
                $arr = self::decode($params['client_seri']);
                self::refresh($arr);
            } else {
                self::refresh($params);
            }
        }
    }

    public static function refreshByUserAgent($HTTP_USER_AGENT = null, $expand = [])
    {
        $HTTP_USER_AGENT = isset($HTTP_USER_AGENT) ? $HTTP_USER_AGENT : $_SERVER['HTTP_USER_AGENT'];
        $info = user_agent($HTTP_USER_AGENT);
        if ($info) {
            $newInfo = ['client_type' => 'web', 'client_object' => 'erp'];
            if ($info['os']) $newInfo['os_name'] = $info['os'];
            if ($info['os_version']) $newInfo['os_version'] = $info['os_version'];
            if ($info['device_type']) $newInfo['device_type'] = $info['device_type'];
            if ($info['browser']) $newInfo['brand_name'] = $info['browser'];
            if ($info['browser_version']) $newInfo['device_model'] = $info['browser_version'];
            self::refresh(array_merge($newInfo, $expand));
        }
    }

    public static function get($name)
    {
        if (array_key_exists($name, self::$info)) {
            return isset(self::$info[$name]) ? self::$info[$name] : '';
        } else if ($name == 'v') {
            if (empty(self::$info['os_name']) || empty(self::$info['v_code'])) return '';
            return self::$info['os_name'] . '_' . self::$info['v_code'];
        } else if ($name == 'os') {
            if (empty(self::$info['os_name']) || empty(self::$info['os_version'])) return '';
            return self::$info['os_name'] . '_' . self::$info['os_version'];
        } else if ($name == 'device_brand') {
            if (empty(self::$info['brand_name']) || empty(self::$info['device_model'])) return '';
            return self::$info['brand_name'] . '_' . self::$info['device_model'];
        }
        return '';
    }

    public static function getClientIp()
    {
        return self::get('client_ip');
    }

    public static function has($name)
    {
        $val = self::get($name);
        return (!empty($val));
    }

    public static function getInfo($fileds = null)
    {
        if (isset($fileds)) {
            $filedArr = is_array($fileds) ? $fileds : ($fileds ? explode(',', $fileds) : []);
            $tmp = [];
            foreach ($filedArr as $item) {
                if (isset(self::$info[$item])) $tmp[$item] = self::$info[$item];
            }
            return $tmp;
        }
        return self::$info;
    }

    public static function isFull()
    {
        if (empty(self::$info['v_code']) || empty(self::$info['os_name']) || empty(self::$info['meid'])) {
            return false;
        }
        return true;
    }

    public static function encode()
    {
        $json = json_encode(self::$info);
        $data = base64_encode($json);
        $data = str_replace(array('+', '/', '='), array('-', '_', ''), $data);
        return $data;
    }

    public static function decode($string)
    {
        $data = str_replace(array('-', '_'), array('+', '/'), $string);
        $mod4 = strlen($data) % 4;
        if ($mod4) {
            $data .= substr('====', $mod4);
        }
        $json = base64_decode($data);
        return json_decode($json, true);
    }

}
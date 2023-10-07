<?php

namespace app\common\behavior;

use app\common\service\DsSession;
use bxkj_common\ClientInfo;
use think\facade\Env;

class ApiInit
{
    public function run()
    {
        $access_token = input('access_token');
        if (!empty($access_token)) {
            $res = DsSession::restore($access_token);
            if ($res) {
                $session = DsSession::get();
                if (is_array($session)) $session['client_ip'] = get_client_ip();
                ClientInfo::refresh($session);
            }
        }
        $module_path = Env::get('module_path');
        $matches = [];
        preg_match('/(v\d+)\/$/', $module_path, $matches);
        $api_v = isset($matches[1]) ? $matches[1] : '';
        $tmpArr = input();
        $tmpArr2 = copy_array(is_array($tmpArr) ? $tmpArr : [], 'v,os,device_brand,client_type,client_object,v_code,network_status,os_name,os_version,channel,longitude,latitude,
        client_ip,brand_name,device_model,meid,device_type');
        ClientInfo::refresh($tmpArr2);
        define('APP_MEID', ClientInfo::get('meid'));
        define('ACCESS_TOKEN', $access_token ? $access_token : '');
        define('APP_OS_NAME', ClientInfo::get('os_name'));
        define('APP_OS_VERSION', ClientInfo::get('os_version'));
        define('APP_CODE', ClientInfo::get('v_code'));
        define('APP_V', ClientInfo::get('v'));
        define('API_V', $api_v ? $api_v : 'v1');
        $accountName = config('app.app_setting.account_name');
        define('APP_ACCOUNT_NAME', $accountName ? $accountName : '账号');
    }
}
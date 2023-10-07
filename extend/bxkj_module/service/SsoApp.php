<?php

namespace bxkj_module\service;

use think\Db;

class SsoApp extends Service
{
    public function getAppUrlList($act, $data = array(), $app_key = null, $type = '')
    {
        $data = isset($data) ? $data : array();
        $urls = array();
        $where = [];
        $where[] = ['status', 'eq', '1'];
        $where[] = ['app_url', 'neq', ''];
        if ($type != '') $where[] = ['type', 'eq', $type];
        $where[] = ['env_name', '=', RUNTIME_ENVIROMENT];
        $app_key = isset($app_key) ? $app_key : defined('APP_KEY') ? APP_KEY : '';
        $where[] = ['app_key', 'neq', $app_key];
        $result = Db::name('sso_app')->where($where)->field('app_url,token')->select();
        $data['app_key'] = $app_key;
        $data['time'] = time();
        $data['act'] = $act;
        $message = http_build_query($data);

        for ($i = 0; $i < count($result); $i++)
        {
            $tmp = $result[$i];
            $validity = config('app.app_setting.request_validity');
            $query = http_build_query(array('message' => sys_encrypt($message, $tmp['token'], $validity)));
            $urls[] = ERP_URL.'/account/sync?' . $query;
        }

        return $urls;
    }
}
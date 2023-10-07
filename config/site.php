<?php

use \think\facade\Env as Env;

$config = [
    'page_tpl' => [],
    'page_info' => [],
    'company_name' => '',
    'site_slogan' => '',
    'company_full_name' => '',
    'contact_qq' => '',
    'contact_address' => '',
    'contact_tel' => '',
    'refresh_text' => [
        '我就是我，颜色不一样的烟火。',
        '最好的东西，往往是意料之外，偶然得来的。',
        '就算全世界与我为敌，我也会继续爱你。',
        '心有多大，舞台就有多大。',
        '把脸一直向着阳光，这样就不会见到阴影',
        '海到无边天作岸，山登绝顶我为峰。',
        '只做第一个我，不做第二个谁。',
        '幸福不是获得多了，而是在乎少了',
        '想和你喝酒是假的，想醉你怀里是真的。',
    ],
    'one_key_login' => '0',
    'login_app_url' => '',
    'login_app_key' => '',
    'login_app_secret' => '',
    'invite_code' => '0',
    'team_status' => '0',
    'weibo_url' => '',
    'idc_num' => '',
    'nc_num' => '',
    'nc_link' => '',
    'netc_num' => '',
    'location_latitude' => '',
    'location_longitude' => '',
    'tongji_code' => '',
    'receipt_name' => '',
    'receipt_account' => '',
    'receipt_bank' => '',
    'qrcode_wxapp' => ''.'/common/qrcode/bingxin.jpg',
    'qrcode_wx' => ''.'/common/qrcode/bingxin.jpg',
    'apple_store' => '',
    'qq_store' => '',
    'agent_status' => 1
];

$env = Env::get('RUN_ENV');

if (!empty($env))
{
    $path = ROOT_PATH.'config/'.$env.'/site.php';

    if (file_exists($path))
    {
        $env_config = require_once $path;

        $config = array_merge($config, $env_config);
    }
}

return $config;

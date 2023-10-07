<?php

namespace app\core\controller;

use think\facade\Request;

/**
 * 统一配置中心
 *
 * Class Config
 * @package app\core\controller
 */
class Config extends Controller
{

    public function getConfig()
    {
        $params = Request::param();

        switch ($params['type'])
        {
            case 'bingxin_socket':
                $res = $this->getSocketConfig();
                break;
        }

        return json_success($res);
    }



    protected function getSocketConfig()
    {
        $redis = config('redis.');
        $db = config('database.');
        $live_setting = config('app.live_setting');
        $product_setting = config('app.product_setting');
        $console = config('iconsole.');
		$live_setting['platform_config']['platform'] = $live_setting['platform'];
        $config = [
            'app_debug' => config('app_debug'),
            'sms_debug' => config('sms_debug'),
            'push_debug' => config('push_debug'),
             //'logger' => RUNTIME_ENVIROMENT == 'pro' ? false : true,
            'logger' => true,
            'exp_rate' => 1,
            //socket来源标识域名
            'push_host' => 'msg.socket.com',
            //十分钟算有效直播
            'live_effective_time'=>config('app.app_setting.live_effective_time') ?: 600,
            'is_ssl' => false,
            'ssl' => [
                'local_cert' => '/www/server/panel/vhost/cert/api.ihuanyu.cn/fullchain.pem',
                'local_pk' => '',
                'verify_peer' => false,
            ],

            //守护配置
            'guard' => [
                'redis_key' => 'BG_GUARD:',
                'max_seat' => 15,
                'seven_time' => 604800,
                'month_time' => 2592000,
                'gift_ids' => ['131','132']
            ],
            //pk相关配置
            'pk' => [
                'pk_duration' => 300,
                'ac_time' => 180,
            ],

            //活动相关配置
            'activity' => [
                'redis_key' => 'cache:activity_config',
            ],

            'redis' => $redis,

            'db' => $db,

            //相关设置
            'setting' => [
                'barrage_fee' => $live_setting['barrage_fee']?:500,
                'credit_score' => $live_setting['credit_score']?:60,
                'horn_fee' => $live_setting['horn_fee']?:20,
                'rank_golden_light' => $live_setting['rank_golden_light']?:32,
                'barrage_level' => $live_setting['barrage_level']?:5,
                'mike_level' => $live_setting['mike_level']?:5,
                'bag_prifit_status' => $live_setting['bag_prifit_status']?:0,
                'message_level' => $live_setting['message_level']?:0,
            ],
            'voice_setting' => [
                'is_anchor' => $live_setting['voice_setting']['is_anchor'],
                'apply_time' => $live_setting['voice_setting']['apply_time'],
                'shut_time' => $live_setting['voice_setting']['shut_time'],
            ],

            //游戏配置
            'game' => [
                'set_rate' => 3,
                'broad_cast_amount' => 100000, //当用户投注获得奖励满足此数时则向所有客户端广播信息
            ],

            //名称配置
            'unit' => [
                'income_name' => $product_setting['millet_name'],
                'coin_name' => $product_setting['bean_name'],
                'app_name' => $product_setting['name'],
                'account_name' => config('app.app_setting.account_name'),
            ],

            //开播依赖配置
            'live' => $live_setting['platform_config'],
        ];

        $data = array_merge($config, $console);

        return $data;


    }


}
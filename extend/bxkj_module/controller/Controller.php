<?php

namespace bxkj_module\controller;

use think\facade\Env;

class Controller extends \think\Controller
{
    protected $appKey = null;

    public function __construct()
    {
        if (empty(RUNTIME_ENVIROMENT)) define('RUNTIME_ENVIROMENT', Env::get('RUN_ENV'));

        parent::__construct();

        $url_config = config('app.system_deploy');
        if(!defined('DEPLOY_MODE')) define('DEPLOY_MODE', $url_config['deploy_mode']);
        if(!defined('BASE_CORE_URL'))define('BASE_CORE_URL', isset($url_config['base_core_url']) ? $url_config['base_core_url'] : '');
        if(!defined('CORE_URL'))define('CORE_URL', $url_config['core_service_url']);
        if(!defined('API_URL'))define('API_URL', $url_config['api_service_url']);
        if(!defined('H5_URL'))define('H5_URL', $url_config['h5_service_url']);
        if(!defined('AGENT_URL'))define('AGENT_URL', $url_config['agent_service_url']);
        if(!defined('PUSH_URL'))define('PUSH_URL', $url_config['push_service_url']);
        if(!defined('FX_URL'))define('FX_URL', $url_config['fx_service_url']);
        if(!defined('NODE_URL'))define('NODE_URL',  isset($url_config['node_service_url']) ? $url_config['node_service_url'] : '');
        if(!defined('RECHARGE_URL'))define('RECHARGE_URL', $url_config['recharge_service_url']);
        if(!defined('ERP_URL'))define('ERP_URL', $url_config['erp_service_url']);
        if(!defined('TK_URL'))define('TK_URL', $url_config['taoke_api_url']);
        if(!defined('MALL_URL'))define('MALL_URL', $url_config['mall_url']);
        if(!defined('WXAPI_URL'))define('WXAPI_URL', '');
        $app_info = config('app.product_setting');
        if(!defined('APP_NAME'))define('APP_NAME', $app_info['name']);
        if(!defined('APP_SLOGAN'))define('APP_SLOGAN', $app_info['slogan']);
        if(!defined('APP_BEAN_NAME'))define('APP_BEAN_NAME', $app_info['bean_name'] ?: '钻石');
        if(!defined('APP_MILLET_NAME'))define('APP_MILLET_NAME', $app_info['millet_name'] ?: '金币');
        if(!defined('APP_BALANCE_NAME'))define('APP_BALANCE_NAME', $app_info['balance_name'] ?: '金币');
        if(!defined('APP_SETTLEMENT_NAME'))define('APP_SETTLEMENT_NAME', isset($app_info['settlement_name']) ? $app_info['settlement_name'] : '结算');
        if(!defined('APP_SERVICE_TEL'))define('APP_SERVICE_TEL', $app_info['service_tel']);
        if(!defined('APP_PREFIX_NAME'))define('APP_PREFIX_NAME', $app_info['prefix_name']);
        if(!defined('APP_REWARD_NAME'))define('APP_REWARD_NAME', isset($app_info['reward_name']) ? $app_info['reward_name'] : '积分');
        if(!defined('APP_CASH_NAME'))define('APP_CASH_NAME', isset($app_info['cash_name']) ? $app_info['cash_name'] : '现金');


        $app_setting = config('app.app_setting');
        if(!defined('USER_NAME_PREFIX'))define('USER_NAME_PREFIX', $app_setting['nickname_prefix']);
        if(!defined('NICK_NAME_PREFIX'))define('NICK_NAME_PREFIX', $app_setting['account_prefix']);
        //if(!defined('WXAPI_URL'))define('APP_ACCOUNT_NAME', $app_setting['account_name'] ?: 'ID');
        if(!defined('PAGE_LIMIT'))define('PAGE_LIMIT', $app_setting['page_limit']);

        $site = config('site.');
        if(!defined('TONGJI_CODE'))define('TONGJI_CODE', $site['tongji_code']);

//        if(!defined('WXAPI_URL'))define('ACCOUNT_PREFIX', $app_info['account_prefix']);
        isset($this->appKey) && define('APP_KEY', $this->appKey);

        //注册推荐服务
        $console = config('iconsole.');

        RUNTIME_ENVIROMENT !== 'pro' && \bxkj_common\Console::init($console);
    }
}

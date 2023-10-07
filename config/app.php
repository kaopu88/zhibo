
<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

// +----------------------------------------------------------------------
// | 应用设置
// +----------------------------------------------------------------------

error_reporting(E_ALL ^ E_NOTICE);

use think\facade\Env as Env;

$config = [
    // 应用名称
    'app_name' => '',
    // 应用地址
    'app_host' => '',
    // 是否支持多模块
    'app_multi_module' => true,
    // 入口自动绑定模块
    'auto_bind_module' => false,
    // 注册的根命名空间
    'root_namespace' => [],
    // 默认输出类型
    'default_return_type' => 'html',
    // 默认AJAX 数据返回格式,可选json xml ...
    'default_ajax_return' => 'json',
    // 默认JSONP格式返回的处理方法
    'default_jsonp_handler' => 'jsonpReturn',
    // 默认JSONP处理方法
    'var_jsonp_handler' => 'callback',
    // 默认时区
    'default_timezone' => 'Asia/Shanghai',
    // 是否开启多语言
    'lang_switch_on' => false,
    // 默认全局过滤方法 用逗号分隔多个
    'default_filter' => '',
    // 默认语言
    'default_lang' => 'zh-cn',
    // 应用类库后缀
    'class_suffix' => false,
    // 控制器类后缀
    'controller_suffix' => false,

    // +----------------------------------------------------------------------
    // | 模块设置
    // +----------------------------------------------------------------------

    // 默认模块名
    'default_module' => 'home',
    // 禁止访问模块
    'deny_module_list' => ['common'],
    // 默认控制器名
    'default_controller' => 'Index',
    // 默认操作名
    'default_action' => 'index',
    // 默认验证器
    'default_validate' => '',
    // 默认的空模块名
    'empty_module' => '',
    // 默认的空控制器名
    'empty_controller' => 'Error',
    // 操作方法前缀
    'use_action_prefix' => false,
    // 操作方法后缀
    'action_suffix' => '',
    // 自动搜索控制器
    'controller_auto_search' => false,

    // +----------------------------------------------------------------------
    // | URL设置
    // +----------------------------------------------------------------------

    // PATHINFO变量名 用于兼容模式
    'var_pathinfo' => 's',
    // 兼容PATH_INFO获取
    'pathinfo_fetch' => ['ORIG_PATH_INFO', 'REDIRECT_PATH_INFO', 'REDIRECT_URL'],
    // pathinfo分隔符
    'pathinfo_depr' => '/',
    // HTTPS代理标识
    'https_agent_name' => '',
    // IP代理获取标识
    'http_agent_ip' => 'X-REAL-IP',
    // URL伪静态后缀
    'url_html_suffix' => 'html',
    // URL普通方式参数 用于自动生成
    'url_common_param' => false,
    // URL参数方式 0 按名称成对解析 1 按顺序解析
    'url_param_type' => 0,
    // 是否开启路由延迟解析
    'url_lazy_route' => false,
    //是否开启路由缓存
    'route_check_cache' => false,
    // 是否强制使用路由
    'url_route_must' => false,
    // 合并路由规则
    'route_rule_merge' => false,
    // 路由是否完全匹配
    'route_complete_match' => false,
    // 使用注解路由
    'route_annotation' => false,
    // 域名根，如thinkphp.cn
    'url_domain_root' => '',
    // 是否自动转换URL中的控制器和操作名
    'url_convert' => true,
    // 默认的访问控制器层
    'url_controller_layer' => 'controller',
    // 表单请求类型伪装变量
    'var_method' => '_method',
    // 表单ajax伪装变量
    'var_ajax' => '_ajax',
    // 表单pjax伪装变量
    'var_pjax' => '_pjax',
    // 当前操作名缓存5分钟(get请求)
    'request_cache' => false,//'__URL__',
    // 请求缓存有效期
    'request_cache_expire' => 60,
    // 全局请求缓存排除规则
    'request_cache_except' => [],
    // 默认跳转页面对应的模板文件
    'dispatch_success_tmpl' => Env::get('think_path') . 'tpl/dispatch_jump.tpl',
    'dispatch_error_tmpl' => Env::get('think_path') . 'tpl/dispatch_jump.tpl',
    // 异常页面的模板文件
    'exception_tmpl' => ROOT_PATH . 'extend/bxkj_module/view/public/think_exception.tpl',
    // 错误显示信息,非调试模式有效
    'error_message' => '页面错误！请稍后再试～',
    // 显示错误信息
    'show_error_msg' => false,
    // 异常处理handle类 留空使用 \think\exception\Handle
    'exception_handle' => '\\bxkj_module\\exception\\Handle',


    // +----------------------------------------------------------------------
    // | 自定义设置
    // +----------------------------------------------------------------------

    //应用调试模式
    'app_debug' => true,
    //应用Trace
    'app_trace' => false,
    //权限检查开关
    'auth_on' => false,
    //推送调试开关
    'push_debug' => false,
    //短信调试开关
    'sms_debug' => false,
    //苹果上架开关
    'ios_debug' => false,
    //app配置
    'app_setting' => [
        'exp_rate' => 1,
        'millet_rate' => 1,
        'page_limit' => 10,
        //官方帐户ID
        'helper_id' => 10000,
        'account_prefix' => 'bxkj',
        'nickname_prefix' => 'BX_',
        'account_name' => 'ID',
        //用于数据加密
        'data_auth' => 'bx_system_data_auth',
        //用于数据签名
        'data_token' => 'bx_system_data_token',
        //定时任务签名
        'timer_token' => 'bx_system_timer_token',
        //初始化安全key
        'app_secret_key' => '2bc29158f230db6c2a7a6712e57de6e4b48116f2',
        //默认请求过期时间
        'request_validity' => 600,
        //收费视频录制时长 秒
        'charge_video_duration' => 120,
        //修改昵称限制时间 秒
        'renick_limit_time' => 2592000,
        'loss_after_months' => 2,
        'loss_min_bean' => 100000,
        //主播直播最短有效时长 s
        'live_effective_time' => 10,
        //默认用户信用分
        'default_credit_score' => 100,
        //认证粉丝数
        'creation_fans_num' => 1000,
        //认证视频数
        'creation_film_num' => 10,
        //认证是否有举报记录
        'creation_report_record' => 1,
    ],
    //系统部署配置
    'system_deploy' => [
        //部署方式
        'deploy_mode' => 'single',
        //供内部访问
        'base_core_url' => 'http://127.0.0.1/'.'/core',
        //供外部访问
        'core_service_url' => '',
        'api_service_url' => '',
        'h5_service_url' => '',
        'agent_service_url' => '',
        'push_service_url' => '',
        'node_service_url' => '',
        'recharge_service_url' => '',
        'erp_service_url' => '',
        'promoter_service_url' => '',
        'taoke_api_url' => '',
        'mall_url' => '',
    ],
    //权限配置
    'auth_config' => [
        'auth_group' => 'admin_group',
        'auth_group_access' => 'admin_group_access',
        'auth_rule' => 'admin_rule'
    ],
    //预置的正则表达式
    'regex_pattern' => [
        'require' => '/\S+/',
        'email' => '/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/',
        'url' => '/^(https?:\/\/)?(((www\.)?[a-zA-Z0-9_-]+(\.[a-zA-Z0-9_-]+)?\.([a-zA-Z]+))|(([0-1]?[0-9]?[0-9]|2[0-5][0-5])\.([0-1]?[0-9]?[0-9]|2[0-5][0-5])\.([0-1]?[0-9]?[0-9]|2[0-5][0-5])\.([0-1]?[0-9]?[0-9]|2[0-5][0-5]))(\:\d{0,4})?)(\/[\w- .\/?%&=]*)?$/i',
        'ip' => '/^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$/',
        'currency' => '/^[0-9]+(\.[0-9]{1,2})?$/',
        'number' => '/^\d+$/',
        'zip' => '/^\d{6}$/',
        'integer' => '/^[-\+]?\d+$/',
        'double' => '/^[-\+]?\d+(\.\d+)?$/',
        'english' => '/^[A-Za-z]+$/',
        'phone' => '/^1[3-8]{1}\d{9}$/',
        'no_blank' => '/^\S+$/',
        'mark' => '/^[A-Z0-9_a-z\:]+$/',
        'nat_num' => '/^[\+\-]?\d+(\.\d+)?$/',
        'date' => '/^\d{4}\-\d{2}\-\d{2}$/',
        'time' => '/^\d{2}\:\d{2}$/'
    ],
    //代理设置
    'agent_setting' => [
        "agent_name" => "代理商",
        "promoter_name" => "推广员",
        'agent_status' => '1',
        'close_info' => ''
    ],
    //地图配置
    'map_setting' => [
        'platform' => 'amap',
        'web_service_key' => '',
        'js_service_key' => '',
    ],
    //搜索配置
    'aomy_search' => [
        'platform' => 'aliyun',
        'access_key' => '',
        'secret' => '',
        'region' => '',
        'host' => '',
        'key_type' => '',
        'debug' => false,
        'search_app' => '',
        'search_suggest' => ''
    ],
    'wx_appid' => 'wx9113b3896bb530f1',
    'wx_appsecret' => 'b39a5daadbf76ce046d99bf580d41535',
    //自媒体平台
    'media_platform' => [
        //微信公众号
        'wx_wap' => [
            'app_id' => 'wx9113b3896bb530f1',
            'secret_key' => 'b39a5daadbf76ce046d99bf580d41535',
        ],

        //微信小程序
        'wx_app' => [
            'app_id' => 'wx9113b3896bb530f1',
            'secret_key' => 'b39a5daadbf76ce046d99bf580d41535',
        ],

        //QQ应用
        'qq' => [
            'app_id' => '',
            'secret_key' => '',
        ],
    ],
    'cash_setting' =>
        array (
            'cash_on' => '0',
            'agent_cash_on' => '1',
            'cash_type' => '0',
            'cash_millet_type' => '0',
            'cash_proportion' => '0.08',
            'agent_cash_fee' => '0',
            'agent_cash_taxes' => '0',
            'cash_rate' => '0.05',
            'cash_user_rate' => '0.05',
            'cash_fee' => '0',
            'cash_taxes' => '0',
            'cash_min' => '100',
            'cash_monthlimit' => '10',
            'exchange_percent' => '',
            'exchange_integral' => '',
        ),
    //用户名称字段
    'username_order' => 'remark_name,realname,real_name,nickname,nick_name,username',
    //测试账号列表
    'test_user' => ['10000', '11000', '11001'],
    //直播配置
    'live_setting' => [
        'platform' => 'tencent',
        'service_host' => '',
        'message_server' => [
            'host' => 'ws://39.98.144.113',
            'port' => '5565',
        ],
        'game_server' => [
            'host' => 'ws://39.98.144.113',
            'port' => '5262',
        ],
        'platform_config' => [
            'secret_id' => '1300599505',
            'access_key' => 'b843d52c0be027508346c32b845a3946',
            'secret_key' => 'b843d52c0be027508346c32b845a3946',
            'push' => 'livepush.myqcloud.com',
            'pull' => 'push.hongshou.top',
            'snapshort' => '',
            'live_space_name' => '',
            'img_space_name' => '',
            'stream_prefix' => 20162,
            'ext' => 43200,
            'pull_protocol' => 'hdl',
        ],
        'avatar_set_cover' => true,
        'live_manage_sum' => 6,
        'shutspeak_expire_time' => 900,
        'robot' =>['max' => 30, 'min' => 10],
        'validate_level' => false,
        'validate_level_value' => 1,
        'validate_black' => false,
        'validate_banned' => false,
        'validate_banned_value' => 3,
        'validate_verified' => true,
        'validate_live_status' => true,
        'user_live' => [
                'front_status' => '0',
                'verify' => '1',
                'open_anchor_type' => '0',
                'person_apply' => '1',
                'agent_apply' => '1',
        ],
        'voice_setting' => [
            'status' => '0',
            'is_anchor' => '0',
            'shut_time' => 300,
            'apply_time' => 300,
        ],
    ],
    //点播配置
    'vod' => [
        'platform' => 'tencent',
        'platform_config' => [
            'secret_Id' => '',
            'secret_key' => '',
            'end_point' => '',
            'time_out' => 15,
            'sign_method' => 'HmacSHA256',
            'source_context' => 'app',
            'one_time_valid' => 0,
            'procedure' => 2,
            'region' => '',
            'token_url' => 'vod.api.qcloud.com/v2/index.php',
            'ProcessMedia' => 0,
        ],
        //审核配置
        'audit_config' => [
            //1待审核2审核通过
            'status' => '1',
            'free_user' => [],//免审核用户
            'verified_status' => '1',//实名认证
            'creation_status' => '2',//创作号
            'isvirtual_status' => '1',
            'credit_score' => null,//达到多少信用分免审核
        ],

        //转码设置
        'TranscodeTaskSet' => [

        ],
        //音乐转码设置
        'TranscodeTaskSet_Music' => [
            'Definition' => 1010,
        ],
        //转动图任务
        'AnimatedGraphicTaskSet' => [
            [
                'Definition' => 21437,
                'StartTimeOffset' => 1,
                'EndTimeOffset' => 3
            ]
        ],
        //封面设置
        'CoverBySnapshotTaskSet' => [
            [
                'Definition' => 22363,
                'PositionType' => 'Time',
                'PositionValue' => 0
            ]
        ]
    ],
    //产品配置
    'product_setting' => [
        'prefix_name' => '秉信',
        'name' => '秉信短视频',
        'slogan' => '秉信助你发现精彩',
        'logo' => '',
        'service_tel' => '0551-8244288',
        'descr' => '秉信短视频是一款专注于年轻人的原创短视频社交分享应用。在这里，每位用户都可以用个性的视角记录生活，表达自我，发现更多精彩。',
        'bean_name' => '',
        'millet_name' => '',
        'balance_name' => '',
        'invite_bean' => 0,//奖励邀请人金币数
        'invite_millet' => 0,//奖励邀请人金币数
        'invite_exp' => 0,//奖励邀请人经验值
        'reg_bean' => 0,//奖励被邀请人金币数
    ],
    //主播任务配置
    'task_setting' => [
        'live_duration' => ['min' => 7200, 'max' => 10800, 'title' => '直播时长'],
        'light_num' => ['min' => 2000, 'max' => 5000, 'title' => '点亮次数'],
        'gift_profit' => ['min' => 500000, 'max' => 1000000, 'title' => '直播收益'],
        'new_fans' => ['min' => 3, 'max' => 10, 'title' => '新增粉丝'],
        'pk_win_num' => ['min' => 1, 'max' => 4, 'title' => 'PK胜场']
    ],




    'film_live' => [
        'num' => 4, //电影直播数量
        'mode' => false,//方式0手动1自动
        'span_time' => 60, //自动方式下结束一场与下一场间隔时间
        'is_loop' => true,//是否循环播放
        'is_notice' => true,//结束时是否显示预告信息
        'notice_mode' => false,//预告信息方式，默认为预设信息
        'notice_msg' => '本场已结束'//预设信息
    ],

    //敏感词过滤配置
    'sensitive' => [
        //默认配置
        'default_sensitive_config' => [
            'filter_on' => false,
            'filter_mode' => 'replace',
            'filter_word' => '*',
            'set_tree_path' => '',
            'set_tree_mode' => 'file',
            'filter_match_mode' => true,
        ],

        //直播配置
        'live_sensitive_config' => [
            'filter_on' => true,
            'filter_mode' => 'filter',
        ],

        //评论配置
        'comment_sensitive_config' => [
            'filter_on' => false,
            'filter_mode' => 'filter',
        ],

        //发布视频配置
        'publish_sensitive_config' => [
            'filter_on' => false,
            'filter_mode' => 'filter',
        ],

        //返馈配置
        'feedback_sensitive_config' => [
            'filter_on' => true,
            'filter_mode' => 'filter',
        ],
    ],

    'default_wxcomment_audit_status' => '0',


    //sso同步配置
    'sync_app_type' => '',
    'sync_token' => '',
    'sync_return_tpl' => ROOT_PATH . 'extend/bxkj_module/view/public/sync_return.tpl',


    //node域名及端口号默认配置
    'node_config' => [
        'site_domain' => '127.0.0.1',
        'debug' => false,
        'debug_api' => false,
        'port' => 3002,
        'queues' => [
            'push' => [
                'name' => 'push',
                'hostname' => '39.98.144.113/push',
                'timeout' => 800,
                'timeout_code' => 10,
                'retryMaxNum' => 0,
                'port' => 80
            ]
        ],
        'email' => [
            'host' => "smtp.163.com",
            'secureConnection' => true,
            'port' => 465,
            'auth' => [
                'user' => '',
                'pass' => ''
            ]
        ],
    ],
];

$env = Env::get('RUN_ENV');

if (!empty($env))
{
    $path = ROOT_PATH.'config/'.$env.'/app.php';

    if (file_exists($path))
    {
        $env_config = require_once $path;

        $config2 = array_merge($config, $env_config);

        $config2['vod'] = array_merge($config['vod'], $env_config['vod']);

        $config = $config2;
    }
}

return $config;
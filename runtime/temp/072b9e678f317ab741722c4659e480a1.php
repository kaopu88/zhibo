<?php /*a:7:{s:60:"/www/wwwroot/zhibb/application/admin/view/sys_config/app.tpl";i:1694144759;s:61:"/www/wwwroot/zhibb/application/admin/view/public/base_nav.tpl";i:1693212818;s:61:"/www/wwwroot/zhibb/application/admin/view/public/jsconfig.tpl";i:1602499828;s:61:"/www/wwwroot/zhibb/application/admin/view/public/main_top.tpl";i:1593518960;s:59:"/www/wwwroot/zhibb/application/admin/view/public/toggle.tpl";i:1592625950;s:64:"/www/wwwroot/zhibb/application/admin/view/components/tab_nav.tpl";i:1592356812;s:65:"/www/wwwroot/zhibb/application/admin/view/components/work_pop.tpl";i:1567562304;}*/ ?>
<!DOCTYPE html>
<html>
<head lang="en">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=Edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <?php if(!(empty($page_app_name) || (($page_app_name instanceof \think\Collection || $page_app_name instanceof \think\Paginator ) && $page_app_name->isEmpty()))): ?>
        <meta name="application-name" content="<?php echo htmlentities($page_app_name); ?>">
    <?php endif; if(!(empty($page_description) || (($page_description instanceof \think\Collection || $page_description instanceof \think\Paginator ) && $page_description->isEmpty()))): ?>
        <meta name="description" content="<?php echo htmlentities($page_description); ?>">
    <?php endif; if(!(empty($page_keywords) || (($page_keywords instanceof \think\Collection || $page_keywords instanceof \think\Paginator ) && $page_keywords->isEmpty()))): ?>
        <meta name="keywords" content="<?php echo htmlentities($page_keywords); ?>">
    <?php endif; ?>
    
    <title><?php echo htmlentities($page_title); ?></title>
    <link rel="stylesheet" type="text/css" href="/static/vendor/icomoon/style.css?v=<?php echo config('upload.resource_version'); ?>"/>
    <link rel="stylesheet" type="text/css" href="/static/vendor/webuploader/webuploader.css?v=<?php echo config('upload.resource_version'); ?>"/>
    <link rel="stylesheet" type="text/css" href="/static/vendor/flatpickr/flatpickr.min.css?v=<?php echo config('upload.resource_version'); ?>"/>
    <link rel="stylesheet" type="text/css" href="/static/vendor/fancybox/jquery.fancybox.css?v=<?php echo config('upload.resource_version'); ?>"/>
    <link rel="stylesheet" type="text/css" href="/static/smart_admin/css/smart_admin.css?v=<?php echo config('upload.resource_version'); ?>"/>
    <link rel="stylesheet" type="text/css" href="/static/common/css/public.css?v=<?php echo config('upload.resource_version'); ?>"/>
    <link rel="stylesheet" type="text/css" href="<?php echo ('/static/'.strtolower(\think\facade\Request::module()).'/css'); ?>/public.css?v=<?php echo config('upload.resource_version'); ?>"/>
    <link rel="stylesheet" type="text/css" href="/bx_static/custom.css"/>
    <style>
        .main_top_logo a{
            text-align: center;
            line-height: 61px;
            font-size: 22px;
        }
        .main_top_logo span{
            color: #fff;
        }
        .main_top_logo a:link{
            text-decoration:none;
        }
        .main_top_logo a:visited{
            text-decoration:none;
        }
        .main_top_logo a:hover{
            text-decoration:none;
        }
        .main_top_logo a:active{
            text-decoration:none;
        }
    </style>
    
    <link rel="stylesheet" type="text/css" href="/static/vendor/smart/smart_region/region.css?v=<?php echo config('upload.resource_version'); ?>"/>
    <link rel="stylesheet" type="text/css" href="/static/vendor/cropper/cropper.min.css?v=<?php echo config('upload.resource_version'); ?>"/>

    <script>
    var WEB_CONFIG = {
        static_path: '/static',
        vendor_path: '/static/vendor',
        'runtime_enviroment': '<?php echo RUNTIME_ENVIROMENT; ?>',
        ueditor_controller: '<?php echo url("admin/ueditor/controller"); ?>',
        get_qiniu_token: '<?php echo url("admin/common/get_qiniu_token"); ?>',//上传文件签名地址
        img_crop_url: '<?php echo url("admin/common/img_crop"); ?>',
        page_var: 'page',
        update_data_version: '<?php echo url("admin/setting/update_data_version"); ?>',
        exchange_bean_quan: '<?php echo url("admin/common/exchange_bean_quan"); ?>',
        select_agents: '<?php echo url("admin/user_transfer/select_agents"); ?>',//选择
        send_sms_url: '<?php echo url("admin/common/send_sms"); ?>',
        user_reg: '<?php echo url("admin/user/reg"); ?>',
        user_role: '<?php echo url("admin/user_roler/setting"); ?>',
        change_work_status: '<?php echo url("admin/personal/change_work_status"); ?>',
        change_work_sms_status: '<?php echo url("admin/personal/change_work_sms_status"); ?>',
        get_unread_num: '<?php echo url("admin/personal/get_unread_num"); ?>',
        get_promoter_cons_trend: '<?php echo url("admin/promoter/get_cons_trend"); ?>',
        get_recharge_consume_trend: '<?php echo url("admin/index/get_recharge_consume_trend"); ?>',
        get_video_info:'<?php echo url("admin/common/get_video_info"); ?>'
    };
    var _domConfig = {};
    var bean_name = '<?php echo APP_BEAN_NAME; ?>';
</script>
    <script src="/static/vendor/jquery.min.js?v=<?php echo config('upload.resource_version'); ?>"></script>
    <script src="/static/vendor/layer/layer.js?v=<?php echo config('upload.resource_version'); ?>"></script>
    <script src="/static/vendor/jquery.nicescroll.min.js?v=<?php echo config('upload.resource_version'); ?>"></script>
    <script src="/static/vendor/jquery.cookie.js?v=<?php echo config('upload.resource_version'); ?>"></script>
    <script src="/static/vendor/flatpickr/flatpickr.min.js?v=<?php echo config('upload.resource_version'); ?>"></script>
    <script type="text/javascript" src="/static/vendor/wcs-js-sdk-1.0.10/dist/wcs.min.js"></script>
    <script type="text/javascript" src="/static/vendor/webuploader/webuploader.js?v=<?php echo config('upload.resource_version'); ?>"></script>
    <script src="/static/vendor/qiniu.min.js?v=<?php echo config('upload.resource_version'); ?>"></script>
    <script src="/static/vendor/fancybox/jquery.fancybox.pack.js?v=<?php echo config('upload.resource_version'); ?>"></script>
    <script src="/static/vendor/smart/smart.bundle.js?v=<?php echo config('upload.resource_version'); ?>"></script>
    <script src="/static/smart_admin/js/smart_admin.bundle.js?v=<?php echo config('upload.resource_version'); ?>"></script>
    <script src="/static/common/js/public.js?v=<?php echo config('upload.resource_version'); ?>"></script>
    <script src="<?php echo ('/static/'.strtolower(\think\facade\Request::module()).'/js'); ?>/public.js?v=<?php echo config('upload.resource_version'); ?>"></script>
    
    <script src="/static/vendor/smart/smart_region/region.js?v=<?php echo config('upload.resource_version'); ?>"></script>
    <script src="/static/vendor/cropper/cropper.min.js?v=<?php echo config('upload.resource_version'); ?>"></script>

</head>
<body>
<div class="main">
    
    <div class="main_nav">
        <div class="flex_between">
            <div class="flex">
                <div class="main_top_logo">
                    <a href="<?php echo url('index/index'); ?>">
                        <span><?php echo config('site.company_full_name'); ?></span>
                    </a>
                </div>
                <div class="icon_more"></div>
                <div class="menu_icon" title="主菜单"></div>
            </div>
            <ul class="tool_list">
                <li><a title="网站首页" target="_self" href="<?php echo url('index/index'); ?>"><span class="icon-home"></span></a>
                </li>
                <li>
                    <a poplink="work_box" title="任务设置" href="javascript:;">
                        <span class="icon-light-bulb"></span>&nbsp;<span style="font-size: 12px;"
                                                                            class="badge_work_num">0</span>
                    </a>
                </li>
                <li><a title="退出" ajax-confirm="是否确认退出？" confirm ajax="get" href="<?php echo url('account/logout'); ?>"><span
                                class="icon-exit"></span></a></li>
                <li>
                    <!--  <a title="消息" href="javascript:;">
                            <span class="icon-envelope"></span>
                            <span class="message_badge">0</span>
                        </a>-->
                    <ul class="message_list">
                        <li><a href="javascript:;"><span class="icon-remove"></span>清空消息</a></li>
                        <li><a href="javascript:;"><span class="icon-plus"></span>查看更多</a></li>
                    </ul>
                </li>

                <li class="avatar_link">
                    <a title="<?php echo htmlentities(user_name($admin)); ?>" href="javascript:;">
                        <div class="main_top_avatar"><img
                                    src="<?php echo img_url($admin['avatar'],'200_200','admin_avatar'); ?>"/></div>
                    </a>
                    <ul class="avatar_list">
                        <li><a href="<?php echo url('personal/base_info'); ?>"><?php echo htmlentities(user_name($admin)); ?></a></li>
                        <li><a href="<?php echo url('personal/change_pwd'); ?>">修改密码</a></li>
                        <li><a poplink="work_box" href="javascript:;">任务设置</a></li>
                        <li><a ajax-confirm ajax="get" href="<?php echo url('account/logout'); ?>">退出</a></li>
                    </ul>
                </li>
            </ul>
        </div>
        
    </div>
    <div class="main_top_content">
        <div class="main_top_progress"></div>
        <div class="main_top_bar">
            <ul class="nav_list">
                <?php if(is_array($admin_tree[0]['children']) || $admin_tree[0]['children'] instanceof \think\Collection || $admin_tree[0]['children'] instanceof \think\Paginator): $i = 0; $__LIST__ = $admin_tree[0]['children'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$menu1): $mod = ($i % 2 );++$i;?>
                    <li class="pitch"><a class="<?php echo htmlentities($menu1['current']); ?>" target="<?php echo htmlentities($menu1['target']); ?>"
                                         href="<?php echo !empty($menu1['url']) ? htmlentities($menu1['menu_url']) : htmlentities($menu1["children"][0]["children"][0]["menu_url"]); ?>">
                            <?php echo htmlentities($menu1['name']); ?><span unread-types="<?php echo htmlentities($menu1['badge']); ?>" class="badge_unread">0</span>
                        </a></li>
                <?php endforeach; endif; else: echo "" ;endif; ?>
            </ul>
        </div>
    </div>
    <div class="icon_side"></div>
    <div class="main_left">
        <div class="sidebar_nav">
            <a href="javascript:;" class="current">菜单导航</a>
        </div>
        <div class="sidebar_box">
            <ul class="sidebar">
                <?php if(is_array($admin_tree[1]['children']) || $admin_tree[1]['children'] instanceof \think\Collection || $admin_tree[1]['children'] instanceof \think\Paginator): $i = 0; $__LIST__ = $admin_tree[1]['children'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$menu2): $mod = ($i % 2 );++$i;?>
                    <li>
                        <div class="sidebar_title">
                            <a target="<?php echo htmlentities($menu2['target']); ?>" class="<?php echo htmlentities($menu2['current']); ?>" href="<?php echo htmlentities($menu2['menu_url']); ?>">
                                <?php if(!(empty($menu2['icon']) || (($menu2['icon'] instanceof \think\Collection || $menu2['icon'] instanceof \think\Paginator ) && $menu2['icon']->isEmpty()))): ?><span class="sidebar_menu_icon"><?php echo htmlentities($menu2['icon']); ?></span>
                                <?php endif; ?>
                                <?php echo htmlentities($menu2['name']); ?><span unread-types="<?php echo htmlentities($menu2['badge']); ?>" class="badge_unread">0</span></a>
                            <div class="sidebar_icon"><span></span></div>
                        </div>
                        <ul class="sidebar_childrens sidebar_up">
                            <?php if(is_array($menu2['children']) || $menu2['children'] instanceof \think\Collection || $menu2['children'] instanceof \think\Paginator): $i = 0; $__LIST__ = $menu2['children'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$menu3): $mod = ($i % 2 );++$i;?>
                                <li><a target="<?php echo htmlentities($menu3['target']); ?>" class="<?php echo htmlentities($menu3['current']); ?>" href="<?php echo htmlentities($menu3['menu_url']); ?>">
                                    <?php if(!(empty($menu3['icon']) || (($menu3['icon'] instanceof \think\Collection || $menu3['icon'] instanceof \think\Paginator ) && $menu3['icon']->isEmpty()))): ?><span class="sidebar_menu_icon"><?php echo htmlentities($menu3['icon']); ?></span>
                                    <?php endif; ?>
                                    <?php echo htmlentities($menu3['name']); ?><span unread-types="<?php echo htmlentities($menu3['badge']); ?>" class="badge_unread">0</span></a>
                                </li>
                            <?php endforeach; endif; else: echo "" ;endif; ?>
                        </ul>
                    </li>
                <?php endforeach; endif; else: echo "" ;endif; ?>
            </ul>
        </div>
    </div>


    <div class="main_right">
        <div class="main_right_content">
            <div class="catalog">
  <div class="top_catalog"></div>
  <div class="sub_catalog"></div>
</div>


            
    <div class="pa_20 p-0 bg_normal">
        <ul class="tab_nav">
    <?php if(is_array($admin_tree[3]['children']) || $admin_tree[3]['children'] instanceof \think\Collection || $admin_tree[3]['children'] instanceof \think\Paginator): $i = 0; $__LIST__ = $admin_tree[3]['children'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$menu4): $mod = ($i % 2 );++$i;?>
        <li><a target="<?php echo htmlentities($menu4['target']); ?>" class="<?php echo htmlentities($menu4['current']); ?>" href="<?php echo htmlentities($menu4['menu_url']); ?>"><?php echo htmlentities($menu4['name']); ?><span unread-types="<?php echo htmlentities($menu4['badge']); ?>" class="badge_unread">0</span></a></li>
    <?php endforeach; endif; else: echo "" ;endif; ?>
</ul>

<style>
    .pa_20 > ul{
        position: fixed;
        background: #fff;
        margin-top: -20px;
        z-index: 999;
        width: 100%;
    }
    .pa_20 > form {
        margin-top: 40px;
    }
    .base_label {
        padding: 0;
        border: 0;
        background-color: transparent;
    }
    .base_button{
        float: left;
        margin-left: 445px;
    }

    .base_button_a{
        margin-right: 65.5%;
        float: right;
        margin-left: 0;
    }

</style>
<link rel="stylesheet" href="/static/vendor/layer/layui/css/layui.css">
<script src="/static/vendor/layer/layui/layui.js"></script>
<script>
    layui.use(['element', 'layer'], function(){
        var element = layui.element,layer = layui.layer;
    });
</script>
        <div class="bg_form">
            <form action="<?php echo url('app'); ?>">
                <div class="layui-tab layui-tab-brief" lay-filter="docDemoTabBrief">
                    <ul class="layui-tab-title">
                        <li class="layui-this">调试模式</li>
                        <li>系统配置</li>
                        <li>服务部署</li>
                        <li>提现配置</li>
                        <li>创作号申请</li>
                    </ul>
                    <div class="layui-tab-content">

                        <div class="layui-tab-item layui-show">
                            <table class="content_info2 mt_10">
                                <tr>
                                    <td class="field_name">系统调试模式</td>
                                    <td>
                                        <select class="base_select" name="app_debug" selectedval="<?php echo !empty($_info['app_debug']) ? '1'  :  '0'; ?>">
                                            <option value="1">启用</option>
                                            <option value="0">禁用</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">系统追踪调试</td>
                                    <td>
                                        <select class="base_select" name="app_trace" selectedval="<?php echo !empty($_info['app_trace']) ? '1'  :  '0'; ?>">
                                            <option value="1">启用</option>
                                            <option value="0">禁用</option>
                                        </select>

                                    </td>

                                </tr>
                                <tr>
                                    <td class="field_name">短信调试模式</td>
                                    <td>
                                        <select class="base_select" name="sms_debug" selectedval="<?php echo !empty($_info['sms_debug']) ? '1'  :  '0'; ?>">
                                            <option value="1">启用</option>
                                            <option value="0">禁用</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">推送调试模式</td>
                                    <td>
                                        <select class="base_select" name="push_debug" selectedval="<?php echo !empty($_info['push_debug']) ? '1'  :  '0'; ?>">
                                            <option value="1">启用</option>
                                            <option value="0">禁用</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">ios审核状态</td>
                                    <td>
                                        <select class="base_select" name="ios_debug" selectedval="<?php echo htmlentities($_info['ios_debug']); ?>">
                                            <option value="1">启用</option>
                                            <option value="0">禁用</option>
                                        </select>
                                        <span>注：苹果上架涉敏感信息隐藏开关</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">登录权限检查</td>
                                    <td>
                                        <select class="base_select" name="auth_on" selectedval="<?php echo htmlentities($_info['auth_on']); ?>">
                                            <option value="1">启用</option>
                                            <option value="0">禁用</option>
                                        </select>
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <div class="layui-tab-item">
                            <table class="content_info2 mt_10">
                                <tr>
                                    <td class="field_name">帐号简称</td>
                                    <td>
                                        <input class="base_text" name="app_setting[account_name]" value="<?php echo htmlentities($_info['app_setting']['account_name']); ?>"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">帐号前缀</td>
                                    <td>
                                        <input class="base_text" name="app_setting[account_prefix]" value="<?php echo htmlentities($_info['app_setting']['account_prefix']); ?>"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">昵称前缀</td>
                                    <td>
                                        <input class="base_text" name="app_setting[nickname_prefix]" value="<?php echo htmlentities($_info['app_setting']['nickname_prefix']); ?>"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">默认分页条数</td>
                                    <td>
                                        <input class="base_text" name="app_setting[page_limit]" value="<?php echo htmlentities($_info['app_setting']['page_limit']); ?>"/>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="field_name">权限加密TOKEN</td>
                                    <td>
                                        <input class="base_text" name="app_setting[data_auth]" value="<?php echo htmlentities($_info['app_setting']['data_auth']); ?>"/>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="field_name">数据加密TOKEN</td>
                                    <td>
                                        <input class="base_text" name="app_setting[data_token]" value="<?php echo htmlentities($_info['app_setting']['data_token']); ?>"/>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="field_name">定时任务签名TOKEN</td>
                                    <td>
                                        <input class="base_text" name="app_setting[timer_token]" value="<?php echo htmlentities($_info['app_setting']['timer_token']); ?>"/>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="field_name">APP初始化KEY</td>
                                    <td>
                                        <input class="base_text" name="app_setting[app_secret_key]" value="<?php echo htmlentities($_info['app_setting']['app_secret_key']); ?>"/>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="field_name">经验值转化率</td>
                                    <td>
                                        <input class="base_text" name="app_setting[exp_rate]" value="<?php echo htmlentities($_info['app_setting']['exp_rate']); ?>"/>
                                        <span>小于或等于1的数值，1表示为等值转换</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name"><?php echo APP_MILLET_NAME; ?>转为<?php echo APP_BEAN_NAME; ?>转化率</td>
                                    <td>
                                        <input class="base_text" name="app_setting[millet_rate]" value="<?php echo htmlentities($_info['app_setting']['millet_rate']); ?>"/>
                                        <span>小于或等于1的数值，1表示为等值转换</span>
                                    </td>
                                </tr>

                                <tr style="display: none">
                                    <td class="field_name">收费视频时长</td>
                                    <td>
                                        <input class="base_text" name="app_setting[charge_video_duration]" value="<?php echo htmlentities($_info['app_setting']['charge_video_duration']); ?>"/>
                                        <span>注：单位(秒)</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">昵称修改间隔</td>
                                    <td>
                                        <input class="base_text" name="app_setting[renick_limit_time]" value="<?php echo htmlentities($_info['app_setting']['renick_limit_time']); ?>"/>
                                        <span>注：单位(秒)</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">默认用户信用分</td>
                                    <td>
                                        <input class="base_text" name="app_setting[default_credit_score]" value="<?php echo htmlentities($_info['app_setting']['default_credit_score']); ?>"/>
                                        <span>用户注册初始信用分</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">直播有效时长</td>
                                    <td>
                                        <input class="base_text" name="app_setting[live_effective_time]" value="<?php echo htmlentities($_info['app_setting']['live_effective_time']); ?>"/>
                                        <span>注：单位(秒)</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">主播失效时长</td>
                                    <td>
                                        <input class="base_text" name="app_setting[loss_after_months]" value="<?php echo htmlentities($_info['app_setting']['loss_after_months']); ?>"/>
                                        <span>注：单位(月)</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">主播失效<?php echo APP_BEAN_NAME; ?>最小值</td>
                                    <td>
                                        <input class="base_text" name="app_setting[loss_min_bean]" value="<?php echo htmlentities($_info['app_setting']['loss_min_bean']); ?>"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">官方帐号UID</td>
                                    <td>
                                        <input class="base_text"  name="app_setting[helper_id]" value="<?php echo htmlentities($_info['app_setting']['helper_id']); ?>" style="width: 210px;float: left;margin-right: 15px;"/>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="field_name">测试帐号</td>
                                    <td>
                                        <ul class="json_list test_list"></ul>
                                        <input name="test_user" type="hidden" value="<?php echo htmlentities(implode($_info['test_user'],',')); ?>"/>
                                    </td>
                                </tr>

                            </table>
                        </div>

                        <div class="layui-tab-item">
                            <table class="content_info2 mt_10">
                                <tr>
                                    <td class="field_name">部署方式</td>
                                    <td>
                                        <select class="base_select" name="system_deploy[deploy_mode]" selectedval="<?php echo htmlentities($_info['system_deploy']['deploy_mode']); ?>">
                                            <option value="single">单机</option>
                                            <option value="colony">集群</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">服务网关</td>
                                    <td>
                                        <input class="base_text" name="system_deploy[core_service_url]" value="<?php echo htmlentities($_info['system_deploy']['core_service_url']); ?>"/>
                                        <span>单机部署尽量配置回环地址127.0.0.1提供服务</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">接口网关</td>
                                    <td>
                                        <input class="base_text" name="system_deploy[api_service_url]" value="<?php echo htmlentities($_info['system_deploy']['api_service_url']); ?>"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">推送网关</td>
                                    <td>
                                        <input class="base_text" name="system_deploy[push_service_url]" value="<?php echo htmlentities($_info['system_deploy']['push_service_url']); ?>"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">分享网关</td>
                                    <td>
                                        <input class="base_text" name="system_deploy[fx_service_url]" value="<?php echo htmlentities($_info['system_deploy']['fx_service_url']); ?>"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">H5 地址</td>
                                    <td>
                                        <input class="base_text" name="system_deploy[h5_service_url]" value="<?php echo htmlentities($_info['system_deploy']['h5_service_url']); ?>"/>
                                        <span>APP内H5页面访问地址</span>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="field_name">合作商地址</td>
                                    <td>
                                        <input class="base_text" name="system_deploy[agent_service_url]" value="<?php echo htmlentities($_info['system_deploy']['agent_service_url']); ?>"/>
                                        <span>合作商管理地址</span>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="field_name">业务员地址</td>
                                    <td>
                                        <input class="base_text" name="system_deploy[promoter_service_url]" value="<?php echo htmlentities($_info['system_deploy']['promoter_service_url']); ?>"/>
                                        <span>业务员管理地址</span>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="field_name">充值服务地址</td>
                                    <td>
                                        <input class="base_text" name="system_deploy[recharge_service_url]" value="<?php echo htmlentities($_info['system_deploy']['recharge_service_url']); ?>"/>
                                        <span>用户充值回调服务地址</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">后台地址</td>
                                    <td>
                                        <input class="base_text" name="system_deploy[erp_service_url]" value="<?php echo htmlentities($_info['system_deploy']['erp_service_url']); ?>"/>
                                        <span>管理后台</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">淘客API地址</td>
                                    <td>
                                        <input class="base_text" name="system_deploy[taoke_api_url]" value="<?php echo htmlentities($_info['system_deploy']['taoke_api_url']); ?>"/>
                                        <span>淘客功能代理接口</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">商城链接地址</td>
                                    <td>
                                        <input class="base_text" name="system_deploy[mall_url]" value="<?php echo htmlentities($_info['system_deploy']['mall_url']); ?>"/>
                                        <span>商城链接地址</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">淘客API授权key</td>
                                    <td>
                                        <input class="base_text" name="system_deploy[taoke_api_key]" value="<?php echo htmlentities($_info['system_deploy']['taoke_api_key']); ?>"/>
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <div class="layui-tab-item">
                            <table class="content_info2 mt_10">

                                <tr>
                                    <td class="field_name">平台提现</td>
                                    <td>
                                        <select class="base_select" name="cash_setting[cash_on]" selectedval="<?php echo htmlentities($_info['cash_setting']['cash_on']); ?>">
                                            <option value="1">启用</option>
                                            <option value="0">禁用</option>
                                        </select>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="field_name">公会提现</td>
                                    <td>
                                        <select class="base_select" name="cash_setting[agent_cash_on]" selectedval="<?php echo htmlentities($_info['cash_setting']['agent_cash_on']); ?>">
                                            <option value="1">启用</option>
                                            <option value="0">禁用</option>
                                        </select>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="field_name">公会结算方式</td>
                                    <td>
                                        <select class="base_select cashtype" name="cash_setting[cash_type]" selectedval="<?php echo htmlentities($_info['cash_setting']['cash_type']); ?>">
                                            <option value="0">公会结算</option>
                                            <option value="1">平台结算(收益<?php echo APP_MILLET_NAME; ?>结算)</option>
                                        </select>
                                    </td>
                                </tr>

                                <tr id="agentcash" <?php if($_info['cash_setting']['cash_type'] == '1'): ?>style ="display:none"<?php endif; ?>>
                                    <td class="field_name">公会提现类型</td>
                                    <td>
                                        <select class="base_select" name="cash_setting[cash_millet_type]" selectedval="<?php echo htmlentities($_info['cash_setting']['cash_millet_type']); ?>">
                                            <option value="0">客消<?php echo APP_BEAN_NAME; ?></option>
                                            <option value="1">收益<?php echo APP_MILLET_NAME; ?></option>
                                        </select>
                                        <span>只针对公会结算方式为公会结算的时候 以那种收益为准</span>
                                    </td>
                                </tr>


                                <tr>
                                    <td class="field_name">公会结算比例</td>
                                    <td>
                                        <input class="base_text" name="cash_setting[cash_proportion]" value="<?php echo htmlentities($_info['cash_setting']['cash_proportion']); ?>"/>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="field_name">公会提现单笔手续费</td>
                                    <td>
                                        <input class="base_text" name="cash_setting[agent_cash_fee]" value="<?php echo htmlentities($_info['cash_setting']['agent_cash_fee']); ?>"/>
                                        <span>RMB后扣除的金额</span>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="field_name">公会提现单笔税率</td>
                                    <td>
                                        <input class="base_text" name="cash_setting[agent_cash_taxes]" value="<?php echo htmlentities($_info['cash_setting']['agent_cash_taxes']); ?>"/>
                                        <span>扣除税率 如100元 税率0.01 那么扣除 1 RMB</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">公会提现最低额度</td>
                                    <td>
                                        <input class="base_text" name="cash_setting[agent_cash_min]" value="<?php echo htmlentities($_info['cash_setting']['agent_cash_min']); ?>"/>
                                        <span>元</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">公会月提现次数</td>
                                    <td>
                                        <input class="base_text" name="cash_setting[agent_cash_monthlimit]" value="<?php echo htmlentities($_info['cash_setting']['agent_cash_monthlimit']); ?>"/>
                                        <span>次</span>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="field_name">主播提现比率</td>
                                    <td>
                                        <input class="base_text" name="cash_setting[cash_rate]" value="<?php echo htmlentities($_info['cash_setting']['cash_rate']); ?>"/>
                                        <span>主播<?php echo APP_MILLET_NAME; ?>转RMB比率</span>
                                        <span>小于或等于1的数值，1表示为等值转换</span>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="field_name">用户提现比率</td>
                                    <td>
                                        <input class="base_text" name="cash_setting[cash_user_rate]" value="<?php echo htmlentities($_info['cash_setting']['cash_user_rate']); ?>"/>
                                        <span>用户<?php echo APP_MILLET_NAME; ?>转RMB比率</span>
                                        <span>小于或等于1的数值，1表示为等值转换</span>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="field_name">单笔手续费</td>
                                    <td>
                                        <input class="base_text" name="cash_setting[cash_fee]" value="<?php echo htmlentities($_info['cash_setting']['cash_fee']); ?>"/>
                                        <span><?php echo APP_MILLET_NAME; ?>转RMB后扣除的金额</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">提现税率</td>
                                    <td>
                                        <input class="base_text" name="cash_setting[cash_taxes]" value="<?php echo htmlentities($_info['cash_setting']['cash_taxes']); ?>"/>
                                        <span>扣除<?php echo APP_MILLET_NAME; ?>税率 如100金币 税率0.01 那么扣除 1 RMB</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">提现最低额度</td>
                                    <td>
                                        <input class="base_text" name="cash_setting[cash_min]" value="<?php echo htmlentities($_info['cash_setting']['cash_min']); ?>"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">月提现次数</td>
                                    <td>
                                        <input class="base_text" name="cash_setting[cash_monthlimit]" value="<?php echo htmlentities($_info['cash_setting']['cash_monthlimit']); ?>"/>
                                    </td>
                                </tr>
                               <tr>
                                    <td class="field_name">积分兑换比率</td>
                                    <td>
                                        <input class="base_text" name="cash_setting[exchange_percent]" value="<?php echo htmlentities($_info['cash_setting']['exchange_percent']); ?>"/>
                                        <span>兑换比率就是多少积分兑换一个金币</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">兑换积分展示</td>
                                    <td>
                                        <input class="base_text" name="cash_setting[exchange_integral]" value="<?php echo htmlentities($_info['cash_setting']['exchange_integral']); ?>"/>
                                        <span>数字使用‘,’分割</span>
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <div class="layui-tab-item">
                            <table class="content_info2 mt_10">
                                <tr>
                                    <td class="field_name">认证粉丝数</td>
                                    <td>
                                        <input class="base_text" name="app_setting[creation_fans_num]" value="<?php echo htmlentities($_info['app_setting']['creation_fans_num']); ?>"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">认证原创视频数</td>
                                    <td>
                                        <input class="base_text" name="app_setting[creation_film_num]" value="<?php echo htmlentities($_info['app_setting']['creation_film_num']); ?>"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">认证是否检查举报记录</td>
                                    <td>
                                        <select class="base_select" name="app_setting[creation_report_record]" selectedval="<?php echo htmlentities($_info['app_setting']['creation_report_record']); ?>">
                                            <option value="1">是</option>
                                            <option value="0">否</option>
                                        </select>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="base_button_div p_b_20">
                    <a href="javascript:;" class="base_button" ajax="post">提交</a>
                </div>
            </form>
        </div>
    </div>

    <script>

        new JsonList('.test_list', {
            input: '[name=test_user]',
            btns: ['add', 'remove'],
            max: 10,
            format: 'separate',
            fields: [
                {
                    title: '测试帐号ID',
                    name: 'name',
                    type: 'text',
                    width: 100
                }
            ]
        });


        $(".cashtype").change(function(){
            var type = $('.cashtype option:selected').val();
            if (type == 0) {
                $('#agentcash').show();

            }
            if (type == 1) {
                $('#agentcash').hide();
            }
        });
    </script>


        </div>
    </div>
</div>

<div dom-key="work_box" popbox="work_box" class="work_box layer_box" title="任务设置"
     popbox-action="<?php echo url('personal/work'); ?>" popbox-get-data="<?php echo url('personal/work'); ?>" popbox-area="640px,450px">
    <div class="pa_10">
        <p>我的工作编号：<?php echo htmlentities($admin['id']); ?></p>
        <table class="content_list work_list mt_5">
            <thead>
            <tr>
                <td>工作项</td>
                <td>本月接手数量</td>
                <td>短信提醒</td>
                <td>工作状态</td>
            </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
</div>


<script src="/bx_static/toggle.js"></script>
</body>
</html>
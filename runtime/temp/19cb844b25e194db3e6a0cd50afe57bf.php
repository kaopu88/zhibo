<?php /*a:7:{s:62:"/www/wwwroot/zhibb/application/admin/view/sys_config/third.tpl";i:1661240922;s:61:"/www/wwwroot/zhibb/application/admin/view/public/base_nav.tpl";i:1693212818;s:61:"/www/wwwroot/zhibb/application/admin/view/public/jsconfig.tpl";i:1602499828;s:61:"/www/wwwroot/zhibb/application/admin/view/public/main_top.tpl";i:1593518960;s:59:"/www/wwwroot/zhibb/application/admin/view/public/toggle.tpl";i:1592625950;s:64:"/www/wwwroot/zhibb/application/admin/view/components/tab_nav.tpl";i:1592356812;s:65:"/www/wwwroot/zhibb/application/admin/view/components/work_pop.tpl";i:1567562304;}*/ ?>
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
            <form action="<?php echo url('third'); ?>">

                <div class="layui-tab layui-tab-brief" lay-filter="docDemoTabBrief">
                    <ul class="layui-tab-title">
                        <li class="layui-this">地图配置</li>
                        <!-- <li>搜索服务</li>-->
                        <li>自媒体配置</li>
                        <li>身份认证配置</li>
                        <!-- <li>Node配置</li>
                         <li>MQ配置</li>-->
                    </ul>
                    <div class="layui-tab-content">

                        <div class="layui-tab-item layui-show">
                            <table class="content_info2 mt_10">
                                <tr>
                                    <td class="field_name">服务商</td>
                                    <td>
                                        <select class="base_select" name="map_setting[platform]" selectedval="<?php echo htmlentities($_info['map_setting']['platform']); ?>">
                                            <option value="amap">高德地图</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">应用WEB_KEY</td>
                                    <td>
                                        <input class="base_text" name="map_setting[web_service_key]" value="<?php echo htmlentities($_info['map_setting']['web_service_key']); ?>"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">应用JS_KEY</td>
                                    <td>
                                        <input class="base_text" name="map_setting[js_service_key]" value="<?php echo htmlentities($_info['map_setting']['js_service_key']); ?>"/>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="field_name">应用JS秘钥</td>
                                    <td>
                                        <input class="base_text" name="map_setting[js_service_secret]" value="<?php echo htmlentities($_info['map_setting']['js_service_secret']); ?>"/>
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <!-- <div class="layui-tab-item">
                            <table class="content_info2 mt_10">
                                <tr>
                                    <td class="field_name">服务商</td>
                                    <td>
                                        <select class="base_select" name="aomy_search[platform]" selectedval="<?php echo htmlentities($_info['aomy_search']['platform']); ?>">
                                            <option value="aliyun">阿里开放搜索</option>
                                            <option value="local">自建搜索</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">应用KEY</td>
                                    <td>
                                        <input class="base_text" name="aomy_search[access_key]" value="<?php echo htmlentities($_info['aomy_search']['access_key']); ?>"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">应用secret</td>
                                    <td>
                                        <input class="base_text" name="aomy_search[secret_key]" value="<?php echo htmlentities($_info['aomy_search']['secret_key']); ?>"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">应用region</td>
                                    <td>
                                        <input class="base_text" name="aomy_search[region]" value="<?php echo htmlentities($_info['aomy_search']['region']); ?>"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">应用host</td>
                                    <td>
                                        <input class="base_text" name="aomy_search[host]" value="<?php echo htmlentities($_info['aomy_search']['host']); ?>"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">应用key_type</td>
                                    <td>
                                        <input class="base_text" name="aomy_search[key_type]" value="<?php echo htmlentities($_info['aomy_search']['key_type']); ?>"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">应用debug</td>
                                    <td>
                                        <select class="base_select" name="aomy_search[debug]" selectedval="<?php echo !empty($_info['aomy_search']['debug']) ? '1'  :  '0'; ?>">
                                            <option value="1">启用</option>
                                            <option value="0">禁用</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">搜索应用</td>
                                    <td>
                                        <input class="base_text" name="aomy_search[search_app]" value="<?php echo htmlentities($_info['aomy_search']['search_app']); ?>"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">搜索建议</td>
                                    <td>
                                        <input class="base_text" name="aomy_search[search_suggest]" value="<?php echo htmlentities($_info['aomy_search']['search_suggest']); ?>"/>
                                    </td>
                                </tr>
                            </table>
                        </div>-->

                        <div class="layui-tab-item">
                            <table class="content_info2 mt_10">
                                <tr>
                                    <td class="field_name">公众号ID</td>
                                    <td>
                                        <input class="base_text" placeholder="APP_ID" name="media_platform[wx_wap][app_id]" value="<?php echo htmlentities($_info['media_platform']['wx_wap']['app_id']); ?>"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">公众号Secret</td>
                                    <td>
                                        <input class="base_text" placeholder="APP_SECRET" name="media_platform[wx_wap][secret_key]" value="<?php echo htmlentities($_info['media_platform']['wx_wap']['secret_key']); ?>"/>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="field_name">小程序Id</td>
                                    <td>
                                        <input class="base_text" placeholder="APP_ID" name="media_platform[wx_app][app_id]" value="<?php echo htmlentities($_info['media_platform']['wx_app']['app_id']); ?>"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">小程序Secret</td>
                                    <td>
                                        <input class="base_text" placeholder="APP_SECRET" name="media_platform[wx_app][secret_key]" value="<?php echo htmlentities($_info['media_platform']['wx_app']['secret_key']); ?>"/>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="field_name">QQ/ID</td>
                                    <td>
                                        <input class="base_text" placeholder="APP_ID" name="media_platform[qq][app_id]" value="<?php echo htmlentities($_info['media_platform']['qq']['app_id']); ?>"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">QQ/Key</td>
                                    <td>
                                        <input class="base_text" placeholder="KEY" name="media_platform[qq][secret_key]" value="<?php echo htmlentities($_info['media_platform']['qq']['secret_key']); ?>"/>
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <div class="layui-tab-item">
                            <table class="content_info2 mt_10">
                                <tr>
                                    <td class="field_name">认证开关</td>
                                    <td>
                                        <select class="base_select" name="certification_setting[cert_on]" selectedval="<?php echo htmlentities($_info['certification_setting']['cert_on']); ?>">
                                            <option value="1">开启</option>
                                            <option value="0">关闭</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">服务商</td>
                                    <td>
                                        <select class="base_select" name="certification_setting[platform]" selectedval="<?php echo htmlentities($_info['certification_setting']['platform']); ?>">
                                            <!--<option value="jd">京东</option>-->
                                            <option value="tx">腾讯</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">应用secretId</td>
                                    <td>
                                        <input class="base_text" name="certification_setting[app_key]" value="<?php echo htmlentities($_info['certification_setting']['app_key']); ?>"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">应用secretKey</td>
                                    <td>
                                        <input class="base_text" name="certification_setting[app_secret]" value="<?php echo htmlentities($_info['certification_setting']['app_secret']); ?>"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">服务网关地址</td>
                                    <td>
                                        <input class="base_text" name="certification_setting[service_gateway]" value="<?php echo htmlentities($_info['certification_setting']['service_gateway']); ?>"/>
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <!--<div class="layui-tab-item">
                            <table class="content_info2 mt_10">
                                <tr>
                                    <td class="field_name">微信公众号App_id</td>
                                    <td>
                                        <input class="base_text" name="wx_appid" value="<?php echo htmlentities($_info['wx_appid']); ?>"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">微信公众号App_secret</td>
                                    <td>
                                        <input class="base_text" name="wx_appsecret" value="<?php echo htmlentities($_info['wx_appsecret']); ?>"/>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="field_name">微信小程序App_id</td>
                                    <td>
                                        <input class="base_text" name="wxapp_appid" value="<?php echo htmlentities($_info['wxapp_appid']); ?>"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">微信小程序App_secret</td>
                                    <td>
                                        <input class="base_text" name="wxapp_secret" value="<?php echo htmlentities($_info['wxapp_secret']); ?>"/>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="field_name">QQ应用App_id</td>
                                    <td>
                                        <input class="base_text" name="qq_appid" value="<?php echo htmlentities($_info['qq_appid']); ?>"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">QQ应用Key</td>
                                    <td>
                                        <input class="base_text" name="qq_key" value="<?php echo htmlentities($_info['qq_key']); ?>"/>
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <div class="layui-tab-item">
                            <table class="content_info2 mt_10">
                                <tr>
                                    <td class="field_name">微信公众号App_id</td>
                                    <td>
                                        <input class="base_text" name="wx_appid" value="<?php echo htmlentities($_info['wx_appid']); ?>"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">微信公众号App_secret</td>
                                    <td>
                                        <input class="base_text" name="wx_appsecret" value="<?php echo htmlentities($_info['wx_appsecret']); ?>"/>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="field_name">微信小程序App_id</td>
                                    <td>
                                        <input class="base_text" name="wxapp_appid" value="<?php echo htmlentities($_info['wxapp_appid']); ?>"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">微信小程序App_secret</td>
                                    <td>
                                        <input class="base_text" name="wxapp_secret" value="<?php echo htmlentities($_info['wxapp_secret']); ?>"/>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="field_name">QQ应用App_id</td>
                                    <td>
                                        <input class="base_text" name="qq_appid" value="<?php echo htmlentities($_info['qq_appid']); ?>"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">QQ应用Key</td>
                                    <td>
                                        <input class="base_text" name="qq_key" value="<?php echo htmlentities($_info['qq_key']); ?>"/>
                                    </td>
                                </tr>
                            </table>
                        </div>-->

                    </div>
                </div>
                <div class="base_button_div p_b_20">
                    <a href="javascript:;" class="base_button" ajax="post">提交</a>
                </div>

            </form>
        </div>
    </div>


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
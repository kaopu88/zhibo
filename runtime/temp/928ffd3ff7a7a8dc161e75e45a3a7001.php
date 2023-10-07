<?php /*a:7:{s:60:"/www/wwwroot/zhibb/application/admin/view/sys_config/sms.tpl";i:1684910310;s:61:"/www/wwwroot/zhibb/application/admin/view/public/base_nav.tpl";i:1693212818;s:61:"/www/wwwroot/zhibb/application/admin/view/public/jsconfig.tpl";i:1602499828;s:61:"/www/wwwroot/zhibb/application/admin/view/public/main_top.tpl";i:1593518960;s:59:"/www/wwwroot/zhibb/application/admin/view/public/toggle.tpl";i:1592625950;s:64:"/www/wwwroot/zhibb/application/admin/view/components/tab_nav.tpl";i:1592356812;s:65:"/www/wwwroot/zhibb/application/admin/view/components/work_pop.tpl";i:1567562304;}*/ ?>
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
            <form action="<?php echo url('sms'); ?>">

                <div class="layui-tab layui-tab-brief" lay-filter="docDemoTabBrief">
                    <ul class="layui-tab-title">
                        <li class="layui-this">短信配置</li>
                        <li>线路配置</li>
                        <li>私信配置</li>
                        <li>推送配置</li>
                        <li>客服配置</li>
                    </ul>
                    <div class="layui-tab-content">

                        <div class="layui-tab-item layui-show">
                            <table class="content_info2 mt_10">
                                <tr>
                                    <td class="field_name">服务商</td>
                                    <td>
                                        <select class="base_select" name="aomy_sms[platform]" selectedval="<?php echo htmlentities($_info['aomy_sms']['platform']); ?>">
                                            <option value="aliyun">阿里云通信</option>
                                            <option value="tencloud">腾讯云通信</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">验证码有效期</td>
                                    <td>
                                        <input class="base_text" name="aomy_sms[sms_code_expire]" value="<?php echo htmlentities($_info['aomy_sms']['sms_code_expire']); ?>"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">发送时间间隔</td>
                                    <td>
                                        <input class="base_text" name="aomy_sms[sms_code_limit]" value="<?php echo htmlentities($_info['aomy_sms']['sms_code_limit']); ?>"/>
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <div class="layui-tab-item">
                            <div class="content_title2">
                                区域线路
                            </div>
                            <table class="content_info2 mt_10">
                                <tr>
                                    <td class="field_name">应用Access_id</td>
                                    <td>
                                        <input class="base_text" name="aomy_sms[regional][access_id]" value="<?php echo htmlentities($_info['aomy_sms']['regional']['access_id']); ?>"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">应用Access_secret</td>
                                    <td>
                                        <input class="base_text" name="aomy_sms[regional][access_secret]" value="<?php echo htmlentities($_info['aomy_sms']['regional']['access_secret']); ?>"/>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="field_name">应用SDK AppID</td>
                                    <td>
                                        <input class="base_text" name="aomy_sms[regional][sdk_app_id]" value="<?php echo htmlentities($_info['aomy_sms']['regional']['sdk_app_id']); ?>"/>
                                        <span>如不是腾讯云通信  可不填</span>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="field_name">应用Region</td>
                                    <td>
                                        <input class="base_text" name="aomy_sms[regional][region]" value="<?php echo htmlentities($_info['aomy_sms']['regional']['region']); ?>"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">应用Endpoint_name</td>
                                    <td>
                                        <input class="base_text" name="aomy_sms[regional][endpoint_name]" value="<?php echo htmlentities($_info['aomy_sms']['regional']['endpoint_name']); ?>"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">应用Sign_name</td>
                                    <td>
                                        <input class="base_text" name="aomy_sms[regional][sign_name]" value="<?php echo htmlentities($_info['aomy_sms']['regional']['sign_name']); ?>"/>
                                    </td>
                                </tr>
                                <!--<tr>
                                    <td class="field_name">短信模板</td>
                                    <td>
                                        <ul class="json_list sms_code_scenes_list"></ul>
                                        <input name="sms_code_scenes" type="hidden" value="<?php echo htmlentities($_info['sms_code_scenes']); ?>"/>
                                    </td>
                                </tr>-->
                            </table>

                            <div class="content_title2">
                                国际线路
                            </div>
                            <table class="content_info2 mt_10">
                                <tr>
                                    <td class="field_name">应用Access_id</td>
                                    <td>
                                        <input class="base_text" name="aomy_sms[global][access_id]" value="<?php echo htmlentities($_info['aomy_sms']['global']['access_id']); ?>"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">应用Access_secret</td>
                                    <td>
                                        <input class="base_text" name="aomy_sms[global][access_secret]" value="<?php echo htmlentities($_info['aomy_sms']['global']['access_secret']); ?>"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">应用SDK AppID</td>
                                    <td>
                                        <input class="base_text" name="aomy_sms[global][sdk_app_id]" value="<?php echo htmlentities($_info['aomy_sms']['global']['sdk_app_id']); ?>"/>
                                        <span>如不是腾讯云通信  可不填</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">应用Region</td>
                                    <td>
                                        <input class="base_text" name="aomy_sms[global][region]" value="<?php echo htmlentities($_info['aomy_sms']['global']['region']); ?>"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">应用Endpoint_name</td>
                                    <td>
                                        <input class="base_text" name="aomy_sms[global][endpoint_name]" value="<?php echo htmlentities($_info['aomy_sms']['global']['endpoint_name']); ?>"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">应用Sign_name</td>
                                    <td>
                                        <input class="base_text" name="aomy_sms[global][sign_name]" value="<?php echo htmlentities($_info['aomy_sms']['global']['sign_name']); ?>"/>
                                    </td>
                                </tr>
                                <!--<tr>
                                    <td class="field_name">短信模板</td>
                                    <td>
                                        <ul class="json_list sms_code_scenes_list"></ul>
                                        <input name="sms_code_scenes" type="hidden" value="<?php echo htmlentities($_info['sms_code_scenes']); ?>"/>
                                    </td>
                                </tr>-->
                            </table>
                        </div>

                        <div class="layui-tab-item">
                            <table class="content_info2 mt_10">
                                <tr>
                                    <td class="field_name">安卓私信</td>
                                    <td>
                                        <select class="base_select" name="aomy_private_letter[private_letter_status]" selectedval="<?php echo !empty($_info['aomy_private_letter']['private_letter_status']) ? '1'  :  '0'; ?>">
                                            <option value="1">启用</option>
                                            <option value="0">禁用</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">ios私信</td>
                                    <td>
                                        <select class="base_select" name="aomy_private_letter[private_ios_letter_status]" selectedval="<?php echo !empty($_info['aomy_private_letter']['private_ios_letter_status']) ? '1'  :  '0'; ?>">
                                            <option value="1">启用</option>
                                            <option value="0">禁用</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">服务商</td>
                                    <td>
                                        <select class="base_select" name="aomy_private_letter[platform]" selectedval="<?php echo htmlentities($_info['aomy_private_letter']['platform']); ?>">
                                            <option value="yunxin">网易云信</option>
                                            <!--<option value="rongcloud">融云</option>-->
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">APP_KEY</td>
                                    <td>
                                        <input class="base_text" name="aomy_private_letter[app_key]" value="<?php echo htmlentities($_info['aomy_private_letter']['app_key']); ?>"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">APP_SECRET</td>
                                    <td>
                                        <input class="base_text" name="aomy_private_letter[app_secret]" value="<?php echo htmlentities($_info['aomy_private_letter']['app_secret']); ?>"/>
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <div class="layui-tab-item">
                            <div class="content_title2">服务商选择</div>
                            <table class="content_info2 mt_10">
                                <tr>
                                    <td class="field_name">服务商</td>
                                    <td>
                                        <select class="base_select" name="bxkj_push[platform]" selectedval="<?php echo htmlentities($_info['bxkj_push']['platform']); ?>">
                                            <option value="umeng">友盟</option>
                                            <!--<option value="jiguang">极光</option>-->
                                        </select>
                                    </td>
                                </tr>
                            </table>
                            <div class="content_title2">Android</div>
                            <table class="content_info2 mt_10">
                                <tr>
                                    <td class="field_name">APP_kEY</td>
                                    <td>
                                        <input class="base_text" name="bxkj_push[android][app_key]" value="<?php echo htmlentities($_info['bxkj_push']['android']['app_key']); ?>"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">Message_secret</td>
                                    <td>
                                        <input class="base_text" name="bxkj_push[android][message_secret]" value="<?php echo htmlentities($_info['bxkj_push']['android']['message_secret']); ?>"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">Master_secret</td>
                                    <td>
                                        <input class="base_text" name="bxkj_push[android][app_master_secret]" value="<?php echo htmlentities($_info['bxkj_push']['android']['app_master_secret']); ?>"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">Default_activity</td>
                                    <td>
                                        <input class="base_text" name="bxkj_push[android][default_activity]" value="<?php echo htmlentities($_info['bxkj_push']['android']['default_activity']); ?>"/>
                                    </td>
                                </tr>
                            </table>
                            <div class="content_title2">IOS</div>
                            <table class="content_info2 mt_10">
                                <tr>
                                    <td class="field_name">APP_KEY</td>
                                    <td>
                                        <input class="base_text" name="bxkj_push[ios][app_key]" value="<?php echo htmlentities($_info['bxkj_push']['ios']['app_key']); ?>"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">App_secret</td>
                                    <td>
                                        <input class="base_text" name="bxkj_push[ios][app_master_secret]" value="<?php echo htmlentities($_info['bxkj_push']['ios']['app_master_secret']); ?>"/>
                                    </td>
                                </tr>
                            </table>
                            <div class="content_title2">统一配置</div>
                            <table class="content_info2 mt_10">
                                <tr>
                                    <td class="field_name">每秒延迟率</td>
                                    <td>
                                        <input class="base_text" name="bxkj_push[push_delay_rate]" value="<?php echo htmlentities($_info['bxkj_push']['push_delay_rate']); ?>"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">延迟最大范围</td>
                                    <td>
                                        <input class="base_text" name="bxkj_push[push_delay_range]" value="<?php echo htmlentities($_info['bxkj_push']['push_delay_range']); ?>"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">最大延迟时间</td>
                                    <td>
                                        <input class="base_text" name="bxkj_push[push_max_delay]" value="<?php echo htmlentities($_info['bxkj_push']['push_max_delay']); ?>"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">默认分片长度</td>
                                    <td>
                                        <input class="base_text" name="bxkj_push[push_section_length]" value="<?php echo htmlentities($_info['bxkj_push']['push_section_length']); ?>"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">撤回时间</td>
                                    <td>
                                        <input class="base_text" name="bxkj_push[push_receipt_period]" value="<?php echo htmlentities($_info['bxkj_push']['push_receipt_period']); ?>"/>
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <div class="layui-tab-item">
                            <table class="content_info2 mt_10">
                                <tr>
                                    <td class="field_name">客服</td>
                                    <td>
                                        <select class="base_select" id="service"  name="bxkj_customer_service[type]" selectedval="<?php echo htmlentities($_info['bxkj_customer_service']['type']); ?>" onchange="func(this)">
                                            <option value="0">默认</option>
                                            <option value="1">链接</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr  <?php if($_info['bxkj_customer_service']['type'] == 0): ?> style="display: none" <?php endif; ?>  id="type">
                                <td class="field_name">链接</td>
                                <td>
                                    <input class="base_text" name="bxkj_customer_service[link]" value="<?php echo htmlentities($_info['bxkj_customer_service']['link']); ?>"/>
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
        // new JsonList('.sms_code_scenes_list', {
        //     input: '[name=sms_code_scenes]',
        //     btns: ['add', 'remove'],
        //     max: 50,
        //     fields: [
        //         {
        //             name: 'name',
        //             title: '名称',
        //             type: 'text',
        //             width: 160
        //         },
        //         {
        //             name: 'value',
        //             title: '代码',
        //             type: 'text',
        //             width: 150
        //         },
        //         {
        //             name: 'exists',
        //             title: '手机号是否存在',
        //             type: 'text',
        //             width: 80
        //         },
        //         {
        //             name: 'bind',
        //             title: '是否登录且绑定',
        //             type: 'text',
        //             width: 80
        //         },
        //         {
        //             name: 'main',
        //             title: 'main',
        //             type: 'text',
        //             width: 80
        //         },
        //         {
        //             name: 'sms_tpl',
        //             title: '国内短信模板',
        //             type: 'text',
        //             width: 200
        //         },
        //         {
        //             name: 'g_sms_tpl',
        //             title: '国际短信模板',
        //             type: 'text',
        //             width: 200
        //         }
        //     ]
        // });

        function func(e){
            var vs = $('#service').val();

            if (vs == 0) {
                $("#type").hide();
            } else {
                $("#type").show();
            }
        }

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
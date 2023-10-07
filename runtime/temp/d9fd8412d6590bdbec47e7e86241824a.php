<?php /*a:7:{s:61:"/www/wwwroot/zhibb/application/admin/view/sys_config/live.tpl";i:1691482494;s:61:"/www/wwwroot/zhibb/application/admin/view/public/base_nav.tpl";i:1693212818;s:61:"/www/wwwroot/zhibb/application/admin/view/public/jsconfig.tpl";i:1602499828;s:61:"/www/wwwroot/zhibb/application/admin/view/public/main_top.tpl";i:1593518960;s:59:"/www/wwwroot/zhibb/application/admin/view/public/toggle.tpl";i:1592625950;s:64:"/www/wwwroot/zhibb/application/admin/view/components/tab_nav.tpl";i:1592356812;s:65:"/www/wwwroot/zhibb/application/admin/view/components/work_pop.tpl";i:1567562304;}*/ ?>
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
            <form action="<?php echo url('live'); ?>">

                <div class="layui-tab layui-tab-brief" lay-filter="docDemoTabBrief">
                    <ul class="layui-tab-title">
                        <li class="layui-this">直播配置</li>
                        <li>直播流配置</li>
                        <li>开播配置</li>
                        <li>主播任务</li>
                        <li>申请主播配置</li>
                        <li>开播商城配置</li>
                    </ul>
                    <div class="layui-tab-content">

                        <div class="layui-tab-item layui-show">
                            <table class="content_info2 mt_10">
                                <tr>
                                    <td class="field_name">直播云服务商</td>
                                    <td>
                                        <select class="base_select" name="live_setting[platform]" selectedval="<?php echo htmlentities($_info['platform']); ?>">
                                            <option value="tencent">腾讯云</option>
                                            <option value="qiniu">七牛云</option>
                                            <option value="aliyun">阿里云</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">设置头像为封面</td>
                                    <td>
                                        <select class="base_select" name="live_setting[avatar_set_cover]" selectedval="<?php echo htmlentities($_info['avatar_set_cover']); ?>">
                                            <option value="1">是</option>
                                            <option value="0">否</option>
                                        </select>
                                        <span>主播开播是否将头像作为直播间封面</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">场控人数</td>
                                    <td>
                                        <input class="base_text" name="live_setting[live_manage_sum]" value="<?php echo htmlentities($_info['live_manage_sum']); ?>"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">禁言时长</td>
                                    <td>
                                        <input class="base_text" name="live_setting[shutspeak_expire_time]" value="<?php echo htmlentities($_info['shutspeak_expire_time']); ?>"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name"></td>
                                    <td>
                                        <div class="base_group">
                                            <span class="base_label" style="width: 211px;">地址</span>
                                            <span class="base_label" style="width: 201px;">端口</span>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">消息服务器地址</td>
                                    <td>
                                        <input class="base_text" name="live_setting[message_server][host]" style="width: 193px;" value="<?php echo htmlentities($_info['message_server']['host']); ?>"/>
                                        <input class="base_text" name="live_setting[message_server][port]" style="width: 193px;" value="<?php echo htmlentities($_info['message_server']['port']); ?>"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">游戏服务器地址</td>
                                    <td>
                                        <input class="base_text" name="live_setting[game_server][host]" style="width: 193px;" value="<?php echo htmlentities($_info['game_server']['host']); ?>"/>
                                        <input class="base_text" name="live_setting[game_server][port]" style="width: 193px;" value="<?php echo htmlentities($_info['game_server']['port']); ?>"/>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="field_name">配置服务地址</td>
                                    <td>
                                        <input class="base_text" name="live_setting[service_host]" value="<?php echo htmlentities($_info['service_host']); ?>"/>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="field_name"></td>
                                    <td>
                                        <div class="base_group">
                                            <span class="base_label" style="width: 211px;">最大</span>
                                            <span class="base_label" style="width: 201px;">最小</span>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">机器人数量</td>
                                    <td>
                                        <input class="base_text" name="live_setting[robot][max]" style="width: 193px;" value="<?php echo htmlentities($_info['robot']['max']); ?>"/>
                                        <input class="base_text" name="live_setting[robot][min]" style="width: 193px;" value="<?php echo htmlentities($_info['robot']['min']); ?>"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">弹幕价格</td>
                                    <td>
                                        <input class="base_text" name="live_setting[barrage_fee]"  value="<?php echo htmlentities($_info['barrage_fee']); ?>"/>
                                        <span>单位：<?php echo APP_BEAN_NAME; ?></span>
                                    </td>
                                </tr>
                                <!--<tr>
                                    <td class="field_name">发言信用分</td>
                                    <td>
                                        <input class="base_text" name="live_setting[credit_score]"  value="<?php echo htmlentities($_info['credit_score']); ?>"/>
                                        <span>用户发言低于此分时只有自已和主播可见</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">全区弹幕价格</td>
                                    <td>
                                        <input class="base_text" name="live_setting[horn_fee]"  value="<?php echo htmlentities($_info['horn_fee']); ?>"/>
                                        <span>单位：<?php echo APP_BEAN_NAME; ?></span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">入房金光等级</td>
                                    <td>
                                        <input class="base_text" name="live_setting[rank_golden_light]"  value="<?php echo htmlentities($_info['rank_golden_light']); ?>"/>

                                    </td>
                                </tr>-->
                                <tr>
                                    <td class="field_name">发送弹幕等级</td>
                                    <td>
                                        <input class="base_text" name="live_setting[barrage_level]"  value="<?php echo htmlentities($_info['barrage_level']); ?>"/>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="field_name">发送消息等级</td>
                                    <td>
                                        <input class="base_text" name="live_setting[message_level]"  value="<?php echo htmlentities($_info['message_level']); ?>"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">连麦等级</td>
                                    <td>
                                        <input class="base_text" name="live_setting[mike_level]"  value="<?php echo htmlentities($_info['mike_level']); ?>"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">活动背包礼物是否算业绩</td>
                                    <td>
                                        <select class="base_select" name="live_setting[bag_prifit_status]" selectedval="<?php echo !empty($_info['bag_prifit_status']) ? '1'  :  '0'; ?>">
                                            <option value="1">是</option>
                                            <option value="0">否</option>
                                        </select>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="field_name">是否显示休息主播列表</td>
                                    <td>
                                        <select class="base_select" name="live_setting[is_rest_display]" selectedval="<?php echo !empty($_info['is_rest_display']) ? '1'  :  '0'; ?>">
                                            <option value="1">是</option>
                                            <option value="0">否</option>
                                        </select>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="field_name">房间主播uid前缀名称</td>
                                    <td>
                                        <input class="base_text" name="live_setting[live_room_name]"  value="<?php echo htmlentities($_info['live_room_name']); ?>"/>
                                    </td>
                                </tr>

                            </table>
                        </div>

                        <div class="layui-tab-item">
                            <table class="content_info2 mt_10">
                                <tr>
                                    <td class="field_name">应用Id</td>
                                    <td>
                                        <input class="base_text" name="live_setting[platform_config][secret_id]" value="<?php echo htmlentities($_info['platform_config']['secret_id']); ?>"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">应用Access_key</td>
                                    <td>
                                        <input class="base_text" name="live_setting[platform_config][access_key]" value="<?php echo htmlentities($_info['platform_config']['access_key']); ?>"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">应用Secret_key</td>
                                    <td>
                                        <input class="base_text" name="live_setting[platform_config][secret_key]" value="<?php echo htmlentities($_info['platform_config']['secret_key']); ?>"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">推流地址</td>
                                    <td>
                                        <input class="base_text" name="live_setting[platform_config][push]" value="<?php echo htmlentities($_info['platform_config']['push']); ?>"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">播流地址</td>
                                    <td>
                                        <input class="base_text" name="live_setting[platform_config][pull]" value="<?php echo htmlentities($_info['platform_config']['pull']); ?>"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">图片Snapshort</td>
                                    <td>
                                        <input class="base_text" name="live_setting[platform_config][snapshort]" value="<?php echo htmlentities($_info['platform_config']['snapshort']); ?>"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">流空间名</td>
                                    <td>
                                        <input class="base_text" name="live_setting[platform_config][live_space_name]" value="<?php echo htmlentities($_info['platform_config']['live_space_name']); ?>"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">图片空间名</td>
                                    <td>
                                        <input class="base_text" name="live_setting[platform_config][img_space_name]" value="<?php echo htmlentities($_info['platform_config']['img_space_name']); ?>"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">流前缀</td>
                                    <td>
                                        <input class="base_text" name="live_setting[platform_config][stream_prefix]" value="<?php echo htmlentities($_info['platform_config']['stream_prefix']); ?>"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">有效期</td>
                                    <td>
                                        <input class="base_text" name="live_setting[platform_config][ext]" value="<?php echo htmlentities($_info['platform_config']['ext']); ?>"/>
                                    </td>
                                </tr>
                                <tr>

                                    <td class="field_name">播流协议</td>
                                    <td>
                                        <select class="base_select" name="live_setting[platform_config][pull_protocol]"
                                                selectedval="<?php echo htmlentities($_info['platform_config']['pull_protocol']); ?>">
                                            <option value="rtmp">RTMP</option>
                                            <option value="hls">M3U8</option>
                                            <option value="hdl">FLV</option>

                                        </select>
                                    </td>

                                </tr>
                            </table>
                        </div>

                        <div class="layui-tab-item">
                            <table class="content_info2 mt_10">
                                <tr>
                                    <td class="field_name">等级检查</td>
                                    <td>
                                        <select class="base_select" name="live_setting[validate_level]" selectedval="<?php echo !empty($_info['validate_level']) ? '1'  :  '0'; ?>">
                                            <option value="1">启用</option>
                                            <option value="0">禁用</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">等级条件</td>
                                    <td>
                                        <input class="base_text" name="live_setting[validate_level_value]" value="<?php echo htmlentities($_info['validate_level_value']); ?>"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">黑名单检查</td>
                                    <td>
                                        <select class="base_select" name="live_setting[validate_black]" selectedval="<?php echo !empty($_info['validate_black']) ? '1'  :  '0'; ?>">
                                            <option value="1">启用</option>
                                            <option value="0">禁用</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">禁播检查</td>
                                    <td>
                                        <select class="base_select" name="live_setting[validate_banned]" selectedval="<?php echo !empty($_info['validate_banned']) ? '1'  :  '0'; ?>">
                                            <option value="1">启用</option>
                                            <option value="0">禁用</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">禁播天数</td>
                                    <td>
                                        <input class="base_text" name="live_setting[validate_banned_value]" value="<?php echo htmlentities($_info['validate_banned_value']); ?>"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">实名认证检查</td>
                                    <td>
                                        <select class="base_select" name="live_setting[validate_verified]" selectedval="<?php echo !empty($_info['validate_verified']) ? '1'  :  '0'; ?>">
                                            <option value="1">启用</option>
                                            <option value="0">禁用</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">开播权限检查</td>
                                    <td>
                                        <select class="base_select" name="live_setting[validate_live_status]" selectedval="<?php echo !empty($_info['validate_live_status']) ? '1'  :  '0'; ?>">
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
                                    <td class="field_name"></td>
                                    <td>
                                        <div class="base_group">
                                            <span class="base_label" style="width: 92px;text-align: center;">是否开启</span>
                                            <span class="base_label" style="width: 92px;text-align: center;">最大</span>
                                            <span class="base_label" style="width: 95px;text-align: center;">最小</span>
                                            <span class="base_label" style="width: 215px;text-align: center;">显示标题</span>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">直播时长</td>
                                    <td>
                                        <select class="base_select"  style="width: 92px;text-align: center;"  name="task_setting[live_duration][status]" selectedval="<?php echo !empty($_task['live_duration']['status']) ? '1'  :  '0'; ?>">
                                            <option value="1">启用</option>
                                            <option value="0">禁用</option>
                                        </select>
                                        <input class="base_text" name="task_setting[live_duration][max]" style="width: 80px;" value="<?php echo htmlentities($_task['live_duration']['max']); ?>"/>
                                        <input class="base_text" name="task_setting[live_duration][min]" style="width: 80px;" value="<?php echo htmlentities($_task['live_duration']['min']); ?>"/>
                                        <input class="base_text" name="task_setting[live_duration][title]" style="width: 208px;" value="<?php echo htmlentities($_task['live_duration']['title']); ?>"/>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="field_name"></td>
                                    <td>
                                        <div class="base_group">
                                            <span class="base_label" style="width: 92px;text-align: center;">是否开启</span>
                                            <span class="base_label" style="width: 92px;text-align: center;">最大</span>
                                            <span class="base_label" style="width: 95px;text-align: center;">最小</span>
                                            <span class="base_label" style="width: 215px;text-align: center;">显示标题</span>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">点亮次数</td>
                                    <td>
                                        <select class="base_select"  style="width: 92px;text-align: center;" name="task_setting[light_num][status]" selectedval="<?php echo !empty($_task['light_num']['status']) ? '1'  :  '0'; ?>">
                                            <option value="1">启用</option>
                                            <option value="0">禁用</option>
                                        </select>
                                        <input class="base_text" name="task_setting[light_num][max]" style="width: 80px;" value="<?php echo htmlentities($_task['light_num']['max']); ?>"/>
                                        <input class="base_text" name="task_setting[light_num][min]" style="width: 80px;" value="<?php echo htmlentities($_task['light_num']['min']); ?>"/>
                                        <input class="base_text" name="task_setting[light_num][title]" style="width: 208px;" value="<?php echo htmlentities($_task['light_num']['title']); ?>"/>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="field_name"></td>
                                    <td>
                                        <div class="base_group">
                                            <span class="base_label" style="width: 92px;text-align: center;">是否开启</span>
                                            <span class="base_label" style="width: 92px;text-align: center;">最大</span>
                                            <span class="base_label" style="width: 95px;text-align: center;">最小</span>
                                            <span class="base_label" style="width: 215px;text-align: center;">显示标题</span>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">直播收益</td>
                                    <td>
                                        <select class="base_select"   style="width: 92px;text-align: center;" name="task_setting[gift_profit][status]" selectedval="<?php echo !empty($_task['gift_profit']['status']) ? '1'  :  '0'; ?>">
                                            <option value="1">启用</option>
                                            <option value="0">禁用</option>
                                        </select>
                                        <input class="base_text" name="task_setting[gift_profit][max]" style="width: 80px;" value="<?php echo htmlentities($_task['gift_profit']['max']); ?>"/>
                                        <input class="base_text" name="task_setting[gift_profit][min]" style="width: 80px;" value="<?php echo htmlentities($_task['gift_profit']['min']); ?>"/>
                                        <input class="base_text" name="task_setting[gift_profit][title]" style="width: 208px;" value="<?php echo htmlentities($_task['gift_profit']['title']); ?>"/>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="field_name"></td>
                                    <td>
                                        <div class="base_group">
                                            <span class="base_label" style="width: 92px;text-align: center;">是否开启</span>
                                            <span class="base_label" style="width: 92px;text-align: center;">最大</span>
                                            <span class="base_label" style="width: 95px;text-align: center;">最小</span>
                                            <span class="base_label" style="width: 215px;text-align: center;">显示标题</span>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">新增粉丝</td>
                                    <td>
                                        <select class="base_select"  style="width: 92px;text-align: center;" name="task_setting[new_fans][status]" selectedval="<?php echo !empty($_task['new_fans']['status']) ? '1'  :  '0'; ?>">
                                            <option value="1">启用</option>
                                            <option value="0">禁用</option>
                                        </select>
                                        <input class="base_text" name="task_setting[new_fans][max]" style="width: 80px;" value="<?php echo htmlentities($_task['new_fans']['max']); ?>"/>
                                        <input class="base_text" name="task_setting[new_fans][min]" style="width: 80px;" value="<?php echo htmlentities($_task['new_fans']['min']); ?>"/>
                                        <input class="base_text" name="task_setting[new_fans][title]" style="width: 208px;" value="<?php echo htmlentities($_task['new_fans']['title']); ?>"/>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="field_name"></td>
                                    <td>
                                        <div class="base_group">
                                            <span class="base_label" style="width: 92px;text-align: center;">是否开启</span>
                                            <span class="base_label" style="width: 92px;text-align: center;">最大</span>
                                            <span class="base_label" style="width: 95px;text-align: center;">最小</span>
                                            <span class="base_label" style="width: 215px;text-align: center;">显示标题</span>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">PK胜场</td>
                                    <td>
                                        <select class="base_select"  style="width: 92px;text-align: center;" name="task_setting[pk_win_num][status]" selectedval="<?php echo !empty($_task['pk_win_num']['status']) ? '1'  :  '0'; ?>">
                                            <option value="1">启用</option>
                                            <option value="0">禁用</option>
                                        </select>
                                        <input class="base_text" name="task_setting[pk_win_num][max]" style="width: 80px;" value="<?php echo htmlentities($_task['pk_win_num']['max']); ?>"/>
                                        <input class="base_text" name="task_setting[pk_win_num][min]" style="width: 80px;" value="<?php echo htmlentities($_task['pk_win_num']['min']); ?>"/>
                                        <input class="base_text" name="task_setting[pk_win_num][title]" style="width: 208px;" value="<?php echo htmlentities($_task['pk_win_num']['title']); ?>"/>
                                    </td>
                                </tr>

                            </table>
                        </div>

                        <div class="layui-tab-item">
                            <table class="content_info2 mt_10">
                                <tr>
                                    <td class="field_name">主播申请</td>
                                    <td>
                                        <select class="base_select" name="live_setting[user_live][front_status]" selectedval="<?php echo !empty($_info['user_live']['front_status']) ? '1'  :  '0'; ?>">
                                            <option value="1">开启</option>
                                            <option value="0">关闭</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">是否审核</td>
                                    <td>
                                        <select class="base_select" name="live_setting[user_live][verify]" selectedval="<?php echo !empty($_info['user_live']['verify']) ? '1'  :  '0'; ?>">
                                            <option value="1">开启</option>
                                            <option value="0">关闭</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">主播开通方式</td>
                                    <td>
                                        <select class="base_select" name="live_setting[user_live][open_anchor_type]" selectedval="<?php echo !empty($_info['user_live']['open_anchor_type']) ? htmlentities($_info['user_live']['open_anchor_type']) :  '0'; ?>">
                                            <option value="0">默认后台开通</option>
                                            <option value="1">带货权限中开通</option>
                                            <option value="2">用户主动申请</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">个人主播申请</td>
                                    <td>
                                        <select class="base_select" name="live_setting[user_live][person_apply]" selectedval="<?php echo !empty($_info['user_live']['person_apply']) ? '1'  :  '0'; ?>">
                                            <option value="1">开启</option>
                                            <option value="0">禁用</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">公会主播申请</td>
                                    <td>
                                        <select class="base_select" name="live_setting[user_live][agent_apply]" selectedval="<?php echo !empty($_info['user_live']['agent_apply']) ? '1'  :  '0'; ?>">
                                            <option value="1">开启</option>
                                            <option value="0">禁用</option>
                                        </select>
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <div class="layui-tab-item">
                            <table class="content_info2 mt_10">
                                <tr>
                                    <td class="field_name">开启商城</td>
                                    <td>
                                        <select class="base_select" name="live_setting[is_shop_open]" selectedval="<?php echo !empty($_info['is_shop_open']) ? '1'  :  '0'; ?>">
                                            <option value="1">开启</option>
                                            <option value="0">关闭</option>
                                        </select>
                                        <span>开启后才会有带自营商城的权限</span>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="field_name">开播前添加商品</td>
                                    <td>
                                        <select class="base_select" name="live_setting[is_goods_open]" selectedval="<?php echo !empty($_info['is_goods_open']) ? '1'  :  '0'; ?>">
                                            <option value="1">开启</option>
                                            <option value="0">关闭</option>
                                        </select>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="field_name">最多添加数量</td>
                                    <td>
                                        <input class="base_text" name="live_setting[goods_max_num]" value="<?php echo htmlentities($_info['goods_max_num']); ?>"/>
                                    </td>
                                </tr>
                                <tr style="display: none">
                                    <td class="field_name">保存时间</td>
                                    <td>
                                        <input class="base_text" name="live_setting[goods_save_time]"  value="<?php echo htmlentities($_info['goods_save_time']); ?>"/>
                                        <span>单位：分钟(添加完没立即开播保存的时间)</span>
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
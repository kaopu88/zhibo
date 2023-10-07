<?php /*a:7:{s:64:"/www/wwwroot/zhibb/application/friend/view/config/baseconfig.tpl";i:1599699724;s:62:"/www/wwwroot/zhibb/application/friend/view/public/base_nav.tpl";i:1593227892;s:62:"/www/wwwroot/zhibb/application/friend/view/public/jsconfig.tpl";i:1592625950;s:62:"/www/wwwroot/zhibb/application/friend/view/public/main_top.tpl";i:1593518960;s:60:"/www/wwwroot/zhibb/application/friend/view/public/toggle.tpl";i:1592653918;s:65:"/www/wwwroot/zhibb/application/friend/view/components/tab_nav.tpl";i:1592625950;s:66:"/www/wwwroot/zhibb/application/friend/view/components/work_pop.tpl";i:1592625950;}*/ ?>
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
        .main_top_logo a {
            text-align: center;
            line-height: 61px;
            font-size: 22px;
        }

        .main_top_logo span {
            color: #fff;
        }

        .main_top_logo a:link {
            text-decoration: none;
        }

        .main_top_logo a:visited {
            text-decoration: none;
        }

        .main_top_logo a:hover {
            text-decoration: none;
        }

        .main_top_logo a:active {
            text-decoration: none;
        }
    </style>
    
    <style>
        .distribute_reward {
            display: none;
        }
        .content_info2 td.field_name {
            text-align: right;
            padding-right: 10px;
            padding-left: 0;
            font-size: 14px;
            width: 350px;
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
        select_agents: '<?php echo url("admin/user_transfer/select_agents"); ?>',//选择<?php echo config('app.agent_setting.agent_name'); ?>
        send_sms_url: '<?php echo url("admin/common/send_sms"); ?>',
        user_reg: '<?php echo url("admin/user/reg"); ?>',
        user_role: '<?php echo url("admin/user_roler/setting"); ?>',
        change_work_status: '<?php echo url("admin/personal/change_work_status"); ?>',
        change_work_sms_status: '<?php echo url("admin/personal/change_work_sms_status"); ?>',
        get_unread_num: '<?php echo url("admin/personal/get_unread_num"); ?>',
        get_promoter_cons_trend: '<?php echo url("admin/promoter/get_cons_trend"); ?>',
        get_recharge_consume_trend: '<?php echo url("admin/index/get_recharge_consume_trend"); ?>',
        get_video_info: '<?php echo url("admin/common/get_video_info"); ?>'
    };
    var _domConfig = {};
    var bean_name = '<?php echo APP_BEAN_NAME; ?>';
</script>
    <script src="/static/vendor/jquery.min.js?v=<?php echo config('upload.resource_version'); ?>"></script>
    <script src="/static/vendor/layer/layer.js?v=<?php echo config('upload.resource_version'); ?>"></script>
    <script src="/static/vendor/jquery.nicescroll.min.js?v=<?php echo config('upload.resource_version'); ?>"></script>
    <script src="/static/vendor/jquery.cookie.js?v=<?php echo config('upload.resource_version'); ?>"></script>
    <script src="/static/vendor/flatpickr/flatpickr.min.js?v=<?php echo config('upload.resource_version'); ?>"></script>
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
                    <a href="<?php echo url('/admin/index/index'); ?>">
                        <span><?php echo config('site.company_full_name'); ?></span>
                    </a>
                </div>
                <div class="icon_more"></div>
                <div class="menu_icon" title="主菜单"></div>
            </div>
            <ul class="tool_list">
                <li><a title="网站首页" target="_self" href="<?php echo url('/admin/index/index'); ?>"><span class="icon-home"></span></a>
                </li>
                <li>
                    <a poplink="work_box" title="任务设置" href="javascript:;">
                        <span class="icon-light-bulb"></span>&nbsp;<span style="font-size: 12px;"
                                                                        class="badge_work_num">0</span>
                    </a>
                </li>
                <li><a title="退出" ajax-confirm="是否确认退出？" confirm ajax="get" href="<?php echo url('/admin/account/logout'); ?>"><span
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
                        <li><a href="<?php echo url('/admin/personal/base_info'); ?>"><?php echo htmlentities(user_name($admin)); ?></a></li>
                        <li><a href="<?php echo url('/admin/personal/change_pwd'); ?>">修改密码</a></li>
                        <li><a poplink="work_box" href="javascript:;">任务设置</a></li>
                        <li><a ajax-confirm ajax="get" href="<?php echo url('/admin/account/logout'); ?>">退出</a></li>
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
                                        <?php if(!(empty($menu3['icon']) || (($menu3['icon'] instanceof \think\Collection || $menu3['icon'] instanceof \think\Paginator ) && $menu3['icon']->isEmpty()))): ?><span
                                                    class="sidebar_menu_icon"><?php echo htmlentities($menu3['icon']); ?></span>
                                        <?php endif; ?>
                                        <?php echo htmlentities($menu3['name']); ?><span unread-types="<?php echo htmlentities($menu3['badge']); ?>"
                                                           class="badge_unread">0</span></a>
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


            
    <div class="pa_20 p-0 show_bottom">
        <ul class="tab_nav mt_10">
    <?php if(is_array($admin_tree[3]['children']) || $admin_tree[3]['children'] instanceof \think\Collection || $admin_tree[3]['children'] instanceof \think\Paginator): $i = 0; $__LIST__ = $admin_tree[3]['children'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$menu4): $mod = ($i % 2 );++$i;?>
        <li><a target="<?php echo htmlentities($menu4['target']); ?>" class="<?php echo htmlentities($menu4['current']); ?>" href="<?php echo htmlentities($menu4['menu_url']); ?>"><?php echo htmlentities($menu4['name']); ?><span
                        unread-types="<?php echo htmlentities($menu4['badge']); ?>" class="badge_unread">0</span></a></li>
    <?php endforeach; endif; else: echo "" ;endif; ?>
</ul>

<style>
    .pa_20 > ul {
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

    .base_button {
        float: left;
        margin-left: 445px;
    }

    .base_button_a {
        margin-right: 65.5%;
        float: right;
        margin-left: 0;
    }

</style>
<link rel="stylesheet" href="/static/vendor/layer/layui/css/layui.css">
<script src="/static/vendor/layer/layui/layui.js"></script>
<script>
    layui.use(['element', 'layer'], function () {
        var element = layui.element, layer = layui.layer;
    });
</script>
            <form action="<?php echo url('baseconfig'); ?>">
            <div class="table_slide">
                <table class="content_info2 mt_10 font_normal table_fixed sm_width">
                    <tr>
                        <td class="field_name" style="width:110px;">是否启用</td>
                        <td>
                            <select class="base_select" name="is_open" selectedval="<?php echo !empty($_info['is_open']) ? '1'  :  '0'; ?>">
                                <option value="1">启用</option>
                                <option value="0">禁用</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td class="field_name">发布图片长度</td>
                        <td>
                            <input class="base_text" name="msg_img_length" value="<?php echo htmlentities($_info['msg_img_length']); ?>"/>
                        </td>
                    </tr>
                    <tr>
                        <td class="field_name">评论图片长度</td>
                        <td>
                            <input class="base_text" name="comment_img_length" value="<?php echo htmlentities($_info['comment_img_length']); ?>"/>
                        </td>
                    </tr>
                    <tr hidden>
                        <td class="field_name">评论间隔时间(如果可以进行重复评论)</td>
                        <td>
                            <input class="base_text" name="comment_interval" value="<?php echo htmlentities($_info['comment_interval']); ?>"/> 秒
                        </td>
                    </tr>
                    <tr>
                        <td class="field_name">单条可以评论的次数</td>
                        <td>
                            <input class="base_text" name="comment_total_num" value="<?php echo htmlentities($_info['comment_total_num']); ?>"/>
                        </td>
                    </tr>
                    <tr hidden >
                        <td class="field_name">对评论进行留言的间隔时间(如果可以进行重复留言)</td>
                        <td>
                            <input class="base_text" name="comment_evaluate_interval"
                                value="<?php echo htmlentities($_info['comment_evaluate_interval']); ?>"/> 秒
                        </td>
                    </tr>
                    <tr>
                        <td class="field_name">单条可以留言的次数</td>
                        <td>
                            <input class="base_text" name="comment_evaluate_total_num"
                                value="<?php echo htmlentities($_info['comment_evaluate_total_num']); ?>"/>
                        </td>
                    </tr>

                    <tr>
                        <td class="field_name">推荐话题展示数量</td>
                        <td>
                            <input class="base_text" name="recommend_topic_num"
                                value="<?php echo htmlentities($_info['recommend_topic_num']); ?>"/>
                        </td>
                    </tr>
                    <tr>
                        <td class="field_name">热门话题展示数量</td>
                        <td>
                            <input class="base_text" name="hot_topic_num"
                                value="<?php echo htmlentities($_info['hot_topic_num']); ?>"/>
                        </td>
                    </tr>
                    <tr>
                        <td class="field_name">最新话题展示数量</td>
                        <td>
                            <input class="base_text" name="new_topic_num"
                                value="<?php echo htmlentities($_info['new_topic_num']); ?>"/>
                        </td>
                    </tr>
                    <tr>
                        <td class="field_name">历史话题展示数量</td>
                        <td>
                            <input class="base_text" name="history_topic_num"
                                value="<?php echo htmlentities($_info['history_topic_num']); ?>"/>
                        </td>
                    </tr>


                    <tr>
                        <td class="field_name">非好友可发信息数量</td>
                        <td>
                            <input class="base_text" name="chat_num"
                                value="<?php echo htmlentities($_info['chat_num']); ?>"/>
                        </td>
                    </tr>
                    <tr>
                        <td class="field_name">每日可创建发圈的数量</td>
                        <td>
                            <input class="base_text" name="create_circle_num"
                                value="<?php echo htmlentities($_info['create_circle_num']); ?>"/>
                        </td>
                    </tr>

                    <tr>
                        <td class="field_name">圈子多长时间修改一次</td>
                        <td>
                            <input class="base_text" name="circle_update_day"
                                value="<?php echo htmlentities($_info['circle_update_day']); ?>"/> 天
                        </td>

                    </tr>

                    <tr>
                        <td class="field_name">发布动态话题最高数量</td>
                        <td>
                            <input class="base_text" name="create_dynamic_num"
                                value="<?php echo htmlentities($_info['create_dynamic_num']); ?>"/>
                        </td>
                    </tr>
                    <tr>
                        <td class="field_name">用户动态是否需要审核</td>
                        <td>
                            <select class="base_select" name="msg_examine" selectedval="<?php echo !empty($_info['msg_examine']) ? '1'  :  '0'; ?>">
                                <option value="1">启用</option>
                                <option value="0">关闭</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td class="field_name">用户回复是否需要审核</td>
                        <td>
                            <select class="base_select" name="msg_commment_examine"
                                    selectedval="<?php echo !empty($_info['msg_commment_examine']) ? '1'  :  '0'; ?>">
                                <option value="1">启用</option>
                                <option value="0">关闭</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td class="field_name">用户回复评论是否需要审核</td>
                        <td>
                            <select class="base_select" name="msg_commment_evaluate_examine"
                                    selectedval="<?php echo !empty($_info['msg_commment_evaluate_examine']) ? '1'  :  '0'; ?>">
                                <option value="1">启用</option>
                                <option value="0">关闭</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td class="field_name">附近的人最大距离</td>
                        <td>
                            <input class="base_text" name="friend_near_max"
                                value="<?php echo htmlentities($_info['friend_near_max']); ?>"/>千米
                        </td>

                    </tr>
                    <tr>
                        <td class="field_name">圈子默认背景图</td>
                        <td>
                            <div class="base_group" style="float: left;">
                                <input style="width: 308px;float: left;" name="citcle_defaut_back" value="<?php echo htmlentities($_info['citcle_defaut_back']); ?>" type="text" class="base_text border_left_radius"/>
                                <a uploader-type="image" href="javascript:;" class="base_button border_right_radius" style="margin-left: 0;" uploader="taoke_images" uploader-field="citcle_defaut_back">上传</a>
                            </div>
                            <a rel="thumb" href="<?php echo img_url($_info['citcle_defaut_back'],'','thumb'); ?>" class="thumb thumb_img user_base_avatar fancybox" alt="">
                            <div imgview="[name=citcle_defaut_back]" style="width: 20px;"><img src="<?php echo htmlentities($_info['citcle_defaut_back']); ?>" class="preview"/></div>
                        </td>
                    </tr>

                    <tr>
                        <td class="field_name">圈子默认封面</td>
                        <td>
                            <div class="base_group" style="float: left;">
                                <input style="width: 308px;float: left;" name="citcle_defaut_cover" value="<?php echo htmlentities($_info['citcle_defaut_cover']); ?>" type="text" class="base_text border_left_radius"/>
                                <a uploader-type="image" href="javascript:;" class="base_button border_right_radius" style="margin-left: 0;" uploader="taoke_images" uploader-field="citcle_defaut_cover">上传</a>
                            </div>
                            <a rel="thumb" href="<?php echo img_url($_info['citcle_defaut_cover'],'','thumb'); ?>" class="thumb thumb_img user_base_avatar fancybox" alt="">
                                <div imgview="[name=citcle_defaut_cover]" style="width: 20px;"><img src="<?php echo htmlentities($_info['citcle_defaut_cover']); ?>" class="preview"/></div>

                    </tr>
                    <tr>
                        <td class="field_name">用户图标</td>
                        <td>
                            <div class="base_group" style="float: left;">
                                <input style="width: 308px;float: left;" name="citcle_defaut_user" value="<?php echo htmlentities($_info['citcle_defaut_user']); ?>" type="text" class="base_text border_left_radius"/>
                                <a uploader-type="image" href="javascript:;" class="base_button border_right_radius" style="margin-left: 0;" uploader="taoke_images" uploader-field="citcle_defaut_user">上传</a>
                            </div>
                            <a rel="thumb" href="<?php echo img_url($_info['citcle_defaut_user'],'','thumb'); ?>" class="thumb thumb_img user_base_avatar fancybox" alt="">
                                <div imgview="[name=citcle_defaut_user]" style="width: 20px;"><img src="<?php echo htmlentities($_info['citcle_defaut_user']); ?>" class="preview"/></div>

                    </tr>
                    <tr>
                        <td class="field_name">动态图标</td>
                        <td>
                            <div class="base_group" style="float: left;">
                                <input style="width: 308px;float: left;" name="citcle_defaut_dynamic" value="<?php echo htmlentities($_info['citcle_defaut_dynamic']); ?>" type="text" class="base_text border_left_radius"/>
                                <a uploader-type="image" href="javascript:;" class="base_button border_right_radius" style="margin-left: 0;" uploader="taoke_images" uploader-field="citcle_defaut_dynamic">上传</a>
                            </div>
                            <a rel="thumb" href="<?php echo img_url($_info['citcle_defaut_dynamic'],'','thumb'); ?>" class="thumb thumb_img user_base_avatar fancybox" alt="">
                                <div imgview="[name=citcle_defaut_dynamic]" style="width: 20px;"><img src="<?php echo htmlentities($_info['citcle_defaut_dynamic']); ?>" class="preview"/></div>

                    </tr>
                    <tr>
                        <td class="field_name">直播图标</td>
                        <td>
                            <div class="base_group" style="float: left;">
                                <input style="width: 308px;float: left;" name="citcle_defaut_live" value="<?php echo htmlentities($_info['citcle_defaut_live']); ?>" type="text" class="base_text border_left_radius"/>
                                <a uploader-type="image" href="javascript:;" class="base_button border_right_radius" style="margin-left: 0;" uploader="taoke_images" uploader-field="citcle_defaut_live">上传</a>
                            </div>
                            <a rel="thumb" href="<?php echo img_url($_info['citcle_defaut_live'],'','thumb'); ?>" class="thumb thumb_img user_base_avatar fancybox" alt="">
                                <div imgview="[name=citcle_defaut_live]" style="width: 20px;"><img src="<?php echo htmlentities($_info['citcle_defaut_live']); ?>" class="preview"/></div>

                    </tr>
                    <tr>
                        <td class="field_name">小视频图标</td>
                        <td>
                            <div class="base_group" style="float: left;">
                                <input style="width: 308px;float: left;" name="citcle_defaut_video" value="<?php echo htmlentities($_info['citcle_defaut_video']); ?>" type="text" class="base_text border_left_radius"/>
                                <a uploader-type="image" href="javascript:;" class="base_button border_right_radius" style="margin-left: 0;" uploader="taoke_images" uploader-field="citcle_defaut_video">上传</a>
                            </div>
                            <a rel="thumb" href="<?php echo img_url($_info['citcle_defaut_video'],'','thumb'); ?>" class="thumb thumb_img user_base_avatar fancybox" alt="">
                                <div imgview="[name=citcle_defaut_video]" style="width: 20px;"><img src="<?php echo htmlentities($_info['citcle_defaut_video']); ?>" class="preview"/></div>

                    </tr>

                </table>
                <div class="base_button_div" style="max-width:547px;">
                    <a href="javascript:;" class="base_button" ajax="post">提交</a>
                </div>
                </div>
            </form>
        
    </div>

    <script>
        $(function () {

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
<?php /*a:12:{s:59:"/www/wwwroot/zhibb/application/admin/view/anchor/detail.tpl";i:1625034972;s:61:"/www/wwwroot/zhibb/application/admin/view/public/base_nav.tpl";i:1693212818;s:61:"/www/wwwroot/zhibb/application/admin/view/public/jsconfig.tpl";i:1602499828;s:61:"/www/wwwroot/zhibb/application/admin/view/public/main_top.tpl";i:1593518960;s:59:"/www/wwwroot/zhibb/application/admin/view/public/toggle.tpl";i:1592625950;s:60:"/www/wwwroot/zhibb/application/admin/view/user/user_info.tpl";i:1694070544;s:62:"/www/wwwroot/zhibb/application/admin/view/user/user_agent2.tpl";i:1592625950;s:65:"/www/wwwroot/zhibb/application/admin/view/components/work_pop.tpl";i:1567562304;s:65:"/www/wwwroot/zhibb/application/admin/view/rank/millet_handler.tpl";i:1568606376;s:61:"/www/wwwroot/zhibb/application/admin/view/user/remark_pop.tpl";i:1592625950;s:69:"/www/wwwroot/zhibb/application/admin/view/anchor/add_anchor_guard.tpl";i:1567562314;s:68:"/www/wwwroot/zhibb/application/admin/view/anchor/add_live_manage.tpl";i:1567562314;}*/ ?>
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
    
    <link href="<?php echo ('/static/'.strtolower(\think\facade\Request::module()).'/css'); ?>/user/detail.css?v=<?php echo config('upload.resource_version'); ?>" rel="stylesheet" type="text/css"/>
    <style>
        .show_num td {
            width: 12.5%;
        }
        .thumb {
            width: 50%;
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
    
    <script src="<?php echo ('/static/'.strtolower(\think\facade\Request::module()).'/js'); ?>/user/detail.js?v=<?php echo config('upload.resource_version'); ?>"></script>

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


            
    <div class="pa_20">
        <div class="content_title">
            <h1><?php echo htmlentities($admin_last['name']); ?></h1>
            <a href="javascript:;" class="base_button base_button_gray base_button_s history_back">返回</a>
        </div>
        <div class="user_base">
            <a rel="avatar" href="<?php echo img_url($user['avatar'],'','avatar'); ?>" class="user_base_avatar fancybox" alt="">
                <img src="<?php echo img_url($user['avatar'],'200_200','avatar'); ?>"/>
                <div class="thumb_level_box">
                    <img title="<?php echo htmlentities($user['level_name']); ?>" src="<?php echo htmlentities($user['level_icon']); ?>"/>
                </div>
            </a>
            <div class="user_base_info">
                <div class="user_base_title">
                    <h1 style="color: #FF12A6;">[<?php echo htmlentities($user['user_id']); ?>]&nbsp;<?php echo htmlentities($user['nickname']); ?>&nbsp;&nbsp;</h1>
                    <?php if($user['verified'] == '1'): ?><span class="fc_green">已认证</span>
                        <?php else: ?>
                        <span class="fc_gray">未认证</span><?php endif; if(check_auth('admin:user:remark',AUTH_UID)): ?>
                        &nbsp;&nbsp;&nbsp;
                        <a data-id="user_id:<?php echo htmlentities($user['user_id']); ?>" poplink="user_remark_box" href="javascript:;">
                            <?php echo htmlentities((isset($user['remark_name']) && ($user['remark_name'] !== '')?$user['remark_name']:'未备注')); ?>
                            <span class="icon-pencil"></span>
                        </a>
                    <?php endif; ?>
                </div>
                <p>
                    <span class="icon-phone"></span>&nbsp;<?php echo htmlentities((str_hide($user['phone'],3,4) ?: '未绑定')); switch($user['gender']): case "0": ?>保密<?php break; case "1": ?><span class="fc_blue">男</span><?php break; case "2": ?><span class="fc_magenta">女</span><?php break; endswitch; ?>
                    &nbsp;&nbsp;<?php echo htmlentities((isset($user['province_name']) && ($user['province_name'] !== '')?$user['province_name']:'省份')); ?>-<?php echo htmlentities((isset($user['city_name']) && ($user['city_name'] !== '')?$user['city_name']:'城市')); ?>&nbsp;&nbsp;<?php echo htmlentities((isset($user['birthday']) && ($user['birthday'] !== '')?$user['birthday']:'生日')); ?>
                    &nbsp;&nbsp;
                    <?php switch($user['vip_status']): case "0": ?>
                            <span class="fc_gray">VIP <?php echo htmlentities($user['vip_expire_str']); ?></span>
                        <?php break; case "1": ?>
                            <span class="fc_green">VIP <?php echo htmlentities($user['vip_expire_str']); ?></span>
                        <?php break; case "2": ?>
                            <span class="fc_red">VIP <?php echo htmlentities($user['vip_expire_str']); ?></span>
                        <?php break; endswitch; ?>
                  <!--  <?php if($user['isvirtual'] == '1'): ?>
                        &nbsp;&nbsp;<a class="fc_red" href="">虚拟用户</a>
                    <?php endif; ?>-->
                    <?php if($user['is_promoter'] == '1'): ?>
                        &nbsp;&nbsp;<a class="fc_orange" href="<?php echo url('promoter/detail',['user_id'=>$user['user_id']]); ?>"><?php echo config('app.agent_setting.promoter_name'); ?></a>
                    <?php endif; if($user['is_creation'] == '1'): ?>
                        &nbsp;&nbsp;<a class="fc_orange" href="">创作号</a>
                    <?php endif; ?>
                    &nbsp;&nbsp;<a href="<?php echo url('user/detail',['user_id'=>$user['user_id']]); ?>">转到用户详情</a>
                </p>
                <p>
                    <?php echo htmlentities((isset($user['sign']) && ($user['sign'] !== '')?$user['sign']:'这个家伙太懒了，什么也没留下。')); ?>
                </p>
                <p>
                    <?php if(empty($user['agent_info'])): ?>
                        直属用户
                        <?php else: if(!(empty($user['agent_info']) || (($user['agent_info'] instanceof \think\Collection || $user['agent_info'] instanceof \think\Paginator ) && $user['agent_info']->isEmpty()))): ?>
                            <?php echo config('app.agent_setting.agent_name'); ?>：<a href=""><?php echo htmlentities($user['agent_info']['name']); ?></a>
                        <?php endif; if(!(empty($user['promoter_info']) || (($user['promoter_info'] instanceof \think\Collection || $user['promoter_info'] instanceof \think\Paginator ) && $user['promoter_info']->isEmpty()))): ?>
                            &nbsp;&nbsp; <?php echo config('app.agent_setting.promoter_name'); ?>：<a href=""><?php echo htmlentities(user_name($user['promoter_info'])); ?></a>
                        <?php endif; endif; ?>
                    &nbsp;&nbsp;加入时间：<?php echo htmlentities(time_format($user['anchor']['create_time'],'','date')); ?>
                </p>
            </div>


            <ul class="user_base_btns">
                <?php if(check_auth('admin:user:change_status',AUTH_UID)): ?>
                    <li>
                        <div tgradio-not="0" tgradio-on="1" tgradio-off="0" tgradio-value="<?php echo htmlentities($user['status']); ?>"
                             tgradio-name="status"
                             tgradio="<?php echo url('user/change_status',['id'=>$user['user_id']]); ?>"></div>
                    </li>
                <?php endif; ?>
                <li>
                    <a ajax="get" ajax-reload="false" href="<?php echo url('user/refresh_redis',['user_id'=>$user['user_id']]); ?>">
                        <span class="icon-reload"></span>&nbsp;刷新数据
                    </a>
                </li>
            </ul>
            <div class="clear"></div>
        </div>
        <div class="table_slide">
            <table class="content_list mt_10 show_num md_width">
                <tr>
                    <td>
                        累计收获<?php echo APP_MILLET_NAME; ?>
                        <br/>
                        <span class="show_num_span"><?php echo htmlentities($user['anchor']['total_millet']); ?></span>
                    </td>
                    <td>
                        累计直播时长
                        <br/>
                        <span class="show_num_span"><?php echo htmlentities($user['anchor']['total_duration_str']); ?></span>
                    </td>
                    <td>
                        收到的赞
                        <br/>
                        <span class="show_num_span"><?php echo htmlentities($user['like_num']); ?></span>
                    </td>
                    <td>
                        粉丝数量
                        <br/>
                        <span class="show_num_span"><?php echo htmlentities($user['fans_num']); ?></span>
                    </td>
                    <td>
                        关注数量
                        <br/>
                        <span class="show_num_span"><?php echo htmlentities($user['follow_num']); ?></span>
                    </td>
                    <td>
                        收藏数量
                        <br/>
                        <span class="show_num_span"><?php echo htmlentities($user['collection_num']); ?></span>
                    </td>
                    <td>
                        下载数量
                        <br/>
                        <span class="show_num_span"><?php echo htmlentities($user['download_num']); ?></span>
                    </td>
                    <td>
                        信用评分
                        <br/>
                        <span class="show_num_span"><?php echo htmlentities($user['credit_score']); ?></span>
                    </td>
                </tr>
            </table>
        </div>
        <div class="content_title2">
            <?php echo APP_MILLET_NAME; ?>
            <div class="content_links">
                <a href="">变更记录</a>
                <a href="">提现记录</a>
            </div>
        </div>
        <div class="table_slide">
            <table class="content_list mt_10 bean_tab md_width">
                <tr>
                    <td class="field_name">剩余<?php echo APP_MILLET_NAME; ?></td>
                    <td class="field_value"><?php echo htmlentities($user['millet']); ?></td>
                    <td class="field_name">冻结<?php echo APP_MILLET_NAME; ?></td>
                    <td class="field_value"><?php echo htmlentities($user['fre_millet']); ?></td>
                    <td class="field_name">累计<?php echo APP_MILLET_NAME; ?></td>
                    <td class="field_value"><?php echo htmlentities($user['his_millet']); ?></td>
                    <td class="field_name">提现功能</td>
                    <td class="field_value">
                        <div tgradio-not="<?php echo check_auth('admin:user:change_millet_status')?'0':'1'; ?>" tgradio-on="1"
                            tgradio-off="0" tgradio-value="<?php echo htmlentities($user['millet_status']); ?>"
                            tgradio-name="millet_status"
                            tgradio="<?php echo url('user/change_millet_status',['id'=>$user['user_id']]); ?>"></div>
                    </td>
                    <td class="field_name">最近提现</td>
                    <td class="field_value"><?php echo htmlentities(time_format($user['millet_change_time'],'未变动','datetime')); ?></td>
                </tr>
            </table>
        </div>
        <div class="content_title2">
            TA的守护&nbsp;(<?php echo htmlentities($guard_total); ?>)
            <div class="content_links">
                <?php if(check_auth('admin:anchor:guard',AUTH_UID)): ?>
                    <a href="javascript:;" data-query="anchor_uid=<?php echo htmlentities($user['user_id']); ?>" poplink="add_anchor_guard"><span style="margin-right: 10px;" class="icon-plus"></span>守护</a>
                <?php endif; ?>
            </div>
        </div>
        <div class="table_slide">
            <table class="content_list mt_10 md_width">
                <thead>
                <tr>
                    <td style="width: 10%;"><input type="checkbox" checkall="list_id" value="<?php echo htmlentities($vo['user_id']); ?>"/></td>
                    <td style="width: 10%;">用户ID</td>
                    <td style="width: 15%;">用户信息</td>
                    <td style="width: 15%;">到期时间</td>
                    <td style="width: 20%;">所属<?php echo config('app.agent_setting.agent_name'); ?></td>
                    <td style="width: 30%;">操作</td>
                </tr>
                </thead>
                <tbody>
                <?php if(!(empty($guards) || (($guards instanceof \think\Collection || $guards instanceof \think\Paginator ) && $guards->isEmpty()))): if(is_array($guards) || $guards instanceof \think\Collection || $guards instanceof \think\Paginator): $i = 0; $__LIST__ = $guards;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
                        <tr data-id="<?php echo htmlentities($vo['user_id']); ?>">
                            <td><input class="list_id" type="checkbox" name="ids[]" value="<?php echo htmlentities($vo['user_id']); ?>"/></td>
                            <td><?php echo htmlentities($vo['user_id']); ?></td>
                            <td>
                                <div class="thumb">
    <a href="<?php echo url('user/detail',['user_id'=>$vo['user_id']]); ?>" class="thumb_img thumb_img_avatar">
        <img src="<?php echo img_url($vo['avatar'],'200_200','avatar'); ?>"/>
        <div class="thumb_level_box">
            <img title="<?php echo htmlentities($vo['level_name']); ?>" src="<?php echo htmlentities($vo['level_icon']); ?>"/>
        </div>
    </a>
    <p class="thumb_info">
        <a href="<?php echo url('user/detail',['user_id'=>$vo['user_id']]); ?>">
            <?php echo htmlentities(user_name($vo)); if(check_auth('admin:user:remark',AUTH_UID)): ?>
                &nbsp;<a data-id="user_id:<?php echo htmlentities($vo['user_id']); ?>" poplink="user_remark_box" href="javascript:;"><span class="icon-pencil"></span></a>
            <?php endif; ?>
            <br/>
            <?php echo htmlentities($vo['username']); ?>
            
        </a>
        <?php if($vo['agent_num'] != '0'): ?>
            <br/><?php echo config('app.agent_setting.agent_name'); ?>：<?php echo htmlentities($vo['agent_name']); endif; ?>
    </p>
</div>
                            </td>
                            <td><?php echo htmlentities(time_format($vo['guard_expire'],'')); ?></td>
                            <td>
                                <div class="user_agent_info">
    <?php if($vo['agent_num'] > '0'): if(!(empty($vo['agent_list']) || (($vo['agent_list'] instanceof \think\Collection || $vo['agent_list'] instanceof \think\Paginator ) && $vo['agent_list']->isEmpty()))): if(is_array($vo['agent_list']) || $vo['agent_list'] instanceof \think\Collection || $vo['agent_list'] instanceof \think\Paginator): $i = 0; $__LIST__ = $vo['agent_list'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$fo): $mod = ($i % 2 );++$i;?>
                <?php echo htmlentities($fo['agent_name']); ?><br/>
            <?php endforeach; endif; else: echo "" ;endif; endif; if(!(empty($vo['promoter_info']) || (($vo['promoter_info'] instanceof \think\Collection || $vo['promoter_info'] instanceof \think\Paginator ) && $vo['promoter_info']->isEmpty()))): ?>
            <br/>当前<?php echo config('app.agent_setting.promoter_name'); ?>:[<?php echo htmlentities($vo['promoter_info']['user_id']); ?>]<?php echo htmlentities($vo['promoter_info']['nickname']); endif; else: if(!(empty($vo['agent_info']) || (($vo['agent_info'] instanceof \think\Collection || $vo['agent_info'] instanceof \think\Paginator ) && $vo['agent_info']->isEmpty()))): ?>
            [<?php echo htmlentities($vo['agent_info']['id']); ?>]<?php echo htmlentities($vo['agent_info']['name']); else: ?>
            无公会
        <?php endif; endif; ?>
</div>
                            </td>
                            <td>
                                <?php if(check_auth('admin:anchor:guard',AUTH_UID)): ?>
                                    <a ajax="post" ajax-confirm="是否确认移除守护？" class="fc_red"
                                    href="<?php echo url('anchor/remove_guard',['anchor_uid'=>$user['user_id'],'user_id'=>$vo['user_id']]); ?>">移除守护</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; endif; else: echo "" ;endif; else: ?>
                    <tr>
                        <td>
                            <div class="content_empty">
                                <div class="content_empty_icon"></div>
                                <p class="content_empty_text">暂无守护</p>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="content_title2">
            TA的场控&nbsp;(<?php echo htmlentities($manager_total); ?>)
            <div class="content_links">
                <?php if(check_auth('admin:anchor:live_manage',AUTH_UID)): ?>
                    <a href="javascript:;" data-query="anchor_uid=<?php echo htmlentities($user['user_id']); ?>" poplink="add_live_manage"><span style="margin-right: 10px;" class="icon-plus"></span>场控</a>
                <?php endif; ?>
            </div>
        </div>
        <div class="table_slide">
            <table class="content_list mt_10 md_width">
                <thead>
                <tr>
                    <td style="width: 10%;"><input type="checkbox" checkall="list_id" value="<?php echo htmlentities($vo['user_id']); ?>"/></td>
                    <td style="width: 10%;">用户ID</td>
                    <td style="width: 15%;">用户信息</td>
                    <td style="width: 15%;">加入时间</td>
                    <td style="width: 20%;">所属<?php echo config('app.agent_setting.agent_name'); ?></td>
                    <td style="width: 30%;">操作</td>
                </tr>
                </thead>
                <tbody>
                <?php if(!(empty($managers) || (($managers instanceof \think\Collection || $managers instanceof \think\Paginator ) && $managers->isEmpty()))): if(is_array($managers) || $managers instanceof \think\Collection || $managers instanceof \think\Paginator): $i = 0; $__LIST__ = $managers;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
                        <tr data-id="<?php echo htmlentities($vo['user_id']); ?>">
                            <td><input class="list_id" type="checkbox" name="ids[]" value="<?php echo htmlentities($vo['user_id']); ?>"/></td>
                            <td><?php echo htmlentities($vo['user_id']); ?></td>
                            <td>
                                <div class="thumb">
    <a href="<?php echo url('user/detail',['user_id'=>$vo['user_id']]); ?>" class="thumb_img thumb_img_avatar">
        <img src="<?php echo img_url($vo['avatar'],'200_200','avatar'); ?>"/>
        <div class="thumb_level_box">
            <img title="<?php echo htmlentities($vo['level_name']); ?>" src="<?php echo htmlentities($vo['level_icon']); ?>"/>
        </div>
    </a>
    <p class="thumb_info">
        <a href="<?php echo url('user/detail',['user_id'=>$vo['user_id']]); ?>">
            <?php echo htmlentities(user_name($vo)); if(check_auth('admin:user:remark',AUTH_UID)): ?>
                &nbsp;<a data-id="user_id:<?php echo htmlentities($vo['user_id']); ?>" poplink="user_remark_box" href="javascript:;"><span class="icon-pencil"></span></a>
            <?php endif; ?>
            <br/>
            <?php echo htmlentities($vo['username']); ?>
            
        </a>
        <?php if($vo['agent_num'] != '0'): ?>
            <br/><?php echo config('app.agent_setting.agent_name'); ?>：<?php echo htmlentities($vo['agent_name']); endif; ?>
    </p>
</div>
                            </td>
                            <td><?php echo htmlentities(time_format($vo['create_time'],'')); ?></td>
                            <td>
                                <div class="user_agent_info">
    <?php if($vo['agent_num'] > '0'): if(!(empty($vo['agent_list']) || (($vo['agent_list'] instanceof \think\Collection || $vo['agent_list'] instanceof \think\Paginator ) && $vo['agent_list']->isEmpty()))): if(is_array($vo['agent_list']) || $vo['agent_list'] instanceof \think\Collection || $vo['agent_list'] instanceof \think\Paginator): $i = 0; $__LIST__ = $vo['agent_list'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$fo): $mod = ($i % 2 );++$i;?>
                <?php echo htmlentities($fo['agent_name']); ?><br/>
            <?php endforeach; endif; else: echo "" ;endif; endif; if(!(empty($vo['promoter_info']) || (($vo['promoter_info'] instanceof \think\Collection || $vo['promoter_info'] instanceof \think\Paginator ) && $vo['promoter_info']->isEmpty()))): ?>
            <br/>当前<?php echo config('app.agent_setting.promoter_name'); ?>:[<?php echo htmlentities($vo['promoter_info']['user_id']); ?>]<?php echo htmlentities($vo['promoter_info']['nickname']); endif; else: if(!(empty($vo['agent_info']) || (($vo['agent_info'] instanceof \think\Collection || $vo['agent_info'] instanceof \think\Paginator ) && $vo['agent_info']->isEmpty()))): ?>
            [<?php echo htmlentities($vo['agent_info']['id']); ?>]<?php echo htmlentities($vo['agent_info']['name']); else: ?>
            无公会
        <?php endif; endif; ?>
</div>
                            </td>
                            <td>
                                <?php if(check_auth('admin:anchor:live_manage',AUTH_UID)): ?>
                                    <a ajax="post" ajax-confirm="是否确认移除场控？" class="fc_red"
                                    href="<?php echo url('anchor/remove_live_manage',['anchor_uid'=>$user['user_id'],'user_id'=>$vo['user_id']]); ?>">移除场控</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; endif; else: echo "" ;endif; else: ?>
                    <tr>
                        <td>
                            <div class="content_empty">
                                <div class="content_empty_icon"></div>
                                <p class="content_empty_text">暂无守护</p>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="content_title2">
            粉丝贡献榜
            <div class="content_links">
                <?php if(check_auth('admin:anchor:contr',AUTH_UID)): ?>
                    <a href="<?php echo url('rank/contr',['interval'=>'his','user_id'=>$user['user_id']]); ?>"><span style="margin-right: 10px;" class="icon-plus"></span>全部</a>
                <?php endif; ?>
            </div>
        </div>
        <div class="table_slide">
            <table class="content_list mt_10 md_width">
                <thead>
                <tr>
                    <td style="width: 10%;"><input type="checkbox" checkall="list_id" value=""/></td>
                    <td style="width: 10%;">排名</td>
                    <td style="width: 10%;">用户ID</td>
                    <td style="width: 25%;">用户信息</td>
                    <td style="width: 10%;"><?php echo APP_MILLET_NAME; ?></td>
                    <td style="width: 15%;">所属<?php echo config('app.agent_setting.agent_name'); ?></td>
                    <td style="width: 20%;">操作</td>
                </tr>
                </thead>
                <tbody>
                <?php if(!(empty($heroes_rank) || (($heroes_rank instanceof \think\Collection || $heroes_rank instanceof \think\Paginator ) && $heroes_rank->isEmpty()))): if(is_array($heroes_rank) || $heroes_rank instanceof \think\Collection || $heroes_rank instanceof \think\Paginator): $i = 0; $__LIST__ = $heroes_rank;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
                        <tr data-id="<?php echo htmlentities($vo['user_id']); ?>">
                            <td><input class="list_id" type="checkbox" name="ids[]" value="<?php echo htmlentities($vo['user_id']); ?>"/></td>
                            <td><?php echo htmlentities($vo['num']); ?></td>
                            <td><?php echo htmlentities($vo['user_id']); ?></td>
                            <td>
                                <div class="thumb">
    <a href="<?php echo url('user/detail',['user_id'=>$vo['user_id']]); ?>" class="thumb_img thumb_img_avatar">
        <img src="<?php echo img_url($vo['avatar'],'200_200','avatar'); ?>"/>
        <div class="thumb_level_box">
            <img title="<?php echo htmlentities($vo['level_name']); ?>" src="<?php echo htmlentities($vo['level_icon']); ?>"/>
        </div>
    </a>
    <p class="thumb_info">
        <a href="<?php echo url('user/detail',['user_id'=>$vo['user_id']]); ?>">
            <?php echo htmlentities(user_name($vo)); if(check_auth('admin:user:remark',AUTH_UID)): ?>
                &nbsp;<a data-id="user_id:<?php echo htmlentities($vo['user_id']); ?>" poplink="user_remark_box" href="javascript:;"><span class="icon-pencil"></span></a>
            <?php endif; ?>
            <br/>
            <?php echo htmlentities($vo['username']); ?>
            
        </a>
        <?php if($vo['agent_num'] != '0'): ?>
            <br/><?php echo config('app.agent_setting.agent_name'); ?>：<?php echo htmlentities($vo['agent_name']); endif; ?>
    </p>
</div>
                            </td>
                            <td>
                                总：<?php echo htmlentities($vo['millet']); ?><br/>
                                <span class="fc_green">真实：<?php echo htmlentities($vo['real_millet']); ?><br/></span>
                            <!--    <span class="fc_red">虚拟：<?php echo htmlentities($vo['virtual_millet']); ?></span>-->
                            </td>
                            <td>
                                <div class="user_agent_info">
    <?php if($vo['agent_num'] > '0'): if(!(empty($vo['agent_list']) || (($vo['agent_list'] instanceof \think\Collection || $vo['agent_list'] instanceof \think\Paginator ) && $vo['agent_list']->isEmpty()))): if(is_array($vo['agent_list']) || $vo['agent_list'] instanceof \think\Collection || $vo['agent_list'] instanceof \think\Paginator): $i = 0; $__LIST__ = $vo['agent_list'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$fo): $mod = ($i % 2 );++$i;?>
                <?php echo htmlentities($fo['agent_name']); ?><br/>
            <?php endforeach; endif; else: echo "" ;endif; endif; if(!(empty($vo['promoter_info']) || (($vo['promoter_info'] instanceof \think\Collection || $vo['promoter_info'] instanceof \think\Paginator ) && $vo['promoter_info']->isEmpty()))): ?>
            <br/>当前<?php echo config('app.agent_setting.promoter_name'); ?>:[<?php echo htmlentities($vo['promoter_info']['user_id']); ?>]<?php echo htmlentities($vo['promoter_info']['nickname']); endif; else: if(!(empty($vo['agent_info']) || (($vo['agent_info'] instanceof \think\Collection || $vo['agent_info'] instanceof \think\Paginator ) && $vo['agent_info']->isEmpty()))): ?>
            [<?php echo htmlentities($vo['agent_info']['id']); ?>]<?php echo htmlentities($vo['agent_info']['name']); else: ?>
            无公会
        <?php endif; endif; ?>
</div>
                            </td>
                      <!--      <td>
                                <a data-query="user_id=<?php echo htmlentities($vo['user_id']); ?>&interval=his&millet=<?php echo htmlentities($vo['real_millet']); ?>&name=contr:real:<?php echo input('user_id'); ?>" poplink="millet_handler"
                                href="javascript:;">真实<?php echo APP_MILLET_NAME; ?>变更</a><br/>
                                <a data-query="user_id=<?php echo htmlentities($vo['user_id']); ?>&interval=his&millet=<?php echo htmlentities($vo['virtual_millet']); ?>&name=contr:isvirtual:<?php echo input('user_id'); ?>" poplink="millet_handler"
                                href="javascript:;">虚拟<?php echo APP_MILLET_NAME; ?>变更</a>
                            </td>-->
                        </tr>
                    <?php endforeach; endif; else: echo "" ;endif; else: ?>
                    <tr>
                        <td>
                            <div class="content_empty">
                                <div class="content_empty_icon"></div>
                                <p class="content_empty_text">暂无守护</p>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
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

    <div title="<?php echo config('app.product_info.millet_name'); ?>变更" class="layer_box millet_handler pa_10" dom-key="millet_handler" popbox-area="520px,200px" popbox-get-data="<?php echo url('rank/millet_handler'); ?>" popbox-action="<?php echo url('rank/millet_handler'); ?>">
    <table class="content_info2">
        <tr>
            <td class="field_name"><?php echo APP_MILLET_NAME; ?></td>
            <td>
                <input placeholder="" name="millet" class="base_text" value=""/>
            </td>
        </tr>
        <tr>
            <td class="field_name"></td>
            <td>
                <input type="hidden" name="user_id" value="" />
                <input type="hidden" name="interval" value="" />
                <input type="hidden" name="name" value="" />
                <input type="hidden" name="rnum" value="" />
                <div class="base_button sub_btn">提交</div>
            </td>
        </tr>
    </table>
</div>
    <div dom-key="user_remark_box" class="layer_box user_remark_box pa_10" title="用户备注" popbox-action="<?php echo url('user/remark'); ?>"
     popbox-area="520px,280px" popbox-get-data="<?php echo url('user/remark'); ?>">
    <table class="content_info2">
        <tr>
            <td class="field_name">备注名称</td>
            <td>
                <input placeholder="" name="remark_name" class="base_text" value=""/>
            </td>
        </tr>
        <tr>
            <td class="field_name">备注信息</td>
            <td>
                <textarea name="remark" class="base_textarea"></textarea>
            </td>
        </tr>
        <tr>
            <td class="field_name"></td>
            <td>
                <input type="hidden" name="user_id" value=""/>
                <div class="base_button sub_btn">保存</div>
            </td>
        </tr>
    </table>
</div>

    <block name="css">
    <style>
        .rule_item {
            display: inline-block;
            border: solid 1px #DCDCDC;
            line-height: 30px;
            padding: 0px 5px;
            border-radius: 5px;
            margin: 0 3px 3px 0;
            cursor: pointer;
            font-size: 12px;
            width: 140px;
            text-align: left;
        }

        .rule_item .icon-remove {
            margin-left: 5px;
            display: inline-block;
            cursor: pointer;
            float: right;
            margin-right: 3px;
            margin-top: 8px;
        }

        .rule_item:hover {
            color: #e60012;
        }
    </style>
</block>
<div title="添加守护" class="layer_box add_anchor_guard pa_10" dom-key="add_anchor_guard"
     popbox-action="<?php echo url('anchor/add_guard'); ?>" popbox-get-data="<?php echo url('anchor/add_guard'); ?>" popbox-area="480px,320px">
    <table class="content_info2">
        <tr>
            <td class="field_name">守护列表</td>
            <td>
                <div class="box anchor_box">

                </div>
                <div>
                    <span class="icon-plus icon-plus-guard"></span>
                    <input type="hidden" value="" name="anchor_guard_uids"/>
                    <input type="hidden" value="" name="anchor_guard_arr"/>
                    <input type="hidden" value="" name="anchor_uid"/>
                </div>
            </td>
        </tr>
        <tr>
            <td class="field_name"></td>
            <td>
                <div class="base_button sub_btn">设为守护</div>
            </td>
        </tr>
    </table>
</div>
<script>
    var findUidsUrl = "<?php echo url('user/find'); ?>";
    var liData = {};
    var params = {};
    var ids  = [];
    var selectedList = [];
    var anchor_guard_uids = $('[name=anchor_guard_uids]').val();

    if (anchor_guard_uids && anchor_guard_uids != '') {
        params['selected'] = anchor_guard_uids;
    }

    var obj = {
        type: 2,
        scrollbar: false,
        title: '选择用户',
        shadeClose: true,
        shade: 0.75,
        area: ['800px', '600px'],
        content: $s.buildUrl(findUidsUrl, params)
    };

    $('.icon-plus-guard').click(function(){
        var anchor_guard_arr = $("input[name='anchor_guard_arr']").val();

        if(anchor_guard_arr){
            anchor_guard_arr = JSON.parse(anchor_guard_arr);
        }else{
            anchor_guard_arr = [];
        }

        layerIframe.open(obj,function(win){
            win['getFillValue']=function(){
                if(anchor_guard_arr){
                    return selectedList.concat(anchor_guard_arr);
                }else{
                    return selectedList;
                }
            };

            win.WinEve.on('select',function(eve){
                var selectedList = anchor_guard_arr;
                liData = eve.data;
                selectedList.push(liData);
                ids = $("input[name='anchor_guard_uids']").val();
                if(ids){
                    ids = $("input[name='anchor_guard_uids']").val().split(',');
                }else{
                    ids = [];
                }
                ids.push(liData.user_id);
                anchor_guard_uids = ids.join(',');
                $("input[name='anchor_guard_uids']").val(anchor_guard_uids);
                var str = '<div class="rule_item rule_item_'+liData.user_id+'" rule-id="'+liData.user_id+'"><span class="rule_item_name">'+liData.nickname+'</span><span class="icon-remove" onclick="remove(\''+liData.user_id+'\')"></span></div>';
                $('.anchor_box').append(str);
                $("input[name='anchor_guard_arr']").val(JSON.stringify(selectedList));
            });

            win.WinEve.on('remove', function (eve) {
                ids = $("input[name='anchor_guard_uids']").val();
                if(ids){
                    ids = $("input[name='anchor_guard_uids']").val().split(',');
                }else{
                    ids = [];
                }

                var selectedList = anchor_guard_arr;
                for (var i = 0; i < selectedList.length; i++) {
                    if (selectedList[i]['user_id'] == eve.data) {
                        selectedList.splice(i, 1);
                        ids.splice(i, 1);
                        $('.rule_item_'+eve.data).remove();
                        break;
                    }
                }
                anchor_guard_uids = ids.join(',');
                $("input[name='anchor_guard_uids']").val(anchor_guard_uids);
                $("input[name='anchor_guard_arr']").val(JSON.stringify(selectedList));
            });
        })
    })

    function remove(id){
        var anchor_guard_arr = $("input[name='anchor_guard_arr']").val();
        if(anchor_guard_arr){
            anchor_guard_arr = JSON.parse(anchor_guard_arr);
        }else{
            anchor_guard_arr = [];
        }
        ids = $("input[name='anchor_guard_uids']").val();
        if(ids){
            ids = $("input[name='anchor_guard_uids']").val().split(',');
        }else{
            ids = [];
        }
        var selectedList = anchor_guard_arr;
        for (var i = 0; i < selectedList.length; i++) {
            if (selectedList[i]['user_id'] == id) {
                selectedList.splice(i, 1);
                ids.splice(i, 1);
                $('.rule_item_'+id).remove();
                break;
            }
        }
        anchor_guard_uids = ids.join(',');
        $("input[name='anchor_guard_uids']").val(anchor_guard_uids);
        $("input[name='anchor_guard_arr']").val(JSON.stringify(selectedList));
    }

</script>
    <block name="css">
    <style>
        .rule_item {
            display: inline-block;
            border: solid 1px #DCDCDC;
            line-height: 30px;
            padding: 0px 5px;
            border-radius: 5px;
            margin: 0 3px 3px 0;
            cursor: pointer;
            font-size: 12px;
            width: 140px;
            text-align: left;
        }

        .rule_item .icon-remove {
            margin-left: 5px;
            display: inline-block;
            cursor: pointer;
            float: right;
            margin-right: 3px;
            margin-top: 8px;
        }

        .rule_item:hover {
            color: #e60012;
        }
    </style>
</block>
<div title="添加场控" class="layer_box add_live_manage pa_10" dom-key="add_live_manage"
     popbox-action="<?php echo url('anchor/add_live_manage'); ?>" popbox-get-data="<?php echo url('anchor/add_live_manage'); ?>" popbox-area="480px,320px">
    <table class="content_info2">
        <tr>
            <td class="field_name">场控列表</td>
            <td>
                <div class="box manage_box">

                </div>
                <div>
                    <span class="icon-plus icon-plus-manage"></span>
                    <input type="hidden" value="" name="live_manage_uids"/>
                    <input type="hidden" value="" name="live_manage_arr"/>
                    <input type="hidden" value="" name="anchor_uid"/>
                </div>
            </td>
        </tr>
        <tr>
            <td class="field_name"></td>
            <td>
                <div class="base_button sub_btn">设为场控</div>
            </td>
        </tr>
    </table>
</div>
<script>
    var findUidsUrl = "<?php echo url('user/find'); ?>";
    var liData = {};
    var params = {};
    var ids  = [];
    var selectedList = [];
    var live_manage_uids = $('[name=live_manage_uids]').val();

    if (live_manage_uids && live_manage_uids != '') {
        params['selected'] = live_manage_uids;
    }

    var obj = {
        type: 2,
        scrollbar: false,
        title: '选择用户',
        shadeClose: true,
        shade: 0.75,
        area: ['800px', '600px'],
        content: $s.buildUrl(findUidsUrl, params)
    };

    $('.icon-plus-manage').click(function(){
        var live_manage_arr = $("input[name='live_manage_arr']").val();

        if(live_manage_arr){
            live_manage_arr = JSON.parse(live_manage_arr);
        }else{
            live_manage_arr = [];
        }

        layerIframe.open(obj,function(win){
            win['getFillValue']=function(){
                if(live_manage_arr){
                    return selectedList.concat(live_manage_arr);
                }else{
                    return selectedList;
                }
            };

            win.WinEve.on('select',function(eve){
                var selectedList = live_manage_arr;
                liData = eve.data;
                selectedList.push(liData);
                ids = $("input[name='live_manage_uids']").val();
                if(ids){
                    ids = $("input[name='live_manage_uids']").val().split(',');
                }else{
                    ids = [];
                }
                ids.push(liData.user_id);
                live_manage_uids = ids.join(',');
                $("input[name='live_manage_uids']").val(live_manage_uids);
                var str = '<div class="rule_item rule_item_'+liData.user_id+'" rule-id="'+liData.user_id+'"><span class="rule_item_name">'+liData.nickname+'</span><span class="icon-remove" onclick="remove(\''+liData.user_id+'\')"></span></div>';
                $('.manage_box').append(str);
                $("input[name='live_manage_arr']").val(JSON.stringify(selectedList));
            });

            win.WinEve.on('remove', function (eve) {
                ids = $("input[name='live_manage_uids']").val();
                if(ids){
                    ids = $("input[name='live_manage_uids']").val().split(',');
                }else{
                    ids = [];
                }

                var selectedList = live_manage_arr;
                for (var i = 0; i < selectedList.length; i++) {
                    if (selectedList[i]['user_id'] == eve.data) {
                        selectedList.splice(i, 1);
                        ids.splice(i, 1);
                        $('.rule_item_'+eve.data).remove();
                        break;
                    }
                }
                live_manage_uids = ids.join(',');
                $("input[name='live_manage_uids']").val(live_manage_uids);
                $("input[name='live_manage_arr']").val(JSON.stringify(selectedList));
            });
        })
    })

    function remove(id){
        var live_manage_arr = $("input[name='live_manage_arr']").val();
        if(live_manage_arr){
            live_manage_arr = JSON.parse(live_manage_arr);
        }else{
            live_manage_arr = [];
        }
        ids = $("input[name='live_manage_uids']").val();
        if(ids){
            ids = $("input[name='live_manage_uids']").val().split(',');
        }else{
            ids = [];
        }
        var selectedList = live_manage_arr;
        for (var i = 0; i < selectedList.length; i++) {
            if (selectedList[i]['user_id'] == id) {
                selectedList.splice(i, 1);
                ids.splice(i, 1);
                $('.rule_item_'+id).remove();
                break;
            }
        }
        live_manage_uids = ids.join(',');
        $("input[name='live_manage_uids']").val(live_manage_uids);
        $("input[name='live_manage_arr']").val(JSON.stringify(selectedList));
    }

</script>

<script src="/bx_static/toggle.js"></script>
</body>
</html>
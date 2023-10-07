<?php /*a:12:{s:57:"/www/wwwroot/zhibb/application/admin/view/user/detail.tpl";i:1695287837;s:61:"/www/wwwroot/zhibb/application/admin/view/public/base_nav.tpl";i:1693212818;s:61:"/www/wwwroot/zhibb/application/admin/view/public/jsconfig.tpl";i:1602499828;s:61:"/www/wwwroot/zhibb/application/admin/view/public/main_top.tpl";i:1593518960;s:59:"/www/wwwroot/zhibb/application/admin/view/public/toggle.tpl";i:1592625950;s:65:"/www/wwwroot/zhibb/application/admin/view/components/work_pop.tpl";i:1567562304;s:63:"/www/wwwroot/zhibb/application/admin/view/user/recharge_pop.tpl";i:1597646238;s:64:"/www/wwwroot/zhibb/application/admin/view/user/deduction_pop.tpl";i:1597646238;s:61:"/www/wwwroot/zhibb/application/admin/view/user/remark_pop.tpl";i:1592625950;s:66:"/www/wwwroot/zhibb/application/admin/view/user/user_credit_pop.tpl";i:1592645870;s:62:"/www/wwwroot/zhibb/application/admin/view/user/disable_pop.tpl";i:1592625950;s:61:"/www/wwwroot/zhibb/application/admin/view/user/update_pop.tpl";i:1593227892;}*/ ?>
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
    <script src="<?php echo ('/static/'.strtolower(\think\facade\Request::module()).'/js'); ?>/user/update.js?v=<?php echo config('upload.resource_version'); ?>"></script>

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
                    <h1>[<?php echo htmlentities($user['user_id']); ?>]&nbsp;<?php echo htmlentities($user['nickname']); ?>&nbsp;&nbsp;</h1>
                    <?php if($user['verified'] == '1'): ?><span class="fc_green">已认证</span>
                        <?php else: ?>
                        <span class="fc_gray">未认证</span><?php endif; if(check_auth('admin:user:remark',AUTH_UID)): ?>
                        &nbsp;&nbsp;&nbsp;
                        <a data-id="user_id:<?php echo htmlentities($user['user_id']); ?>" poplink="user_remark_box" href="javascript:;">
                            <?php echo htmlentities((isset($user['remark_name']) && ($user['remark_name'] !== '')?$user['remark_name']:'未备注')); ?>
                            <span class="icon-pencil"></span>
                        </a>
                    <?php endif; if(check_auth('admin:user:reset_nickname',AUTH_UID)): ?>
                        &nbsp;&nbsp;&nbsp;
                        <a ajax="post" ajax-confirm="确认重置用户昵称？"
                           href="<?php echo url('user/reset_nickname',['user_id'=>$user['user_id']]); ?>">重置昵称</a>
                    <?php endif; if(check_auth('admin:user:reset_password',AUTH_UID)): ?>
                        &nbsp;&nbsp;&nbsp;
                        <a ajax="post" ajax-confirm="确认重置用户密码？"
                           href="<?php echo url('user/reset_password',['user_id'=>$user['user_id']]); ?>">重置密码</a>
                    <?php endif; if(check_auth('admin:user:reset_nickname',AUTH_UID)): ?>
                        &nbsp;&nbsp;&nbsp;
                        <a ajax="post" ajax-confirm="确认重置用户头像？"
                           href="<?php echo url('user/reset_avatar',['user_id'=>$user['user_id']]); ?>">重置头像</a>
                    <?php endif; if(check_auth('admin:user:reset_nickname',AUTH_UID)): ?>
                        &nbsp;&nbsp;&nbsp;
                        <a ajax="post" ajax-confirm="确认重置用户封面？"
                           href="<?php echo url('user/reset_cover',['user_id'=>$user['user_id']]); ?>">重置封面</a>
                    <?php endif; if(check_auth('admin:user:reset_rename_time',AUTH_UID)): ?>
                        &nbsp;&nbsp;&nbsp;
                        <a ajax="post" ajax-confirm="确认重置限制时间？"
                           href="<?php echo url('user/reset_rename_time',['user_id'=>$user['user_id']]); ?>">重置限制时间</a>
                    <?php endif; ?>
                </div>
                <p>
                    <span class="icon-phone"></span>&nbsp;<?php if($admin['id'] == '1'): ?> <?php echo htmlentities($user['phone']); else: ?><?php echo htmlentities((str_hide($user['phone'],3,4) ?: '未绑定')); endif; ?>&nbsp;
                    <?php switch($user['gender']): case "0": ?>保密<?php break; case "1": ?><span class="fc_blue">男</span><?php break; case "2": ?><span class="fc_magenta">女</span><?php break; endswitch; ?>
                    &nbsp;&nbsp;<?php echo htmlentities((isset($user['province_name']) && ($user['province_name'] !== '')?$user['province_name']:'省份')); ?>-<?php echo htmlentities((isset($user['city_name']) && ($user['city_name'] !== '')?$user['city_name']:'城市')); ?>&nbsp;&nbsp;<?php echo htmlentities((isset($user['birthday']) && ($user['birthday'] !== '')?$user['birthday']:'生日')); ?>
                    &nbsp;&nbsp;
                    <?php switch($user['vip_status']): case "0": ?>
                            <span class="fc_gray">VIP <?php echo htmlentities($user['vip_expire_str']); ?></span>
                        <?php break; case "1": ?>
                            <span class="fc_green">VIP <?php echo htmlentities($user['vip_expire_str']); ?></span>
                        <?php break; case "2": ?>
                            <span class="fc_red">VIP <?php echo htmlentities($user['vip_expire_str']); ?></span>
                        <?php break; endswitch; if($user['isvirtual'] == '1'): ?>
                        &nbsp;&nbsp;<a class="fc_red" href="javascript:;">虚拟用户</a>
                    <?php endif; if($user['is_promoter'] == '1'): ?>
                        &nbsp;&nbsp;<a class="fc_orange" href="<?php echo url('promoter/detail',['user_id'=>$user['user_id']]); ?>"><?php echo config('app.agent_setting.promoter_name'); ?></a>
                    <?php endif; if($user['is_anchor'] == '1'): ?>
                        &nbsp;&nbsp;<a class="fc_orange"
                                       href="<?php echo url('anchor/detail',['user_id'=>$user['user_id']]); ?>">主播</a>
                    <?php endif; if($user['is_creation'] == '1'): ?>
                        &nbsp;&nbsp;<a class="fc_orange" href="">创作号</a>
                    <?php endif; if(check_auth('admin:user:update',AUTH_UID)): ?>
                        &nbsp;&nbsp;&nbsp;
                        <a data-id="user_id:<?php echo htmlentities($user['user_id']); ?>" poplink="user_update_box" href="javascript:;">
                            <?php echo htmlentities((isset($user['remark_name']) && ($user['remark_name'] !== '')?$user['remark_name']:'编辑')); ?>
                            <span class="icon-pencil"></span>
                        </a>
                    <?php endif; ?>
                </p>
                <p>
                    <?php echo htmlentities((isset($user['sign']) && ($user['sign'] !== '')?$user['sign']:'这个家伙太懒了，什么也没留下。')); if(check_auth('admin:user:clear_sign',AUTH_UID)): if(!(empty($user['sign']) || (($user['sign'] instanceof \think\Collection || $user['sign'] instanceof \think\Paginator ) && $user['sign']->isEmpty()))): ?>
                            &nbsp;&nbsp;<a ajax="get" ajax-confirm
                                           href="<?php echo url('user/clear_sign',['user_id'=>$user['user_id']]); ?>">清除签名</a>
                        <?php endif; endif; ?>
                </p>
                <p>
                    <?php if(empty($user['agent_info'])): ?>
                        直属用户
                        <?php else: if(!(empty($user['agent_info']) || (($user['agent_info'] instanceof \think\Collection || $user['agent_info'] instanceof \think\Paginator ) && $user['agent_info']->isEmpty()))): ?>
                            <?php echo config('app.agent_setting.agent_name'); ?>：<?php echo htmlentities($user['agent_info']['name']); endif; if(!(empty($user['promoter_info']) || (($user['promoter_info'] instanceof \think\Collection || $user['promoter_info'] instanceof \think\Paginator ) && $user['promoter_info']->isEmpty()))): ?>
                            &nbsp;&nbsp; <?php echo config('app.agent_setting.promoter_name'); ?>：<a href="<?php echo url('promoter/detail',['user_id'=>$user['promoter_uid']]); ?>"><?php echo htmlentities(user_name($user['promoter_info'])); ?></a>
                        <?php endif; endif; ?>
                    &nbsp;&nbsp;注册时间：<?php echo htmlentities($user['create_time']); ?>
                </p>
            </div>


            <ul class="user_base_btns">
                <?php if(check_auth('admin:user:change_status',AUTH_UID)): ?>
                    <li>
                        <div tgradio-before="tgradioStatusBefore" tgradio-not="0" tgradio-on="1" tgradio-off="0"
                             tgradio-value="<?php echo htmlentities($user['status']); ?>"
                             tgradio-name="status"
                             tgradio="<?php echo url('user/change_status',['id'=>$user['user_id']]); ?>"></div>
                    </li>
                <?php endif; ?>
                <li>
                    <a ajax="get" ajax-reload="false" href="<?php echo url('user/refresh_redis',['user_id'=>$user['user_id']]); ?>">
                        <span class="icon-reload"></span>&nbsp;刷新数据
                    </a>
                </li>
                <?php if(check_auth('admin:credit_log:add',AUTH_UID)): ?>
                    <li>
                        <a poplink="user_credit_box" data-id="user_id:<?php echo htmlentities($user['user_id']); ?>" href="javascript:;">
                            <span class="icon-ribbon"></span>&nbsp;添加信用记录
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
            <div class="clear"></div>
        </div>
        <div class="table_slide">
            <table class="content_list mt_10 show_num sm_width">
                <tr>
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
                        用户积分
                        <br/>
                        <a class="show_num_span" href="<?php echo url('user_point/index',['user_id'=>$user['user_id']]); ?>"><?php echo htmlentities($user['points']); ?></a>
                    </td>
                    <td>
                        信用评分
                        <br/>
                        <a class="show_num_span" href="<?php echo url('credit_log/_list',['user_id'=>$user['user_id']]); ?>"><?php echo htmlentities($user['credit_score']); ?></a>
                    </td>
                </tr>
            </table>
        </div>
        <div class="content_title2">
            <?php echo APP_BEAN_NAME; ?>
            <div class="content_links">
                <a href="<?php echo url('bean_log/index',['user_id'=>$user['user_id']]); ?>">变更记录</a>
                <a target="_blank" href="<?php echo url('recharge_order/index',['user_id'=>$user['user_id']]); ?>">充值记录</a>
            </div>
        </div>
        <div class="table_slide">
            <table class="content_list mt_10 bean_tab md_width">
                <tr>
                    <td class="field_name font_nowrap">账户余额</td>
                    <td class="field_value">
                        <div style="display:flex">
                            <?php echo htmlentities($user['bean']); if(check_auth('admin:recharge_order:add,admin:recharge_order:add_isvirtual',AUTH_UID)): ?>
                                &nbsp;&nbsp;
                                <a poplink="user_recharge_box" data-id="user_id:<?php echo htmlentities($user['user_id']); ?>" class="recharge_btn"
                                   href="javascript:;">充值</a>
                                <a style="margin-left: 5px;" poplink="user_deduction_box" data-id="user_id:<?php echo htmlentities($user['user_id']); ?>"
                                   class="deduction_btn"
                                   href="javascript:;">扣除</a>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td class="field_name font_nowrap">冻结余额</td>
                    <td class="field_value"><?php echo htmlentities($user['fre_bean']); ?></td>
                    <td class="field_name font_nowrap">累计充值</td>
                    <td class="field_value"><?php echo htmlentities($user['recharge_total']); ?></td>
                    <td class="field_name font_nowrap">支付功能</td>
                    <td class="field_value">
                        <div tgradio-not="<?php echo check_auth('admin:user:change_pay_status')?'0':'1'; ?>" tgradio-on="1"
                             tgradio-off="0" tgradio-value="<?php echo htmlentities($user['pay_status']); ?>"
                             tgradio-name="pay_status"
                             tgradio="<?php echo url('user/change_pay_status',['id'=>$user['user_id']]); ?>"></div>
                    </td>
                    <td class="field_name font_nowrap">最后支付</td>
                    <td class="field_value"><?php echo htmlentities(time_format($user['last_pay_time'],'从未支付','datetime')); ?></td>
                    <td class="field_name font_nowrap">不计入额度</td>
                    <td class="field_value"><?php echo htmlentities($user['loss_bean']); ?></td>
                </tr>
            </table>
        </div>

        <div class="content_title2">
            <?php echo APP_MILLET_NAME; ?>
            <div class="content_links">
                <a href="<?php echo url('millet_log/index',['user_id'=>$user['user_id']]); ?>">变更记录</a>
                <a href="<?php echo url('millet_cash/index',['user_id'=>$user['user_id']]); ?>">提现记录</a>
            </div>
        </div>
        <div class="table_slide">
            <table class="content_list mt_10 millet_tab md_width">
                <tr>
                    <td class="field_name font_nowrap">剩余<?php echo APP_MILLET_NAME; ?></td>
                    <td class="field_value"><?php echo htmlentities($user['millet']); ?></td>
                    <td class="field_name font_nowrap">冻结<?php echo APP_MILLET_NAME; ?></td>
                    <td class="field_value"><?php echo htmlentities($user['fre_millet']); ?></td>
                    <td class="field_name font_nowrap">累计<?php echo APP_MILLET_NAME; ?></td>
                    <td class="field_value"><?php echo htmlentities($user['his_millet']); ?></td>
                    <td class="field_name font_nowrap">提现功能</td>
                    <td class="field_value">
                        <div tgradio-not="<?php echo check_auth('admin:user:change_millet_status')?'0':'1'; ?>" tgradio-on="1"
                             tgradio-off="0" tgradio-value="<?php echo htmlentities($user['millet_status']); ?>"
                             tgradio-name="millet_status"
                             tgradio="<?php echo url('user/change_millet_status',['id'=>$user['user_id']]); ?>"></div>
                    </td>
                    <td class="field_name font_nowrap">最近提现</td>
                    <td class="field_value"><?php echo htmlentities(time_format($user['millet_change_time'],'未变动','datetime')); ?></td>
                </tr>
            </table>
        </div>

        <?php if($distribute_status == '1'): ?>
            <div class="content_title2">
                <?php echo htmlentities($distribute_name); ?>
                <div class="content_links">
                    <a href="<?php echo url('/giftdistribute/gift_commission_log/index',['user_id'=>$user['user_id']]); ?>">变更记录</a>
                    <a href="">提现记录</a>
                </div>
            </div>
            <div class="table_slide">
                <table class="content_list mt_10 millet_tab md_width">
                    <tr>
                        <td class="field_name font_nowrap">剩余<?php echo htmlentities($distribute_name); ?></td>
                        <td class="field_value"><?php echo htmlentities($user['commission_price']); ?></td>
                        <td class="field_name font_nowrap">冻结<?php echo htmlentities($distribute_name); ?></td>
                        <td class="field_value"><?php echo htmlentities($user['commission_pre_price']); ?></td>
                        <td class="field_name font_nowrap">累计<?php echo htmlentities($distribute_name); ?></td>
                        <td class="field_value"><?php echo htmlentities($user['commission_total_price']); ?></td>
                    </tr>
                </table>
            </div>
        <?php endif; ?>

        <div class="content_title2">
            背包
            <div class="content_links">
                <a href="<?php echo url('user_package/index',['user_id'=>$user['user_id']]); ?>">更多</a>
            </div>
        </div>
        <div class="table_slide">
            <table class="content_list mt_10 md_width">
                <thead>
                <tr>
                    <td style="width: 5%;"><input type="checkbox" checkall="list_id" value=""/></td>
                    <td style="width: 5%;">ID</td>
                    <td style="width: 10%;">礼物信息</td>
                    <td style="width: 10%;">类型</td>
                    <td style="width: 10%;">数量</td>
                    <td style="width: 10%;">花费</td>
                    <td style="width: 10%;">获取方式</td>
                    <td style="width: 10%;">状态</td>
                    <td style="width: 10%;">可使用时间</td>
                    <td style="width: 10%;">过期时间</td>
                    <td style="width: 10%;">获取时间</td>
                </tr>
                </thead>
                <tbody>
                <?php if(!(empty($user_packages) || (($user_packages instanceof \think\Collection || $user_packages instanceof \think\Paginator ) && $user_packages->isEmpty()))): if(is_array($user_packages) || $user_packages instanceof \think\Collection || $user_packages instanceof \think\Paginator): $i = 0; $__LIST__ = $user_packages;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
                        <tr>
                            <td><input class="list_id" type="checkbox" name="ids[]" value="<?php echo htmlentities($vo['id']); ?>"/></td>
                            <td><?php echo htmlentities($vo['id']); ?></td>
                            <td>
                                <div class="thumb">
                                    <a href="javascript:;" class="thumb_img thumb_img_avatar">
                                        <img src="<?php echo img_url($vo['icon'],'200_200','icon'); ?>"/>
                                    </a>
                                    <p class="thumb_info">
                                        <a href="javascript:;">
                                            <?php echo htmlentities($vo['name']); ?>
                                        </a>
                                    </p>
                                </div>
                            </td>
                            <td><?php echo !empty($vo['type']) ? '大礼物'  :  '小礼物'; ?></td>
                            <td><?php echo htmlentities($vo['num']); ?></td>
                            <td><?php echo htmlentities($vo['user_cost']); ?></td>
                            <td>
                                <?php switch($vo['access_method']): case "liudanji": ?>扭蛋机<?php break; case "lottery": ?>大转盘<?php break; endswitch; ?>
                            </td>
                            <td>
                                <?php switch($vo['status']): case "0": ?>失效<?php break; case "1": ?>有效<?php break; case "2": ?>已使用<?php break; endswitch; ?>
                            <td>
                                <?php if(!(empty($vo['use_time']) || (($vo['use_time'] instanceof \think\Collection || $vo['use_time'] instanceof \think\Paginator ) && $vo['use_time']->isEmpty()))): ?>
                                    <?php echo htmlentities(time_format($vo['use_time'],'','datetime')); endif; ?>
                            </td>
                            <td>
                                <?php if(!(empty($vo['expire_time']) || (($vo['expire_time'] instanceof \think\Collection || $vo['expire_time'] instanceof \think\Paginator ) && $vo['expire_time']->isEmpty()))): ?>
                                    <?php echo htmlentities(time_format($vo['expire_time'],'','datetime')); endif; ?>
                            </td>
                            <td>
                                <?php echo htmlentities(time_format($vo['create_time'],'','datetime')); ?>
                            </td>
                        </tr>
                    <?php endforeach; endif; else: echo "" ;endif; else: ?>
                    <tr>
                        <td>
                            <div class="content_empty">
                                <div class="content_empty_icon"></div>
                                <p class="content_empty_text">暂未查询到相关数据</p>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="content_title2">启动日志</div>
        <div class="table_slide">
            <table class="content_list mt_10 md_width">
                <thead>
                <tr>
                    <td style="width: 5%;"><input type="checkbox" checkall="list_id" value=""/></td>
                    <td style="width: 5%;">ID</td>
                    <td style="width: 10%;">会话标识</td>
                    <td style="width: 10%;">APP版本</td>
                    <td style="width: 10%;">系统版本</td>
                    <td style="width: 10%;">设备型号</td>
                    <td style="width: 10%;">网络状态</td>
                    <td style="width: 10%;">MEID</td>
                    <td style="width: 10%;">IP</td>
                    <td style="width: 10%;">启动位置</td>
                    <td style="width: 10%;">启动时间</td>
                </tr>
                </thead>
                <tbody>
                <?php if(!(empty($start_logs) || (($start_logs instanceof \think\Collection || $start_logs instanceof \think\Paginator ) && $start_logs->isEmpty()))): if(is_array($start_logs) || $start_logs instanceof \think\Collection || $start_logs instanceof \think\Paginator): $i = 0; $__LIST__ = $start_logs;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
                        <tr>
                            <td><input class="list_id" type="checkbox" name="ids[]" value="<?php echo htmlentities($vo['id']); ?>"/></td>
                            <td><?php echo htmlentities($vo['id']); ?></td>
                            <td><?php echo str_hide($vo['access_token'],3,4,'*',true); ?></td>
                            <td><?php echo htmlentities($vo['v_code']); ?></td>
                            <td><?php echo htmlentities($vo['os_name']); ?> <?php echo htmlentities($vo['os_version']); ?></td>
                            <td><?php echo htmlentities($vo['brand_name']); ?> <?php echo htmlentities($vo['device_model']); ?></td>
                            <td><?php echo htmlentities($vo['network_status']); ?></td>
                            <td><?php echo str_hide($vo['meid'],3,4,'*',true); ?></td>
                            <td><?php echo htmlentities($vo['client_ip']); ?></td>
                            <td></td>
                            <td><?php echo htmlentities(time_format($vo['start_time'],'','datetime')); ?></td>
                        </tr>
                    <?php endforeach; endif; else: echo "" ;endif; else: ?>
                    <tr>
                        <td>
                            <div class="content_empty">
                                <div class="content_empty_icon"></div>
                                <p class="content_empty_text">暂未查询到相关数据</p>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="content_title2 mt_10">登录日志</div>
        <div class="table_slide">
            <table class="content_list mt_10 md_width">
                <thead>
                <tr>
                    <td style="width: 5%;"><input type="checkbox" checkall="list_id" value=""/></td>
                    <td style="width: 5%;">ID</td>
                    <td style="width: 10%;">登录方式</td>
                    <td style="width: 10%;">APP版本</td>
                    <td style="width: 10%;">系统版本</td>
                    <td style="width: 10%;">设备型号</td>
                    <td style="width: 10%;">网络状态</td>
                    <td style="width: 10%;">MEID</td>
                    <td style="width: 10%;">IP</td>
                    <td style="width: 10%;">登录时间</td>
                    <td style="width: 10%;">操作</td>
                </tr>
                </thead>
                <tbody>
                <?php if(!(empty($login_logs) || (($login_logs instanceof \think\Collection || $login_logs instanceof \think\Paginator ) && $login_logs->isEmpty()))): if(is_array($login_logs) || $login_logs instanceof \think\Collection || $login_logs instanceof \think\Paginator): $i = 0; $__LIST__ = $login_logs;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
                        <tr>
                            <td><input class="list_id" type="checkbox" name="ids[]" value="<?php echo htmlentities($vo['id']); ?>"/></td>
                            <td><?php echo htmlentities($vo['id']); ?></td>
                            <td>
                                <?php switch($vo['login_way']): case "quick": ?>快捷登录<?php break; case "login": ?>用户名登录<?php break; case "device": ?>设备码登录<?php break; case "third": ?>三方登录<?php break; case "after": ?>注册后自动登录<?php break; endswitch; ?>
                            </td>
                            <td><?php echo htmlentities($vo['v_code']); ?></td>
                            <td><?php echo htmlentities($vo['os_name']); ?> <?php echo htmlentities($vo['os_version']); ?></td>
                            <td><?php echo htmlentities($vo['brand_name']); ?> <?php echo htmlentities($vo['device_model']); ?></td>
                            <td><?php echo htmlentities($vo['network_status']); ?></td>
                            <td><?php echo str_hide($vo['meid'],3,4,'*',true); ?></td>
                            <td><?php echo htmlentities($vo['login_ip']); ?></td>
                            <td><?php echo htmlentities(time_format($vo['login_time'],'','datetime')); ?></td>
                            <td>
                                <?php if(check_auth('admin:ad_space:delete',AUTH_UID)): ?>
                                <a class="fc_red" ajax-confirm ajax="get" href="<?php echo url('sbjin',array('sb'=>$vo['meid'])); ?>?<?php echo ('redirect='.urlencode(\think\facade\Request::url())); ?>">禁用设备号</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; endif; else: echo "" ;endif; else: ?>
                    <tr>
                        <td>
                            <div class="content_empty">
                                <div class="content_empty_icon"></div>
                                <p class="content_empty_text">暂未查询到相关数据</p>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="content_title2">网速监测</div>
        <div class="table_slide">
            <table class="content_list mt_10" style="min-width:1220px;">
                <thead>
                <tr>
                    <td style="width: 5%;"><input type="checkbox" checkall="list_id" value=""/></td>
                    <td style="width: 5%;">ID</td>
                    <td style="width: 10%;">APP版本</td>
                    <td style="width: 10%;">系统版本</td>
                    <td style="width: 10%;">设备型号</td>
                    <td style="width: 10%;">[次数]场景/网络状态</td>
                    <td style="width: 10%;">上行速率(kbps)</td>
                    <td style="width: 10%;">下行速率(kbps)</td>
                    <td style="width: 10%;">MEID</td>
                    <td style="width: 10%;">IP</td>
                    <td style="width: 10%;">上报时间</td>
                </tr>
                </thead>
                <tbody>
                <?php if(!(empty($network_logs) || (($network_logs instanceof \think\Collection || $network_logs instanceof \think\Paginator ) && $network_logs->isEmpty()))): if(is_array($network_logs) || $network_logs instanceof \think\Collection || $network_logs instanceof \think\Paginator): $i = 0; $__LIST__ = $network_logs;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
                        <tr>
                            <td><input class="list_id" type="checkbox" name="ids[]" value="<?php echo htmlentities($vo['id']); ?>"/></td>
                            <td><?php echo htmlentities($vo['id']); ?></td>
                            <td><?php echo htmlentities($vo['v_code']); ?></td>
                            <td><?php echo htmlentities($vo['os_name']); ?> <?php echo htmlentities($vo['os_version']); ?></td>
                            <td><?php echo htmlentities($vo['brand_name']); ?> <?php echo htmlentities($vo['device_model']); ?></td>
                            <td>
                                [<?php echo htmlentities($vo['num']); ?>]
                                <?php switch($vo['scene']): case "live": ?>直播重连<?php break; endswitch; ?>
                                <br/>
                                <?php echo htmlentities($vo['network_status']); ?>
                            </td>
                            <td><?php echo htmlentities($vo['upload_rate']); ?></td>
                            <td><?php echo htmlentities($vo['download_rate']); ?></td>
                            <td><?php echo str_hide($vo['meid'],3,4,'*',true); ?></td>
                            <td><?php echo htmlentities($vo['client_ip']); ?></td>
                            <td><?php echo htmlentities(time_format($vo['create_time'],'','datetime')); ?></td>
                        </tr>
                    <?php endforeach; endif; else: echo "" ;endif; else: ?>
                    <tr>
                        <td>
                            <div class="content_empty">
                                <div class="content_empty_icon"></div>
                                <p class="content_empty_text">暂未查询到相关数据</p>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!--  <div class="content_title2">VIP订单</div>
          <div class="content_title2">收到礼物</div>
          <div class="content_title2">送出礼物</div>
          <div class="content_title2">兴趣标签</div>
          <div class="content_title2">实名认证</div>-->
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

    <div dom-key="user_recharge_box" class="layer_box  user_recharge_box" title="填写充值申请单" popbox-action="<?php echo url('user/recharge'); ?>"
     popbox-area="520px,520px">
    <div class="pa_10">
        <table class="content_info2">
            <tr>
                <td class="field_name">付款方式</td>
                <td>
                    <select name="pay_method" class="base_select">
                        <option value="">请选择</option>
                        <?php if(is_array($recharge_pay_methods) || $recharge_pay_methods instanceof \think\Collection || $recharge_pay_methods instanceof \think\Paginator): $i = 0; $__LIST__ = $recharge_pay_methods;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$pay_method): $mod = ($i % 2 );++$i;?>
                            <option value="<?php echo htmlentities($pay_method['value']); ?>"><?php echo htmlentities($pay_method['name']); ?></option>
                        <?php endforeach; endif; else: echo "" ;endif; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td class="field_name">充值金额</td>
                <td><input placeholder="单位：元" name="total_fee" class="base_text" value=""/></td>
            </tr>
            <tr class="capital_tr">
                <td class="field_name">大写金额</td>
                <td>
                    <input placeholder="请输入充值金额的大写读数" name="capital_fee" class="base_text" value=""/>
                    <p class="field_tip">示例：12050 壹万贰仟零伍拾元整，5000.5 伍仟元伍角整</p>
                </td>
            </tr>
            <tr>
                <td class="field_name"><?php echo APP_BEAN_NAME; ?></td>
                <td>
                    <input readonly class="base_text bean_num" value="0"/>
                    <p class="field_tip bean_tip">请输入充值金额</p>
                </td>
            </tr>
            <tr class="pay_tr">
                <td class="field_name">付款人</td>
                <td><input name="pay_name" placeholder="如：马云" class="base_text" value=""/></td>
            </tr>
            <tr class="pay_tr">
                <td class="field_name">付款账号</td>
                <td><input name="pay_account" class="base_text" value=""/></td>
            </tr>
            <tr>
                <td class="field_name">备注</td>
                <td>
                    <textarea name="remark" class="base_textarea"></textarea>
                </td>
            </tr>
            <tr>
                <td class="field_name"></td>
                <td>
                    <input type="hidden" name="user_id" value=""/>
                    <div class="base_button sub_btn">提交</div>
                </td>
            </tr>
        </table>
    </div>
</div>

    <div dom-key="user_deduction_box" class="layer_box  user_deduction_box " title="填写扣款申请单"
     popbox-action="<?php echo url('user/deduction_recharge'); ?>"
     popbox-area="540px,450px">
    <div class="pa_10">
        <table class="content_info2">
            <tr>
                <td class="field_name">付款方式</td>
                <td>
                    <select name="deduction_method" class="base_select">
                        <option value="">请选择</option>
                        <?php if(is_array($deduction_methods) || $deduction_methods instanceof \think\Collection || $deduction_methods instanceof \think\Paginator): $i = 0; $__LIST__ = $deduction_methods;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$deduction_method): $mod = ($i % 2 );++$i;?>
                            <option value="<?php echo htmlentities($deduction_method['value']); ?>"><?php echo htmlentities($deduction_method['name']); ?></option>
                        <?php endforeach; endif; else: echo "" ;endif; ?>
                    </select>
                </td>
            </tr>
            <tr class="bean_tr">
                <td class="field_name">扣除金额</td>
                <td>
                    <input placeholder="单位：<?php echo APP_BEAN_NAME; ?>" name="bean_num" class="base_text" value=""/>
                    <p class="field_tip bean_tip">
                        <a href="">当前全部余额</a>&nbsp;&nbsp;单位：<?php echo APP_BEAN_NAME; ?>
                    </p>
                </td>
            </tr>
            <tr class="refund_tr">
                <td class="field_name">充值单号</td>
                <td>
                    <input placeholder="18位单号" name="order_no" class="base_text" value=""/>
                    <p class="field_tip bean_tip">请在后台查找&nbsp;&nbsp;<a class="order_no_btn" href="javascript:;">如何查看充值单号？</a></p>
                </td>
            </tr>
            <tr class="refund_tr">
                <td class="field_name">支付单号</td>
                <td>
                    <input placeholder="18位单号" name="third_trade_no" class="base_text" value=""/>
                    <p class="field_tip bean_tip">需要客户提供&nbsp;&nbsp;<a class="third_trade_no_btn" href="javascript:;">如何查看支付单号？</a></p>
                </td>
            </tr>
            <tr class="refund_tr">
                <td class="field_name">支付时间</td>
                <td>
                    <input placeholder="请选择时间" name="pay_time" class="base_text" value=""/>
                    <p class="field_tip bean_tip">客户可以通过查看通知短信、支付凭据获取，时间误差不能超出十分钟</p>
                </td>
            </tr>
            <tr class="refund_tr">
                <td class="field_name">操作提示</td>
                <td>
                    1、为了验证真实性，充值单号、支付单号和支付时间必须一致。<br/>
                    2、假设A错充到B账号100个<?php echo APP_BEAN_NAME; ?>，但是在后台退款处理期间，B已经消费了50个<?php echo APP_BEAN_NAME; ?>，那么最多给A退款50个<?php echo APP_BEAN_NAME; ?>的等值人民币。<br/>
                    3、先扣除<?php echo APP_BEAN_NAME; ?>，扣除成功后再按照建议退款金额退款给用户。
                </td>
            </tr>
            <tr class="remark_tr">
                <td class="field_name">备注</td>
                <td><textarea name="remark" class="base_textarea"></textarea></td>
            </tr>
            <tr>
                <td class="field_name"></td>
                <td>
                    <input type="hidden" name="user_id" value=""/>
                    <div class="base_button sub_btn">提交</div>
                </td>
            </tr>
        </table>
    </div>
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

    <div title="添加信用记录" popbox="user_credit_box" dom-key="user_credit_box" class="layer_box user_credit_box pa_10" popbox-action="<?php echo url('credit_log/add'); ?>">
    <table class="content_info2">
        <tr>
            <td class="field_name">类型</td>
            <td>
                <select name="change_type" class="base_select">
                    <option value="">请选择</option>
                    <option value="exp">负面记录</option>
                    <option value="inc">积极记录</option>
                </select>
            </td>
        </tr>
        <tr>
            <td class="field_name">信用分值</td>
            <td>
                <input name="score" class="base_text" value="1"/>
                <p class="field_tip">正整数</p>
            </td>
        </tr>
        <tr>
            <td class="field_name">具体事项</td>
            <td>
                <textarea style="display: none" name="" class="base_textarea remark_textarea"></textarea>
                <select style="display: none" class="base_select remark_select" name="">
                    <option value="">请选择</option>
                    <option value="广告欺诈">广告欺诈</option>
                    <option value="淫秽色情">淫秽色情</option>
                    <option value="骚扰谩骂">骚扰谩骂</option>
                    <option value="反动政治">反动政治</option>
                    <option value="侵权（冒充他人、侵犯名誉等）">侵权（冒充他人、侵犯名誉等）</option>
                    <option value="发布不实信息">发布不实信息</option>
                    <option value="违法犯罪">违法犯罪</option>
                    <option value="账号可能被盗用">账号可能被盗用</option>
                    <option value="其它内容">其它内容</option>
                </select>

            </td>
        </tr>
        <tr>
            <td class="field_name"></td>
            <td>
                <input type="hidden" name="user_id" value=""/>
                <div class="base_button_div max_w_412">
                    <div class="base_button sub_btn">提交</div>
                </div>
            </td>
        </tr>
    </table>
</div>
    <div class="disable_user_box pa_10 layer_box">
    <table class="content_info2">
        <tr>
            <td class="field_name">封禁时间</td>
            <td>
                <select name="disable_length" class="base_select">
                    <option value="">永久</option>
                    <option value="15 minutes">15分钟</option>
                    <option value="1 hours">1小时</option>
                    <option value="6 hours">6小时</option>
                    <option value="1 days">1天</option>
                    <option value="3 days">3天</option>
                    <option value="7 days">一周</option>
                    <option value="1 months">1个月</option>
                    <option value="3 months">3个月</option>
                    <option value="1 years">1年</option>
                </select>
            </td>
        </tr>
        <tr>
            <td class="field_name">封禁原因</td>
            <td>
                <textarea placeholder="可选" name="disable_desc" class="base_textarea"></textarea>
            </td>
        </tr>
        <tr>
            <td class="field_name"></td>
            <td>
                <input type="hidden" name="user_id" value=""/>
                <div class="base_button_div max_w_412">
                    <div class="base_button sub_disable_btn">封禁</div>
                </div>
            </td>
        </tr>
    </table>
</div>

    <block name="js">
    <script src="/static/vendor/smart/smart_region/region.js?v=<?php echo config('upload.resource_version'); ?>"></script>
    <script src="/static/vendor/cropper/cropper.min.js?v=<?php echo config('upload.resource_version'); ?>"></script>
</block>

<block name="css">
    <link rel="stylesheet" type="text/css" href="/static/vendor/smart/smart_region/region.css?v=<?php echo config('upload.resource_version'); ?>"/>
    <link rel="stylesheet" type="text/css" href="/static/vendor/cropper/cropper.min.css?v=<?php echo config('upload.resource_version'); ?>"/>
    <style>
        .field_name {
            width: 100px;
        }
    </style>
</block>
<div dom-key="user_update_box" class="layer_box user_update_box pa_10" title="用户信息" popbox-action="<?php echo url('user/update'); ?>"
     popbox-area="520px,480px" popbox-get-data="<?php echo url('user/update'); ?>">
    <table class="content_info2">
        <tr>
            <td class="field_name">头像</td>
            <td>
                <div class="base_group">
                    <input style="width: 308px;" name="avatar" value="" type="text" class="base_text border_left_radius"/>
                    <a uploader-crop="1" uploader-size="2147483648" uploader-type="image" href="javascript:;"
                       class="base_button border_right_radius"
                       uploader="avatar"
                       uploader-before="imageUploadBefore"
                       uploader-field="avatar">上传</a>
                </div>
                <div imgview="[name=avatar]" style="width: 120px;margin-top: 10px;"><img src=""/>
                </div>
            </td>
        </tr>
        <tr>
            <td class="field_name">昵称</td>
            <td>
                <input placeholder="" name="nickname" class="base_text" value=""/>
            </td>
        </tr>
        <tr>
            <td class="field_name">性别</td>
            <td>
                <label class="base_label2"><input value="1" type="radio" name="gender" />男</label>
                <label class="base_label2"><input value="2" type="radio" name="gender" />女</label>
            </td>
        </tr>
        <tr>
            <td class="field_name">生日</td>
            <td>
                <input readonly placeholder="默认为当前时间" class="base_text" name="birthday"
                       value=""/>
            </td>
        </tr>
        <tr>
            <td class="field_name">所在地</td>
            <td>
                <input placeholder="请选择地区" data-fill-path="1" data-min-level="3" data-max-num="1"
                       url="<?php echo url('common/get_region'); ?>" region="[name=area_id]" type="text" readonly
                       class="base_text area_name" value="">
                <input type="hidden" name="area_id" value=""/>
            </td>
        </tr>
        <tr>
            <td class="field_name">签名</td>
            <td>
                <textarea name="sign" class="base_textarea"></textarea>
            </td>
        </tr>
        <tr>
            <td class="field_name"></td>
            <td>
                <input type="hidden" name="user_id" value="" class="user_id"/>
                <div class="base_button sub_btn">保存</div>
            </td>
        </tr>
    </table>
</div>

    <script>
        $("[name=birthday]").flatpickr({
            dateFormat: 'Y-m-d',
            enableTime: false,
        });

        function imageUploadBefore(file) {
            var user_id = $('.user_id').val();

            $('[uploader-field=avatar]').attr('uploader-query', 'user_id=' + user_id);
            return true;
        }
    </script>

<script src="/bx_static/toggle.js"></script>
</body>
</html>
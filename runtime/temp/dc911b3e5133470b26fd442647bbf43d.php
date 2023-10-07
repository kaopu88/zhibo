<?php /*a:17:{s:56:"/www/wwwroot/zhibb/application/admin/view/video/user.tpl";i:1592625950;s:61:"/www/wwwroot/zhibb/application/admin/view/public/base_nav.tpl";i:1693212818;s:61:"/www/wwwroot/zhibb/application/admin/view/public/jsconfig.tpl";i:1602499828;s:61:"/www/wwwroot/zhibb/application/admin/view/public/main_top.tpl";i:1593518960;s:59:"/www/wwwroot/zhibb/application/admin/view/public/toggle.tpl";i:1592625950;s:60:"/www/wwwroot/zhibb/application/admin/view/user/user_info.tpl";i:1694070544;s:60:"/www/wwwroot/zhibb/application/admin/view/user/user_type.tpl";i:1592625950;s:66:"/www/wwwroot/zhibb/application/admin/view/user/user_vip_status.tpl";i:1592625950;s:61:"/www/wwwroot/zhibb/application/admin/view/user/user_agent.tpl";i:1592625950;s:59:"/www/wwwroot/zhibb/application/admin/view/user/user_fun.tpl";i:1592625950;s:65:"/www/wwwroot/zhibb/application/admin/view/components/work_pop.tpl";i:1567562304;s:58:"/www/wwwroot/zhibb/application/admin/view/user/reg_pop.tpl";i:1592625950;s:61:"/www/wwwroot/zhibb/application/admin/view/user/remark_pop.tpl";i:1592625950;s:70:"/www/wwwroot/zhibb/application/admin/view/components/recommend_pop.tpl";i:1567562304;s:59:"/www/wwwroot/zhibb/application/admin/view/user/role_pop.tpl";i:1592625950;s:62:"/www/wwwroot/zhibb/application/admin/view/user/disable_pop.tpl";i:1592625950;s:66:"/www/wwwroot/zhibb/application/admin/view/video/add_video_user.tpl";i:1592625950;}*/ ?>
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
    
    <script>
        var countdown = parseInt('<?php echo htmlentities($countdown); ?>');
        var regUrl = '<?php echo url("user/reg"); ?>';
        var myConfig = {
            list: [
                {
                    name: 'status',
                    title: '启用状态',
                    opts: [
                        {name: '禁用', value: '0'},
                        {name: '启用', value: '1'}
                    ]
                },
                {
                    name: 'user_type',
                    title: '用户身份',
                    opts: [
                        {name: '普通用户', value: 'user'},
                        {name: '主播', value: 'anchor'},
                        {name: '<?php echo config('app.agent_setting.promoter_name'); ?>', value: 'promoter'},
                        {name: '非<?php echo config('app.agent_setting.promoter_name'); ?>', value: 'not_promoter'},
                        {name: '非主播', value: 'not_anchor'},
                        {name: '虚拟用户', value: 'isvirtual'}
                    ]
                }
            ]
        };
    </script>
    <script src="<?php echo ('/static/'.strtolower(\think\facade\Request::module()).'/js'); ?>/user/index.js?v=<?php echo config('upload.resource_version'); ?>"></script>

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
        <div class="filter_box mt_10">
            <div class="filter_nav">
                已选择&nbsp;>&nbsp;
                <p class="filter_selected"></p>
            </div>
            <div class="filter_options">
                <ul class="filter_list"></ul>
                <div class="filter_order">
                    <?php if(check_auth('admin:film_user:add',AUTH_UID)): ?>
                    <a class="base_button base_button_s" href="javascript:;" poplink="add_video_user">新增用户</a>
                    <?php endif; ?>
                    <div class="filter_search">
                        <input placeholder="用户ID、手机号、昵称" type="text" name="keyword" value="<?php echo input('keyword'); ?>"/>
                        <button class="filter_search_submit">搜索</button>
                    </div>
                    <div class="clear"></div>
                </div>
            </div>
            <input type="hidden" name="status" value="<?php echo input('status'); ?>"/>
            <input type="hidden" name="live_status" value="<?php echo input('live_status'); ?>"/>
            <input type="hidden" name="vip_status" value="<?php echo input('vip_status'); ?>"/>
            <input type="hidden" name="user_type" value="<?php echo input('user_type'); ?>"/>
            <input type="hidden" name="level" value="<?php echo input('level'); ?>"/>
            <input type="hidden" name="province" value="<?php echo input('province'); ?>"/>
            <input type="hidden" name="city" value="<?php echo input('city'); ?>"/>
            <input type="hidden" name="district" value="<?php echo input('district'); ?>"/>
        </div>

        <div class="table_slide">
            <table class="content_list mt_10" style="min-width: 840px;">
            <thead>
            <tr>
                <td style="width: 5%;"><input type="checkbox" checkall="list_id" value=""/></td>
                <td style="width: 7%;">ID</td>
                <td style="width: 15%;">用户信息</td>
                <td style="width: 9%;">用户类型</td>
                <td style="width: 10%;">用户属性</td>
                <td style="width: 13%;"><?php echo config('app.agent_setting.agent_name'); ?>信息</td>
                <td style="width: 8%;"><?php echo APP_BEAN_NAME; ?></td>
                <td style="width: 8%;">功能</td>
                <td style="width: 9%;">状态</td>
                <td style="width: 7%;">最近登录</td>
                <td style="width: 9%;">操作</td>
            </tr>
            </thead>
            <tbody>
            <?php if(!(empty($_list) || (($_list instanceof \think\Collection || $_list instanceof \think\Paginator ) && $_list->isEmpty()))): if(is_array($_list) || $_list instanceof \think\Collection || $_list instanceof \think\Paginator): $i = 0; $__LIST__ = $_list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
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
                        <td>
                            <?php if($vo['is_promoter']!='1' && $vo['is_anchor']!='1'): ?>
    <span class="fc_black">普通用户</span>
    <?php else: if($vo['is_promoter'] == '1'): ?>
        <span class="fc_orange">[<?php echo htmlentities($vo['promoter_info']['agent_name']); ?>] <?php echo config('app.agent_setting.promoter_name'); ?></span>
    <?php endif; if($vo['is_promoter'] == 1 and $vo['is_anchor'] == 1): ?>
        <br/>
    <?php endif; if($vo['is_anchor'] == '1'): ?>
        <span class="fc_orange">[ <?php if($vo['anchor_info']['agent_name']): ?>  <?php echo htmlentities($vo['anchor_info']['agent_name']); else: ?>平台 <?php endif; ?>]主播</span>
    <?php endif; endif; if($vo['isvirtual'] == '1'): ?>
    <br/><span class="fc_red">虚拟用户</span>
<?php endif; ?>
                        </td>
                        <td>
                            <b><?php echo htmlentities((isset($vo['city_info']['name']) && ($vo['city_info']['name'] !== '')?$vo['city_info']['name']:'未知')); ?></b><br/>
                            <?php switch($vo['vip_status']): case "0": ?>
        <span class="fc_gray"><?php echo htmlentities($vo['vip_expire_str']); ?></span>
    <?php break; case "1": ?>
        <span class="fc_green"><?php echo htmlentities($vo['vip_expire_str']); ?></span>
    <?php break; case "2": ?>
        <span class="fc_red"><?php echo htmlentities($vo['vip_expire_str']); ?></span>
    <?php break; endswitch; ?>

                            <br/>
                            <?php if($vo['verified'] == '1'): ?>
                                <span class="fc_green">已认证</span>
                                <?php else: ?>
                                <span class="fc_gray">未认证</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a target="_blank" href="<?php echo url('user/agent_list',['user_id'=>$vo['user_id']]); ?>">
    <?php echo htmlentities($vo['agent_num']); ?>>
</a>
                        </td>
                        <td>
                            <?php if($vo['pay_status'] == '1'): ?>
                                <span class="icon-credit"></span>&nbsp;<span><?php echo htmlentities($vo['bean']); ?></span><br/>
                                <?php else: ?>
                                <span class="icon-credit"></span>&nbsp;<span title="支付功能已禁用"
                                                                             class="fc_red"><?php echo htmlentities($vo['bean']); ?></span><br/>
                            <?php endif; ?>
                            <span class="fc_gray">  <span class="icon-lock"></span>&nbsp;<?php echo htmlentities($vo['fre_bean']); ?></span>
                        </td>
                        <td>
                            直播：<?php switch($vo['live_status']): case "0": ?><span class="fc_red">关闭</span><?php break; case "1": ?><span class="fc_green">开启</span><?php break; endswitch; ?>
&nbsp;
<?php if(check_auth('admin:user:change_upload_status',AUTH_UID)): ?>
    视频：<a fun-name="film_status" fun-value="<?php echo htmlentities($vo['film_status']); ?>" href="<?php echo url('user/change_film_status',['id'=>$vo['user_id']]); ?>"></a>
    <?php else: ?>
    视频：<?php switch($vo['film_status']): case "0": ?><span class="fc_red">关闭</span><?php break; case "1": ?><span class="fc_green">开启</span><?php break; endswitch; endif; ?>
<br/>
<?php if(check_auth('admin:user:change_comment_status',AUTH_UID)): ?>
    评论：<a fun-name="comment_status" fun-value="<?php echo htmlentities($vo['comment_status']); ?>" href="<?php echo url('user/change_comment_status',['id'=>$vo['user_id']]); ?>"></a>&nbsp;
    <?php else: ?>
    评论：<?php switch($vo['comment_status']): case "0": ?><span class="fc_red">关闭</span><?php break; case "1": ?><span class="fc_green">开启</span><?php break; endswitch; ?>&nbsp;
<?php endif; if(check_auth('admin:user:change_contact_status',AUTH_UID)): ?>
    私信：<a fun-name="contact_status" fun-value="<?php echo htmlentities($vo['contact_status']); ?>" href="<?php echo url('user/change_contact_status',['id'=>$vo['user_id']]); ?>"></a>
    <?php else: ?>
    私信：<?php switch($vo['contact_status']): case "0": ?><span class="fc_red">关闭</span><?php break; case "1": ?><span class="fc_green">开启</span><?php break; endswitch; endif; ?>
                        </td>
                        <td>
                            <div tgradio-before="tgradioStatusBefore"
                                 tgradio-not="<?php echo check_auth('admin:user:change_status')?'0':'1'; ?>" tgradio-on="1"
                                 tgradio-off="0" tgradio-value="<?php echo htmlentities($vo['status']); ?>"
                                 tgradio-name="status"
                                 tgradio="<?php echo url('user/change_status',['id'=>$vo['user_id']]); ?>"></div>
                        </td>
                        <td><?php echo htmlentities(time_before($vo['login_time'],'前')); ?></td>
                        <td>
                            <?php if(check_auth('admin:film_user:cancel',AUTH_UID)): ?>
                            <a ajax="get" ajax-confirm="是否确认取消视频用户？" class="fc_red"
                               href="<?php echo url('cancel',['user_id'=>$vo['user_id']]); ?>">取消视频用户</a>
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
        <div class="pageshow mt_10"><?php echo htmlspecialchars_decode($_page);; ?></div>
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

    <div class="layer_box reg_box">
    <div class="pa_10">
        <ul class="reg_list">
            <li class="reg_li">
                <label>手机号：</label><input type="text" name="phone" class="base_text"/>
            </li>
            <li style="position: relative;" class="reg_li">
                <label>验证码：</label>
                <div class="base_group">
                    <input style="width: 209px;" name="code" value="" type="text" class="base_text"/>
                    <a href="javascript:;" class="base_button base_button_gray send_btn">发送验证码</a>
                </div>
            </li>
            <li class="reg_li">
                <label><?php echo config('app.agent_setting.promoter_name'); ?>：</label>
                <div class="base_group">
                    <input placeholder="可选项" suggester-value="[name=promoter_uid]"
                           suggester="<?php echo url('promoter/get_suggests'); ?>" style="width: 209px;" value="" type="text"
                           class="base_text"/>
                    <input type="hidden" name="promoter_uid" value=""/>
                    <a href="javascript:;" class="base_button base_button_gray">选择</a>
                </div>
                <div class="clear"></div>
            </li>
            <li class="reg_li">
                <label>创建密码：</label><input name="password" type="password" class="base_text" value=""/>
            </li>
            <li class="reg_li">
                <label>确认密码：</label><input name="confirm_password" type="password" class="base_text" value=""/>
            </li>
        </ul>
        <div>
            <div class="base_button reg_sub_btn">注册</div>
        </div>
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

    <div dom-key="recommend_box" popbox="recommend_box"  class="recommend_box layer_box pa_10" title="推荐" popbox-action="<?php echo url('recommend_content/save'); ?>" popbox-get-data="<?php echo url('recommend_content/save'); ?>" popbox-area="560px,450px">
    <ul class="recommend_list"></ul>
    <div style="text-align: center;">
        <input name="id" type="hidden" value=""/>
        <input name="type" type="hidden" value=""/>
        <div class="base_button sub_btn">保存</div>
    </div>
</div>
    <div class="layer_box user_role_box" dom-key="user_role_box" title="设置用户角色" popbox-action="<?php echo url('user_roler/setting'); ?>" popbox-get-data="<?php echo url('user_roler/get_role_list'); ?>" >
    <div class="pa_10">
        <p style="padding: 10px;">用户：【<span class="role_user_id"></span>】<span class="role_nickname"></span>，请选择下列角色</p>
        <ul class="user_role_list"></ul>
        <input type="hidden" name="user_id" />
    </div>
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
<div title="添加视频用户" class="layer_box add_video_user pa_10" dom-key="add_video_user"
     popbox-action="<?php echo url('video/add_user'); ?>" popbox-get-data="<?php echo url('video/add_user'); ?>" popbox-area="700px,550px">
    <table class="content_info2">
        <tr>
            <td class="field_name">用户列表</td>
            <td>
                <div class="box">
                    
                </div>
                <div>
                    <span class="icon-plus"></span>
                    <input type="hidden" value="" name="uids"/>
                    <input type="hidden" value="" name="arr"/>
                </div>
            </td>
        </tr>
        <tr>
            <td class="field_name"></td>
            <td>
                <div class="base_button sub_btn">设为视频用户</div>
            </td>
        </tr>
    </table>
</div>
<script>
    var findUidsUrl = "<?php echo url('user/find'); ?>";
    var params = {source:'video_user'};
    var liData = {};
    var ids  = [];
    var selectedList = [];
    var uids = $('[name=uids]').val();
    
    if (uids && uids != '') {
        params['selected'] = uids;
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

    $('.icon-plus').click(function(){
        var arr = $("input[name='arr']").val();
        
        if(arr){
            arr = JSON.parse(arr);
        }else{
            arr = [];
        }

        layerIframe.open(obj,function(win){
            win['getFillValue']=function(){
                if(arr){
                    return selectedList.concat(arr);
                }else{
                    return selectedList;
                }
            };

            win.WinEve.on('select',function(eve){
                var selectedList = arr;
                liData = eve.data;
                selectedList.push(liData);
                ids = $("input[name='uids']").val();
                if(ids){
                    ids = $("input[name='uids']").val().split(',');
                }else{
                    ids = [];
                }
                ids.push(liData.user_id);
                uids = ids.join(',');
                $("input[name='uids']").val(uids);
                var str = '<div class="rule_item rule_item_'+liData.user_id+'" rule-id="'+liData.user_id+'"><span class="rule_item_name">'+liData.nickname+'</span><span class="icon-remove" onclick="remove(\''+liData.user_id+'\')"></span></div>';
                $('.box').append(str);
                $("input[name='arr']").val(JSON.stringify(selectedList));
            });

            win.WinEve.on('remove', function (eve) {
                ids = $("input[name='uids']").val();
                if(ids){
                    ids = $("input[name='uids']").val().split(',');
                }else{
                    ids = [];
                }
                
                var selectedList = arr;
                for (var i = 0; i < selectedList.length; i++) {
                    if (selectedList[i]['user_id'] == eve.data) {
                        selectedList.splice(i, 1);
                        ids.splice(i, 1);
                        $('.rule_item_'+eve.data).remove();
                        break;
                    }
                }
                uids = ids.join(',');
                $("input[name='uids']").val(uids);
                $("input[name='arr']").val(JSON.stringify(selectedList));
            });
        })
    })

    function remove(id){
        var arr = $("input[name='arr']").val();
        if(arr){
            arr = JSON.parse(arr);
        }else{
            arr = [];
        }
        ids = $("input[name='uids']").val();
        if(ids){
            ids = $("input[name='uids']").val().split(',');
        }else{
            ids = [];
        }
        var selectedList = arr;
        for (var i = 0; i < selectedList.length; i++) {
            if (selectedList[i]['user_id'] == id) {
                selectedList.splice(i, 1);
                ids.splice(i, 1);
                $('.rule_item_'+id).remove();
                break;
            }
        }
        uids = ids.join(',');
        $("input[name='uids']").val(uids);
        $("input[name='arr']").val(JSON.stringify(selectedList));
    }
    
</script>

<script src="/bx_static/toggle.js"></script>
</body>
</html>
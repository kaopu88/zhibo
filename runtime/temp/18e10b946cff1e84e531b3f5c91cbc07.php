<?php /*a:6:{s:57:"/www/wwwroot/zhibb/application/admin/view/index/index.tpl";i:1596186878;s:62:"/www/wwwroot/zhibb/application/admin/view/public/base_nav2.tpl";i:1592625950;s:61:"/www/wwwroot/zhibb/application/admin/view/public/jsconfig.tpl";i:1602499828;s:61:"/www/wwwroot/zhibb/application/admin/view/public/main_top.tpl";i:1593518960;s:59:"/www/wwwroot/zhibb/application/admin/view/public/toggle.tpl";i:1592625950;s:65:"/www/wwwroot/zhibb/application/admin/view/components/work_pop.tpl";i:1567562304;}*/ ?>
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

    <style>
        .main_top_logo a{
            text-align: center;
            line-height: 61px;
            font-size: 22px;
        }
        .main_top_logo span{
            color: #555;
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
    
    <link rel="stylesheet" type="text/css" href="<?php echo ('/static/'.strtolower(\think\facade\Request::module()).'/css'); ?>/index/index.css?v=<?php echo config('upload.resource_version'); ?>"/>
    <style>
        :root{
            font-size:16px !important;
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
    <script type="text/javascript" src="/static/vendor/webuploader/webuploader.js?v=<?php echo config('upload.resource_version'); ?>"></script>
    <script src="/static/vendor/qiniu.min.js?v=<?php echo config('upload.resource_version'); ?>"></script>
    <script src="/static/vendor/fancybox/jquery.fancybox.pack.js?v=<?php echo config('upload.resource_version'); ?>"></script>
    <script src="/static/vendor/smart/smart.bundle.js?v=<?php echo config('upload.resource_version'); ?>"></script>
    <script src="/static/smart_admin/js/smart_admin.bundle.js?v=<?php echo config('upload.resource_version'); ?>"></script>
    <script src="/static/common/js/public.js?v=<?php echo config('upload.resource_version'); ?>"></script>
    <script src="<?php echo ('/static/'.strtolower(\think\facade\Request::module()).'/js'); ?>/public.js?v=<?php echo config('upload.resource_version'); ?>"></script>
    <link rel="stylesheet" type="text/css" href="/bx_static/custom.css"/>
    
    <script src="/static/vendor/echarts/echarts.min.js?v=<?php echo config('upload.resource_version'); ?>"></script>
    <script src="/static/vendor/echarts/shine.js?v=<?php echo config('upload.resource_version'); ?>"></script>
    <script src="/static/vendor/echarts/dataTool.js?v=<?php echo config('upload.resource_version'); ?>"></script>
    <script src="<?php echo ('/static/'.strtolower(\think\facade\Request::module()).'/js'); ?>/index/index.js?v=<?php echo config('upload.resource_version'); ?>"></script>

</head>
<body>
<div class="main_container">
    
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
</div>

    <div class="toggle_container">
        <div class="catalog">
  <div class="top_catalog"></div>
  <div class="sub_catalog"></div>
</div>


    </div>
    <div class="index_main">
        <div class="welcome_message">
            欢迎访问<?php echo APP_PREFIX_NAME; ?>ERP管理系统！
			<!--<a style="float: right;" href="">进入个人中心</a>-->        </div>
        <div class="panel-body" style="overflow: hidden;overflow-x: auto;">
            <div class="functions">
                <div class="">
                    <a href="<?php echo url('user/index'); ?>">
                        <div class="fun_icon"><span class="icon-users"></span></div>
                    </a>
                    <div class="base_info">
                        <div class="stat_num"><?php echo htmlentities($userNum); ?></div>
                        <div class="stat_name">用户</div>
                    </div>
                </div>
                <div class="">
                    <a href="<?php echo url('agent/index'); ?>">
                        <div class="fun_icon"><span class="icon-home"></span></div>
                    </a>
                    <div class="base_info">
                        <div class="stat_num"><?php echo htmlentities($agentNum); ?></div>
                        <div class="stat_name"><?php echo config('app.agent_setting.agent_name'); ?></div>
                    </div>
                </div>
                <div class="">
                    <a href="<?php echo url('anchor/index'); ?>">
                        <div class="fun_icon"><span class="icon-user"></span></div>
                    </a>
                    <div class="base_info">
                        <div class="stat_num"><?php echo htmlentities($anchorNum); ?></div>
                        <div class="stat_name">主播</div>
                    </div>
                </div>
                <div class="">
                    <a href="<?php echo url('promoter/index'); ?>">
                        <div class="fun_icon"><span class="icon-user"></span></div>
                    </a>
                    <div class="base_info">
                        <div class="stat_num"><?php echo htmlentities($promoterNum); ?></div>
                        <div class="stat_name"><?php echo config('app.agent_setting.promoter_name'); ?></div>
                    </div>
                </div>
                <div class="">
                    <a href="<?php echo url('live/index'); ?>">
                        <div class="fun_icon"><span class="icon-film"></span></div>
                    </a>
                    <div class="base_info">
                        <div class="stat_num"><?php echo htmlentities($liveNum); ?></div>
                        <div class="stat_name">直播</div>
                    </div>
                </div>
                <div class="">
                    <a href="<?php echo url('article/index'); ?>">
                        <div class="fun_icon"><span class="icon-pencil"></span></div>
                    </a>
                    <div class="base_info">
                        <div class="stat_num"><?php echo htmlentities($articleNum); ?></div>
                        <div class="stat_name">文章</div>
                    </div>
                </div>
                <div class="">
                    <a href="<?php echo url('help/index'); ?>">
                        <div class="fun_icon"><span class="icon-help"></span></div>
                    </a>
                    <div class="base_info">
                        <div class="stat_num"><?php echo htmlentities($helpNum); ?></div>
                        <div class="stat_name">帮助中心</div>
                    </div>
                </div>
                <div class="">
                    <a href="<?php echo url('packages/index'); ?>">
                        <div class="fun_icon"><span class="icon-settings"></span></div>
                    </a>
                    <div class="base_info">
                        <div class="stat_num"><?php echo htmlentities($settingNum); ?></div>
                        <div class="stat_name">总安装量</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="data_block_container">
            <div class="data_block mt_10 recharge_trend">
                <div class="data_title">充值趋势图 单位(元)</div>
                <div class="data_toolbar">
                    <div class="data_date">
                        <div class="data_date_line">
                            <a href="javascript:;" class="date_range" range-unit="w" range-num="0">本周</a>
                            <a href="javascript:;" class="date_range" range-unit="w" range-num="-1">上周</a>
                            <a href="javascript:;" class="date_range" range-unit="m" range-num="0" range-default>本月</a>
                            <a href="javascript:;" class="date_range" range-unit="m" range-num="-1">上月</a>
                        </div>
                        <input class="data_date_input" readonly/>
                        <input type="hidden" class="data_date_unit"/>
                        <input type="hidden" class="data_date_start"/>
                        <input type="hidden" class="data_date_end"/>
                    </div>
                </div>
                <div style="width: 100%;height:350px;" class="mt_10 my_container">
                </div>
            </div>

            <div class="data_block mt_10 consume_trend">
                <div class="data_title">消费趋势图 单位(<?php echo config('app.product_info.bean_name'); ?>)</div>
                <div class="data_toolbar">
                    <div class="data_date">
                        <div class="data_date_line">
                            <a href="javascript:;" class="date_range" range-unit="w" range-num="0">本周</a>
                            <a href="javascript:;" class="date_range" range-unit="w" range-num="-1">上周</a>
                            <a href="javascript:;" class="date_range" range-unit="m" range-num="0" range-default>本月</a>
                            <a href="javascript:;" class="date_range" range-unit="m" range-num="-1">上月</a>
                        </div>
                        <input class="data_date_input" readonly/>
                        <input type="hidden" class="data_date_unit"/>
                        <input type="hidden" class="data_date_start"/>
                        <input type="hidden" class="data_date_end"/>
                    </div>
                </div>
                <div style="width: 100%;height:350px;" class="mt_10 my_container">
                </div>
            </div>
        </div>
        <div class="panel-body">
            <div class= "brand-card-container">
                <div class="brand-card-items">
                    <div>
                        <div class="brand-card-item">
                            <div class="brand-card">
                                <div class="brand-card-header bg-facebook">
                                    <a href="<?php echo url('video/index'); ?>">短视频统计</a>
                                </div>
                                <div class="brand-card-body">
                                    <div>
                                        <div class="text-value"><?php echo htmlentities($filmNum); ?></div>
                                        <div class="text-uppercase text-muted small">全部</div>
                                    </div>
                                    <div>
                                        <div class="text-value"><?php echo htmlentities($filmCheckNum); ?></div>
                                        <div class="text-uppercase text-muted small">未审核</div>
                                    </div>
                                    <div>
                                        <div class="text-value text-value-red"><a href="<?php echo url('video/audit_list', ['audit_status' => 1]); ?>"><?php echo htmlentities($filmSelfCheckNum); ?></a></div>
                                        <div class="text-uppercase text-muted small">待审核</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="brand-card-item">
                            <div class="brand-card">
                                <div class="brand-card-header bg-facebook">
                                    <a href="<?php echo url('user_verified/index'); ?>">评论统计</a>
                                </div>
                                <div class="brand-card-body">
                                    <div>
                                        <div class="text-value"><?php echo htmlentities($commentNum); ?></div>
                                        <div class="text-uppercase text-muted small">全部</div>
                                    </div>
                                    <div>
                                        <div class="text-value">0</div>
                                        <div class="text-uppercase text-muted small">未审核</div>
                                    </div>
                                    <div>
                                        <div class="text-value text-value-red">0</div>
                                        <div class="text-uppercase text-muted small">待审核</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div>
                        <div class="brand-card-item">
                            <div class="brand-card">
                                <div class="brand-card-header bg-facebook">
                                    <a href="<?php echo url('user_data_deal/index'); ?>">用户资料统计</a>
                                </div>
                                <div class="brand-card-body">
                                    <div>
                                        <div class="text-value"><?php echo htmlentities($userDataNum); ?></div>
                                        <div class="text-uppercase text-muted small">全部</div>
                                    </div>
                                    <div>
                                        <div class="text-value"><?php echo htmlentities($userDataCheckNum); ?></div>
                                        <div class="text-uppercase text-muted small">未审核</div>
                                    </div>
                                    <div>
                                        <div class="text-value text-value-red"><a href="<?php echo url('user_data_deal/check', ['audit_status' => 0]); ?>"><?php echo htmlentities($userDataSelfCheckNum); ?></a></div>
                                        <div class="text-uppercase text-muted small">待审核</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="brand-card-item">
                            <div class="brand-card">
                                <div class="brand-card-header bg-facebook">
                                    <a href="<?php echo url('complaint/index'); ?>">举报统计</a>
                                </div>
                                <div class="brand-card-body">
                                    <div>
                                        <div class="text-value"><?php echo htmlentities($complaintNum); ?></div>
                                        <div class="text-uppercase text-muted small">全部</div>
                                    </div>
                                    <div>
                                        <div class="text-value"><?php echo htmlentities($complaintCheckNum); ?></div>
                                        <div class="text-uppercase text-muted small">未审核</div>
                                    </div>
                                    <div>
                                        <div class="text-value text-value-red"><a href="<?php echo url('complaint/check', ['audit_status' => 0]); ?>"><?php echo htmlentities($complaintSelfCheckNum); ?></a></div>
                                        <div class="text-uppercase text-muted small">待审核</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="brand-card-items">
                    <div>
                        <div class="brand-card-item">
                            <div class="brand-card">
                                <div class="brand-card-header bg-facebook">
                                    <a href="<?php echo url('viewback/index'); ?>">反馈统计</a>
                                </div>
                                <div class="brand-card-body">
                                    <div>
                                        <div class="text-value"><?php echo htmlentities($viewbackNum); ?></div>
                                        <div class="text-uppercase text-muted small">全部</div>
                                    </div>
                                    <div>
                                        <div class="text-value"><?php echo htmlentities($viewbackCheckNum); ?></div>
                                        <div class="text-uppercase text-muted small">未审核</div>
                                    </div>
                                    <div>
                                        <div class="text-value text-value-red"><a href="<?php echo url('viewback/check', ['audit_status' => 0]); ?>"><?php echo htmlentities($viewbackSelfCheckNum); ?></a></div>
                                        <div class="text-uppercase text-muted small">待审核</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="brand-card-item">
                            <div class="brand-card">
                                <div class="brand-card-header bg-facebook">
                                    <a href="<?php echo url('creation/index'); ?>">创作号统计</a>
                                </div>
                                <div class="brand-card-body">
                                    <div>
                                        <div class="text-value"><?php echo htmlentities($creationNum); ?></div>
                                        <div class="text-uppercase text-muted small">全部</div>
                                    </div>
                                    <div>
                                        <div class="text-value"><?php echo htmlentities($creationCheckNum); ?></div>
                                        <div class="text-uppercase text-muted small">未审核</div>
                                    </div>
                                    <div>
                                        <div class="text-value text-value-red"><a href="<?php echo url('creation/check', ['audit_status' => 0]); ?>"><?php echo htmlentities($creationSelfCheckNum); ?></a></div>
                                        <div class="text-uppercase text-muted small">待审核</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div>
                        <div class="brand-card-item">
                            <div class="brand-card">
                                <div class="brand-card-header bg-facebook">
                                    <a href="<?php echo url('recharge_app/all_list'); ?>">充值统计</a>
                                </div>
                                <div class="brand-card-body">
                                    <div>
                                        <div class="text-value"><?php echo htmlentities($rechargeAppNum); ?></div>
                                        <div class="text-uppercase text-muted small">全部</div>
                                    </div>
                                    <div>
                                        <div class="text-value"><?php echo htmlentities($rechargeAppCheckNum); ?></div>
                                        <div class="text-uppercase text-muted small">未审核</div>
                                    </div>
                                    <div>
                                        <div class="text-value text-value-red"><a href="<?php echo url('recharge_app/index', ['audit_status' => 0]); ?>"><?php echo htmlentities($rechargeAppSelfCheckNum); ?></a></div>
                                        <div class="text-uppercase text-muted small">待审核</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="brand-card-item">
                            <div class="brand-card">
                                <div class="brand-card-header bg-facebook">
                                    <a href="<?php echo url('user_verified/index'); ?>">实名认证统计</a>
                                </div>
                                <div class="brand-card-body">
                                    <div>
                                        <div class="text-value"><?php echo htmlentities($userVerifiedNum); ?></div>
                                        <div class="text-uppercase text-muted small">全部</div>
                                    </div>
                                    <div>
                                        <div class="text-value"><?php echo htmlentities($userVerifiedCheckNum); ?></div>
                                        <div class="text-uppercase text-muted small">未审核</div>
                                    </div>
                                    <div>
                                        <div class="text-value text-value-red"><a href="<?php echo url('user_verified/audit', ['status' => 0]); ?>"><?php echo htmlentities($userVerifiedSelfCheckNum); ?></a></div>
                                        <div class="text-uppercase text-muted small">待审核</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php if(!(empty($admin_notice) || (($admin_notice instanceof \think\Collection || $admin_notice instanceof \think\Paginator ) && $admin_notice->isEmpty()))): ?>
            <table class="content_list mt_10">
                <thead>
                <tr>
                    <td>最新公告</td>
                    <td>发布人</td>
                    <td>时间</td>
                </tr>
                </thead>
               <tbody>
                <?php if(is_array($admin_notice) || $admin_notice instanceof \think\Collection || $admin_notice instanceof \think\Paginator): $i = 0; $__LIST__ = $admin_notice;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$an): $mod = ($i % 2 );++$i;?>
               <tr>
                   <td>
                       <a href="<?php echo url('notice/detail',['id'=>$an['id']]); ?>" target="_blank"><?php echo htmlentities($an['title']); ?></a>
                   </td>
                   <td><?php echo htmlentities($an['username']); ?></td>
                   <td><?php echo htmlentities(time_format($an['create_time'])); ?></td>
               </tr>
               <?php endforeach; endif; else: echo "" ;endif; ?>
               </tbody>
            </table>
            <?php endif; ?>
            <div class="panel mt_10">
                <div class="panel-heading">服务器状态</div>
                <div class="panel-body">
                    <table class="content_info2">
                        <tr>
                            <td>运行状态</td>
                            <td>正常</td>
                        </tr>
                        <tr>
                            <td>直播在线人数</td>
                            <td>***</td>
                        </tr>
                        <tr>
                            <td>环境检测</td>
                            <td>
                                production：production
                            </td>
                        </tr>
                        <tr>
                            <td>node服务</td>
                            <td>正常</td>
                        </tr>
                        <tr>
                            <td>数据库</td>
                            <td>正常</td>
                        </tr>
                        <tr>
                            <td>系统版本</td>
                            <td>3.0.0</td>
                        </tr>
                    </table>
                </div>
            </div>
            <div style="height: 30px"></div>
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
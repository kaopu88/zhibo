<?php /*a:8:{s:66:"/www/wwwroot/zhibb/application/admin/view/recharge_order/index.tpl";i:1575954996;s:61:"/www/wwwroot/zhibb/application/admin/view/public/base_nav.tpl";i:1693212818;s:61:"/www/wwwroot/zhibb/application/admin/view/public/jsconfig.tpl";i:1602499828;s:61:"/www/wwwroot/zhibb/application/admin/view/public/main_top.tpl";i:1593518960;s:59:"/www/wwwroot/zhibb/application/admin/view/public/toggle.tpl";i:1592625950;s:68:"/www/wwwroot/zhibb/application/admin/view/recharge_order/summary.tpl";i:1604901710;s:74:"/www/wwwroot/zhibb/application/admin/view/recharge_order/recharge_list.tpl";i:1592645870;s:65:"/www/wwwroot/zhibb/application/admin/view/components/work_pop.tpl";i:1567562304;}*/ ?>
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
        var myConfig = {
            list: [
                {
                    name: 'pay_method',
                    title: '支付方式',
                    opts: JSON.parse('<?php echo json_encode(enum_array("pay_methods")); ?>')
                },
                {
                    name: 'pay_status',
                    title: '支付状态',
                    opts: [
                        {name: '已支付', value: '1'},
                        {name: '未支付', value: '0'}
                    ]
                },
                {
                    name: 'isvirtual',
                    title: '虚拟充值',
                    opts: [
                        {name: '虚拟', value: '1'},
                        {name: '正常', value: '0'}
                    ]
                },
            ]
        };
    </script>

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
                    <div class="filter_search">
                    <input readonly="readonly" placeholder="开始时间" name="start_time" value="<?php echo input('start_time'); ?>" type="text" class="base_text flatpickr-input">
                        <input readonly="readonly" placeholder="结束时间" name="end_time" value="<?php echo input('end_time'); ?>" type="text" class="base_text flatpickr-input">
                        <input placeholder="支付号" type="text" name="third_trade_no" value="<?php echo input('third_trade_no'); ?>"/>
                        <input placeholder="订单号" type="text" name="order_no" value="<?php echo input('order_no'); ?>"/>
                        <input placeholder="用户ID、用户呢称、手机号" type="text" name="keyword" value="<?php echo input('keyword'); ?>"/>
                        <button class="filter_search_submit">搜索</button>
                    </div>
                    <div class="clear"></div>
                </div>
            </div>
            <input type="hidden" name="isvirtual" value="<?php echo input('isvirtual'); ?>"/>
            <input type="hidden" name="pay_method" value="<?php echo input('pay_method'); ?>"/>
            <input type="hidden" name="pay_status" value="<?php echo input('pay_status'); ?>"/>
        </div>
        
<div class="data_block mt_10">
    <div class="data_title">汇总信息</div>
    <div class="table_slide">
        <table class="content_list sm_width">
        <thead>
        <tr>
            <td style="width: 10.28%;">支付宝</td>
            <td style="width: 10.28%;">支付宝手机页面</td>
            <td style="width: 10.28%;">微信支付</td>
            <td style="width: 10.28%;">微信公众号</td>
            <td style="width: 10.28%;">微信网页支付</td>
            <td style="width: 10.28%;">系统赠送</td>
            <td style="width: 10.28%;">Apple支付</td>
            <td style="width: 10.28%;">系统结算</td>
            <td style="width: 12.28%;">合计(单位：元)</td>
        </tr>
        </thead>
        <tbody>
        <?php if(!(empty($recharge_list) || (($recharge_list instanceof \think\Collection || $recharge_list instanceof \think\Paginator ) && $recharge_list->isEmpty()))): ?>
            <tr class="today_data_tr">
                <td><?php echo htmlentities((isset($summary['alipay_app']) && ($summary['alipay_app'] !== '')?$summary['alipay_app']:0)); ?></td>
                <td><?php echo htmlentities((isset($summary['alipay_wap']) && ($summary['alipay_wap'] !== '')?$summary['alipay_wap']:0)); ?></td>
                <td><?php echo htmlentities((isset($summary['wxpay_app']) && ($summary['wxpay_app'] !== '')?$summary['wxpay_app']:0)); ?></td>
                <td><?php echo htmlentities((isset($summary['wxpay_h5']) && ($summary['wxpay_h5'] !== '')?$summary['wxpay_h5']:0)); ?></td>
                <td><?php echo htmlentities((isset($summary['wxpay_wxwap']) && ($summary['wxpay_wxwap'] !== '')?$summary['wxpay_wxwap']:0)); ?></td>
                <td><?php echo htmlentities((isset($summary['system_free']) && ($summary['system_free'] !== '')?$summary['system_free']:0)); ?></td>
                <td><?php echo htmlentities((isset($summary['applepay_app']) && ($summary['applepay_app'] !== '')?$summary['applepay_app']:0)); ?></td>
                <td><?php echo htmlentities((isset($summary['system_pay']) && ($summary['system_pay'] !== '')?$summary['system_pay']:0)); ?></td>
                <td><?php echo htmlentities((number_format($summary['summary'],'2') ?: 0)); ?></td>
            <?php else: ?>
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
</div>


        <div class="data_title" style="margin-top: 20px;">列表信息</div>
        <div class="table_slide">
<table class="content_list mt_10 md_width">
    <thead>
    <tr>
        <td style="width: 10%;"><input type="checkbox" checkall="list_id" value="<?php echo htmlentities($vo['user_id']); ?>"/></td>
        <td style="width: 10%;">ID</td>
        <td style="width: 15%;">充值用户</td>
        <td style="width: 20%;">订单详情</td>
        <td style="width: 15%;">支付平台</td>
        <td style="width: 15%;">支付状态</td>
        <td style="width: 15%;">下单时间</td>
    </tr>
    </thead>
    <tbody>
    <?php if(!(empty($recharge_list) || (($recharge_list instanceof \think\Collection || $recharge_list instanceof \think\Paginator ) && $recharge_list->isEmpty()))): if(is_array($recharge_list) || $recharge_list instanceof \think\Collection || $recharge_list instanceof \think\Paginator): $i = 0; $__LIST__ = $recharge_list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
            <tr data-id="<?php echo htmlentities($vo['user_id']); ?>">
                <td><input class="list_id" type="checkbox" name="ids[]" value="<?php echo htmlentities($vo['id']); ?>"/></td>
                <td><?php echo htmlentities($vo['id']); ?></td>
                <td>
                    <div class="thumb">
                        <a target="_blank" href="<?php echo url('user/detail',['user_id'=>$vo['user_id']]); ?>"
                           class="thumb_img thumb_img_avatar">
                            <img src="<?php echo img_url($vo['avatar'],'200_200','avatar'); ?>"/>
                            <div class="thumb_level_box">
                                <img title="<?php echo htmlentities($vo['level_name']); ?>" src="<?php echo htmlentities($vo['level_icon']); ?>"/>
                            </div>
                        </a>
                        <p class="thumb_info">
                            <a target="_blank" href="<?php echo url('user/detail',['user_id'=>$vo['user_id']]); ?>">
                                [<?php echo htmlentities($vo['user_id']); ?>]<br/>
                                <?php echo htmlentities(user_name($vo)); ?>
                            </a>
                        </p>
                    </div>
                </td>
                <td>
                    <?php if($vo['isvirtual'] == '1'): ?>
                        <span class="fc_red">[虚拟充值]</span><br/>
                    <?php endif; ?>
                    订单号：<?php echo htmlentities($vo['order_no']); ?><br/>
                    支付号：<?php echo htmlentities($vo['third_trade_no']); ?>
                    <br/>
                    充值<?php echo htmlentities($vo['total_bean']); ?><?php echo APP_BEAN_NAME; ?>，金额：<?php echo htmlentities($vo['total_fee']); ?>元
                </td>
                <td>
                    <?php echo htmlentities(enum_name($vo['pay_method'],'pay_methods')); ?>
                </td>
                <td>
                    <?php switch($vo['pay_status']): case "0": ?>
                            <span class="fc_red">未支付</span>
                        <?php break; case "1": ?>
                            <span class="fc_green">已支付</span><br/>
                            <?php echo htmlentities(time_format($vo['pay_time'])); break; endswitch; ?>
                </td>
                <td><?php echo htmlentities(time_format($vo['create_time'])); ?></td>
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
    <script>
        new SearchList('.filter_box', myConfig);
        var startTime = $('[name=start_time]').flatpickr({
            dateFormat: 'Y-m-d',
            onChange: function (dateObj, dateStr, instance) {
                endTime.set('minDate', dateStr);
            }
        });
        var endTime = $('[name=end_time]').flatpickr({
            dateFormat: 'Y-m-d',
            onChange: function (dateObj, dateStr, instance) {
                startTime.set('maxDate', dateStr);
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
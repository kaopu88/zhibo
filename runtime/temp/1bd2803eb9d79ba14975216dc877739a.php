<?php /*a:10:{s:58:"/www/wwwroot/zhibb/application/admin/view/anchor/index.tpl";i:1619342624;s:61:"/www/wwwroot/zhibb/application/admin/view/public/base_nav.tpl";i:1693212818;s:61:"/www/wwwroot/zhibb/application/admin/view/public/jsconfig.tpl";i:1602499828;s:61:"/www/wwwroot/zhibb/application/admin/view/public/main_top.tpl";i:1593518960;s:59:"/www/wwwroot/zhibb/application/admin/view/public/toggle.tpl";i:1592625950;s:62:"/www/wwwroot/zhibb/application/admin/view/anchor/user_info.tpl";i:1624446314;s:62:"/www/wwwroot/zhibb/application/admin/view/user/user_agent2.tpl";i:1592625950;s:65:"/www/wwwroot/zhibb/application/admin/view/components/work_pop.tpl";i:1567562304;s:65:"/www/wwwroot/zhibb/application/admin/view/anchor/location_pop.tpl";i:1592645870;s:62:"/www/wwwroot/zhibb/application/admin/view/anchor/cash_rate.tpl";i:1594349984;}*/ ?>
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
    
    <script src="https://webapi.amap.com/maps?v=1.4.8&key=0d29625c9a07fbc35067cc31b0b30489"></script>
    <script>
        var myConfig = {
            list: [
                {
                    name: 'live_status',
                    title: '直播状态',
                    opts: [
                        {name: '禁用', value: '0'},
                        {name: '启用', value: '1'}
                    ]
                }
            ]
        };
    </script>
    <script src="<?php echo ('/static/'.strtolower(\think\facade\Request::module()).'/js'); ?>/location.js?v=<?php echo config('upload.resource_version'); ?>"></script>
    <script src="<?php echo ('/static/'.strtolower(\think\facade\Request::module()).'/js'); ?>/anchor/index.js?v=<?php echo config('upload.resource_version'); ?>"></script>

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
                    <?php if(check_auth('admin:user:change_live_status',AUTH_UID)): ?>
                        <div ajax="post" ajax-url="<?php echo url('user/change_live_status',['live_status'=>'1']); ?>"
                             ajax-target="list_id"
                             class="base_button base_button_s base_button_gray">直播启用
                        </div>
                        <div ajax="post" ajax-url="<?php echo url('user/change_live_status',['live_status'=>'0']); ?>"
                             ajax-target="list_id"
                             class="base_button base_button_s base_button_gray">直播禁用
                        </div>
                    <?php endif; ?>
                    <div class="filter_search">
                        <input placeholder="主播ID、手机号、昵称" type="text" name="keyword" value="<?php echo input('keyword'); ?>"/>
                        <button class="filter_search_submit">搜索</button>
                    </div>
                    <div class="clear"></div>
                </div>
            </div>
            <input type="hidden" name="live_status" value="<?php echo input('live_status'); ?>"/>
        </div>
        <div class="table_slide">
        <table class="content_list mt_10" style="min-width:975px;">
            <thead>
            <tr>
                <td style="width: 5%;"><input type="checkbox" checkall="list_id" value="<?php echo htmlentities($vo['user_id']); ?>"/></td>
                <td style="width: 5%;">ID</td>
                <td style="width: 12%;">主播信息</td>
                <td style="width: 8%;"><?php echo APP_MILLET_NAME; ?>余额</td>
                <td style="width: 8%;">累计获得<?php echo APP_MILLET_NAME; ?></td>
                <td style="width: 10%;">累计直播时长</td>
                <td style="width: 8%;">直播状态</td>
                <td style="width: 10%;display: none">直播位置</td>
                <td style="width: 7%;">提现比例</td>
                <td style="width: 10%;">所属<?php echo config('app.agent_setting.agent_name'); ?></td>
                <td style="width: 8%;">加入时间</td>
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
    <a href="<?php echo url('anchor/detail',['user_id'=>$vo['user_id']]); ?>" class="thumb_img thumb_img_avatar">
        <img src="<?php echo img_url($vo['avatar'],'200_200','avatar'); ?>"/>
        <div class="thumb_level_box">
            <img title="<?php echo htmlentities($vo['level_name']); ?>" src="<?php echo htmlentities($vo['level_icon']); ?>"/>
        </div>
    </a>
    <p class="thumb_info">
        <a href="<?php echo url('anchor/detail',['user_id'=>$vo['user_id']]); ?>">
            <?php echo htmlentities(user_name($vo)); ?>
            <br/>
            <?php echo htmlentities((str_hide($vo['phone'],3,4) ?: '未绑定')); ?>
        </a>
    </p>
</div>
                        </td>
                        <td>
                            <?php if($vo['millet_status'] == '1'): ?>
                                <span class="icon-credit"></span>&nbsp;<span><?php echo htmlentities($vo['millet']); ?></span><br/>
                                <?php else: ?>
                                <span class="icon-credit"></span>&nbsp;<span title="提现功能已禁用"
                                                                             class="fc_red"><?php echo htmlentities($vo['millet']); ?></span><br/>
                            <?php endif; ?>
                            <span class="fc_gray">  <span class="icon-lock"></span>&nbsp;<?php echo htmlentities($vo['fre_millet']); ?></span>
                        </td>
                        <td><?php echo htmlentities($vo['total_millet']); ?></td>
                        <td><?php echo htmlentities($vo['total_duration_str']); ?></td>
                        <td>
                            <div tgradio-not="<?php echo check_auth('admin:user:change_live_status')?'0':'1'; ?>" tgradio-on="1"
                                 tgradio-off="0" tgradio-value="<?php echo htmlentities($vo['live_status']); ?>"
                                 tgradio-name="live_status"
                                 tgradio="<?php echo url('user/change_live_status',['id'=>$vo['user_id']]); ?>"></div>
                        </td>
                        <td style="display: none">
                            <?php switch($vo['location']['location_type']): case "auto": ?>
                                    自动定位
                                <?php break; case "unknown": ?>
                                    始终未知
                                <?php break; case "static": ?>
                                    <?php echo htmlentities($vo['location']['city']); break; endswitch; if(check_auth('admin:anchor:change_location',AUTH_UID)): ?>
                                &nbsp;<a data-id="user_id:<?php echo htmlentities($vo['user_id']); ?>" poplink="anchor_location_box" title="修改主播位置"
                                         href="javascript:;"><span class="icon-location2"></span></a>
                            <?php endif; ?>
                        </td>

                        <td>
                            <a data-id="user_id:<?php echo htmlentities($vo['user_id']); ?>" poplink="anchor_cash_rate" title="修改主播提现比例" href="javascript:;">
                                <?php if($vo['cash_rate'] == 0): ?>
                                    默认
                                    <?php else: ?>
                                    <?php echo htmlentities($vo['cash_rate']); endif; ?>
                            </a>
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
                        <td><?php echo htmlentities(time_format($vo['create_time'],'','date')); ?></td>
                        <td>
                            <a href="<?php echo url('anchor/detail',['user_id'=>$vo['user_id']]); ?>">主播详情</a><br/>
                            <a href="<?php echo url('anchor/album',['user_id'=>$vo['user_id']]); ?>">主播相册</a><br/>
                            <?php if(check_auth('admin:anchor:cancel',AUTH_UID)): ?>
                                <a ajax="get" ajax-confirm="是否确认取消主播？" class="fc_red" href="<?php echo url('cancel',['user_id'=>$vo['user_id']]); ?>">取消主播</a>
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

    <div class="layer_box anchor_location_box" dom-key="anchor_location_box" title="设置直播位置"
     popbox-action="<?php echo url('anchor/location'); ?>" popbox-get-data="<?php echo url('anchor/location'); ?>">
    <div class="pa_20">
        <table class="content_info2" style="width: 100%">
            <tr>
                <td>定位类型</td>
                <td>
                    <select name="location_type" class="base_select">
                        <option value="">请选择</option>
                        <option value="unknown">始终未知</option>
                        <option value="auto">自动定位</option>
                        <option value="static">指定位置</option>
                    </select>
                </td>
            </tr>
            <tr class="static_tr">
                <td>指定位置</td>
                <td>
                    <div><span class="location_city_str"></span> [<span class="location_lng_str"></span>,<span class="location_lat_str"></span>]</div>
                    <div class="mt_10">
                        <input name="city" type="hidden" value=""/>
                        <input name="lat" type="hidden" value=""/>
                        <input name="lng" type="hidden" value=""/>
                        <div class="base_button base_button_gray open_map">打开地图选择位置</div>
                    </div>
                </td>
            </tr>
            <tr>
                <td colspan="2" style="text-align: center;">
                    <input type="hidden" name="user_id" value="" />
                    <div class="base_button_div" style="max-width:480px;">
                        <div class="base_button sub_btn">保存设置</div>
                    </div>
                </td>
            </tr>
        </table>
    </div>
</div>
    <div class="layer_box anchor_cash_rate" dom-key="anchor_cash_rate" title="设置比例"
     popbox-action="<?php echo url('anchor/cash'); ?>" popbox-get-data="<?php echo url('anchor/cash'); ?>">
    <div class="pa_20">
        <table class="content_info2" style="width: 100%">
            <tr>
                <td>比例</td>
                <td>
                    <input type="text" name="cash_rate" value=""  class="base_select"/>
                </td>
            </tr>

            <tr>
                <td colspan="2" style="text-align: center;">
                    <input type="hidden" name="user_id" value="" />
                    <div class="base_button_div" style="max-width:455px;">
                        <div class="base_button sub_btn">保存设置</div>
                    </div>
                </td>
            </tr>
        </table>
    </div>
</div>

<script src="/bx_static/toggle.js"></script>
</body>
</html>
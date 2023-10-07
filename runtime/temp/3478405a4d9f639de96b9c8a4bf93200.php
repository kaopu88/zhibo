<?php /*a:7:{s:57:"/www/wwwroot/zhibb/application/admin/view/agent/index.tpl";i:1605143216;s:61:"/www/wwwroot/zhibb/application/admin/view/public/base_nav.tpl";i:1693212818;s:61:"/www/wwwroot/zhibb/application/admin/view/public/jsconfig.tpl";i:1602499828;s:61:"/www/wwwroot/zhibb/application/admin/view/public/main_top.tpl";i:1593518960;s:59:"/www/wwwroot/zhibb/application/admin/view/public/toggle.tpl";i:1592625950;s:65:"/www/wwwroot/zhibb/application/admin/view/components/work_pop.tpl";i:1567562304;s:68:"/www/wwwroot/zhibb/application/admin/view/agent/viewback_handler.tpl";i:1600937228;}*/ ?>
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
                    name: 'status',
                    title: '启用状态',
                    opts: [
                        {name: '禁用', value: '0'},
                        {name: '启用', value: '1'}
                    ]
                },
                {
                    name: 'level',
                    title: "<?php echo config('app.agent_setting.agent_name'); ?>级别",
                    opts: [
                        {name: "一级<?php echo config('app.agent_setting.agent_name'); ?>", value: '0'},
                        {name: "二级<?php echo config('app.agent_setting.agent_name'); ?>", value: '1'},
                    ]
                },
                {
                    name: 'grade',
                    title: "<?php echo config('app.agent_setting.agent_name'); ?>等级",
                    opts: JSON.parse('<?php echo json_encode(enum_array("agent_grades")); ?>')
                },
                {
                    name: 'province',
                    title: '所在省份',
                    data: {country: 0},
                    auto_sub: false,
                    get: '<?php echo url("common/get_area"); ?>'
                },
                {
                    name: 'city',
                    parent: 'province',
                    title: '所在城市',
                    get: '<?php echo url("common/get_area"); ?>'
                },
                {
                    name: 'district',
                    parent: 'city',
                    title: '所在区县',
                    get: '<?php echo url("common/get_area"); ?>'
                }
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
                    <?php if(check_auth('admin:agent:add',AUTH_UID)): ?>
                        <a href="<?php echo url('agent/add'); ?>?<?php echo ('redirect='.urlencode(\think\facade\Request::url())); ?>" class="base_button base_button_s">新增</a>
                    <?php endif; if(check_auth('admin:agent:delete',AUTH_UID)): ?>
                        <a href="<?php echo url('agent/del'); ?>" ajax="post" ajax-target="list_id" ajax-confirm
                           class="base_button base_button_s base_button_red">删除</a>
                    <?php endif; ?>
                    <div class="filter_search">
                        <input placeholder="上级<?php echo config('app.agent_setting.agent_name'); ?>ID" type="text" name="pid" value="<?php echo input('pid'); ?>"/>
                        <input placeholder="手机号、名称、ID" type="text" name="keyword" value="<?php echo input('keyword'); ?>"/>
                        <button class="filter_search_submit">搜索</button>
                    </div>
                    <div class="clear"></div>
                </div>
            </div>
            <input type="hidden" name="status" value="<?php echo input('status'); ?>"/>
            <input type="hidden" name="level" value="<?php echo input('level'); ?>"/>
            <input type="hidden" name="grade" value="<?php echo input('grade'); ?>"/>
            <input type="hidden" name="province" value="<?php echo input('province'); ?>"/>
            <input type="hidden" name="city" value="<?php echo input('city'); ?>"/>
            <input type="hidden" name="district" value="<?php echo input('district'); ?>"/>
        </div>
        <div class="table_slide">
        <table class="content_list mt_10 sm_width">
            <thead>
            <tr>
                <td style="width: 5%"><input type="checkbox" checkall="list_id"/></td>
                <!--                <td style="width: 5%">ID</td>-->
                <td style="width: 15%"><?php echo config('app.agent_setting.agent_name'); ?></td>
<!--                <td style="width: 8%">二级<?php echo config('app.agent_setting.agent_name'); ?></td>-->
                <!--               <td style="width: 10%">团队规模</td>-->
               <!--                <td style="width: 10%">累计业绩</td>-->
                <td style="width: 15%">所在地区</td>
                <td style="width: 7%">管理员</td>
                <td style="width: 8%">上级<?php echo config('app.agent_setting.agent_name'); ?></td>
                <td style="width: 8%">状态</td>
                <td style="width: 7%">提现状态</td>
                <td style="width: 8%">结算方式</td>
                <td style="width: 15%">相关时间</td>
                <td style="width: 20%">操作</td>
            </tr>
            </thead>
            <tbody>
            <?php if(!(empty($_list) || (($_list instanceof \think\Collection || $_list instanceof \think\Paginator ) && $_list->isEmpty()))): if(is_array($_list) || $_list instanceof \think\Collection || $_list instanceof \think\Paginator): $i = 0; $__LIST__ = $_list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
                    <tr>
                        <td><input class="list_id" type="checkbox" name="id[]" value="<?php echo htmlentities($vo['id']); ?>"/></td>
                        <td>
                            <div class="thumb">
                                <a href="javascript:;" class="thumb_img">
                                    <img src="<?php echo img_url($vo['logo'],'200_200','logo'); ?>"/>
                                </a>
                                <p class="thumb_info">
                                    <a href="javascript:;">
                                        <?php echo htmlentities($vo['name']); ?><br/>
                                        <?php echo $vo['level']+1; ?>级&nbsp;&nbsp;
                                    </a>
                                </p>
                            </div>
                        </td>
                        <!--
                        <td>
                            <?php if($vo['add_sec'] == '1'): if($vo['sec_num'] < $vo['max_sec_num']): ?>
                                    <a title="查看下级<?php echo config('app.agent_setting.agent_name'); ?>" href="<?php echo url('agent/index',['pid'=>$vo['id']]); ?>" class="fc_green"><?php echo htmlentities($vo['sec_num']); ?>/<?php echo htmlentities($vo['max_sec_num']); ?></a>
                                    <?php else: ?>
                                    <a title="查看下级<?php echo config('app.agent_setting.agent_name'); ?>" href="<?php echo url('agent/index',['pid'=>$vo['id']]); ?>" class="fc_red"><?php echo htmlentities($vo['sec_num']); ?>/<?php echo htmlentities($vo['max_sec_num']); ?></a>
                                <?php endif; else: ?>
                                <span class="fc_red">未开通</span>
                            <?php endif; if(!(empty($vo['parent']) || (($vo['parent'] instanceof \think\Collection || $vo['parent'] instanceof \think\Paginator ) && $vo['parent']->isEmpty()))): ?>
                                <br/>
                                <a href="<?php echo url('index',['pid'=>$vo['pid']]); ?>">上级：<?php echo htmlentities($vo['parent']['name']); ?></a>
                            <?php endif; ?>
                        </td>
                        -->
                        <!--
                        <td>
                            <?php echo config('app.agent_setting.promoter_name'); ?>：<?php echo htmlentities($vo['promoter_num']); ?><br/>
                            主播：<?php echo htmlentities($vo['anchor_num']); ?>
                        </td>
                        -->
                        <!--
                        <td>
                            客消：<?php echo htmlentities($vo['total_cons']); ?><br/>
                            <?php echo APP_MILLET_NAME; ?>：<?php echo htmlentities($vo['total_millet']); ?><br/>
                            拉新：<?php echo htmlentities($vo['total_fans']); ?>
                        </td>
                        -->
                        <td>
                            <?php echo htmlentities($vo['province_name']); ?><br>
                            <?php echo htmlentities($vo['city_name']); ?>
                        </td>
                        <td>
                            <?php echo htmlentities($vo['admin_name']); ?>
                        </td>
                        <td>
                            <?php echo htmlentities((isset($vo['parent']['name']) && ($vo['parent']['name'] !== '')?$vo['parent']['name']:'暂无')); ?>
                        </td>
                        <td>
                            <?php switch($vo['applystatus']): case "0": ?>
                                    <div  tgradio-not="<?php echo check_auth('admin:agent:update')?'0':'1'; ?>" tgradio-on="1"
                                           tgradio-off="0" tgradio-value="<?php echo htmlentities($vo['status']); ?>" tgradio-name="status"
                                           tgradio="<?php echo url('agent/change_status',array('id'=>$vo['id'])); ?>"></div>
                                <?php break; case "1": ?>
                                    等待审核
                                <?php break; case "2": ?>
                                    审核未通过
                                <?php break; endswitch; ?>

                        </td>

                        <td>
                            <div tgradio-not="<?php echo check_auth('admin:agent:update')?'0':'1'; ?>" tgradio-on="1"
                                 tgradio-off="0" tgradio-value="<?php echo htmlentities($vo['cash_on']); ?>" tgradio-name="cash_on"
                                 tgradio="<?php echo url('agent/change_cash_on',array('id'=>$vo['id'])); ?>"></div>
                        </td>

                        <td>
                            <?php if($vo['cash_type'] == 0): ?>
                                    默认
                            <?php endif; if($vo['cash_type'] == 1): ?>
                                平台结算
                            <?php endif; if($vo['cash_type'] == 2): ?>
                                公会结算
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php switch($vo['expire_status']): case "0": ?>
                                    <span class="fc_green">合作中</span>
                                <?php break; case "1": ?>
                                    <span class="fc_orange">即将过期</span>
                                <?php break; case "2": ?>
                                    <span class="fc_red">已过期</span>
                                <?php break; endswitch; ?>
                            <br/>
                            到期：<?php echo htmlentities(time_format($vo['expire_time'],'','date')); ?><br/>
                            注册：<?php echo htmlentities(time_format($vo['create_time'],'','date')); ?>
                        </td>
                        <td>
                            <?php if(!(empty($vo['root_id']) || (($vo['root_id'] instanceof \think\Collection || $vo['root_id'] instanceof \think\Paginator ) && $vo['root_id']->isEmpty()))): if(check_auth('admin:agent:update',AUTH_UID)): ?>
                                    <a href="<?php echo url('agent/edit',array('id'=>$vo['id'])); ?>?<?php echo ('redirect='.urlencode(\think\facade\Request::url())); ?>">编辑信息</a> <br/>
                                <?php endif; else: switch($vo['applystatus']): case "0": ?>
                                        <a href="<?php echo url('agent/set_root',array('id'=>$vo['id'])); ?>?<?php echo ('redirect='.urlencode(\think\facade\Request::url())); ?>">设置主账号</a> <br/>
                                    <?php break; case "1": break; case "2": break; endswitch; endif; if(check_auth('admin:agent:transfer',AUTH_UID)): ?>
                                <a target="_blank" href="<?php echo url('agent/transfer',array('id'=>$vo['id'])); ?>">传送后台</a><br/>
                            <?php endif; if(check_auth('admin:agent:delete',AUTH_UID)): ?>
                                <a class="fc_red" ajax-confirm ajax="get"
                                   href="<?php echo url('agent/del',array('id'=>$vo['id'])); ?>">删除<?php echo config('app.agent_setting.agent_name'); ?></a>
                            <?php endif; switch($vo['applystatus']): case "0": break; case "1": if(check_auth('admin:agent:update',AUTH_UID)): ?>
                                        <a href="<?php echo url('agent/show',array('id'=>$vo['id'])); ?>?<?php echo ('redirect='.urlencode(\think\facade\Request::url())); ?>">查看信息</a> <br/>
                                    <?php endif; if(check_auth('admin:viewback:audit',AUTH_UID)): ?>
                                        <a data-id="id:<?php echo htmlentities($vo['id']); ?>" poplink="viewback_handler"
                                           href="javascript:;">审核</a><br/>
                                    <?php endif; break; case "2": ?>
                                    原因：<?php echo htmlentities($vo['handle_desc']); break; endswitch; ?>
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
        new SearchList('.filter_box', myConfig);
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

    <div title="处理反馈申请" class="layer_box viewback_handler pa_10" dom-key="viewback_handler" popbox-action="<?php echo url('agent/handler'); ?>">
    <table class="content_info2">
        <tr>
            <td class="field_name">处理状态</td>
            <td>
                <select name="audit_status" class="base_select">
                    <option value="">请选择</option>
                    <option value="1">通过</option>
                    <option value="0">驳回</option>
                </select>
            </td>
        </tr>
        <tr>
            <td class="field_name">备注</td>
            <td>
                <textarea name="handle_desc" class="base_textarea"></textarea>
            </td>
        </tr>
        <tr>
            <td class="field_name"></td>
            <td>
                <input type="hidden" name="id" value="" />
                <div class="base_button sub_btn">提交</div>
            </td>
        </tr>
    </table>
</div>

<script src="/bx_static/toggle.js"></script>
</body>
</html>
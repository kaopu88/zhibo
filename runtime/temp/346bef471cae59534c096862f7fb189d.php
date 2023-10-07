<?php /*a:7:{s:64:"/www/wwwroot/zhibb/application/admin/view/anchor_apply/index.tpl";i:1694597310;s:61:"/www/wwwroot/zhibb/application/admin/view/public/base_nav.tpl";i:1693212818;s:61:"/www/wwwroot/zhibb/application/admin/view/public/jsconfig.tpl";i:1602499828;s:61:"/www/wwwroot/zhibb/application/admin/view/public/main_top.tpl";i:1593518960;s:59:"/www/wwwroot/zhibb/application/admin/view/public/toggle.tpl";i:1592625950;s:68:"/www/wwwroot/zhibb/application/admin/view/recharge_app/user_info.tpl";i:1624446656;s:65:"/www/wwwroot/zhibb/application/admin/view/components/work_pop.tpl";i:1567562304;}*/ ?>
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
    
    <style>
        .thumb .thumb_img {
            flex: none;
            width: 100px;
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


            
    <div class="pa_20 p-0" style="padding-bottom: 1px !important;">
        <ul class="tab_nav mt_10">
            <li><a target="_self" class="<?php if($status == 0): ?> current <?php endif; ?>" href="<?php echo url('anchor_apply/index'); ?>">全部<span unread-types="" class="badge_unread">0</span></a></li>
            <li><a target="_self" class="<?php if($status == 1): ?> current <?php endif; ?>" href="<?php echo url('anchor_apply/index',['status'=>1]); ?>">待实名审核<span unread-types="user_verified" class="badge_unread" style="display: none;">0</span></a></li>
            <li><a target="_self" class="<?php if($status == 4): ?> current <?php endif; ?>" href="<?php echo url('anchor_apply/index',['status'=>4]); ?>">待公会审核<span unread-types="" class="badge_unread">0</span></a></li>
            <li><a target="_self" class="<?php if($status == 3): ?> current <?php endif; ?>" href="<?php echo url('anchor_apply/index',['status'=>3]); ?>">待平台审核<span unread-types="" class="badge_unread">0</span></a></li>
            <li><a target="_self" class="<?php if($status == 2): ?> current <?php endif; ?>" href="<?php echo url('anchor_apply/index',['status'=>2]); ?>">已通过<span unread-types="" class="badge_unread">0</span></a></li>
            <li><a target="_self" class="<?php if($status == 5): ?> current <?php endif; ?>" href="<?php echo url('anchor_apply/index',['status'=>5]); ?>">未通过<span unread-types="" class="badge_unread">0</span></a></li>
            <li><a target="_self" class="<?php if($status == 6): ?> current <?php endif; ?>" href="<?php echo url('anchor_apply/index',['status'=>6]); ?>">待支付<span unread-types="" class="badge_unread">0</span></a></li>
        </ul>
        <div class="bg_container">
            <div class="filter_box mt_10">
                <div class="filter_nav">
                    已选择&nbsp;>&nbsp;
                    <p class="filter_selected"></p>
                </div>

                <div class="filter_options">
                    <ul class="filter_list"></ul>
                    <div class="filter_order">
                        <?php if(check_auth('admin:anchorApply:reviews',AUTH_UID)): ?>
                            <div ajax="post" ajax-url="<?php echo url('anchorApply/reviews',['status'=>'2']); ?>" ajax-target="list_id"
                                 class="base_button base_button_s base_button_gray">批量审核
                            </div>
                        <?php endif; ?>
                        <div class="filter_search">
                            <input placeholder="用户ID" type="text" name="user_id" value="<?php echo input('user_id'); ?>"/>
                            <button class="filter_search_submit">搜索</button>
                        </div>
                        <div class="clear"></div>
                    </div>
                </div>
                <input type="hidden" name="status" value="<?php echo htmlentities($get['status']); ?>"/>
            </div>
            <div class="table_slide">
                <table class="content_list mt_10">
                    <thead>
                    <tr>
                        <td style="width: 5%;"><input type="checkbox" checkall="list_id" value=""/></td>
                        <td style="width: 5%;">ID</td>
                        <td style="width: 15%;">用户信息</td>
                        <td style="width: 10%;">主播类型</td>
                        <td style="width: 10%;">处理描述</td>
                        <td style="width: 15%;">审核状态</td>
                        <td style="width: 10%;">操作</td>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if(!(empty($_list) || (($_list instanceof \think\Collection || $_list instanceof \think\Paginator ) && $_list->isEmpty()))): if(is_array($_list) || $_list instanceof \think\Collection || $_list instanceof \think\Paginator): $i = 0; $__LIST__ = $_list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
                            <tr data-id="<?php echo htmlentities($vo['id']); ?>">
                                <td><input class="list_id" type="checkbox" name="ids[]" value="<?php echo htmlentities($vo['id']); ?>"/></td>
                                <td><?php echo htmlentities($vo['id']); ?></td>
                                <td>
                                    <div class="thumb">
    <a href="<?php echo url('user/detail',['user_id'=>$vo['user']['user_id']]); ?>"
       class="thumb_img thumb_img_avatar">
        <img src="<?php echo img_url($vo['user']['avatar'],'200_200','avatar'); ?>"/>
        <div class="thumb_level_box">
            <img title="<?php echo htmlentities($vo['user']['level_name']); ?>" src="<?php echo htmlentities($vo['user']['level_icon']); ?>"/>
        </div>
    </a>
    <p class="thumb_info">
        <a href="<?php echo url('user/detail',['user_id'=>$vo['user']['user_id']]); ?>">
            <?php if($vo['user']['isvirtual'] == '1'): ?>
                <span class="fc_red">[虚拟号]</span><br/>
            <?php endif; ?>
            <?php echo htmlentities(user_name($vo['user'])); ?><br/>
            <?php echo htmlentities((str_hide($vo['user']['phone'],3,4) ?: '未绑定')); ?>
        </a>
    </p>
</div>
                                </td>

                                <td><span style="color: red"><?php if($vo['agent_id'] != 0): ?>公会主播 <br>(公会名称: <?php echo htmlentities(get_agent($vo['agent_id'])); ?>)<?php else: ?>个人主播<?php endif; ?></span>
                                </td>

                                <td>


                                </td>
                                <td>
                                    申请：<?php echo htmlentities(time_format($vo['create_time'])); ?><br/>
                                    处理：  <?php switch($vo['status']): case "1": ?>
                                            <a href="javascript:;" class="fc_blue">待实名处理<span style="color: blue">(实名审核状态:<?php echo htmlentities($vo['reason']); ?>)</span></a>
                                        <?php break; case "2": ?>
                                            <a href="javascript:;" class="fc_green">已通过</a>
                                        <?php break; case "3": ?>
                                            <a href="javascript:;" class="fc_blue">待平台审核</a>
                                        <?php break; case "4": ?>
                                            <a href="javascript:;" class="fc_blue">待公会审核<span style="color: blue">(公会审核状态:<?php echo htmlentities($vo['reason']); ?>)</span></a>
                                        <?php break; case "5": ?>
                                            <a href="javascript:;" class="fc_red">未通过</a>
                                        <?php break; case "6": ?>
                                            <a href="javascript:;" class="fc_orange">待支付</a>
                                        <?php break; endswitch; ?>
                                </td>
                                <td>
                                    <?php switch($vo['status']): case "3": ?>
                                            <a ajax="get"  <?php if($vo['status'] == 3): ?>  ajax-confirm="是否通过该申请？"  class="fc_gray" <?php else: ?>   class="fc_red"<?php endif; ?>  href="<?php echo url('anchor_apply/review',['id'=>$vo['id'],'status'=>2]); ?>">审核</a>
                                            <a href="javascript:;" class="reject" data-id="<?php echo htmlentities($vo['id']); ?>">驳回</a>
                                        <?php break; endswitch; ?>
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

    <script>
        $(function () {
            new SearchList('.filter_box',myConfig);

            $('.reject').on('click', function () {
                var id = $(this).data("id");
                layer.prompt({
                    formType: 2,
                    value: '管理员驳回',
                    title: '请输入驳回理由'
                },function(val, index){
                    $.ajax({
                        type: "POST",
                        url: '/admin/anchor_apply/review',
                        data: {id: id, reason: val, status: 5},
                        dataType: "json",
                        success: function (rs) {
                            layer.close(index);
                            if( rs.status == 0 ){
                                layer.msg('驳回成功', {}, function(){
                                    location.reload();
                                });
                            }
                        }
                    });

                });
            });
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
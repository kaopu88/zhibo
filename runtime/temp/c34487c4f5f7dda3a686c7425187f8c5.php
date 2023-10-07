<?php /*a:9:{s:61:"/www/wwwroot/zhibb/application/admin/view/complaint/index.tpl";i:1592625950;s:61:"/www/wwwroot/zhibb/application/admin/view/public/base_nav.tpl";i:1693212818;s:61:"/www/wwwroot/zhibb/application/admin/view/public/jsconfig.tpl";i:1602499828;s:61:"/www/wwwroot/zhibb/application/admin/view/public/main_top.tpl";i:1593518960;s:59:"/www/wwwroot/zhibb/application/admin/view/public/toggle.tpl";i:1592625950;s:68:"/www/wwwroot/zhibb/application/admin/view/recharge_app/user_info.tpl";i:1624446656;s:65:"/www/wwwroot/zhibb/application/admin/view/components/work_pop.tpl";i:1567562304;s:79:"/www/wwwroot/zhibb/application/admin/view/recharge_app/recharge_app_handler.tpl";i:1592625950;s:74:"/www/wwwroot/zhibb/application/admin/view/components/task_transfer_box.tpl";i:1592645870;}*/ ?>
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
                    name: 'audit_status',
                    title: '状态',
                    opts: [
                        {name: '待审核', value: '0'},
                        {name: '已通过', value: '1'},
                        {name: '未通过', value: '2'}
                    ]
                },
                {
                    name: 'target_type',
                    title: '举报对象',
                    auto_sub: false,
                    opts: [
                        {name: '用户', value: 'user'},
                        {name: '短视频', value: 'film'},
                        {name: '评论', value: 'comment'}
                    ]
                },
                {
                    name: 'cid',
                    parent: 'target_type',
                    title: '举报类型',
                    get: '<?php echo url("complaint/get_category"); ?>'
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
                    <div class="filter_search">
                        <input type="text" name="user_id" value="<?php echo input('user_id'); ?>" placeholder="举报人ID"/>
                        <input type="text" name="to_uid" value="<?php echo input('to_uid'); ?>" placeholder="被举报人ID"/>
                        <button class="filter_search_submit">搜索</button>
                    </div>
                    <div class="clear"></div>
                </div>
            </div>
            <input type="hidden" name="audit_status" value="<?php echo htmlentities($get['audit_status']); ?>" />
            <input type="hidden" name="target_type" value="<?php echo htmlentities($get['target_type']); ?>" />
            <input type="hidden" name="cid" value="<?php echo htmlentities($get['cid']); ?>" />
        </div>
        <div class="table_slide">
        <table class="content_list mt_10">
            <thead>
            <tr>
                <td style="width: 5%;">ID</td>
                <td style="width: 15%;">举报人</td>
                <td style="width: 15%;">被举报人</td>
                <td style="width: 15%;">举报对象</td>
                <td style="width: 20%;">举报类型</td>
                <td style="width: 15%;">处理描述</td>
                <td style="width: 15%;">审核状态</td>
            </tr>
            </thead>
            <tbody>
            <?php if(!(empty($_list) || (($_list instanceof \think\Collection || $_list instanceof \think\Paginator ) && $_list->isEmpty()))): if(is_array($_list) || $_list instanceof \think\Collection || $_list instanceof \think\Paginator): $i = 0; $__LIST__ = $_list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
                    <tr data-id="<?php echo htmlentities($vo['id']); ?>">
                        <td><?php echo htmlentities($vo['id']); ?></td>
                        <td><div class="thumb">
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
</div></td>
                        <td>
                            <div class="thumb">
                                <a href="<?php echo url('user/detail',['user_id'=>$vo['to_user']['user_id']]); ?>"
                                   class="thumb_img thumb_img_avatar">
                                    <img src="<?php echo img_url($vo['to_user']['avatar'],'200_200','avatar'); ?>"/>
                                    <div class="thumb_level_box">
                                        <img title="<?php echo htmlentities($vo['to_user']['level_name']); ?>" src="<?php echo htmlentities($vo['to_user']['level_icon']); ?>"/>
                                    </div>
                                </a>
                                <p class="thumb_info">
                                    <a href="<?php echo url('user/detail',['user_id'=>$vo['to_user']['user_id']]); ?>">
                                        <?php if($vo['to_user']['isvirtual'] == '1'): ?>
                                            <span class="fc_red">[虚拟号]</span><br/>
                                        <?php endif; ?>
                                        <?php echo htmlentities(user_name($vo['to_user'])); ?><br/>
                                        <?php echo htmlentities((isset($vo['to_user']['phone']) && ($vo['to_user']['phone'] !== '')?$vo['to_user']['phone']:'未绑定')); ?>
                                    </a>
                                </p>
                            </div>
                        </td>
                        <td>
                            【<?php echo htmlentities($vo['target_type_name']); ?>】
                            <?php switch($vo['target_type']): case "user": ?>
                                    <a href="<?php echo url('user/detail',['user_id'=>$vo['target_info']['user_id']]); ?>"><?php echo htmlentities(user_name($vo['target_info'])); ?></a>
                                <?php break; case "comment": ?><a href=""><?php if($vo['target_info']['content'] != ''): ?><?php echo htmlentities($vo['target_info']['content']); else: ?>该评论已删除<?php endif; ?></a>
                                <?php break; case "film": ?>
                                    <a href="">
                                        <div class="thumb">
                                            <a layer-title="0" layer-area="414px,779px"
                                               layer-open="<?php echo url('video/tcplayer',['id'=>$vo['target_info']['id']]); ?>" href="javascript:;"
                                               class="thumb_img">
                                                <img src="<?php echo img_url($vo['target_info']['cover_url'],'120_68','film_cover'); ?>"/>
                                            </a>
                                            <p class="thumb_info">
                                                <a target="_blank" href="<?php echo url('video/detail',['id'=>$vo['target_info']['id']]); ?>"><?php echo htmlentities($vo['target_info']['title']); ?></a>
                                            </p>
                                        </div>
                                    </a>
                                <?php break; case "music": ?><a href="javascript:;"><span class="icon-music" style="margin-right: 3px;"></span><?php echo htmlentities(short($vo['target_info']['title'],15)); ?></a>
                                <?php break; endswitch; ?>
                        </td>
                        <td>
                            <?php if($vo['cinfo']['name'] != ''): ?>
                                <?php echo htmlentities($vo['cinfo']['name']); else: ?>
                                <?php echo htmlentities($vo['content']); endif; ?>
                        </td>
                        <td>
                            <?php switch($vo['audit_status']): case "0": ?>
                                    待审核
                                <?php break; case "1": ?><a href="javascript:;" class="fc_green">已通过</a>
                                <?php break; case "2": ?>
                                    <a href="javascript:;" class="fc_red">未通过</a>
                                <?php break; endswitch; ?>
                            <br/><?php if(!(empty($vo['audit_admin']) || (($vo['audit_admin'] instanceof \think\Collection || $vo['audit_admin'] instanceof \think\Paginator ) && $vo['audit_admin']->isEmpty()))): ?>
                                <a admin-id="<?php echo htmlentities($vo['audit_admin']['id']); ?>" href="javascript:;"><?php echo htmlentities(user_name($vo['audit_admin'])); ?></a>
                                <?php else: ?>
                                未分配
                            <?php endif; if($vo['audit_status'] != '0'): ?>
                                <br/>处理详情：<?php echo htmlentities($vo['handle_desc']); endif; ?>
                        </td>
                        <td>
                            申请：<?php echo htmlentities(time_format($vo['create_time'])); ?><br/>
                            处理：<?php if($vo['handle_time'] != '0'): ?><?php echo htmlentities(time_format($vo['handle_time'],'未处理')); else: ?>未处理<?php endif; ?>
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
        $(function () {
            new SearchList('.filter_box',myConfig);

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

    <div title="处理充值申请" class="layer_box recharge_app_handler pa_10" dom-key="recharge_app_handler" popbox-action="<?php echo url('recharge_app/handler'); ?>">
    <table class="content_info2">
        <tr>
            <td class="field_name">处理状态</td>
            <td>
                <select name="audit_status" class="base_select">
                    <option value="">请选择</option>
                    <option value="1">通过</option>
                    <option value="2">驳回</option>
                </select>
            </td>
        </tr>
        <tr>
            <td class="field_name">备注</td>
            <td>
                <textarea name="audit_remark" class="base_textarea"></textarea>
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
    <div dom-key="task_transfer_box" popbox="task_transfer_box" class="task_transfer_box layer_box" title="转交任务"
     popbox-action="<?php echo url('personal/task_transfer'); ?>" popbox-get-data="<?php echo url('personal/task_transfer'); ?>"
     popbox-area="510px,350px">
    <div class="pa_10">
        <table class="content_info2">
            <tr>
                <td>任务类型</td>
                <td>
                    <select name="type" class="base_select">
                        <option value="">请选择任务类型</option>
                        <?php if(is_array($work_types) || $work_types instanceof \think\Collection || $work_types instanceof \think\Paginator): $i = 0; $__LIST__ = $work_types;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$work_type): $mod = ($i % 2 );++$i;?>
                            <option value="<?php echo htmlentities($work_type['value']); ?>"><?php echo htmlentities($work_type['name']); ?></option>
                        <?php endforeach; endif; else: echo "" ;endif; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td>任务ID</td>
                <td><input name="id" class="base_text" value="" readonly/></td>
            </tr>
            <tr>
                <td>接手人</td>
                <td>
                    <select class="base_select admin_select">
                        <?php if(is_array($work_types) || $work_types instanceof \think\Collection || $work_types instanceof \think\Paginator): $i = 0; $__LIST__ = $work_types;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$work_type): $mod = ($i % 2 );++$i;?>
                            <option value="<?php echo htmlentities($work_type['value']); ?>"><?php echo htmlentities($work_type['name']); ?></option>
                        <?php endforeach; endif; else: echo "" ;endif; ?>
                    </select><br/>
                    <input name="aid" value="" placeholder="接手人的ID" class="base_text mt_10" />
                    <p class="field_tip">不选择接手人也可以直接填写接手人的ID</p>
                </td>
            </tr>
            <tr>
                <td></td>
                <td>
                  <div class="base_button_div max_w_412">
                    <div class="base_button sub_btn">确认转交</div>
                </div>
                </td>
            </tr>
        </table>
    </div>
</div>

<script src="/bx_static/toggle.js"></script>
</body>
</html>
<?php /*a:7:{s:62:"/www/wwwroot/zhibb/application/videotask/view/task_set/set.tpl";i:1596766212;s:65:"/www/wwwroot/zhibb/application/videotask/view/public/base_nav.tpl";i:1593227892;s:65:"/www/wwwroot/zhibb/application/videotask/view/public/jsconfig.tpl";i:1587813326;s:65:"/www/wwwroot/zhibb/application/videotask/view/public/main_top.tpl";i:1593518960;s:63:"/www/wwwroot/zhibb/application/videotask/view/public/toggle.tpl";i:1591944838;s:68:"/www/wwwroot/zhibb/application/videotask/view/components/tab_nav.tpl";i:1588735924;s:69:"/www/wwwroot/zhibb/application/videotask/view/components/work_pop.tpl";i:1592374474;}*/ ?>
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
        select_agents: '<?php echo url("admin/user_transfer/select_agents"); ?>',//选择<?php echo config('app.agent_setting.agent_name'); ?>
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


            
    <div class="pa_20 p-0">
        <ul class="tab_nav mt_10">
    <?php if(is_array($admin_tree[3]['children']) || $admin_tree[3]['children'] instanceof \think\Collection || $admin_tree[3]['children'] instanceof \think\Paginator): $i = 0; $__LIST__ = $admin_tree[3]['children'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$menu4): $mod = ($i % 2 );++$i;?>
        <li><a target="<?php echo htmlentities($menu4['target']); ?>" class="<?php echo htmlentities($menu4['current']); ?>" href="<?php echo htmlentities($menu4['menu_url']); ?>"><?php echo htmlentities($menu4['name']); ?><span unread-types="<?php echo htmlentities($menu4['badge']); ?>" class="badge_unread">0</span></a></li>
    <?php endforeach; endif; else: echo "" ;endif; ?>
</ul>

<style>
    .pa_20 > ul{
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
    .base_button{
        float: left;
        margin-left: 445px;
    }

    .base_button_a{
        margin-right: 65.5%;
        float: right;
        margin-left: 0;
    }

</style>
<link rel="stylesheet" href="/static/vendor/layer/layui/css/layui.css">
<script src="/static/vendor/layer/layui/layui.js"></script>
<script>
    layui.use(['element', 'layer'], function(){
        var element = layui.element,layer = layui.layer;
    });
</script>
        <div class="bg_form min_w_unset">
            <form action="<?php echo url('set'); ?>">
                <div class="layui-tab layui-tab-brief" lay-filter="docDemoTabBrief">
                    <ul class="layui-tab-title">
                        <li class="layui-this">新人特权</li>
                        <li>签到任务</li>
                        <li>拍摄短视频</li>
                        <li>观看短视频</li>
                    </ul>
                    <div class="layui-tab-content">

                        <div class="layui-tab-item layui-show">
                            <table class="content_info2 mt_10">
                                <tr>
                                    <td class="field_name">是否开启</td>
                                    <td>
                                        <select class="base_select" name="new_people_task_config[is_status]" selectedval="<?php echo htmlentities($_info['new_people_task_config']['is_status']); ?>">
                                            <option value="2">开启</option>
                                            <option selected value="1">关闭</option>
                                        </select>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="field_name">简要</td>
                                    <td>
                                        <input class="base_text" name="new_people_task_config[is_withdraw_brief]" value="<?php echo htmlentities($_info['new_people_task_config']['is_withdraw_brief']); ?>"/>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="field_name">首次最低提现</td>
                                    <td>
                                        <input class="base_text" name="new_people_task_config[new_first_withdraw]" value="<?php echo htmlentities($_info['new_people_task_config']['new_first_withdraw']); ?>"/>
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <div class="layui-tab-item">
                            <table class="content_info2 mt_10">
                                <tr>
                                    <td class="field_name">是否开启</td>
                                    <td>
                                        <select class="base_select" name="new_people_task_config[is_sign_status]" selectedval="<?php echo htmlentities($_info['new_people_task_config']['is_sign_status']); ?>">
                                            <option value="2">开启</option>
                                            <option selected value="1">关闭</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">签到简要</td>
                                    <td>
                                        <input class="base_text" name="new_people_task_config[sign_brief]" value="<?php echo htmlentities($_info['new_people_task_config']['sign_brief']); ?>"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name"></td>
                                    <td>
                                        <div class="base_group">
                                            <span class="base_label" style="width: 92px;text-align: center;">签到</span>
                                            <span class="base_label" style="width: 95px;text-align: center;">已签</span>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">文字自定义</td>
                                    <td>
                                        <input class="base_text" name="new_people_task_config[sign_text][textsign]" style="width: 80px;" value="<?php echo htmlentities($_info['new_people_task_config']['sign_text']['textsign']); ?>"/>
                                        <input class="base_text" name="new_people_task_config[sign_text][textsigned]" style="width: 80px;" value="<?php echo htmlentities($_info['new_people_task_config']['sign_text']['textsigned']); ?>"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">签到周期(天)</td>
                                    <td>
                                        <input class="base_text" name="new_people_task_config[is_sign_circle]" style="width: 80px;" value="<?php echo htmlentities($_info['new_people_task_config']['is_sign_circle']); ?>"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name"></td>
                                    <td>
                                        <div class="base_group">
                                            <span class="base_label" style="width: 92px;text-align: center;"><?php echo APP_MILLET_NAME; ?></span>
                                            <span class="base_label" style="width: 95px;text-align: center;"><?php echo APP_BEAN_NAME; ?></span>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">签到提醒奖励奖励</td>
                                    <td>
                                        <input class="base_text" name="new_people_task_config[sign_warn_reward][millet]" style="width: 80px;" value="<?php echo htmlentities($_info['new_people_task_config']['sign_warn_reward']['millet']); ?>"/>
                                        <input class="base_text" name="new_people_task_config[sign_warn_reward][bean]" style="width: 80px;" value="<?php echo htmlentities($_info['new_people_task_config']['sign_warn_reward']['bean']); ?>"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">普通奖励 <br>(自动发放)</td>
                                    <td>
                                        <input class="base_text" name="new_people_task_config[sign_reward][millet]" style="width: 80px;" value="<?php echo htmlentities($_info['new_people_task_config']['sign_reward']['millet']); ?>"/>
                                        <input class="base_text" name="new_people_task_config[sign_reward][bean]" style="width: 80px;" value="<?php echo htmlentities($_info['new_people_task_config']['sign_reward']['bean']); ?>"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">奖励递增 <br>(连续签到递增)</td>
                                    <td>
                                        <input class="base_text" name="new_people_task_config[sign_continuity_reward][millet]" style="width: 80px;" value="<?php echo htmlentities($_info['new_people_task_config']['sign_continuity_reward']['millet']); ?>"/>
                                        <input class="base_text" name="new_people_task_config[sign_continuity_reward][bean]" style="width: 80px;" value="<?php echo htmlentities($_info['new_people_task_config']['sign_continuity_reward']['bean']); ?>"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name"></td>
                                    <td>
                                        <ul id="content">
                                            <?php if(is_array($items) || $items instanceof \think\Collection || $items instanceof \think\Paginator): $i = 0; $__LIST__ = $items;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
                                                <li class="recharge-item" style="padding-top: 10px">
                                                    <span class="input-group-addon">连续签到&nbsp&nbsp&nbsp</span>
                                                    <input style="width: 100px" class="base_text" name="new_people_task_config[sign_day][]" value="<?php echo htmlentities($vo['sign_day']); ?>"/>
                                                    <span class="input-group-addon">天&nbsp&nbsp&nbsp奖励<?php echo APP_MILLET_NAME; ?>&nbsp&nbsp</span>
                                                    <input style="width: 160px" class="base_text" name="new_people_task_config[sign_millet][]" value="<?php echo htmlentities($vo['sign_millet']); ?>"/>
                                                    <span class="input-group-addon">&nbsp&nbsp&nbsp奖励<?php echo APP_BEAN_NAME; ?>&nbsp&nbsp</span>
                                                    <input style="width: 160px" class="base_text" name="new_people_task_config[sign_bean][]" value="<?php echo htmlentities($vo['sign_bean']); ?>"/>
                                                    <button class="base_button base_button_delete" type="button" onclick="removeConsumeItem(this)">删除</button>
                                                </li>
                                            <?php endforeach; endif; else: echo "" ;endif; ?>
                                        </ul>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name"></td>
                                    <td>
                                        
                                            <button class='base_button aa' type='button' onclick="addConsumeItem()">添加一个连续签到奖励</button>
                                        
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">签到规则</td>
                                    <td>
                                        <textarea style="width:900px;height:100px;" name="new_people_task_config[rules]" ueditor><?php echo htmlentities($_info['new_people_task_config']['rules']); ?></textarea>
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <div class="layui-tab-item">
                            <table class="content_info2 mt_10">
                                <tr>
                                    <td class="field_name">是否开启</td>
                                    <td>
                                        <select class="base_select" name="new_people_task_config[is_video_status]" selectedval="<?php echo htmlentities($_info['new_people_task_config']['is_video_status']); ?>">
                                            <option value="2">开启</option>
                                            <option selected value="1">关闭</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">简要</td>
                                    <td>
                                        <input class="base_text" name="new_people_task_config[is_video_brief]" value="<?php echo htmlentities($_info['new_people_task_config']['is_video_brief']); ?>"/>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="field_name"></td>
                                    <td>
                                        <div class="base_group">
                                            <span class="base_label" style="width: 92px;text-align: center;"><?php echo APP_MILLET_NAME; ?></span>
                                            <span class="base_label" style="width: 95px;text-align: center;"><?php echo APP_BEAN_NAME; ?></span>
                                        </div>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="field_name">每日首拍奖励 <br>(审核通过发放)</td>
                                    <td>
                                        <input class="base_text" name="new_people_task_config[vedio_upload_reward][millet]" style="width: 80px;" value="<?php echo htmlentities($_info['new_people_task_config']['vedio_upload_reward']['millet']); ?>"/>
                                        <input class="base_text" name="new_people_task_config[vedio_upload_reward][bean]" style="width: 80px;" value="<?php echo htmlentities($_info['new_people_task_config']['vedio_upload_reward']['bean']); ?>"/>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="field_name">累计方式</td>
                                    <td>
                                        <select class="base_select" name="new_people_task_config[video_add_type]" selectedval="<?php echo htmlentities($_info['new_people_task_config']['video_add_type']); ?>">
                                            <option value="2">单日</option>
                                            <option selected value="1">永久</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name"></td>
                                    <td>
                                        <ul id="video_content">
                                            <?php if(is_array($video_items) || $video_items instanceof \think\Collection || $video_items instanceof \think\Paginator): $i = 0; $__LIST__ = $video_items;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
                                                <li class="recharge-item" style="padding-top: 10px">
                                                    <span class="input-group-addon">累计拍摄&nbsp&nbsp&nbsp</span>
                                                    <input style="width: 100px" class="base_text" name="new_people_task_config[video_num][]" value="<?php echo htmlentities($vo['video_num']); ?>"/>
                                                    <span class="input-group-addon">个&nbsp&nbsp&nbsp奖励<?php echo APP_MILLET_NAME; ?>&nbsp&nbsp</span>
                                                    <input style="width: 160px" class="base_text" name="new_people_task_config[video_millet][]" value="<?php echo htmlentities($vo['video_millet']); ?>"/>
                                                    <span class="input-group-addon">&nbsp&nbsp&nbsp奖励<?php echo APP_BEAN_NAME; ?>&nbsp&nbsp</span>
                                                    <input style="width: 160px" class="base_text" name="new_people_task_config[video_bean][]" value="<?php echo htmlentities($vo['video_bean']); ?>"/>
                                                    <button class="base_button base_button_delete" type="button" onclick="removeConsumeItem(this)">删除</button>
                                                </li>
                                            <?php endforeach; endif; else: echo "" ;endif; ?>
                                        </ul>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="field_name"></td>
                                    <td>
                                        <button class='base_button aa' type='button' onclick="addVideoItem()" style="margin-left: 0;">添加一个累计拍摄奖励</button>
                                    </td>
                                </tr>

                            </table>
                        </div>

                        <div class="layui-tab-item">
                            <table class="content_info2 mt_10">
                                <tr>
                                    <td class="field_name">是否开启</td>
                                    <td>
                                        <select class="base_select" name="new_people_task_config[is_watch_video_status]" selectedval="<?php echo htmlentities($_info['new_people_task_config']['is_watch_video_status']); ?>">
                                            <option value="2">开启</option>
                                            <option selected value="1">关闭</option>
                                        </select>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="field_name">简要</td>
                                    <td>
                                        <input class="base_text" name="new_people_task_config[is_watch_video_brief]" value="<?php echo htmlentities($_info['new_people_task_config']['is_watch_video_brief']); ?>"/>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="field_name"></td>
                                    <td>
                                        <ul id="watch_video_content">
                                            <?php if(is_array($watch_video_items) || $watch_video_items instanceof \think\Collection || $watch_video_items instanceof \think\Paginator): $i = 0; $__LIST__ = $watch_video_items;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
                                                <li class="recharge-item" style="padding-top: 10px">
                                                    <span class="input-group-addon">累计观看&nbsp&nbsp&nbsp</span>
                                                    <input style="width: 100px" class="base_text" name="new_people_task_config[watch_video_num][]" value="<?php echo htmlentities($vo['watch_video_num']); ?>"/>
                                                    <span class="input-group-addon">分钟&nbsp&nbsp&nbsp奖励<?php echo APP_MILLET_NAME; ?>&nbsp&nbsp</span>
                                                    <input style="width: 160px" class="base_text" name="new_people_task_config[watch_video_millet][]" value="<?php echo htmlentities($vo['watch_video_millet']); ?>"/>
                                                    <span class="input-group-addon">&nbsp&nbsp&nbsp奖励<?php echo APP_BEAN_NAME; ?>&nbsp&nbsp</span>
                                                    <input style="width: 160px" class="base_text" name="new_people_task_config[watch_video_bean][]" value="<?php echo htmlentities($vo['watch_video_bean']); ?>"/>
                                                    <button class="base_button base_button_delete" type="button" onclick="removeConsumeItem(this)">删除</button>
                                                </li>
                                            <?php endforeach; endif; else: echo "" ;endif; ?>
                                        </ul>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="field_name"></td>
                                    <td>
                                        <button class='base_button aa' type='button' onclick="addWatchVideoItem()" style="margin-left: 0;">添加一个时段</button>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="layui-tab-item">
                            <table class="content_info2 mt_10">

                                <tr>
                                    <td class="field_name"></td>
                                    <td>
                                        <div class="base_group">
                                            <span class="base_label" style="width: 92px;text-align: center;">是否开启</span>
                                            <span class="base_label" style="width: 92px;text-align: center;">关注数量</span>
                                            <span class="base_label" style="width: 95px;text-align: center;">奖励金额</span>
                                            <span class="base_label" style="width: 215px;text-align: center;">显示标题</span>
                                            <span class="base_label" style="width: 120px;text-align: center;">任务类型</span>

                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">关注好友</td>
                                    <td>
                                        <select class="base_select"  style="width: 92px;text-align: center;"  name="new_people_task_config[followFriends][status]" selectedval="<?php echo !empty($_info['new_people_task_config']['followFriends']['status']) ? '1'  :  '0'; ?>">
                                            <option value="1">启用</option>
                                            <option value="0">禁用</option>
                                        </select>
                                        <input class="base_text" name="new_people_task_config[followFriends][follow]" style="width: 80px;" value="<?php echo htmlentities($_info['new_people_task_config']['followFriends']['follow']); ?>" />
                                        <input class="base_text" name="new_people_task_config[followFriends][money]" style="width: 95px;" value="<?php echo htmlentities($_info['new_people_task_config']['followFriends']['money']); ?>"/>
                                        <input class="base_text" name="new_people_task_config[followFriends][title]" style="width: 208px;" value="<?php echo htmlentities((isset($_info['new_people_task_config']['followFriends']['title']) && ($_info['new_people_task_config']['followFriends']['title'] !== '')?$_info['new_people_task_config']['followFriends']['title']:'关注数量')); ?>"/>
                                        <select class="base_select"  style="width: 92px;text-align: center;" name="new_people_task_config[followFriends][type]" selectedval="<?php echo !empty($_info['new_people_task_config']['followFriends']['type']) ? '1'  :  '0'; ?>">
                                            <option value="1">循环任务</option>
                                            <option value="0">一次任务</option>
                                        </select>

                                        <input class="base_text" hidden name="new_people_task_config[followFriends][task_type]" style="width: 208px;" value="<?php echo htmlentities((isset($_info['new_people_task_config']['followFriends']['task_type']) && ($_info['new_people_task_config']['followFriends']['task_type'] !== '')?$_info['new_people_task_config']['followFriends']['task_type']:'followFriends')); ?>"/>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="field_name"></td>
                                    <td>
                                        <div class="base_group">
                                            <span class="base_label" style="width: 92px;text-align: center;">是否开启</span>
                                            <span class="base_label" style="width: 92px;text-align: center;">发布数量</span>
                                            <span class="base_label" style="width: 95px;text-align: center;">奖励金额</span>
                                            <span class="base_label" style="width: 215px;text-align: center;">显示标题</span>
                                            <span class="base_label" style="width: 120px;text-align: center;">任务类型</span>


                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">发布视频</td>
                                    <td>
                                        <select class="base_select"  style="width: 92px;text-align: center;" name="new_people_task_config[postVideo][status]" selectedval="<?php echo !empty($_info['new_people_task_config']['postVideo']['status']) ? '1'  :  '0'; ?>">
                                            <option value="1">启用</option>
                                            <option value="0">禁用</option>
                                        </select>
                                        <input class="base_text" name="new_people_task_config[postVideo][attention]" style="width: 80px;" value="<?php echo htmlentities((isset($_info['new_people_task_config']['postVideo']['attention']) && ($_info['new_people_task_config']['postVideo']['attention'] !== '')?$_info['new_people_task_config']['postVideo']['attention']:1)); ?>" readonly/>
                                        <input class="base_text" name="new_people_task_config[postVideo][money]" style="width: 95px;" value="<?php echo htmlentities($_info['new_people_task_config']['postVideo']['money']); ?>"/>
                                        <input class="base_text" name="new_people_task_config[postVideo][title]" style="width: 208px;" value="<?php echo htmlentities((isset($_info['new_people_task_config']['postVideo']['title']) && ($_info['new_people_task_config']['postVideo']['title'] !== '')?$_info['new_people_task_config']['postVideo']['title']:'发布视频')); ?>"/>
                                        <select class="base_select"  style="width: 92px;text-align: center;" name="new_people_task_config[postVideo][type]" selectedval="<?php echo !empty($_info['new_people_task_config']['postVideo']['type']) ? '1'  :  '0'; ?>">
                                            <option value="1">循环任务</option>
                                            <option value="0">一次任务</option>
                                        </select>

                                        <input class="base_text"  hidden name="new_people_task_config[postVideo][task_type]" style="width: 208px;" value="<?php echo htmlentities((isset($_info['new_people_task_config']['postVideo']['task_type']) && ($_info['new_people_task_config']['postVideo']['task_type'] !== '')?$_info['new_people_task_config']['postVideo']['task_type']:'postVideo')); ?>"/>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="field_name"></td>
                                    <td>
                                        <div class="base_group">
                                            <span class="base_label" style="width: 92px;text-align: center;">是否开启</span>
                                            <span class="base_label" style="width: 92px;text-align: center;">观看时长</span>
                                            <span class="base_label" style="width: 95px;text-align: center;">奖励金额</span>
                                            <span class="base_label" style="width: 215px;text-align: center;">显示标题</span>
                                            <span class="base_label" style="width: 120px;text-align: center;">任务类型</span>

                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">观看视频</td>
                                    <td>
                                        <select class="base_select"  style="width: 92px;text-align: center;" name="new_people_task_config[watchVideo][status]" selectedval="<?php echo !empty($_info['new_people_task_config']['watchVideo']['status']) ? '1'  :  '0'; ?>">
                                            <option value="1">启用</option>
                                            <option value="0">禁用</option>
                                        </select>
                                        <input class="base_text" name="new_people_task_config[watchVideo][seenum]" style="width: 80px;" value="<?php echo htmlentities($_info['new_people_task_config']['watchVideo']['seenum']); ?>"/>
                                        <input class="base_text" name="new_people_task_config[watchVideo][money]" style="width: 95px;" value="<?php echo htmlentities($_info['new_people_task_config']['watchVideo']['money']); ?>"/>
                                        <input class="base_text" name="new_people_task_config[watchVideo][title]" style="width: 208px;" value="<?php echo htmlentities((isset($_info['new_people_task_config']['watchVideo']['title']) && ($_info['new_people_task_config']['watchVideo']['title'] !== '')?$_info['new_people_task_config']['watchVideo']['title']:'观看视频')); ?>"/>
                                        <select class="base_select"  style="width: 92px;text-align: center;" name="new_people_task_config[watchVideo][type]" selectedval="<?php echo !empty($_info['new_people_task_config']['watchVideo']['type']) ? '1'  :  '0'; ?>">
                                            <option value="1">循环任务</option>
                                            <option value="0">一次任务</option>
                                        </select>

                                        <input class="base_text" hidden  name="new_people_task_config[watchVideo][task_type]" style="width: 208px;" value="<?php echo htmlentities((isset($_info['new_people_task_config']['watchVideo']['task_type']) && ($_info['new_people_task_config']['watchVideo']['task_type'] !== '')?$_info['new_people_task_config']['watchVideo']['task_type']:'watchVideo')); ?>"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name"></td>
                                    <td>
                                        <div class="base_group">
                                            <span class="base_label" style="width: 92px;text-align: center;">是否开启</span>
                                            <span class="base_label" style="width: 92px;text-align: center;">分享数量</span>
                                            <span class="base_label" style="width: 95px;text-align: center;">奖励金额</span>
                                            <span class="base_label" style="width: 215px;text-align: center;">显示标题</span>
                                            <span class="base_label" style="width: 120px;text-align: center;">任务类型</span>

                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">分享视频</td>
                                    <td>
                                        <select class="base_select"  style="width: 92px;text-align: center;" name="new_people_task_config[shareVideo][status]" selectedval="<?php echo !empty($_info['new_people_task_config']['shareVideo']['status']) ? '1'  :  '0'; ?>">
                                            <option value="1">启用</option>
                                            <option value="0">禁用</option>
                                        </select>
                                        <input class="base_text" name="new_people_task_config[shareVideo][sharenum]" style="width: 80px;" value="<?php echo htmlentities($_info['new_people_task_config']['shareVideo']['sharenum']); ?>"/>
                                        <input class="base_text" name="new_people_task_config[shareVideo][money]" style="width: 95px;" value="<?php echo htmlentities($_info['new_people_task_config']['shareVideo']['money']); ?>"/>
                                        <input class="base_text" name="new_people_task_config[shareVideo][title]" style="width: 208px;" value="<?php echo htmlentities((isset($_info['new_people_task_config']['shareVideo']['title']) && ($_info['new_people_task_config']['shareVideo']['title'] !== '')?$_info['new_people_task_config']['shareVideo']['title']:'分享视频')); ?>"/>
                                        <select class="base_select"  style="width: 92px;text-align: center;" name="new_people_task_config[shareVideo][type]" selectedval="<?php echo !empty($_info['new_people_task_config']['shareVideo']['type']) ? '1'  :  '0'; ?>">
                                            <option value="1">循环任务</option>
                                            <option value="0">一次任务</option>
                                        </select>

                                        <input class="base_text"  hidden name="new_people_task_config[shareVideo][task_type]" style="width: 208px;" value="<?php echo htmlentities((isset($_info['new_people_task_config']['shareVideo']['task_type']) && ($_info['new_people_task_config']['shareVideo']['task_type'] !== '')?$_info['new_people_task_config']['shareVideo']['task_type']:'shareVideo')); ?>"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name"></td>
                                    <td>
                                        <div class="base_group">
                                            <span class="base_label" style="width: 92px;text-align: center;">是否开启</span>
                                            <span class="base_label" style="width: 92px;text-align: center;">签到次数</span>
                                            <span class="base_label" style="width: 95px;text-align: center;">多重奖励（','分割)</span>
                                            <span class="base_label" style="width: 215px;text-align: center;">显示标题</span>
                                            <span class="base_label" style="width: 120px;text-align: center;">任务类型</span>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">签到任务</td>
                                    <td>
                                        <select class="base_select"  style="width: 92px;text-align: center;" name="new_people_task_config[dailyLogin][status]" selectedval="<?php echo !empty($_info['new_people_task_config']['dailyLogin']['status']) ? '1'  :  '0'; ?>">
                                            <option value="1">启用</option>
                                            <option value="0">禁用</option>
                                        </select>
                                        <input class="base_text" name="new_people_task_config[dailyLogin][sign]" style="width: 80px;" value="<?php echo htmlentities((isset($_info['new_people_task_config']['dailyLogin']['sign']) && ($_info['new_people_task_config']['dailyLogin']['sign'] !== '')?$_info['new_people_task_config']['dailyLogin']['sign']:1)); ?>"  readonly/>
                                        <input class="base_text" name="new_people_task_config[dailyLogin][Rewards]" style="width: 95px;" value="<?php echo htmlentities($_info['new_people_task_config']['dailyLogin']['Rewards']); ?>"/>
                                        <input class="base_text" name="new_people_task_config[dailyLogin][title]" style="width: 208px;" value="<?php echo htmlentities((isset($_info['new_people_task_config']['dailyLogin']['title']) && ($_info['new_people_task_config']['dailyLogin']['title'] !== '')?$_info['new_people_task_config']['dailyLogin']['title']:'每日登录')); ?>"/>
                                        <select class="base_select"  style="width: 92px;text-align: center;" name="new_people_task_config[dailyLogin][type]" selectedval="<?php echo !empty($_info['new_people_task_config']['dailyLogin']['type']) ? '1'  :  '0'; ?>">
                                            <option value="1">循环任务</option>
                                            <option value="0">一次任务</option>
                                        </select>
                                        <input class="base_text" hidden name="new_people_task_config[dailyLogin][task_type]" style="width: 208px;" value="<?php echo htmlentities((isset($_info['new_people_task_config']['dailyLogin']['task_type']) && ($_info['new_people_task_config']['dailyLogin']['task_type'] !== '')?$_info['new_people_task_config']['dailyLogin']['task_type']:'dailyLogin')); ?>"/>
                                    </td>
                                </tr>

                            </table>
                        </div>
                    </div>


                </div>
                <div class="base_button_div p_b_20" style="max-width: none;width: 1058px;">
                    <a href="javascript:;" class="base_button" ajax="post">提交</a>
                </div>
            </form>
        </div>
    </div>

    <script charset="utf-8" src="<?php echo ('/static/'.strtolower(\think\facade\Request::module()).'/js'); ?>/ueditor.config.js?v=<?php echo config('upload.resource_version'); ?>" type="text/javascript"></script>
    <script src="/static/vendor/ueditor/ueditor.all.min.js?v=<?php echo config('upload.resource_version'); ?>"></script>
    <script>

        function addConsumeItem() {
            var html='<li class="recharge-item" style="padding-top: 10px">';
            html+='<span class="input-group-addon">连续签到&nbsp&nbsp&nbsp</span>';
            html+='<input style="width: 100px" class="base_text" name="new_people_task_config[sign_day][]" value=""/>';
            html+=' <span class="input-group-addon">天&nbsp&nbsp&nbsp奖励<?php echo APP_MILLET_NAME; ?>&nbsp&nbsp</span>';
            html+='<input style="width: 160px" class="base_text" name="new_people_task_config[sign_millet][]" value=""/>';
            html+=' <span class="input-group-addon">&nbsp&nbsp&nbsp奖励<?php echo APP_BEAN_NAME; ?>&nbsp&nbsp</span>';
            html+='<input style="width: 160px" class="base_text" name="new_people_task_config[sign_bean][]" value=""/>';
            html+=' <button class="base_button base_button_delete" type="button" onclick="removeConsumeItem(this)">删除</button>';
            html+='</li>';
            $('#content').append(html);
        }

        function removeConsumeItem(obj){
            $(obj).closest('.recharge-item').remove();
        }
        
        function addVideoItem() {
            var html='<li class="recharge-item" style="padding-top: 10px">';
            html+='<span class="input-group-addon">累计拍摄&nbsp&nbsp&nbsp</span>';
            html+='<input style="width: 100px" class="base_text" name="new_people_task_config[video_num][]" value=""/>';
            html+=' <span class="input-group-addon">个&nbsp&nbsp&nbsp奖励<?php echo APP_MILLET_NAME; ?>&nbsp&nbsp</span>';
            html+='<input style="width: 160px" class="base_text" name="new_people_task_config[video_millet][]" value=""/>';
            html+=' <span class="input-group-addon">&nbsp&nbsp&nbsp奖励<?php echo APP_BEAN_NAME; ?>&nbsp&nbsp</span>';
            html+='<input style="width: 160px" class="base_text" name="new_people_task_config[video_bean][]" value=""/>';
            html+=' <button class="base_button base_button_delete" type="button" onclick="removeConsumeItem(this)">删除</button>';
            html+='</li>';
            $('#video_content').append(html);
        }
        function addWatchVideoItem() {
            var html='<li class="recharge-item" style="padding-top: 10px">';
            html+='<span class="input-group-addon">累计观看&nbsp&nbsp&nbsp</span>';
            html+='<input style="width: 100px" class="base_text" name="new_people_task_config[watch_video_num][]" value=""/>';
            html+=' <span class="input-group-addon">分钟&nbsp&nbsp&nbsp奖励<?php echo APP_MILLET_NAME; ?>&nbsp&nbsp</span>';
            html+='<input style="width: 160px" class="base_text" name="new_people_task_config[watch_video_millet][]" value=""/>';
            html+=' <span class="input-group-addon">&nbsp&nbsp&nbsp奖励<?php echo APP_BEAN_NAME; ?>&nbsp&nbsp</span>';
            html+='<input style="width: 160px" class="base_text" name="new_people_task_config[watch_video_bean][]" value=""/>';
            html+=' <button class="base_button base_button_delete" type="button" onclick="removeConsumeItem(this)">删除</button>';
            html+='</li>';
            $('#watch_video_content').append(html);
        }


    </script>

        </div>
    </div>
</div>

<div dom-key="work_box" popbox="work_box" class="work_box layer_box" title="任务设置"
           popbox-action="<?php echo url('/admin/personal/work'); ?>" popbox-get-data="<?php echo url('/admin/personal/work'); ?>" popbox-area="640px,450px">
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
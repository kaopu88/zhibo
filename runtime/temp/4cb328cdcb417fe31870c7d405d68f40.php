<?php /*a:13:{s:62:"/www/wwwroot/zhibb/application/admin/view/video/audit_list.tpl";i:1592881236;s:61:"/www/wwwroot/zhibb/application/admin/view/public/base_nav.tpl";i:1693212818;s:61:"/www/wwwroot/zhibb/application/admin/view/public/jsconfig.tpl";i:1602499828;s:61:"/www/wwwroot/zhibb/application/admin/view/public/main_top.tpl";i:1593518960;s:59:"/www/wwwroot/zhibb/application/admin/view/public/toggle.tpl";i:1592625950;s:64:"/www/wwwroot/zhibb/application/admin/view/components/tab_nav.tpl";i:1592356812;s:62:"/www/wwwroot/zhibb/application/admin/view/video/video_info.tpl";i:1592625950;s:58:"/www/wwwroot/zhibb/application/admin/view/video/source.tpl";i:1592625950;s:64:"/www/wwwroot/zhibb/application/admin/view/components/vo_user.tpl";i:1567562304;s:72:"/www/wwwroot/zhibb/application/admin/view/components/vo_audit_status.tpl";i:1567562304;s:65:"/www/wwwroot/zhibb/application/admin/view/components/work_pop.tpl";i:1567562304;s:70:"/www/wwwroot/zhibb/application/admin/view/video/film_audit_handler.tpl";i:1618972150;s:74:"/www/wwwroot/zhibb/application/admin/view/components/task_transfer_box.tpl";i:1592645870;}*/ ?>
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
        .film_audit_handler .label_ul {
            display: flex;
            flex-wrap: wrap;
            box-sizing: border-box;
        }

        .film_audit_handler .label_ul * {
            box-sizing: border-box;
        }

        .film_audit_handler .label_ul .label_li {
            flex: 0 1 auto;
            display: block;
            line-height: 25px;
            padding: 2px 5px;
            border: solid 1px #DCDCDC;
            border-radius: 5px;
            margin: 5px;
        }

        .film_audit_handler .label_ul .label_li .icon-remove {
            margin-left: 5px;
            display: inline-block;
            cursor: pointer;
        }

        .film_audit_handler .label_ul .label_li .icon-remove:hover {
            color: #ed0202;
        }

        .film_audit_handler .label_ul .label_plus {
            border: none;
            font-size: 18px;
            cursor: pointer;
        }

        .film_audit_handler .label_ul .label_plus:hover {
            color: #1D9DFD;
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
    <script src="/static/vendor/raty/jquery.raty.min.js?v=<?php echo config('upload.resource_version'); ?>"></script>
    <script src="<?php echo ('/static/'.strtolower(\think\facade\Request::module()).'/js'); ?>/video/audit.js?v=<?php echo config('upload.resource_version'); ?>"></script>

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


            
    <div class="pa_20 p-0">
        <ul class="tab_nav">
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
        <div class="table_slide bg_container">
            <table class="content_list mt_10 audit_list md_width">
                <thead>
                <tr>
                    <td style="width: 5%;"><input type="checkbox" checkall="list_id"/></td>
                    <td style="width: 5%;">ID</td>
                    <td style="width: 11%;">视频封面</td>
                    <td style="width: 11%;">视频描述</td>
                    <td style="width: 9%;">视频属性</td>
                    <td style="width: 8%;">商品</td>
                    <td style="width: 10%;">发布用户</td>
                    <td style="width: 10%;">标签</td>
                    <td style="width: 10%;">审核状态</td>
                    <td style="width: 10%;">申请时间</td>
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
    <a layer-title="0" layer-area="414px,779px"
       layer-open="<?php echo url('video/tcplayer',['id'=>$vo['id']]); ?>" href="javascript:;"
       class="thumb_img" style="display: inline-block;max-width: 100px;">
        <img src="<?php echo img_url($vo['animate_url']?$vo['animate_url']:$vo['cover_url'],'120_68','film_cover'); ?>"/>
    </a>
</div>
                            </td>
                            <td>
                                <?php if(!(empty($vo['city_name']) || (($vo['city_name'] instanceof \think\Collection || $vo['city_name'] instanceof \think\Paginator ) && $vo['city_name']->isEmpty()))): ?>
                                    <span class="fc_orange">【<?php echo htmlentities($vo['city_name']); ?>】</span>
                                <?php endif; ?>
                                <?php echo htmlentities((isset($vo['describe']) && ($vo['describe'] !== '')?$vo['describe']:'未填写')); ?>
                            </td>
                            <td>
                                宽高：<?php echo htmlentities($vo['width']); ?>*<?php echo htmlentities($vo['height']); ?><br/>
                                大小：<?php echo htmlentities($vo['file_size_str']); ?><br/>
                                来源：
                                <?php switch($vo['source']): case "user": ?>用户<?php break; case "erp": ?>后台<?php break; endswitch; ?>
                                <br/>
                                版权：
                                <?php if($vo['copy_right'] == '0'): ?>
                                    <span class="fc_green">无标识</span>
                                    <?php else: ?>
                                    <span class="fc_red">有标识</span>
                                <?php endif; ?>
                                <br/>
                                人评：<?php echo htmlentities($vo['rating']); ?><br/>
                                总分：<?php echo htmlentities($vo['score']); ?>
                            </td>
                            <td>
                                <?php if(!(empty($vo['goods']) || (($vo['goods'] instanceof \think\Collection || $vo['goods'] instanceof \think\Paginator ) && $vo['goods']->isEmpty()))): ?>
                                    <img src="<?php echo htmlentities($vo['goods']['img']); ?>" style="width: 80px;height: 80px"/>
                                    <Br>
                                    <?php echo htmlentities($vo['goods']['short_title']); else: ?>
                                    暂无
                                <?php endif; ?>

                            </td>
                            <td>
                                <div class="thumb">
    <a href="<?php echo url('user/detail',['user_id'=>$vo['user']['user_id']]); ?>" class="thumb_img thumb_img_avatar">
        <img src="<?php echo img_url($vo['user']['avatar'],'200_200','avatar'); ?>"/>
        <div class="thumb_level_box">
            <img title="<?php echo htmlentities($vo['user']['level_name']); ?>" src="<?php echo htmlentities($vo['user']['level_icon']); ?>"/>
        </div>
    </a>
    <p class="thumb_info">
        <a href="<?php echo url('user/detail',['user_id'=>$vo['user']['user_id']]); ?>">
            <?php echo htmlentities(user_name($vo['user'])); ?><br/>
            <?php echo htmlentities((str_hide($vo['user']['phone'],3,4) ?: '未绑定')); ?>
        </a>
    </p>
</div>
                            </td>
                            <td>
                                <?php if(!empty($vo['location_lng']) AND !empty($vo['location_lat'])): ?>
                                    位置：<?php echo htmlentities(short($vo['location_name'],20)); ?>
                                    <a class="check_location" location-name="发布位置" location-lng="<?php echo htmlentities($vo['location_lng']); ?>"
                                    location-lat="<?php echo htmlentities($vo['location_lat']); ?>" href="javascript:;"><span
                                            class="icon-location2"></span> </a>
                                <?php endif; ?>
                                <br/>
                                标签：<?php echo htmlentities($vo['tag_names']); ?>
                            </td>
                            <td>
                                <?php switch($vo['audit_status']): case "0": ?>
        <span class="fc_gray">处理中</span>
    <?php break; case "1": ?>
        <span class="fc_black">审核中</span>
    <?php break; case "2": ?>
        <span class="fc_green">已通过</span>
    <?php break; case "3": ?>
        <span class="fc_red">未通过</span>
        <?php if(!(empty($vo['reason']) || (($vo['reason'] instanceof \think\Collection || $vo['reason'] instanceof \think\Paginator ) && $vo['reason']->isEmpty()))): ?>
            <a class="video_reason" data-reason="<?php echo htmlentities(htmlspecialchars($vo['reason'])); ?>" data-id="<?php echo htmlentities($vo['id']); ?>" href="javascript:;">原因</a>
        <?php endif; break; endswitch; ?>
                            </td>
                            <td>
                                申请：<?php echo htmlentities(time_format($vo['create_time'])); ?><br/>
                                处理：<?php echo htmlentities(time_format($vo['audit_time'],'未处理')); ?>
                            </td>
                            <td>
                                <?php switch($vo['audit_status']): case "0": break; case "1": if(check_auth('admin:film:audit',AUTH_UID)): ?>
                                            <a data-id="id:<?php echo htmlentities($vo['id']); ?>" poplink="film_audit_handler"
                                            href="javascript:;" class="repair_font">审核</a>
                                        <?php endif; ?>
                                        <br/>
                                        <a data-query="id=<?php echo htmlentities($vo['id']); ?>&type=audit_film" poplink="task_transfer_box"
                                        href="javascript:;" class="optimize_font">转交</a>
                                    <?php break; case "2": ?>
                                        原因：<?php echo htmlentities($vo['reason']); break; endswitch; ?>
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

    <div title="短视频审核" class="layer_box film_audit_handler pa_10" dom-key="film_audit_handler"
     popbox-action="<?php echo url('video/audit'); ?>" popbox-get-data="<?php echo url('video/audit'); ?>" popbox-area="800px,700px">
    <table class="content_info2">
        <tr>
            <td class="tcplayer" colspan="2"></td>
        </tr>
        <tr>
            <td class="field_name">审核状态</td>
            <td>
                <label class="base_label2"><input name="audit_status" value="2" type="radio"/>通过</label>
                <label class="base_label2"><input name="audit_status" value="3" type="radio"/>驳回</label>
                <label class="base_label2"><input name="audit_status" value="13" type="radio"/>驳回并删除</label>
            </td>
        </tr>
        <tr class="reason_tr">
            <td class="field_name">驳回原因</td>
            <td>
                <textarea name="reason" style="height: 100px;" class="base_text"></textarea>
                <div class="mt_5">
                    <a class="reason_link" href="javascript:;">内容违规，可能有涉黄信息或者低俗信息</a><br/>
                    <a class="reason_link" href="javascript:;">内容违规，可能有涉暴恐信息</a><br/>
                    <a class="reason_link" href="javascript:;">内容违规，可能有涉政治敏感信息</a><br/>
                    <a class="reason_link" href="javascript:;">内容违规，侵犯他人版权</a><br/>
                    <a class="reason_link" href="javascript:;">内容违规，可能有违规广告或者谣言信息</a><br/>
                    <a class="reason_link" href="javascript:;">视频内容不符合平台规范</a><br/>
                    <a class="reason_link" href="javascript:;">视频画面模糊不清等原因、质量不符合平台标准</a><br/>
                    <a class="reason_link" href="javascript:;">请勿重复上传视频</a>
                </div>
            </td>
        </tr>
        <tr class="edit_tr">
            <td class="field_name">视频描述</td>
            <td>
                <textarea name="describe" style="height: 70px;" class="base_text"></textarea>
            </td>
        </tr>
        <tr class="edit_tr">
            <td class="field_name">话题列表</td>
            <td>
                <ul class="label_ul">
                    <li class="label_li">
                    </li>
                </ul>
            </td>
        </tr>
        <tr class="edit_tr">
            <td class="field_name">标签列表</td>
            <td>
                <ul class="label_ul">
                    <li class="label_li">
                    </li>
                </ul>
            </td>
        </tr>
        <tr class="edit_tr">
            <td class="field_name">版权标识</td>
            <td>
                <label class="base_label2"><input name="copy_right" value="1" type="radio"/>有标识</label>
                <label class="base_label2"><input name="copy_right" value="0" type="radio"/>无标识</label>
                <div class="field_tip">是否有第三方平台的水印LOGO或者包含侵权内容</div>
            </td>
        </tr>
        <tr class="edit_tr">
            <td class="field_name">视频备注</td>
            <td><input class="base_text" name="source" readonly value=""/></td>
        </tr>
        <tr class="edit_tr">
            <td class="field_name">作者信息</td>
            <td><input class="base_text" name="author" readonly value=""/></td>
        </tr>
        <tr class="edit_tr">
            <td class="field_name">视频评分</td>
            <td>
                <div class="star_box"></div>
                <div>
                    <span class="star_tip">没有评分</span>
                </div>
            </td>
        </tr>

        <tr>
            <td class="field_name"></td>
            <td>
                <input type="hidden" name="id" value=""/>
                <div class="flex_end">
                    <div data-next="0" class="base_button sub_btn2 mt_10" style="margin-right:10px;">提交</div>
                    <div data-next="1" class="base_button base_button_orange sub_btn2 mt_10" style="margin-left: 10px;">
                        提交并审核下一个
                    </div>
                </div>
            </td>
        </tr>
    </table>
    <div style="height: 30px"></div>
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
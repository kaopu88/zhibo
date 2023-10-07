<?php /*a:7:{s:63:"/www/wwwroot/zhibb/application/admin/view/sys_config/upload.tpl";i:1692782960;s:61:"/www/wwwroot/zhibb/application/admin/view/public/base_nav.tpl";i:1693212818;s:61:"/www/wwwroot/zhibb/application/admin/view/public/jsconfig.tpl";i:1602499828;s:61:"/www/wwwroot/zhibb/application/admin/view/public/main_top.tpl";i:1593518960;s:59:"/www/wwwroot/zhibb/application/admin/view/public/toggle.tpl";i:1592625950;s:64:"/www/wwwroot/zhibb/application/admin/view/components/tab_nav.tpl";i:1592356812;s:65:"/www/wwwroot/zhibb/application/admin/view/components/work_pop.tpl";i:1567562304;}*/ ?>
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


            
    <div class="pa_20 p-0 bg_normal">
        <style>
            .default_img{
                float: left;
            }
            .default_img>img{
                width: 35px;
                margin-left: 9px;
            }
        </style>
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
        <div class="bg_form">
            <form action="<?php echo url('upload'); ?>">

                <div class="layui-tab layui-tab-brief" lay-filter="docDemoTabBrief">
                    <ul class="layui-tab-title">
                        <li class="layui-this">上传设置</li>
                        <li>服务配置</li>
                        <li>默认图片</li>
                        <!--<li>提现配置</li>-->
                    </ul>
                    <div class="layui-tab-content">

                        <div class="layui-tab-item layui-show">
                            <table class="content_info2 mt_10">
                                <tr>
                                    <td class="field_name">存储服务商</td>
                                    <td>
                                        <select class="base_select" name="platform" selectedval="<?php echo htmlentities($_info['platform']); ?>">
                                            <option value="qiniu">七牛云</option>
                                            <option value="aliyun">阿里云</option>
                                            <option value="wsyun">网宿云</option>
                                        </select>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="field_name">静态资源CDN</td>
                                    <td>
                                        <input class="base_text" name="resource_cdn" value="<?php echo htmlentities($_info['resource_cdn']); ?>"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">静态资源版本</td>
                                    <td>
                                        <input class="base_text" name="resource_version" value="<?php echo htmlentities($_info['resource_version']); ?>"/>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="field_name">上传最小限制</td>
                                    <td>
                                        <input class="base_text" name="upload_config[_default][fsizeMin]" value="<?php echo htmlentities($_info['upload_config']['_default']['fsizeMin']); ?>"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">上传最大限制</td>
                                    <td>
                                        <input class="base_text" name="upload_config[_default][fsizeLimit]" value="<?php echo htmlentities($_info['upload_config']['_default']['fsizeLimit']); ?>"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">类型限制</td>
                                    <td>
                                        <input class="base_text" name="upload_config[_default][mimeLimit]" value="<?php echo htmlentities($_info['upload_config']['_default']['mimeLimit']); ?>"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">文件后缀名</td>
                                    <td>
                                        <input class="base_text" name="upload_config[_default][allowExts]" value="<?php echo htmlentities($_info['upload_config']['_default']['allowExts']); ?>"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">保存路径规则</td>
                                    <td>
                                        <input class="base_text" name="upload_config[_default][path]" value="<?php echo htmlentities($_info['upload_config']['_default']['path']); ?>"/>
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <div class="layui-tab-item">
                            <table class="content_info2 mt_10">
                                <tr>
                                    <td class="field_name">应用Access_key</td>
                                    <td>
                                        <input class="base_text" name="platform_config[access_key]" value="<?php echo htmlentities($_info['platform_config']['access_key']); ?>"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">应用Secret_key</td>
                                    <td>
                                        <input class="base_text" name="platform_config[secret_key]" value="<?php echo htmlentities($_info['platform_config']['secret_key']); ?>"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">应用bucket</td>
                                    <td>
                                        <input class="base_text" name="platform_config[bucket]" value="<?php echo htmlentities($_info['platform_config']['bucket']); ?>"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">应用Root_path</td>
                                    <td>
                                        <input class="base_text" name="platform_config[root_path]" value="<?php echo htmlentities($_info['platform_config']['root_path']); ?>"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">应用Base_url组</td>
                                    <td>
                                        <input class="base_text" name="platform_config[base_url]" value="<?php echo htmlentities($_info['platform_config']['base_url']); ?>"/>
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <div class="layui-tab-item">
                            <div class="content_title2">默认注册头像</div>
                            <table class="content_info2 mt_10">
                                <tr>
                                    <td class="field_name">注册头像1</td>
                                    <td>
                                        <input class="base_text default_img" name="reg_avatar[0]" value="<?php echo htmlentities($_info['reg_avatar'][0]); ?>"/>
                                        <div class="default_img">
                                            <img src="<?php echo htmlentities($_info['reg_avatar'][0]); ?>"/>

                                            <a style="margin-left: 0" uploader-size="2147483648" uploader-type="image" href="javascript:;"
                                            class="base_button"
                                            uploader="reg_avatar"
                                            uploader-field="reg_avatar[0]">上传
                                            </a>

                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">注册头像2</td>
                                    <td>
                                        <input class="base_text default_img" name="reg_avatar[1]" value="<?php echo htmlentities($_info['reg_avatar'][1]); ?>"/>
                                        <div class="default_img">
                                            <img src="<?php echo htmlentities($_info['reg_avatar'][1]); ?>"/>

                                            <a style="margin-left: 0" uploader-size="2147483648" uploader-type="image" href="javascript:;"
                                            class="base_button"
                                            uploader="reg_avatar"
                                            uploader-field="reg_avatar[1]">上传
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">注册头像3</td>
                                    <td>
                                        <input class="base_text default_img" name="reg_avatar[2]" value="<?php echo htmlentities($_info['reg_avatar'][2]); ?>"/>
                                        <div class="default_img">
                                            <img src="<?php echo htmlentities($_info['reg_avatar'][2]); ?>"/>

                                            <a style="margin-left: 0" uploader-size="2147483648" uploader-type="image" href="javascript:;"
                                            class="base_button"
                                            uploader="reg_avatar"
                                            uploader-field="reg_avatar[2]">上传
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">注册头像4</td>
                                    <td>
                                        <input class="base_text default_img" name="reg_avatar[3]" value="<?php echo htmlentities($_info['reg_avatar'][3]); ?>"/>
                                        <div class="default_img">
                                            <img src="<?php echo htmlentities($_info['reg_avatar'][3]); ?>"/>

                                            <a style="margin-left: 0" uploader-size="2147483648" uploader-type="image" href="javascript:;"
                                            class="base_button"
                                            uploader="reg_avatar"
                                            uploader-field="reg_avatar[3]">上传
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                            <div class="content_title2">默认资源图片</div>
                            <table class="content_info2 mt_10">
                                <tr>
                                    <td class="field_name">通用默认头像</td>
                                    <td>
                                        <input class="base_text default_img" name="image_defaults[avatar]" value="<?php echo htmlentities($_info['image_defaults']['avatar']); ?>"/>
                                        <div class="default_img">
                                            <img src="<?php echo htmlentities($_info['image_defaults']['avatar']); ?>"/>

                                            <a style="margin-left: 0" uploader-size="2147483648" uploader-type="image" href="javascript:;"
                                            class="base_button"
                                            uploader="image_defaults"
                                            uploader-field="image_defaults[avatar]">上传
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">小助手头像</td>
                                    <td>
                                        <input class="base_text default_img" name="image_defaults[helper_avatar]" value="<?php echo htmlentities($_info['image_defaults']['helper_avatar']); ?>"/>
                                        <div class="default_img">
                                            <img src="<?php echo htmlentities($_info['image_defaults']['helper_avatar']); ?>"/>

                                            <a style="margin-left: 0" uploader-size="2147483648" uploader-type="image" href="javascript:;"
                                            class="base_button"
                                            uploader="image_defaults"
                                            uploader-field="image_defaults[helper_avatar]">上传
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">官方通知头像</td>
                                    <td>
                                        <input class="base_text default_img" name="image_defaults[official_avatar]" value="<?php echo htmlentities($_info['image_defaults']['official_avatar']); ?>"/>
                                        <div class="default_img">
                                            <img src="<?php echo htmlentities($_info['image_defaults']['official_avatar']); ?>"/>
                                            <a style="margin-left: 0" uploader-size="2147483648" uploader-type="image" href="javascript:;"
                                            class="base_button"
                                            uploader="image_defaults"
                                            uploader-field="image_defaults[official_avatar]">上传
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">后端默认头像</td>
                                    <td>
                                        <input class="base_text default_img" name="image_defaults[admin_avatar]" value="<?php echo htmlentities($_info['image_defaults']['admin_avatar']); ?>"/>
                                        <div class="default_img">
                                            <img src="<?php echo htmlentities($_info['image_defaults']['admin_avatar']); ?>"/>

                                            <a style="margin-left: 0" uploader-size="2147483648" uploader-type="image" href="javascript:;"
                                            class="base_button"
                                            uploader="image_defaults"
                                            uploader-field="image_defaults[admin_avatar]">上传
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">缩略图(长方形)</td>
                                    <td>
                                        <input class="base_text default_img" name="image_defaults[thumb]" value="<?php echo htmlentities($_info['image_defaults']['thumb']); ?>"/>
                                        <div class="default_img">
                                            <img src="<?php echo htmlentities($_info['image_defaults']['thumb']); ?>"/>
                                            <a style="margin-left: 0" uploader-size="2147483648" uploader-type="image" href="javascript:;"
                                            class="base_button"
                                            uploader="image_defaults"
                                            uploader-field="image_defaults[thumb]">上传
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">缩略图(正方形)</td>
                                    <td>
                                        <input class="base_text default_img" name="image_defaults[thumb2]" value="<?php echo htmlentities($_info['image_defaults']['thumb2']); ?>"/>
                                        <div class="default_img">
                                            <img src="<?php echo htmlentities($_info['image_defaults']['thumb2']); ?>"/>
                                            <a style="margin-left: 0" uploader-size="2147483648" uploader-type="image" href="javascript:;"
                                            class="base_button"
                                            uploader="image_defaults"
                                            uploader-field="image_defaults[thumb2]">上传
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">全局logo</td>
                                    <td>
                                        <input class="base_text default_img" name="image_defaults[logo]" value="<?php echo htmlentities($_info['image_defaults']['logo']); ?>"/>
                                        <div class="default_img">
                                            <img src="<?php echo htmlentities($_info['image_defaults']['logo']); ?>"/>
                                            <a style="margin-left: 0" uploader-size="2147483648" uploader-type="image" href="javascript:;"
                                            class="base_button"
                                            uploader="image_defaults"
                                            uploader-field="image_defaults[logo]">上传
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">主站logo</td>
                                    <td>
                                        <input class="base_text default_img" name="image_defaults[home_logo]" value="<?php echo htmlentities($_info['image_defaults']['home_logo']); ?>"/>
                                        <div class="default_img">
                                            <img src="<?php echo htmlentities($_info['image_defaults']['home_logo']); ?>"/>

                                            <a style="margin-left: 0" uploader-size="2147483648" uploader-type="image" href="javascript:;"
                                            class="base_button"
                                            uploader="image_defaults"
                                            uploader-field="image_defaults[home_logo]">上传
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">下载站logo</td>
                                    <td>
                                        <input class="base_text default_img" name="image_defaults[download_logo]" value="<?php echo htmlentities($_info['image_defaults']['download_logo']); ?>"/>
                                        <div class="default_img">
                                            <img src="<?php echo htmlentities($_info['image_defaults']['download_logo']); ?>"/>

                                            <a style="margin-left: 0" uploader-size="2147483648" uploader-type="image" href="javascript:;"
                                            class="base_button"
                                            uploader="image_defaults"
                                            uploader-field="image_defaults[download_logo]">上传
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">wap网站logo</td>
                                    <td>
                                        <input class="base_text default_img" name="image_defaults[wap_logo]" value="<?php echo htmlentities($_info['image_defaults']['wap_logo']); ?>"/>
                                        <div class="default_img">
                                            <img src="<?php echo htmlentities($_info['image_defaults']['wap_logo']); ?>"/>

                                            <a style="margin-left: 0" uploader-size="2147483648" uploader-type="image" href="javascript:;"
                                            class="base_button"
                                            uploader="image_defaults"
                                            uploader-field="image_defaults[wap_logo]">上传
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">短视频封面</td>
                                    <td>
                                        <input class="base_text default_img" name="image_defaults[film_cover]" value="<?php echo htmlentities($_info['image_defaults']['film_cover']); ?>"/>
                                        <div class="default_img">
                                            <img src="<?php echo htmlentities($_info['image_defaults']['film_cover']); ?>"/>

                                            <a style="margin-left: 0" uploader-size="2147483648" uploader-type="image" href="javascript:;"
                                            class="base_button"
                                            uploader="image_defaults"
                                            uploader-field="image_defaults[film_cover]">上传
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">主播直播间封面</td>
                                    <td>
                                        <input class="base_text default_img" name="image_defaults[live_anchor_cover]" value="<?php echo htmlentities($_info['image_defaults']['live_anchor_cover']); ?>"/>
                                        <div class="default_img">
                                            <img src="<?php echo htmlentities($_info['image_defaults']['live_anchor_cover']); ?>"/>

                                            <a style="margin-left: 0" uploader-size="2147483648" uploader-type="image" href="javascript:;"
                                            class="base_button"
                                            uploader="image_defaults"
                                            uploader-field="image_defaults[live_anchor_cover]">上传
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">小店背景默认图</td>
                                    <td>
                                        <input class="base_text default_img" name="image_defaults[shop_bg]" value="<?php echo htmlentities($_info['image_defaults']['shop_bg']); ?>"/>
                                        <div class="default_img">
                                            <img src="<?php echo htmlentities($_info['image_defaults']['shop_bg']); ?>"/>

                                            <a style="margin-left: 0" uploader-size="2147483648" uploader-type="image" href="javascript:;"
                                            class="base_button"
                                            uploader="image_defaults"
                                            uploader-field="image_defaults[shop_bg]">上传
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            </table>

                            <div class="content_title2">邀请码注册轮播图</div>
                            <table class="content_info2 mt_10">
                                <tr>
                                    <td class="field_name">轮播图</td>
                                    <td>
                                        <ul class="json_list exhibition_img"></ul>
                                        <input name="invite_imgs" type="hidden" value="<?php if($_info['invite_imgs']): ?><?php echo htmlentities(implode($_info['invite_imgs'],',')); endif; ?>"/>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <!--<div class="layui-tab-item">
                            <table class="content_info2 mt_10">
                                <tr>
                                    <td class="field_name">提现比率</td>
                                    <td>
                                        <input class="base_text" name="cash_rate" value="<?php echo htmlentities($_info['cash_rate']); ?>"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">每笔提现手续费</td>
                                    <td>
                                        <input class="base_text" name="cash_fee" value="<?php echo htmlentities($_info['cash_fee']); ?>"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">户提现收取的税费</td>
                                    <td>
                                        <input class="base_text" name="cash_taxes" value="<?php echo htmlentities($_info['cash_taxes']); ?>"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">申请提现所需满足最低货币量</td>
                                    <td>
                                        <input class="base_text" name="cash_min" value="<?php echo htmlentities($_info['cash_min']); ?>"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">每月最多可提现次数</td>
                                    <td>
                                        <input class="base_text" name="cash_monthlimit" value="<?php echo htmlentities($_info['cash_monthlimit']); ?>"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name">{::APP_BEAN_NAME}转化率</td>
                                    <td>
                                        <input class="base_text" name="app_setting[exp_rate]" value="<?php echo htmlentities($_info['app_setting']['exp_rate']); ?>"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field_name"><?php echo APP_MILLET_NAME; ?>转为<?php echo APP_BEAN_NAME; ?>转化率</td>
                                    <td>
                                        <input class="base_text" name="app_setting[millet_rate]" value="<?php echo htmlentities($_info['app_setting']['millet_rate']); ?>"/>
                                    </td>
                                </tr>
                            </table>
                        </div>-->
                    </div>
                </div>
                <div class="base_button_div p_b_20">
                    <a href="javascript:;" class="base_button" ajax="post">提交</a>
                </div>

            </form>
        </div>
    </div>
    <script>
        var myJsonList = new JsonList('.json_list', {
            input: '[name=invite_imgs]',
            btns: ['up', 'down', 'add', 'remove'],
            max: 5,
            format: 'separate',
            fields: [
                {
                    name: 'image',
                    title: '图片',
                    type: 'file',
                    width: 250,
                    upload: {
                        uploader: 'friend_images'
                    }
                }
            ]
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
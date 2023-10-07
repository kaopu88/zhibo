<!DOCTYPE html>
<html>
<head lang="en">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=Edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <notempty name="page_app_name">
        <meta name="application-name" content="{$page_app_name}">
    </notempty>
    <notempty name="page_description">
        <meta name="description" content="{$page_description}">
    </notempty>
    <notempty name="page_keywords">
        <meta name="keywords" content="{$page_keywords}">
    </notempty>
    <block name="meta"></block>
    <title>{$page_title}</title>
    <link rel="stylesheet" type="text/css" href="__VENDOR__/icomoon/style.css?v=__RV__"/>
    <link rel="stylesheet" type="text/css" href="__VENDOR__/webuploader/webuploader.css?v=__RV__"/>
    <link rel="stylesheet" type="text/css" href="__VENDOR__/flatpickr/flatpickr.min.css?v=__RV__"/>
    <link rel="stylesheet" type="text/css" href="__VENDOR__/fancybox/jquery.fancybox.css?v=__RV__"/>
    <link rel="stylesheet" type="text/css" href="__STATIC__/smart_admin/css/smart_admin.css?v=__RV__"/>
    <link rel="stylesheet" type="text/css" href="__STATIC__/common/css/public.css?v=__RV__"/>
    <link rel="stylesheet" type="text/css" href="__CSS__/public.css?v=__RV__"/>
    <link rel="stylesheet" type="text/css" href="/bx_static/custom.css"/>
    <block name="css"></block>
    <include file="public:jsconfig"/>
    <script src="__VENDOR__/jquery.min.js?v=__RV__"></script>
    <script src="__VENDOR__/layer/layer.js?v=__RV__"></script>
    <script src="__VENDOR__/jquery.nicescroll.min.js?v=__RV__"></script>
    <script src="__VENDOR__/jquery.cookie.js?v=__RV__"></script>
    <script src="__VENDOR__/flatpickr/flatpickr.min.js?v=__RV__"></script>
    <script type="text/javascript" src="__VENDOR__/webuploader/webuploader.js?v=__RV__"></script>
    <script src="__VENDOR__/qiniu.min.js?v=__RV__"></script>
    <script src="__VENDOR__/fancybox/jquery.fancybox.pack.js?v=__RV__"></script>
    <script src="__VENDOR__/smart/smart.bundle.js?v=__RV__"></script>
    <script src="__STATIC__/smart_admin/js/smart_admin.bundle.js?v=__RV__"></script>
    <script src="__STATIC__/common/js/public.js?v=__RV__"></script>
    <script src="__JS__/public.js?v=__RV__"></script>
    <block name="js"></block>
</head>
<body>
<div class="main">
    <div class="main_nav">
        <div class="flex_between">
            <div class="flex">
                <div class="main_top_logo">
                    <a href="javascript:;" style="text-decoration: none;">
                        <div style="background-color: #f9f9f9;width: 180px;height: 50px;line-height: 20px;text-align: left;color: #444;font-size: 14px;font-weight: bold;padding-top: 10px;padding-left: 20px;padding-right: 10px;">
                            <div style="overflow: hidden;word-break: keep-all;font-size:22px;">
                                {$agent.name|short=12}<br/>
                                <span style="font-size: 12px;font-weight: normal;color: #777">{:config('app.agent_setting.agent_name')}管理平台</span>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="icon_more"></div>
                <div class="menu_icon" title="主菜单"></div>
            </div>
            <ul class="tool_list">
                <li><a title="网站首页" target="_self" href="{:url('index/index')}"><span class="icon-home"></span></a>
                </li>
                <li><a title="联系客服" target="_blank"
                        href="http://wpa.qq.com/msgrd?v=3&uin={:config('site.contact_qq')}&site=qq&menu=yes"><span
                        class="icon-user"></span>&nbsp;联系客服</a></li>
                <!--<li><a title="退出" ajax-confirm ajax="get" href="{:url('account/logout')}"><span class="icon-exit"></span></a></li>-->
                <li>
                    <a title="消息" href="javascript:;">
                        <span class="icon-envelope"></span>
                        <span class="message_badge">0</span>
                    </a>
                    <ul class="message_list">
                        <li><a href="javascript:;"><span class="icon-remove"></span>清空消息</a></li>
                        <li><a href="javascript:;"><span class="icon-plus"></span>查看更多</a></li>
                    </ul>
                </li>
                <li class="avatar_link">
                    <a title="{$admin|user_name}" href="javascript:;">
                        <div class="main_top_avatar"><img
                                src="{:img_url($admin['avatar'],'200_200','admin_avatar')}"/>
                        </div>
                    </a>
                    <ul class="avatar_list">
                        <li><a href="javascript:;">{$admin|user_name}</a></li>
                        <li><a href="{:url('agent_admin/change_pwd')}">修改密码</a></li>
                        <li><a href="javascript:;">系统设置</a></li>
                        <li><a ajax-confirm ajax="get" href="{:url('account/logout')}">退出</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
    <div class="main_top_content">
        <div class="main_top_progress"></div>
        <div class="main_top_bar">
            <ul class="nav_list">
                <volist name="agent_tree[0]['children']" id="menu1">
                    <li class="pitch"><a class="{$menu1.current}" target="{$menu1.target}"
                            href="{$menu1.menu_url}">{$menu1.name}</a></li>
                </volist>
            </ul>

            <!--  <p class="kpi_num_p" style="margin-top: 16px;margin-left: 20px;color: #d00;font-size: 12px;float: left">
                <a style="color: #d00;" href="{:url('index/index')}">关于数据时效性公告</a>
            </p>-->

        <!--    <p class="kpi_num_p" style="margin-top: 16px;margin-left: 20px;color: #d00;font-size: 12px;float: left">
                数据正在恢复中，不影响今日数据的正常显示，恢复进度：<span class="kpi_num_tip"></span>(进度每5分钟更新一次)
            </p>
            <script>
                function get_kpi_num_tip() {
                    $s.post('{:url("common/get_kpi_num_tip")}', {}, function (result, next) {
                        if (result['status'] == 0) {
                            if(result['data']==''||!result['data']){
                                $('.kpi_num_p').hide();
                            }else{
                                $('.kpi_num_p').show();
                                $('.kpi_num_tip').text(result['data']);
                                setTimeout(function () {
                                    get_kpi_num_tip();
                                }, 5000);

                            }

                        }
                    },{loading:false});
                }

                get_kpi_num_tip();
            </script>-->
        </div>

    </div>
    <div class="icon_side"></div>
    <div class="main_left">
        <div class="sidebar_nav">
            <a href="javascript:;" class="current">菜单导航</a>
        </div>
        <div class="sidebar_box">
            <ul class="sidebar">
                <volist name="agent_tree[1]['children']" id="menu2">
                    <eq name="menu2['mark']" value="agent_manage">
                        <eq name="agent['add_sec']" value="1">
                            <li>
                                <div class="sidebar_title">
                                    <a target="{$menu2.target}" class="{$menu2.current}" href="{$menu2.menu_url}">
                                        <notempty name="menu2['icon']"><span class="sidebar_menu_icon">{$menu2.icon}</span>
                                        </notempty>
                                        {$menu2.name}</a>
                                    <div class="sidebar_icon"><span></span></div>
                                </div>
                                <ul class="sidebar_childrens sidebar_up">
                                    <volist name="menu2['children']" id="menu3">
                                        <li><a target="{$menu3.target}" class="{$menu3.current}" href="{$menu3.menu_url}">
                                            <notempty name="menu3['icon']"><span class="sidebar_menu_icon">{$menu3.icon}</span>
                                            </notempty>
                                            {$menu3.name}</a></li>
                                    </volist>
                                </ul>
                            </li>
                        </eq>
                        <else/>
                        <li>
                            <div class="sidebar_title">
                                <a target="{$menu2.target}" class="{$menu2.current}" href="{$menu2.menu_url}">
                                    <notempty name="menu2['icon']"><span class="sidebar_menu_icon">{$menu2.icon}</span>
                                    </notempty>
                                    {$menu2.name}</a>
                                <div class="sidebar_icon"><span></span></div>
                            </div>
                            <ul class="sidebar_childrens sidebar_up">
                                <volist name="menu2['children']" id="menu3">
                                    <li><a target="{$menu3.target}" class="{$menu3.current}" href="{$menu3.menu_url}">
                                            <notempty name="menu3['icon']"><span class="sidebar_menu_icon">{$menu3.icon}</span>
                                            </notempty>
                                            {$menu3.name}</a></li>
                                </volist>
                            </ul>
                        </li>
                    </eq>
                </volist>
            </ul>
        </div>
    </div>
    <div class="main_right">
        <div class="main_right_content">
            <include file="public/toggle"/>
            <block name="body"></block>
        </div>
    </div>
</div>
<block name="script">
    <script>

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
</block>
<block name="layer"></block>
<script src="/bx_static/toggle.js"></script>
</body>
</html>
<?php /*a:10:{s:56:"/www/wwwroot/zhibb/application/admin/view/live/index.tpl";i:1651826716;s:61:"/www/wwwroot/zhibb/application/admin/view/public/base_nav.tpl";i:1693212818;s:61:"/www/wwwroot/zhibb/application/admin/view/public/jsconfig.tpl";i:1602499828;s:61:"/www/wwwroot/zhibb/application/admin/view/public/main_top.tpl";i:1593518960;s:59:"/www/wwwroot/zhibb/application/admin/view/public/toggle.tpl";i:1592625950;s:60:"/www/wwwroot/zhibb/application/admin/view/live/live_info.tpl";i:1592625950;s:62:"/www/wwwroot/zhibb/application/admin/view/live_film/source.tpl";i:1567562312;s:65:"/www/wwwroot/zhibb/application/admin/view/components/work_pop.tpl";i:1567562304;s:69:"/www/wwwroot/zhibb/application/admin/view/live/live_add_robot_pop.tpl";i:1592625950;s:58:"/www/wwwroot/zhibb/application/admin/view/live/top_pop.tpl";i:1594985552;}*/ ?>
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
                    name: 'room_model',
                    title: '房间模式',
                    opts: [
                        {name: '直播', value: '0'},
                        {name: '录播', value: '1'},
                    ]
                },
                {
                    name: 'type',
                    title: '房间类型',
                    opts: [
                        {name: '普通', value: '0'},
                        {name: '私密', value: '1'},
                        {name: '收费', value: '2'},
                        {name: '计费', value: '3'},
                        {name: 'VIP', value: '4'},
                        {name: '等级', value: '5'}
                    ]
                }
            ]
        };
        $(function () {
            new SearchList('.filter_box', myConfig);
        });
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
                    <div style="float: left">
                        <?php if(check_auth('admin:live:delete',AUTH_UID)): ?>
                            <a class="base_button base_button_s" href="<?php echo url('live/delete'); ?>?<?php echo ('redirect='.urlencode(\think\facade\Request::url())); ?>">关闭直播</a>
                            <a href="<?php echo url('live/robot_delete'); ?>" ajax="post" ajax-target="list_id" ajax-confirm class="base_button base_button_s base_button_red">一键修复机器人</a>
                        <?php endif; ?>
                    </div>
                    <div class="filter_search">
                        <input placeholder="直播间ID" type="text" name="room_id" value="<?php echo input('room_id'); ?>"/>
                        <!--<input placeholder="主播ID" type="text" name="keyword" value="<?php echo input('keyword'); ?>"/> -->
                        <input placeholder="主播ID" type="text" name="user_id" value="<?php echo input('user_id'); ?>"/>
                        <button class="filter_search_submit">搜索</button>
                    </div>
                    <div class="clear"></div>
                </div>
            </div>
            <input type="hidden" name="room_model" value="<?php echo input('room_model'); ?>"/>
            <input type="hidden" name="type" value="<?php echo input('type'); ?>"/>
        </div>
        <div class="table_slide">
        <table class="content_list mt_10">
            <thead>
            <tr>
                <td style="width: 5%;"><input type="checkbox" checkall="list_id"/></td>
                <td style="width: 5%;">ID</td>
                <td style="width: 12%;">直播标题</td>
                <td style="width: 10%;">主播信息</td>
                <td style="width: 7%;">直播属性</td>
                <td style="width: 8%;">推播流状态</td>
                <td style="width: 6%;">房间人数</td>
                <td style="width: 15%;">今日任务</td>
                <td style="width: 6%;">热门推荐</td>
                <!--<td style="width: 6%;">热门置顶</td>-->
                <td style="width: 12%;">直播时间</td>
                <td style="width: 10%;">操作</td>
            </tr>
            </thead>
            <tbody>
            <?php if(!(empty($_list) || (($_list instanceof \think\Collection || $_list instanceof \think\Paginator ) && $_list->isEmpty()))): if(is_array($_list) || $_list instanceof \think\Collection || $_list instanceof \think\Paginator): $i = 0; $__LIST__ = $_list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
                    <tr data-id="<?php echo htmlentities($vo['id']); ?>">
                        <td>
                            <input class="list_id" type="checkbox" name="ids[]" value="<?php echo htmlentities($vo['id']); ?>"/>
                        </td>
                        <td><?php echo htmlentities($vo['id']); ?></td>
                        <td>
                            <div class="thumb">
    <a layer-title="0" layer-area="414px,779px"
       layer-open="<?php echo url('live/tcplayer',['id'=>$vo['id']]); ?>" href="javascript:;"
       class="thumb_img" style="display: inline-block;max-width: 100px;">
        <img src="<?php echo img_url($vo['cover_url']?$vo['cover_url']:$vo['avatar'],'120_68','film_cover'); ?>"/>
    </a>
</div>
                        </td>
                        <td>
                            ID：<?php echo htmlentities($vo['user_id']); ?><br/>
                            昵称：<span class=""><?php echo htmlentities(short($vo['nickname'],'20')); ?></span><br/>
                        </td>
                        <td>
                            房间模式：<?php echo htmlentities($vo['model_str']); ?><br/>
                            直播类型：<?php echo htmlentities($vo['type_str']); ?><br/>
                            类型值：<?php echo htmlentities((isset($vo['type_val']) && ($vo['type_val'] !== '')?$vo['type_val']:'暂无')); ?>
                        </td>
                        <td>
                            <?php if(!(empty($vo['video_id']) || (($vo['video_id'] instanceof \think\Collection || $vo['video_id'] instanceof \think\Paginator ) && $vo['video_id']->isEmpty()))): ?>
    云点播：<?php echo htmlentities($vo['video_id']); ?><a style="margin-left: 5px;color: #f57941;" href="javascript:;"><span class="icon-play"></span></a><br/>
<?php endif; if(!(empty($vo['third_url']) || (($vo['third_url'] instanceof \think\Collection || $vo['third_url'] instanceof \think\Paginator ) && $vo['third_url']->isEmpty()))): ?>
    <?php echo htmlentities($vo['source']); ?>：<a target="_blank" href="<?php echo htmlentities($vo['third_url']); ?>"><?php echo htmlentities(short($vo['third_url'],15)); ?></a>
    <a style="margin-left: 5px;color: #f57941;" href="javascript:;"><span class="icon-play"></span></a>
<?php endif; ?>
                        </td>
                        <td>
                            观众数：<?php echo htmlentities((isset($vo['audience']) && ($vo['audience'] !== '')?$vo['audience']:'0')); ?><br/>
                            机器人：<?php echo htmlentities((isset($vo['robot']) && ($vo['robot'] !== '')?$vo['robot']:'0')); ?><br/>
                            实时显：<?php echo htmlentities($vo['audience']*2+$vo['robot']*20); ?>
                        </td>
                        <td>
                            累计时长：<?php echo htmlentities((isset($vo['task_duration']) && ($vo['task_duration'] !== '')?$vo['task_duration']:'')); ?><br/>
                            累计点亮：<?php echo htmlentities((isset($vo['task_light']) && ($vo['task_light'] !== '')?$vo['task_light']:'')); ?><br/>
                            今日收益：<?php echo htmlentities((isset($vo['task_profit']) && ($vo['task_profit'] !== '')?$vo['task_profit']:'')); ?><br/>
                            新增粉丝：<?php echo htmlentities((isset($vo['task_fans']) && ($vo['task_fans'] !== '')?$vo['task_fans']:'')); ?><br/>
                            PK胜场：<?php echo htmlentities((isset($vo['task_pk']) && ($vo['task_pk'] !== '')?$vo['task_pk']:'')); ?><br/>
                        </td>
                          <td>
                            <div tgradio-not="<?php echo check_auth('admin:live:hot')?'0':'1'; ?>"
                                 tgradio-value="<?php echo htmlentities($vo['hot_status']); ?>" tgradio-name="hot"
                                 tgradio="<?php echo url('change_hot',array('id'=>$vo['id'])); ?>"></div>
                        </td>

                        <!--<td>
                            <div tgradio-not="<?php echo check_auth('admin:live:top')?'0':'1'; ?>"
                                 tgradio-value="<?php echo htmlentities($vo['top_status']); ?>" tgradio-name="top"
                                 tgradio="<?php echo url('change_top',array('id'=>$vo['id'])); ?>"></div>
                        </td>-->

                        <td>
                            开播时间：<?php echo htmlentities(time_format($vo['create_time'],'暂无','datetime')); ?><br/>
                            直播时长：<?php echo htmlentities($vo['live_duration']); ?>
                        </td>
                        <td>
                            <?php if($vo['room_model'] == 1): if(check_auth('admin:live:update',AUTH_UID)): ?>
                                <a href="<?php echo url('edit',array('id'=>$vo['id'])); ?>">编辑信息</a><br/>
                            <?php endif; endif; if(check_auth('admin:live:robot',AUTH_UID)): ?>
                                <a poplink="live_add_robot" data-id="room_id:<?php echo htmlentities($vo['id']); ?>" href="javascript:;">调配机器人</a><br/>
                            <?php endif; ?>
                           <a poplink="live_top_box" data-query="room_id=<?php echo htmlentities($vo['id']); ?>&sort=<?php echo htmlentities($vo['sort']); ?>"  href="javascript:;">
                                添加置顶排序
                            </a><br/>
                            <?php if(check_auth('admin:live:select',AUTH_UID)): ?>
                                <a class="fc_red" ajax-confirm ajax="get" href="<?php echo url('live/delete',array('room_id'=>$vo['id'])); ?>">关播</a>
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

    <div class="live_add_robot pa_10 layer_box" title="分配机器人" popbox-action="<?php echo url('admin/live/addRobot'); ?>"
     popbox-area="350px,250px">
    <table class="content_info2">
        <tr>
            <td>
                数量：<input name="num" type="text" class="base_text" style="width: 120px;">
            </td>
        </tr>
        <tr>
            <td style="padding-left: 40px;">
                <span>请注意变动1个机器人APP端相应放大20倍</span><br />
                <span>为正值时增加机器人,为负值时移除机器人。如(-20)</span>
            </td>
        </tr>
        <tr>
            <td style="padding-left: 40px;">
                <input type="hidden" name="room_id" value=""/>
                <div class="base_button sub_btn">提交</div>
            </td>
        </tr>
    </table>
</div>

    <div dom-key="live_top_box" class="live_top_box pa_10 layer_box" title="排序" popbox-action="<?php echo url('admin/live/addTopSort'); ?>"
     popbox-area="350px,250px">
    <table class="content_info2">
        <tr>
            <td>
                排序：<input name="sort" type="text" value="" class="base_text" style="width: 120px;">
            </td>
        </tr>
        <tr>
            <td style="float:left;padding-left: 40px;">
                <input type="hidden" name="room_id" value=""/>
                <div class="base_button sub_btn">提交</div>
            </td>
        </tr>
    </table>
</div>


<script src="/bx_static/toggle.js"></script>
</body>
</html>
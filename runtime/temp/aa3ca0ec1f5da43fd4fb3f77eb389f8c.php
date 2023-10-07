<?php /*a:13:{s:68:"/www/wwwroot/zhibb/application/admin/view/recommend_content/film.tpl";i:1592360224;s:61:"/www/wwwroot/zhibb/application/admin/view/public/base_nav.tpl";i:1693212818;s:61:"/www/wwwroot/zhibb/application/admin/view/public/jsconfig.tpl";i:1602499828;s:61:"/www/wwwroot/zhibb/application/admin/view/public/main_top.tpl";i:1593518960;s:59:"/www/wwwroot/zhibb/application/admin/view/public/toggle.tpl";i:1592625950;s:62:"/www/wwwroot/zhibb/application/admin/view/video/video_info.tpl";i:1592625950;s:58:"/www/wwwroot/zhibb/application/admin/view/video/source.tpl";i:1592625950;s:59:"/www/wwwroot/zhibb/application/admin/view/video/vo_user.tpl";i:1592625950;s:61:"/www/wwwroot/zhibb/application/admin/view/video/total_fee.tpl";i:1592625950;s:65:"/www/wwwroot/zhibb/application/admin/view/components/work_pop.tpl";i:1567562304;s:76:"/www/wwwroot/zhibb/application/admin/view/recommend_content/sort_handler.tpl";i:1592645870;s:69:"/www/wwwroot/zhibb/application/admin/view/video/film_audit_update.tpl";i:1650953148;s:70:"/www/wwwroot/zhibb/application/admin/view/components/recommend_pop.tpl";i:1567562304;}*/ ?>
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
                    name: 'rec_id',
                    title: '推荐位',
                    get: '<?php echo url("recommend_content/get_rec",array('type'=>'film')); ?>'
                }
            ]
        };
    </script>
    <script src="<?php echo ('/static/'.strtolower(\think\facade\Request::module()).'/js'); ?>/video/index.js?v=<?php echo config('upload.resource_version'); ?>"></script>
    <script src="/static/vendor/raty/jquery.raty.min.js?v=<?php echo config('upload.resource_version'); ?>"></script>
    <script src="<?php echo ('/static/'.strtolower(\think\facade\Request::module()).'/js'); ?>/video/update.js?v=<?php echo config('upload.resource_version'); ?>"></script>

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
                        <a href="<?php echo url('del_recommend'); ?>" ajax="post" ajax-target="list_id" ajax-confirm
                           class="base_button base_button_s base_button_red">取消推荐</a>
                    </div>
                    <div class="filter_search">
                        <input placeholder="发布者ID、昵称" type="text" name="user_keyword" value="<?php echo input('user_keyword'); ?>"/>
                        <input placeholder="视频标题ID" type="text" name="keyword" value="<?php echo input('keyword'); ?>"/>
                        <button class="filter_search_submit">搜索</button>
                    </div>
                    <div class="clear"></div>
                </div>
            </div>
            <input type="hidden" name="rec_id" value="<?php echo htmlentities($get['rec_id']); ?>" />
        </div>
        <div class="table_slide">
            <table class="content_list mt_10">
            <thead>
            <tr>
                <td style="width: 5%;"><input type="checkbox" checkall="list_id"/></td>
                <td style="width: 5%;">ID</td>
                <td style="width: 10%;">推荐位</td>
                <td style="width: 10%;">视频封面</td>
                <td style="width: 15%;">视频描述</td>
                <td style="width: 8%;">视频属性</td>
                <td style="width: 15%;">发布用户</td>
                <td style="width: 7%;">热度</td>
                <td style="width: 8%;">标签</td>
                <td style="width: 7%;">排序</td>
                <td style="width: 10%;">操作</td>
            </tr>
            </thead>
            <tbody>
            <?php if(!(empty($_list) || (($_list instanceof \think\Collection || $_list instanceof \think\Paginator ) && $_list->isEmpty()))): if(is_array($_list) || $_list instanceof \think\Collection || $_list instanceof \think\Paginator): $i = 0; $__LIST__ = $_list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
                    <tr data-id="<?php echo htmlentities($vo['rc_id']); ?>">
                        <td><input class="list_id" type="checkbox" name="ids[]" value="<?php echo htmlentities($vo['rc_id']); ?>"/></td>
                        <td><?php echo htmlentities($vo['rc_id']); ?></td>
                        <td><?php echo htmlentities($vo['rs_name']); ?></td>
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
                            <span class=""><?php echo htmlentities($vo['describe']); ?></span>
                            <?php switch($vo['visible']): case "0": break; case "1": ?><span title="互关可见" class="icon-users3 fc_gray"></span> <?php break; case "2": ?><span title="私密视频" class="icon-eye-blocked fc_gray"></span> <?php break; endswitch; ?>
                        </td>
                        <td>
                            宽高：<?php echo htmlentities($vo['width']); ?>*<?php echo htmlentities($vo['height']); ?><br/>
                            时长：<?php echo htmlentities($vo['duration_str']); ?><br/>
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
                            播放：<?php echo htmlentities($vo['play_sum']); ?><br/>
                            点赞：<?php echo htmlentities($vo['zan_sum']); ?><br/>
                            评论：<?php echo htmlentities($vo['comment_sum']); ?><br/>
                            人次：<?php echo htmlentities($vo['watch_sum']); ?><br/>
                        </td>
                        <td>
                            <?php echo htmlentities((isset($vo['tag_names']) && ($vo['tag_names'] !== '')?$vo['tag_names']:'无标签')); ?><br/>
<?php if(!(empty($vo['music']) || (($vo['music'] instanceof \think\Collection || $vo['music'] instanceof \think\Paginator ) && $vo['music']->isEmpty()))): ?>
    <a href="javascript:;"><span class="icon-music" style="margin-right: 3px;"></span><?php echo htmlentities(short($vo['music']['title'],15)); ?></a> <br/>
    <?php else: ?>
    无音乐<br/>
<?php endif; ?>
                        </td>
                        <td><?php echo htmlentities($vo['sort']); ?></td>
                        <td>
                            <a data-query="id=<?php echo htmlentities($vo['rc_id']); ?>&sort=<?php echo htmlentities($vo['sort']); ?>" poplink="sort_handler"
                               href="javascript:;">修改排序</a><br/>
                            <a class="fc_red" ajax-confirm ajax="get" href="<?php echo url('del_recommend',array('id'=>$vo['rc_id'])); ?>?<?php echo ('redirect='.urlencode(\think\facade\Request::url())); ?>">取消推荐</a>
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

    <div title="修改排序" class="layer_box sort_handler pa_10" dom-key="sort_handler" popbox-area="520px,200px" popbox-get-data="<?php echo url('recommend_content/sort_handler'); ?>" popbox-action="<?php echo url('recommend_content/sort_handler'); ?>">
    <table class="content_info2">
        <tr>
            <td class="field_name">排序</td>
            <td>
                <input placeholder="" name="sort" class="base_text w_400" value=""/>
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
    <block name="css">
    <style>
        .rule_item {
            display: inline-block;
            border: solid 1px #DCDCDC;
            line-height: 30px;
            padding: 0px 5px;
            border-radius: 5px;
            margin: 0 3px 3px 0;
            cursor: pointer;
            font-size: 12px;
            width: 140px;
            text-align: left;
        }

        .rule_item .icon-remove {
            margin-left: 5px;
            display: inline-block;
            cursor: pointer;
            float: right;
            margin-right: 3px;
            margin-top: 8px;
        }

        .rule_item:hover {
            color: #e60012;
        }
    </style>
</block>
<div title="短视频编辑" class="layer_box film_audit_update pa_10" dom-key="film_audit_update"
     popbox-action="<?php echo url('video/audit_update'); ?>" popbox-get-data="<?php echo url('video/audit_update'); ?>" popbox-area="700px,550px">
    <table class="content_info2">
        <tr class="edit_tr">
            <td class="field_name">视频描述</td>
            <td>
                <textarea name="describe" style="height: 70px;" class="base_text"></textarea>
            </td>
        </tr>
        <tr class="edit_tr">
            <td class="field_name">标签列表</td>
            <td>
                <div class="box">
                    
                </div>
                <div>
                    <span class="icon-plus"></span>
                    <input type="hidden" value="" name="tags"/>
                    <input type="hidden" value="" name="tag_names"/>
                    <input type="hidden" value="" name="arr"/>
                </div>
                <script>
                var findTagsUrl = "<?php echo url('film_tags/selector'); ?>";
                var params = {};
                var liData = {};
                var tags = '';
                var tag_names = '';
                var ids  = [];
                var names  = [];
                var selectedList = [];
                var tags = $('[name=tags]').val();
                if (tags && tags != '') {
                    params['selected'] = tags;
                }

                var obj = {
                    type: 2,
                    scrollbar: false,
                    title: '选择标签',
                    shadeClose: true,
                    shade: 0.75,
                    area: ['800px', '600px'],
                    content: $s.buildUrl(findTagsUrl, params)
                };
                $('.icon-plus').click(function(){
                    var arr = $("input[name='arr']").val();
                    if(arr){
                        arr = JSON.parse(arr);
                    }else{
                        arr = [];
                    }
                    layerIframe.open(obj,function(win){
                        win['getFillValue']=function(){
                            if(arr){
                                return selectedList.concat(arr);
                            }else{
                                return selectedList;
                            }
                        };

                        win.WinEve.on('select',function(eve){
                            
                            var selectedList = arr;
                            liData = eve.data;
                            selectedList.push(liData);
                            ids = $("input[name='tags']").val();
                            if(ids){
                                ids = $("input[name='tags']").val().split(',');
                            }else{
                                ids = [];
                            }
                            names = $("input[name='tag_names']").val();
                            if(names){
                                names = $("input[name='tag_names']").val().split(',');
                            }else{
                                names = [];
                            }
                            ids.push(liData.id);
                            names.push(liData.name);
                            tags = ids.join(',');
                            tag_names = names.join(',');
                            $("input[name='tags']").val(tags);
                            $("input[name='tag_names']").val(tag_names);
                            var str = '<div class="rule_item rule_item_'+liData.id+'" rule-id="'+liData.id+'"><span class="rule_item_name">'+liData.name+'</span><span class="icon-remove" onclick="remove(\''+liData.id+'\')"></span></div>';
                            $('.box').append(str);
                            $("input[name='arr']").val(JSON.stringify(selectedList));
                        });
                        win.WinEve.on('remove', function (eve) {
                            ids = $("input[name='tags']").val();
                            if(ids){
                                ids = $("input[name='tags']").val().split(',');
                            }else{
                                ids = [];
                            }
                            names = $("input[name='tag_names']").val();
                            if(names){
                                names = $("input[name='tag_names']").val().split(',');
                            }else{
                                names = [];
                            }
                            var selectedList = arr;
                            for (var i = 0; i < selectedList.length; i++) {
                                if (selectedList[i]['id'] == eve.data) {
                                    selectedList.splice(i, 1);
                                    ids.splice(i, 1);
                                    names.splice(i, 1);
                                    $('.rule_item_'+eve.data).remove();
                                    break;
                                }
                            }
                            tags = ids.join(',');
                            $("input[name='tags']").val(tags);
                            tag_names = names.join(',');
                            $("input[name='tag_names']").val(tag_names);
                            $("input[name='arr']").val(JSON.stringify(selectedList));
                        });
                    })
                })

                function remove(id){
                    var arr = $("input[name='arr']").val();
                    if(arr){
                        arr = JSON.parse(arr);
                    }else{
                        arr = [];
                    }
                    ids = $("input[name='tags']").val();
                    if(ids){
                        ids = $("input[name='tags']").val().split(',');
                    }else{
                        ids = [];
                    }
                    names = $("input[name='tag_names']").val();
                    if(names){
                        names = $("input[name='tag_names']").val().split(',');
                    }else{
                        names = [];
                    }
                    var selectedList = arr;
                    for (var i = 0; i < selectedList.length; i++) {
                        if (selectedList[i]['id'] == id) {
                            selectedList.splice(i, 1);
                            ids.splice(i, 1);
                            names.splice(i, 1);
                            $('.rule_item_'+id).remove();
                            break;
                        }
                    }
                    tags = ids.join(',');
                    $("input[name='tags']").val(tags);
                    tag_names = names.join(',');
                    $("input[name='tag_names']").val(tag_names);
                    $("input[name='arr']").val(JSON.stringify(selectedList));
                }
                
                </script>

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
            <td class="field_name">视频评分</td>
            <td>
                <div class="star_box"></div>
                <div>
                    <span class="star_tip">没有评分</span>
                </div>
            </td>
        </tr>

        <tr class="edit_tr">
            <td class="field_name">推荐权重</td>
            <td>
                <input class="base_text" type="number" name="weight" value=""/>
                <div class="field_tip">权重为1到9之间数值</div>
            </td>
        </tr>
        <tr>
            <td class="field_name"></td>
            <td>
                <input type="hidden" name="id" value=""/>
                <div class="base_button_div max_w_412">
                    <div data-next="0" class="base_button sub_btn2 mt_10">提交</div>
                </div>
            </td>
        </tr>
    </table>
    <div style="height: 30px"></div>
</div>
    <div dom-key="recommend_box" popbox="recommend_box"  class="recommend_box layer_box pa_10" title="推荐" popbox-action="<?php echo url('recommend_content/save'); ?>" popbox-get-data="<?php echo url('recommend_content/save'); ?>" popbox-area="560px,450px">
    <ul class="recommend_list"></ul>
    <div style="text-align: center;">
        <input name="id" type="hidden" value=""/>
        <input name="type" type="hidden" value=""/>
        <div class="base_button sub_btn">保存</div>
    </div>
</div>

<script src="/bx_static/toggle.js"></script>
</body>
</html>
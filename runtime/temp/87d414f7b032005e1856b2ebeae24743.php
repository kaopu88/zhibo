<?php /*a:3:{s:55:"/www/wwwroot/zhibb/application/admin/view/gift/find.tpl";i:1592625950;s:64:"/www/wwwroot/zhibb/application/admin/view/public/base_iframe.tpl";i:1592625950;s:61:"/www/wwwroot/zhibb/application/admin/view/public/jsconfig.tpl";i:1602499828;}*/ ?>
<!DOCTYPE html>
<html>
<head lang="en">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=Edge">
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
        .select_btn {
            color: #555;
        }

        .selected, .selected:visited, .selected:hover, .selected:active {
            color: #eb6100;
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
    <script src="/static/vendor/flatpickr/flatpickr.min.js?v=<?php echo config('upload.resource_version'); ?>"></script>
    <script type="text/javascript" src="/static/vendor/webuploader/webuploader.js?v=<?php echo config('upload.resource_version'); ?>"></script>
    <script src="/static/vendor/qiniu.min.js?v=<?php echo config('upload.resource_version'); ?>"></script>
    <script src="/static/vendor/fancybox/jquery.fancybox.pack.js?v=<?php echo config('upload.resource_version'); ?>"></script>
    <script src="/static/vendor/smart/smart.bundle.js?v=<?php echo config('upload.resource_version'); ?>"></script>
    <script src="/static/smart_admin/js/smart_admin.bundle.js?v=<?php echo config('upload.resource_version'); ?>"></script>
    <script src="/static/common/js/public.js?v=<?php echo config('upload.resource_version'); ?>"></script>
    <script src="<?php echo ('/static/'.strtolower(\think\facade\Request::module()).'/js'); ?>/public.js?v=<?php echo config('upload.resource_version'); ?>"></script>
    <script>
        if (!hasParentWindow()) {
            window.location = '/';
        }
    </script>
    
    <script>
        var liveFilmItemUrl = '<?php echo url("gift/detail"); ?>',
            selectedListJson = '<?php echo htmlspecialchars_decode($selected_list); ?>';
    </script>
    <script>
        $(function () {
            new SearchList('.filter_box', myConfig);
            new FindListController('body', {
                primary_key: 'id',
                name_key: 'name',
                selected: selectedListJson,
                item_url: liveFilmItemUrl
            });
        });

        var myConfig = {
            action: '<?php echo url("gift/find"); ?>',//提交地址
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
                    name: 'cid',
                    title: '使用场景',
                    opts: [
                        {name: '直播礼物', value: '0'},
                        {name: '视频礼物', value: '1'},
                        {name: '道具礼物', value: '1'},
                    ]
                },
                {
                    name: 'type',
                    title: '类型',
                    opts: [
                        {name: '连送', value: '1'},
                        {name: '单送', value: '0'}
                    ]
                }
            ]
        };
    </script>

</head>
<body>

    <div class="pa_20">
        <div class="filter_box mt_10">
            <div class="filter_nav">
                已选择&nbsp;>&nbsp;
                <p class="filter_selected"></p>
            </div>
            <div class="filter_options">
                <ul class="filter_list"></ul>
                <div class="filter_order">
                    <div style="float: left;line-height: 30px;font-size: 12px;">
                        <a class="show_selected" href="javascript:;">已选中礼物(<span class="selected_num">0</span>)</a>
                    </div>
                    <div class="filter_search">
                        <input placeholder="礼物ID" type="text" name="id" value="<?php echo input('id'); ?>"/>
                        <input placeholder="礼物名称、ID" type="text" name="keyword" value="<?php echo input('keyword'); ?>"/>
                        <button class="filter_search_submit">搜索</button>
                    </div>
                    <div class="clear"></div>
                </div>
            </div>
            <input type="hidden" name="status" value="<?php echo input('status'); ?>"/>
            <input type="hidden" name="source" value="<?php echo input('source'); ?>"/>
            <input type="hidden" name="is_local" value="<?php echo input('is_local'); ?>"/>
        </div>
        <table class="content_list mt_10 find_list">
            <thead>
            <tr>
                <td style="width: 5%;"><input type="checkbox" checkall="list_id"/></td>
                <td style="width: 10%;">ID</td>
                <td style="width: 18%;">礼物名称</td>
                <td style="width: 15%;">礼物价值</td>
                <td style="width: 10%;">类型</td>
                <td style="width: 15%;">应用场景</td>
                <td style="width: 10%;">启用状态</td>
                <td style="width: 10%;">创建时间</td>
                <td style="width: 10%;">操作</td>
            </tr>
            </thead>
            <tbody>
            <?php if(!(empty($_list) || (($_list instanceof \think\Collection || $_list instanceof \think\Paginator ) && $_list->isEmpty()))): if(is_array($_list) || $_list instanceof \think\Collection || $_list instanceof \think\Paginator): $i = 0; $__LIST__ = $_list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
                    <tr class="find_list_li" data-id="<?php echo htmlentities($vo['user_id']); ?>">
                        <td><input class="list_id" type="checkbox" name="ids[]" value="<?php echo htmlentities($vo['id']); ?>"/></td>
                        <td><?php echo htmlentities($vo['id']); ?></td>
                        <td>
                            <div style="margin-right: 8px; float: left;"><img style="vertical-align: middle;" src="<?php echo htmlentities($vo['picture_url']); ?>?imageView2/1/w/50/h/50" alt=""></div>
                            <div style="float: left;">
                                礼物名称：<a href="javascript:;"><?php echo htmlentities(short($vo['name'],'20')); ?></a>
                            </div>
                        </td>
                        <td>
                            <?php echo config('app.product_info.bean_name'); ?>：<?php echo htmlentities($vo['price']); ?><br/>
                            优惠：<?php echo htmlentities($vo['discount_str']); ?><br/>
                        </td>
                        <td>
                            <?php echo htmlentities($vo['type_str']); ?>
                        </td>
                        <td><?php echo htmlentities($vo['cid_str']); ?></td>
                        <td>
                            <div tgradio-not="<?php echo check_auth('admin:gift:update')?'0':'1'; ?>"
                                 tgradio-value="<?php echo htmlentities($vo['status']); ?>" tgradio-name="status"
                                 tgradio="<?php echo url('change_status',array('id'=>$vo['id'])); ?>"></div>
                        </td>
                        <td><?php echo htmlentities(time_format($vo['create_time'],'无','date')); ?></td>
                        <td>
                            <input class="find_params" type="hidden" name="id" value="<?php echo htmlentities($vo['id']); ?>"/>
                            <input class="find_params" type="hidden" name="name" value="<?php echo htmlentities($vo['name']); ?>"/>
                            <input class="find_params" type="hidden" name="images" value="<?php echo htmlentities($vo['picture_url']); ?>"/>
                            <input class="find_params" type="hidden" name="price" value="<?php echo htmlentities($vo['price']); ?>"/>

                            <a data-id="<?php echo htmlentities($vo['id']); ?>" class="select_btn" href="javascript:;">选择</a>
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
        <div class="pageshow mt_10"><?php echo htmlspecialchars_decode($_page);; ?></div>
    </div>






    <div class="selected_box" style="padding: 10px;display: none;">
        <table class="table" style="width: 100%;box-sizing: border-box;">
            <thead>
            <tr>
                <td>礼物ID</td>
                <td>名称</td>
                <td>操作</td>
            </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>

</body>
</html>
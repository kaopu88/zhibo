<?php /*a:7:{s:60:"/www/wwwroot/zhibb/application/admin/view/video/batchadd.tpl";i:1693292240;s:61:"/www/wwwroot/zhibb/application/admin/view/public/base_nav.tpl";i:1693212818;s:61:"/www/wwwroot/zhibb/application/admin/view/public/jsconfig.tpl";i:1602499828;s:61:"/www/wwwroot/zhibb/application/admin/view/public/main_top.tpl";i:1593518960;s:59:"/www/wwwroot/zhibb/application/admin/view/public/toggle.tpl";i:1592625950;s:64:"/www/wwwroot/zhibb/application/admin/view/components/tab_nav.tpl";i:1592356812;s:65:"/www/wwwroot/zhibb/application/admin/view/components/work_pop.tpl";i:1567562304;}*/ ?>
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
    
    <link rel="stylesheet" type="text/css" href="/static/vendor/cropper/cropper.min.css?v=<?php echo config('upload.resource_version'); ?>"/>
    <link rel="stylesheet" type="text/css" href="/static/vendor/layer/layui/css/layui.css"/>

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
    
    <script src="/static/vendor/tencentyun/ugcUploader.js?v=<?php echo config('upload.resource_version'); ?>"></script>
    <script src="/static/vendor/cropper/cropper.min.js?v=<?php echo config('upload.resource_version'); ?>"></script>
    <script src="<?php echo ('/static/'.strtolower(\think\facade\Request::module()).'/js'); ?>/video/add.js?v=<?php echo config('upload.resource_version'); ?>"></script>
    <script src="/static/vendor/layer/layui/layui.js" charset="utf-8"></script>
    <script src="<?php echo ('/static/'.strtolower(\think\facade\Request::module()).'/js'); ?>/video/es6-promise.auto.js"></script>
    <script src="<?php echo ('/static/'.strtolower(\think\facade\Request::module()).'/js'); ?>/vue.js"></script>
    <script src="<?php echo ('/static/'.strtolower(\think\facade\Request::module()).'/js'); ?>/axios.js"></script>
    <script src="<?php echo ('/static/'.strtolower(\think\facade\Request::module()).'/js'); ?>/video/vod-js-sdk-v6.js"></script>

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


            
    <div class="pa_20 p_nav p_b_60">
        <div class="content_title">
            <h1><?php echo htmlentities($admin_last['name']); ?></h1>
            <a href="javascript:;" class="base_button base_button_gray base_button_s history_back">返回</a>
        </div>
        
        <ul class="tab_nav mt_10">
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
        </ul>

        <div class="content_toolbar mt_10">
            <ul class="content_toolbar_btns">
                <?php if(check_auth('admin:film:add',AUTH_UID)): ?>
                    <li><a id="batchUp" href="javascript:;" class="base_button base_button_s base_button_gray add_btn">
                            <span class="icon-plus"></span> 选择视频
                        </a></li>
                <?php endif; ?>
            </ul>
            <div style="float: right;font-size: 12px;line-height: 30px;" class="fc_orange">已选择<span class="video_num">0</span>个视频</div>
        </div>

        <table class="content_list mt_10 audit_list mt_10">
            <thead>
            <tr>
                <td>标题</td>
                <td>大小</td>
                <td>进度</td>
                <td>状态</td>
                <td>操作</td>
            </tr>
            </thead>
            <tbody id="batchUpBody">

            </tbody>
        </table>

        <div class="mt_10">
            <div class="base_button layui-btn-disabled layui_upload" id="beforeBatchAction">开始上传</div>
            <a class="layui-btn next" style="float:right;display:none;" href="<?php echo url('batchedit'); ?>">下一步</a>
        </div>

    </div>
    <script type="text/javascript" src="/static/vendor/wcs-js-sdk-1.0.10/dist/wcs.min.js"></script>
    <script>
        
        function getSignature() {
          return axios.post('<?php echo url('getsignature'); ?>').then(function (response) {
            return response.data;
          })
        };
        function urlsafeBase64Decode(encodedString) {
          // 替换 URL 安全的 Base64 字符串中的特殊字符
          encodedString = encodedString.replace(/-/g, '+').replace(/_/g, '/');
        
          // 进行 Base64 解码
          const decodedString = atob(encodedString);
        
          // 将解码后的字符串转换为 Uint8Array
          const decodedBytes = new Uint8Array(decodedString.length);
          for (let i = 0; i < decodedString.length; i++) {
            decodedBytes[i] = decodedString.charCodeAt(i);
          }
        
          return decodedBytes;
        }
        var self = '';

        var batchFile = {};
        layui.use(['form', 'layer', 'element', 'upload'], function () {
            
            var form = layui.form, layer = layui.layer, element = layui.element, upload = layui.upload;

            var batchUpView = $('#batchUpBody'),
                batchUpRender = upload.render({
                elem: '#batchUp',
                url: '',
                accept: 'video',
                multiple: true,
                auto: false,
                bindAction: '',

                choose: function(obj){
                    $('.layui_upload').removeClass('layui-btn-disabled').attr('id','beforeBatchAction');
                    this.files = obj.pushFile();
                    self = this;
                    obj.preview(function(index, files, result){

                        batchFile[index] = {"videourl":'', "videoid":'', "videoname":files.name};

                        var tr = $(
                            [
                                '<tr id='+index+'>',
                                '<td>'+files.name+'</td>',
                                '<td>'+ (files.size/1048576).toFixed(2) +'MB</td>',
                                '<td class="progress_'+index+'" style="color:green">0.00%</td>',
                                '<td>等待上传</td>',
                                '<td>',
                                '<button class="layui-btn layui-btn-sm batch-reload layui-hide">重传</button>',
                                '<button class="layui-btn layui-btn-sm layui-btn-danger batch-delete">删除</button>',
                                '</td>',
                                '</tr>'].join('')
                        );


                        tr.find('.batch-reload').on('click', function(){
                            obj.upload(index, files);//单个重传
                        });

                        //删除
                        tr.find('.batch-delete').on('click', function(){
                            delete self.files[index]; //删除对应的文件
                            tr.remove();
                            batchUpRender.config.elem.next()[0].value = ''; //清空 input file 值，以免删除后出现同名文件不可选
                            delete batchFile[index];
                            var video_num = $('#batchUpBody tr').length;
                            if(video_num==0){
                                $('.layui_upload').addClass('layui-btn-disabled').attr('id','');
                            }
                            $('.video_num').text(video_num);
                        });

                        batchUpView.append(tr);
                        var video_num = $('#batchUpBody tr').length;
                        if(video_num==0){
                            $('.layui_upload').addClass('layui-btn-disabled').attr('id','');
                        }
                        $('.video_num').text(video_num);
                        form.render();
                    });

                },

                done: function(res, index, upload){
                
                    if(res.status == 0){
                        let tr = batchUpView.find('tr#'+ index), tds = tr.children();
                        tds.eq(3).html('<span style="color: #5FB878;">上传成功</span>');
                        delete self.files[index];
                        delete batchFile[index];
                        $('.next').show();
                    }else {
                        this.error(index, upload);
                    }

                },

                error: function(index, upload){
                    let tr = batchUpView.find('tr#'+ index), tds = tr.children();
                    tds.eq(3).html('<span style="color: #FF5722;">上传失败</span>');
                    tds.last().find('.batch-reload').removeClass('layui-hide'); //显示重传
                }

            });
            
            //批量上传-视频上传
            $('#beforeBatchAction').on('click', function () {

                if(self.files == '' || self.files == 'undefined')
                {
                    layer.msg('暂未添加视频源');
                    return false;
                }


                let load = layer.load(1, {shade: [0.4,'#fff']});

                let tag = true;
                $.each(self.files, function (index, item) {
                    let tokenData={};
                    tokenData.type = '_default';
                    tokenData.storer = 'wsyun';
                    tokenData.filename = item.name;
                    let tokenRes = {};
                    $.ajax({
                            type: "POST",
                            url: "/admin/common/get_qiniu_token.html",
                            dataType: 'JSON',
                            async: false,
                            data: tokenData,
                            success: function (res) {
                                tokenRes = res.data;
                            },
                        });
                    let extraConfig={
                                timeout: 0,
                                concurrentRequestLimit:5,
                                retryCount:0
                            }
                    var uploaderInfo = {
                      isVideoUploadSuccess: false,
                      isVideoUploadCancel: false,
                      progress: 0,
                      fileId: '',
                      videoUrl: '',
                      cancel: function() {
                        uploaderInfo.isVideoUploadCancel = true;
                        uploader.cancel()
                      },
                    }
                    //const uploadObj = wcs.wcsUpload(item, tokenRes.token, tokenRes.base, extraConfig);
                    const uploadObj = wcs.wcsUpload(item, tokenRes.token, tokenRes.base);
                    uploadObj.putFile();
                    uploadObj.uploadProgress = function (info) {
                        $('.progress_'+index).text((info.total.percent).toFixed(2)+'%');
                        uploaderInfo.progress = info.total.percent;
                    }
                    uploadObj.onError = function (error) {
                        console.log(error);
                    }
                    uploadObj.onComplete = function(res){
                        batchFile[index].videourl = tokenRes.url;
                        batchFile[index].videoid = Date.now();
                        console.log(batchFile);
                        $.ajax({
                            type: "POST",
                            url: "<?php echo url('upfilm'); ?>",
                            dataType: 'JSON',
                            async: false,
                            data: batchFile[index],
                            success: function (res) {
                                batchUpRender.config.done(res, index, batchFile[index]);
                            },
                        });
                    }
                   
                    return;
                    
                    const tcVod = new TcVod.default({
                      getSignature: getSignature
                    })
                    // const tcVod = new TcVod.default({
                    //   getSignature: getSignature
                    // })
                    // const uploader = tcVod.upload({
                    //   videoFile: item,
                    // })
                    // uploader.on('video_progress', function(info) {
                    //   $('.progress_'+index).text((info.percent * 100).toFixed(2)+'%');
                    //   uploaderInfo.progress = info.percent;
                    // })
                    // uploader.on('video_upload', function(info) {
                    //   uploaderInfo.isVideoUploadSuccess = true;
                    // })
                    var uploaderInfo = {
                      videoInfo: uploader.videoInfo,
                      isVideoUploadSuccess: false,
                      isVideoUploadCancel: false,
                      progress: 0,
                      fileId: '',
                      videoUrl: '',
                      cancel: function() {
                        uploaderInfo.isVideoUploadCancel = true;
                        uploader.cancel()
                      },
                    }
                    uploader.done().then(function(res) {
                        
                        console.log(res);
                        batchFile[index].videourl = res.video.url;
                        batchFile[index].videoid = res.fileId;
                        $.ajax({
                            type: "POST",
                            url: "<?php echo url('upfilm'); ?>",
                            dataType: 'JSON',
                            async: false,
                            data: batchFile[index],
                            success: function (res) {
                                batchUpRender.config.done(res, index, batchFile[index]);
                            },
                        });
                    }).then(function (videoUrl) {
                    })

                });
                if (!tag)
                {
                    layer.close(load);
                    return false;
                }
                layer.close(load);
                $(this).addClass('layui-btn-disabled').attr('id','');
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
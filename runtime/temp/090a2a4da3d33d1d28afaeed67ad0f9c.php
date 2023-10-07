<?php /*a:1:{s:57:"/www/wwwroot/zhibb/application/h5/view/download/index.tpl";i:1614309756;}*/ ?>
<!doctype html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>下载-<?php echo APP_NAME; ?>-看直播,尽在<?php echo APP_PREFIX_NAME; ?>APP</title>
    <meta name="keywords" content="<?php echo APP_NAME; ?>,下载APP,Android,IOS,下载<?php echo APP_NAME; ?>"/>
    <meta name="description" content=""/>
    <meta id="viewport" content="width=device-width, user-scalable=no,initial-scale=1" name="viewport"/>
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta content="black" name="apple-mobile-web-app-status-bar-style"/>
    <meta content="telephone=no" name="format-detection"/>
    <link href="/static/vendor/animate.min.css?v=<?php echo config('upload.resource_version'); ?>" type="text/css" rel="stylesheet"/>
    <link href="<?php echo ('/static/'.strtolower(\think\facade\Request::module()).'/css'); ?>/icomoon/style.css?v=<?php echo config('upload.resource_version'); ?>" type="text/css" rel="stylesheet"/>
    <link href="<?php echo ('/static/'.strtolower(\think\facade\Request::module()).'/css'); ?>/download.css?v=<?php echo config('upload.resource_version'); ?>" type="text/css" rel="stylesheet"/>
    <link rel="stylesheet" type="text/css" href="/bx_static/custom.css"/>
    <script src="/static/vendor/jquery.min.js?v=<?php echo config('upload.resource_version'); ?>"></script>
    <script src="/static/vendor/smart/smart_lw.bundle.js?v=<?php echo config('upload.resource_version'); ?>"></script>
    <script src="/static/vendor/layer/mobile/layer.js?v=<?php echo config('upload.resource_version'); ?>"></script>
    <script type="application/ecmascript" src="/static/vendor/qrcode.min.js"></script>
    <style>

        *{margin:0; padding:0;}
        a{text-decoration: none;}
        img{max-width: 100%; height: auto;}
        .weixin-tip{display: none; position: fixed; left:0; top:0; bottom:0; background: rgba(0,0,0,0.8); filter:alpha(opacity=80);  height: 100%; width: 100%; z-index: 100;}
        .weixin-tip p{text-align: center; margin-top: 10%; padding:0 5%;}

        .open_tip {
            position: fixed;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 999;
            left: 0;
            top: 0;
            display: none;
        }

        .open_tip img {
            max-width: 100%;
            margin: 0;
        }
    </style>
</head>

<body style="overflow-x:hidden" class="bg_white">

    <div class="weixin-tip">
        <p>
            <img src="/static/common/image/default/weixin_to_browser.png" alt="微信打开"/>
        </p>
    </div>
    <div class="background_left"></div>
    <div class="background_right"></div>
    <div class="main download_container" style="background-color:white!important">
        <div class="download_content">
            <div class="content">
                <div class="product_box download_header download_pc">
                    <div class="download_wrapper">
                        <a href="javascript:;" class="product_logo">
                            <img src="<?php echo img_url('','200_200','logo'); ?>"/>
                        </a>
                        <a href="javascript:;" class="product_logo">
                            <div id="qrcode"></div>
                            <script type="text/javascript">
                                new QRCode(document.getElementById("qrcode"), window.location.href);  // 设置要生成二维码的链接
                            </script>
                        </a>
                    </div>
                    <div class="download_header_body">
                        <h1 class="download_title" style="padding:16px 0 20px"><?php echo config('app.product_setting.name'); ?></h1>
                        <p>扫描二维码下载</p>
                        <p>或用手机浏览器输入这个网址:  <span><?php echo htmlentities($self_url); ?></span></p>
                    </div>
                    <div class="version_info">
                        <p class="product_info">
                            <b><?php echo htmlentities($product_name); ?></b><br/>
                            <span class="span_gray">
                                最新版本：<span class="version"></span> &nbsp;&nbsp;&nbsp;<a class="check_desc" href="javascript:;">更新说明</a>
                            </span>
                        </p>
                    </div>
                    <p class="product_descr"><?php echo config('app.product_info.descr'); ?></p>
                    <a href="javascript:;" class="download_btn android_btn">下载安装</a>
                </div>
                <div class="product_box download_header download_app">
                    <a href="javascript:;" class="product_logo">
                        <img src="<?php echo img_url('','200_200','logo'); ?>" style="width:120px;"/>
                    </a>
                    <div class="download_header_body">
                        <div class="download_IOS_icon IOS_icon_toggle"></div>
                        <div class="download_Android_icon Android_icon_toggle"></div>
                        <h1 class="download_title" style="font-size:24px;"><?php echo config('app.product_setting.name'); ?></h1>
                    </div>
                    <div class="version_info">
                        <p class="product_info">
                            <span class="span_gray">
                                最新版本：<span class="version"></span> &nbsp;&nbsp;&nbsp;<a class="check_desc" href="javascript:;">更新说明</a>
                            </span>
                        </p>
                    </div>
                    <p class="product_descr"><?php echo config('app.product_info.descr'); ?></p>
                    <a href="javascript:;" class="download_btn android_btn">下载安装</a>
                    <div style="clear: both;"></div>
                </div>
            </div>
        </div>
        <div class="download_body">
            <div>
                <div class="download_IOS_icon"></div>
                <div class="download_IOS_font">
                    <p class="span_gray download_app_font_size">当前版本: <?php echo htmlentities($ios['version']); ?>  文件大小: 156.32 MB</p>
                    <p class="span_gray download_app_font_size">更新于: <?php echo htmlentities(date('Y-m-d H:i',!is_numeric($ios['create_time'])? strtotime($ios['create_time']) : $ios['create_time'])); ?></p>
                </div>
            </div>
            <div>
                <div class="download_Android_icon"></div>
                <div class="download_Android_font">
                    <p class="span_gray download_app_font_size">当前版本: <?php echo htmlentities($android['version']); ?>  文件大小: 156.32 MB</p>
                    <p class="span_gray download_app_font_size">更新于: <?php echo htmlentities(date('Y-m-d H:i',!is_numeric($android['create_time'])? strtotime($android['create_time']) : $android['create_time'])); ?></p>
                </div>
            </div>
        </div>
        <div class="footer_font">
            <div class="span_gray">Copyright 2018 - <?php echo date('Y'); ?> <?php echo htmlentities($product_name); ?>. All Rights Reserved</div>
        </div>
    </div>

    <div class="descr" style="display: none;"> <textarea style="resize:none;border: none;text-align: center" readonly><?php echo htmlentities($android['descr']); ?></textarea></div>
    <div class="iosdescr" style="display: none;"> <textarea style="resize:none;border: none;text-align: center" readonly><?php echo htmlentities($ios['descr']); ?></textarea></div>

<script>
    var isIOS = !!navigator.userAgent.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/); //ios终端
    var isAndroid = navigator.userAgent.indexOf('Android') > -1 || navigator.userAgent.indexOf('Linux') > -1; //安卓
    var apple_store = '<?php echo htmlentities($ios['url']); ?>';
    var qq_store = '<?php echo htmlentities($qq['url']); ?>';
    var file_path = '<?php echo htmlentities($android['file_path']); ?>';
    var android_id = '<?php echo htmlentities($android['id']); ?>';
    var android_desc = "";
    var browseros = "<?php echo htmlentities($browseros); ?>";
    var winHeight = $(window).height();

    var weixin_show = function () {
        $(".weixin-tip").css("height",winHeight);
        $(".weixin-tip").show();
    };

    //初始化版本信息
    if(isIOS){
        $('.version').text('IOS <?php echo htmlentities($ios['version']); ?>');
    	if(browseros == 'weixin'){weixin_show();};
        $('.download_body').css({'display':'none'});
        $('.download_content').css({'height':'100vh'});
        $('.download_title').css({'text-align':'center'});
        $('.IOS_icon_toggle').show();
    }else if(isAndroid){
        $('.version').text('Android <?php echo htmlentities($android['version']); ?>');
        if(browseros == 'weixin'){weixin_show()};
        $('.download_body').css({'display':'none'});
        $('.download_content').css({'height':'100vh'});
        $('.download_title').css({'text-align':'center'});
        $('.Android_icon_toggle').show();
    }else{
        $('.version').text('Android <?php echo htmlentities($android['version']); ?>');
        if(browseros == 'weixin'){weixin_show();};
    }

    //更新说明
    $('.check_desc').click(function ()
    {
        if(isIOS) {
            layer.open({
                content: $('.iosdescr').html()
                , btn: '我知道了'
            });
        } else {
            layer.open({
                content: $('.descr').html()
                , btn: '我知道了'
            });
        }
    });

    //点击下载
    $('.download_btn').click(function () {
    //alert(browseros);
		if(browseros == 'weixin'){
                if (qq_store && qq_store != '') {
                    location.href = qq_store;
                }else {
                    weixin_show();
                }
                return;
		}
		if(isIOS){
                if (apple_store == '' || !apple_store) {
                    $s.msg('请在Apple Store内下载');
                    return false;
                }
                location.href = apple_store;
		}else{
                if ($s.empty(file_path) && qq_store == '') {
                    $s.msg('暂无安装包');
                    return;
                }
                if (file_path != ''){
                    location.href = file_path;
                    upload();
                    return;
                }
                if (qq_store && qq_store != '') {
                    location.href = qq_store;
                }
		}

    });


    $('.weixin-tip').click(function () {
        $(this).hide();
    })


    function upload() {
        if ($s.empty(android_id) || android_id == '0') {
            return false;
        }
        $s.post('<?php echo url("download/incr"); ?>', {id: android_id}, function () {
        });
    }

    function return2Br(str) {
        return str.replace(/\n/g,"<br/>");
    }

</script>

</body>
</html>
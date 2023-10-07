<!doctype html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>下载-{:APP_NAME}-看直播,尽在{:APP_PREFIX_NAME}APP</title>
    <meta name="keywords" content="{:APP_NAME},下载APP,Android,IOS,下载{:APP_NAME}"/>
    <meta name="description" content=""/>
    <meta id="viewport" content="width=device-width, user-scalable=no,initial-scale=1" name="viewport"/>
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta content="black" name="apple-mobile-web-app-status-bar-style"/>
    <meta content="telephone=no" name="format-detection"/>
    <link href="__VENDOR__/animate.min.css?v=__RV__" type="text/css" rel="stylesheet"/>
    <link href="__CSS__/icomoon/style.css?v=__RV__" type="text/css" rel="stylesheet"/>
    <link href="__CSS__/download.css?v=__RV__" type="text/css" rel="stylesheet"/>
    <link rel="stylesheet" type="text/css" href="/bx_static/custom.css"/>
    <script src="__VENDOR__/jquery.min.js?v=__RV__"></script>
    <script src="__VENDOR__/smart/smart_lw.bundle.js?v=__RV__"></script>
    <script src="__VENDOR__/layer/mobile/layer.js?v=__RV__"></script>
    <script type="application/ecmascript" src="__VENDOR__/qrcode.min.js"></script>
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
            <img src="__STATIC__/common/image/default/weixin_to_browser.png" alt="微信打开"/>
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
                            <img src="{:img_url('','200_200','logo')}"/>
                        </a>
                        <a href="javascript:;" class="product_logo">
                            <div id="qrcode"></div>
                            <script type="text/javascript">
                                new QRCode(document.getElementById("qrcode"), window.location.href);  // 设置要生成二维码的链接
                            </script>
                        </a>
                    </div>
                    <div class="download_header_body">
                        <h1 class="download_title" style="padding:16px 0 20px">{:config('app.product_setting.name')}</h1>
                        <p>扫描二维码下载</p>
                        <p>或用手机浏览器输入这个网址:  <span>{$self_url}</span></p>
                    </div>
                    <div class="version_info">
                        <p class="product_info">
                            <b>{$product_name}</b><br/>
                            <span class="span_gray">
                                最新版本：<span class="version"></span> &nbsp;&nbsp;&nbsp;<a class="check_desc" href="javascript:;">更新说明</a>
                            </span>
                        </p>
                    </div>
                    <p class="product_descr">{:config('app.product_info.descr')}</p>
                    <a href="javascript:;" class="download_btn android_btn">下载安装</a>
                </div>
                <div class="product_box download_header download_app">
                    <a href="javascript:;" class="product_logo">
                        <img src="{:img_url('','200_200','logo')}" style="width:120px;"/>
                    </a>
                    <div class="download_header_body">
                        <div class="download_IOS_icon IOS_icon_toggle"></div>
                        <div class="download_Android_icon Android_icon_toggle"></div>
                        <h1 class="download_title" style="font-size:24px;">{:config('app.product_setting.name')}</h1>
                    </div>
                    <div class="version_info">
                        <p class="product_info">
                            <span class="span_gray">
                                最新版本：<span class="version"></span> &nbsp;&nbsp;&nbsp;<a class="check_desc" href="javascript:;">更新说明</a>
                            </span>
                        </p>
                    </div>
                    <p class="product_descr">{:config('app.product_info.descr')}</p>
                    <a href="javascript:;" class="download_btn android_btn">下载安装</a>
                    <div style="clear: both;"></div>
                </div>
            </div>
        </div>
        <div class="download_body">
            <div>
                <div class="download_IOS_icon"></div>
                <div class="download_IOS_font">
                    <p class="span_gray download_app_font_size">当前版本: {$ios.version}  文件大小: 156.32 MB</p>
                    <p class="span_gray download_app_font_size">更新于: {$ios.create_time|date='Y-m-d H:i'}</p>
                </div>
            </div>
            <div>
                <div class="download_Android_icon"></div>
                <div class="download_Android_font">
                    <p class="span_gray download_app_font_size">当前版本: {$android.version}  文件大小: 156.32 MB</p>
                    <p class="span_gray download_app_font_size">更新于: {$android.create_time|date='Y-m-d H:i'}</p>
                </div>
            </div>
        </div>
        <div class="footer_font">
            <div class="span_gray">Copyright 2018 - {:date('Y')} {$product_name}. All Rights Reserved</div>
        </div>
    </div>

    <div class="descr" style="display: none;"> <textarea style="resize:none;border: none;text-align: center" readonly>{$android.descr}</textarea></div>
    <div class="iosdescr" style="display: none;"> <textarea style="resize:none;border: none;text-align: center" readonly>{$ios.descr}</textarea></div>

<script>
    var isIOS = !!navigator.userAgent.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/); //ios终端
    var isAndroid = navigator.userAgent.indexOf('Android') > -1 || navigator.userAgent.indexOf('Linux') > -1; //安卓
    var apple_store = '{$ios.url}';
    var qq_store = '{$qq.url}';
    var file_path = '{$android.file_path}';
    var android_id = '{$android.id}';
    var android_desc = "";
    var browseros = "{$browseros}";
    var winHeight = $(window).height();

    var weixin_show = function () {
        $(".weixin-tip").css("height",winHeight);
        $(".weixin-tip").show();
    };

    //初始化版本信息
    if(isIOS){
        $('.version').text('IOS {$ios.version}');
    	if(browseros == 'weixin'){weixin_show();};
        $('.download_body').css({'display':'none'});
        $('.download_content').css({'height':'100vh'});
        $('.download_title').css({'text-align':'center'});
        $('.IOS_icon_toggle').show();
    }else if(isAndroid){
        $('.version').text('Android {$android.version}');
        if(browseros == 'weixin'){weixin_show()};
        $('.download_body').css({'display':'none'});
        $('.download_content').css({'height':'100vh'});
        $('.download_title').css({'text-align':'center'});
        $('.Android_icon_toggle').show();
    }else{
        $('.version').text('Android {$android.version}');
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
        $s.post('{:url("download/incr")}', {id: android_id}, function () {
        });
    }

    function return2Br(str) {
        return str.replace(/\n/g,"<br/>");
    }

</script>

</body>
</html>
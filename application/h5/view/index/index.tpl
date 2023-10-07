<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="charset" content="utf-8">
    <meta name="viewport"
          content="initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no, width=device-width">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <meta name="application-name" content="{$product_name}">
    <title>{$product_name}_移动版官网</title>
    <meta name="description" content="{$product_name}_移动版官网，{$bean_name}充值，{$product_name}下载">
    <meta name="keywords" content="{$product_name}官网H5版，{$bean_name}充值，{$product_name}下载">
    <link rel="stylesheet" type="text/css" href="__VENDOR__/animate.min.css?v=__RV__"/>
    <link rel="stylesheet" type="text/css" href="__CSS__/index/recharge.css?v=__RV__"/>
    <link rel="stylesheet" type="text/css" href="/bx_static/h5.css"/>
    <script src="__VENDOR__/jquery.min.js?v=__RV__"></script>
    <script src="__VENDOR__/layer/mobile/layer.js?v=__RV__"></script>
    <script src="/bx_static/changeFont.js"></script>
    <style>
        .main {
            padding: 20px;
        }

        .product_logo {
            width: 200px;
            display: block;
            margin: 0 auto;
        }

        .product_logo img {
            max-width: 100%;
            border-radius: 5px;
        }

        .product_info {
            text-align: center;
            font-size: 16px;
            margin-top: 20px;
        }

        .product_descr {
            font-size: 14px;
            line-height: 26px;
            margin-top: 10px;
        }

        .btn, .btn2 {
            display: block;
            margin-top: 20px;
        }
    </style>
</head>
<body class="help_index">
    <div class="nav">
        <div class="icon"></div>
        <div class="font">{$product_name}_移动版官网</div>
    </div>
    <div class="main">
        <div class="product_box">
            <p class="product_info">
                {$product_name}<br/>
            </p>
            <p class="product_descr">
                {$product_name}{$slogan}
            </p>
        </div>
        <a class="btn" href="{:url('recharge/index')}">充值中心</a>
        <a class="btn2" href="{:url('download/index')}">下载中心</a>
    </div>
    <div class="footer_font">
        <div class="span_gray">Copyright 2018 - {:date('Y')} {$product_name}. All Rights Reserved</div>
    </div>
    <script>
        $(function () {
            setTimeout(function () {
                layer();
            }, 1000);
            function layer(){
                var hh=$(window).height();
                var top=hh-$('.main').outerHeight()-$('.bottom').height();
                if(top>0){
                    $('.bottom').css({'margin-top':top});
                }
            }
            layer();
        });
    </script>
</body>
</html>
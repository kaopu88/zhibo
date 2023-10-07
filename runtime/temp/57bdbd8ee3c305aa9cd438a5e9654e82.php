<?php /*a:1:{s:54:"/www/wwwroot/zhibb/application/h5/view/index/index.tpl";i:1594812202;}*/ ?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="charset" content="utf-8">
    <meta name="viewport"
          content="initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no, width=device-width">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <meta name="application-name" content="<?php echo htmlentities($product_name); ?>">
    <title><?php echo htmlentities($product_name); ?>_移动版官网</title>
    <meta name="description" content="<?php echo htmlentities($product_name); ?>_移动版官网，<?php echo htmlentities($bean_name); ?>充值，<?php echo htmlentities($product_name); ?>下载">
    <meta name="keywords" content="<?php echo htmlentities($product_name); ?>官网H5版，<?php echo htmlentities($bean_name); ?>充值，<?php echo htmlentities($product_name); ?>下载">
    <link rel="stylesheet" type="text/css" href="/static/vendor/animate.min.css?v=<?php echo config('upload.resource_version'); ?>"/>
    <link rel="stylesheet" type="text/css" href="<?php echo ('/static/'.strtolower(\think\facade\Request::module()).'/css'); ?>/index/recharge.css?v=<?php echo config('upload.resource_version'); ?>"/>
    <link rel="stylesheet" type="text/css" href="/bx_static/h5.css"/>
    <script src="/static/vendor/jquery.min.js?v=<?php echo config('upload.resource_version'); ?>"></script>
    <script src="/static/vendor/layer/mobile/layer.js?v=<?php echo config('upload.resource_version'); ?>"></script>
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
        <div class="font"><?php echo htmlentities($product_name); ?>_移动版官网</div>
    </div>
    <div class="main">
        <div class="product_box">
            <p class="product_info">
                <?php echo htmlentities($product_name); ?><br/>
            </p>
            <p class="product_descr">
                <?php echo htmlentities($product_name); ?><?php echo htmlentities($slogan); ?>
            </p>
        </div>
        <a class="btn" href="<?php echo url('recharge/index'); ?>">充值中心</a>
        <a class="btn2" href="<?php echo url('download/index'); ?>">下载中心</a>
    </div>
    <div class="footer_font">
        <div class="span_gray">Copyright 2018 - <?php echo date('Y'); ?> <?php echo htmlentities($product_name); ?>. All Rights Reserved</div>
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
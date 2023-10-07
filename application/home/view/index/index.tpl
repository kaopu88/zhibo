<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{:APP_NAME}-看直播,玩视频,尽在{:APP_PREFIX_NAME}APP</title>
    <meta name="keywords" content="{$site.company_keyword}" />
    <meta name="description" content="{$site.company_description}" />
    <link rel="stylesheet" href="__NEWSTATIC__/layui.css">
    <link rel="stylesheet" href="__NEWSTATIC__/home/css/home.css">
</head>

<body>
<div class="home_bg">
    <div class="layui-carousel" id="test1">
        <div carousel-item>
            <div class="img1"></div>
            <div class="img3"></div>
        </div>
    </div>
    <div class="container"  style="position: relative;">
        <nav class="layui-row layui-col-md-12">
            <ul>
                <li>
                    <img src="{:img_url('', '', 'home_logo')}" alt="">
                </li>
                <li class="title">
                    <a href="/">首页</a>
                    <a href="{:url('aboutus')}">关于我们</a>
                    <a href="{:url('contactus')}">联系我们</a>
                </li>
            </ul>
        </nav>
        <div class="main">
            <div class="top">
                <div class="title">
                    <h3>{$site.company_name},</h3>
                    <h3>{$site.site_slogan}</h3>
                </div>
                <div class="center">
                    <div class="download" style=" background-image: url({$site.qrcode_download});"></div>
                    <div class="btn">
                        <div>
                            <span class="appstore"></span>
                            <a href="{$site.apple_store}"><span class="app_font">AppStore</span></a>
                        </div>
                        <div>
                            <span class="android"></span>
                            <a href="{$site.qq_store}"><span class="app_font">Android</span> </a>
                        </div>
                    </div>
                </div>
                <div class="footer">
                    <span class="scan" >扫描下载</span>
                </div>
            </div>
            <footer class="layui-row layui-col-md-12">
                <div class="first-row">
                    <a href="{:url('artInfo',['mark'=>'app_privacy'])}"><span class="m-r-26">隐私政策</span></a>
                    <a href="{:url('artInfo',['mark'=>'agreement'])}"> <span class="m-r-26">服务条款</span></a>
                    <a href="{:url('contactus')}"> <span class="m-r-26">联系我们</span></a>
                    <a href="{:url('/h5/recharge/index')}"><span class="m-r-26">在线支付</span></a>
                </div>
                <div>
                    <span>&copy;{$site.company_full_name}</span>
                    <span>客服热线：{$site.contact_tel}</span>
                </div>
                <div>
                    <span>版权所有2023-2024保留所有权利。{$site.idc_num}</span>
                </div>
            </footer>
        </div>
    </div>
</div>
<script src="__NEWSTATIC__/layui.js"></script>
<script src="__NEWSTATIC__/home/js/carousel.js"></script>
</body>

</html>
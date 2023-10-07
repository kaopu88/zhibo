<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{:APP_NAME}-{$site.site_slogan}</title>
    <link rel="stylesheet" href="__NEWSTATIC__/layui.css">
    <link rel="stylesheet" href="__NEWSTATIC__/home/css/home.css">
</head>

<body>
    <div class="contact_bg">
        <div class="layui-carousel" id="test2">
            <div carousel-item>
                <div class="img_1"></div>
                <div class="img_2"></div>
                <div class="img_3"></div>
            </div>
        </div>
        <div class="container" style="position: relative;">
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
            <div class="contact_main">
                <div class="content">
                    <div class="content_text">
                        <div>
                            <h1 class="font_us">{$_info.title}</h1>
                        </div>
                        <div class="contact_way">
                            {:htmlspecialchars_decode($_info.content)}
                        </div>

                    </div>

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
                    <span>版权所有2019-2020保留所有权利。{$site.idc_num}</span>
                </div>
            </footer>
        </div>
    </div>
    <script src="__NEWSTATIC__/layui.js"></script>
    <script src="__NEWSTATIC__/home/js/carousel.js"></script>
</body>

</html>
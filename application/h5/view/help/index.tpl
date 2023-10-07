<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>帮助中心</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0"/>
    <meta name="apple-mobile-web-app-capable" content="yes"/>
    <meta name="apple-mobile-web-app-status-bar-style" content="black"/>
    <meta name="format-detection" content="telephone=no"/>
    <link rel="stylesheet" href="__CSS__/common.css?v=__RV__">
    <link rel="stylesheet" type="text/css" href="/bx_static/h5.css"/>
    <link rel="stylesheet" type="text/css" href="/bx_static/layui.css"/>
    <script type="text/javascript" src="__VENDOR__/jquery.min.js?v=__RV__"></script>
    <script src="/bx_static/help.js"></script>
</head>
<body>
<div class="help-wrap">
    <div class="container">
        <div class="help-box">
            <div class="bd">
                <volist name="cat_list" id="cat">
                    <eq name="cat['display']" value="1">
                        <div class="help-block close">
                            <div class="hleft">
                                <img src="{$cat.descr}" class="cat-img"/>
                                <p class="tit">{$cat.name}</p>
                                <span class="trigger trigger_down"></span>
                            </div>
                            <div class="hright">
                                <volist name="cat['art_list']" id="art">
                                    <a href="{:url('help/detail',array('id'=>$art['id'],'version'=>input('version')))}">{$art.title}</a>
                                </volist>
                            </div>
                        </div>
                    </eq>
                </volist>
            </div>
        </div>
    </div>
</div>
<!-- /container -->
<script type="text/javascript" src="__JS__/mooer.swiper.js?v=__RV__"></script>
<script type="text/javascript" charset="utf-8">
    $(document).ready(function () {
        $(".trigger").click(function () {
            $(this).parent().parent().toggleClass("active").next().removeClass("active");
            return false;
        });

        var ntsSlider = new Swiper('.notice-box .cnt', {
            direction: 'vertical',
            slidesPerView: 1,
            loop: true,
            autoplay: 2000,
            autoplayDisableOnInteraction: false,
            //autoplay : 1000,
            //autoplayDisableOnInteraction : false,
        })
    });
</script>
</body>
</html>
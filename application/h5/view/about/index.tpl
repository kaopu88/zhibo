<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>关于我们</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0"/>
    <meta name="apple-mobile-web-app-capable" content="yes"/>
    <meta name="apple-mobile-web-app-status-bar-style" content="black"/>
    <meta name="format-detection" content="telephone=no"/>
    <link rel="stylesheet" href="__CSS__/common.css?v=__RV__">
    <link rel="stylesheet" type="text/css" href="/bx_static/h5.css"/>
</head>
<style>
    body{
        font-family: Arial,Microsoft YaHei,"\9ED1\4F53","\5B8B\4F53",sans-serif;
    }
}
</style>
<body>
<div class="about-wrap bg_white">
    <div class="container_about">
        <div class="about-logo">
            <img style="border-radius: 10%;" src="{:img_url('','200_200','logo')}" alt="{:APP_NAME}"/>
        </div>
        <div class="about-list clearfix">
            <volist name="_list" id="vo">
                <a href="{:url('about/detail',array('id'=>$vo['id'], 'version'=>input('version')))}">{$vo['title']}</a>
            </volist>
        </div>
        <div class="bottom about_footer">Copyright 2018 - {:date('Y')} {$product_name}. All Rights Reserved</div>
    </div>
</div>
<script src="/bx_static/media_auto.js"></script>
</body>
</html>

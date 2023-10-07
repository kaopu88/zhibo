<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{$content.title}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0"/>
    <meta name="apple-mobile-web-app-capable" content="yes"/>
    <meta name="apple-mobile-web-app-status-bar-style" content="black"/>
    <meta name="format-detection" content="telephone=no"/>
    <link rel="stylesheet" href="__H5__/css/common.css?v=__RV__">
</head>
<body>
<div class="page-wrap">
    <div class="container">
        <div style="margin-top: 20px;width: 92%;margin: 20px auto 0px;">
            <h3 style="font-size: 22px;color: #000000;text-align: justify;font-family: Microsoft YaHei;line-height: 28px;">
                {$content.title}</h3>
        </div>
        <div style="margin-top: 13px;display:box;box-align: start;box-pack: justify;display: -webkit-box;-webkit-box-align: start;-webkit-box-pack: justify;margin-bottom: 15px;">
            <div style="padding-left: 2rem;">
                <span style="font-size: 13px;color: #909090;font-family: Microsoft YaHei;margin-right: 10px;">{$content.create_time|date='Y-m-d'}</span>
                <span style="font-size: 13px;color: #909090;font-family: Microsoft YaHei;">{:APP_NAME}</span>
            </div>
        </div>
    </div>
    <div class="container">
        <div class="page-content">
            {:html_entity_decode($content.content)}
        </div>
    </div>
</div>
<!-- /container -->
<script type="text/javascript" src="__VENDOR__/jquery.min.js?v=__RV__"></script>
<script type="text/javascript" src="__H5__/js/responsive-tables.js?v=__RV__"></script>
</body>
</html>
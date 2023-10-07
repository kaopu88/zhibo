<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>直播头条</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" />
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-status-bar-style" content="black" />
    <meta name="format-detection" content="telephone=no" />
    <link rel="stylesheet" href="__CSS__/common.css?v=__RV__">
    <style>
        .app_title { margin: 0 1rem 1rem 1rem; background-color: #fff;color: #525252;padding-top: 1rem;text-align: justify;text-justify: distribute-all-lines; }
        .app_title h2 {font-size: 2rem; line-height: 2.8rem; margin: 0; padding: .5rem .7rem; color: #333; }
        .app_title .page-bar {   position: relative;  display: flex; flex-wrap: nowrap; padding: .5rem 0; }
        .app_title .page-bar .avatar { width: auto; text-align: center;flex: 0 1 auto;display: block;box-sizing: border-box; padding:0 .5rem;  }
        .app_title .page-bar .avatar img { display: block; width:2rem; border-radius: 50%; }
        .app_title .page-bar .author { width: 50%; flex: 0 1 auto;display: block;box-sizing: border-box; line-height: 2rem; }
        .app_title .page-bar .author .t1 { font-size: 1.6rem; font-weight: 500; }
        .app_title .page-bar .author .t2 { font-size: 1.4rem; color: #b0b0b0; }
        .app_title .page-bar .pvs { position: absolute; right: 0; bottom:0; font-size: 1.4rem; color: #b0b0b0; }

        .page-content { margin: 0 1rem 1rem 1rem !important; }
        .page-content p { line-height: 2.2rem; }
        .page-content video {
            max-width: 100%;
            width: auto !important;
            height: auto !important;
        }
        .page-content table { width: auto; }
        .page-content img{
            max-width: 100%;
            width: auto !important;
            height: auto !important;
        }
    </style>
</head>
<body>
<div class="page-wrap">
    <div class="container">
        <div class="app_title">
            <h2>
                {:html_entity_decode($_info.title)}
            </h2>
            <div class="page-bar clearfix">
                <div class="avatar">
                    <img src="{$_info.logo}">
                </div>
                <div class="author">
                    <div class="t1">{$_info.author}</div>
                </div>
                <div class="pvs">{$_info.pv}阅读</div>
            </div>
        </div>
        <div class="page-content">
            {:html_entity_decode($_info.content)}
        </div>
    </div>
</div>
<!-- /container -->
<script type="text/javascript" src="__VENDOR__/jquery.min.js?v=__RV__"></script>
<script type="text/javascript" src="__JS__/responsive-tables.js?v=__RV__"></script>
</body>
</html>
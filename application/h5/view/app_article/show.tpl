<!DOCTYPE html>
<html lang="en">
<head>
    <title>{$_info.title}</title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta id="viewport" content="width=device-width, user-scalable=no,initial-scale=1" name="viewport"/>
    <meta name="apple-mobile-web-app-capable" content="yes">
    <link href="__STATIC__/h5/css/app_article/app_article.css" type="text/css" rel="stylesheet" />
    <script src="__VENDOR__/jquery.min.js?v=__RV__"></script>
    <script src="__VENDOR__/bugujsdk.js?v=__RV__"></script>
</head>
<body>
<section class="article_content">
    {:htmlspecialchars_decode($_info.content)}
</section>
</body>
</html>
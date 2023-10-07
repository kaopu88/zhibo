<?php /*a:1:{s:59:"/www/wwwroot/zhibb/application/h5/view/app_article/show.tpl";i:1554858446;}*/ ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title><?php echo htmlentities($_info['title']); ?></title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta id="viewport" content="width=device-width, user-scalable=no,initial-scale=1" name="viewport"/>
    <meta name="apple-mobile-web-app-capable" content="yes">
    <link href="/static/h5/css/app_article/app_article.css" type="text/css" rel="stylesheet" />
    <script src="/static/vendor/jquery.min.js?v=<?php echo config('upload.resource_version'); ?>"></script>
    <script src="/static/vendor/bugujsdk.js?v=<?php echo config('upload.resource_version'); ?>"></script>
</head>
<body>
<section class="article_content">
    <?php echo htmlspecialchars_decode($_info['content']); ?>
</section>
</body>
</html>
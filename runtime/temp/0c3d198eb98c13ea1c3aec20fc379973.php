<?php /*a:2:{s:61:"/www/wwwroot/zhibb/application/h5/view/cover_star/explain.tpl";i:1561095900;s:54:"/www/wwwroot/zhibb/application/h5/view/public/head.tpl";i:1595042494;}*/ ?>
<!DOCTYPE html>
<html lang="en" data-dpr="1">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta http-equiv="x-dns-prefetch-control" content="on" />
    <meta name="renderer" content="webkit" />
    <meta name="applicable-device" content="pc" />
    <meta http-equiv="X-UA-Compatible" content="IE=Edge" />
    <meta name="application-name" content="" />
    <meta name="renderer" content="webkit" />
    <meta name="format-detection" content="telephone=no" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, minimum-scale=1, user-scalable=no, viewport-fit=cover" />
    <meta name="keywords" content=""/>
    <meta name="description" content=""/>

<title><?php echo htmlentities($explain['title']); ?></title>
<link rel="stylesheet" href="/static/h5/css/cover_star/common.css?<?php echo date('YmdHis'); ?>">
<script src="/static/h5/js/css-base.js?v=<?php echo config('upload.resource_version'); ?>"></script>
</head>
<body>

<div class="page-wrap">
    <div class="container">
        <div class="page-content">
            <?php echo html_entity_decode($explain['content']); ?>
        </div>
    </div>
</div>

</body>
</html>
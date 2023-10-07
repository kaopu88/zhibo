<?php /*a:1:{s:54:"/www/wwwroot/zhibb/application/h5/view/about/index.tpl";i:1594212992;}*/ ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>关于我们</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0"/>
    <meta name="apple-mobile-web-app-capable" content="yes"/>
    <meta name="apple-mobile-web-app-status-bar-style" content="black"/>
    <meta name="format-detection" content="telephone=no"/>
    <link rel="stylesheet" href="<?php echo ('/static/'.strtolower(\think\facade\Request::module()).'/css'); ?>/common.css?v=<?php echo config('upload.resource_version'); ?>">
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
            <img style="border-radius: 10%;" src="<?php echo img_url('','200_200','logo'); ?>" alt="<?php echo APP_NAME; ?>"/>
        </div>
        <div class="about-list clearfix">
            <?php if(is_array($_list) || $_list instanceof \think\Collection || $_list instanceof \think\Paginator): $i = 0; $__LIST__ = $_list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
                <a href="<?php echo url('about/detail',array('id'=>$vo['id'], 'version'=>input('version'))); ?>"><?php echo htmlentities($vo['title']); ?></a>
            <?php endforeach; endif; else: echo "" ;endif; ?>
        </div>
        <div class="bottom about_footer">Copyright 2018 - <?php echo date('Y'); ?> <?php echo htmlentities($product_name); ?>. All Rights Reserved</div>
    </div>
</div>
<script src="/bx_static/media_auto.js"></script>
</body>
</html>

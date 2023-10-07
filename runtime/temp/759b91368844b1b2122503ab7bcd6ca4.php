<?php /*a:1:{s:55:"/www/wwwroot/zhibb/application/h5/view/about/detail.tpl";i:1594212992;}*/ ?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title><?php echo htmlentities($_info['title']); ?></title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" />
		<meta name="apple-mobile-web-app-capable" content="yes" />
		<meta name="apple-mobile-web-app-status-bar-style" content="black" />
		<meta name="format-detection" content="telephone=no" />
		<link rel="stylesheet" href="<?php echo ('/static/'.strtolower(\think\facade\Request::module()).'/css'); ?>/common.css?v=<?php echo config('upload.resource_version'); ?>">
		<link rel="stylesheet" type="text/css" href="/bx_static/h5.css"/>
		<script src="/bx_static/changeFont.js"></script>
    </head>
<body>
	<div class="page-wrap bg_white">	
	<div class="container_about_detail">	
	   <div class="page-content" style="padding:0.1rem;font-size:0.16rem;">
		     <?php echo html_entity_decode($_info['content']); ?>
		</div>
	</div>	
	</div>
	<!-- /container -->
	<script type="text/javascript" src="/static/vendor/jquery.min.js?v=<?php echo config('upload.resource_version'); ?>"></script>
	<script type="text/javascript" src="<?php echo ('/static/'.strtolower(\think\facade\Request::module()).'/js'); ?>/responsive-tables.js?v=<?php echo config('upload.resource_version'); ?>"></script>
</body>
</html>
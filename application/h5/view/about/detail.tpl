<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>{$_info.title}</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" />
		<meta name="apple-mobile-web-app-capable" content="yes" />
		<meta name="apple-mobile-web-app-status-bar-style" content="black" />
		<meta name="format-detection" content="telephone=no" />
		<link rel="stylesheet" href="__CSS__/common.css?v=__RV__">
		<link rel="stylesheet" type="text/css" href="/bx_static/h5.css"/>
		<script src="/bx_static/changeFont.js"></script>
    </head>
<body>
	<div class="page-wrap bg_white">	
	<div class="container_about_detail">	
	   <div class="page-content" style="padding:0.1rem;font-size:0.16rem;">
		     {:html_entity_decode($_info.content)}
		</div>
	</div>	
	</div>
	<!-- /container -->
	<script type="text/javascript" src="__VENDOR__/jquery.min.js?v=__RV__"></script>
	<script type="text/javascript" src="__JS__/responsive-tables.js?v=__RV__"></script>
</body>
</html>
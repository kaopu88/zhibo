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
		<script type="text/javascript" src="__VENDOR__/jquery.min.js?v=__RV__"></script>
		<script src="/bx_static/help.js"></script>
    </head>
<body>
	<div class="page-wrap">	
	<div class="container" style="padding-bottom:0">	
	  <div class="page-content">
			<div class="title">
				{:html_entity_decode($_info.title)}
			</div>
			<div class="content">
		    {:html_entity_decode($_info.content)}
			</div>
		</div>

	</div>	
	</div>
	<!-- /container -->
	<script type="text/javascript" src="__JS__/responsive-tables.js?v=__RV__"></script>
</body>
</html>
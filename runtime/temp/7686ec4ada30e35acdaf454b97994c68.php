<?php /*a:1:{s:59:"/www/wwwroot/zhibb/application/h5/view/live/live_slider.tpl";i:1662025814;}*/ ?>
<!doctype html>
<html>

<head>
	<meta charset="utf-8">
	<title></title>
	<meta name="viewport" content="width=device-width,initial-scale=1,minimum-scale=1,maximum-scale=1,user-scalable=no" />
	<link href="/static/vendor/mui/css/mui.min.css" rel="stylesheet" />
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Swiper/4.0.2/css/swiper.min.css">

	<style>
		html, body{
			background-color: transparent !important;
		}
		.mui-slider-item a{
			width: 100%;
			height: 100%;
			display: inline-block;
		}

		.slider-common{
			height: <?php echo htmlentities($css['h']); ?>;
			width: <?php echo htmlentities($css['w']); ?>;
		}


		.slider-content{
			position: relative;
			left: 0;
			right: 0;
			top: 8px;
			margin: auto;
		}

		.circle{
			-webkit-border-radius: 100%;
			-moz-border-radius: 100%;
			border-radius: 100%;
			overflow: hidden;
			position: relative;
			z-index: -7;
			text-align: center;
			left: 0;
			top: 4px;
			right: 0;
			margin: auto;
			width: <?php echo htmlentities($css['w']); ?>;
			height: <?php echo htmlentities($css['h']); ?>;
			background: url(/static/h5/images/setting/task_back.png) no-repeat center;
			background-size: 90%;
		}

		.circle .wave-num{
			width: 100%;
			overflow: hidden;
			-webkit-border-radius: 50%;
			border-radius: 50%;
			text-align: center;
			vertical-align: middle;
			position: absolute;
			left: 0;
			top: 0;
			z-index: 999;
			display: flex;
			align-items: center;
			justify-content: center;
			flex-direction: column;
			height: 100%;
		}

		.circle .wave-num b{
			font-size: 10px;
			color: #a764a6;
		}

		.circle .wave-num span{
			font-size: 16px;
			position: absolute;
			bottom: 20px;
		}

		.wave{
			width: 50%;
			height: 100%;
			-webkit-border-radius: 100%;
			-moz-border-radius: 100%;
			border-radius: 100%;
			overflow: hidden;
			position: relative;
			z-index: 0;
			margin: 0 auto;
		}

		.wave-icon {
			width: 400px;
			min-height: 38%;
			position: absolute;
			background: url(/static/h5/images/setting/wave.png) no-repeat;
			left: 0;
			bottom: 0;
			animation: move_wave 1s linear infinite;
			-webkit-animation: move_wave 2s linear infinite;
			opacity: 0.55;
		}

		@-webkit-keyframes move_wave {
			0% {
				transform: translateX(0);
				-ms-transform: translateX(0);
				-moz-transform: translateX(0);
				-webkit-transform: translateX(0);
				-o-transform: translateX(0);
			}
			25% {
				transform: translateX(-15%);
				-ms-transform: translateX(-15%);
				-moz-transform: translateX(-15%);
				-webkit-transform: translateX(-15%);
				-o-transform: translateX(-15%);
			}
			50% {
				transform: translateX(-25%);
				-ms-transform: translateX(-25%);
				-moz-transform: translateX(-25%);
				-webkit-transform: translateX(-25%);
				-o-transform: translateX(-25%);
			}
			75% {
				transform: translateX(-35%);
				-ms-transform: translateX(-35%);
				-moz-transform: translateX(-35%);
				-webkit-transform: translateX(-35%);
				-o-transform: translateX(-35%);
			}
			100% {
				transform: translateX(-50%);
				-ms-transform: translateX(-50%);
				-moz-transform: translateX(-50%);
				-webkit-transform: translateX(-50%);
				-o-transform: translateX(-50%);
			}
		}

		@keyframes move_wave {
			0% {
				transform: translateX(0);
				-ms-transform: translateX(0);
				-moz-transform: translateX(0);
				-webkit-transform: translateX(0);
				-o-transform: translateX(0);
			}
			25% {
				transform: translateX(-15%);
				-ms-transform: translateX(-15%);
				-moz-transform: translateX(-15%);
				-webkit-transform: translateX(-15%);
				-o-transform: translateX(-15%);
			}
			50% {
				transform: translateX(-25%);
				-ms-transform: translateX(-25%);
				-moz-transform: translateX(-25%);
				-webkit-transform: translateX(-25%);
				-o-transform: translateX(-25%);
			}
			75% {
				transform: translateX(-35%);
				-ms-transform: translateX(-35%);
				-moz-transform: translateX(-35%);
				-webkit-transform: translateX(-35%);
				-o-transform: translateX(-35%);
			}
			100% {
				transform: translateX(-50%);
				-ms-transform: translateX(-50%);
				-moz-transform: translateX(-50%);
				-webkit-transform: translateX(-50%);
				-o-transform: translateX(-50%);
			}
		}


		.task-title-img{
			text-align: center;
			margin-top: -14px;
		}

		.task-title-img>img{
			width: <?php echo htmlentities($css['w']); ?>;
		}

		.swiper-pagination-bullet{
			width: 4px;
			height: 4px;
			margin: 0 2px !important;
			box-shadow: 0 0 2px #4d4242;
		}

		.swiper-pagination-bullet-active{
			background: #ececec !important;
		}

		.swiper-pagination{
			bottom: 0 !important;
		}

		.content{
			position: absolute;
			top: 0;
			left: 0;
			margin: 0 auto;
			right: 0;
			width: <?php echo htmlentities($css['w']); ?>;
			height: <?php echo htmlentities($css['h']); ?>;
		}

		.content p{
			margin: 0 0 0 2px;
			color: #fff;
			height: 8px;
			font-size: 10px;
			transform: scale(0.65);
		}

		.task-new{
			position: absolute;
			top: 37px;
			font-size: 10px;
			color: #000;
			font-weight: 600;
			left: 7px;
			white-space: nowrap;
		}

	</style>

</head>

<body>
<div class="mui-slider">
	<div class="swiper-container">
		<div class="swiper-wrapper" style="height: calc(<?php echo htmlentities($css['h']); ?> + 23px);">

			<?php if(is_array($slider) || $slider instanceof \think\Collection || $slider instanceof \think\Paginator): $i = 0; $__LIST__ = $slider;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$item): $mod = ($i % 2 );++$i;if($item['type'] == 'task'): ?>
				<div class="swiper-slide">
					<a href="<?php echo htmlentities($item['href']); ?>">
						<div class="slider-content slider-common" style="background: url(<?php echo htmlentities($item['icon']); ?>) no-repeat center;background-size:92% 95%;">
							<div style="position: relative;">
								<p class="task-new">已完成<span class="done"><?php echo htmlentities($item['done']); ?></span>/<?php echo htmlentities($count); ?></p>
							</div>
						</div>
					</a>
				</div>
				<?php else: ?>
				<div class="swiper-slide">
					<a href="<?php echo htmlentities($item['href']); ?>">
						<div class="slider-content slider-common" style="background: url(<?php echo htmlentities($item['icon']); ?>) no-repeat center;background-size:100% 100%;"></div>
					</a>
				</div>
				<?php endif; endforeach; endif; else: echo "" ;endif; ?>

		</div>

		<!-- 如果需要分页器 -->
		<!--<div class="swiper-pagination"></div>-->

	</div>

</div>

<script src="/static/h5/js/ws.js?date=<?php echo htmlentities($now); ?>"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Swiper/4.0.2/js/swiper.min.js"></script>
<script type="text/javascript">

	new Swiper (
			'.swiper-container',
			{
				direction: 'horizontal', // 垂直切换选项
				loop: true, // 循环模式选项
				autoplay: {
					disableOnInteraction: false
				},
				pagination: {
					el: '.swiper-pagination'
				}
			}
	);

	//主播任务数据更新
	var taskLive = function (data) {

		var doneModels = document.querySelectorAll('.done');

		[].forEach.call(doneModels, function(e) {
			e.textContent = data.done;
		});
	};

	var socket_conf = <?php echo json_encode($ws); ?>;
	var socket_msg = '<?php echo json_encode($msg); ?>';

	if (socket_conf && socket_msg)
	{
		//初始化链接地址
		var socket = ws.init(socket_conf).connect();
		console.log(socket_msg);
		socket.send(socket_msg);
		//接收信息
		ws.onMessage = function(message) {
			console.log(message);
			var className = message.emit;
			if(typeof(window[className]) === "function") eval(className+"(message.data)");
		};
	}

</script>
</body>

</html>

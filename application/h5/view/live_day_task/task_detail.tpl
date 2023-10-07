<!doctype html>
<html>

<head>
	<meta charset="utf-8">
	<title>主播任务</title>
	<meta name="viewport" content="width=device-width,initial-scale=1,minimum-scale=1,maximum-scale=1,user-scalable=no" />
	<link href="__VENDOR__/mui/css/mui.min.css" rel="stylesheet" />
	<style>
		body{
			background-color: #fff !important;
		}
		.task_img{
			background-image: url("__H5__/images/setting/day_task_bg.png");
			background-size: 100% 100%;
			background-repeat: no-repeat;
			height:170px;
		}
		.mui-table-view:after, .mui-table-view:before{
			height: 0 !important;
		}
		.mui-table-view-cell.mui-collapse.mui-active, .mui-content>.mui-table-view:first-child{
			margin-top:0 !important;
		}
		.mui-content{
			background-color:#fff !important;
		}
		.mui-table-view-cell.mui-active{
			background-color: #fff !important;
		}
		.mui-input-range input[type=range]::-webkit-slider-thumb{
			background-color: #40DAE4;
			width:18px;
			height:18px;
		}

		.mui-input-range input[type=range]{
			background-color: #FF9F00;
			opacity: .4;
		}
        .mui-table-view-cell>a:not(.mui-btn).mui-active{
            background-color:#fff;
        }
		.mui-table-view-cell:after{
			background-color: #e0e0e0;
			right: 10px;
			left: 60px;
		}
		.mui-table-view-cell:last-child:after{
			height: 1px;
		}

		.task_img>div:first-child>img{
			width: 20%;
			border-radius: 50%;
			top: 50%;
			left: 50%;
			transform: translate(-50%,-50%);
			position: absolute;
		}

		.task_img>div:first-child{
			text-align: center;
			height: 60%;
			position: relative;
		}

		.task_img>div:nth-child(2){
			text-align: center;
			color: #FEFEFE;
			height: 20%;
			font-size: 16px;
			font-weight: Bold;
		}

		.task_img>div:last-child{
			width: 100%;
			margin: auto;
			text-align: center;
			color: #fff;
		}

		.task_img>div:last-child>img{
			width: 8%;
			margin: 0 1px;
			vertical-align: middle;
		}

		.mui-progressbar{
			height: 4px;
			margin: 2px 0;
		}
		.mui-table-view-cell{
			padding: 11px 10px 11px 8px;
		}

		.mui-table-view .mui-media-object.mui-pull-left {
			margin-right: 5px;
		}

		.mui-table-view .mui-media-object {
			line-height: 50px;
			width: 50px;
			height: 50px;
			max-width: 50px;
		}

	</style>
</head>

<body>
	<div class="mui-content">
		<div class="mui-row">
			<div class="task_img" style="margin:10px;">
				<div><img src="{$user_info.avatar}" alt=""></div>
				<div>主播任务星</div>
				<div>
					<!--<foreach name="taskItem" id="vo">
						<if condition="$vo['progress'] lt 100">
							<img src="__H5__/images/setting/star.png" style="filter: opacity(45%);" alt="">
						<else/>
							<img src="__H5__/images/setting/star.png" alt="">
						</if>
					</foreach>-->
					<if condition="!empty($task_star)">
						<img src="__H5__/images/setting/star.png" alt=""><span style="font-size: 10px;">X</span><span style="font-size: 18px;">{$task_star}</span>
					</if>
				</div>
			</div>
		</div>
	</div>

	<div class="mui-content">
		<ul class="mui-table-view">
			<foreach name="taskItem" id="vo">
				<li class="mui-table-view-cell mui-media mui-collapse">
					<img class="mui-media-object mui-pull-left" src="{$vo.icon}">
					<div class="mui-media-body">
						<p class='mui-ellipsis' style="color: #373737;font-weight: bold;">{$vo.target}</p>
						<div id="demo1" class="mui-progressbar" style="background-color: rgba({$vo['bg_color']}, 0.4);">
							<span style="left: {$vo['progress']|default=0}%; background-color: rgb({$vo['bg_color']})"></span>
						</div>
						<p class='mui-ellipsis' style="font-size: 13px;">当前进度：{$vo['complete']}<span style="font-size: 10px;"></span></p>
					</div>
				</li>
			</foreach>
		</ul>

	</div>
	<!--<script src="__VENDOR__/mui/js/mui.min.js"></script>-->
</body>

</html>

<?php /*a:1:{s:63:"/www/wwwroot/zhibb/application/h5/view/setting/task_setting.tpl";i:1594812202;}*/ ?>
<!doctype html>
<html>

<head>
	<meta charset="utf-8">
	<title>主播任务设置</title>
	<meta name="viewport" content="width=device-width,initial-scale=1,minimum-scale=1,maximum-scale=1,user-scalable=no" />
	<link href="/static/vendor/mui/css/mui.min.css" rel="stylesheet" />
	<link rel="stylesheet" type="text/css" href="/bx_static/h5.css"/>
	<script src="/bx_static/changeFont.js"></script>
	<style>
		body{
			background-color: #fff !important;
		}
		.task_img{
			width: 100%;
			background-size: 100% 100%;
			background-repeat: no-repeat;
			height:23vw;
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
			background-color: rgb(240,240,240);
			width:15px;
			height:15px;
			border: 0.5px solid rgb(182,182,182);
		}

		.mui-input-range input[type=range]{
			background-color: rgb(153,153,153);
			opacity: .4;
		}
        .mui-table-view-cell>a:not(.mui-btn).mui-active{
            background-color:#fff;
        }
		.mui-table-view-cell:after{
			background-color: #e0e0e0;
		}
		.mui-card-header:after{
			height: 0;
		}
		.mui-input-range input[type=range]{
			height: 1px;
		}

		.mui-table-view .mui-media-object {
			line-height: 50px;
			width: 50px;
			height: 50px;
			max-width: 50px;
		}

		.mui-media-body{
			display: flex;
			justify-content: space-between;
			align-items: center;
			height: 45px
		}

		.mui-media-body>div:last-child{
			text-align: center;
		}
		.mui-numbox{
			width: 160px;
			border-color: #ececec;
			background-color: #fff;
		}

		.mui-numbox .mui-input-numbox, .mui-numbox .mui-numbox-input {

			border-right-color: #ececec !important;
			border-left-color: #ececec !important;
		}

		.select-img{
			width: 15% !important;
			float: right !important;
			height: initial !important;
		}
		.input{
			margin-bottom: 0 !important;
			padding: 2px !important;
			text-align: center !important;
			border-color: #ececec !important;
			color: #4d4d50 !important;
			width: 18% !important;
		}
		.mui-ellipsis{
			font-size: 16px !important;
		}

	</style>
</head>

<body class="task_setting">
	<div class="title">
			<div class="anchor_task_setting">主播任务设置</div>
	</div>
	<div class="mui-content">
		<div class="mui-row">
			<div class="mui-col-xs-6" style="padding: 0.1rem 0.05rem 0.1rem 0.1rem;">
				<div id="system-task" class="mui-card-header mui-card-media task_img sys_task">
					<div class="active"></div>
					系统任务
				</div>
			</div>
			<div class="mui-col-xs-6" style="padding: 0.1rem 0.1rem 0.1rem 0.05rem;">
				<div id="user-task" class="mui-card-header mui-card-media task_img custom_task">
					自定义任务
				</div>
			</div>
		</div>
	</div>

	<div class="mui-content <?php if($task_type == 0): ?>mui-hidden<?php endif; ?>" id="user-task-content">
		<ul class="mui-table-view">
			<?php if(is_array($taskItem['task_detail']) || $taskItem['task_detail'] instanceof \think\Collection || $taskItem['task_detail'] instanceof \think\Paginator): if( count($taskItem['task_detail'])==0 ) : echo "" ;else: foreach($taskItem['task_detail'] as $i=>$vo): ?>
				<li class="mui-table-view-cell mui-collapse">
					<img class="mui-media-object mui-pull-left" src="<?php echo htmlentities($vo['icon']); ?>">
					<div class="mui-media-body">
						<p class='mui-ellipsis'><?php echo htmlentities($vo['title']); ?> <span class="mui-badge mui-badge-success"><?php echo htmlentities($vo['unit']); ?></span></p>
						<input class="mui-input-numbox input" data-init-value="<?php echo htmlentities($vo['num']); ?>" name="<?php echo htmlentities($vo['name']); ?>" data-numbox-min="<?php echo htmlentities($vo['min']); ?>" type="number" value="<?php echo htmlentities($vo['num']); ?>" onchange="input(this)" <?php if($task_type == 1): ?>disabled="disabled"<?php endif; ?> style="<?php if($i == 2): ?>width: 28% !important;<?php endif; ?>"/>
					</div>
				</li>
			<?php endforeach; endif; else: echo "" ;endif; ?>
		</ul>
		<?php if($task_type == 0): ?>
			<div class="mui-text-center submit" style="margin-top: 170px;">
				<img id="submit" src="/static/h5/images/setting/sub.png" alt="" style="width: 60%;">
			</div>
		<?php endif; ?>
	</div>

	<script src="/static/vendor/mui/js/mui.min.js"></script>
	<script type="text/javascript">

		var task_type = "<?php echo htmlentities($task_type); ?>";

		var user_id = <?php echo htmlentities($user_id); ?>;

        if (task_type == 0)
        {
            mui('.mui-row').on('tap', '#system-task', function () {
                document.getElementById('user-task-content').classList.add('mui-hidden');
                document.getElementById('system-task-select').classList.remove('mui-hidden');
                document.getElementById('user-task-select').classList.add('mui-hidden');
            });

            mui('.mui-row').on('tap', '#user-task', function () {
                document.getElementById('user-task-content').classList.remove('mui-hidden');
                document.getElementById('user-task-select').classList.remove('mui-hidden');
                document.getElementById('system-task-select').classList.add('mui-hidden');
            });
        }

		var input = function (obj) {
			var min = obj.getAttribute('data-numbox-min');
            var user_val = obj.value;
			if ((user_val-min) < 0)
			{
			    var text = obj.parentNode.firstElementChild.innerHTML;
			    mui.alert(text+'设定值不能低于'+min, '');
			    obj.value = min;
			    return false;
			}
         };

        mui('.submit').on('tap', '#submit', function () {
            if(task_type == 1)
			{
			    mui.alert('今日任务已设');
			    return false;
            }
            var inputs = document.getElementsByClassName('input');
			var data = {"user_id":user_id};
			for (var i=0, e; e=inputs[i++];){
				data[e.name] = e.value;
			}
            mui.post('defineTaskSetting', data, function(res){
                if (res.status == 0)
				{
				    task_type = 1;

                    for (var i=0, e; e=inputs[i++];){
                        e.setAttribute('disabled', 'disabled');
                    }
                }
					mui.alert(res.message);
                },'json'
            );
        });





	</script>

</body>

</html>

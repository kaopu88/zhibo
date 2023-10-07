<!DOCTYPE html>
<html>

<head>
	<meta charset="utf-8" />
	<meta name="viewport"
		content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, viewport-fit=cover" />
	<title>开通提示</title>
	<link rel="stylesheet" href="__H5__/css/vant/index.css">
	<link rel="stylesheet" type="text/css" href="__H5__/css/applylabourunionsuccess/index.css"/>
	<script src="__H5__/js/media_auto.js"></script>
	<script src="__H5__/js/vue/vue.js" type="text/javascript" charset="utf-8"></script>
	<script src="__H5__/js/vant/vant.min.js" type="text/javascript" charset="utf-8"></script>
	<script src="__NEWSTATIC__/h5/agent/js/axios.min.js"></script>
</head>

<body>
	<div id="openTip" v-cloak>
		<van-skeleton title avatar :row="3" :loading="loading">

			<img src="__H5__/images/h5labourunion/icon_befault_shcg@2x.png" class="icon">
			<div class="tip">
				<div class="title">
					恭喜您已创建{$_agentName}
				</div>
				<div class="info">
					请前往{$_agentName}后台电脑端进行{$_agentName}管理
				</div>
			</div>
			<div class="context">
				<div class="top">
					<div class="url">
						{{value}}
					</div>
					<input type="text" style="position: absolute;z-index: -1;opacity: 0;" id="oInput" />
					<div class="copyStyle" @click="copy(value)">
						复制
					</div>
				</div>
				<div class="top">
					账号:{$username}
				</div>
				<van-divider :style="{ borderColor: '#EAEAEA', padding: '0 0.32rem' }"></van-divider>
				<div class="footer">
					复制链接后可通过微信、QQ等工具发送到PC端，用浏览器打开链接
				</div>
			</div>
			<van-number-keyboard safe-area-inset-bottom></van-number-keyboard>
		</van-skeleton>
	</div>
</body>

<script type="module">
	new Vue({
		el: "#openTip",
		data() {
			return {
				loading: false,
				navTitle: '开通提示',
				value: 'http://'+document.domain+'/agent/',
			};
		},
		methods: {
			onClickLeft() {
				window.history.back();
			},
			copy(data) {
				let oInput = document.getElementById('oInput');
				oInput.value = data;
				oInput.select();
				document.execCommand("Copy"); // 执行浏览器复制命令
				this.$toast('复制成功');
			},
		}
	})
</script>

</html>
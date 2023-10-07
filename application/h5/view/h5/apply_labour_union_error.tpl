<!DOCTYPE html>
<html>

<head>
	<meta charset="utf-8" />
	<meta name="viewport"
		content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, viewport-fit=cover" />
	<title>审核失败</title>
	<link rel="stylesheet" href="__H5__/css/vant/index.css">
	<link rel="stylesheet" type="text/css" href="__H5__/css/applylabourunionerror/index.css"/>
	<script src="__H5__/js/media_auto.js"></script>
	<script src="__H5__/js/vue/vue.js" type="text/javascript" charset="utf-8"></script>
	<script src="__H5__/js/vant/vant.min.js" type="text/javascript" charset="utf-8"></script>
	<script src="__NEWSTATIC__/h5/agent/js/axios.min.js"></script>
</head>

<body>
	<div id="auditErr" v-cloak>
		<van-skeleton title avatar :row="3" :loading="loading">

			<img src="__H5__/images/h5labourunion/icon_err_shcg@2x.png" class="icon">
			<div class="tip">
				<div class="title">
					抱歉，您的审核未通过
				</div>
				<div class="info">
					{{refusemsg}}
				</div>
			</div>
			<div class="know-btn" v-on:click="goback()">
				知道了
			</div>
			<van-number-keyboard safe-area-inset-bottom></van-number-keyboard>
		</van-skeleton>
	</div>
</body>

<script type="module">
	import {getUrlKey} from '__H5__/js/gturl.js';
	new Vue({
		el: "#auditErr",
		data() {
			return {
				loading: false,
				refusemsg:'拒绝'
			};
		},
		created(){
			this.getMessageDetail();
		},
		methods: {
			onClickLeft() {
				window.history.back();

			},
			getMessageDetail(){
				let token = getUrlKey("token",window.location.href);
				let msg   = getUrlKey("msg",window.location.href);
				console.log(token+msg);
				this.refusemsg = msg;
			},
			goback(){
				let token = getUrlKey("token",window.location.href);
				let url = '/h5/H5/applyLabourUnion?token='+token;
				this.goaway(url);
			},
			goaway(path) {
				window.location.href=path;
			}


		}
	})
</script>

</html>
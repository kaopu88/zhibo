<?php /*a:2:{s:54:"/www/wwwroot/zhibb/application/h5/view/level/index.tpl";i:1594711482;s:54:"/www/wwwroot/zhibb/application/h5/view/public/head.tpl";i:1595042494;}*/ ?>
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

<title>我的等级</title>
<script type="text/javascript" src="/static/vendor/jquery.min.js?v=<?php echo config('upload.resource_version'); ?>"></script>
<link rel="stylesheet" type="text/css" href="/static/h5/css/swiper-3.4.2.min.css?v=<?php echo config('upload.resource_version'); ?>">
</head>
<script type="text/javascript">
    function calcuroot_size(blockWidth){
        var root_size=100*(blockWidth/750);
        $("html").css({"font-size":root_size+"px"});
    }
    calcuroot_size(window.innerWidth);
</script>
<style type="text/css">
	*{
		padding: 0;
		margin: 0;
	}
	a {color: #444;}
	a, a:focus, a:hover, a:active {outline: 0;text-decoration: none;}
	a:hover {text-decoration: none;}
	img{
		vertical-align: middle;
	}
	li,ul{
		list-style-type: none;
	}
	.page{
		width: 100%;
		height: 100%;
		position: relative;
	}
	.page:before{
		display: table;
		content: '';
	}
	#status{
		width:100%;
		height:0.4rem;
		background:#58BFBD;
	}
	#status:before{
		display: table;
		content: '';
	}
	#nav{
		width:100%;
		height:0.88rem;
		background:#58BFBD;
		background-image:url(/static/h5/images/level/return_back.png);
		background-repeat:no-repeat;
		background-position:0.3rem center;
		background-size: 0.9rem auto;
	}
	#nav p{
		font-size:0.34rem;
		text-align:center;
		line-height:0.88rem;
		color:#FFF;
		float:left;
		margin-left:3.1rem;
	}
	#nav .exp{
		margin-right:0.3rem;
		height:0.88rem;
		float:right;
		font-size:0.28rem;
		line-height:0.88rem;
		color:#FFF;
	}
	#nav:before{
		display: table;
		content: '';
	}
	.headPic{
		width: 4.8rem;
		height: 3.01rem;
		margin:0.78rem auto 0;
		position: relative;
	}
	.headPic:before{
		content: '';
		display: table;
	}
	.headPic .uavatar{
		width: 2.5rem;
		height: 2.5rem;
		display: inline-block;
		border-radius: 2.5rem;
		position: relative;
		left: 1.15rem;
		top: 0.13rem;
	}

	.headHover{
		/*position: absolute;*/
		width: 3rem;
		height: 3rem;
		background: url("/bx_static/admin/assets/touxiangkuang@3x.png");
		background-size: 100% 100%;
		margin: -2.6rem auto;
	}

	.level{
		position: absolute;
		width: 100%;
		height: 100%;
		background: url("/bx_static/admin/assets/rank_bottom@3x.png");
		background-size: 100% 100%;
		z-index: 10;
		top:0;
	}


	.level{
		position: absolute;
		width: 2.8rem;
		height:0.8rem;
		line-height:1rem;
		top:2.18rem;
		left:1rem;
		text-align: center;
		font-size: 0.3rem;
		color:#fff;
	}
	.levelDesc{
		margin:0.63rem auto 0;
		font-size: 0.2rem;
		color:#B0B0B0;
		text-align: center;
	}
	.alert {
    color: #F92C56;
	}
	.levelDetail{
		width: 6.11rem;
		margin:0.14rem auto 0.16rem;
		overflow: hidden;
	}
	.levelDetail:before{
		content: '';
		display: table;
	}
	.currentLevel{
		float:left;
		font-size: 0.24rem;
		margin-right: 0.12rem;
	}
	.levelLine{
		width: 4.34rem;
		height: 0.08rem;
		border-radius:0.05rem;
		background: #E9E9E9;
		float: left;
		margin-top: 0.14rem;
		margin-bottom:0.36rem;
	}
	.getLev{
		width: 50%;
		height: 100%;
		border-radius: 0.03rem;
		background-color: #F92C56;
	}
	.nextLevel{
		font-size: 0.24rem;
		margin-left: 0.12rem;
		color:#B0B0B0;
		float: left;
	}



	.cup{
		width: 100%;
		height: 0.1rem;
		position: relative;
		background-color: #F4F4F4;
	}
	.grade{
		width: 4.77rem;
		margin:0.30rem auto 0 auto;
		font-size: 0.3rem;
		color:#373737;
		text-align: center;
	}
	.gradePic{
		width: 6.42rem;
		height: 4.26rem;
		margin:0 auto;
		margin-top:0.37rem;
		position: relative;
	}
	.gradePic:before{
		content: '';
		display: table;
	}
	.gradePage{
		float:left;
		width:1.26rem;
		height:2.13rem;
		-webkit-tap-highlight-color: rgba(0,0,0,0);
		margin-bottom: 0.24rem;
	}
	.gradeCsc{
		width: 1.26rem;
		display: block;
		margin:0 auto;
	}
	.gradeAsc{
		margin:0.09rem auto 0;
		font-size: 0.28rem;
		color:#373737;
		text-align: center;
	}
	.gradeBsc{
		margin:0.07rem auto 0.23rem;
		font-size: 0.20rem;
		color:#B0B0B0;
		text-align: center;
	}
	.gradePage#gradePageone{
		margin-left:1.32rem;
		margin-right:1.32rem;
	}
	.gradePage#gradePagetwo{
		margin-left:1.32rem;
		margin-right:1.32rem;
	}
	.gradeDsc{
		margin-top: 0.16rem;
		margin-bottom: 0.36rem;
		font-size: 0.24rem;
		color: #B0B0B0;
		text-align: center;
	}



	.upPic{
		width: 6.17rem;
		height: 4.22rem;
		margin:0.72rem auto 0;
		position: relative;
	}
	.upPic{
		content: '';
		display: table;
	}
	.upPic>img{
		width: 6.17rem;
		display: block;
		margin:0.2rem auto 0;
	}
	.gradeP{
		margin-top:0.76rem;
		margin-bottom:0.9rem;
		font-size: 0.28rem;
		line-height:.6rem;
		color:#373737;
		text-align: center;
	}
	.swiper-container{
		width: 100%;
		height: 5.5rem;
		position: relative;

	}
	.swiper-pagination {
		position: absolute;
		bottom: 0rem !important;
		text-align: center;
	}
	.swiper-container-horizontal>.swiper-pagination-bullets .swiper-pagination-bullet{
		margin:0 0.25rem;
	}
	.swiper-pagination-bullet{
		width:0.15rem;
		height:0.15rem;
		display:inline-block;
		border-radius:100%;
		background:#000;
		opacity: .2;
	}
	.swiper-pagination-bullet-active{
		opacity: 1;
		background:#F92C56;
	}
	.upPic{
		display:flex;
		justify-content: space-between;
	}
</style>

<div class="page">
	<div class="headPic">
		<img src="<?php if(!empty($user['avatar']) == true): ?><?php echo htmlentities($user['avatar']); else: ?>http://live.yunbaozhibo.com/api/public/upload/avatar/default_thumb.jpg<?php endif; ?>" class="uavatar">
		<div class="headHover">
			<p class="level">Lv.<?php echo htmlentities($user['level']); ?></p>
		</div>
	</div>
	<p class="levelDesc">当前累计经验<span class="alert"><?php echo htmlentities($user['exp']); ?></span>，离升级还差<span class="alert"><?php echo htmlentities($cha); ?></span></p>
	<div class="levelDetail">
		<div class="currentLevel alert">
			Lv.<?php echo htmlentities($user['level']); ?>
		</div>
		<div class="levelLine">
			<div class="getLev" style="width: <?php echo htmlentities($rate); ?>%"></div>
		</div>
		<div class="nextLevel">Lv.<?php echo htmlentities($user['level']+1); ?></div>
	</div>
</div>

<div class="cup"></div>

<div class="page">
	<p class="grade">等级特权</p>
	<div class="swiper-container">
		<div class="swiper-pagination"></div>
		<div class="swiper-wrapper">
			<div class="swiper-slide">
				<div class="gradePic">
					<a class="gradePage" href="javascript:void(0);">
						<img src="/bx_static/admin/assets/dengji@3x.png" alt="等级图标" class="gradeCsc"/>
						<p class="gradeAsc">等级图标</p>
						<p class="gradeBsc">LV1开启</p>
					</a>

					<a class="gradePage" id="gradePageone"  href="javascript:void(0);">
						<img src="/bx_static/admin/assets/lianmai@3x.png" alt="视频连麦" class="gradeCsc"/>
						<p class="gradeAsc">视频连麦</p>
						<p class="gradeBsc">LV45开启</p>
					</a>


					<a class="gradePage" href="javascript:void(0);">
						<img src="/bx_static/admin/assets/xiankan@3x.png" alt="热片先看" class="gradeCsc"/>
						<p class="gradeAsc">热片先看</p>
						<p class="gradeBsc">LV50开启</p>
					</a>

					<a class="gradePage" href="javascript:void(0);">
						<img src="/bx_static/admin/assets/shenpian@3x.png" alt="审片优先" class="gradeCsc"/>
						<p class="gradeAsc">审片优先</p>
						<p class="gradeBsc">LV55开启</p>
					</a>

					<a class="gradePage" id="gradePageone"  href="javascript:void(0);">
						<img src="/bx_static/admin/assets/biaoqing@3x.png" alt="尊贵表情" class="gradeCsc"/>
						<p class="gradeAsc">尊贵表情</p>
						<p class="gradeBsc">LV60开启</p>
					</a>

					<a class="gradePage" href="javascript:void(0);">
						<img src="/bx_static/admin/assets/piaoping@3x.png" alt="高级顠屏" class="gradeCsc"/>
						<p class="gradeAsc">高级顠屏</p>
						<p class="gradeBsc">LV65开启</p>
					</a>
					

				</div>
			</div>

			<div class="swiper-slide">
				<div class="gradePic">

					<a class="gradePage" href="javascript:void(0);">
						<img src="/bx_static/admin/assets/guajian@3x.png" alt="头像挂件" class="gradeCsc"/>
						<p class="gradeAsc">头像挂件</p>
						<p class="gradeBsc">LV70开启</p>
					</a>

					<a class="gradePage" id="gradePageone" href="javascript:void(0);">
						<img src="/bx_static/admin/assets/liwu@3x.png" alt="特级礼物" class="gradeCsc"/>
						<p class="gradeAsc">特级礼物</p>
						<p class="gradeBsc">LV75开启</p>
					</a>

					<a class="gradePage" href="javascript:void(0);">
						<img src="/bx_static/admin/assets/huangguan@3x.png" alt="尊贵皇冠" class="gradeCsc"/>
						<p class="gradeAsc">至尊皇冠</p>
						<p class="gradeBsc">LV80开启</p>
					</a>

				</div>
			</div>

		</div>
	</div>
	<p class="gradeDsc">更多特权，敬请期待~</p>

</div>

<div class="cup"></div>

<div class="page">
	<p class="grade">如何快速升级</p>
	<div class="upPic">
		<img src="/bx_static/admin/assets/footer_feiji@3x.png" alt="飞机"/>
	</div>
	<div class="gradeP">
		<p>签到，直播，看播都可获得经验值，</p>
		<p>如不想等待，礼物刷起来，刷的越多升级越快~</p>
	</div>
</div>
<script type="text/javascript" src="/static/h5/js/swiper-3.4.2.min.js?v=<?php echo config('upload.resource_version'); ?>"></script>
<script type="text/javascript">
    window.onload=function(){
        var mySwiper=new Swiper('.swiper-container',{
            pagination : '.swiper-pagination',
        })
    }
</script>
</body>
</html>


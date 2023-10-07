<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width,initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
	<title>精灵女王排行榜</title>
	<style type="text/css">
		*{margin:0px;padding:0px;border:none;}
      	html{font-size:20px;font-family:'Microsoft YaHei','Hiragino Sans GB',Helvetica,Arial,'Lucida Grande',sans-serif;color: #73320A;}
      	body{ background-color:#FFA0C5;}
      	a{text-decoration:none}
      	img{border: none;}
      	.header{width: 100%;height: 100%;}
      	.header>img{width: 100%;height: 100%;}
      	.section-act{background-image: url(__H5__/images/star/act.png);width: 74%;margin: 0 auto;background-size: 100% 100%;padding: 4% 3%;border-image:url(__H5__/images/star/border.png) 30 30 round;line-height: 23px;}
      	.section-act>p{font-size: 14px;}
      	.section-act>p>span{font-size: 20px;font-weight: bold;}
		.title{display: block;margin: 0 auto;width: 75%;}
		.section-title>div:first-child{text-align: center;margin:7% 0 -8%;}
		.section-body{width: 90%;padding-bottom: 13%;margin:0 auto;
		-webkit-border-image: url(__H5__/images/star/body.png) 38 49 round;
		-o-border-image: url(__H5__/images/star/body.png) 38 49 round;
		border-image-width: 9%;
		float: left;
		margin-left: 6%;


		}
		.section-body>ul>li{list-style: none;float: left;width: 100%;}
		.ava-left,.ava-yuan,.ava-hg{float: left;}
		.ava-left{width: 53%;}
		.ava-yuan{width: 50%;margin: -40% 0 0 35%;}
		.ava-hg{width: 39%;margin: -54% 0 0 56%;}
		.section-body-realava{float: left;margin: -44% 0 0 41%;}
		.section-body-realava>img{width: 65%;border-radius: 100%;}
		.section-body-info{float: right;width: 50%;margin: -16% 14% 0 0;}
		.section-body-info>p{font-size: 0.8rem;margin: 1% 0;}
		.section-body-avater{width: 30%;margin: 8% 0 0 2%;}
		/*.section-body-avater>div:first-child{margin-top: 20%;}*/
		.section-body>ul{margin-top: 10%;height: 265px;overflow: scroll;}

	</style>
</head>
<body>
	<div class="header">
		<img src="__H5__/images/star/header.png">
	</div>

		<div class="section-act">
			<p><span>活动详情：</span>{$msg}</p>
		</div>
		<div class="section-title">
			<div>
				<img class="title" src="__H5__/images/star/title.png">
			</div>
			<div class="section-body">
				<ul>
					<volist name="info" id="vo" key="key">
					<li>
						<div class="section-body-avater">
							<div>
								<if condition="$key eq 1 ">
									<img class="ava-left" src="__H5__/images/star/no1.png">						
									<img class="ava-hg" src="__H5__/images/star/no1h.png">
									<img class="ava-yuan" src="__H5__/images/star/no1t.png">
								<elseif condition="$key eq 2" />
									<img class="ava-left" src="__H5__/images/star/no2.png">						
									<img class="ava-hg" src="__H5__/images/star/no2h.png">
									<img class="ava-yuan" src="__H5__/images/star/no2t.png">
								<elseif condition="$key eq 3" />
									<img class="ava-left" src="__H5__/images/star/no3.png">						
									<img class="ava-hg" src="__H5__/images/star/no3h.png">
									<img class="ava-yuan" src="__H5__/images/star/no3t.png">
								<else />
									<img class="ava-left" src="__H5__/images/star/no4.png">					
									<img class="ava-yuan" src="__H5__/images/star/no4t.png">
								</if>
							</div>
							<div class="section-body-realava">
								<img src="{$vo.avatar}">
							</div>
						</div>
						<div class="section-body-info">
							<p>{$vo.nickname}</p>
							<p>礼物量：{$vo.giftNum}</p>
						</div>
					</li>
					</volist>
				</ul>
			</div>
		</div>

	<footer style="float: left;width: 100%;">
		 <div style="font-size: 0.5rem;margin: 5% 0 5% 25%;">*本活动最终解释权归{:APP_NAME}官方所有*</div>
	</footer>
</body>
</html>
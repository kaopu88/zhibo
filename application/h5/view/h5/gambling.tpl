<!doctype html> 
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>赌神之神-份额赠送活动</title>
        <meta name="keywords" content="" />
        <meta name="description" content="" />
        <meta name="HandheldFriendly" content="true" />
        <meta name="MobileOptimized" content="width" />
        <meta id="viewport" content="width=device-width, user-scalable=no,initial-scale=1" name="viewport" />
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta content="black" name="apple-mobile-web-app-status-bar-style" />
        <meta content="telephone=no" name="format-detection" />
        <link href="__H5__/css/h5display/all.css" type="text/css" rel="stylesheet"/>
        <link href="__H5__/css/h5display/index.css" type="text/css" rel="stylesheet"/>
        <style type="text/css">
        .touxiang{
        	border-radius: 120px;border:2px solid #2c9694;float: left;
        	/* position: absolute;top:-195px;left: 10px;  */
        }
        .form-control { 
		display: block; 
		width: 90%; 
		height: 34px; 
		padding: 6px 12px; 
		font-size: 14px; 
		line-height: 1.428571429; 
		color: #555555; 
		vertical-align: middle; 
		background-color: #ffffff; 
		border: 1px solid #cccccc; 
		border-radius: 4px; 
		-webkit-box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.075); 
		box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.075); 
		-webkit-transition: border-color ease-in-out .15s, box-shadow ease-in-out .15s; 
		transition: border-color ease-in-out .15s, box-shadow ease-in-out .15s; font-size: 22px;
		} 
		.form-control:focus { 
		border-color: #66afe9; 
		outline: 0; 
		-webkit-box-shadow: inset 0 1px 1px rgba(0,0,0,.075), 0 0 8px rgba(102, 175, 233, 0.6); 
		box-shadow: inset 0 1px 1px rgba(0,0,0,.075), 0 0 8px rgba(102, 175, 233, 0.6);

		} 
		.getcodes{display: inline-block; 
		width: 40%; 
		height: 34px; 
		padding: 6px 12px; 
		font-size: 14px; 
		line-height: 1.428571429; 
		color: #555555; 
		vertical-align: middle; 
		background-color: #ffffff; 
		border: 1px solid #cccccc; 
		border-radius: 4px; 
		-webkit-box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.075); 
		box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.075); 
		-webkit-transition: border-color ease-in-out .15s, box-shadow ease-in-out .15s; 
		transition: border-color ease-in-out .15s, box-shadow ease-in-out .15s; font-size: 22px;}
		.iframestyle{
			min-width: 100%;height: 100%;
			position: absolute;
			top: 20%;left: 0px;
		}
		
        </style>
    </head>
	

    <body>
		<div class="" id="slider">
			<section class="main-page current">
				<div>
					<img src="__H5__/images/h5display/ds1.png?v=__RV__" class="img100" style="display:block;" />
				</div>
                <div style="position: relative;">
                    <img src="__H5__/images/h5display/ds-bg.png?v=__RV__" class="img100" style="display:block;" />
                    <div style="position: absolute;top: 0;border: 1px solid #C9AC84;width: 90%;left: 0;right: 0;margin: 0 auto;z-index: 999;overflow: hidden;padding: 1px;">
                        <div id="live_player" style="width:100%; height:auto;margin: 0 auto;"></div>
                    </div>
                </div>
				<div style="position: relative;">
					<img src="__H5__/images/h5display/ds2.png?v=__RV__" class="img100" style="display:block;" />
                    <img src="__H5__/images/h5display/but.png?v=__RV__" alt="" style="position: absolute;left: 0;right: 0;width: 28%;bottom: 6%;margin: 0 auto;" onclick="toWxApp()">
				</div>
				<div>
					<img src="__H5__/images/h5display/ds3.png?v=__RV__" class="img100" style="display:block;" />
				</div>
			</section>			
		</div>
    </body>

    <script src="__VENDOR__/bugujsdk.js?v=__RV__"></script>

    <script src="__VENDOR__/TcPlayer/TcPlayer-2.2.2.js?v=__RV__" charset="utf-8"></script>
    <script type="text/javascript" src="http://apps.bdimg.com/libs/zepto/1.1.4/zepto.min.js"></script>

    <script>

        function toWxApp() {
            dssdk.ready(function (sdk) {
                sdk.openWxAPP({
                    userName: 'gh_01081f93d2e9',
                    miniProgramType: 0,
                    path: '/pages/movie/detail?id=17'
                });
            });
        }

        var player = new TcPlayer('live_player', {
            "mp4": "http://1255693052.vod2.myqcloud.com/8126a1cdvodtransgzp1255693052/70aa37805285890788927422588/v.f30.mp4",
            "controls": "none",
            "coverpic":{"style":"stretch", "src":"http://1255693052.vod2.myqcloud.com/8126a1cdvodtransgzp1255693052/70aa37805285890788927422588/1557451113_2719833720.100_0.jpg"},
            "width": "100%",
            "height": "auto",
            "autoplayer":true
        });

        $(function () {
//           $('.vcp-poster-pic').css({'width':'100%'});
            $('.vcp-bigplay').css({'opacity': '1', 'background': 'url(__H5__/images/h5display/play.png) no-repeat center', 'background-size': '15% auto','height': '100%'});
        });


        $('.vcp-bigplay').click(function () {
            $('.vcp-poster-pic').css('display', 'none');
            if (player.playing())
            {
                $(this).css({'opacity': '0'});
            }else {
                $(this).css({'opacity': '1', 'background': 'url(__H5__/images/h5display/stop.png) no-repeat center', 'background-size': '15% auto'});
            }
        });

    </script>

</html>
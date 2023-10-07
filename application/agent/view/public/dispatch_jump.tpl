<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1.0,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no"/>
    <title><?php echo ($status==0?'成功提示':'失败提示'); ?></title>
    <style>
        body{
            padding: 0;
            margin: 0;
            background-color: #F2F2F2;
        }
        .main{
            width: 1000px;
            margin: 0 auto;
            background-color:#fff;
            padding: 20px;
            border: solid 1px #DCDCDC;
            border-radius: 5px;
            margin-top: 100px;
            position: relative;
        }
        .main .icon_box{
            width: 150px;
            height: 150px;
            background:url("__STATIC__/smart_admin/images/result_icons.png") no-repeat;
            margin: 0 auto;
        }
        .main .tip{
            font-size: 24px;
            font-weight: normal;
            color: #333;
            text-align: center;
            margin: 0;
            padding: 10px 0;
        }
        .info{
            font-size: 14px;
            padding: 0;
            margin: 0;
            line-height: 30px;
            text-align: center;
            color: #656565;
        }
        .info a{
            display: inline-block;
            text-decoration: none;
            color: #0068B7;
            font-weight: 700;
            margin: 0 5px;
        }
        .info a:hover{
            text-decoration: underline;
        }
        .home{
            background-color: #f2f2f2;
            border-radius: 5px;
            padding: 0 10px;
            line-height: 30px;
            font-size: 12px;
            position: absolute;
            right: 10px;
            top: 10px;
            color: #555;
            display: block;
            text-decoration: none;
            border: solid 1px #DCDCDC;
        }
        .home:hover{
            border-color: #0068B7;
            color: #0068B7;
        }
        .main.success .tip{
            color: #5ac48f;
        }
        .main.error .tip{
            color: #ff4c4c;
        }
        .main.error .icon_box{
            background-position: -150px 0;
        }

    </style>
</head>
<body>
<div class="main <?php echo ($status==0?'success':'error'); ?>">
    <div class="icon_box"></div>
    <div style="border-bottom: solid 1px #F2F2F2;margin: 0 auto;"></div>
    <h1 class="tip"><?php echo(strip_tags($message));?></h1>
    <p class="info">
        页面自动 <a id="href" href="<?php echo($url);?>">跳转</a> 等待时间： <b id="wait"><?php echo($wait);?></b>s
    </p>
    <a href="/" class="home">返回首页</a>
</div>

<script type="text/javascript">
    (function(){
        var wait = document.getElementById('wait'),
            href = document.getElementById('href').href;
        var interval = setInterval(function(){
            var time = --wait.innerHTML;
            if(time <= 0) {
                location.href = href;
                clearInterval(interval);
            };
        }, 1000);
    })();
</script>

</body>
</html>
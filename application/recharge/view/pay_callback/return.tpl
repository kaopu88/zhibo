<!DOCTYPE html>
<html lang="en">
<head>
    <title>{$title}</title>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8"/>
    <meta name="viewport" id="viewport"
          content="width=320,user-scalable=no,initial-scale=1,minimum-scale=1,maximum-scale=1">
    <style>
        html, html * {
            box-sizing: border-box;
        }

        body {
            background-color: #f2f2f2;
            margin: 0;
            padding: 0;
        }

        .tip {
            background-color: #fff;
            color: #333;
            border-radius: 5px;
            padding: 20px;
            width: 80%;
            max-width: 1000px;
            border: solid 1px #dcdcdc;
            margin: 100px auto;
        }

        .tip h1 {
            font-size: 20px;
            font-weight: normal;
            color: #333333;
            padding: 0;
            margin: 0;
            line-height: 36px;
        }

        .tip p {
            font-size: 12px;
            color: #777;
            padding: 0;
            margin: 0;
            line-height: 24px;
        }

        .tip a {
            color: #333;
            text-decoration: none;
        }
    </style>
    <script src="__VENDOR__/jquery.min.js"></script>
</head>
<body>
<div class="tip">
    <h1>{$message}</h1>
    <p>
        支付跳转错误，<span class="countdown">3</span>秒后返回
        <a class="site_url" href="{$site_url}">{$company_name}</a>
    </p>
</div>
<script>
    layout();
    $(window).resize(function () {
        layout();
    });
    setInterval(function () {
        countdownHandler();
    }, 1000);

    function countdownHandler() {
        var countdown = $('.countdown').text();
        countdown = parseInt(countdown);
        countdown--;
        $('.countdown').text(countdown >= 0 ? countdown : 0);
        if (countdown <= 0) {
            location.href = $('.site_url').prop('href');
        }
    }

    function layout() {
        var ww = $(window).width();
        var hh = $(window).height();
        var tipW = $('.tip').outerWidth();
        var tipH = $('.tip').outerHeight();
        var left = (ww - tipW) / 2;
        $('.tip').css({
            left: left + 'px',
            top: (((hh - tipH) / 2) * 0.3) + 'px'
        });
    }
</script>
</body>
</html>

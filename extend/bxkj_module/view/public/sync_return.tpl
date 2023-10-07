<!DOCTYPE html>
<html>
<head lang="en">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=Edge">
    <title>安全通道</title>
    <style>
        body {
            padding: 0;
            margin: 0;
            background-color: #F7F7F7;
            color: #555555;
            font-family: "Microsoft YaHei", "微软雅黑", "Arial";
        }

        .sync_box {
            width: 1000px;
            margin: 0 auto;
            margin-top: 100px;
            border: solid 1px #dcdcdc;
            background-color: #ffffff;
            padding: 10px;
        }

        .sync_box h1 {
            font-size: 18px;
            font-weight: normal;
            line-height: 30px;
            margin: 0;
        }

        .sync_box p {
            font-size: 14px;
            line-height: 25px;
            margin: 0;
        }
    </style>
</head>
<body>

<div class="sync_box">
    <h1>{$sync_message}</h1>
    <notempty name="sync_redirect">
        <p><span id="delay_num"></span>秒后自动跳转...</p>
    </notempty>
</div>

<script>
    var app_total = parseInt('{:count($app_list)}'), redirect = '{$sync_redirect}', delay = parseInt('{$sync_delay}');
    var app_num = 0, timer, tiping = false;

    function syncReturn() {
        app_num++;
        //回调完成后跳转
        if (app_num >= app_total) {
            redirectHandler();
        }
    }

    //跳转提示
    if (redirect && delay > 0) {
        tiping = true;
        document.getElementById('delay_num').innerHTML = delay;
        timer = setInterval(function () {
            delay--;
            document.getElementById('delay_num').innerHTML = delay;
            if (delay <= 0) {
                tiping = false;
                clearInterval(timer);
                redirectHandler();
            }
        }, 1000);
    }

    //跳转
    function redirectHandler() {
        if (redirect && !tiping && app_num >= app_total) {
            location.href = redirect;
        }
    }

</script>

<volist name="app_list" id="app">
    <script src="{:$app.(strpos($app,'?')===false?'?callback=syncReturn':'&callback=syncReturn')}"></script>
</volist>
</body>
</html>
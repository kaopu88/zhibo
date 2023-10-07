<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta http-equiv="x-dns-prefetch-control" content="on" />
    <meta name="renderer" content="webkit" />
    <meta name="applicable-device" content="pc" />
    <meta http-equiv="X-UA-Compatible" content="IE=Edge" />
    <meta name="application-name" content="" />
    <meta name="renderer" content="webkit" />
    <meta name="format-detection" content="telephone=no" />
    <meta name="viewport"
        content="width=device-width, initial-scale=1, maximum-scale=1, minimum-scale=1, user-scalable=no, viewport-fit=cover" />
    <meta name="keywords" content="" />
    <meta name="description" content="" />
    <link rel="stylesheet" href="/bx_static/layui.css" />
    <link rel="stylesheet" href="/bx_static/withdrawal_activities.css" />
    <script src="/bx_static/layui.js"></script>
    <script src="/bx_static/media_auto.js"></script>
    <title>活动设置</title>
</head>

<body>
    <div class="container activety_set">
        <header>
            <div class="back"></div>
            <div class="title">活动设置</div>
        </header>
        <div class="content">
            <div class="list-item layui-form">
                <div class="title">签到提现</div>
                <div class="info">关闭提醒后可能错过签到</div>
                <input type="checkbox" lay-filter="sign_remind" name="xxx" lay-skin="switch">
            </div>
        </div>
    </div>
    <script>
        layui.use('form', function () {
            var form = layui.form;
            var $ = layui.jquery;
            // 签到提现的监听事件
            form.on('switch(sign_remind)', function (data) {
                console.log(data);
                console.log(data.elem); //得到checkbox原始DOM对象
                console.log(data.elem.checked); //开关是否开启，true或者false
                console.log(data.value); //开关value值，也可以通过data.elem.value得到
                console.log(data.othis); //得到美化后的DOM对象
            });
            $('.back').click(function () {
                window.history.back(-1);
            });
        });
    </script>
</body>

</html>
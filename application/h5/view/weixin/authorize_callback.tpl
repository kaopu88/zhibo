<!DOCTYPE html>
<html lang="zh-cmn-Hans">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,user-scalable=0,viewport-fit=cover">
    <title>微信授权</title>
    <link rel="stylesheet" href="__CSS__/weui.min.css?v=__RV__"/>
</head>
<body>
<div class="page">
    <div class="weui-msg">
        <div class="weui-msg__icon-area"><i class="weui-icon-warn weui-icon_msg"></i></div>
        <div class="weui-msg__text-area">
            <h2 class="weui-msg__title">{$title}</h2>
            <p class="weui-msg__desc">{$message}</p>
        </div>
        <div class="weui-msg__opr-area">
            <p class="weui-btn-area">
                <a href="{:url('retry',array('state'=>$state))}" class="weui-btn weui-btn_primary">重新授权</a>
            </p>
        </div>
        <div class="weui-msg__extra-area">
            <div class="weui-footer">
                <p class="weui-footer__text">Copyright &copy; 2008-2016 weui.io</p>
            </div>
        </div>
    </div>
</div>
</body>
</html>

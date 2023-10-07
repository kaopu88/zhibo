<!DOCTYPE html>
<html>
<head lang="en">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=Edge">
    <notempty name="page_app_name">
        <meta name="application-name" content="{$page_app_name}">
    </notempty>
    <notempty name="page_description">
        <meta name="description" content="{$page_description}">
    </notempty>
    <notempty name="page_keywords">
        <meta name="keywords" content="{$page_keywords}">
    </notempty>
    <block name="meta"></block>
    <title>{$page_title}</title>
    <link rel="stylesheet" type="text/css" href="__VENDOR__/icomoon/style.css?v=__RV__"/>
    <link rel="stylesheet" type="text/css" href="__VENDOR__/webuploader/webuploader.css?v=__RV__"/>
    <link rel="stylesheet" type="text/css" href="__VENDOR__/flatpickr/flatpickr.min.css?v=__RV__"/>
    <link rel="stylesheet" type="text/css" href="__VENDOR__/fancybox/jquery.fancybox.css?v=__RV__"/>
    <link rel="stylesheet" type="text/css" href="__STATIC__/smart_admin/css/smart_admin.css?v=__RV__"/>
    <link rel="stylesheet" type="text/css" href="__STATIC__/common/css/public.css?v=__RV__"/>
    <link rel="stylesheet" type="text/css" href="__CSS__/public.css?v=__RV__"/>
    <block name="css"></block>
    <include file="public:jsconfig"/>
    <script src="__VENDOR__/jquery.min.js?v=__RV__"></script>
    <script src="__VENDOR__/layer/layer.js?v=__RV__"></script>
    <script src="__VENDOR__/jquery.nicescroll.min.js?v=__RV__"></script>
    <script src="__VENDOR__/jquery.cookie.js?v=__RV__"></script>
    <script src="__VENDOR__/flatpickr/flatpickr.min.js?v=__RV__"></script>
    <script type="text/javascript" src="__VENDOR__/webuploader/webuploader.js?v=__RV__"></script>
    <script src="__VENDOR__/qiniu.min.js?v=__RV__"></script>
    <script src="__VENDOR__/fancybox/jquery.fancybox.pack.js?v=__RV__"></script>
    <script src="__VENDOR__/smart/smart.bundle.js?v=__RV__"></script>
    <script src="__STATIC__/smart_admin/js/smart_admin.bundle.js?v=__RV__"></script>
    <script src="__STATIC__/common/js/public.js?v=__RV__"></script>
    <!-- <script src="__JS__/public.js?v=__RV__"></script>-->
    <block name="js"></block>
    <style>
        body {
            background-color: #336499;
        }

        .top_login {
            margin: 0 auto;
            margin-top: 100px;
            width: 210px;
        }

        .forget_box {
            background-color: #fff;
            border-radius: 5px;
            padding: 20px;
            width: 480px;
            margin: 30px auto;
        }

        .forget_box h1 {
            font-weight: normal;
            font-size: 24px;
            text-align: center;
            color: #555;
        }

        .forget_name {
            display: inline-block;
            padding-right: 15px;
        }

        .forget_box ul {
            margin-top: 20px;
        }

        .forget_box ul li {
            margin-bottom: 10px;
        }
        .send_sms{
            position: absolute;
            display: block;
            top:0;
            right: 0;
            border-radius: 0;
        }
        .next_tr{
            display: none;
        }
    </style>
</head>
<body>
<div class="top_login">
    <img src="__IMAGES__/erp.png"/>
</div>
<div class="forget_box">
    <h1>找回密码</h1>
    <ul>
        <li>
            <span class="forget_name">手机号</span>
            <input name="phone" value="" class="base_text"/>
        </li>
        <li style="position: relative;">
            <span class="forget_name">验证码</span>
            <input name="code" value="" class="base_text"/>
            <div class="base_button send_sms">获取验证码</div>
        </li>

        <li class="next_tr">
            <span class="forget_name">新密码</span>
            <input name="password" type="password" class="base_text"/>
        </li>
        <li class="next_tr">
            <span class="forget_name">请确认</span>
            <input name="confirm_password" type="password" class="base_text"/>
        </li>

    </ul>
    <div style="text-align: center;margin-top: 20px">
        <div class="base_button next_btn">下一步</div>
        <div class="base_button next_tr">重置密码</div>
    </div>
</div>
<script>
    $('.next_btn').click(function () {
        var phone=$('[name=phone]').val();
        if(isEmpty(phone)){
            return $s.error('请输入手机号');
        }
    });
</script>

</body>
</html>
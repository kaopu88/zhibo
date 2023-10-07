<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimal-ui, maximum-scale=1.0, user-scalable=no">
    <title>{:config('app.agent_setting.agent_name')}登录中心</title>
    <link rel="stylesheet" href="__NEWSTATIC__/layui.css">
    <link rel="stylesheet" href="__NEWSTATIC__/admin/style/admin/css/login.css">
    <link rel="stylesheet" href="__NEWSTATIC__/admin/style/agent/css/cima_login.css">
</head>

<body>
<div class="cima_login" id="login_background">
    <div class="cima_login_main">
        <h3 class="t cima_title" style="font-size:31px">{:config('app.agent_setting.agent_name')}登录中心</h3>
        <form class="layui-form cima_form_style" action="" id="myform">
            <div class="cima_form_center">
                <p class="t center" style="font-size: 22px;margin-bottom: 50px;">{:config('app.agent_setting.agent_name')}登录中心</p>
                <div class="layui-form-item layui-form-item-user flex">
                    <span class="icon_user"></span>
                    <div class="layui-input-block m-l-30 w-200">
                        <input type="text" name="username" required lay-verify="user" maxlength="15" placeholder="请输入用户名" autocomplete="off" class="user layui-input input_text gray">
                    </div>
                    <div class="hr hr_user t-118"></div>
                </div>
                <span class="err_phone t-125 user_err">请输入用户名</span>
                <div class="layui-form-item flex m-b-31dot5">
                    <span class="icon_password"></span>
                    <div class="layui-input-block m-l-30 w-200">
                        <input type="password" name="password" required lay-verify="password" placeholder="请输入密码" autocomplete="off" class="password layui-input input_text gray">
                    </div>
                    <span class="eye_icon"></span>
                    <div class="hr hr_password t-192"></div>
                </div>
                <span class="err_phone t-199 password_err">请输入密码</span>
                <input type="checkbox" name="auto_login" lay-skin="primary" id="rememb_password">
                <label for="rememb_password" class="rememb">记住密码</label>
                <!--<a href="../template/foget_password.html" class="forget_password">忘记密码</a>-->
                <div class="layui-form-item">
                    <button type="button" class="layui-btn layui-btn-radius layui-btn-normal login_btn w-full web_login_btn" lay-submit="" lay-filter="login">登录</button>
                </div>
        </form>
    </div>
</div>
</div>
<script src="__NEWSTATIC__/layui.js"></script>
<script src="__NEWSTATIC__/admin/js/login.js"></script>
<script>
    var loginUrl='{:url("account/login")}';
</script>
</body>

</html>
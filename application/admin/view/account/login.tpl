<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimal-ui, maximum-scale=1.0, user-scalable=no">
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="x5-fullscreen" content="true" />
    <meta name="full-screen" content="yes" />
    <title>登录中心-{:APP_NAME}管理中心</title>
    <link rel="stylesheet" href="__NEWSTATIC__/layui.css">
    <link rel="stylesheet" href="__NEWSTATIC__/admin/style/admin/css/login.css">
</head>

<body>
<!-- pc -->
<div class="login layui-hide-xs layui-show-sm-flex" id="login_background">
    <div class="login_left">
        <div class="login_left_container">
            <div class="bingxin_logo">
                <a href="#">
                    <img src="{:img_url('','200_200','logo');}" alt="">
                </a>
            </div>
            <div class="login_text">
                <p class="login_text_top">{:APP_NAME}</p>
                <p class="login_text_top">{:APP_SLOGAN}</p>
                <p class="login_text_bottom">
                    —{:APP_NAME}后台管理系统</p>
            </div>
        </div>
    </div>
    <div class="login_main">
        <ul class="login_nav">
            <li>
                <span class="union icon"></span>
                <a href="/agent">公会</a>
            </li>
            <li>
                <span class="broker icon"></span>
                <a href="/promoter">经纪人</a>
            </li>
            <li>
                <span class="live icon"></span>
                <a href="/">官网</a>
            </li>
            <li class="m-r-0">
                <span class="contact_icon icon"></span>
                <a class="contact_us">联系我们</a>
            </li>
        </ul>
        <div class="login_center login_welcome">
            <h2 class="welcome">Welcome!</h2>
            <form class="layui-form form_style" action="" id="myform">
                <div class="layui-form-item layui-form-item-user flex">
                    <span class="icon_user"></span>
                    <div class="layui-input-block m-l-30 w-200">
                        <input type="text" name="username" required lay-verify="user" maxlength="15" placeholder="请输入用户名" autocomplete="off" class="user layui-input input_text">
                    </div>
                    <div class="hr hr_user user_top"></div>
                </div>
                <span class="err_phone user_err">请输入手机号或邮箱</span>
                <div class="layui-form-item layui-form-item-user flex">
                    <span class="icon_password"></span>
                    <div class="layui-input-block m-l-30 w-200">
                        <input type="password" name="password" required lay-verify="password" placeholder="请输入密码" autocomplete="off" class="password layui-input input_text">
                    </div>
                    <div class="hr hr_user user_top"></div>
                    <span class="eye_icon"></span>
                    <div class="hr hr_password password_top"></div>
                </div>
                <span class="err_phone t-120 password_err">请输入密码</span>
                <input type="checkbox" name="auto_login" lay-skin="primary" id="rememb_password">
                <label for="rememb_password" class="rememb">记住密码</label>
                <div class="layui-form-item">
                    <button type="button" class="layui-btn layui-btn-radius layui-btn-normal login_btn w-full web_login_btn" lay-submit="" lay-filter="login">登录</button>
                </div>
            </form>
        </div>
        <div class="contact">
            <span class="contact_text">联系我们</span>
            <div class="contact_us_img">
                <img src="{$site.qrcode_wx}" alt="">
            </div>
            <button type="button" class="layui-btn layui-btn-radius layui-btn-normal back_btn">返回</button>
        </div>
    </div>
</div>
<!-- app -->
<div class="login layui-hide-sm layui-show-xs-flex" id="login_background">
    <div class="login_main_app">
        <div class="login_center_app login_welcome">
            <div>
                <h2 class="welcome">欢迎,</h2>
                <h2 class="welcome">{:APP_NAME}后台管理!</h2>
            </div>
            <form class="layui-form form_style_app" action="" >
                <div class="layui-form-item layui-form-item-user flex">
                    <span class="icon_user"></span>
                    <div class="layui-input-block m-l-30 w-200">
                        <input type="text" name="app_username" required lay-verify="user_app" maxlength="15" placeholder="请输入用户名" autocomplete="off" class="user_app layui-input input_text">
                    </div>
                    <div class="hr hr_user user_top"></div>
                </div>
                <span class="err_phone user_err">请输入手机号或邮箱</span>
                <div class="layui-form-item layui-form-item-user flex">
                    <span class="icon_password"></span>
                    <div class="layui-input-block m-l-30 w-200">
                        <input type="password" name="app_password" required lay-verify="password_app" placeholder="请输入密码" autocomplete="off" class="password_app layui-input input_text">
                    </div>
                    <div class="hr hr_user user_top"></div>
                    <span class="eye_icon"></span>
                    <div class="hr hr_password password_top"></div>
                </div>
                <span class="err_phone t-120 password_err">请输入密码</span>
                <input type="checkbox" name="app_auto_login" lay-skin="primary" id="rememb_password_app">
                <label for="rememb_password_app" class="rememb">记住密码</label>
                <div class="layui-form-item">
                    <button type="button" class="layui-btn layui-btn-radius layui-btn-normal login_btn w-full app_login_btn" lay-submit="" lay-filter="login">登录</button>
                </div>
            </form>
            <ul class="login_nav_app">
                <li>
                    <span class="union icon"></span>
                    <a href="/agent">公会</a>
                </li>
                <li>
                    <span class="broker icon"></span>
                    <a href="/promoter">经纪人</a>
                </li>
                <li>
                    <span class="live icon"></span>
                    <a href="/">官网</a>
                </li>
                <li class="m-r-0">
                    <span class="contact_icon icon"></span>
                    <a class="contact_us">联系我们</a>
                </li>
            </ul>
        </div>
        <div class="contact">
            <span class="contact_text">联系我们</span>
            <div class="contact_us_img">
                <img src="{$site.qrcode_wx}" alt="">
            </div>
            <button type="button" class="layui-btn layui-btn-radius layui-btn-normal back_btn">返回</button>
        </div>
    </div>
</div>
<script src="__NEWSTATIC__/layui.js"></script>
<script src="__NEWSTATIC__/admin/js/login.js"></script>
<script>
    var loginUrl = '{:url("login")}';
</script>

<script>
    {:TONGJI_CODE}
</script>
</body>

</html>
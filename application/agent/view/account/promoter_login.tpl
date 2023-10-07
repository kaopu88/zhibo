<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>{:config('app.agent_setting.promoter_name')}登录中心</title>
    <link rel="stylesheet" href="__CSS__/promoter_style.css?v=__RV__" type="text/css" media="all"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <!-- -->
    <script>var __links = document.querySelectorAll('a');function __linkClick(e) { parent.window.postMessage(this.href, '*');} ;for (var i = 0, l = __links.length; i < l; i++) {if ( __links[i].getAttribute('data-t') == '_blank' ) { __links[i].addEventListener('click', __linkClick, false);}}</script>
    <script src="__VENDOR__/jquery.min.js?v=__RV__"></script>
    <script src="__VENDOR__/layer/layer.js?v=__RV__"></script>
    <script src="__VENDOR__/smart/smart.bundle.js?v=__RV__"></script>
    <script>
        var loginUrl='{:url("account/promoter_login")}';
    </script>
</head>
<body>

<!-- contact-form -->
<div class="message warning">
    <div class="inset">
        <div class="login-head">
            <h1>{:config('app.agent_setting.promoter_name')}登录中心</h1>
        </div>
        <form>
            <li>
                <input type="text" class="text" name="username" placeholder="用户名/手机号" onfocus="this.value = &#39;&#39;;"><a href="javascript:;" class=" icon user"></a>
            </li>
            <div class="clear"> </div>
            <li>
                <input type="password" name="password" onfocus="this.value = &#39;&#39;;" onblur="if (this.value == &#39;&#39;) {this.value = &#39;Password&#39;;}"> <a href="javascript:;" class="icon lock"></a>
            </li>
            <div class="clear"> </div>
            <div class="submit">
                <input class="login_btn" style="border-radius: 10px;" type="submit" value="登 录"/>
                <div class="clear">  </div>
            </div>

        </form>
    </div>
</div>

<div class="clear"> </div>
<!--- footer --->
<div class="footer">
    <p>Copyright © 2019.<a target="_blank" href="/">{:config('site.company_full_name')}</a> All rights reserved.</p>
</div>
<script src="__JS__/account/login.js?v=__RV__"></script>
</body>
</html>
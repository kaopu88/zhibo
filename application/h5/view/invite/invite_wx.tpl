<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>{$product_slogan} 邀请您体验{$product_name}服务~</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1">
    <link rel="stylesheet" href="/bx_static/css/layui.css" />
    <script src="__VENDOR__/layer/mobile/layer.js?v=__RV__"></script>
</head>

<body>

</body>
<script>
    var wxConfigJson = '{:htmlspecialchars_decode($jsapi_config)}', wxConfig = {};
    if (wxConfigJson != '') {
        try {
            wxConfig = JSON.parse(wxConfigJson);
        } catch (e) {
        }
    }

    function isWeixin() {
        var ua = navigator.userAgent.toLowerCase();
        if (ua.match(/MicroMessenger/i) == "micromessenger") {
            return true;
        } else {
            return false;
        }
    }

    if (!isWeixin()) {
        layer.open({
            content: '请在微信中打开此页面'
            , btn: '好的'
        });
    }
</script>
</html>

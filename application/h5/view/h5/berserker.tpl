<!doctype html> 
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>狂暴巨鳄</title>
    <meta name="keywords" content="" />
    <meta name="description" content="" />
    <meta name="HandheldFriendly" content="true" />
    <meta name="MobileOptimized" content="width" />
    <meta id="viewport" content="width=device-width, user-scalable=no,initial-scale=1" name="viewport" />
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta content="black" name="apple-mobile-web-app-status-bar-style" />
    <meta content="telephone=no" name="format-detection" />
    <link href="__H5__/css/h5display/all.css" type="text/css" rel="stylesheet"/>
    <link href="__H5__/css/h5display/index.css" type="text/css" rel="stylesheet"/>
    <style>
        body { background-color: #000000; }
    </style>
</head>
<body>
    <div class="" id="slider">
        <section class="main-page current">
            <div>
                <img src="__H5__/images/h5display/berserker_01.jpg?v=__RV__" class="img100" style="display:block;" />
            </div>
            <div>
                <img src="__H5__/images/h5display/berserker_02.jpg?v=__RV__" class="img100" style="display:block;" />
            </div>
            <div>
                <img src="__H5__/images/h5display/berserker_03.jpg?v=__RV__" class="img100" style="display:block;" />
            </div>

            <div style="position: relative;">
                <img src="__H5__/images/h5display/berserker_04.jpg?v=__RV__" class="img100" style="display:block;" onclick="toWxApp()">
            </div>
            <div>
                <img src="__H5__/images/h5display/berserker_05.jpg?v=__RV__" class="img100" style="display:block;" />
            </div>
        </section>
    </div>
</body>

<script src="__VENDOR__/bugujsdk.js?v=__RV__"></script>
<script>

    function toWxApp() {
        dssdk.ready(function (sdk) {
            sdk.openWxAPP({
                userName: 'gh_01081f93d2e9',
                miniProgramType: 0,
                path: '/pages/movie/detail?id=19'
            });
        });
    }
</script>

</html>
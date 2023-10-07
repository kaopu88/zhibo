<include file="public/head" />
<title>{$explain.title}</title>
<link rel="stylesheet" href="__H5__/css/user_task/common.css?{:date('YmdHis')}">
<script src="__VENDOR__/jquery.min.js?v=__RV__"></script>
<script src="__VENDOR__/layer/layer.js?v=__RV__"></script>
<script src="__H5__/js/css-base.js?v=__RV__"></script>
</head>
<body class="explain">

<header class="mui-bar mui-bar-nav">
    <a class="mui-icon" href="javascript:history.back(-1);"><img src="__H5__/images/user_task/ico-back2.png"></a>
    <h1 class="mui-title">{$explain.title}</h1>
</header>

<div class="container">
    <div class="page-content">
        {:htmlspecialchars_decode($explain.content)}
    </div>
</div>

<script src="__VENDOR__/bugujsdk.js?v=__RV__"></script>

<script>
    dssdk.ready(function (sdk) {
        sdk.getDeviceInfo(function (result2) {
            if (typeof result2 == 'object' && result2 !== null) {
                if ( parseInt(result2.notch_screen_height) > 0 ) {
                    $('.mui-bar').css("padding-top", result2.notch_screen_height + "px");
                    $('.mui-bar').css("height", (parseInt(result2.notch_screen_height) + 44) + "px");
                    $('.container').css("padding-top", (parseInt(result2.notch_screen_height) + 44) + "px");
                }
            }
        });
    });
</script>
</body>
</html>
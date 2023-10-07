<include file="public/head" />
<title>爱的供养</title>
<script type="text/javascript" charset="UTF-8" async="" src="__H5__/js/activity/adaptation.js"></script>
<link href="__H5__/css/activity/common.css" rel="stylesheet" />
<link href="__H5__/css/activity/love-raise.css" rel="stylesheet" />
<link href="__H5__/css/activity/load.css" rel="stylesheet" />
<script type="text/javascript" src="__VENDOR__/require.js" data-main="__VENDOR__/common/main"></script>
<script src="__VENDOR__/bugujsdk.js?v=__RV__"></script>
<script>
    var jump = function ($url) {
        if ($url == '') return false;
        dssdk.ready(function (sdk) {
            sdk.goTo({url:$url});
        })
    };
</script>
</head>
<body>
    <div class="loading" id="globalLoading" style="display: none;"></div>
    <main class="p-gift-scramble3">

        <header class="p-gift-scramble3-header">
            <img src="__H5__/images/activity/love_raise/top.png" alt="爱的供养" class="p-gift-scramble3-header-slogan" />
        </header>

        {__CONTENT__}

        <footer class="main-footer">
            本活动最终解释权归{:APP_NAME}所有(本活动与苹果公司无关)
        </footer>
    </main>
</body>
</html>
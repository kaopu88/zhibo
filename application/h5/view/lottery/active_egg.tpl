<include file="public/head"/>
<title>砸金蛋</title>
<meta content="新一代专注于交互的社交平台，其愿景是提供在虚拟环境中人和人连接的最高效交互方式。" name="description">
<meta content="直播,视频,游戏,社交" name="keywords">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black">
<meta name="format-detection" content="telephone=no">
<link rel="stylesheet" href="__STATIC__/lottery/css/common_mobile.css">
<link rel="stylesheet" href="__STATIC__/lottery/css//index.css">
<script>
    var html = document.querySelector('html');
    changeRem();
    window.addEventListener('resize', changeRem);

    function changeRem() {
        var width = html.getBoundingClientRect().width;
        html.style.fontSize = width / 10 + 'px';
    }
</script>
</head>
<body class="bd-goldegg">
<div id="wrap">
    <div class="bg"></div>
    <div class="rule"></div>
    <a href="" id="myWin">
        <div class="my">我的奖品</div>
    </a>
    <!--砸蛋区域-->
    <div class="box">
        <ul class="egg clearfix">
            <li>
                <img src="__STATIC__/lottery/image/egg.png" class="goldegg init">
                <img src="__STATIC__/lottery/image/base.png">
                <div class="info" style="display: none;"></div>

            </li>
            <li>
                <img src="__STATIC__/lottery/image/egg.png" class="goldegg init">
                <img src="__STATIC__/lottery/image/base.png">
                <div class="info" style="display: none;"></div>
            </li>
            <li>
                <img src="__STATIC__/lottery/image/egg.png" class="goldegg init">
                <img src="__STATIC__/lottery/image/base.png">
                <div class="info" style="display: none;"></div>
            </li>
            <li>
                <img src="__STATIC__/lottery/image/egg.png" class="goldegg init">
                <img src="__STATIC__/lottery/image/base.png">
                <div class="info" style="display: none;"></div>
            </li>
            <li>
                <img src="__STATIC__/lottery/image/egg.png" class="goldegg init">
                <img src="__STATIC__/lottery/image/base.png">
                <div class="info" style="display: none;"></div>
            </li>
            <li>
                <img src="__STATIC__/lottery/image/egg.png" class="goldegg init">
                <img src="__STATIC__/lottery/image/base.png">
                <div class="info" style="display: none;"></div>
            </li>
        </ul>
        <div id="hammer" class="shak"></div>
    </div>
    <div class="cpbox">
        <div class="left">我的余额：<span id="balance"> {$bean} </span></div>
        <div class="right"> {$config['lottery_egg_bean']}{$bean_name}/次</div>
    </div>
    <!--游戏规则弹窗-->
    <div id="mask-rule">
        <div class="box-rule">
            <span class="star"></span>
            <h2>玩法规则说明</h2>
            <span id="close-rule"></span>
            <div class="con">
                <div class="text">
                    {$config['lottery_egg_desc']}
                </div>
            </div>
        </div>
    </div>
    <!--中奖提示-->
    <div id="mask">
        <div class="blin"></div>
        <div class="caidai"></div>
        <div class="winning">
            <div class="red-head"></div>
            <div class="red-body"></div>
            <div id="card">
                <a href="javascript:void(0);" class="win"></a>
            </div>
            <a href="javascript:void(0);" target="_self" class="btn"></a>
            <span id="close"></span>
        </div>
    </div>
</div>
<script type="text/javascript" src="__STATIC__/lottery/js/jquery.js"></script>
<script src="__STATIC__/lottery/js/jquery_002.js"></script>
<script src="__STATIC__/lottery/js/ck_002.js"></script>
<script src="__STATIC__/lottery/js/h5_game_common.js"></script>
<script src="__STATIC__/lottery/js/index.js"></script>
<script src="__STATIC__/lottery/js/ck.js" type="text/javascript"></script>
<script type="text/javascript">
    var _DATA = {
        //基础配置
        'siteUrl' : '',
        'cdnUrl' : '',
        'ckUri' : '{:LOCAL_PROTOCOL_DOMAIN}',
        'userId' : '{$user_id}',
        'token' : '{$token}',
        'roomId' : '',
    };
</script>
</body>
</html>
<include file="public/head" />
<title>甜蜜大作战</title>
<link rel="stylesheet" href="__H5__/css/share/common.css">
<style type="text/css">
    .btnss img{
        width: 100%;
    }
    .btnss{
        width: 17%;
        position: fixed;
        top: 38%;
        right: 5px;
    }
    img{
        width: 100%;
        float: left;
    }

    body .layui-layer{background-color: transparent;}
    body .layui-layer-setwin .layui-layer-close2{
        right: 10px;
        top: 10px;
        height: 40px;
        width: 40px;
        background-position: 0;
    }
    body .layui-layer-ico{
        background: url(__H5__/images/live_act/sweetBattle/cancel.png) no-repeat center;
        background-size: 100% 100%;
    }
</style>
</head>
<body>
<div class="" id="slider">
    <section class="main-page current">
        <div>
            <img src="__H5__/images/live_act/sweetBattle/1.png?v=__RV__" />
        </div>
        <div style="position: relative;z-index: 99;">
            <img src="__H5__/images/live_act/sweetBattle/2.png?v=__RV__"/>
            <div class="btnss">
                <a href="javascript:;" onclick="digg(this)"><img src="__H5__/images/live_act/sweetBattle/baoming.png?v=__RV__" alt=""></a>
            </div>
        </div>
        <div>
            <img src="__H5__/images/live_act/sweetBattle/3.png?v=__RV__"/>
        </div>
        <div>
            <img src="__H5__/images/live_act/sweetBattle/4.png?v=__RV__"/>
        </div>
    </section>
</div>
</body>


<script src="__VENDOR__/jquery.min.js?v=__RV__"></script>
<script src="__VENDOR__/layer/layer.js?v=__RV__"></script>
<script src="__VENDOR__/bugujsdk.js?v=__RV__"></script>
<script src="__H5__/js/css-base.js?v=__RV__"></script>
<script>

    var user_id = '';

    dssdk.ready(function (sdk) {
        sdk.getUser(function (result) {
            if (typeof result == 'object' && result !== null) {
                user_id = result.user_id
            }
        });
    });


    var digg = function ($this) {
        layer.open({
            type: 2,
            title: false,
            shadeClose: true,
            shade: 0.5,
            anim: 2,
            closeBtn: 2,
            resize:false,
            scrollbar:false,
            area: ['100%', '90%'],
            content: "{:url('entry')}"+'?user_id='+user_id
        });
    };

</script>

</html>

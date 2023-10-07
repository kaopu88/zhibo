<include file="public/head" />
<title>个人任务</title>
<link rel="stylesheet" href="__H5__/css/user_task/common.css?{:date('YmdHis')}">
<link rel="stylesheet" type="text/css" href="/bx_static/h5.css"/>
<script src="__VENDOR__/jquery.min.js?v=__RV__"></script>
<script src="__VENDOR__/layer/layer.js?v=__RV__"></script>
<script src="/bx_static/media_auto.js"></script>
<script>
    var user_id = {$user_id};
</script>
</head>

<body class="gift_exchange">


<div class="taskHD">
    <div class="task-tab">
        <ul class="clearfix">
            <li><a href="{:url('index',['user_id'=>$user_id])}">{$milletname}任务</a></li>
            <li class="action">{$milletname}兑换</li>
        </ul>
    </div>
    <div class="integral">
        <div class="today">
            <div class="today-point">我的{$milletname}</div>
            <div class="point">{$user_info.points|default="0"}</div>
        </div>
        <div class="my">
            <div class="title">兑换金币</div>
            <div class="point">{$diamonds|default="0"}</div>
        </div>
    </div>
    <div class="detail">
        <div class="rule">活动规则</div>
        <div class="exchange"><a href="{:url('exchangeList',['user_id'=>$user_id])}">兑换记录</a></div>
    </div>
</div>

<div class="recharge">
        <volist name="exchangeArray" id="vo">
            <div class="">
                <p class="jewel"  data="{$vo.number}">{$vo.number}金币</p>
                <p class="integral1" data="{$vo.integral}" >{$vo.integral}{$milletname}</p>
            </div>
        </volist>

</div>

<div class="for_instructions" >
</div>
<div class="immediately_change">
    <div class="icon" id="exchange" ></div>
</div>
<div id="activity_rules" style="display: none">
    <div class="activity_rules_container">
    </div>
</div>
<div id="click_immediately_change" style="display: none">
    <div class="click_immediately_change_container">
        <p class="title">确认提交</p>
        <p class="info">{$milletname}将会在1~3个工作日到账，记的查收哦~</p>
    </div>
</div>
<script src="__VENDOR__/bugujsdk.js?v=__RV__"></script>
<script>
    var exchange = '';
    var useru_id = "{$user_id}";
    $(function () {
        $( htmlDecode("{$exchangNotice.content}")).appendTo(".activity_rules_container");
        var tempcontent = htmlDecode("{$exchangExplain.content}");
       $(tempcontent).appendTo(".for_instructions");
        function navChangeArea() {
            var scrollTop = document.documentElement.scrollTop || document.body.scrollTop;
            if (scrollTop < 90) {
                $('.mui-bar').css("background-image", "none");
                $('.mui-bar').css("background-color", "transparent");
            } else {
                $('.mui-bar').css("background-image", "url(https://static.cnibx.cn/bg_header.png?imageView2/2/w/750)");
                $('.mui-bar').css("background-position", "bottom");
                $('.mui-bar').css("background-size", "100%");
            }
        }
        $(window).bind("scroll", function() {
            navChangeArea();
        });
        $(window).bind("touchmove", function() {
            navChangeArea();
        });

        dssdk.ready(function (sdk) {
            //获取已登录用户信息
            sdk.getUser(function (result) {
                if (typeof result == 'object' && result !== null) {
                    if (result.user_id != '' && user_id != result.user_id) {
                        window.location.replace("/task/index?user_id=" + result.user_id);
                    }
                }
            });

            sdk.getDeviceInfo(function (result2) {
                if (typeof result2 == 'object' && result2 !== null) {
                    if ( parseInt(result2.notch_screen_height) > 0 ) {
                        $('.mui-bar').css("padding-top", result2.notch_screen_height + "px");
                        $('.mui-bar').css("height", (parseInt(result2.notch_screen_height) + 44) + "px");
                        $('.taskHD').css("padding-top", (parseInt(result2.notch_screen_height) + 44) + "px");
                    }
                }
            });

            //ios 240+ js 返回按钮
            $('.mui-icon').click(function () {
                sdk.navigateBack();
            });

        });
    });
function htmlDecode (text){
        //1.首先动态创建一个容器标签元素，如DIV
        var temp = document.createElement("div");
        //2.然后将要转换的字符串设置为这个元素的innerHTML(ie，火狐，google都支持)
        temp.innerHTML = text;
        //3.最后返回这个元素的innerText(ie支持)或者textContent(火狐，google支持)，即得到经过HTML解码的字符串了。
        var output = temp.innerText || temp.textContent;
        temp = null;
        return output;
    }
function exchange() {
    alert(1);

}
</script>
<script src="/static/h5/js/task/redeem.js"></script>

</body>
</html>
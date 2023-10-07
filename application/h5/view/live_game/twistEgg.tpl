    <include file="public/head" />
    <title>{$title|default='欢乐扭蛋机'}</title>
    <link rel="stylesheet" href="__H5__/css/share/common.css">
    <link rel="stylesheet" href="__H5__/css/game/twistEgg.css">
</head>
<body>
    <div class="machine">
        <img src="__H5__/images/live_act/twistEgg/eca3ab7e12d5a7eb5.png" alt="" style="width: 100%;float: left;">
        <div class="glassWrap">
            <foreach name="prize" id="vo" key="index">
                <img src="{$vo}" alt="" class="ball ani-ballDown{$index+1}" />
            </foreach>
        </div>
        <div class="prizeBallWrap">
            <img src="__H5__/images/live_act/twistEgg/liwu.png" alt="" class="prizeBall" />
        </div>
        <div>
            <a class="gameStart"></a>
            <img id="pointer" src="__H5__/images/live_act/twistEgg/pointer.png" alt="" class="xs" />
        </div>
        <div class="rule">
            <a href="javascript:void(0);" onClick="rule(this)"></a>
        </div>
        <div style="width: 100%;float: left;overflow: hidden;">
            <div class="user_prize">
                <div class="prize">
                    <foreach name="user_prize" id="vo">
                        <if condition="$e eq 1">
                            <div class="prize-back" style="filter: opacity(25%);">
                        <else/>
                            <div class="prize-back">
                        </if>
                            <img src="{$vo.icon}" alt="">
                            <div id="prize-info">
                                <span style="display: none;">{$vo.prize_id}</span>
                                <span>&times;</span>
                                <span>{$vo.num}</span>
                            </div>
                        </div>
                    </foreach>
                </div>
                <div class="balance">
                    <img src="__H5__/images/live_act/twistEgg/doudou.png" alt="" />
                    <a href="javascript:void(0);">
                        <span id="balance">{$balance|default=0}</span>
                    </a>
                    <img src="__H5__/images/live_act/twistEgg/right.png" alt="">
                    <input type="hidden" value="{$user_id}" id="user_id">
                </div>
            </div>
        </div>
    </div>
    <div id="toast" style="display: none;">
        <include file="activity_component/twistTips" />
    </div>
</body>

<script type="text/javascript" src="http://apps.bdimg.com/libs/zepto/1.1.4/zepto.min.js"></script>
<script src="__H5__/js/css-base.js"></script>
<script src="__STATIC__/common/js/layer_mobile/layer.js"></script>
<script src="__H5__/js/game/twistEgg.js"></script>
<script src="__VENDOR__/bugujsdk.js"></script>

<script>
    dssdk.ready(function (sdk) {
        $('#balance').click(function() {
            sdk.recharge();
        });
    });

    function rule (obj) {
        layer.open({
            btn: '我知道了',
            anim: 'up',
            content: '<div class="rule_html">{:htmlspecialchars_decode($rule_html.content)}</div>'
        });
    }

    $(function() {

        var error = function (msg) {
            layer.open({
                btn: '我知道了',
                anim: 'up',
                content: msg,
            });
        };

        $(".gameStart").on("click", function(e) {

            var user_id = $('#user_id').val();

            if (user_id == '')
            {
                error('请登录!');
                return false;
            }

            egg.init({url:'/Live_game_twist_egg/twistTurntable', user_id:user_id, error: error, success: egg.gameOver});
        });

        $('.close_stop_tip').click(egg.closeBox);
    });
</script>



</html>
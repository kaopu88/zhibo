<include file="public/head" />
<title>{$data.title}</title>
<link rel="stylesheet" href="__H5__/css/share/common.css">

<style>
    html{
        background: transparent;
    }

    html,body{
        -moz-user-select: none;
        -khtml-user-select: none;
        user-select: none;
    }

    .container{
        background: url("__H5__/images/live_act/christmas/live.png") no-repeat left top;
        background-size: 100% auto;
        font-size: 0.6rem;
        text-align: left;
        position: relative;
    }

    .container div{
        position: absolute;
        top: 3rem;
        width: 100%;
        display: flex;
        align-items: center;
        flex-wrap: wrap;
    }
    .container p{
        line-height: .9rem;
        background: #5f59bc;
        width: 83%;
        margin: .1rem auto;
        color: #fff;
        box-sizing: border-box;
        height: .9rem;
        padding-left: .2rem;
        overflow: hidden;
        border-radius: .2rem;
    }

</style>
</head>

<body class="container">
        <a href="/Live_act/activityRank">
            <div>
                <if condition="$data['is_stop'] eq 0">
                    <p>热度值：<span id="score">{$data.score|default=0}</span></p>
                    <p>奖励值：<span id="reward">{$data.reward|default=0}</span></p>
                    <p>排名：<span id="rank">{$data.rank|default=0}</span></p>
                <elseif condition="$data['is_stop'] eq -1" />
                    <p style="text-align: center;">活动尚未开始</p>
                <else />
                    <p style="text-align: center;">活动排名</p>
                    <p style="line-height: 2.2rem;height: 2.2rem;text-align: center;font-size: 2rem;">{$data.rank|default=0}</p>
                </if>
            </div>
        </a>
    </body>

<script src="__VENDOR__/jquery.min.js?v=__RV__"></script>
<script src="__H5__/js/css-base.js"></script>
<script src="__VENDOR__/TcPlayer/TcPlayer-2.3.1.js" charset="utf-8"></script>
<script src="__H5__/js/ws.js"></script>

<script>

    var room_id = "{$data.room_id}";
    var score = $('#score');
    var rank = $('#rank');
//    var container = $('.container');
    var reward = $('#reward');

    function activityLive(data)
    {
        if (data.is_stop == 0) {
            score.text(data.score);
            rank.text(data.rank);
            reward.text(data.reward);
        }else if(data.is_stop == 1) {
            score.parent('p').text('活动排名').css('text-align', 'center');
            rank.parent('p').css({'line-height':'2.2rem', 'height':'2.2rem', 'text-align':'center', 'font-size':'2rem'});
            reward.parent('p').remove();
        }else{
            score.parent('p').text('活动尚未开始').css('text-align', 'center');
            rank.parent('p').remove();
            reward.parent('p').remove();
        }

        /*var img = container.css('background-image');

        img = img.replace(/[\(\)\"\']/g, "");

        var nowimg = img.split('/').pop();

        if (data.image != nowimg)
        {
            container.css('background-image', 'url(/static/h5/images/live_act/guardNight/'+data.image+')')
        }*/
    }

    //初始化链接地址
    ws.init({url:"{$ws_url}", 'port':5555}).connect();

  //接收信息
     ws.onMessage = function(message) {

         var className = message.emit;

         if(typeof(window[className]) === "function")
         {
             eval(className+"(message.data)");
         }
     };


     if (room_id != '')
     {
         ws.send('{"mod": "Live","act": "connectH5","args": {"room_id":"'+room_id+'"}}');
     }

</script>

</html>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>关于我们</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0"/>
    <meta name="apple-mobile-web-app-capable" content="yes"/>
    <meta name="apple-mobile-web-app-status-bar-style" content="black"/>
    <meta name="format-detection" content="telephone=no"/>
    <link rel="stylesheet" href="__CSS__/common.css?v=__RV__">
    <link rel="stylesheet" type="text/css" href="/bx_static/h5.css"/>
    <style>
    .layui-layer{
        display: flex;
        flex-direction: column;
        justify-content: space-around;
        align-items: center;
    }
    .layui-layer-title,.layui-layer-setwin{
        display:none;
    }
    .layui-layer#layui-layer100001 .layui-layer-btn{
        height: 1.024rem;
        display: flex;
    }
    .layui-layer-dialog .layui-layer-content{
        font-size:0.26rem;
        text-align:center;
        ​padding-top: 0.853rem;
        font-family:'PingFangSC-Semibold';
    }
    .layui-layer-btn .layui-layer-btn0,.layui-layer-btn .layui-layer-btn1{
        font-size: 0.239rem;
        height: 0.614rem;
        width: 1.962rem;
        border-radius: 0.375rem;
        text-align: center;
        line-height: 0.614rem;
    }
    .vcp-poster-pic.cover, .vcp-poster-pic.default{
        width: 100vw;
        height: 100vw;
    }
    .layui-layer-btn .layui-layer-btn0{
        background-color: #ff2d52;
        border-color: #ff2d52;
        color: white;
    }
    .layui-layer-btn .layui-layer-btn1{
        border-color: #f5f9fc;
        background-color: #f5f9fc;
        color: #282828;
    }
    #live_player{
        position: absolute;
        top:0;
        height: 100vw !important;
    }
    .vcp-player{
        height: 100vw !important;
        position: absolute;
        top: 0;
        z-index: -1;
    }
    .vcp-error-tips{
        font-size:0.239rem;
    }
    .vcp-player video{
        height: 100vw !important;
    }
    .vcp-controls-panel{
        display:none;
    }
    .vcp-bigplay{
        position: absolute;
        top: 50%;
        left: 50%;
        height: 1.126rem;
        width: 1.126rem;
        transform: translate(-50%, -50%);
        margin-left: 0;
    }
    .login_container{
        margin-left: 50%;
        transform: translateX(-50%);
        width: fit-content;
        position: relative;
    }
    .login_input{
        outline: none;
        border: none;
        width: 4.62rem;
        height: 0.648rem;
        line-height: 0.648rem;
        background-color: #F5F9FC;
        border-radius: 0.204rem !important;
        padding-left: 0.17rem;
        margin-top: 0.462rem;
    }
    body .rules-class .activity_rules_container h4{
        font-size: 0.341rem;
    }
    body .rules-class .layui-layer-btn .layui-layer-btn0 {
        height: 0.682rem;
        width: 3.41rem;
        font-size: 0.273rem;
        line-height: 0.682rem;
    }
    .err_toast{
        color:#ff2d52;
        font-size:0.204rem;
        position: absolute;
        right: 0;
        top:1.143rem;
        display:none;
    }
    </style>
</head>
<body>
<div class="body-content">
    <div id="bannerTop" class="banner-top">
        <div class="user-wrap">
            <div class="avator" style="background-image:url({$user_info.avatar})"></div>
            <div class="info-wrap">
                <div class="name-wrap">{$user_info.nickname}</div>
                <div class="txt-wrap">正在{:APP_NAME}直播</div>
            </div>
        </div>
        <div class="banner-btn" id="download">去看</div>
    </div>
    <div class="open_app_hint">打开{:config('app.product_setting.prefix_name')}TV观看，不卡不延迟，超清晰 > </div>
    <if condition="!empty($live_info)">
        <div class="video-wrap">
            <div class="bg" style="background-image: url({$his_live.cover})"></div>
            <div class="player-wrap horizen-video" id="video"></div>
            <div class="video-mask"></div>

            <div class="video-user" id="videoUser">
                <div class="tool">
                    <div></div>
                    <div></div>
                </div>
                <div class="invite">主播邀你来聊天 </div>
            </div>
            <div class="video-info" id="videoInfo">
                <div id="live_player" style="width:100%; height:auto;">
                    <div class="video_user_info">
                        <div class="anchor_info">
                            <div class="anchor_img" style="background-image:url({$user_info.avatar})"></div>
                            <div class="anchor_detail">
                                <div class="anchor_name">💫{$user_info.nickname} 🌕 </div>
                                <div class="views_number">{$audience|count}</div>
                            </div>
                            <div class="concern">关注</div>
                        </div>
                        <ul>
                            <volist name="audience" id="audience">
                                <li><div style="background-image:url({$audience.avatar})"></div></li>
                            </volist>
                        </ul>
                    </div>
                    <div class="anther_content">
                        <div class="CFI">钻石：<span>{$user_info.live_bean}</span> </div>
                        <div class="anther_account">直播号:{$user_info.user_id} </div>
                    </div>
                    <div class="chat_board">
                        
                    </div>
                    <div class="chat_board_input">
                        <input type="text" placeholder="请输入发送的消息" id="send_input">
                        <div class="send_btn">发送</div>
                    </div>
                </div>
            </div>
        </div>
        <else />
        <div class="end-wrap " id="endWrap">
            <div class="bg" style="background-image: url({$his_live.cover})"></div>
            <div class="end-title">直播即将开始</div>
            <div class="user-pic"><img class="avator" src="{$his_live.avatar}"></div>
            <div class="user-nickname">{$his_live.nickname}</div>
            <div class="user-nickname user_account">账号1000120</div>
            <!-- <div class="end-time">直播时长: {$his_live.duration}</div> -->
            <div class="attention">关注</div>
            <div class="to_live">进入{:config('app.product_setting.prefix_name')}T</div>
        </div>
    </if>
    <div class="" style="background: white;padding-top: 0.102rem;">
        <div class="dynamic_label">主播动态</div>
        <div class="dynamic_state">
            <ul class="video-list clearfix">
                <volist name='more' id='item'>
                    <li>
                        <a href="javascript:;" class="hot-video-item">
                            <div class="cover" style="background-image: url({$item.cover_url});">
                                <span class="play-btn"></span>
                                <div class="music-info">
                                    <span class="icon" style="background-image: url({$item.avatar})"></span>
                                </div>
                            </div>
                        </a>
                    </li>
                </volist>
            </ul>
        </div>
    </div>
    <div class="list-wrap">
        <div class="top-head" style="margin-top: 0.512rem;">
            <span class="dynamic_label">热门直播</span>
            <span class="underline" style="width: 80px; transform: translateX(0px) translateZ(0px);"></span>
        </div>
        <ul class="video-list clearfix">
            <volist name='livelist' id='livelist'>
                <li>
                    <a href="javascript:;" class="hot-video-item">
                        <div class="cover" style="background-image: url({$livelist.cover_url});">
                            <div class="mask"></div>
                            <span class="play-btn"></span>
                            <div class="music-info">
                                <span class="icon" style="background-image: url({$livelist.avatar})"></span>
                                <div class="info">
                                    <p class="name">{$livelist.nickname}</p>
                                </div>
                            </div>
                        </div>
                    </a>
                </li>
            </volist>
        </ul>
    </div>
    <div class="more-video" id="moreVideo">
        <div class="shade"></div>
        <p class="txt">查看更多直播</p>
        <div class="arrow"></div>
    </div>
</div>
<div class="banner-bottom">
    <div class="banner-bottom-side">
        <img src="{$h5_image.download_logo}" class="banner-img">
    </div>
    <span class="banner-btn">立即加入</span>
</div>
<div id="activity_rules" style="display: none">
    <div class="activity_rules_container">
        <h4>登陆</h4>
        <div class="login_container">
            <input type="text" placeholder="请输入用户名" class="login_input" id="uname">
            <p class="err_toast">用户名输入不正确</p>
        </div>
        <div class="login_container">
            <input type="text" placeholder="请输入密码" class="login_input" id="upass">
            <p class="err_toast">密码错误</p>
        </div>
    </div>
</div>
<script src="__VENDOR__/jquery.min.js?v=__RV__"></script>
<script src="__VENDOR__/layer/layer.js?v=__RV__"></script>
<script src="__H5__/js/css-base.js"></script>
<script src="__H5__/js/wsweb.js?date={$now}"></script>
<block name="js">
    <script src="__VENDOR__/TcPlayer/TcPlayer-2.2.2.js?v=__RV__" charset="utf-8"></script>
</block>
<script>
    $(document).ready(function(){
        // 登陆弹窗
        $('.to_live').click(function () {
            layer.open({
                id: 'login',
                title: false,
                type: 1,
                skin: 'rules-class',
                area: ['5.5rem', '6.5rem'],
                content: $('#activity_rules'),
                btn: ['登陆'],
                yes: function (index, layero) {
                    layer.close(index)
                },
            })
        })
        // 设置滚动条高度
        function setScrollHeight(){
            $('.chat_board').scrollTop( $('.chat_board')[0].scrollHeight);
        }
        $('.send_btn').click(function(){

            var islogin =sessionStorage.getItem("islogin");
            if(islogin!=1){
                layer.open({
                    id: 'login',
                    title: false,
                    type: 1,
                    skin: 'rules-class',
                    area: ['5.5rem', '6.5rem'],
                    content: $('#activity_rules'),
                    btn: ['登陆'],
                    yes: function (index, layero) {
                        var  username =  $("#uname").val();
                        var   password =    $("#upass").val()
                        $.ajax({
                            url: 'login',
                            type: 'get',
                            // 设置的是请求参数
                            data: { "username": username, "password": password },
                            // 用于设置响应体的类型 注意 跟 data 参数没关系！！！
                            dataType: 'json',
                            success: function (res) {
                                if(res.code==0){
                                    sessionStorage.setItem("islogin", 1);
                                    window.location.reload(true);
                                }
                            }
                        })

                    },
                })
            }
            var socket = ws.init(socket_conf).connect();
            var message =   JSON.parse(msg1);
            var test = $('#send_input').val();
            message.args.content   = test ;
            socket.send(JSON.stringify(message));
            setScrollHeight()
        })
        setScrollHeight()
    })
</script>
<if condition="!empty($live_info) and !empty($user_info)">

    <script>

        var socket_conf = {$ws|raw|json_encode};
        var socket_msg = '{$msg|raw|json_encode}';
        var msg1 = '{$msg1|raw|json_encode}';
        var msg2 = '{$msg2|raw|json_encode}';
        if (socket_conf && socket_msg)
        {
            //初始化链接地址
            var socket = ws.init(socket_conf).connect();
            socket.send(socket_msg);
            //接收信息
            ws.onMessage = function(message) {
               console.log(message);
                var message =   JSON.parse(message);
                var message =   JSON.parse(message);

                var className = message.emit;
                if(typeof(window[className]) === "function") eval(className+"(message.data)");
            };
        }

        function systemMsg(data){

            $(".chat_board").append("<p>"+data.content+"</p>")
            $(".chat_board p").addClass('bg_gray')
            $('.chat_board').scrollTop( $('.chat_board')[0].scrollHeight);
        }
        function lightMsg(data){

            $(".chat_board").append("<p>"+data.user_info.nice_name+':'+data.content+"</p>")
            $(".chat_board p").addClass('bg_gray')
            $('.chat_board').scrollTop( $('.chat_board')[0].scrollHeight);
        }

        function chatMsg(data){
            $(".chat_board").append("<p>"+data.user_info.nice_name+':'+data.content+"</p>")
            $(".chat_board p").addClass('bg_gray')
            $('.chat_board').scrollTop( $('.chat_board')[0].scrollHeight);
        }

        function giftMsg(data){
            $(".chat_board").append("<p>"+data.user_info.nice_name+':'+data.content+"</p>")
            $(".chat_board p").addClass('bg_gray')
            $('.chat_board').scrollTop( $('.chat_board')[0].scrollHeight);
        }

        function enterMsg(data){
            $(".chat_board").append("<p>"+data.user_info.nice_name+':'+data.content+"</p>")
            $(".chat_board p").addClass('bg_gray')
            $('.chat_board').scrollTop( $('.chat_board')[0].scrollHeight);
        }



        $(function () {
            var player = new TcPlayer('live_player', {
                "{$live_info.ext}": "{$live_info.pull}",
                "autoplay": true,
                "live" : true,
                "h5_flv": true,
                "coverpic": "{$live_info.cover_url}",
                "width": "{$live_info.w|default='414'}",
                "height": "{$live_info.h|default='736'}",
            });
        });

    </script>
</if>
</body>
</html>
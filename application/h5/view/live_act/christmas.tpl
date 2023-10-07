<include file="public/head" />
<title>{$title}</title>
<link rel="stylesheet" href="__H5__/css/share/common.css">
<style type="text/css">

    body{
        background: url(__IMAGES__/live_act/christmas/bg.png);
        background-size: 100% auto;
        background-repeat-y: repeat;
        height: auto;
    }

    img{
        width: 100%;
        vertical-align: middle;
    }

    .btn{
        position: absolute;
        top: 28rem;
        display: flex;
        width: 100%;
        justify-content: space-around;
    }
    .btn>div{
        width: 40%;
    }


    #user{
        opacity:0.3;
    }

    .section-body-avater{
        float: left;
        width: 2.5rem;
        height: 1rem;
        display: flex;
        align-items: center;
        justify-content: flex-end;

    }
    .section-body-avater span{
        font-size: 0.4rem;
    }

    .section-body-avater img{
        width: 1rem;
        border-radius: 100%;
        margin-left: .2rem;
    }

    .section-body-info{
        font-size: .25rem;
        float: left;
        width: 2.8rem;
        height: 1rem;
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        padding: .1rem 0;
        box-sizing: border-box;
        margin-left: .4rem;
    }

    .linght{
        color:#FFF;
        text-shadow: -5px -5px 20px #f600ff,
        5px -5px 20px #f600ff,
        5px 5px 20px #f600ff,
        -5px 5px 20px #f600ff;
    }

    .text-linght{
        color:#FFF;
        text-shadow: 0px -1px 10px #f600ff;
    }

    .layui-flow-more{
        font-size: .2rem !important;
        width: 100% !important;
        clear: both !important;
        text-align: center !important;
        padding-bottom: .2rem !important;
    }


    #user_rank{
        display: none;
    }

    .anchor_rank{
        width:1rem;
    }

    .anchor_rank>img{
        width: 100%;
        border-radius: 100%;
        margin-left: 0.3rem;
    }

    .anchor_cotr{
        float: right;
        width: 4.4rem;
    }

    .user_cotr_li{
        float: left;
        width: 100%;
        border-bottom: 1px dashed #70046e;
        padding-bottom: .3rem;
        margin-bottom: 0.3rem;
    }

    .user_info{
        background: url('__IMAGES__/live_act/gratitude/user_info.png') no-repeat;
        width: 55%;
        height: .6rem;
        background-size: 100% 100%;
        margin: 0 auto;
        line-height: .6rem;
        color: #B1298E;
        font-size: .26rem;
        text-align: center;
    }


    .marg{
        margin-top: 2rem;
    }


    .input{
        height: 1.8rem;
        width: 65%;
        border-radius: 3px;
        border: 1px solid #5588df;
        margin: 1rem;
        padding-left: 2px;
        color: #6271DB;
        font-size: 1rem;
    }

    .bugu{
        color: #c8c5c5;
        text-align: center;
        width: 100%;
        float: left;
        font-size: .15rem;
    }




    /*活动规则*/
    .rule{
        overflow: hidden;
        text-overflow: ellipsis;
        color: rgb(98, 113, 219);
        font-size: 0.25rem;
        margin: 0 auto;
        text-align: justify;
        padding: 0 .3rem;
        height: 3rem;
    }
    .rule p:first-child{
        padding-top: .1rem;
    }

    .rule .arrow-up {
        display: inline-block;
        vertical-align: top;
        border-bottom: 6px solid #6271db;
        border-right: 6px solid transparent;
        border-left: 6px solid transparent;
        content: "";
    }
    .rule .arrow-down {
        display: inline-block;
        vertical-align: middle;
        border-top: 6px solid #6271db;
        border-right: 6px solid transparent;
        border-left: 6px solid transparent;
        content: "";
    }


    /*排名css*/
    .gratitude_rank{
        width: 100%;
        max-height: 8rem;
        padding-top: .1rem;
    }



    /*背景*/
    .container .bg{
        background: url('__IMAGES__/live_act/christmas/mid.png');
        background-size: 100% auto;
        background-repeat-y: repeat;
    }

    .container .title{
        position: relative;
    }

    .container .title>span{
        position: absolute;
        left: 0;
        top: .25rem;
        right: 0;
        text-align: center;
        color: #5970dc;
        font-weight: 600;
        font-size: .28rem;
    }
    .container .bottom, .container .title{
        /*max-height: .5rem;*/
    }

    .container .bottom>img, .container .title>img{
        vertical-align: text-top;
    }

    .container {
        width: 88%;
        margin: 0 auto;
        clear: both;
    }


    /*获奖名单*/
    .winners{
        font-size: .3rem;
        color: #6271DB;
    }

    .winners .winners-title{
        display: flex;
        justify-content: space-around;
        font-weight: 600;
        font-size: 0.27rem;
        padding: .15rem 0;
    }

    .winners .winners-content{
        overflow: scroll;
        max-height: 3rem;
    }

    .winners .winners-content li{
        float: left;
        width: 32%;
        text-align: center;
        overflow: hidden;
        white-space: nowrap;
        text-overflow: ellipsis;
        font-size: .24rem;
    }

    .qiehuan{
        width: 100%;
        text-align: center;
        height: 2rem;
    }

    .qiehuan>div{
        position: relative;
        float: left;
        width: 50%;
        text-align: center;
    }

    .qiehuan span{
        position: absolute;
        font-size: 0.3rem;
        color: white;
        left: .55rem;
        top: .4rem;
        display: inline-block;
        width: 2rem;
        height: .7rem;
        line-height: .4rem;
    }

    .qiehuan img{
        width: 70%;
    }

</style>
<script type="text/javascript" src="http://apps.bdimg.com/libs/zepto/1.1.4/zepto.min.js"></script>
</head>

<body>

<!--头部-->
<div style="position: relative;">
    <img src="__IMAGES__/live_act/christmas/head.png" alt="">
    <img src="__IMAGES__/live_act/christmas/head-2.png" alt="">
</div>

<div class="qiehuan">
    <div>
        <img src="__IMAGES__/live_act/christmas/select.png" alt="活动规则" id="act_rule">
        <span id="act_rules">活动规则</span>
    </div>

    <div>
        <img src="__IMAGES__/live_act/christmas/noselect.png" alt="主播排名" id="rank_anchor">
        <span id="rank_anchors">主播排名</span>
    </div>
</div>

<!--活动规则-->
<div class="container" style="width: 100%;" id="rules">
    <img src="__IMAGES__/live_act/christmas/rule-1.png" alt="">
    <img src="__IMAGES__/live_act/christmas/rule-2.png" alt="">
    <img src="__IMAGES__/live_act/christmas/rule-3.png" alt="">
    <img src="__IMAGES__/live_act/christmas/rule-4.png" alt="">
</div>

<!--排名-->
<div class="container" style="display: none;" id="ranks">
    <div class="title">
        <img src="__IMAGES__/live_act/christmas/top.png" />
        <span style="display: none;">活动排名</span>
    </div>
    <div class="gratitude_rank bg">
        <include file="activity_component/list" />
    </div>
    <div class="bottom">
        <img src="__IMAGES__/live_act/christmas/bom.png" />
    </div>
</div>

<div>
    <img src="__IMAGES__/live_act/christmas/footer.png" alt="">
</div>

<div class="bugu" style="display: none;">
    *本活动最终解释权归{:APP_NAME}官方所有*
</div>
</body>
<!--<script src="__STATIC__/common/js/layer_mobile/layer.js"></script>-->
<script src="__H5__/js/css-base.js?v=__RV__"></script>
<!--<script src="__VENDOR__/jsBridge.js?v=__RV__"></script>-->

<script>

    $(function () {

        $('#act_rules').click(function () {
            $('#ranks').hide();
            $('#rules').show();
            $('#act_rule').attr('src','/static/h5/images/live_act/christmas/select.png');
            $('#rank_anchor').attr('src','/static/h5/images/live_act/christmas/noselect.png');
        });

        $('#rank_anchors').click(function () {
            $('#ranks').show();
            $('#rules').hide();
            $('#rank_anchor').attr('src', '/static/h5/images/live_act/christmas/select.png');
            $('#act_rule').attr('src','/static/h5/images/live_act/christmas/noselect.png');
        });

        /*var uid = "{$uid}";

        if (uid == '')
        {
            bugu.ready(function (sdk) {
                sdk.getUser(function (result) {
                    if (typeof result == 'object' && result !== null) {
                        if (result.user_id != '')
                        {
                            $.post('/Live_act/getUserPrizeNum', {"user_id":result.user_id}, function (res) {
                                if (res.status == 0)
                                {
                                    $('#chance').html(res.data.score);
                                    uid = result.user_id;
                                }else {
                                    $('.user_info').html('请从直播间进入查看抽奖次数');
                                }
                            });
                        }
                    }
                });
            });
        }

        function disabled(key) {
            switch (key) {
                case "noStart":
                    layer.open({
                        content: '活动尚未开始',
                        btn: '我知道了'
                    });
                    break;
                case "completed":
                    layer.open({
                        content: '活动已结束',
                        btn: '我知道了'
                    });
                    break;
            }
        }


        function clickCallback() {
            var that = this;
            $.ajax({
                type:'GET',
                url:"{$ajaxUrl}",
                data:{"user_id":uid},
                success:function(result){
                    if(result.status == 0){
                        that.opts.success_msg = result.data.prize_name;
                        that.opts.is_material = result.data.is_material;
                        that.opts.is_register = result.data.is_register;
                        that.rotate(result.data.deg_start, result.data.deg_end);
                    }else{
                        layer.open({
                            content: result.message,
                            btn: '我知道了'
                        });
                    }
                },
                timeout:1500
            });
        }


        function end(deg) {
            var msg = this.success_msg;
            var material = this.is_material;
            var register = this.is_register;
            var user_id = uid.substr(0,1)+'***'+uid.substr(-1,1);
            var myDate = new Date();
            var d = myDate.getDate();
            d = d < 10 ? ('0' + d) : d;
            var shijian   = myDate.getFullYear()+'-'+(myDate.getMonth()+1)+'-'+d;
            $('#chance').html(function(index,html){
                return html-1;
            });

            $('.titlelist').before('<li style="list-style: none;float: left;width: 32%;text-align: center;line-height: 1.5rem;overflow:hidden;white-space:nowrap;text-overflow:ellipsis;font-size: 0.9rem;color: #6271DB;">'+user_id+'</li><li style="list-style: none;float: left;width: 32%;text-align: left;line-height: 1.5rem;overflow:hidden;white-space:nowrap;text-overflow:ellipsis;font-size: 0.9rem;color: #6271DB;">'+msg+'</li><li style="list-style: none;float: left;width: 32%;text-align: center;line-height: 1.5rem;overflow:hidden;white-space:nowrap;text-overflow:ellipsis;font-size: 0.9rem;color: #6271DB;">'+shijian+'</li>');

            if (material == 1)
            {
                layer.open({
                    content: '恭喜您获得'+ msg
                    ,btn: '我知道了'
                    ,yes : function (index) {
                        layer.close(index);
                        if (register == 0)
                        {
                            //弹出窗登录地址
                            layer.open({
                                type: 1
                                ,content: '<div>\n' +
                                '    <div style="color: #6271DB;padding-left: .5rem;text-align: right;">收件人:<input id="nickname" class="input" type="text" name="nickname"></div>\n' +
                                '    <div style="color: #6271DB;padding-left: .5rem;text-align: right;">联系方式:<input id="mobile" class="input" type="text" name="mobile"></div>\n' +
                                '    <div style="color: #6271DB;padding-left: .5rem;text-align: right;">收件地址:<input id="address" class="input" type="text" name="address"></div>\n' +
                                '</div>'
                                ,anim: 'up'
                                ,style: 'width: 85%; border:none;border-radius: 6px;'
                                ,btn : '提交'
                                ,yes : function (index) {
                                    var mobile = $('#mobile').val();
                                    var address = $('#address').val();
                                    var nickname = $('#nickname').val();
                                    if (mobile != '' || address != '' || nickname != '')
                                    {
                                        $.post('/live_act/addUserAddress', {"mobile":mobile, "address":address, "nickname":nickname, 'user_id':uid}, function (res) {
                                            if (res.status == 0)
                                            {
                                                layer.open({
                                                    content: res.message
                                                    ,skin: 'msg'
                                                    ,time: 2 //2秒后自动关闭
                                                });
                                            }
                                        });
                                    }

                                    layer.close(index);
                                }
                            });
                        }
                    }
                });

            }else {
                layer.open({
                    content: '您获得的"'+msg+'"系统已自动发放'
                    ,skin: 'msg'
                    ,time: 3 //2秒后自动关闭
                });
            }
        }


        new Turntable({
            rotateNum:8,
            body:"#rotate",
            direction:0,
            disabled : disabled,
            clickCallback : clickCallback,
            end:end,
            rotateBody : ".luck_rotate_content", //转盘旋转主体选择符
            trigger: ".luck_rotate_btn" //点击触发的选择符
        });*/
    });


</script>

</html>
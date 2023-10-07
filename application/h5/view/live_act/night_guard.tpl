<include file="public/head" />
<title>{$title}</title>
<link rel="stylesheet" href="__H5__/css/share/common.css?{:date('YmdHis')}">

<style>

    html,body{
        -moz-user-select: none;
        -khtml-user-select: none;
        user-select: none;
    }

    img{
        width: 100%;
        float: left;
    }
    
    .rank-body{
        position: relative;
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

    .content{
        position: absolute;
        top: 29rem;
        width: 100%;
    }

    .content>div{
        height: 9.55rem;
        color: #fff;
        width: 91%;
        margin: 0 auto;
        overflow: scroll;
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
        font-size: .3rem !important;
        width: 100% !important;
        clear: both !important;
        text-align: center !important;
    }

    #LAY_demo2 li{
        margin-bottom: .2rem;
        float: left;
        width: 100%;
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

</style>

</head>

<body>
    <header>
        <img src="__H5__/images/live_act/guardNight/head.png" alt="">
        <img src="__H5__/images/live_act/guardNight/rule2.png" alt="">
        <img src="__H5__/images/live_act/guardNight/gift2.png" alt="">
        <img src="__H5__/images/live_act/guardNight/reward_anchor.png" alt="">
        <img src="__H5__/images/live_act/guardNight/reward_user.png" alt="">
    </header>
    <section class="rank-body">
        <div>"{$params['user_id']}"=>"{$params['room_id']}"</div>
        <img src="__H5__/images/live_act/guardNight/rank.png" alt="">
        <img src="__H5__/images/live_act/guardNight/rank_body.png" alt="">
        <div class="btn">
            <div><img src="__H5__/images/live_act/guardNight/rank_anchor.png" alt="" id="anchor"></div>
            <div><img src="__H5__/images/live_act/guardNight/rank_user.png" alt="" id="user"></div>
        </div>
        <div class="content">
            <div>
                <ul id="LAY_demo2">
                </ul>
                <ul id="user_rank">

                    <volist name="user_data" id="vo">

                        <li class="user_cotr_li">
                            <div class="anchor_rank"><img src="{$vo.avatar}" alt=""></div>
                            <ul class="anchor_cotr">
                                <volist name="vo.contr" id="uo" key="uokey">
                                    <li style="float: left;margin-bottom: .2rem;">
                                        <div class="section-body-avater" style="width: 1.8rem;"><span class="linght">{$uokey}</span><img src="{$uo.avatar}"></div><div class="section-body-info" style="width: 2rem;"><p class="text-linght text-over" style="width: 100%;">{$uo.nickname}</p><p style="color: #c13c95;width: 100%;">活动积：{$uo.score}分</p></div>
                                    </li>
                                </volist>

                            </ul>
                        </li>
                    </volist>

                </ul>
            </div>
        </div>
    </section>
    <footer>

    </footer>
</body>
<script src="__VENDOR__/jquery.min.js?v=__RV__"></script>
<script src="__H5__/js/css-base.js?v=__RV__"></script>
<script src="__VENDOR__/layer/layui/layui.js" charset="utf-8"></script>
<script>

    $(function () {
        layui.use('flow', function(){
            var html = '';
            layui.flow.load({
                elem: '#LAY_demo2' //流加载容器
                ,scrollElem: '#LAY_demo2' //滚动条所在元素，一般不用填，此处只是演示需要。
                ,isAuto: false
                ,isLazyimg: true
                ,done: function(page, next){
                    var list = [];
                    $.post('/Live_act/getActivityRankData', {'p':page}, function(res){

                        layui.each(res.data.lists, function(index, item)
                        {
                            html = '<li><div class="section-body-avater"><span class="linght">'+item.rank+'</span><img src="'+item.avatar+'"></div><div class="section-body-info"><p class="text-linght text-over" style="width: 100%;">'+item.nickname+'</p><p style="color: #c13c95;width: 100%;">活动积：'+item.score+'分</p></div></li>';
                            list.push(html);
                        });

                        next(list.join(''), page < res.data.total);
                    });
                }
            });
        });

        $('#anchor').click(function () {
            $(this).css('opacity', '1');
            $('#user').css('opacity', '0.3');
            $('#LAY_demo2').show();
            $('#user_rank').hide();
        });

        $('#user').click(function () {
            $(this).css('opacity', '1');
            $('#anchor').css('opacity', '0.3');
            $('#LAY_demo2').hide();
            $('#user_rank').show();
        });


    });

</script>

</html>
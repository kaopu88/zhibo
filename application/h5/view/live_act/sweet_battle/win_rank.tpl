<include file="public/head" />
<title>甜蜜大作战</title>
<link rel="stylesheet" href="__H5__/css/share/common.css">
<style>

    html,body{
        -moz-user-select: none;
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
        top: 8.5rem;
        display: flex;
        width: 5.2rem;
        justify-content: space-between;
        left: 0;
        right: 0;
        margin: 0 auto;
    }
    .btn>div{
        margin: 0 .1rem;
    }

    .content{
        position: absolute;
        top: 8.2rem;
        height: 13.5rem;
        color: #fff;
        width: 84%;
        margin: 0 auto;
        overflow: scroll;
        left: 0;
        right: 0;
        border-radius: .2rem;
    }

    .section-body-avater span{
        font-size: 0.4rem;
    }

    .section-body-avater img{
        width: 1rem;
        border-radius: 100%;
        margin-left: .2rem;
    }

    .anchor_rank>img{
        width: 100%;
        border-radius: 100%;
        margin-left: 0.3rem;
    }


    .component-rank{
        width: 98%;
        height: 1.2rem;
        display: flex;
        margin: .15rem 0;
        border-bottom: 1.5px dashed rgba(49,237,255, .4);
        padding: .1rem 0;
        overflow: scroll;
        text-align: center;
        border-radius: 5px;
    }
    .component-rank .avatar{
        position: relative;
        width: 1.5rem;
        display: flex;
        justify-content: center;
    }
    .component-rank .avatar>div{
        position: relative;
    }
    .component-rank .info{
        color: #fff;
        text-align: left;
        font-size: 0.25rem;
        width: 37%;
        padding: .1rem 0;
    }


    .component-rank .avatar .avatar_icon{
        width: .9rem;
        border-radius: 100%;
        height: 0.9rem;
    }
    .component-rank .avatar .avatar_crown{
        position: absolute;
        width: .4rem;
        top: .18rem;
        left: -.1rem;
        transform: rotate(-25deg);
    }
    .component-rank .avatar .icon-span{
        display: inline-block;
        width: .8rem;
        height: 0.33rem;
        border-radius: .1rem;
        color: #fff;
        text-align: center;
        line-height: .34rem;
        font-size: .22rem;
        position: absolute;
        top: .8rem;
        background: linear-gradient(90deg,rgba(120,38,255,1) 0%,rgba(195,0,255,1) 100%);
        left: 0;
        right: 0;
        margin: 0 auto;
    }

    .fans{
        position: relative;
        width: 33%;
    }

    .fans img{
        width: .8rem;
        border-radius: 100%;
        height: 0.8rem;
        position: absolute;
        border: 1px solid #01E7FF;
        float: right;
    }

    .fans>img:first-child{
        left: 0;
        z-index: 100;
    }

    .fans>img:nth-child(2){
        left: 0.4rem;
        z-index: 90;
    }

    .fans>img:nth-child(3){
        left: 0.8rem;
        z-index: 80;
    }

</style>

</head>

<body>
<header>
    <img src="__H5__/images/live_act/sweetBattle/f2_body.png" alt="">
</header>
<section class="rank-body">
    <img src="__H5__/images/live_act/sweetBattle/f2_rank1.png" alt="">
    <img src="__H5__/images/live_act/sweetBattle/f2_rank2.png" alt="">

    <div class="content">
        <volist name="win" id="vo">
            <div class="component-rank">
                <div class="avatar">
                    <img class="avatar_icon" src={$vo.avatar}>
                    <span class="icon-span">NO:{$vo.rank}</span>
                </div>
                <div class="info text-over">
                    <p class="text-over">{$vo.nickname}</p>
                    <p style="color: #dfdfdf;">礼物积分：<span>{$vo.score}</span></p>
                </div>
                <div class="fans">
                    <volist name="vo.fans" id="fans_vo">
                        <img src="{$fans_vo}" alt="">
                    </volist>
                </div>
            </div>
        </volist>
    </div>
</section>
</body>
<script src="__H5__/js/css-base.js"></script>
</html>

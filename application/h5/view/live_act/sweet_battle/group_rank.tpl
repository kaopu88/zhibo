<include file="public/head" />
<title>甜蜜大作战</title>
<link rel="stylesheet" href="__H5__/css/share/common.css?{:date('YmdHis')}">
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
        top: 9.2rem;
        height: 12.7rem;
        color: #fff;
        width: 84%;
        margin: 0 auto;
        overflow: scroll;
        left: 0;
        right: 0;
        display: none;
        border-radius: .2rem;
    }

    .show{
        display: inline-block;
    }

    .hide{
        display: none;
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
        overflow: scroll;
        text-align: center;
        width: 90%;
        max-height: 8rem;
        height: .9rem;
        border-radius: .5rem;
        display: flex;
        align-items: center;
        justify-content: space-around;
        margin: .2rem 0;
    }
    .component-rank>div{
        margin-left: 0.2rem;
    }

    .component-rank .avatar{
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .component-rank .avatar>div{
        position: relative;
    }
    .component-rank .info{
        color: #fff;
        text-align: left;
        font-size: 0.25rem;
        width: 45%;
    }
    .component-rank .info > p{
        line-height: .4rem;
        font-size: 0.25rem;
    }

    .component-rank .info > p:first-child{
        font-weight: 600;
    }

    .component-rank .avatar .icon{
        width: 0.4rem;
    }
    .component-rank .avatar .avatar_icon{
        width: .9rem;
        border-radius: 100%;
        margin-left: .6rem;
    }

    .component-rank .avatar .icon-span{
        display: inline-block;
        width: .4rem;
        height: .4rem;
        border-radius: 100%;
        color: #fff;
        text-align: center;
        line-height: .4rem;
        font-size: .23rem;
    }



</style>

</head>

<body>
    <header>
        <img src="__H5__/images/live_act/sweetBattle/f_body.png" alt="">
    </header>
    <section class="rank-body">
        <img src="__H5__/images/live_act/sweetBattle/f_rank1.png" alt="">
        <img src="__H5__/images/live_act/sweetBattle/f_rank2.png" alt="">

        <div class="btn">
            <volist name="group" id="item" key="group_key">
                <if condition="$group_key eq $user_group">
                    <div><img src="__H5__/images/live_act/sweetBattle/f_g{$group_key}_s.png" data="{$group_key}" alt=""></div>
                <else />
                    <div><img src="__H5__/images/live_act/sweetBattle/f_g{$group_key}_h.png" data="{$group_key}" alt=""></div>
                </if>
            </volist>
        </div>

        <volist name="group" id="item" key="group_key">
            <if condition="$group_key eq $user_group">
                <div class="content show">
            <else />
                <div class="content">
            </if>
                <volist name="item" id="vo">
                    <div class="component-rank">
                        <div class="avatar">
                            <span class="icon-span">NO.{$vo.rank}</span>
                            <img class="avatar_icon" src={$vo.avatar}>
                        </div>
                        <div class="info text-over">
                            <p class="text-over">{$vo.nickname}</p>
                            <p style="color: #dfdfdf;">收获：<span>{$vo.score}</span> 金币</p>
                        </div>
                    </div>
                </volist>
            </div>
        </volist>
    </section>
    <footer>

    </footer>
</body>
<script src="__VENDOR__/jquery.min.js?v=__RV__"></script>
<script src="__H5__/js/css-base.js?v=__RV__"></script>
<script src="__VENDOR__/layer/layui/layui.js" charset="utf-8"></script>
<script>

    $(function () {
       $('.btn img').click(function () {

           var i = $(this).attr('data');
           var path = $(this).attr('src');
           var nowimg = path.split('/');
           nowimg.pop();

           $('.btn img').each(function (index, obj) {
               index++;
                if (index != i)
                {
                    $(obj).attr('src', nowimg.join('/')+'/f_g'+index+'_h.png')
                }
                else {
                    $(obj).attr('src', nowimg.join('/')+'/f_g'+i+'_s.png')
                }
           });


           $('.content').each(function (index, obj) {
               index++;
               if (index != i)
               {
                   $(obj).removeClass('show').addClass('hide');
               }
               else {
                   $(obj).removeClass('hide').addClass('show')
               }
           });

       });


    });


</script>

</html>
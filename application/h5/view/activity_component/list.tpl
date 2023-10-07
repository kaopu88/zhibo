<style>
    .component-rank{
        margin: 0 auto;
        overflow: scroll;
        text-align: center;
        height: auto;
        border-radius: 5px;
        width: 90%;
        max-height: 8rem;
    }

    .component-rank>ul{
        background: #fff;
        padding-top: .1rem;
    }

    .component-rank .rank_li .content{
        width: 100%;
        height: .9rem;
        border-radius: .5rem;
        /*background: #f4d2c7;*/
        display: flex;
        align-items: center;
        justify-content: space-around;
    }
    .component-rank .rank_li .avatar{
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .component-rank .rank_li .avatar>div{
        position: relative;
    }
    .component-rank .rank_li .info{
        color: #2341C7;
        text-align: left;
        font-size: 0.25rem;
        width: 45%;
    }
    .component-rank .rank_li .info > p{
        line-height: .4rem;
        font-size: 0.2rem;
    }

    .component-rank .rank_li .info > p:first-child{
        font-weight: 600;
    }

    .component-rank .rank_li .avatar .icon{
        width: 0.4rem;
    }
    .component-rank .rank_li .avatar .avatar_icon{
        width: .85rem;
        border-radius: 100%;
        margin-left: .4rem;
        /*border: 2px solid #579FF9;*/
    }
    .component-rank .rank_li .avatar .avatar_crown{
        position: absolute;
        width: .4rem;
        top: .18rem;
        left: -.1rem;
        transform: rotate(-25deg);
    }
    .component-rank .rank_li .avatar .icon-span{
        display: inline-block;
        width: .4rem;
        height: .4rem;
        /*background: rgb(83,157,252);*/
        border-radius: 100%;
        color: #2341CE;
        text-align: center;
        line-height: .4rem;
        font-size: .22rem;
        font-weight: 600;
    }
    .component-rank .rank_li{
        padding: .1rem .1rem;
    }
</style>


<div class="component-rank">
    <ul id="LAY_demo2"></ul>
</div>

<script src="__VENDOR__/layer/layui/layui.js" charset="utf-8"></script>

<script>
    layui.use('flow', function(){
        var html = '';
        var $ = layui.jquery;
        layui.flow.load({
            elem: '#LAY_demo2' //流加载容器
            ,scrollElem: '#LAY_demo2' //滚动条所在元素，一般不用填，此处只是演示需要。
            ,isAuto: false
            ,isLazyimg: true
//            ,end : '<span>无数据了~</span>'
            ,done: function(page, next){
                var list = [];
                $.post('/Live_act/getActivityRankData', {'p':page}, function(res){
                    if (res.data.lists != '')
                    {
                        layui.each(res.data.lists, function(index, item)
                        {
                            switch (item.rank)
                            {
                                /*case 1:
                                    html = '<li class="rank_li">' +
                                        '<div class="content" style="background: #fee2f8;">' +
                                        '<div class="avatar">' +
                                        '<img class="icon" src="/static/h5/images/activity_component/no_1.jpg"><span style="font-size: 0.25rem;color: #E359D2;">当前人气王</span><div><img class="avatar_icon" style="border-color: #E359D2;" src="'+item.avatar+'"><img src="/static/h5/images/activity_component/avatar1.png" class="avatar_crown" alt=""></div></div><div class="info text-over" style="color: #E359D2;"><p class="text-over">'+item.nickname+'</p><p>积分：'+item.score+'</p></div></div></li>';
                                    break;
                                case 2 :
                                    html = '<li class="rank_li"><div class="content" style="background: rgb(253,217,203);"><div class="avatar"><img class="icon" src="/static/h5/images/activity_component/no_2.jpg"><div><img class="avatar_icon" src="'+item.avatar+'" style="border-color: #FF7E00;"><img src="/static/h5/images/activity_component/avatar2.png" class="avatar_crown" alt=""></div></div><div class="info text-over" style="color: #FF7E00;"><p class="text-over">'+item.nickname+'</p><p>积分：'+item.score+'</p></div></div></li>';
                                    break;
                                case 3 :
                                    html = '<li class="rank_li"><div class="content" style="background: #D3FFDE;"><div class="avatar"><img class="icon" src="/static/h5/images/activity_component/no_3.jpg"><div><img class="avatar_icon" src="'+item.avatar+'" style="border-color: #FDB5EB;"><img src="/static/h5/images/activity_component/avatar3.png" class="avatar_crown" alt=""></div></div><div class="info text-over" style="color: #3EE3A7;"><p class="text-over">'+item.nickname+'</p><p>积分：'+item.score+'</p></div></div></li>';
                                    break;*/
                                default:
                                    html = '<li class="rank_li"><div class="content"><div class="avatar"><span class="icon-span">NO.'+item.rank+'</span><img class="avatar_icon" src="'+item.avatar+'"></div><div class="info text-over"><p class="text-over">'+item.nickname+'</p><p>收获：<span style="color: rgb(132,28,38);">'+item.score+'</span>热度值</p></div></div></li>';
                                    break;
                            }

                            list.push(html);
                        });
                        next(list.join(''), page < res.data.total);
                    }else {
                        $('.layui-flow-more i').text('暂无数据~');
                    }
                });
            }
        });
    });
</script>
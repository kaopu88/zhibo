<include file="public/head" />
<title>爱的供养</title>
<script type="text/javascript" charset="UTF-8" async="" src="__H5__/js/activity/adaptation.js"></script>
<link href="__H5__/css/activity/common.css" rel="stylesheet" />
<style>
    html{
        background: #fff;
    }

    section{
        width: 100%;
    }

    #reply_app{
        width: 0.935rem;
        margin: 0.03rem auto;
    }

    #reply_app ul{
        height: 1.47rem;
        overflow: scroll;
        border-radius: 0.03rem;
        background: rgba(255,235,242,1);
    }

    #reply_app li{
        display: flex;
        justify-content: center;
        align-items: center;
        height: 0.25rem;
    }

    .avatar{
        width: 15%;
        margin: 0 0.04rem;
    }

    .avatar img{
        border-radius: 100%;
    }

    .info{
        width: 40%;
        color: #D34D7C;
    }

    .info p:first-child{
        font-weight: bold;
        margin-bottom: 0.02rem;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .buttons{
        width: 40%;
        display: flex;
        justify-content: space-evenly;
        align-items: center;
    }

    .buttons a{
        display: inline-block;
        padding: 0 0.02rem;
        height: 0.09rem;
        background: rgba(254,167,199,1);
        border-radius: 0.02rem;
        text-align: center;
        line-height: 0.09rem;
        color: #fff;
    }

    body .layui-flow-more{
        text-align: center;
        color: #999;
    }

</style>
</head>
<body>
    <section>
        <div id="reply_app">
            <ul id="LAY_demo2"></ul>
        </div>
    </section>
</body>

<script src="__VENDOR__/jquery.min.js" charset="utf-8"></script>
<script src="__VENDOR__/layer/layui/layui.js" charset="utf-8"></script>
<script src="__VENDOR__/axios.js" charset="utf-8"></script>
<script src="__VENDOR__/bugujsdk.js" charset="utf-8"></script>

<script>

    var user_info = {$user_info|raw|json_encode};

    var handle = function ($obj, $reply_id, $type) {

        axios.post('/activity/'+$type, {
            reply_id: $reply_id,
            user_id: user_info.user_id,
        }).then(function (response) {

            if (response.status == 200 && response.data.status == 0)
            {
                if ($type == 'refuse')
                {
                    $($obj).parent('.buttons').empty().append('<a href="javascript:;">已拒绝</a>');
                }
                else {

                    $($obj).parent('.buttons').empty().append('<a href="javascript:;">已同意</a>');

                    var buts = $('#LAY_demo2').find('.buttons');

                    buts.each(function (index, item) {

                        var container_id = $(item).attr('data-container');

                        var reply_id = $(item).attr('data-reply');

                        if (container_id == response.data.data.container_id && reply_id != $reply_id)
                        {
                            $(item).empty().append('<a href="javascript:;">已拒绝</a>');
                        }
                    });
                }
            }
            else {
                layer.msg(response.data.message)
            }

        }).catch(function (error) {
            layer.msg(error);
        });
    };

    layui.use(['flow', 'layer'], function(){

        var layer = layui.layer;

        layui.flow.load({
            elem: '#LAY_demo2',
            scrollElem: '#LAY_demo2',
            isAuto: false,
            isLazyimg: true,
            done: function(page, next)
            {
                var list = [];

                var html = '';

                $.post('/activity/replyList', {'p':page, user_id:user_info.user_id, type:user_info.type}, function(res){

                    if (res.data.list != '')
                    {
                        layui.each(res.data.list, function(index, item)
                        {
                            switch (user_info.type)
                            {
                                case 'user':
                                    html = '<li><div class="avatar"><img src="'+item.avatar+'" alt=""></div><div class="info"><p>向“'+item.nickname+'”</p><p>申请表白罐</p></div><div class="buttons"><a href="javascript:;">'+item.status_str+'</a></div></li>';
                                    break;

                                case 'anchor':

                                    if (item.handle_status == 0)
                                    {
                                        html = '<li><div class="avatar"><img src="'+item.avatar+'" alt=""></div><div class="info"><p>"'+item.nickname+'"向你</p><p>申请表白罐</p></div><div class="buttons" data-container="'+item.container_id+'" data-reply="'+item.id+'"><a href="javascript:;" onClick="handle(this, '+item.id+', \'refuse\')">拒绝</a><a onClick="handle(this, '+item.id+', \'agree\')" href="javascript:;" style="background: #DF5787;">同意</a></div></li>';
                                    }
                                    else {
                                        var str = item.handle_status == 2 ? '已拒绝' : '已同意';

                                        html = '<li><div class="avatar"><img src="'+item.avatar+'" alt=""></div><div class="info"><p>"'+item.nickname+'"向你</p><p>申请表白罐</p></div><div class="buttons"><a href="javascript:;">'+str+'</a></div></li>';
                                    }

                                    break;
                            }

                            list.push(html);
                        });
                        next(list.join(''), page < res.data.total);

                    }
                    else if(res.data.total == 0) {
                        var empty = ' <div><img src="__H5__/images/common/no_resources.png" alt="哈哈"><span style="position: absolute;top: 0.8rem;left: 0;right: 0;text-align: center;">暂无数据</span></div>';
                        $('#LAY_demo2').append(empty);
                        $('.layui-flow-more').hide();
                    }
                    else{
                        $('.layui-flow-more i').text('暂无数据~');
                    }
                });
            }
        });

    });

</script>

</html>
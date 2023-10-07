<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta http-equiv="x-dns-prefetch-control" content="on" />
    <meta name="renderer" content="webkit" />
    <meta name="applicable-device" content="pc" />
    <meta http-equiv="X-UA-Compatible" content="IE=Edge" />
    <meta name="application-name" content="" />
    <meta name="renderer" content="webkit" />
    <meta name="format-detection" content="telephone=no" />
    <meta name="viewport"
        content="width=device-width, initial-scale=1, maximum-scale=1, minimum-scale=1, user-scalable=no, viewport-fit=cover" />
    <meta name="keywords" content="" />
    <meta name="description" content="" />
    <link rel="stylesheet" href="/bx_static/layui.css" />
    <link rel="stylesheet" href="/bx_static/withdrawal_activities.css" />
    <script src="/bx_static/layui.js"></script>
    <script src="/bx_static/media_auto.js"></script>
    <title>我的收益</title>
</head>

<body>
    <div class="container earnings">
        <header>
            <div class="back"></div>
            <div class="title">我的收益</div>
        </header>
        <div class="panel">
            <div class="title">
                <div class="gold">
                    <div class="font">金币收益</div>
                    <div class="price">{$userMsg['millet']}<span>金币</span></div>
                    <div class="proportion">兑换比例：10000金币=1元</div>
                </div>
                <div class="cash">
                    <div class="font">现金收益</div>
                    <div class="price">{$userMsg['cash']}<span>元</span></div>
                    <div class="go_withdrawal">去提现</div>
                </div>
            </div>
            <div class="datail">兑换比例受每日广告收益影响会有浮动I金币到账可能会有延迟</div>
        </div>
        <div class="content">
            <div class="layui-tab layui-tab-brief" lay-filter="docDemoTabBrief">
                <ul class="layui-tab-title">
                    <li class="layui-this">金币收益</li>
                    <li>现金收益</li>
                </ul>
                <div class="layui-tab-content">
                        <div class="layui-tab-item layui-show" id="LAY_point">

                        </div>
                    <div class="layui-tab-item" id="LAY_cash">

                    </div>

                </div>
                <div class="layui-tab-item">
                    <ul  id="LAY_exchange" >
                    </ul>
                </div>
            </div>
        </div>
        <footer>
            <div class="info">累计收益<span>{$sumcash}元</span></div>
            <div class="btn">邀请好友</div>
        </footer>
    </div>
    <script>
        layui.use(['element', 'layer'], function () {
            var element = layui.element;
            var layer = layui.layer;
            var $ = layui.jquery;
            $('.back').click(function () {
                window.history.back(-1);
            })
        });
        layui.use('flow', function () {
            var $ = layui.jquery; //不用额外加载jQuery，flow模块本身是有依赖jQuery的，直接用即可。
            var flow = layui.flow;
            flow.load({
                elem: '#LAY_point'    //指定列表容器
                , isAuto: true      //到底页面底端自动加载下一页，设为false则点击'加载更多'才会加载
                //, mb: 100          //距离底端多少像素触发auto加载
                , isLazying: true    //当单个li很长时，内部有很多图片，对图片进行懒加载，默认false。
                , end: '<p style="color:red">木有了</p>'    //加载所有后显示文本，默认'没有更多了'
                , done: function (page, next) {            //到达临界，触发下一页
                    var lis = [];
                    $.get('/h5/Withdrawal/millloglist?page_index=' + page+"&user_id="+{$user_id}, function (res) {
                        //假设你的列表返回在data集合中
                        layui.each(res.data.data, function (index, item) {
                            var html="";
                            html+=

                                '                            <div class="list-item">\n' +
                                '                                <div class="title">'+item.content+'</div>\n' +
                                '                                <div class="detail">'+item.acttime+'</div>\n' +
                                '                                <div class="count">'+item.point+'</div>\n' +
                                '                            </div>\n'

                            lis.push(html);

                        });
                        next(lis.join(''), page < res.data.page_count);//pages是后台返回的总页数
                    });
                }
            });

            flow.load({
                elem: '#LAY_cash'    //指定列表容器
                , isAuto: true      //到底页面底端自动加载下一页，设为false则点击'加载更多'才会加载
                //, mb: 100          //距离底端多少像素触发auto加载
                , isLazying: true    //当单个li很长时，内部有很多图片，对图片进行懒加载，默认false。
                , end: '<p style="color:red">木有了</p>'    //加载所有后显示文本，默认'没有更多了'
                , done: function (page, next) {            //到达临界，触发下一页
                    var lis = [];
                    $.get('/h5/Withdrawal/cashloglist?page=' + page+"&user_id="+{$user_id}, function (res) {
                        //假设你的列表返回在data集合中
                        layui.each(res.data.data, function (index, item) {
                            var html="";
                            html+=
                                '                            <div class="list-item">\n' +
                                '                                <div class="title">'+item.content+'</div>\n' +
                                '                                <div class="detail">'+item.acttime+'</div>\n' +
                                '                                <div class="count">'+item.total+'</div>\n' +
                                '                            </div>\n'

                            lis.push(html);

                        });
                        next(lis.join(''), page < res.data.page_count);//pages是后台返回的总页数
                    });
                }
            });
        });
    </script>

</body>

</html>
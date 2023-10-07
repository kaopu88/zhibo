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

    <title>邀请好友</title>
</head>

<body>
    <div class="container Invite_friends">
        <header>
            <div class="back"></div>
            <div class="title">邀请好友</div>
            <div class="explain">活动说明</div>
        </header>
        <ul>
            <li class="layui-nav-item">
                <div class="notice" >
                    <div class="layui-carousel" id="msg-id" style="background-color: transparent;">
                        <div carousel-item style="color:peru; text-align:center" id="msg-id2">
                            <volist name='_list' id='item' key="key">
                                <div>{$item.username}***又邀请1个好友，获得<span class="yellow">{$item.reward}</span></div>
                            </volist>
                        </div>
                    </div>
                </div>
            </li>
        </ul>
        <div class="bg_wall">
            <div class="btn">立即邀请赚钱</div>
            <div class="info">我的邀请码：
                <div class="Invite_code">{$_mydetail.invite_code}</div>
                <input id="input" value="" style="opacity: 0;position: absolute;" />
                <div class="copy"></div>
            </div>
        </div>
        <div class="content">
            <div class="item">
                <div class="title">我的好友</div>
                <!-- 没有好友 -->
                <div class="main no-friends">
                    <div>咦，竟然没有好友</div>
                    <div>邀请好友，赚大钱哟。还不快去试试~</div>
                </div>
                <!-- 有好友 -->
                <div class="main yes-friends">
                    <div class="font">已邀请 <span class="red">{$_mydetail.count}位好友</span>，累计获得<span class="red">{$_mydetail.inventSum}元</span></div>
                    <div class="font">提醒好友完成任务最高还可以获得 <span class="red">36.00元</span> 赶紧去看看吧！</div>
                    <div class="head_portrait">
                        <div class="item"></div>
                    </div>
                    <div class="go_watch"></div>
                </div>
            </div>
            <div class="item">
                <div class="title">如何拿到38元</div>
                <div class="img_list">
                    <div class="info_img"></div>
                    <div class="info_img"></div>
                    <div class="info_img"></div>
                </div>
            </div>
            <div class="item">
                <div class="title">如何拿到38元</div>
                <div class="info_list">
                    <div class="item">1、分享给好友，让好友通过你的分享链接下载注册，首次邀请立刻的2元奖励；</div>
                    <div class="item">2、好友每天看视频，你可以获得现金奖励，累计3天可以获得最高10元奖励励；</div>
                    <div class="item">3、好友每天看视频赚金币，你可以获得金币奖励，每位好友累计最高可达26元</div>
                    <div class="item">4、邀请好友总量无上限，邀请越多奖励越多。</div>
                </div>
            </div>
            <div class="warn_info">如果疑问请参考 <a class="red" href="./strategy.html">赚钱攻略</a></div>
        </div>
        <footer>
            <div class="WeChat">微信</div>
            <div class="Code">二维码</div>
            <div class="QQ">QQ</div>
        </footer>
    </div>
    <script>
        layui.use(['element', 'layer'], function () {
            var element = layui.element;
            var layer = layui.layer;
            var $ = layui.jquery;
            var value;
            var str =
                '<div>' +
                '<p>1. 您可能通过完成本APP内的任务来获得平台向您提供的现金红包或金币奖励。</p>' +
                '<p>2. 您获得的金币将于次日凌晨自动换算成现金红包，计入您的个人账号中，兑换比例受平台每日广告收益影响，可能会有浮动。</p>' +
                '<p> 3. 您可能通过完成本APP内的任务来获得平台向您提供的现金红包或金币奖励。</p >' +
                '<p>4. 您获得的金币将于次日凌晨自动换算成现金红包，计入您的个人账号中，兑换比例受平台每日广告收益影响，可能会有浮动。</p>' +
                '</div> '
            $('.explain').click(function () {
                layer.open({
                    type: 1,
                    title: '规则说明',
                    content: $(str).html(),
                    area: ['7.253rem', '8.586rem'],
                    skin: 'gold-info',
                    closeBtn: 0,
                    btn: ['立即申请'],
                    shadeClose: true,
                    scrollbar: false,
                    yes: function (index, layero) {
                        //return false 开启该代码可禁止点击该按钮关闭
                    }
                });
            });
            $('.back').click(function () {
                window.history.back(-1);
            });
            $('.copy').click(function () {
                value = $('.Invite_code').text();
                $('#input').val(value).select();
                document.execCommand("Copy");
                layer.msg('复制成功');
            })
        });
        layui.use('carousel', function(){
            var carousel = layui.carousel;
            //建造实例
            carousel.render({
                elem: '#msg-id'
                , width: '7.41rem' //设置容器宽度
                , height: '0.85333rem' //设置容器高度
                , arrow: 'none' //始终显示箭头
                , indicator: 'none'
                , anim: 'updown' //切换动画方式
                , autoplay: false   //是否自动切换
                , interval: 2000   //时间间隔
            });
        });
    </script>
</body>

</html>
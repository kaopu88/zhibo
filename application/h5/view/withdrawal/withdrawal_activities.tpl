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
    <link rel="stylesheet" href="/bx_static/ystep.css">
    <link rel="stylesheet" href="/bx_static/withdrawal_activities.css" />
    <script src="/bx_static/layui.js"></script>
    <script src="/bx_static/media_auto.js"></script>
    <title>提现活动</title>
</head>

<body>
    <div class="container withdrawal_activities">
        <div class="backdrop">
            <header>
                <div class="back"></div>
                <a href="./activety_set.html">
                    <div class="set"></div>
                </a>
            </header>
            <div class="earnings">
                <div class="gold">
                    <div class="title">金币收益</div>
                    <div class="content">
                        <div class="">{$userMsg['points']}</div>
                        <div class="gold_icon"></div>
                        <div class="go_gold"></div>
                    </div>
                </div>
                <div class="cash">
                    <div class="title">现金收益</div>
                    <div class="content">
                        <div class="">{$userMsg['cash']}</div>
                        <div class="yuan">元</div>
                        <div class="go_gold"></div>
                    </div>
                </div>
            </div>
            <footer>金币每天凌晨左右自动兑换成现金</footer>
        </div>
        <div class="content">
            <div class="img_top"></div>
            <div class="task">
                <div class="title">日常任务</div>
                <div class="list">
                    <div class="list_content">
                        <div class="list_title">
                            <div class="font">新人一元提现</div>
                            <div class="red_packet"></div>
                        </div>
                        <div class="list_details">
                            新人专属特权，立即提现1元
                        </div>
                        <a href="#" class="go_withdraw">去提现</a>
                    </div>
                    <div class="list_content">
                        <div class="list_title">
                            <div class="font">限时任务赚金币</div>
                            <div class="bg_gold">+7344金币</div>
                        </div>
                        <div class="list_details">
                            每60分钟完成一次广告任务，单日最高
                            可赚7344金币
                        </div>
                        <a href="#" class="go_withdraw">去领取</a>
                    </div>
                    <div class="list_content">
                        <div class="list_title">
                            <div class="font">3000金币大礼包</div>
                        </div>
                        <div class="list_details">
                            新人超值奖励，马上领取
                        </div>
                        <a href="#" class="go_withdraw">去提现</a>
                    </div>

                    <if condition=" !empty($tasklist) ">

                            <volist name='tasklist' id='item' key="key">

                                  <if condition="$key neq 'watchVidep'" />
                                    <div class="list_content">
                                        <div class="list_title">
                                            <div class="font">{$item['task_title']}</div>
                                        </div>
                                        <div class="list_details">
                                            {$item['tips']}
                                        </div>
                                        <switch $item.status >
                                            <case 1> <a href="javascript:;"  class="go_withdraw   goreceive" data-id="{$item.task_id}" data-uid="{$user_id}">{$item['status_txt']}</a> </case>
                                            <case 2> <a href="javascript:;" class="go_withdraw " >{$item['status_txt']}</a>
                                            </case>
                                            <default /><if condition=" $item.task_type == 'postVideo' || $item.task_type == 'watchVideo'|| $item.task_type == 'shareVideo'|| $item.task_type == 'thumbsVideo'|| $item.task_type == 'commentVideo'"> <a href="javascript:;" class="go_withdraw done" >待完成</a> <else /> <a  class="go_withdraw ">已完成</a> </if>
                                        </switch>
                                    </div>
                                <else />
                                <div class="list_content">
                                    <div class="info">
                                        <div class="list_title">
                                            <div class="font">看视屏，赚金币 </div>
                                            <div class="bg_gold">奖励升级</div>
                                        </div>
                                        <div class="list_details">
                                            {$watchtips}
                                        </div>
                                        <switch $item.status >
                                            <case 1> <a href="javascript:;"  class=" go_withdraw done goreceive" data-id="{$item.task_id}" data-uid="{$user_id}">{$item['status_txt']}</a> </case>
                                            <case 2> <a href="javascript:;" class="go_withdraw " >{$item['status_txt']}</a>
                                            </case>
                                            <default /><if condition=" $item.task_type == 'postVideo' || $item.task_type == 'watchVideo'|| $item.task_type == 'shareVideo'|| $item.task_type == 'thumbsVideo'|| $item.task_type == 'commentVideo'"> <a href="javascript:;" class="go_withdraw done" >待完成</a> <else /> <a  class="go_withdraw ">已完成</a> </if>
                                        </switch>

                                    </div>

                                    <div class="stepCont stepCont1">
                                        <!-- 菜单导航显示 -->
                                        <div class='ystep-container ystep-lg ystep-blue'></div>
                                        <!-- 分页容器-->
                                        <div class="pageCont">
                                            <volist name="arrayAll" id="data" key="k">
                                                <div id="page{$k-1}" class="stepPage">
                                                    <p>{$data}</p>
                                                </div>
                                            </volist>

                                        </div>
                                    </div>
                                </div>
                                 </if>

                            </volist>

                        <else />
                        <div style="text-align: center;font-size: .3rem;padding: .3rem 0;">暂无任务</div>
                    </if>
                </div>
            </div>
            <footer>如果疑问请参考 <span id="rule">活动规则</span></footer>
        </div>
        <div class="alert_sign">
            <div class="close"></div>
            <div class="sign">
                <div class="sign_content">
                    <div class="statistics">已连续签到1天</div>
                    <div class="title">签到成功加166金币</div>
                    <div class="sign_gold">
                        <div>166</div>
                        <div>366</div>
                        <div>466</div>
                        <div>566</div>
                        <div>766</div>
                        <div>866</div>
                    </div>
                    <div class="sign_flow">
                        <div class="sign_day">
                            <div>1天</div>
                            <div>2天</div>
                            <div>3天</div>
                            <div>4天</div>
                            <div>5天</div>
                            <div>6天</div>
                            <div class="sign_bg"></div>
                            <div class="top_dot">
                                <div></div>
                                <div></div>
                                <div></div>
                                <div></div>
                                <div></div>
                                <div></div>
                            </div>
                        </div>
                        <div class="gift">
                            <div>6666</div>
                            <div>3666</div>
                            <div>2666</div>
                            <div>1666</div>
                        </div>
                        <div class="vertical_bg"></div>
                    </div>
                    <div class="sign_week">
                        <div>7天</div>
                        <div>14天</div>
                        <div>21天</div>
                        <div>28天</div>
                    </div>
                    <div class="footer_dot">
                        <div></div>
                        <div></div>
                        <div></div>
                        <div></div>
                    </div>
                    <div class="footer_bg"></div>
                </div>
                <div class="btn"></div>
            </div>
        </div>
    </div>
    <script type="text/javascript" src="/bx_static/jquery.min.js"></script>
    <script type="text/javascript" src="/bx_static/setStep.js"></script>
    <script>
        //注意进度条依赖 element 模块，否则无法进行正常渲染和功能性操作
        layui.use(['element', 'layer'], function () {
            var element = layui.element;
            var layer = layui.layer;
            var $ = layui.jquery;
            var str =
                '<div>' +
                '<p>1. 您可能通过完成本APP内的任务来获得平台向您提供的现金红包或金币奖励。</p>' +
                '<p>2. 您获得的金币将于次日凌晨自动换算成现金红包，计入您的个人账号中，兑换比例受平台每日广告收益影响，可能会有浮动。</p>' +
                '<p> 3. 您可能通过完成本APP内的任务来获得平台向您提供的现金红包或金币奖励。</p >' +
                '<p>4. 您获得的金币将于次日凌晨自动换算成现金红包，计入您的个人账号中，兑换比例受平台每日广告收益影响，可能会有浮动。</p>' +
                '</div> '
            $('#rule').click(function () {
                layer.open({
                    type: 1,
                    title: '金币兑换说明',
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
        });
        var stoop = {$steps};
        var step1 = new SetStep({
            skin: 1,
            content: '.stepCont1',
            showBtn: false,
            descriptionHeader: {:htmlspecialchars_decode($_watchArray)},
        },stoop)
        $('.close').click(function () {
            $('.alert_sign').hide();
        });
        // 签到动画
        var top_width = 0;
        var bottom_width = 0.88;
        var count = 0;  // 代表签到的天数
        $('.go_check').click(function () {
            // 更改已签文字
            if (count !== 0 && count <= 6) {
                $('.sign_day div').eq(count - 1).text('已签');
            }

            count++;

            // 签到
            if (count <= 6) {

                // 明日领取的签到奖励
                if (count !== 6) {
                    $('.sign_gold div').eq(count).siblings().removeClass('active');
                    $('.sign_gold div').eq(count).addClass('active');
                } else {
                    $('.sign_gold div').removeClass('active');
                    $('.gift div').eq(3).addClass('active');
                }

                // 当日签到
                $('.sign_day div').eq(count - 1).addClass('active').text('今日已签');
                $('.sign_gold div').eq(count - 1).addClass('main');

                // 前六天签到的进度条样式
                $(".sign_bg").css('width', top_width + 'rem');
                $(".vertical_bg").css('height', '0');
                top_width += 1.22667;
                $(".sign_bg").animate({ width: '+=1.22667rem' }, 1000);
            } else if (count == 7) {
                $('.gift div').eq(3).removeClass('active');
                // 签到7天的文字样式
                $('.sign_week div').eq(0).addClass('active').text('今日已签');
                $('.gift div').eq(3).addClass('main');

                // 签到7天的进度条样式
                $(".vertical_bg").animate({ height: '2.45rem' }, 1000);
                setTimeout(function () {
                    $(".footer_bg").animate({ width: '0.88rem' }, 1000);
                }, 1000);
            } else {
                // 第七天之后的进度条样式
                $(".footer_bg").css('width', bottom_width + 'rem');
                bottom_width += 0.275;
                $(".footer_bg").animate({ width: '+=0.275rem' }, 1000);

                // 第七天之后的文字样式
                switch (count) {
                    case 8:
                        $('.sign_week div').eq(0).text('已签');
                        break;
                    case 14:
                        $('.sign_week div').eq(1).addClass('active').text('今日已签');
                        $('.gift div').eq(2).addClass('main');
                        break;
                    case 15:
                        $('.sign_week div').eq(1).text('已签');
                        break;
                    case 21:
                        $('.sign_week div').eq(2).addClass('active').text('今日已签');
                        $('.gift div').eq(1).addClass('main');
                        break;
                    case 22:
                        $('.sign_week div').eq(2).text('已签');
                        break;
                    case 28:
                        $('.sign_week div').eq(3).addClass('active').text('今日已签');
                        $('.gift div').eq(0).addClass('main');
                        break;
                    case 29:
                        $('.sign_week div').eq(3).text('已签');
                        break;
                }
            }
            $('.alert_sign').show();
        });
        $(function () {

            //点击领取
            $('.goreceive').click(function () {
                var task_id = $(this).data('id');
                var user_id = $(this).data('uid');
                $.ajax({
                    type: 'post',
                    url: '{:url("task/receive")}',
                    data: {user_id : user_id, task_id : task_id},
                    success: function(result){
                        layer.closeAll();
                        if(result.status == 0){
                            layer.msg('领取失败');
                        } else {
                            layer.msg(result.msg);
                            $(this).parent().text('已完成');
                            var point = Number(result.data.point) + Number($('.today-point span').text());
                            $('.taskHD .point').text(result.data.total_point);
                            $('.today-point span').text(point);
                            location.reload();

                        }
                    }
                })

            });

            function navChangeArea() {
                var scrollTop = document.documentElement.scrollTop || document.body.scrollTop;
                if (scrollTop < 90) {
                    $('.mui-bar').css("background-image", "none");
                    $('.mui-bar').css("background-color", "transparent");
                } else {
                    $('.mui-bar').css("background-image", "url(https://static.cnibx.cn/bg_header.png?imageView2/2/w/750)");
                    $('.mui-bar').css("background-position", "bottom");
                    $('.mui-bar').css("background-size", "100%");
                }
            }
            $(window).bind("scroll", function() {
                navChangeArea();
            });
            $(window).bind("touchmove", function() {
                navChangeArea();
            });

            dssdk.ready(function (sdk) {
                //获取已登录用户信息
                sdk.getUser(function (result) {
                    if (typeof result == 'object' && result !== null) {
                        if (result.user_id != '' && user_id != result.user_id) {
                            window.location.replace("/h5/task/index?user_id=" + result.user_id);
                        }
                    }
                });

                sdk.getDeviceInfo(function (result2) {
                    if (typeof result2 == 'object' && result2 !== null) {
                        if ( parseInt(result2.notch_screen_height) > 0 ) {
                            $('.mui-bar').css("padding-top", result2.notch_screen_height + "px");
                            $('.mui-bar').css("height", (parseInt(result2.notch_screen_height) + 44) + "px");
                            $('.taskHD').css("padding-top", (parseInt(result2.notch_screen_height) + 44) + "px");
                        }
                    }
                });

                // 去拍摄
                $('.postVideo .gofinish').click(function () {
                    sdk.goTo({
                        url: '{:LOCAL_PROTOCOL_DOMAIN}post_video'
                    }, function (result) {

                    });
                });

                //ios 240+ js 返回按钮
                $('.mui-icon').click(function () {
                    sdk.navigateBack();
                });

            });

        });
    </script>

</body>
<!-- 弹出层内容 -->

</html>
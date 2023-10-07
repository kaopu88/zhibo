<?php /*a:2:{s:53:"/www/wwwroot/zhibb/application/h5/view/task/index.tpl";i:1612175230;s:54:"/www/wwwroot/zhibb/application/h5/view/public/head.tpl";i:1595042494;}*/ ?>
<!DOCTYPE html>
<html lang="en" data-dpr="1">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta http-equiv="x-dns-prefetch-control" content="on" />
    <meta name="renderer" content="webkit" />
    <meta name="applicable-device" content="pc" />
    <meta http-equiv="X-UA-Compatible" content="IE=Edge" />
    <meta name="application-name" content="" />
    <meta name="renderer" content="webkit" />
    <meta name="format-detection" content="telephone=no" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, minimum-scale=1, user-scalable=no, viewport-fit=cover" />
    <meta name="keywords" content=""/>
    <meta name="description" content=""/>

<title>个人任务</title>
<link rel="stylesheet" href="/static/h5/css/user_task/common.css?<?php echo date('YmdHis'); ?>">
<link rel="stylesheet" type="text/css" href="/bx_static/h5.css"/>
<script src="/static/vendor/jquery.min.js?v=<?php echo config('upload.resource_version'); ?>"></script>
<script src="/static/vendor/layer/layer.js?v=<?php echo config('upload.resource_version'); ?>"></script>
<script src="/bx_static/media_auto.js"></script>
<script>
    var user_id = <?php echo htmlentities($user_id); ?>;
</script>
</head>

<body>
<div class="taskHD">
    <div class="task-tab">
        <ul class="clearfix">
            <li class="action"><?php echo htmlentities($milletname); ?>任务</li>
            <li><a href="<?php echo url('redeem',['user_id'=>$user_id]); ?>"><?php echo htmlentities($milletname); ?>兑换</a></li>
        </ul>
    </div>
    <div class="integral">
        <div class="today">
            <div class="today-point">今日<?php echo htmlentities($milletname); ?></div>
            <div class="point"><?php echo htmlentities((isset($user_info['today_point']) && ($user_info['today_point'] !== '')?$user_info['today_point']:"0")); ?></div>
        </div>
        <div class="my">
            <div class="title">我的<?php echo htmlentities($milletname); ?></div>
            <div class="point"><?php echo htmlentities((isset($user_info['points']) && ($user_info['points'] !== '')?$user_info['points']:"0")); ?></div>
        </div>
    </div>
</div>
<div class="sign_card">
    <div class="sign_card_container">
        <div class="sign_card_title">
            <div class="icon"></div>
            <div class="title">每日签到</div>
            <div class="rule">活动规则</div>
            <div class="sign_in  "  <?php if($tasklist['dailyLogin']['status'] == 2): ?>style="pointer-events: none;"<?php endif; ?>> <?php if($tasklist['dailyLogin']['status'] == 2): ?>已签到<?php else: ?>去签到<?php endif; ?></div>
    </div>
    <div class="sign_card_content">
        <div>
            <?php if(is_array($tasklist['dailyLogin']['days']) || $tasklist['dailyLogin']['days'] instanceof \think\Collection || $tasklist['dailyLogin']['days'] instanceof \think\Paginator): $i = 0; $__LIST__ = $tasklist['dailyLogin']['days'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$day): $mod = ($i % 2 );++$i;if($key< 4): ?>
                    <div id="<?php echo htmlentities($day); ?>" <?php if($tasklist['dailyLogin']['check_days'] > $key): ?>class="active"<?php endif; ?>   >
                    <div class="tab"><?php echo htmlentities($key+1); ?>天</div>
                    <div class="icon"></div>
                    <div class="pair_icon"></div>
                    </div>
                 <?php endif; endforeach; endif; else: echo "" ;endif; ?>
        </div>
    <div>
        <?php if(is_array($tasklist['dailyLogin']['days']) || $tasklist['dailyLogin']['days'] instanceof \think\Collection || $tasklist['dailyLogin']['days'] instanceof \think\Paginator): $i = 0; $__LIST__ = $tasklist['dailyLogin']['days'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$day): $mod = ($i % 2 );++$i;if($key > 3): ?>
                <div id="<?php echo htmlentities($day); ?>"  <?php if($tasklist['dailyLogin']['check_days'] > $key): ?>class="active"<?php endif; ?> >
                <div class="tab"><?php echo htmlentities($key+1); ?>天</div>
                <div class="icon"></div>
                <div class="pair_icon"></div>
                </div>
             <?php endif; endforeach; endif; else: echo "" ;endif; ?>

        <div class="fonts">
            <div class="continuous_title">已连续签到</div>
            <div class="continuous_number"><span><?php echo htmlentities($tasklist['dailyLogin']['check_days']); ?></span>天</div>
        </div>
    </div>
</div>
</div>
</div>

<?php if(!empty($tasklist)): ?>
    <div class="task-content">
        <?php if(is_array($tasklist) || $tasklist instanceof \think\Collection || $tasklist instanceof \think\Paginator): $i = 0; $__LIST__ = $tasklist;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$item): $mod = ($i % 2 );++$i;?>
            <div class="task-list">
                <div class="tasklist_icon"></div>
                <div class="tasklist_intro">
                    <div class="hd">
                        <div class="title"><?php echo htmlentities($item['task_title']); ?></div>
                    </div>
                    <div class="intro"><?php echo htmlentities($item['tips']); ?></div>
                    <div class="integral_sm">
                        <div class="
                        <?php switch($item['reward_type']): case "1": ?> integral_icon <?php break; case "2": ?> integral_millet <?php break; case "3": ?> integral_bean <?php break; case "4": ?> integral_cash <?php break; default: endswitch; ?>
                     "></div>
                        <div class="integral_num"><?php echo htmlentities($item['rewoadpoint']); ?><?php echo htmlentities($item['rewoardname']); ?></div>
                    </div>
                </div>
                <div class="task-status <?php echo htmlentities($item['task_type']); ?>">
                    <?php switch($item['status']): case "1": if($item['status_reword'] == 1): ?>
                                <a href="javascript:;" style="background:transparent; color:#B2B2B2;" data-id="<?php echo htmlentities($item['task_id']); ?>" data-uid="<?php echo htmlentities($user_id); ?>"><?php echo htmlentities($item['status_txt']); ?></a>
                             <?php else: ?>
                                <a href="javascript:;" style="background:transparent; color:#B2B2B2;" class="goreceive" data-id="<?php echo htmlentities($item['task_id']); ?>" data-uid="<?php echo htmlentities($user_id); ?>"><?php echo htmlentities($item['status_txt']); ?></a>
                            <?php endif; break; case "2": ?> <a href="javascript:;"><?php echo htmlentities($item['status_txt']); ?></a>
                        <?php break; default: ?> <a href="javascript:;" class="gofinish " >去完成</a>
                    <?php endswitch; ?>
                </div>
                <?php if($item['task_type']=='dailyLogin'): ?>
                    <div class="other " style="display: none">
                        <div class="login">
                            <img src="/static/h5/images/user_task/login-<?php echo htmlentities($item['check_days']); ?>.png">
                        </div>
                        <ul class="login-day clearfix ">
                            <?php if(is_array($item['days']) || $item['days'] instanceof \think\Collection || $item['days'] instanceof \think\Paginator): $i = 0; $__LIST__ = $item['days'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$day): $mod = ($i % 2 );++$i;?>
                                <li><?php echo htmlentities($day); ?></li>
                            <?php endforeach; endif; else: echo "" ;endif; ?>
                        </ul>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; endif; else: echo "" ;endif; ?>
    </div>
    <?php else: ?>
    <div style="text-align: center;font-size: .3rem;padding: .3rem 0;">暂无任务</div>
<?php endif; if(isset($tasklist['dailyLogin'])): ?>
    <div id="qiandao" style="display: none">
        <div class="check">
            <div class="bg">
                <div class="bd"></div>
            </div>
            <div class="check_now"><?php echo htmlentities($tasklist['dailyLogin']['check_days'] + 1); ?></div>
            <?php if($tasklist['dailyLogin']['check_days'] < 6): ?>
                <div class="check_in"><?php echo htmlentities($tasklist['dailyLogin']['check_days'] + 2); ?></div>
            <?php endif; ?>
            <div class="check_line"></div>
        </div>
        <div class="tip1"><?php echo htmlentities($tasklist['dailyLogin']['today_tips']); ?></div>
        <div class="tip2"><?php echo htmlentities($tasklist['dailyLogin']['tips']); ?></div>
        <div class="chechin">
            <a href="javascript:;" class="btn" data-id="<?php echo htmlentities($tasklist['dailyLogin']['task_id']); ?>">立即签到</a>
        </div>
    </div>
<?php endif; ?>
<div id="activity_rules" style="display: none">
    <div class="activity_rules_container">
        <h4>活动规则</h4>
        <ol>
            <li>您可能通过完成本APP内的任务来获得平台向您提供的现金红包或金币奖励</li>
            <li>您获得的金币将于次日凌晨自动换算成现金红包，计入您的个人账号中，兑换比例受平台每日广告收益影响，可能会有浮动。</li>
            <li>如金币没有及时到账，别担心，可能会有延迟。</li>
            <li>提交申请后，<?php echo htmlentities($milletname); ?>将直接扣除，<?php echo htmlentities($milletname); ?>将在<span>1-3个工作日内到账</span>。</li>
            <li>提交申请后，<?php echo htmlentities($milletname); ?>将直接扣除，<?php echo htmlentities($milletname); ?>将在<span>1-3个工作日内到账</span>。</li>
            <li>提交申请后，<?php echo htmlentities($milletname); ?>将直接扣除，<?php echo htmlentities($milletname); ?>将在<span>1-3个工作日内到账</span>。</li>
            <li>提交申请后，<?php echo htmlentities($milletname); ?>将直接扣除，<?php echo htmlentities($milletname); ?>将在<span>1-3个工作日内到账</span>。</li>
            <li>提交申请后，<?php echo htmlentities($milletname); ?>将直接扣除，<?php echo htmlentities($milletname); ?>将在<span>1-3个工作日内到账</span>。</li>
            <li>提交申请后，<?php echo htmlentities($milletname); ?>将直接扣除，<?php echo htmlentities($milletname); ?>将在<span>1-3个工作日内到账</span>。</li>
            <li>提交申请后，{<?php echo htmlentities($milletname); ?>}将直接扣除，钻石将在<span>1-3个工作日内到账</span>。</li>
        </ol>

    </div>
</div>
<script src="/static/vendor/bugujsdk.js"></script>
<script>
    $(function () {

        //签到弹层
        $('.dailyLogin .gofinish').click(function () {
            layer.open({
                title: ' ',
                type: 1,
                skin: 'check-class',
                area: '5rem',
                content: $('#qiandao')
            });
        });

        $('.sign_in').click(function () {
            layer.open({
                title: ' ',
                type: 1,
                skin: 'check-class',
                area: '5rem',
                content: $('#qiandao')
            });
        });

        //点击签到
        $('#qiandao .btn').click(function () {
            $(this).attr("disabled","true");
            $.ajax({
                type: 'post',
                url: '<?php echo url("task/daily_check"); ?>',
                data: {user_id : user_id, task_id : $(this).data('id')},
                success: function(result){
                    console.log(result);
                    layer.closeAll();
                    if(result.status == 0){
                        layer.msg('签到失败');
                    } else {
                        layer.msg('签到成功');
                        $('.dailyLogin').html('已完成');
                        var img = '/static/h5/images/user_task/login-'+ result.data.task_value +'.png'
                        $('.task-list .other .login img').attr("src",img);
                        var point_all = Number(result.data.point) + Number($('.taskHD .point').text());
                        var point = Number(result.data.point) + Number($('.today-point span').text());
                        $('.taskHD .point').text(point_all);
                        $('.today-point span').text(point);
                        location.reload();
                    }
                }
            })
        });

        //点击领取
        $('.goreceive').click(function () {
            var task_id = $(this).data('id');
            var user_id = $(this).data('uid');
            $.ajax({
                type: 'post',
                url: '<?php echo url("task/receive"); ?>',
                data: {user_id : user_id, task_id : task_id},
                success: function(result){
                    layer.closeAll();
                    if(result.status == 0){
                        layer.msg('领取失败');
                    } else {
                        layer.msg(result.msg);
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
                console.log(result2);
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
                    url: '<?php echo LOCAL_PROTOCOL_DOMAIN; ?>post_video'
                }, function (result) {

                });
            });
            //关注好友
            $('.followFriends .gofinish').click(function () {
                sdk.goTo({
                    url: '<?php echo LOCAL_PROTOCOL_DOMAIN; ?>followFriends'
                }, function (result) {

                });
            });

            //邀请好友
            $('.inviteFriends .gofinish').click(function () {
                sdk.goTo({
                    url: '<?php echo LOCAL_PROTOCOL_DOMAIN; ?>inviteFriends'
                }, function (result) {

                });
            });

            //每日充值
            $('.dayRecharge .gofinish').click(function () {
                sdk.goTo({
                    url: '<?php echo LOCAL_PROTOCOL_DOMAIN; ?>dayRecharge'
                }, function (result) {

                });
            });
            //每日提现oneWithdrawal
            $('.oneWithdrawal .gofinish').click(function () {
                sdk.goTo({
                    url: '<?php echo LOCAL_PROTOCOL_DOMAIN; ?>oneWithdrawal'
                }, function (result) {

                });
            });
            //每日打赏
            $('.dayReward .gofinish').click(function () {
                sdk.goTo({
                    url: '<?php echo LOCAL_PROTOCOL_DOMAIN; ?>dayReward'
                }, function (result) {

                });
            });
            //每日分享直播间
            $('.dayShareRoom .gofinish').click(function () {
                sdk.goTo({
                    url: '<?php echo LOCAL_PROTOCOL_DOMAIN; ?>dayShareRoom'
                }, function (result) {

                });
            });
            //评论动态
            $('.commentDynamic .gofinish').click(function () {
                sdk.goTo({
                    url: '<?php echo LOCAL_PROTOCOL_DOMAIN; ?>commentDynamic'
                }, function (result) {

                });
            });
            //点赞动态liveDynamic
            $('.liveDynamic .gofinish').click(function () {
                sdk.goTo({
                    url: '<?php echo LOCAL_PROTOCOL_DOMAIN; ?>liveDynamic'
                }, function (result) {

                });
            });
            //分享动态
            $('.shareDynamic .gofinish').click(function () {
                sdk.goTo({
                    url: '<?php echo LOCAL_PROTOCOL_DOMAIN; ?>shareDynamic'
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
<script src="/static/h5/js/task/redeem.js"></script>
</body>
</html>
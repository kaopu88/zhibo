<!DOCTYPE html>
<html lang="en">
<head>
    <title>WebViewJavascriptBridge测试</title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta id="viewport" content="width=device-width, user-scalable=no,initial-scale=1" name="viewport"/>
    <meta name="apple-mobile-web-app-capable" content="yes">
    <style>
        body {
            margin: 0;
            padding: 20px;
        }

        .btn {
            display: block;
            width: 100%;
            box-sizing: border-box;
            line-height: 35px;
            border: solid 1px #DCDCDC;
            background-color: #f5f5f5;
            text-align: center;
            font-size: 14px;
            color: #555;
            text-decoration: none;
            margin-top: 20px;
        }

        .tip {
            display: block;
            padding: 10px;
            border: solid 1px #DCDCDC;
            font-size: 14px;
            line-height: 30px;
            word-break: break-all;
        }

        .btn_tip {
            margin-top: 10px;
            font-size: 14px;
            background-color: #f5f5f5;
            padding: 10px;
            line-height: 25px;
            display: block;
            word-break: break-all;
        }

        .ds_btn {
            line-height: 35px;
            display: block;
        }

    </style>
    <script src="__VENDOR__/jquery.min.js?v=__RV__"></script>
    <script src="__VENDOR__/bugujsdk.js?v={:date('YmdHis')}"></script>
</head>
<body>
<p class="tip"></p>

<p class="btn_tip">
    如果有错误的话：<br/>
    直接在第一层数据带上错误信息，如:<br/>
    {error_msg:'xxx错误',error_status:1}
</p>

<a class="get_user btn" href="javascript:;"><span style="color: red">*</span> 获取登录用户信息</a>
<p class="btn_tip">
    [getUser]<br/>
    获取登录用户信息：<br/>
    已登录用户至少返回字段：{user_id:'100',nickname:'bx',avatar:'',gender:'1',phone:''}<br/>
    未登录用户至少返回字段:{user_id:''}
</p>
<a class="get_version btn" href="javascript:;"><span style="color: red">*</span> 获取APP版本信息</a>
<p class="btn_tip">
    [getVersion]<br/>
    获取APP版本信息：<br/>
    已登录用户至少返回字段：{vcode:'ios_70',version:'1.0.1'}<br/>
    vcode是内部版本号
</p>

<a class=" btn" href="javascript:;">调用登录</a>
<p class="btn_tip">
    [goToLogin]<br/>
    前往登录页面
</p>

<a class="share_btn btn" href="javascript:;">调用分享</a>
<p class="btn_tip">
    [share]<br/>
    调用原生分享功能：<br/>
    参数：<br/>
    title：带走负能量，拯救不开心,我在{:APP_NAME}等你来~<br/>
    descr：这里是描述<br/>
    url：/invite?sk=738d3583d77dfd3227a96380d2ff83212d7aed55&user_id=10152<br/>
    thumb:缩略图地址<br/>
    share_key：本次分享操作唯一标识符<br/>
    返回：<br/>
    分享结果<br/>
    status:1 分享成功 2分享失败<br/>
    share_channel：分享渠道 qq<br/>
</p>

<a class="upload_btn btn" href="javascript:;"><span style="color: red">*</span> 上传文件（图片）</a>
<a class="upload_video_btn btn" href="javascript:;">上传文件（视频）</a>

<p class="btn_tip">
    [uploadFile]<br/>
    调用原生上传文件功能：<br/>
    参数：<br/>
    source:camera、album 文件来源 相机或者相册<br/>
    mimeType：上传文件类型 如：images/*,video/mp4  多个用逗号隔开<br/>
    limitSize:限制大小 654444 单位字节<br/>
    type:images  videos 文件类型：图片 视频<br/>
    <span style="color: #ed0202">【已删除】</span>server:接收文件的服务器地址(第三方存储的获取签名地址)<br/>
    ticket_type:第三方签名时的类型值type<br/>
    返回：<br/>
    fileUrl 上传完成后的文件地址
</p>

<a class="goto_btn btn" href="javascript:;"><span style="color: red">*</span> 跳转本地协议</a>
<p class="btn_tip">
    [goTo]<br/>
    跳转到一个本地协议地址：<br/>
    参数：<br/>
    url：本地协议(示例：{:LOCAL_PROTOCOL_DOMAIN}video_detail?id=151)<br/>
    返回：<br/>
    无
</p>

<a class="recharge_btn btn" href="javascript:;"><span style="color: red">*</span> 打开充值弹窗</a>
<p class="btn_tip">
    [recharge]<br/>
    充值弹窗(快捷充值)：<br/>
    参数：<br/>
    无
</p>

<a class="openwxapp_btn btn" href="javascript:;"><span style="color: red">*</span> 打开微信小程序</a>
<p class="btn_tip">
    [openWxAPP]<br/>
    打开微信小程序：<br/>
    参数：<br/>
    userName：小程序应用ID<br/>
    path：拉起小程序页面的可带参路径<br/>
    miniProgramType：拉起小程序的类型 开发版1，体验版2和正式版0<br/>
    返回值：无
</p>

<a class="navigateback_btn btn" href="javascript:;"><span style="color: red">*</span> 返回入口页面</a>
<p class="btn_tip">
    [navigateBack]<br/>
    返回入口页面：<br/>
    参数：<br/>
    p：1 （此参数暂时不需要） <br/>
    返回值：无
</p>

<a class="getdeviceinfo_btn btn" href="javascript:;"><span style="color: red">*</span> 获取设备信息</a>
<p class="btn_tip">
    [getDeviceInfo]<br/>
    获取设备信息：<br/>
    参数：无<br/>
    返回值：<br/>
    notch_screen:1 是刘海屏 0不是<br/>
    notch_screen_height:100 刘海高度<br/>
</p>

<br/>
<br/>
<p class="btn_tip">
    <a class="ds_btn" data-link="{:LOCAL_PROTOCOL_DOMAIN}personal?user_id=10209" href="javascript:;"><span style="color: red">*</span>跳转到个人主页</a>
    <a class="ds_btn" data-link="{:LOCAL_PROTOCOL_DOMAIN}topic?id=6" href="javascript:;"><span style="color: red">*</span>跳转到话题页面</a>
    <br/>
    <input placeholder="话题ID" class="topic_id" value="">
    <br/>
    <input placeholder="话题标题" class="topic_title" value="">
    <br/>
    <a class="ds_btn" data-type="post" data-link="{:LOCAL_PROTOCOL_DOMAIN}post_video" href="javascript:;"><span style="color: red">*</span>开拍</a>
    <a class="ds_btn" data-link="{:LOCAL_PROTOCOL_DOMAIN}login" href="javascript:;"><span style="color: red">*</span>跳转到登录页面</a>
    <a class="ds_btn" data-link="{:LOCAL_PROTOCOL_DOMAIN}recharge" href="javascript:;"><span style="color: red">*</span>跳转充值页面</a>
    <a class="ds_btn" data-link="{:LOCAL_PROTOCOL_DOMAIN}video_detail?id=151" href="javascript:;"><span style="color: red">*</span>151视频详情</a>
    <a class="ds_btn"
       data-link="{:LOCAL_PROTOCOL_DOMAIN}message_list?cat_type=like&type=like_film&user_id=1111&msg_id=1222"
       href="javascript:;"><span style="color: red">*</span>跳转到消息列表页面</a>
    <input placeholder="直播间ID" class="room_id" value="">
    <a class="ds_btn" data-type="room" data-link="{:LOCAL_PROTOCOL_DOMAIN}live_detail?room_id=" href="javascript:;"><span style="color: red">*</span>跳转直播间</a>
    <a class="ds_btn" data-link="{:LOCAL_PROTOCOL_DOMAIN}vip" href="javascript:;"><span style="color: red">*</span>VIP购买页面</a>
</p>

<script>
    function tip(data) {
        var str = '';
        if (typeof data == 'object' && data !== null) {
            str = JSON.stringify(data);
        } else {
            str = data ? data : '';
        }
        $('.tip').append(str + '<br/><hr/>');
    }

    tip('平台是:' + dssdk.getPlatform());

    dssdk.ready(function (sdk) {
        tip('ready');
        $('.get_version').click(function () {
            tip('调用getVersion');
            sdk.getVersion(function (result) {
                tip(result);
            });
        });

        $('.get_user').click(function () {
            tip('调用getUser');
            sdk.getUser(function (result) {
                tip(result);
            });
        });

        $('.upload_btn').click(function () {
            tip('调用uploadFile');
            sdk.uploadFile({
                source:'album',
                type:'images',
                mimeType: 'images/*',
                limitSize: 654444,
                server: '{:url("common/get_qiniu_token","",true,true)}',
                ticket_type: 'avatar'
            }, function (result) {
                tip(result);
            });
        });

        $('.upload_video_btn').click(function () {
            tip('调用uploadFile(视频)');
            sdk.uploadFile({
                source:'album',
                type:'videos',
                mimeType: 'video/*',
                limitSize: 524288000,
                server: '{:url("common/get_qiniu_token","",true,true)}',
                ticket_type: 'admin_videos'
            }, function (result) {
                tip(result);
            });
        });

        $('.goto_btn').click(function () {
            tip('调用goTo');
            sdk.goTo({
                url: '{:LOCAL_PROTOCOL_DOMAIN}video_detail?id=151'
            }, function (result) {
                tip(result);
            });
        });

        $('.recharge_btn').click(function () {
            tip('调用recharge');
            sdk.recharge(function (result) {
                tip(result);
            });
        });

        $('.openwxapp_btn').click(function () {
            tip('调用openWxAPP');
            sdk.openWxAPP({
                userName: 'gh_01081f93d2e9',
                miniProgramType: 0,
                path: '/pages/movie/detail?id=17'
            }, function (result) {
                tip(result);
            });
        });

        $('.navigateback_btn').click(function () {
            tip('调用了navigateBack');
            sdk.navigateBack({p:1}, function (result) {
                tip(result);
            });
        });

        $('.share_btn').click(function () {
            tip('调用了share');
            sdk.share({
                "title": "带走负能量，拯救不开心,我在{:APP_NAME}等你来~",
                "descr": "测试描述",
                "url": "/invite?sk=738d3583d77dfd3227a96380d2ff83212d7aed55&buguid=10152",
                "thumb": "",
                "share_key": "738d3583d77dfd3227a96380d2ff83212d7aed55"
            }, function (result) {
                tip(result);
            });
        });

        $('.getdeviceinfo_btn').click(function () {
            tip('调用了getDeviceInfo');
            sdk.navigateBack(function (result) {
                tip(result);
            });
        });


        $('.ds_btn').click(function () {
            var link = $(this).data('link');
            var type = $(this).data('type');
            if (type == 'room') {
                link = link + $('.room_id').val();
            }else if(type=='post'){
                var topic_id=$('.topic_id').val();
                if(topic_id!=''){
                    var title=$('.topic_title').val();
                    link = link + '?topic_id='+$('.topic_id').val()+'&title='+encodeURI(title);
                }
            }
            tip('跳转本地协议：' + link);
            sdk.goTo({
                url: link
            }, function (result) {
                tip(result);
            });
        })


    });
</script>

<!--
<script>
    dssdk.ready(function (sdk) {
        //获取APP版本示例
        sdk.getVersion(function (result) {
            if (result['err_status'] == 0) {
                console.log(result);
            } else {
                console.log(result['err_msg']);
            }
        });

        //获取已登录用户信息
        sdk.getUser(function (result) {
            if (result['err_status'] == 0) {
                console.log(result);
            } else {
                console.log(result['err_msg']);
            }
        });

        //调用上传文件
        sdk.uploadFile({
            type: 'images',
            limitSize: 1000,
            server: ''
        }, function (result) {
            if (result['err_status'] == 0) {
                console.log(result);
            } else {
                console.log(result['err_msg']);
            }
        });
    });
</script>-->


</body>
</html>
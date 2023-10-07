<!doctype html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>{$product_slogan} 邀请您体验{$product_name}服务~</title>
    <meta name="keywords" content=""/>
    <meta name="description" content=""/>
    <meta name="HandheldFriendly" content="true"/>
    <meta name="MobileOptimized" content="width"/>
    <meta id="viewport" content="width=device-width, user-scalable=no,initial-scale=1" name="viewport"/>
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta content="black" name="apple-mobile-web-app-status-bar-style"/>
    <meta content="telephone=no" name="format-detection"/>
    <link href="__VENDOR__/animate.min.css?v=__RV__" type="text/css" rel="stylesheet"/>
    <link href="__CSS__/h5_show/all.css?v=__RV__" type="text/css" rel="stylesheet"/>
    <link href="__CSS__/h5_show/index.css?v=__RV__" type="text/css" rel="stylesheet"/>
    <link rel="stylesheet" type="text/css" href="/bx_static/h5.css"/>
    <script src="__VENDOR__/jquery.min.js?v=__RV__"></script>
    <script src="__VENDOR__/smart/smart_lw.bundle.js?v=__RV__"></script>
    <script src="__VENDOR__/layer/mobile/layer.js?v=__RV__"></script>
    <script src="/bx_static/changeFont.js"></script>
    <script type="text/javascript">
        var __HOME = '__H5__', globleLoadingImg = [], defaultPath = '';
        var userId = '{$user.user_id}';
        var anchorId = '{$anchor.user_id}';
        var sendSmsCodeUrl = '{:url("common/send_code")}';
        var countdown = parseInt('{$countdown}');
        var regUrl = '{:url("invite/reg")}';
        var wxSuccess = '{$wx}';
        var downloadUrl = '{:url("h5/download/index")}';
    </script>
    <style type="text/css">
        .touxiang {
            border-radius: 120px;
            border: 2px solid #2c9694;
            float: left;
        }

        .popup_bg {
            position: fixed;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 998;
            display: none;
        }

        .popup_box {
            position: fixed;
            width: 80%;
            padding: 15px;
            background-color: #fff;
            box-sizing: border-box;
            left: 50%;
            margin-left: -40%;
            z-index: 999;
            display: none;
        }

        .popup_box * {
            box-sizing: border-box;
        }

        .popup_list, .popup_list li {
            padding: 0;
            margin: 0;
            list-style: none;
        }

        .popup_title {
            font-size: 26px;
            color: #383838;
            font-weight: normal;
            width: 100%;
            display: block;
            text-align: center;
            padding-bottom: 20px;
        }

        .popup_list li {
            margin-top: 10px;
        }

        .popup_list li:first-child {
            margin-top: 0;
        }

        .popup_input {
            background-color: #fff;
            border: solid 1px #DCDCDC;
            line-height: 30px;
            padding: 5px 10px;
            border-radius: 5px;
            width: 100%;
            font-size: 16px;
        }

        .popup_btn {
            display: block;
            padding: 0;
            margin: 0;
            text-decoration: none;
            color: #fff;
            background-color: #0bb20c;
            line-height: 40px;
            height: 40px;
            width: 100%;
            text-align: center;
            border-radius: 5px;
        }

        .get_sms_code {
            display: block;
            line-height: 40px;
            border: solid 1px #DCDCDC;
            top: 0;
            right: 0;
            width: 40%;
            background-color: #f5f5f5;
            color: #555;
            text-align: center;
            position: absolute;
            font-size: 12px;
            border-bottom-right-radius: 5px;
            border-top-right-radius: 5px;
        }
        .layui-m-layerbtn span[no]
        {
            margin: 0 0.4rem;
            height: 48px;
            line-height: 48px;
            font-size: 0.2rem;
            border-radius: 0.384rem;
        }
        .layui-m-layer0 .layui-m-layerchild {
            max-width: 415px;
        }

        .weixin-tip{display: none; position: fixed; left:0; top:0; bottom:0; background: rgba(0,0,0,0.8); filter:alpha(opacity=80);  height: 100%; width: 100%; z-index: 100;}
        .weixin-tip p{text-align: center; margin-top: 10%; padding:0 5%;}
        .code 
        {
         background:url(code_bg.jpg);
         font-family:Arial;
         font-style:italic;
         color:blue;
         font-size:15px;
         border:0;
         padding:2px 3px;
         letter-spacing:3px;
         font-weight:bolder;
         float:left;
         cursor:pointer;
         width:100px;
         height:50px;
         line-height:40px;
         text-align:center;
         vertical-align:middle;
        }
         
        .acode{
         text-decoration:none;
         font-size:12px;
         color:#288bc4;
        }
        .acode:hover 
        {
         text-decoration:underline;
        }
        
    </style>
    <script src="__JS__/xmt.js?v=__RV__"></script>
</head>


<body class="invite_index">

<div class="weixin-tip">
    <p>
        <img src="__STATIC__/common/image/default/share_friend.png" alt="微信打开" style="max-width: 100%;height: auto;"/>
    </p>
</div>

<div style="display:none;" class="loading">
    <div class="load_box">
        <div class="spinner">
            <div class="cube1 color"></div>
            <div class="cube2 color"></div>
        </div>
    </div>
</div>
<div class="" id="slider">
    <section class="main-page current">
        <div class="poa-wh">
            <div class="font_title" style="width: 100%">
                <div class="">发现你的精彩</div>
                <div class="">{$product_name}</div>
            </div>
            <img src="/bx_static/admin/assets/image_h_back_first@3x.png" class="img100" style="display:block;"/>
            <div class="offset">
                <div style="position: relative;" id="yingcang1">
                    <div class="bg_sm">
                        <div class="write1">
                            <img src="{$info.avatar}" class="touxiang"/>
                            <div class="font_info">
                                Hi~，我是来自{$product_name}的{$info.nickname|short=15}，{$product_name}是一款专注于年轻人的原创短视频社交分享应用。一起发现更多精彩吧~
                            </div>
                        </div>
                    </div>
                </div>
                <div style="position: relative;">
                    <div class="bg">
                        <div class="title">即刻登录</div>
                        <div data-type="account" class="theme2 reg_btn">
                            <div class="account_content">账号登录领取</div>
                        </div>
                        
                    </div>
                    
                    <div class="bg" id="yingcang3">
                        <div class="title">温馨提示</div>
                        <gt name="bean" value="0">
                            <div class="detail" id="yingcang2">
                                <p>领取{$bean_name}后下载{$product_name}APP即可使用；</p>
                                <p>{$bean_name}可以自存也可以用来购买礼物砸晕你心仪的主播；</p>
                                <p>活动仅对被邀请的新注册用户有效，且只可享受一次；</p>
                            </div>
                            <else/>
                            <div class="detail" id="yingcang2">
                                <p>使用第三方登录需要绑定手机号</p>
                                <p>注册成功后即可使用手机号登录APP</p>
                            </div>
                        </gt>
                    </div>
                </div>
            </div>
            <style type="text/css">
                .fenxiangs {
                    position: fixed;
                    bottom: 60px;
                    left: 10%;
                    z-index: 99999;
                    display: none;
                }
            </style>
            <div class="fenxiangs">
                <div style=""></div>
            </div>
            <div class="footer">
                <a href="javascript:;" class="share_btn"> 
                    <div>分享给我的好友</div>
                </a>
                <a href="javascript:;" id="banbenhao" class="download_btn">
                   <div>立即下载APP</div>
                </a>
            </div>
        </div>
    </section>
</div>
<script src="http://res.wx.qq.com/open/js/jweixin-1.2.0.js?v=__RV__"></script>
<script>
    var wxConfigJson = '{:htmlspecialchars_decode($jsapi_config)}', wxConfig = {};
    if (wxConfigJson != '') {
        try {
            wxConfig = JSON.parse(wxConfigJson);
        } catch (e) {
        }
    }
</script>
<script src="__JS__/invite.js?v=__RV__"></script>

<div class="popup_bg"></div>
<div class="popup_phone_box popup_box">
    <h3 class="popup_title">手机号注册</h3>
    <ul class="popup_list">
        <li>
            <input name="phone" placeholder="手机号" class="popup_input"/>
        </li>
        <li style="position: relative;">
            <input name="code" placeholder="验证码" class="popup_input"/>
            <div class="get_sms_code">获取验证码</div>
        </li>

        <li style="position: relative; <if condition="$site.invite_code eq '0'" /> display:none </if> ">
            <input name="invite_code" placeholder="邀请码" class="popup_input" value="{$user.invite_code}"/>
        </li>


        <li>
            <input type="hidden" name="reg_type" value=""/>
            <div class="popup_btn sub_btn">注册</div>
        </li>
        <li style="font-size: 12px;color: #888;line-height: 22px;">
            * <span style="font-size: 12px;color: #888;line-height: 22px;" class="popup_act"></span>成功后即可使用手机号登录APP{:$bean>0?('，登录后将自动领取'.$bean.$bean_name.'奖励。'):'。'}
        </li>
    </ul>
</div>

<div class="popup_account_box popup_box">
    <h3 class="popup_title">用户名密码注册</h3>
    <ul class="popup_list">
        <li>
            <input name="username" placeholder="用户名" class="popup_input"/>
        </li>
        <li>
            <input type="password" name="password" placeholder="密码" class="popup_input"/>
        </li>
        <li>
            <input type="password" name="password_qr" placeholder="确认密码" class="popup_input"/>
        </li>
        <li>
            <div class="code" id="checkCode" onclick="createCode()"></div><a class='acode' href="#" onclick="createCode()">看不清换一张</a>
        </li>
        <li>
            <input type="text" name="inputCode"  id="inputCode" placeholder="请输入验证码" class="popup_input"/>
        </li>
        
        <li style="position: relative; <if condition="$site.invite_code eq '0'" /> display:none </if> ">
            <input name="invite_code" placeholder="邀请码" class="popup_input" value="{$user.invite_code}"/>
        </li>
        <li>
            <input type="hidden" name="reg_type" value=""/>
            <div class="popup_btn sub_btn">注册</div>
        </li>
        <li style="font-size: 12px;color: #888;line-height: 22px;">
            * <span style="font-size: 12px;color: #888;line-height: 22px;" class="popup_act"></span>成功后即可使用用户名密码登录APP{:$bean>0?('，登录后将自动领取'.$bean.$bean_name.'奖励。'):'。'}
        </li>
    </ul>
</div>

<div class="popup_download_box popup_box">
    <h3 class="popup_title popup_download_title">下载APP</h3>
    <p class="popup_download_text" style="font-size: 14px;color: #555;"></p>
    <a href="javascript:;" style="margin-top: 20px;" class="popup_btn download_btn">前往下载</a>
</div>

</body>
</html>
<html>
<head>
    <meta name="keywords" content=""/>
    <meta name="description" content=""/>
    <meta name="HandheldFriendly" content="true"/>
    <meta name="MobileOptimized" content="width"/>
    <meta id="viewport" content="width=device-width, user-scalable=no,initial-scale=1" name="viewport"/>
    <!--    <meta name="apple-mobile-web-app-capable" content="yes">
        <meta content="black" name="apple-mobile-web-app-status-bar-style" />
        <meta content="telephone=no" name="format-detection" />
        <link href="./css/all.css" type="text/css" rel="stylesheet"/>-->
    <script type="text/javascript" src="http://apps.bdimg.com/libs/zepto/1.1.4/zepto.min.js"></script>
    <script src="__VENDOR__/layer/mobile/layer.js"></script>
    <title>{:APP_NAME}微信充值中心</title>
    <style type="text/css">
        /* CSS Document */
        @font-face {
            font-family: 'DIN';
            src: url('/bx_static/admin/style/DIN/DINCond-Bold.otf');
        }
        * {
            margin: 0px;
            padding: 0;
            box-sizing: border-box;
            font-size: 14px;
        }

        html {
            cursor: default;
        }

        #container {
            width: 100%;
            position: relative;
            margin-bottom: 100px;
        }

        html:root body, html:root input, html:root button, html:root textarea, html:root select {
            font-family: Tahoma, Geneva, 'Microsoft YaHei', 'SimSun';
        }

        .clearfloat {
            clear: both;
        }

        .clearfix {
            *zoom: 1
        }

        .clearfix:before, .clearfix:after {
            display: table;
            content: "";
            line-height: 0
        }

        .clearfix:after {
            clear: both
        }

        /* .shadow
        {-webkit-box-shadow: -2px 0 5px #CCCCCC,0 -2px 5px #CCCCCC,0 2px 5px #CCCCCC,2px 0 5px #CCCCCC; -moz-box-shadow: -2px 0 5px #CCCCCC,0 -2px 5px #CCCCCC,0 2px 5px #CCCCCC,2px 0 5px #CCCCCC;  box-shadow: -2px 0 5px #CCCCCC,0 -2px 5px #CCCCCC,0 2px 5px #CCCCCC,2px 0 5px #CCCCCC;}  */
        a img {
            border: none;
        }

        a {
            text-decoration: none;
            color: #3598dc;
            text-overflow: ellipsis;
            white-space: nowrap;
            outline: none;
        }

        a:hover {
            color: #2b84c1;
        }

        input[type=button], button {
            -webkit-appearance: none;
            border: none;
            outline: none;
            font-family: 'microsoft yahei', Verdana, Arial, Helvetica, sans-serif;
        }

        input {
            autocapitalize = "off";
            autocorrect = "off"
        }

        input:focus, textarea:focus, button, select, label:focus {
            outline: none;
            blr: expression_r(this.onFocus=this.blur());
        }

        button, input, select, textarea, label {
            margin: 0;
            vertical-align: middle;
            border: none;
            outline: none;
            font-family: 'microsoft yahei', Verdana, Arial, Helvetica, sans-serif;
        }

        button, input {
            *overflow: visible;
            line-height: normal;
            outline: none;
        }

        button::-moz-focus-inner, input::-moz-focus-inner {
            padding: 0;
            border: 0;
        }

        button, html input[type="button"], input[type="reset"], input[type="submit"] {
            -webkit-appearance: button;
            cursor: pointer;
        }

        input[type="search"] {
            -webkit-box-sizing: content-box;
            -moz-box-sizing: content-box;
            box-sizing: content-box;
            -webkit-appearance: textfield;
        }

        input[type="search"]::-webkit-search-decoration, input[type="search"]::-webkit-search-cancel-button {
            -webkit-appearance: none;
        }

        textarea {
            overflow: auto;
            vertical-align: top;
            resize: none;
        }

        i, em {
            font-style: normal;
        }

        ul, ol li {
            list-style: none;
        }

        .center-btn {
            margin: auto;
            display: block;
        }

        .error {
            color: red;
        }

        .bg-bluegreen {
            color: #fff;
            background-color: #1ab394;
        }

        .inline-block {
            display: inline-block;
        }

        .pointer {
            cursor: pointer;
        }

        .ovhidden {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            display: block;
        }

        .por {
            position: relative;
        }

        .db {
            display: block;
        }

        body, html {
            height: 100%;
            position: relative;
            background: #d6f7ff;
        }

        /* Tools */
        .hidden {
            display: block !important;
            border: 0 !important;
            margin: 0 !important;
            padding: 0 !important;
            font-size: 0 !important;
            line-height: 0 !important;
            width: 0 !important;
            height: 0 !important;
            overflow: hidden !important;
        }

        .o-hidden {
            overflow: hidden !important;
        }

        .nobr {
            white-space: nowrap !important;
        }

        .wrap {
            white-space: normal !important;
        }

        .break {
            word-break: break-all !important;
            word-wrap: break-word !important;
        }

        .a-left, .al {
            text-align: left !important;
        }

        .a-center, .ac {
            text-align: center !important;
        }

        .a-right, .ar {
            text-align: right !important;
        }

        .v-top {
            vertical-align: top;
        }

        .v-middle {
            vertical-align: middle;
        }

        .f-left, .left, .fl {
            float: left !important;
        }

        .f-right, .right, .fr {
            float: right !important;
        }

        .f-none, .fn {
            float: none !important;
        }

        .f-fix {
            float: left;
            width: 100%;
        }

        .no-display, .hide, .none {
            display: none;
        }

        .no-margin, .mg0 {
            margin: 0 !important;
        }

        .no-padding, .pd0 {
            padding: 0 !important;
        }

        .no-bg, .bgn {
            background: none !important;
        }

        .mb0 {
            margin-bottom: 0 !important;
        }

        .mt0 {
            margin-top: 0 !important;
        }

        .mb10 {
            margin-bottom: 10px !important;
        }

        .mb20 {
            margin-bottom: 20px !important;
        }

        .mt10 {
            margin-top: 10px !important;
        }

        .mt20 {
            margin-top: 20px !important;
        }

        .pb0 {
            padding-bottom: 0 !important;
        }

        .pt0 {
            padding-top: 0 !important;
        }

        .pb10 {
            padding-bottom: 10px !important;
        }

        .pb20 {
            padding-bottom: 20px !important;
        }

        .pt10 {
            padding-top: 10px !important;
        }

        .pt20 {
            padding-top: 20px !important;
        }

        .bob0 {
            border-bottom: none !important;
        }

        .cl, .clr {
            height: 0;
            font-size: 1px;
            clear: both;
            line-height: 0;
        }

        .block {
            display: block;
            margin: auto;
        }

        .pro {
            position: relative;
            display: block;
        }

        .fwb {
            font-weight: bold;
        }

        .fw4 {
            font-weight: 400;
        !important
        }

        .fw6 {
            font-weight: 600;
        !important
        }

        .fw7 {
            font-weight: 700;
        !important
        }

        .hidden100 {
            height: 100%;
            overflow: hidden;
        }

        /*°´Å¥*/
        .btn {
            padding: 8px 20px;
            -webkit-border-radius: 5px;
            -moz-border-radius: 5px;
            border-radius: 5px;
            display: inline-block;
        }

        .btn-yellow {
            background: #ffdb2a;
            color: #c9171d;
        }

        .btn-yellow:hover, .btn-yellow:focus, .btn-yellow:active, .btn-yellow.active, .open > .dropdown-toggle.btn-yellow {
            color: #c9171d;
            background-color: #fcd515;
        }

        /*loading*/
        .loading {
            position: fixed;
            top: 0;
            left: 0;
            background-color: #e2e2e2;
            width: 100%;
            height: 100%;
            z-index: 999999999;
            overflow: hidden;
        }

        .load_box {
            position: absolute;
            top: 50%;
            left: 0;
            width: 100%
        }

        .loading .spinner {
            margin: 0 auto;
            width: 64px;
            height: 64px;
            position: relative;
        }

        .loading .color {
            background: #fff;
        }

        .loading .cube1, .loading .cube2 {
            width: 30px;
            height: 30px;
            position: absolute;
            top: 0;
            left: 0;
            -webkit-animation: cubemove 1.8s infinite ease-in-out;
            animation: cubemove 1.8s infinite ease-in-out;
        }

        .loading .cube2 {
            -webkit-animation-delay: -0.9s;
            animation-delay: -0.9s;
        }

        .loading .load_text {
            margin-top: 25px
        }

        @-webkit-keyframes cubemove {
            25% {
                -webkit-transform: translateX(42px) rotate(-90deg) scale(0.5)
            }
            50% {
                -webkit-transform: translateX(42px) translateY(42px) rotate(-180deg)
            }
            75% {
                -webkit-transform: translateX(0px) translateY(42px) rotate(-270deg) scale(0.5)
            }
            100% {
                -webkit-transform: rotate(-360deg)
            }
        }

        @keyframes cubemove {
            25% {
                transform: translateX(42px) rotate(-90deg) scale(0.5);
                -webkit-transform: translateX(42px) rotate(-90deg) scale(0.5);
            }
            50% {
                transform: translateX(42px) translateY(42px) rotate(-179deg);
                -webkit-transform: translateX(42px) translateY(42px) rotate(-179deg);
            }
            50.1% {
                transform: translateX(42px) translateY(42px) rotate(-180deg);
                -webkit-transform: translateX(42px) translateY(42px) rotate(-180deg);
            }
            75% {
                transform: translateX(0px) translateY(42px) rotate(-270deg) scale(0.5);
                -webkit-transform: translateX(0px) translateY(42px) rotate(-270deg) scale(0.5);
            }
            100% {
                transform: rotate(-360deg);
                -webkit-transform: rotate(-360deg);
            }
        }

        /*·âÃæ*/
        #arrowUp {
            position: fixed;
            z-index: 9999;
            width: 40px;
            left: 50%;
            margin-left: -20px;
            bottom: 20px;
            -webkit-animation: arrowUp 1s infinite ease-in-out;
            -moz-animation: arrowUp 1s infinite ease-in-out;
            -ms-animation: arrowUp 1s infinite ease-in-out;
            animation: arrowUp 1s infinite ease-in-out
        }

        .main-page {
            display: none;
        }

        .main-page.current {
            display: block;
        }

        .poa-wh {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
        }

        .img100 {
            width: 100%;
        }

        .main-page.current .logo {
            position: absolute;
            top: 2%;
            left: 3%;
            width: 40%;
            animation: fadeInDown .5s ease .5s 1 both;
            -webkit-animation: fadeInDown .5s ease .5s 1 both;
        }

        .theme1 {
            animation: rotateIn 1s ease 1s 1 both;
            -webkit-animation: rotateIn 1s ease 1s 1 both;
        }

        .theme2 {
            animation: bounceIn 1s ease 1s 1 both;
            -webkit-animation: bounceIn 1s ease 1s 1 both;
        }

        .theme3 {
            animation: bounceIn 1s ease 1.5s 1 both;
            -webkit-animation: bounceIn 1s ease 1.5s 1 both;
        }

        .theme4 {
            animation: bounceIn 1s ease 2s 1 both;
            -webkit-animation: bounceIn 1s ease 2s 1 both;
        }

        .theme5 {
            animation: bounceIn 1s ease 2.5s 1 both;
            -webkit-animation: bounceIn 1s ease 2.5s 1 both;
        }

        .logo {
            animation: bounceInLeft 1s ease 0s 1 both;
            -webkit-animation: bounceInLeft 1s ease 0s 1 both;
        }

        .logo1 {
            animation: bounceInRight 1s ease 0s 1 both;
            -webkit-animation: bounceInRight 1s ease 0s 1 both;
        }

        .search-warp {
            position: relative;
            margin-bottom: 8px;
        }

        .main-page.current .write1 {
            animation: bounceInRight 1s ease 0.5s 1 both;
            -webkit-animation: bounceInRight 1s ease 0.5s 1 both;
        }

        .search-warp {
            position: relative;
            margin-bottom: 8px;
        }

        .search-warp .input-text {
            width: 100%;
            height: 40px;
            padding: 10px 18% 10px 10px;
            font-size: .8em;
            color: #666;
        }

        .search-warp .search-btn {
            position: absolute;
            top: 20%;
            right: 8px;
            width: 9%;
            height: 60%;
            background: url(../images/search_btn.png);
            background-repeat: no-repeat;
            background-size: 100% 100%;
        }

        .main-page .write .item .ranking-btn {
            width: 100%;
            height: 40px;
            color: #fff;
            background: #ff5450;
        }

        .main-page .write .tips {
            font-size: .7em;
            color: #a2e2ff;
            text-align: center;
            margin-top: 15px;
        }

        /*ÏêÇé*/
        .header {
            width: 100%;
            height: 48px;
            position: fixed;
            background: #3c93d7;
            padding: 0 10px;
        }

        .header .go-back {
            float: left;
            margin: 11px 0;
        }

        .header .search-warp {
            width: 88%;
            float: right;
            margin: 7px 0;
        }

        .header .search-warp .input-text {
            height: 34px;
            padding: 7px 18% 7px 7px;
            -webkit-border-radius: 20px;
            -moz-border-radius: 20px;
            border-radius: 20px;
        }

        .header .search-warp .search-btn {
            width: 7%;
        }

        .detail-warp {
            margin-top: 20%;
        }

        .detail-warp .head-img {
            width: 120px;
            height: 120px;
            border: 3px solid #8ec3ea;
            margin: 0 auto;
        }

        .detail-warp .head-img img {
            width: 100%;
            height: 100%;
        }

        .detail-warp .name {
            margin-top: 2%;
            padding: 3px 0;
        }

        .detail-warp .name .cha {
            font-size: 1.2em;
            color: #318bd2;
        }

        .detail-warp .name .en {
            font-size: 1.2em;
            color: #919191;
        }

        .detail-warp .ranking {
            color: #919191;
        }

        .detail-warp .ranking .num {
            color: #5b9ed4;
        }

        .detail-warp .ranking .arrow {
            font-size: 1.2em;
        }

        .detail-warp .ranking .arrow.up {
            color: #46f60f;
        }

        .detail-warp .ranking .arrow.down {
            color: #e65454;
        }

        .detail-warp .data {
            width: 70%;
            margin: 2% auto;
        }

        .detail-warp .data li {
            padding: 5px 0;
        }

        @media screen and (max-width: 320px) {
            .detail-warp .data li {
                padding: 2px 0;
            }
        }

        .detail-warp .data .label {
            font-size: 1.2em;
            color: #4a9ddc;
        }

        .detail-warp .data .label .iconfont {
            font-size: 1em;
            margin-right: 5px;
        }

        .detail-warp .data .word {
            color: #999;
        }

        .btns {
            width: 80%;
            margin: 0 auto;
        }

        .btns .ranking-btn {
            width: 45%;
            padding: 8px 0;
            background: #ff5451;
            color: #fff;
        }

        .btns .share-btn {
            width: 45%;
            padding: 8px 0;
            background: #3b96db;
            color: #fff;
        }

        /*ÁÐ±í*/
        .detail-warp .table {
            width: 80%;
            margin: 0 auto;
        }

        .detail-warp .table th {
            padding: 8px;
        }

        .detail-warp .table td {
            font-size: .8em;
            padding: 5px 8px;
            text-align: center;
        }

        .detail-warp .table td .word {
            margin-left: 10px;
        }

        .detail-warp .table td .word i {
            color: #318bd2;
            display: block;
            font-size: 1em;
        }

        .detail-warp .table td .word em {
            color: #999;
            display: block;
            font-size: .9em;
        }

        .detail-warp .table td .num {
            color: #318bd2;
        }

        .detail-warp .table td .arrow {
            font-size: 1.6em;
        }

        .detail-warp .table td .arrow.up {
            color: #e65454;
        }

        .detail-warp .table td .arrow.down {
            color: #46f60f;
        }

        .detail-warp .table td .history {
            color: #999;
        }

        .modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            display: none;
            cursor: pointer;
        }

        .modal-warp {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            display: none;
            cursor: pointer;
        }

        /*dropload-down*/
        .dropload-down {
            height: 50px;
        }

        .dropload-down .dropload-noData {
            height: 50px;
            line-height: 50px;
            text-align: center;
        }

        .dropload-down .dropload-load {
            height: 50px;
            line-height: 50px;
            text-align: center;
        }

        .dropload-down .dropload-load .load {
            display: inline-block;
            height: 15px;
            width: 15px;
            border-radius: 100%;
            margin: 6px;
            border: 2px solid #666;
            border-bottom-color: transparent;
            vertical-align: middle;
            -webkit-animation: rotate 0.75s linear infinite;
            animation: rotate 0.75s linear infinite;
        }

        @-webkit-keyframes rotate {
            0% {
                -webkit-transform: rotate(0deg);
            }
            50% {
                -webkit-transform: rotate(180deg);
            }
            100% {
                -webkit-transform: rotate(360deg);
            }
        }

        @keyframes rotate {
            0% {
                transform: rotate(0deg);
            }
            50% {
                transform: rotate(180deg);
            }
            100% {
                transform: rotate(360deg);
            }
        }

        body, html {
            background-color: white;
        }

        .title {
            font-size: 18px;
            color: #404040;
            padding: 1em 0;
            border-bottom-width: 1px;
            box-shadow: 0px 2px 10px #dcdbdb;
            width: 100%;
            text-align: center;
            margin-bottom: 1em;
            font-weight: 600;
        }

        li {
            list-style-type: none;
            margin-bottom: 1.2em;
            text-align: center;
        }

        .cuckoo {
            list-style: none;
            width: 30%;
            float: left;
            line-height: 23px;
            padding: 4px;
            vertical-align: center;
            margin: 1.6%;
            box-shadow: 2px 2px 2px 2px #ddd;
            text-shadow: 2px 2px 3px white;
            border-radius: 5px;
            color: gray;
        }
        .cuckoo > p {
            color: #777;
            font-size: 12px;
        }
        .cuckoo img {
            position: absolute;
            right: 0px;
            top: 0px;
            height: 40px;
        }

        .photo {
            border-radius: 50px;
        }

        .setInfo {
            display: none;
            margin: 1em 0;
        }

        .setInfo:after {
            content: "020";
            display: block;
            height: 0;
            clear: both;
            visibility: hidden;
        }

        #user_id {
            height: 36px;
            border-radius: 3px;
            padding-left: 8px;
            line-height: 36px;
            width: 80%;
            font-size: 14px;
            background-color: rgba(227, 227, 227, 0.3);
            transition: all 0.4s;
        }
        #user_id:focus{
            box-shadow: 0 0 2px #ff2d52;
        }
        #btn, #confirmBtn {
            border-radius: 24px;
            height: 45px;
            line-height: 45px;
            font-size: 16px;
            background-color: #ff2d52;
            color: white;
            width: 80%;
            display: block;
            cursor: pointer;
        }

        #getInfo > div {
            padding: 10px 0;
        }

        .tips {
            text-align: left;
            font-size: 14px;
            margin-top: 2em;

        }
        .flex{
            display: flex;
            justify-content: center;
        }
        .flex > p{
            font-family: 'DIN';
            color:#888;
            font-size:22px;
        }
        .LX{
            width:18px;
            height:18px;
            background:url('/bx_static/admin/assets/gold.png') center no-repeat;
            background-size:cover;
            margin-right: 5px;
        }
        .tips > h3 {
            margin: 20px;
        }

        .tips > p {
            padding: 3px 3px 3px 5px;
            font-size: 14px;
            color: #888;
        }
        .layui-m-layerbtn{
            width: 80%;
            height: 50px;
            margin: auto;
            line-height: 50px;
            font-size: 0;
            border-top: unset;
            background-color: #ff2d52;
            border-radius: 24px;
            margin-bottom: 20px;
        }
        .layui-m-layerbtn span[yes]{
            color:white;
        }
        .layui-m-layercont{
            font-size:16px;
        }
    </style>
</head>
<body>
<div align="center">
    <div class="title">请输入用户号</div>
    <div align="center">
        <div class="setInfo">
            <img class="photo" width="80" height="80" src="" alt="">
            <div style="color: gray;padding: 0.6em 0;font-size: 1.1em" class="user_nicename"></div>
            <div style="color: gray;padding: 0.6em 0;font-size: 1.0em;display: none;" class="phone"></div>
            <!-- <div style="width: 50%;">
                <div class="user_cuckoo" style="font-size: 1.2em;border-radius: 10px;line-height: 1.6em;color: gray;"></div>
            </div> -->
        </div>
        <div id="getInfo">
            <div><input type="text" name="user_id" placeholder="请输入用户号" id="user_id"></div>
            <div><a href="javascript:void(0);" id="btn">确认</a></div>
        </div>
        <div class="setInfo" style="margin-bottom: 40px;">
            <volist name="list" id="vo">
                <li class="cuckoo" id="{$vo.id}">
                    <div class="flex">
                        <div class="LX"></div>
                        <p>{$vo.bean_num}</p>
                    </div>
                    <p>{$vo.price}元</p>
                </li>
            </volist>
        </div>
        <div class="setInfo">
            <a href="javascript:void(0);" id="confirmBtn">立即充值</a>
        </div>

    </div>
    <div class="tips">
        <h3>温馨提示:</h3>
        <h4 style="margin: 0 8px 12px;color: #ff2d52;">1、请认真核实您的用户信息，确认无误后进行充值，个人失误造成的充值错误概不负责；</h4>
        <h4 style="margin: 0 8px 12px;color: #ff2d52;">2、一经充值，概不退款；</h4>
        <h3>充值步骤：</h3>
        <p>1、首先输入您需要充值的{:APP_NAME}用户ID进行查询；</p>
        <p>2、确认后在充值页面选择您需要充值的金额进行充值；</p>
        <p>3、若您还没有用户账号，请先下载<a href="{:url('download/index')}">{:APP_NAME}</a>并进行注册；</p>
    </div>
    <input type="hidden" name="uid" id="uid">
    <input type="hidden" name="changeid" id="changeid">
</div>
<div style="height: 30px;clear: both;"></div>
</body>

<script type="text/javascript">
    var isPaying = false;
    $(function () {
        $('#btn').on('click', function () {
            var uid = $('#user_id').val();
            if ($.trim(uid) == '') {
                layer.open({
                    content: '请输入用户号',
                    btn: '确定'
                });
                return;
            }

            $.post('/h5/wx_charge/get_user_info', {"user_id": uid}, function (result) {

                if (result.status != '0') {
                    layer.open({
                        content: '无此用户,请重试'
                        , btn: '确定'
                    });
                    return;
                }

                $('#getInfo').hide();
                $('.setInfo').show();
                $('.title').html('用户充值');
                $('.photo').attr('src', result.data.avatar);
                $('.user_nicename').html(result.data.nickname);
                //$('.phone').html(result.data.phone);
                $('#uid').val(result.data.user_id);
            });
        });

        $('.cuckoo').on("click", function () {
            $(this).css({"color": "#ff2d52","box-shadow": "0px 0px 20px #ffbac6"}).siblings().css({"color": "gray","box-shadow": "2px 2px 2px 2px #ddd"});
            $('#changeid').val($(this)[0].id);
        });

        $('#confirmBtn').on("click", function (ev) {
            var that = $(this);
            var id = $('#user_id').val();
            if ($.trim(id) == '') {
                layer.open({
                    content: '请先输入用户号'
                    , btn: '确定'
                });
                return;
            }

            var changeid = $('#changeid').val();
            var uid = $('#uid').val();

            if (changeid == '') {
                layer.open({
                    content: '请选择充值金额'
                    , btn: '确定'
                });
                return;
            } else if (uid == '') {
                layer.open({
                    content: '充值用户ID不能为空'
                    , btn: '确定'
                });
                return;
            }

            layer.open({
                content: '请再次确认您的账号信息，用户号：' + uid + '，昵称：' + $('.user_nicename').text()
                , btn: ['继续充值', '取消充值']
                , yes: function (index) {
                    next(that, uid, changeid);
                    layer.close(index);
                }
            });


        });
    });

    function next(that, uid, changeid) {
        if (isPaying) {
            return false;
        }
        that.hide();
        isPaying = true;
        $.post('/h5/wx_charge/wx_pay_order', {user_id: uid, change_id: changeid}, function (res) {
            isPaying = false;
            if (res.status != '0') {
                layer.open({
                    content: res.message
                    , btn: '确定'
                }, function () {
                    that.show();
                });
                return;
            } else {
                callPay(res.data.third_data);
            }
        });
    }

    function callPay(obj) {
        WeixinJSBridge.invoke(
            'getBrandWCPayRequest', obj,
            function (res) {
                if (res.err_msg == "get_brand_wcpay_request:ok") {
                    // 使用以上方式判断前端返回,微信团队郑重提示：
                    //res.err_msg将在用户支付成功后返回ok，但并不保证它绝对可靠。
                }
            });
        $('#confirmBtn').show();
    }


</script>

</html>
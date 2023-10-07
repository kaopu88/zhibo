<include file="public/head" />
<title>{:APP_NAME}pk排位赛</title>
<link rel="stylesheet" href="__H5__/css/cover_star/common.css?{:date('YmdHis')}">
<script src="__H5__/js/css-base.js?v=__RV__"></script>
<style>
    /*.page-wrap { background-color: #000000; }*/
    .container { height:100vh; background:#0D0E10 url("__H5__/pk_rank/images/bg_received.jpg") no-repeat; background-size:100%; background-attachment: fixed; background-position:top;}
    .list { padding-top:5.2rem; padding-left: .8rem; }
    .list .fans { margin:0 0 .2rem; display:block; font-size:.3rem; font-weight:700; color:#333; }
    .list .fans .avatar { position: relative;text-align: center;display: inline-block;width: .8rem;vertical-align: middle; }
    .list .fans .avatar img { width: .7rem;height: .7rem;border-radius: 50%; }
    .list .fans .nickname { width: 2rem;display: inline-block;margin-left: .1rem;vertical-align: middle;overflow: hidden;white-space: nowrap;text-overflow: ellipsis; color: #fff;}
    .list .fans .score { width: 1.6rem;text-align: center;display: inline-block; color: #999; font-size: .26rem }
</style>
</head>
<body>
<!--
<div class="page-wrap">
    <div class="container">
        <notempty name="_info">
            <div class="list">
            <volist name="_info" id="vo">
                <div class="fans clearfix">
                    <div class="avatar">
                        <img src="{$vo.avatar}">
                    </div>
                    <div class="nickname">{$vo.nickname}</div>
                    <div class="score">{$vo.datetime|time_format='','date'}</div>
                </div>
            </volist>
            </div>
        </notempty>
    </div>
</div>
-->
</body>
</html>
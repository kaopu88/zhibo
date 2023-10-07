<include file="public/head" />
<title>经纪人关系</title>
<link rel="stylesheet" href="__H5__/css/common.css?{:date('YmdHis')}">
<link rel="stylesheet" href="__H5__/css/promotion.css?{:date('YmdHis')}">
<script src="__VENDOR__/jquery.min.js?v=__RV__"></script>
<script src="__VENDOR__/vue-dev.js?v=__RV__"></script>
<script type="text/javascript" src="__H5__/js/css-base.js?v=__RV__"></script>
<script src="__VENDOR__/bugujsdk.js?{:date('YmdHis')}"></script>
<script src="__STATIC__/common/js/layer_mobile/layer.js"></script>
<script type="text/javascript" src="__H5__/js/promotion/index.js?{:date('YmdHis')}"></script>
</head>
<body>
<div class="body-content" id="app">
    <div class="cover">
        <img v-bind:src="user_info.cover" class="img-responsive">
        <div class="user_info">
            <div class="avatar">
                <img v-bind:src="user_info.avatar">
            </div>
        </div>
    </div>
    <div class="wrap">
        <div class="user_info">
            <div class="nickname">
                {{ user_info.nickname }}
            </div>
        </div>
        <div class="user_info_a">
            <div class="nickname">用户:{{user_info.nickname}}</div>
        </div>
        <div class="agent_info" v-if="user_info.agent_admin">
            <div class="name">经纪人帐号:{{user_info.agent_admin.username}}</div>
        </div>
        <ul class="entrance">
            <li><a href="{:url('bindview')}" class="btn">绑定帐号</a></li>
            <li><a href="{:url('applyview')}" class="btn">绑定客户</a></li>
        </ul>
    </div>
</div>
</body>
</html>
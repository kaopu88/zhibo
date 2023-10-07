<include file="public/head" />
<title>经纪人帐号绑定</title>
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
        <div class="account_box" v-if="!user_info.agent_admin">
            <div class="tip">
                请输入子账户的帐号和密码确认绑定
            </div>
            <div class="account_info">
                <input placeholder="请输入子账户的帐号" type="text" class="account_input" v-model="username" />
                <input placeholder="请输入子账户的密码" type="password" class="account_input" v-model="password" />
            </div>
            <button class="sub" @click="bind" :disabled="isDisabled">绑定</button>
        </div>
        <div class="agent_info" v-else>
            帐号:{{ user_info.agent_admin.username }}<br>
            手机:{{ user_info.agent_admin.phone }}
        </div>
    </div>
</div>
</body>
</html>
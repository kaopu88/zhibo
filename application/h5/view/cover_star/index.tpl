<include file="public/head" />
<title>封面之星</title>
<link rel="stylesheet" href="__H5__/css/cover_star/common.css?{:date('YmdHis')}">
<link rel="stylesheet" href="__H5__/css/cover_star/index.css?{:date('YmdHis')}">
<script src="__VENDOR__/jquery.min.js?v=__RV__"></script>
<script src="__VENDOR__/vue.js?v=__RV__"></script>
<script type="text/javascript" src="__H5__/js/css-base.js?v=__RV__"></script>
<script src="__VENDOR__/bugujsdk.js?{:date('YmdHis')}"></script>
<script src="__STATIC__/common/js/layer_mobile/layer.js"></script>
<script src="__VENDOR__/callapp/index.umd.js?v=__RV__"></script>
<script type="text/javascript" src="__H5__/js/cover_star/cover_star.js?{:date('YmdHis')}"></script>
<script>
    var avatar_img = "{$avatar_img}";
    var down_url = "{$down_url}";
</script>
</head>
<body>
<div class="body-content" id="app">
    <div class="content-wrap">
        <div class="anchor">
<!--            <img src="__H5__/images/share/avatar.png">-->
            <img v-bind:src="coverstar.avatar">
        </div>
        <div class="occupied">
            <div class="avatar"><img v-bind:src="coverstar.avatar"></div>
            <div class="tit">
                <div class="nickname">{{ coverstar.nickname }}</div>
                <div class="tip">占领该封面</div>
                <div class="clearfix"></div>
            </div>
            <div class="clearfix"></div>
        </div>
        <div class="ruleBtn">
            <a href="{:url('explain')}"><img src="__H5__/images/cover_star/guize.png"></a>
        </div>
    </div>
    <div class="anchorRank clearfix" v-if="user_info.is_anchor == '1'">
        <div class="tip">
            <div class="up" v-if="user_info.ranking == 2"></div>
            <div class="down" v-if="user_info.ranking == 1"></div>
        </div>
        <div class="anchorInfo">
            <div class="avatar" @click="toanchorroom('{:LOCAL_PROTOCOL_DOMAIN}personal?user_id=', user_info.user_id)"><img v-bind:src="user_info.avatar"></div>
            <div class="info" @click="toanchorroom('{:LOCAL_PROTOCOL_DOMAIN}personal?user_id=', user_info.user_id)">
                <div class="tit">{{user_info.nickname}}</div>
                <div class="ranktxt" v-if=" user_info.rank !== false">第{{user_info.rank}}名</div>
                <div class="ranktxt" v-else>暂无排名</div>
            </div>
            <div class="clearfix"></div>
        </div>
        <div class="other">
            <div class="votes">{{user_info.rank_vote}}票</div>
        </div>
    </div>
    <div class="rankList">
        <div class="list" v-for="(v,index) in items">
            <div class="anchorRank clearfix" >
                <div class="tip">
                    <div class="rank" :class="v.rank < 4 ? 'rank-'+v.rank : 'class-b' ">{{v.rank}}</div>
                </div>
                <div class="anchorInfo">
                    <div class="avatar" @click="toanchorroom('{:LOCAL_PROTOCOL_DOMAIN}personal?user_id=', v.user_id)"><img v-bind:src="v.avatar"></div>
                    <div class="info" @click="toanchorroom('{:LOCAL_PROTOCOL_DOMAIN}personal?user_id=', v.user_id)">
                        <div class="tit">{{v.nickname}}</div>
                        <div class="votes">获得票数：<span>{{v.votes}}</span></div>
                    </div>
                    <div class="clearfix"></div>
                </div>
                <div class="other">
                    <div class="toVote">
                        <button class="btn" @click="toVote(index,v.user_id)">投票</button>
                    </div>
                </div>
            </div>
            <div class="vote_box" v-if="index === clickVote">
<!--            <div class="vote_box">-->
                <div class="triangle"></div>
                <div class="vote_square">
                    <div class="number_line">
                        <span class="text">投票数：</span>
                        <div class="caculate caculate1" @click="num_dec"></div>
                        <input type="number" name="num" class="number" v-model.number="vote_value" >
                        <div class="caculate caculate2" @click="num_inc"></div>
                    </div>
                    <div class="style_line">
                        <div class="goldplay select"><span>消耗{{vote_bean}}钻石</span> </div>
                    </div>
                    <button class="govote" @click="govote(v.user_id)" :disabled="isDisabled">立即投票</button>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
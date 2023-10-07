<?php /*a:2:{s:59:"/www/wwwroot/zhibb/application/h5/view/cover_star/index.tpl";i:1640852354;s:54:"/www/wwwroot/zhibb/application/h5/view/public/head.tpl";i:1595042494;}*/ ?>
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

<title>封面之星</title>
<link rel="stylesheet" href="/static/h5/css/cover_star/common.css?<?php echo date('YmdHis'); ?>">
<link rel="stylesheet" href="/static/h5/css/cover_star/index.css?<?php echo date('YmdHis'); ?>">
<script src="/static/vendor/jquery.min.js?v=<?php echo config('upload.resource_version'); ?>"></script>
<script src="/static/vendor/vue.js?v=<?php echo config('upload.resource_version'); ?>"></script>
<script type="text/javascript" src="/static/h5/js/css-base.js?v=<?php echo config('upload.resource_version'); ?>"></script>
<script src="/static/vendor/bugujsdk.js?<?php echo date('YmdHis'); ?>"></script>
<script src="/static/common/js/layer_mobile/layer.js"></script>
<script src="/static/vendor/callapp/index.umd.js?v=<?php echo config('upload.resource_version'); ?>"></script>
<script type="text/javascript" src="/static/h5/js/cover_star/cover_star.js?<?php echo date('YmdHis'); ?>"></script>
<script>
    var avatar_img = "<?php echo htmlentities($avatar_img); ?>";
    var down_url = "<?php echo htmlentities($down_url); ?>";
</script>
</head>
<body>
<div class="body-content" id="app">
    <div class="content-wrap">
        <div class="anchor">
<!--            <img src="/static/h5/images/share/avatar.png">-->
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
            <a href="<?php echo url('explain'); ?>"><img src="/static/h5/images/cover_star/guize.png"></a>
        </div>
    </div>
    <div class="anchorRank clearfix" v-if="user_info.is_anchor == '1'">
        <div class="tip">
            <div class="up" v-if="user_info.ranking == 2"></div>
            <div class="down" v-if="user_info.ranking == 1"></div>
        </div>
        <div class="anchorInfo">
            <div class="avatar" @click="toanchorroom('<?php echo LOCAL_PROTOCOL_DOMAIN; ?>personal?user_id=', user_info.user_id)"><img v-bind:src="user_info.avatar"></div>
            <div class="info" @click="toanchorroom('<?php echo LOCAL_PROTOCOL_DOMAIN; ?>personal?user_id=', user_info.user_id)">
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
                    <div class="avatar" @click="toanchorroom('<?php echo LOCAL_PROTOCOL_DOMAIN; ?>personal?user_id=', v.user_id)"><img v-bind:src="v.avatar"></div>
                    <div class="info" @click="toanchorroom('<?php echo LOCAL_PROTOCOL_DOMAIN; ?>personal?user_id=', v.user_id)">
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
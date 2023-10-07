<include file="public/head" />
<title>主播PK排位赛</title>
<link rel="stylesheet" href="__H5__/pk_rank/css/index.css?{:date('YmdHis')}">
<script type="text/javascript" src="__H5__/pk_rank/js/index.js?{:date('YmdHis')}"></script>

<script src="__VENDOR__/jquery.min.js?v=__RV__"></script>

<script src="__VENDOR__/vue.js?v=__RV__"></script>
<script src="__VENDOR__/bugujsdk.js?{:date('YmdHis')}"></script>
<script type="text/javascript" src="__H5__/pk_rank/js/pkrank.js?{:date('YmdHis')}"></script>

<script type="text/javascript" src="__VENDOR__/vue-superslide.umd.min.js"></script>
<script>
    var end_time = "{$_info.end_time}";
</script>
</head>
<body>

<script id="v-carousel" type="x/template">
    <superslide data-v-412d8309="" :options="options" class="Carousel" v-if="hot_pk_list.length > 0">
        <div data-v-412d8309="" id="wrapWidth" class="Carouselwrap transition">
            <div data-v-412d8309="" class="pkBox" v-for="(v,index) in hot_pk_list">
                <div data-v-412d8309="" class="pkItem">
                    <h3 data-v-412d8309="" class="title">热门对战</h3>
                    <p data-v-412d8309="" class="itemInfo" @click="gotopersonal(v.left_user.user_id)">
                        <span data-v-412d8309="" class="liveIcon"><span data-v-412d8309=""></span>LIVE</span>
                        <img data-v-412d8309="" v-bind:src="v.left_user.avatar"> <span data-v-412d8309="">{{v.left_user.con_win}}连胜</span>
                    </p>
                    <p data-v-412d8309="" class="itemInfo vsItem"><span data-v-412d8309="" class="vsIcon"></span>
                    </p>
                    <p data-v-412d8309="" class="itemInfo" @click="gotopersonal(v.right_user.user_id)">
                        <span data-v-412d8309="" class="liveIcon"><span data-v-412d8309=""></span>LIVE</span>
                        <img data-v-412d8309="" v-bind:src="v.right_user.avatar"> <span data-v-412d8309="">{{v.right_user.con_win}}连胜</span>
                    </p>
                    <div data-v-412d8309="" class="progressBar">
                        <i data-v-412d8309="" class="leftBar" :style="getBarWidth(v,'left')"></i>
                        <i data-v-412d8309="" class="rightBar" :style="getBarWidth(v,'right')"></i>
                    </div>
                </div>
            </div>
        </div>
        <div data-v-412d8309="" class="carouselArrow">
            <div data-v-412d8309="" class="arrow arrowLeft prev"></div>
            <div data-v-412d8309="" class="arrow arrowRight next"></div>
        </div>
    </superslide>
</script>

<!--
<div id="app">
    <div class="content">
        <div class="gotoRule" @click="gotoRule">玩法说明</div>
        <div class="authorPK">
            <div data-v-47ff4bcf="" class="endTime">
                <div data-v-47ff4bcf="">{{ dayText }}</div>
            </div>
            <div data-v-36e46db3="" class="spokeBox">
                <h3 data-v-36e46db3="" class="title"><span data-v-36e46db3=""></span>PK排位赛代言人<span data-v-36e46db3=""></span></h3>
                <div data-v-36e46db3="" :class="index==0 ? 'item itemOne' : 'item'" v-for="(v,index) in top_three" @click="gotopersonal(v.user_id)">
                    <div data-v-36e46db3="" class="imgBox">
                        <img data-v-36e46db3="" v-bind:src="v.avatar">
                        <span data-v-36e46db3="" :class="'noNumber No'+v.rank"></span>
                        <span data-v-36e46db3="" class="liveIcon" v-if="v.is_live==1"><span data-v-36e46db3=""></span>LIVE</span>
                    </div>
                    <p data-v-36e46db3="">{{v.nickname}}</p>
                    <span data-v-36e46db3="" class="level" :class="'level_' + v.level"></span>
                </div>
            </div>
        </div>

        <carousel></carousel>

        <div data-v-371f2310="" class="pkRank">
            <ul data-v-371f2310="" class="titleBox">
                <li data-v-371f2310="" class="titleItem" :class="type==1 ? 'titleActive' : ''" @click="setType(1)">主播榜<span data-v-371f2310="" class="titleBorder"></span></li>
                <li data-v-371f2310="" class="titleItem" :class="type==2 ? 'titleActive' : ''" @click="setType(2)">粉丝榜<span data-v-371f2310="" class="titleBorder"></span></li>
            </ul>
            <div data-v-371f2310="" class="weekTab" v-if="type==1">
                <span data-v-371f2310="" :class="level==1 ? 'weekActive' : ''" @click="setLevel(1)">青铜</span>
                <span data-v-371f2310="" :class="level==2 ? 'weekActive' : ''" @click="setLevel(2)">白银</span>
                <span data-v-371f2310="" :class="level==3 ? 'weekActive' : ''" @click="setLevel(3)">黄金</span>
                <span data-v-371f2310="" :class="level==4 ? 'weekActive' : ''" @click="setLevel(4)">钻石</span>
                <span data-v-371f2310="" :class="level==5 ? 'weekActive' : ''" @click="setLevel(5)">王者</span>
            </div>
            <ul data-v-371f2310="" class="rankList" v-if="items.length > 0">
                <li data-v-371f2310="" class="rankItem" v-for="(v,index) in items" @click="gotopersonal(v.user_id)">
                    <span data-v-371f2310="" class="order" v-if="v.rank < 4">
                        <i data-v-371f2310=""></i>
                    </span>
                    <span data-v-371f2310="" class="order" v-else>
                        {{v.rank}}
                    </span>
                    <div data-v-371f2310="" class="avatarBox">
                        <img data-v-371f2310="" v-bind:src="v.avatar">
                        <span data-v-371f2310="" class="liveIcon" v-if="v.is_live==1"><span data-v-371f2310=""></span>LIVE</span>
                    </div>
                    <p data-v-371f2310="" class="nickname">{{v.nickname}}</p>
                    <p data-v-371f2310="" class="score">{{v.score}}</p>
                </li>
            </ul>
            <div v-if="items.length == ''">
                <img src="__H5__/images/common/no_resources.png" alt="哈哈" style="display: block;width: 100%;">
            </div>
        </div>

    </div>
</div>
-->
</body>
</html>
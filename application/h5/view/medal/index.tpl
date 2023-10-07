<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <link rel="stylesheet" href="__NEWSTATIC__/h5/medal/css/index.min.css"/>
    <script src="__H5__/js/vue/vue.js" type="text/javascript" charset="utf-8"></script>
    <title>勋章中心</title>
</head>
<body>
<div id="medal">
    <nav class="nav">
        <span class="back"></span>
        <span>勋章中心</span>
        <span class="tip" @click="showPop = true"></span>
    </nav>
    <section class="user-box">
        <img :src="userInfo.avater" alt="avater" class="avater"/>
        <p class="info">{{userInfo.tip}}</p>
    </section>
    <section class="content">
        <div class="tab">
          <span class="tab-item" :class="{active:tabIndex==index}" v-for="(item,index) in tabArray" :key="index" @click="tabIndex = index">{{item}}</span>
        </div>
        <div class="medal-box">
            <div v-for="(item,index) in medalInfo[tabIndex]" :key="index" class="medal-item" @click="goMedalDetail(item)">
                <img :src="item.icon" alt="icon" class="medal-image" :class="{gray:item.finish == 0}"/>
                <p class="tit">{{item.name}}</p>
                <p class="desc">{{item.finish == 1 ? '已点亮' : '尚未点亮'}}</p>
            </div>
        </div>
    </section>
    <!-- 弹出层 -->
    <div id="popLayer" v-show="showPop"></div>
    <div id="popBox" v-show="showPop">
        <div class="close" @click="showPop = false"></div>
        <div class="content">{$desc}</div>
    </div>
</div>
<input  hidden  id="token" value="{$access_token}">
<script>
    new Vue({
        el: "#medal",
        data: {
            userInfo: {},
            tabIndex: 0,
            //tabArray: ["基础勋章", "活动勋章"],
            tabArray: ["基础勋章"],
            medalInfo: {},
            showPop: false,
        },
        created() {
            Object.assign(this.userInfo, {
                avater:
                '{$user_info.avatar}',
                tip: "快来收集第一个勋章吧~",
            });
            this.getMedalInfo();
        },
        methods: {
            /* 获取勋章数据 */
            getMedalInfo() {
                let [_basisInfo, _activityInfo] = [{$list|raw|json_encode}, []];
               /* for (let i = 0; i < 19; i++) {
                    _basisInfo.push({
                        id: i,
                        icon:
                            "https://res.dy-huyu.com/upload/20201204/FvLYv84k9u00QzMnN98srivMpk5a.png?imageView2/1/w/150/h/150",
                        name: "繁华似锦",
                        status: Math.round(Math.random()),
                    });
                }*/
                /*for (let i = 0; i < 8; i++) {
                    _activityInfo.push({
                        id: i,
                        icon:
                            "https://res.dy-huyu.com/upload/20201204/FvLYv84k9u00QzMnN98srivMpk5a.png?imageView2/1/w/150/h/150",
                        detail: "繁华似锦",
                        status: Math.round(Math.random()),
                    });
                }*/
                this.medalInfo = [[..._basisInfo]];
            },

            goMedalDetail({id}) {
                let token = document.getElementById('token');
                window.location.href = `./medal/detail.html?id=${id}&token=` +token.value;
            },
        },
    });
</script>
</body>
</html>

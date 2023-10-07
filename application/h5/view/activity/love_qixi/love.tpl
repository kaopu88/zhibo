<style>

    .love-raise-tab dl{
        width: 50%;
        padding:0;
        text-align: center;
    }

    .fans-group img{
        width: 26%;
        top: 0.09rem;
        border-radius: 50%;
        position: absolute;
    }

    .fans-group img:first-child{
        left: 0.03rem;
        z-index: 9;
    }

    .fans-group img:nth-child(2){
        left: 0.095rem;
        z-index: 8;
    }

    .fans-group img:nth-child(3){
        left: 0.16rem;
        z-index: 7;
    }

    .fans-group img:last-child{
        left: 0.22rem;
        z-index: 6;
    }


    .rank-list{
        margin-top: 0.06rem;
        height: 1.9rem;
        overflow: scroll;
        padding-top: 0.04rem;
    }

    .empty{
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        display: flex;
        justify-items: center;
        align-items: center;
    }
    .user-info{
        margin-top: 0.04rem;
        width: 0.2rem;
    }
    .love-qixi-fans{
        display: flex;
        align-items: center;
        justify-content: space-between;
        color: #fff;
        border-radius: .2rem;
        height: 0.09rem;
    }

    .love-qixi-fans>img:first-child{
        width: 10%;
        margin: 0 0.02rem;
    }

    .love-qixi-fans>img:nth-child(2){
        width: 17%;
        border-radius: 50%;
    }

    .love-qixi-fans p{
        width: 0.15rem;
        text-align: center;
    }

    .heart{
        margin: 0 0.02rem;
        width: 10%;
    }

    .user-info>p:first-child{
        color: rgb(255,47,87);
    }

    .user-info>p:last-child{
        color: rgb(168,23,52);
    }

    .user-info>p{
        font-weight: bold;
        margin: 0.01rem 0;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        text-align: center;
    }


</style>

<div style="position: absolute;top: .8rem;">
    <img src="__H5__/images/activity/love_qixi/time.png" alt="" style="width: 80%;margin: 0 auto;">
</div>

<section class="pot-home" id="app">

    <div class="love-raise-tab">
        <div class="tab-list">
            <dl class="tab_active">
                <dd>
                    <p>七夕真爱榜</p>
                </dd>
            </dl>
        </div>
    </div>
    <div style="position: relative;top: -0.04rem;z-index: 999;">
        <img src="__H5__/images/activity/love_qixi/top.png" alt="" style="width: 82%;margin: 0 auto;">
    </div>

    <div class="pot-home-list">
        <div style="width: 100%;margin: 0 auto;" >
            <ul id="rankList" class="rank-list">
                <li v-for="(item, index) in rankData">
                    <p :class="setAvatar(item.rank)">
                        <em v-if="item.rank == 1">第一名</em>
                        <em v-else-if="item.rank == 2">第二名</em>
                        <em v-else-if="item.rank == 3">第三名</em>
                        <em v-else>{{item.rank}}</em>
                    </p>
                    <div class="love-portrait">
                        <div>
                            <em :class="setRank(index)"></em>
                            <p class="lover-portrait1">
                                <img :src="item.avatar" alt="礼物争夺战" @touchstart="jump(item.uri)">
                                <span v-show="item.is_follow == 0">+关注</span>
                            </p>
                            <div class="p-gift-scramble3-live-icon lover-portrait1-icon" v-show="item.is_living == 1">
                                <div class="p-gift-scramble3-live-icon-circle"><div>
                                </div>
                                    <div></div>
                                    <div></div>
                                </div>
                            </div>
                            <p></p>
                        </div>
                        <div class="user-info">
                            <p>{{item.nickname}}</p>
                            <p>积分:{{item.score}}</p>
                        </div>
                    </div>
                    <div class="heart">
                        <img src="__H5__/images/activity/love_qixi/xin.png" alt="">
                    </div>
                    <div class="p-gift-scramble3-home-list-info" v-if="item.fans.length > 0">
                        <div class="love-qixi-fans" :style="getFansStyle(fansIndex)" v-for="(fansItem, fansIndex) in item.fans">
                            <img :src="getFansRank(fansIndex)" alt="礼物争夺战">
                            <img :src="fansItem.avatar" alt="礼物争夺战" @touchstart="jump(fansItem.uri)">
                            <p>{{fansItem.nickname}}</p>
                            <p>{{fansItem.score}}</p>
                        </div>
                    </div>
                </li>
            </ul>
        </div>
        <div v-if="rankData.length == ''" class="empty">
            <img src="__H5__/images/common/no_resources.png" alt="哈哈">
        </div>
    </div>
    <div style="position: absolute;bottom: 19px;z-index: 999;">
        <img src="__H5__/images/activity/love_qixi/bottom.png" alt="" style="width: 91%;margin-left: 0.02rem;">
    </div>

    <a href="{:url('/activity/loveQixiRule')}" class="pot-jump-btn love-rule"></a>
</section>


<!--<template>
    <div>
    <my-scroll ref="myScroll" :on-refresh="onRefresh" :on-pull="onPull" :get-scroll-top="getTop" :scroll-state="scrollState">
        <div slot="scrollList">
            &lt;!&ndash; 列表 &ndash;&gt;
        </div>
    </my-scroll>
    </div>
</template>-->


<script type="text/javascript">

    //榜单
    var lists = {$rank|raw};

    require(["vue", 'utils', 'axios', '../layer/layer'],function(vue, utils, axios, layer){

//        import myScroll from "/static/vendor/vue-scroll/dist/vue-scroll.vue";

        layer.config({
            path:'/static/vendor/layer/'
        });

        var App = new vue({

            el: '#app',

            data: {
                rankData:lists,
                scrollState: true, //是否可以滑动
                indexScrollTop:0,
                listdata:[]
            },

            //事件
            methods: {
                jump : jump,

                tab:function (id) {
                    var self = this;
                    axios.post('/activity/getLoveQixiData', {
                        p: ++page,
                    }).then(function (response) {

                        if (response.status == 200 && response.data.status == 0)
                        {
                            self.rank = response.data.data;
                            self.active_menu = id;
                            self.template = id == 1 ? anchorTemplate : userTemplate;
                        }
                        else {
                            layer.msg(response.data.message);
                        }

                    }).catch(function (error) {
                        layer.msg(error);
                    });
                },

                setAvatar: function (index) {
                    if (index < 4)
                    {
                        return 'love-rank love-rank'+index;
                    }
                    else {
                        return 'love-rank';
                    }
                },

                setRank: function (index) {
                    return 'lover-crown-'+index;
                },

                getFansRank : function (index) {
                    return '__H5__/images/activity/love_qixi/no'+(index+1)+'.png'
                },

                getFansStyle : function (index) {
                    switch (index)
                    {
                        case 0:
                            return 'background: rgb(255,47,87);';
                            break;
                        case 1:
                            return 'background: rgb(255,128,147);';
                            break;
                        case 2:
                            return 'background: rgb(255,163,200);';
                            break;
                    }
                },

                // 刷新
                onRefresh(mun) {
                    this.listParams.p = 1;
                    this.$axios
                        .get(apiUrl.noticeList, {
                            params: this.listParams,
                            isLoading: false
                        })
                        .then(res => {
                            if (res.code == 10000) {
                                this.listParams.p++;
                                this.listdata = res.data;
                                this.$refs.myScroll.setState(3);
                            } else {
                                this.$refs.myScroll.setState(3);
                            }
                        });
                },

                //加载
                onPull(mun) {
                    this.$axios
                        .get(apiUrl.noticeList, {
                            params: this.listParams,
                            isLoading: false
                        })
                        .then(res => {
                            if (res.code == 10000 && res.data.length > 0) {
                                this.listParams.p++;
                                res.data.map((v, k) => {
                                    this.listdata.push(v);
                                });
                                this.$refs.myScroll.setState(5);
                            } else {
                                this.$refs.myScroll.setState(7);
                            }
                        });
                },

                //滚动条位置
                getTop(y) {
                    this.indexScrollTop = y;
                }
            },

            /*components: {
                myScroll
            }*/

        });

    });

</script>
<style>

    .love-raise-tab dl{
        width: 50%;
        padding:0;
        text-align: center;
    }
    .fans-group{
        display: flex;
        justify-content: left;
        flex-wrap: wrap;
        width: 40%;
        height: 65%;
        border-left: 1px solid #fabce0;
        padding: 0.03rem 0 0 0.03rem;
        position: relative;
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

    .p-gift-scramble3-home-list-info{
        overflow: hidden;
        width: 45%;
        margin-left: .03rem;
    }

</style>

<div style="position: absolute;">
    <img src="__H5__/images/activity/love_raise/bg.png" alt="">
</div>

<section class="pot-home" id="app_love">

    <div class="love-raise-tab" style="margin-top: 0.28rem;">
        <div class="tab-list">
            <dl :class="{tab_active:(1==active_menu)}" @touchstart="tab(1)">
                <dd>
                    <p>主播榜</p>
                </dd>
            </dl>
            <dl :class="{tab_active:(2==active_menu)}" @touchstart="tab(2)">
                <dd>
                    <p>粉丝榜</p>
                </dd>
            </dl>
        </div>
    </div>

    <div class="pot-home-list">
        <div style="width: 100%;margin: 0 auto;">
            <keep-alive>
                <component :is="template" v-bind:rank="rank"></component>
            </keep-alive>
        </div>

        <div v-if="rank.length == ''">
            <img src="__H5__/images/common/no_resources.png" alt="哈哈">
        </div>
    </div>

</section>

<script>

    //榜单
    var ranks = {$ranks|raw|json_encode};

    require(["vue", 'utils', 'axios', '../layer/layer'],function(vue, utils, axios, layer){

        layer.config({
            path:'/static/vendor/layer/'
        });

        const userTemplate = {

            data(){
                return {
                }
            },

            methods:{

                setAvatar: function (index) {
                    return 'lover-rank-'+index;
                },

                setRank: function (index) {
                    return 'lover-crown-'+index;
                },

                jump : jump,

                toFollow : utils.follow,
            },

            props:['rank'],

            template: `<ul class="rank-list" id="rankList" v-if="rank.length">
                            <li v-for="(item, index) in rank">
                                <p class="love-rank"><em :class="setAvatar(index)">{{item.rank}}</em></p>
                                <div class="love-portrait">
                                    <em :class="setRank(index)"></em>
                                    <p class="lover-portrait1"><img :src="item.avatar" alt="礼物争夺战" @touchstart="jump(item.uri)" /><span v-show="item.is_follow == 0" @touchstart="toFollow(item.provider_id)">关注</span></p>
                                <div class="p-gift-scramble3-live-icon lover-portrait1-icon" style="display: none;">
                                    <div class="p-gift-scramble3-live-icon-circle">
                                    <div></div>
                                    <div></div>
                                    <div></div>
                                    </div>
                                    </div>
                                    <p></p>
                                    </div>
                                <div class="p-gift-scramble3-home-list-info">
                                    <p>{{item.nickname}}</p>
                                    <p>总供养能量：{{item.energy}}g</p>
                                </div>
                              </li>
                        </ul>`,
        }


        const anchorTemplate = {

            data(){
                return {
                }
            },

            methods:{

                setAvatar: function (index) {
                    return 'lover-rank-'+index;
                },

                setRank: function (index) {
                    return 'lover-crown-'+index;
                },

                jump : jump,

                toFollow : utils.follow,
            },

            props:['rank'],

            template: `<ul class="rank-list" id="rankList" v-if="rank.length">
                            <li v-for="(item, index) in rank">
                                <p class="love-rank"><em :class="setAvatar(index)">{{item.rank}}</em></p>
                                <div class="love-portrait">
                                    <em :class="setRank(index)"></em>
                                    <p class="lover-portrait1"><img :src="item.avatar" alt="礼物争夺战" @touchstart="jump(item.uri)" /><span v-show="item.is_follow == 0" @touchstart="toFollow(item.provider_id)">关注</span></p>
                                <div class="p-gift-scramble3-live-icon lover-portrait1-icon" style="display: none;">
                                    <div class="p-gift-scramble3-live-icon-circle">
                                    <div></div>
                                    <div></div>
                                    <div></div>
                                    </div>
                                    </div>
                                    <p></p>
                                    </div>
                                <div class="p-gift-scramble3-home-list-info">
                                    <p>{{item.nickname}}</p>
                                    <p>真爱罐数量：{{item.num||0}}个</p>
                                </div>
                                <div class="fans-group">
                                    <p>真爱粉丝团 ></p>
                                    <div>
                                        <img v-for="fans in item.fans_group" :src="fans.avatar" alt="">
                                    </div>
                                </div>
                              </li>
                        </ul>`,
        }

        var App = new vue({

            el: '#app_love',

            data: {
                rank:ranks,
                active_menu : 1,
                template : anchorTemplate,
            },

            //事件
            methods: {
                jump : jump,

                tab:function (id) {
                    var self = this;
                    axios.post('/activity/loveRaiseLove', {
                        type: id==1 ? 'anchor' : 'user',
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
                    return 'rank-'+index;
                },

                setRank: function (index) {
                    return 'crown-'+index;
                }
            },

            //组件
            component : {
                userTemplate,
                anchorTemplate
            },

        });

    });

</script>
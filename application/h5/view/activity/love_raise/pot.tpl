<style>
    body .layui-layer-iframe{
        border-radius: 0.03rem;
    }
</style>

<div class="pot-home-btn">
    <a href="{:url('/activity/loveRaiseLove', ['user_id' => $user_id])}" class="mini-router-link">
        <img style="width: 90%; margin: 0 auto;" src="__H5__/images/activity/love_raise/love.png" alt="">
    </a>
</div>

<div style="position: absolute;">
    <img src="__H5__/images/activity/love_raise/bg.png" alt="">
</div>

<section class="pot-home" id="app">

    <div class="lastday">
        <div class="lastday-portrait">
            <img @touchstart="jump('{$anchor.uri|default=''}')" src="{$anchor.avatar|default='__H5__/images/activity/default_champion.png'}" />
            <div>
                <eq name="$last_day_champion.is_follow" value="0">
                    <div class="lastday-portrait-focus false" @touchstart="toFollow('{$last_day_champion.user_id|default=''}')">
                        关注
                    </div>
                </eq>
            </div>
        </div>
    </div>

    <div class="love-raise-tab">
        <div class="tab-icon"></div>
        <div class="tab-list">
            <dl v-for="(item, index) in menus" :class="{tab_active:(item.id==active_menu)}" @touchstart="tab(item.id)">
                <dd>
                    <p>{{item.name}}</p>
                </dd>
            </dl>
        </div>
    </div>

    <div class="pot-home-list">
        <div style="width: 100%;margin: 0 auto;">
            <keep-alive>
                <component :is="template" v-bind:list="list"></component>
            </keep-alive>
        </div>

        <div v-if="list.length == ''">
            <img src="__H5__/images/common/no_resources.png" alt="哈哈">
        </div>
    </div>

    <a href="{:url('/activity/loveRaiseRule')}" class="pot-jump-btn love-rule"></a>

    <a href="javascript:void(0);" @click="rank()" class="pot-jump-btn love-reply"></a>
</section>


<script>

    //初始数据
    var list = {$list|raw|json_encode};

    //tab数据
    var menu = {$menu|raw|json_encode};

    var user_info = {$user_info|raw|json_encode};

    require(["vue", "utils", "axios", '../layer/layer'],function(vue, utils, axios, layer){

        layer.config({
            path:'/static/vendor/layer/'
        });


        var self = this;

        //申请
        var reply = function ($data, $pot_id) {

            for (var i=0, pot; pot = self.list[i++];)
            {
                if (pot.pot_data.pot_id == $pot_id)
                {
                    pot.pot_status = 1;
                    pot.pot_data.pot_reply = '申请中...';
                }
            }
        };

        var handle = function ($pot_id, $type) {

            axios.post('/activity/'+$type, {
                pot_id: $pot_id,
                user_id: user_info.user_id,
            }).then(function (response) {

                if (response.status == 200 && response.data.status == 0)
                {
                    if(typeof($type) === "string") eval($type+"(response.data, $pot_id)");
                }
                else {
                    layer.msg(response.data.message)
                }

            }).catch(function (error) {
                layer.msg(error);
            });
        };

        const lovePotTemplate = {

            data(){
                return {
                    is_reply : user_info.user_id == user_info.anchor_id ? 0 : 1,
                }
            },

            methods:{
                setAvatar: function (index) {
                    return 'rank-'+index;
                },

                setRank: function (index) {
                    return 'crown-'+index;
                },

                handle : handle,

                jump : jump,

                toFollow : utils.follow,
            },

            props:['list'],

            template: `<ul v-if="list.length" class="pot-list">
                            <li v-for="(item, index) in list">
                                <em class="pot-tag position-absolute"></em>
                                <div class="pot-list-div" :class="{active_raise:(item.pot_status==2)}">
                                    <div class="pot-list-portrait">
                                        <div class="pot-icon">
                                            <img src="__H5__/images/activity/love_raise/pot.png" alt="">
                                            <em v-show="item.pot_status==2" class="energy">{{item.pot_energy||0}}g</em>
                                        </div>
                                    </div>
                                    <div class="pot-list-info">
                                        <p v-if="item.pot_status==2">
                                            <img @touchstart="jump(item.user_info.uri)" :src="item.pot_data.pot_name" alt="">
                                            <span v-show="item.user_info.is_follow == 0" class="follow-user" @touchstart="toFollow(item.user_info.user_id)">关注</span>
                                        </p>
                                        <p v-else>{{item.pot_data.pot_name}}</p>
                                        <p v-if="item.pot_status==0" v-show="is_reply==1" class="pot-reply" @touchstart="handle(item.pot_data.pot_id, 'reply')">{{item.pot_data.pot_reply}}</p>
                                        <p v-else-if="item.pot_status==1" class="pot-reply pot_reply_active">{{item.pot_data.pot_reply}}</p>
                                        <p v-else-if="item.pot_status==2" class="pot-reply-active" style="margin-top: 0.02rem;">{{item.pot_data.pot_reply}}</p>
                                    </div>
                                </div>
                            </li>
                       </ul>`,
        };

        const myPotTemplate = {

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

            props:['list'],

            template: `<ul class="my-list" v-if="list.length">
                            <li v-for="(item, index) in list">
                                <em class="pot-tag position-absolute"></em>
                                <div class="pot-list-div">
                                    <div class="pot-list-portrait">
                                        <div class="pot-icon">
                                            <img :src="item.pot_image" alt="">
                                        </div>
                                    </div>
                                    <div class="pot-list-info">
                                        <p>来自"{{item.nickname}}的表白罐"</p>
                                        <p class="pot-reply">
                                            <span>能量值：{{item.energy}}g</span>
                                            <span>预计衰减：{{item.decay_energy}}g</span>
                                            <span>剩余：{{item.surplus_energy}}g</span>
                                        </p>
                                    </div>
                                </div>
                            </li>
                        </ul>`,
        };

        const rankTemplate = {

            data(){
                return {
                    myPot:[
                        {

                        }
                    ]
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

            props:['list'],

            template: `<ul class="rank-list" v-if="list.length">
                            <li v-for="(item, index) in list">
                                <p class="love-rank"><em :class="setAvatar(index)">{{index+1}}</em></p>
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
                                    <p>供养罐能量：{{item.energy}}g</p>
                                </div>
                              </li>
                        </ul>`,
        }


        var app = new vue({
            el: '#app',

            data: {
                //赛道数据
                menus: menu.data,

                //默认展示主播模板
                template: eval(menu.active_template),

                //默认激活赛道
                active_menu:menu.active,

                //默认展示主播榜数据
                list: list,
            },

            //事件Object.assign
            methods: {

                rank : function () {
                    layer.open({
                        type: 2,
                        title: false,
                        shadeClose: true,
                        closeBtn:false,
                        shade: 0.8,
                        area: ['80%', '70%'],
                        content: "{:url('/activity/replyList', ['user_id' => $user_info.user_id, 'type' => $user_info.type])}"
                    });

                },

                tab: function (menu_id)
                {
                    const self = this;

                    if (user_info.anchor_id == '' || user_info.user_id == '')
                    {
                        layer.msg('缺少相关参数');

                        return false;
                    }

                    //获取指定礼物的周榜数据（包括这个礼物下的金主榜数据）
                    axios.post('/activity/getLoveRaiseData', {
                        menu_id: menu_id,
                        anchor_id : user_info.anchor_id,
                        user_id: user_info.user_id,
                    }).then(function (response) {

                        if (response.status == 200 && response.data.status == 0)
                        {
                            self.template = eval(response.data.data.template);
                            self.list = response.data.data.list;
                            self.active_menu = menu_id;
                        }
                        else {
                            layer.msg(response.data.message);
                        }

                    }).catch(function (error) {
                        layer.msg(error);
                    });
                },

                jump : jump,

                toFollow : utils.follow,

            },

            //组件
            component : {
                lovePotTemplate,
                myPotTemplate,
                rankTemplate
            },

        })

    });


</script>

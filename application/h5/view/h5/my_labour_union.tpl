<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8"/>
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, viewport-fit=cover"/>
    <title>我的{$_agentName}</title>
    <script src="__H5__/js/media_auto.js"></script>
    <link rel="stylesheet" href="__H5__/css/vant/index.css">
    <link rel="stylesheet" type="text/css" href="__H5__/css/mylabourunion/index.css"/>
    <script src="__H5__/js/vue/vue.js" type="text/javascript" charset="utf-8"></script>
    <script src="__H5__/js/vant/vant.min.js" type="text/javascript" charset="utf-8"></script>
    <script src="__NEWSTATIC__/h5/agent/js/axios.min.js"></script>
    <script src="__VENDOR__/layer/mobile/layer.js"></script>
</head>

<body>
<div id="myGuild" v-cloak>
    <van-skeleton avatar avatar-size="3.8108rem" :loading="loading" class="skeleton_img">
        <van-image src="__H5__/images/h5labourunion/icon_guild_application@2x.png" fit="contain" width="3.8108rem"
                   class="icon"></van-image>
    </van-skeleton>
    <van-skeleton :loading="loading" title :row="2" class="skeleton_info">
        <div class="tip">
            <div class="title">
                {{msgtitle}}
            </div>
            <div class="info">
                {{msgdetail}}
            </div>
        </div>
    </van-skeleton>
    <van-skeleton :loading="loading" :row="2" class="skeleton_btn">
        <div class="btn" v-if="is==1">
            <van-button round block type="info" color="#FF3065" class="join" v-on:click="join()">
                我要加入
            </van-button>
            <van-button round block type="default" v-on:click="createLabourUnion()">
                创建{$_agentName}
            </van-button>
        </div>
        <div class="btn" v-else="is==0">
            <van-button round block type="info" color="#FF3065" class="join" v-on:click="enteragent()">
                    进入{$_agentName}
            </van-button>
            <if condition="$exitgent eq 0">
                <van-button round block type="default" v-on:click="exitagent()">
                    退出{$_agentName}
                </van-button>
            </if>
            <if condition="$exitgent eq 1">
                <van-button round block type="default" v-on:click="cancelexitagent()">
                    撤销 退出{$_agentName}
                </van-button>
            </if>
        </div>

    </van-skeleton>
        <input  hidden  id="token" value="{$_info.access_token}">
        <input  hidden  id="msg" value="{$_info.msg}">
        <input  hidden  id="cango" value="{$_info.cango}">
        <input  hidden  id="msgdetail" value="{$_info.msgdetail}">
        <input  hidden  id="is" value="{$_info.is}">
        <input  hidden  id="agent_id" value="{$_info.agent_id}">

        <van-number-keyboard safe-area-inset-bottom></van-number-keyboard>

</div>
</body>

<script type="module">
    import {getUrlKey} from '__H5__/js/gturl.js';

    new Vue({
        el: "#myGuild",
        data() {
            return {
                loading: true,
                value:'',
                cango: 1,
                alertmsg:'',
                msgtitle:'',
                msgdetail:'',
                is:1,
            };
        },
        created(){
           this.init();
        },
        mounted() {
            setTimeout(() => {
                this.loading = false;
            }, 500)
        },
        methods: {
            onClickLeft() {
                window.history.back();
            },
            join: function () {
                if(this.cango==0){
                    this.$toast(this.alertmsg);
                    return false;
                }
                let token = document.getElementById('token');
                let url = '/h5/agent/index.html?token='+token.value;
                this.goaway(url);
            },
            createLabourUnion(){
                if(this.cango==0){
                    this.$toast(this.alertmsg);
                    return false;
                }
                let token = document.getElementById('token');
                let url = '/h5/H5/applyLabourUnion?token='+token.value;
                this.goaway(url);
            },
            enteragent:function()
            {
                let token = document.getElementById('token');
                let agent_id = document.getElementById('agent_id');
                let url = '/h5/agent/detail.html?token='+token.value + '&id=' + agent_id.value;
                this.goaway(url);
            },
            exitagent:function()
            {
                this.$dialog.confirm({
                    title:'退出',
                    message:'你确定退出吗?',
                }) .then(() => {
                    let token = document.getElementById('token');
                    let agent_id = document.getElementById('agent_id');
                    axios({
                        method:'post',
                        url:'/h5/Agent/exitagent',
                        data: {
                            token: token.value,
                            agent_id:agent_id.value
                        }
                    }).then( res => {
                        let code   = res.data.code;
                        this.$toast(res.data.msg);
                        if (code== 1) {

                        } else {
                            window.location.reload()
                        }

                    });
                }).catch(() => {
                 });
            },

            cancelexitagent:function()
            {
                this.$dialog.confirm({
                    title:'退出',
                    message:'你确定撤销退出吗?',
                }) .then(() => {
                    let token = document.getElementById('token');
                    let agent_id = document.getElementById('agent_id');
                    axios({
                        method:'post',
                        url:'/h5/Agent/cancelexitagent',
                        data: {
                            token: token.value,
                            agent_id:agent_id.value
                        }
                    }).then( res => {
                        let code   = res.data.code;
                        this.$toast(res.data.msg);
                        if (code== 1) {

                        } else {
                            window.location.reload()
                        }

                    });
                }).catch(() => {
                });
            },

            change1(){
                let token = document.getElementById('token');
                axios({
                    method:'get',
                    url:'/h5/Agent/checkAgent?token='+token.value,
                }).then( res => {
                    let returncode = res.data.data;
                    let code   = res.data.code;
                    if(code==1){
                        this.cango = 0;
                        this.alertmsg = res.data.msg;
                        this.msgtitle = res.data.msg;
                        this.$toast(res.data.msg);
                    }
                    if(returncode==2&&code==0){
                        let url = '/h5/H5/applyLabourUnionError?token='+token.value+'&msg='+res.data.msg;
                        this.goaway(url);
                    }
                    if(returncode==1){
                        let url = '/h5/H5/applyLabourUnionExamine?token='+token.value;
                        this.goaway(url);
                    }
                    if(returncode==3){
                         let url = '/h5/agent/index.html?token='+token.value;
                        this.goaway(url);
                    }
                        if(res.data.data.id>0){
                            let url = '/h5/H5/applyLabourUnionSuccess?token='+token.value;
                            this.goaway(url);
                        }

                });
            },
            goaway(path) {
                window.location.href=path;
            },
            init(){
                let msg = document.getElementById('msg');
                this.msgtitle = msg.value;
                let cango = document.getElementById('cango');
                this.cango = cango.value;

                let isadd = document.getElementById('is');
                this.is = isadd.value;

                let msgdetail = document.getElementById('msgdetail');
                if(this.cango==1){
                    this.msgdetail = msgdetail.value;
                }
                if(this.cango==0){
                    this.alertmsg = msg.value;
                }
            }

        }
    })

</script>

</html>
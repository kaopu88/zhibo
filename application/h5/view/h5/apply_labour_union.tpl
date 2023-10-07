<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8"/>
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, viewport-fit=cover"/>
    <title>{$_agentName}申请</title>
    <link rel="stylesheet" href="__H5__/css/vant/index.css">
    <link rel="stylesheet" type="text/css" href="__H5__/css/applylabourunion/index.css"/>
    <script src="__H5__/js/media_auto.js"></script>
    <script src="__H5__/js/vue/vue.js" type="text/javascript" charset="utf-8"></script>
    <script src="__H5__/js/vant/vant.min.js" type="text/javascript" charset="utf-8"></script>
    <script src="__NEWSTATIC__/h5/agent/js/axios.min.js"></script>
    <script src="__VENDOR__/jquery.min.js?v=__RV__"></script>
    <script src="https://unpkg.com/qiniu-js@2.5.5/dist/qiniu.min.js"></script>


</head>

<body>
<div id="guildApply" v-cloak>
    <van-skeleton title avatar :row="3" :loading="loading">

        <van-form @submit="onSubmit" style="margin-top: 0.2703rem;">
            <van-field v-model="guild_name" name="guild_name" label="{$_agentName}名称" placeholder="请输入您要创建的{$_agentName}名称"
                       :rules="[{ required: true, message: '请填写{$_agentName}名称' }]" :border="false"></van-field>
            <van-field v-model="user_name" name="username" label="个人姓名" placeholder="请输入真实姓名"
                       :rules="[{ required: true, message: '请填写个人姓名' }]" :border="false"></van-field>
            <van-field v-model="id_number" type="text" name="idcard" label="身份证号" placeholder="请输入15或18位身份证号"
                       :rules="[{ validator, message: '请填写正确的身份证号' }]" maxlength="18" :border="false"></van-field>

            <van-field v-model="password" name="passwordVer" type="password" label="登录密码" placeholder="请输入密码"
                       :rules="[{ pattern, required: true, message: '密码必须是含有字母和数字的8位字符' }]" :border="false"></van-field>
            <van-grid :column-num="2" :border="false" class="card">
                <van-grid-item v-for="(item,index) in cardList" :key="index" class="card_item" @click="getIndex(index)">

                        <van-image :src="item.img" fit="contain" width="100%"></van-image>

                    <p>{{item.info}}</p>
                </van-grid-item>
            </van-grid>
            <div style="margin: 0.4324rem;margin-top: 1.7297rem;">
                <van-button round block type="info" native-type="submit" color="#FF3065">
                    提交申请
                </van-button>
            </div>
        </van-form>

        <van-number-keyboard safe-area-inset-bottom></van-number-keyboard>
    </van-skeleton>
</div>
</body>
<script type="module">
    import {getUrlKey} from '__H5__/js/gturl.js';
    new Vue({
        el: "#guildApply",
        data() {
            return {
                loading: false,
                guild_name: '',
                user_name: '',
                id_number: '',
                family_introduction: '',
                password: '',
                cardList: [
                    {
                        img: '__H5__/images/h5labourunion/image_focused_txm@2x.png',
                        info: '身份证头像面'
                    },
                    {
                        img: '__H5__/images/h5labourunion/image_focused_ghm@2x.png',
                        info: '身份证国徽面'
                    },
                    {
                        img: '__H5__/images/h5labourunion/image_focused_yyzz@2x.png',
                        info: '{$_agentName}背景图片'
                    }
                ],
                imgIndex: "",
                pattern: /(?=.*\d)(?=.*[A-z])^[0-9A-z]{8,}$/
            };
        },
        mounted() {
            let _this= this;
            window.h5ReceivePictures = function (res) {
                _this.cardList[res.index].img = res.image;
            }
        },
        methods: {
            onClickLeft() {
                console.log('点击了');
            },
            onSubmit(values) {
                let token = getUrlKey("token", window.location.href);
                let data = values;
                let url = window.location.protocol + "//" + window.location.host + '/h5/Agent/applyCreateAgent'
                let data1 = {"name":values.guild_name,
                        "legal_name":values.username,
                        "legal_id":values.idcard,
                        "temppass":values.passwordVer,
                        "logo":this.cardList[2].img,
                        "img_idcardP":this.cardList[0].img,
                        "img_idcardB":this.cardList[1].img,
                        "token":token};
                axios.post(url, data1)
                    .then(res => {
                        if (res.data.code == 1) {
                            this.$toast(res.data.msg);
                        }
                        if (res.data.code == 0) {
                            this.$toast(res.data.msg);
                            let url = '/h5/H5/applyLabourUnionExamine?token=' + token;
                            this.goaway(url);
                        }
                    })

            },
            // 身份证号码校验
            validator(val) {
                return /^(^[1-9]\d{7}((0\d)|(1[0-2]))(([0|1|2]\d)|3[0-1])\d{3}$)|(^[1-9]\d{5}[1-9]\d{3}((0\d)|(1[0-2]))(([0|1|2]\d)|3[0-1])((\d{4})|\d{3}[Xx])$)$/.test(val);
            },
            passwordVer(val) {
                return /^\d{8,}$/.test(val);
            },
            afterRead(file) {
                this.token(file.file.name).then(val => {
                    let token = val.data.token;
                    let config = {
                        useCdnDomain: true,
                        region: qiniu.region.z0
                    };
                    let putExtra = {
                        fname: file.file.name,
                        params: {key: val.data.key},
                        mimeType: ['image/png', 'image/jpeg', 'image/gif'],
                    };
                    let files = file.file;
                    let key = val.data.key;
                    let subscription;
                    // 调用sdk上传接口获得相应的observable，控制上传和暂停
                    let observable = qiniu.upload(files, key, token, putExtra, config);
                    let observer = {
                        next(result) {                        //上传中(result参数带有total字段的 object，包含loaded、total、percent三个属性)
                            let total = result.total;
                            $(".speed").text("进度：" + total.percent + "% ");//查看进度[loaded:已上传大小(字节);total:本次上传总大小;percent:当前上传进度(0-100)]
                        },
                        error(err) {                          //失败后
                            alert(err.message);
                        },
                        complete: (res) => {
                            console.log(res);                   //成功后
                            this.cardList[this.imgIndex].img = val.data.url;
                            // ?imageView2/2/h/100：展示缩略图，不加显示原图
                            // ?vframe/jpg/offset/0/w/480/h/360：用于获取视频截图的后缀，0：秒，w：宽，h：高
                        }
                    }
                    observable.subscribe(observer)
                });

            },
            token(name) {
                return new Promise((resolve, reject) => {
                    //你的逻辑代码
                    let token = getUrlKey("token", window.location.href);
                    axios({
                        method: 'get',
                        url: window.location.protocol + "//" + window.location.host + '/api.php?s=Common.getQiniuToken?access_token=' + token + '&type=friend_images&filename=' + name,
                    }).then(function (res) {
                        resolve(res.data)
                    }).catch(function (error) {
                        console.log(error);
                    });

                });

            },
            getIndex(index) {
                this.imgIndex = index;
                if(this.getPlatform()=='android'){
                    result = window.WebViewJavascriptBridge.createGuildPullImage(index.toString());
                }
                if(this.getPlatform()=='ios'){
                    try {
                        window.webkit.messageHandlers.createGuildPullImage.postMessage(index.toString())
                    }
                    catch(err){

                    }

                }

            },
            goaway(path) {
                window.location.href = path;
            },
            getPlatform() {
                var u = navigator.userAgent;
                if (u.match(/dsbrowser_ios/i) == 'dsbrowser_ios') {
                    return 'ios';
                } else if (u.match(/dsbrowser_android/i) == 'dsbrowser_android') {
                    return 'android';
                }
                return '';
            }
        }
    })
</script>
</html>
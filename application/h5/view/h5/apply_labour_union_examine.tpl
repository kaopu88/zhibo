<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8" />
  <meta name="viewport"
    content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, viewport-fit=cover" />
  <title>审核</title>
  <link rel="stylesheet" href="__H5__/css/vant/index.css">
  <link rel="stylesheet" type="text/css" href="__H5__/css/applylabourunionexamine/index.css"/>
  <script src="__H5__/js/media_auto.js"></script>
  <script src="__H5__/js/vue/vue.js" type="text/javascript" charset="utf-8"></script>
  <script src="__H5__/js/vant/vant.min.js" type="text/javascript" charset="utf-8"></script>
  <script src="__NEWSTATIC__/h5/agent/js/axios.min.js"></script>
</head>

<body>
  <div id="auditIng" v-cloak>
    <van-skeleton title avatar :row="3" :loading="loading">

      <img src="__H5__/images/h5labourunion/icon_befault_shz@2x.png" class="icon">
      <div class="tip">
        <div class="title">
          审核中...
        </div>
        <div class="info">
          提交成功，请等待审核
        </div>
      </div>
      <div class="know-btn" v-on:click="refresh()">
        刷新
      </div>
      <van-number-keyboard safe-area-inset-bottom></van-number-keyboard>
    </van-skeleton>
  </div>
</body>

<script type="module">
  import {getUrlKey} from '__H5__/js/gturl.js';
  new Vue({
    el: "#auditIng",
    data() {
      return {
        loading: false,
      };
    },
    methods: {
      onClickLeft() {
        window.history.back();
      },
      refresh(){
        let token = getUrlKey("token",window.location.href);
        axios({
          method:'get',
          url:'/h5/Agent/checkAgent?token='+token,
        }).then( res => {
         if(res.data.code!=0){
           this.$toast('访问出错');
         };
          if(res.data.data==2){
            let url = '/h5/H5/applyLabourUnionError?token='+token+'&msg='+res.data.msg;
            this.goaway(url);
          }
          if(res.data.data.id>0){
            let url = '/h5/H5/applyLabourUnionSuccess?token='+token;
            this.goaway(url);
          }
        });
      },
      goaway(path) {
        window.location.href=path;
      }
    }
  })
</script>

</html>
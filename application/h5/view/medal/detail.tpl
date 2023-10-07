<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="__NEWSTATIC__/h5/medal/css/detail.min.css"/>
    <script src="__H5__/js/vue/vue.js" type="text/javascript" charset="utf-8"></script>
    <title>勋章中心</title>
  </head>
  <body>
    <div id="medal-detail">
      <nav class="nav">
        <span class="back" @click="back"></span>
        <span></span>
      </nav>
      <div class="medal-box">
        <img
          src="{$res.icon}"
          alt="medal_url"
          class="medal-image"
          :class="{gray:status == 0}"
        />
        <p class="tit">{$res.name}</p>
        <p class="desc">{$res.description}</p>
        <div class="last-light"><if condition=" $res.finish== 1 ">已点亮<else /> 尚未点亮，等你来取~</if></div>
      </div>
    </div>
    <script>
      new Vue({
        el: "#medal-detail",
        data: {
          status: {$res.finish},
        },
        created() {
          this.id = this.getQueryVariable("id");
        },
        methods: {
          getQueryVariable(variable) {
            let query = window.location.href;
            let vars = query.replace("?", "?&").split("&");
            for (let i = 0; i < vars.length; i++) {
              let pair = vars[i].split("=");
              if (pair[0] == variable) {
                return pair[1];
              }
            }
            return false;
          },
          back() {
            window.history.back();
          },
        },
      });
    </script>
  </body>
</html>

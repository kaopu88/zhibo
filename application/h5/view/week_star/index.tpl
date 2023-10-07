<include file="public/head" />
<title>周星礼物活动</title>
<link rel="stylesheet" href="__NEWSTATIC__/h5/week_star/css/swiper-bundle.min.css">
<link rel="stylesheet" href="__NEWSTATIC__/h5/week_star/css/weekStar.min.css">
<script src="__NEWSTATIC__/jquery.min.js"></script>
<script src="__NEWSTATIC__/media_auto.js"></script>
</head>
<body>
  <div class="container">
    <div class="bg_wall">
      <header class="weekStar"></header>
    </div>
    <div class="last_week">
      <div class="notice">
        <!-- Swiper -->
        <div class="swiper-container-last">
          <div class="swiper-wrapper">
            <notempty name="list['last']">
              <volist name="list['last']" id='vo'>
                <div class="swiper-slide user" data-id="{$vo.gift_id}">
                  <div class="icon" style="background: url({$vo.picture_url});background-size: contain;"></div>
                  <div class="name">{$vo.name}</div>
                </div>
              </volist>
            </notempty>
          </div>
        </div>
        <!-- Add Arrows -->
        <div class="left"></div>
        <div class="right"></div>
      </div>
      <div class="week_anchor">
        <div class="title"></div>
        <div class="ranking">

        </div>
      </div>
      <div class="week_rich">
        <div class="title"></div>
        <div class="ranking">

        </div>
      </div>
    </div>
    <div class="this_week">
      <!-- Swiper -->
      <div class="swiper-container">
        <div class="swiper-wrapper">
          <notempty name="list['now']">
            <volist name="list['now']" id='vo'>
              <div class="swiper-slide" data-id="{$vo.gift_id}">
                <img src="{$vo.picture_url}" alt="">
                <p>{$vo.name}</p>
              </div>
            </volist>
          </notempty>
        </div>
      </div>
      <!-- Add Arrows -->
      <div class="swiper-button-next"></div>
      <div class="swiper-button-prev"></div>
    </div>
    <div class="week_star">
      <div class="header">
        <div class="week_anchor active"></div>
        <div class="week_rich"></div>
        <div class="active_rule"></div>
      </div>
      <!-- 周星主播内容 -->
      <div class="week_anchor_content active">

      </div>
      <!-- 周星富豪内容 -->
      <div class="week_rich_content">

      </div>
      <!-- 活动规则 -->
      <div class="active_rule_content">
        <div class="rule">
          <div class="icon"></div>
          <div class="content"></div>
        </div>
        <div class="award">
          <div class="icon"></div>
          <div class="content">
            <div class="title">
              <div>奖励</div>
              <div <if condition="$rank_reward_status neq 1"> style = 'display:none;'</if> >周星主播</div>
              <div  <if condition="$rich_reward_status neq 1"> style = 'display:none;'</if>>周星富豪</div>
            </div>
            <div>
              <div class="font_yellow">第一名</div>
              <div class="anchor_reward"  <if condition="$rank_reward_status neq 1"> style = 'display:none;'</if>></div>
              <div class="rich_reward"  <if condition="$rich_reward_status neq 1"> style = 'display:none;'</if>></div>
            </div>
            <div>
              <div class="font_yellow">第二名</div>
              <div class="anchor_reward"  <if condition="$rank_reward_status neq 1"> style = 'display:none;'</if>></div>
              <div class="rich_reward"  <if condition="$rich_reward_status neq 1"> style = 'display:none;'</if>></div>
            </div>
            <div>
              <div class="font_yellow">第三名</div>
              <div class="anchor_reward"  <if condition="$rank_reward_status neq 1"> style = 'display:none;'</if>></div>
              <div class="rich_reward"  <if condition="$rich_reward_status neq 1"> style = 'display:none;'</if>></div>
            </div>
          </div>
        </div>
      </div>
    </div>

  <div id="tip" style="position: absolute; width: 8.56rem; height: 1.52667rem; z-index: 999; background: #FFFFFF;border-radius: 0.13333rem;display: none; left: 27px;top: 250.5px;">
    <div id="tipmessage" style="font-size: 0.42667rem; color: #8C8C8C;letter-spacing: 0;padding-top: 0.46667rem;text-align: center;">未知错误</div>
  </div>

    <script src="__NEWSTATIC__/h5/week_star/js/swiper-bundle.min.js"></script>
    <script src="__NEWSTATIC__/h5/week_star/js/weekStar.js"></script>
</body>
<script>
  var type=1;
  getWeekRank();
  getLastWeekRank();

  function getWeekRank() {
      var _index = swiper.activeIndex;
      var id = $(".swiper-container .swiper-slide").eq(_index).attr("data-id");
      var gift_name = $(".swiper-container .swiper-slide > p").eq(_index).text();
      getRankList(id, gift_name);
  }

  function getLastWeekRank() {
      var _index = swiper_last.activeIndex;
      var id = $(".swiper-container-last .swiper-slide").eq(_index).attr("data-id");
      getLastRank(id);
  }

  function getRankList(id, gift_name) {
      $.ajax({
          type: 'post',
          url: '{:url("week_star/getGiftRank")}',
          data: {gift_id : id, type: type},
          success: function(result){
              if(result.status == 1){
                  var html = "";
                  var list = result.data;
                  if (type != 3) {
                      for (var i = 0; i < list.length; i++) {
                          html += '<div>';
                          if (i < 3) {
                              html += '<div class="icon"></div>';
                          } else {
                              html += '<div class="font">' + (i + 1) + '</div>';
                          }
                          html += '<div class="via" style="background: url(' + list[i].avatar + ');background-size: contain;"></div>';
                          html += '<div class="explain">';
                          html += '<p>' + list[i].username + '</p>';
                          if (type == 1) {
                              html += '<p class="hint">收到' + list[i].gift_num + '个' + gift_name + '</p>';
                          } else {
                              html += '<p class="hint">贡献' + list[i].gift_num + '个' + gift_name + '</p>';
                          }
                          html += '</div>';
                          html += '</div>';
                      }
                      if (type == 1) {
                          $(".week_anchor_content").html(html);
                      } else {
                          $(".week_rich_content").html(html);
                      }
                  } else {
                      var html = '<div class="icon"></div><div class="content">'+ result.data.rule + '</div>' ;
                      $(".active_rule_content > .rule").html(html);
                      var alist = result.data.anchor_reward;
                      for (var i = 0; i < alist.length; i++) {
                          $(".anchor_reward").eq(i).html(alist[i]);
                      }
                      var rlist = result.data.rich_reward;
                      for (var i = 0; i < alist.length; i++) {
                          $(".rich_reward").eq(i).html(rlist[i]);
                      }
                  }
              } else {
                $('#tipmessage').html(result.msg);
                $("#tip").fadeIn();
                $("#tip").fadeOut(1500);
              }
          }
      })
  }

  function getLastRank(id){
      $.ajax({
          type: 'post',
          url: '{:url("week_star/getLastRank")}',
          data: {gift_id : id},
          success: function(result){
              if(result.status == 1){
                  var html = "";
                  var alist = result.data.anchor;
                  if(alist.length > 0) {
                      for (var i = 0; i < alist.length; i++) {
                          html += '<div>';
                          html += '<div class="title">NO.' + (i + 1) + '</div>';
                          html += '<div class="frist_icon" style="background: url(' + alist[i].avatar + ');background-size: contain;"></div>';
                          html += '<div class="name">' + alist[i].nickname + '</div>';
                          html += '<div class="receive">收到' + alist[i].gift_num + '个</div>';
                          html += '</div>';
                      }
                  }else{
                      html += '<p class="no_data active">暂无排行</p>';
                  }
                  $(".week_anchor > .ranking").html(html);

                  var rhtml = "";
                  var rlist = result.data.rich;
                  if(rlist.length > 0) {
                      for (var i = 0; i < rlist.length; i++) {
                          rhtml += '<div>';
                          rhtml += '<div class="title">NO.' + (i + 1) + '</div>';
                          rhtml += '<div class="frist_icon" style="background: url(' + rlist[i].avatar + ');background-size: contain;"></div>';
                          rhtml += '<div class="name">' + rlist[i].nickname + '</div>';
                          rhtml += '<div class="receive">贡献' + rlist[i].gift_num + '个</div>';
                          rhtml += '</div>';
                      }
                  }else{
                      rhtml += '<p class="no_data active">暂无排行</p>';
                  }
                  $(".week_rich > .ranking").html(rhtml);
              }
          }
      })
  }

</script>
</html>
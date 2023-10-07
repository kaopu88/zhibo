<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport"
    content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, viewport-fit=cover" />
  <link rel="stylesheet" href="__NEWSTATIC__/h5/agent/style/index.min.css">
  <script src="__VENDOR__/jquery.min.js?v=__RV__"></script>
  <script src="__NEWSTATIC__/h5/agent/js/media_auto.js"></script>
  <title>{$agent_name}详情</title>
</head>
<body>
  <div class="container-detail">
    <header></header>
    <div class="recommend_guild">
      <div class="guild_list">
        <div class="guild">
          <div class="guild-img" style="background-image:url( {:img_url($oneRes['logo'],'200_200','logo')}) ;background-size:1.97333rem 1.97333rem;"></div>
          <div class="guild-info">
            <div class="title">{$oneRes.name}</div>
            <div class="chairman-name">{$agent_name}管理：{$info.username}</div>
            <div class="chairman-id">{$agent_name}ID：{$oneRes.id}</div>
          </div>
        </div>
        <if condition=" $apply_status == 1 ">
          <div class="guild-btn-apply">申请中</div>
          <elseif condition="$apply_status == 2" />
          <div class="guild-btn-apply">已加入</div>
          <else />
          <div class="guild-btn" onclick="apply({$oneRes.id}, this)">申请</div>
        </if>
      </div>
      <div class="info">{$oneRes.remark}</div>
    </div>

    <div class="bigShotList">
      <div class="title">大咖主播</div>
      <div class="bigShot">
      </div>

      <div class="live">
        <div class="liveList">
        </div>
      </div>
    </div>
  </div>

  <div id="tip" style="position: absolute; width: 8.56rem; height: 1.52667rem; z-index: 999; background: #FFFFFF;border-radius: 0.13333rem;display: none; left: 27px;top: 250.5px;">
    <div id="tipmessage" style="font-size: 0.42667rem; color: #8C8C8C;letter-spacing: 0;padding-top: 0.46667rem;text-align: center;">最多只能申请一个{$agent_name}</div>
  </div>

  <script>
      $.getUrlParam = function (name) {
          var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)");
          var r = window.location.search.substr(1).match(reg);
          if (r != null)
              return decodeURI(r[2]); // decodeURI(r[2]); 解决参数是中文时的乱码问题

          return null;
      }

      var mytoken = $.getUrlParam("token");
      var id = $.getUrlParam("id");

      page = 1;
      is_loading = true;
      is_append = false;
      is_finish = false;
      keyword = '';

      loading_list(page, is_append, is_loading, keyword);

      $(window).scroll(function () {
          var scrollTop = $(this).scrollTop();
          var scrollHeight = $(document).height();
          var windowHeight = $(this).height();
          if (scrollTop + windowHeight == scrollHeight) {
              if (is_finish) {
                  return;
              }
              page++;
              is_append = true;
              loading_list(page, is_append, is_loading, keyword)
          }
      });

      function loading_list(page, is_append, is_loading, keyword) {
          var pageSize = 10;
          var html_one = '';
          var html_two = '';
          if (is_loading) {
              if (is_loading) {
                  $.ajax({
                      url: '/h5/Agent/get_anchor_list',
                      type: 'POST',
                      dataType: 'json',
                      data: {'token': mytoken, 'id': id, 'page': page},
                      success: function (res) {
                          if (res.status == 0) {
                              var appjson = res.data;
                              if (appjson != "" || appjson != null) {
                                  for (var i = 0; i<appjson.length; i++) {
                                      var  sexstyle = 'class="sex"';
                                      var  livesexstyle = 'class="liveSex"';
                                      if (appjson[i].gender == '1') {
                                          sexstyle = 'class="malesex"';
                                          livesexstyle = 'class="livemaleSex"';
                                      }
                                      if (page == 1 && i<3) {
                                          html_one+= ' <div class="bigShotInfo">' +
                                              '          <div class="bigShotImg" style="background-image:url( '+appjson[i].avatar+');background-size:1.65333rem 1.65333rem;"></div>' +
                                              '          <div class="bigShotName">'+appjson[i].username+'</div>' +
                                              '          <div class="icon">' +
                                              '            <div '+sexstyle+'></div>' +
                                              '            <div class="star"  style="background-image:url( '+appjson[i].level_icon+');"></div>' +
                                              '          </div>' +
                                              '          <div class="getJewel">共收益</div>' +
                                              '          <div class="num"><span class="red">'+appjson[i].his_millet+'</span>{:APP_MILLET_NAME}</div>' +
                                              '        </div>';
                                      } else {
                                          var htmllive;
                                          if (appjson[i].is_live == 1) {
                                              htmllive = '<div class="liveBtn">直播中</div>';
                                          } else  {
                                              htmllive = '<div class="liveBtn-rest">休息中</div>';
                                          }
                                          html_two+= '<div class="live-row">' +
                                              '       ' +
                                              '            <div class="liveImg" style="background-image:url( '+appjson[i].avatar+');background-size:1.33333rem 1.33333rem;"></div>' +
                                              '            <div class="liveInfo">' +
                                              '              <div class="liveTitle" >' +
                                              '                <p style="overflow:hidden;text-overflow: ellipsis;white-space: nowrap">'+appjson[i].username+'</p>' +
                                              '                <div '+livesexstyle+' style="flex-shrink: 0;"></div>' +
                                              '                <div class="liveStar"  style="background-image:url( '+appjson[i].level_icon+');flex-shrink: 0;"></div>' +
                                              '              </div>' +
                                              '              <div class="getLiveJewel">共收到 <span class="red">'+appjson[i].his_millet+'</span> {:APP_MILLET_NAME}</div>' +
                                              '            </div>' + htmllive +
                                              '          </div>';
                                      }
                                  }
                                  $(".bigShot").append(html_one);
                                  $(".liveList").append(html_two);

                                  if (appjson.length - pageSize < 0) {
                                      is_loading = false;
                                      is_finish = true;
                                  }
                              } else {
                                  is_loading = false;
                                  is_finish = true;
                              }
                          } else {
                              return false;
                          }
                          return false;
                      }
                  })
              }
          }
      }

      function apply(id, ele) {
          $.ajax({
              url: '/h5/Agent/applyAgent',
              type: 'POST',
              dataType: 'json',
              data: {'token': mytoken, 'agent_id': id},
              success: function (res) {
                  $('#tipmessage').html(res.message)
                  $("#tip").fadeIn();
                  if (res.status == 0) {
                      $(ele).removeClass('guild-btn').addClass('guild-btn-apply').text('申请中');
                  } else {
                  }
                  $("#tip").fadeOut(1500);
              }
          })
      }
  </script>
</body>
</html>
<?php /*a:1:{s:53:"/www/wwwroot/zhibb/application/h5/view/team/index.tpl";i:1606127574;}*/ ?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no"/>
  <link rel="stylesheet" href="/static/h5/css/vant/index.css">
  <link rel="stylesheet" href="/bx_static/h5/team/style/index.min.css">
  <script src="/static/vendor/jquery.min.js?v=<?php echo config('upload.resource_version'); ?>"></script>
  <script src="/bx_static/h5/agent/js/media_auto.js"></script>
  <script src="/bx_static/h5/team/js/index.js"></script>
  <script src="/bx_static/layui.js"></script>
  <title>我的团队</title>
</head>
<style>
  *{
    -webkit-touch-callout:none; /*系统默认菜单被禁用*/
    -webkit-user-select:none; /*webkit浏览器*/
    -khtml-user-select:none; /*早期浏览器*/
    -moz-user-select:none;/*火狐*/
    -ms-user-select:none; /*IE10*/
    user-select:none;
  }
  input {
    -webkit-user-select:auto; /*webkit浏览器*/
  }
</style>

<body>
  <div class="container" id="Tim">
    <div class="bgGray"></div>
    <div class="setting_wall"></div>

    <div class="content">
      <nav>
        <header id="userInfo">
          <div class="icon"></div>
          <div class="user_name_img" style="background-image:url(<?php echo htmlentities($image_url); ?>) ;background-size:1.28rem 1.28rem;"></div>
          <div class="info">
            <p><?php echo htmlentities($partname); ?></p>
            <p>您的推荐人</p>
          </div>
        </header>

        <div>
          <div></div>
          <div class="title my_team_title"></div>
          <div class="search_input">
            <div class="icon searchuser"></div>
            <form id="myform" action="#" onsubmit="return false;">
              <input type="search" id="generalSearch" name=""  placeholder="请输入昵称或手机号查询">
            </form>
          </div>
        </div>


        <div class="search" id="isSearch"></div>
      </nav>

      <div class="main">

        <div class="tabs">
          <div class="item active">
            <p><?php echo htmlentities($zt_son); ?></p>
            <p class="footer_text">下级粉丝</p>
          </div>
          <div class="item">
            <p><?php echo htmlentities($zt_all_son); ?></p>
            <p class="footer_text">全部</p>
          </div>
        </div>

        <div class="tabs_content_item active">
          <div class="info">
            <div>
              <p><?php echo htmlentities($today_total); ?></p>
              <p>今日新增</p>
            </div>
            <div>
              <p><?php echo htmlentities($yesterday_total); ?></p>
              <p>昨日新增</p>
            </div>
            <div>
              <p><?php echo htmlentities($two_son); ?></p>
              <p>二代粉丝</p>
            </div>
            <div>
              <p><?php echo htmlentities($two_out_son); ?></p>
              <p>二代以后</p>
            </div>
          </div>
          <div class="list" id="first">

          </div>
        </div>

        <div class="tabs_content_item">
          <div class="info" id="newAdd">
            <div>
              <p><?php echo htmlentities($today_total); ?></p>
              <p>今日新增</p>
            </div>
            <div>
              <p><?php echo htmlentities($yesterday_total); ?></p>
              <p>昨日新增</p>
            </div>
            <div>
              <p><?php echo htmlentities($two_son); ?></p>
              <p>二代粉丝</p>
            </div>
            <div>
              <p><?php echo htmlentities($two_out_son); ?></p>
              <p>二代以后</p>
            </div>
          </div>

          <div class="list" id="all">

          </div>
        </div>
      </div>
    </div>
  </div>
</body>

</html>
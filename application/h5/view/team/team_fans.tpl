<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no"/>
  <link rel="stylesheet" href="__NEWSTATIC__/h5/team/style/index.min.css">
  <script src="__VENDOR__/jquery.min.js?v=__RV__"></script>
  <script src="__NEWSTATIC__/h5/agent/js/media_auto.js"></script>
  <script src="__NEWSTATIC__/h5/team/js/team.js"></script>
  <script src="/bx_static/layui.js"></script>
  <script src="/bx_static/clipboard.min.js"></script>

  <title>团队粉丝</title>
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
  <div class="container">
    <div class="bgGray"></div>
    <div class="setting_wall"></div>
    <nav>
      <div></div>
      <div class="title my_team_title"></div>
      <div class="search_input">
        <div class="icon"></div>
        <form id="myform" action="#" onsubmit="return false;">
          <input type="search" id="generalSearch" name=""  placeholder="请输入昵称或手机号查询">
        </form>
      </div>
      <div class="search" id="isSearch"></div>
    </nav>
    <div class="content fans">
      <header>
        <div class="item">
          <div class="user_img"  style="background-image:url({$image_url}) ;background-size:1.28rem 1.28rem;"></div>
          <div class="user_info">
            <div class="title">
              <div class="name">{$username}</div>
            </div>
            <div class="join_tiem">
              加入时间 : {$create_time}
            </div>
          </div>
          <div class="user_count">
            <div><span>下级粉丝</span><span>{$zt_son}</span></div>
          </div>
        </div>
      </header>
      <div class="main">
        <div class="user_profile">
          <div>
            <p class="phone">手机号</p>
            <p class="phone_num copy"><span class="code">{$phone}</span></p>
          </div>
          <div>
            <p class="wechat">微信</p>
            <p>{$wxbind}</p>
          </div>
        </div>

       <!-- <div class="tabs" >
          <div class="item itemall active">
            <p class="taoke">淘客</p>
          </div>
          <div class="item itemall">
            <p>B2B2C</p>
          </div>
        </div> -->
        
        <div class="tabs_content_item active">
          <div class="estimate" style="display: none">
            <div>
              <p>今日预估(元)</p>
              <p>0</p>
            </div>
            <div>
              <p>本月预估(元)</p>
              <p>0</p>
            </div>
            <div>
              <p>上月结算(元)</p>
              <p>0</p>
            </div>
          </div>
        </div>

        <div class="tabs_content_item">
          <div class="estimate">
            <div>
              <p>今日获得(元)</p>
              <p>0</p>
            </div>
            <div>
              <p>本月获得(元)</p>
              <p>0</p>
            </div>
            <div>
              <p>上月结算(元)</p>
              <p>0</p>
            </div>
          </div>
        </div>

        <div class="tabs_content_item active">
          <div class="info" id="newAdd">
            <div>
              <p>{$today_total}</p>
              <p>今日新增</p>
            </div>
            <div>
              <p>{$yesterday_total}</p>
              <p>昨日新增</p>
            </div>
            <div>
              <p>{$two_son}</p>
              <p>二代粉丝</p>
            </div>
            <div>
              <p>{$two_out_son}</p>
              <p>二代以后</p>
            </div>
          </div>

          <div class="list fans" id="list_fans">
          </div>
        </div>

      </div>

    </div>
  </div>
</body>


</html>
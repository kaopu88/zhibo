 <!--
 * @Descripttion: 
 * @version: 
 * @Author: sueRimn
 * @Date: 2020-07-07 13:27:36
 * @LastEditors: sueRimn
 * @LastEditTime: 2020-07-07 14:55:52
-->
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta http-equiv="x-dns-prefetch-control" content="on" />
    <meta name="renderer" content="webkit" />
    <meta name="applicable-device" content="pc" />
    <meta http-equiv="X-UA-Compatible" content="IE=Edge" />
    <meta name="application-name" content="" />
    <meta name="renderer" content="webkit" />
    <meta name="format-detection" content="telephone=no" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, minimum-scale=1, user-scalable=no, viewport-fit=cover" />
    <meta name="keywords" content=""/>
    <meta name="description" content=""/>
    <link rel="stylesheet" href="/bx_static/layui.css" />
    <link rel="stylesheet" href="/bx_static/record.css" />
    <script src="/bx_static/layui.js"></script>
    <script src="/bx_static/media_auto.js"></script>
    <title>兑换记录</title>
  </head>
  <body>
    <div class="container exchange_list">
      <header class="mui-bar mui-bar-nav">
        <a class="mui-icon">
          <img src="/bx_static/admin/assets/icon_befault_gb@3x.png" />
        </a>
        <h1 class="mui-title">兑换记录</h1>
      </header>

      <div class="taskHD">
        <div class="integral">
          <div class="today">
            <div class="today-point">我的{$milletname}</div>
            <div class="point">{$points}</div>
          </div>
          <div class="my">
            <div class="title">兑换金币</div>
            <div class="point">{$pointsexchange}</div>
          </div>
        </div>
        <div class="detail">
          <span> 累积兑换：</span>
          <span> {$sum}金币</span>
        </div>
      </div>

      <div class="layui-tab layui-tab-brief" lay-filter="docDemoTabBrief">
        <ul class="layui-tab-title">
          <li class="layui-this">积分</li>
          <li>金币</li>
        </ul>
        <div class="layui-tab-content">
          <div class="layui-tab-item layui-show">
            <ul  id="LAY_integral">
            </ul>
          </div>
          <div class="layui-tab-item">
            <ul  id="LAY_exchange" >
            </ul>
          </div>
        </div>
      </div>
    </div>
  </body>
  <script src="__VENDOR__/bugujsdk.js"></script>
  <script>
    layui.use('element', function () {
      var element = layui.element
    })
    layui.use('flow', function () {
      var $ = layui.jquery; //不用额外加载jQuery，flow模块本身是有依赖jQuery的，直接用即可。
      var flow = layui.flow;
      flow.load({
        elem: '#LAY_integral'    //指定列表容器
        , isAuto: true      //到底页面底端自动加载下一页，设为false则点击'加载更多'才会加载
        //, mb: 100          //距离底端多少像素触发auto加载
        , isLazying: true    //当单个li很长时，内部有很多图片，对图片进行懒加载，默认false。
        , end: '<p style="color:red">木有了</p>'    //加载所有后显示文本，默认'没有更多了'
        , done: function (page, next) {            //到达临界，触发下一页
          var lis = [];
          $.get('/h5/task/GetList?page=' + page+"&user_id="+{$user_id}, function (res) {
            //假设你的列表返回在data集合中
            layui.each(res.data.data, function (index, item) {
              var html="";
              html+=
                   '<li> <div> <p class="title">'+item.content+'</p>'+
                   '<p class="time">'+item.acttime+'</p> </div>'+
                   '<div class="consume">'+item.point+'</div></li>';

              lis.push(html);

            });
            next(lis.join(''), page < res.data.page_count);//pages是后台返回的总页数
          });
        }
      });

      flow.load({
        elem: '#LAY_exchange'    //指定列表容器
        , isAuto: true      //到底页面底端自动加载下一页，设为false则点击'加载更多'才会加载
        //, mb: 100          //距离底端多少像素触发auto加载
        , isLazying: true    //当单个li很长时，内部有很多图片，对图片进行懒加载，默认false。
        , end: '<p style="color:red">木有了</p>'    //加载所有后显示文本，默认'没有更多了'
        , done: function (page, next) {            //到达临界，触发下一页
          var lis = [];
          $.get('/h5/task/GetExchangeList?page=' + page+"&user_id="+{$user_id}, function (res) {
            //假设你的列表返回在data集合中
            layui.each(res.data.data, function (index, item) {
              var html="";
              html+=
                      '<li> <div> <p class="title">'+item.content+'</p>'+
                      '<p class="time">'+item.acttime+'</p> </div>'+
                      '<div class="consume">'+item.point+'</div></li>';

              lis.push(html);

            });
            next(lis.join(''), page < res.data.page_count);//pages是后台返回的总页数
          });
        }
      });
    });
    dssdk.ready(function (sdk) {
        //获取已登录用户信息
        sdk.getUser(function (result) {
            if (typeof result == 'object' && result !== null) {
                if (result.user_id != '' && user_id != result.user_id) {
                    window.location.replace("/h5/task/index?user_id=" + result.user_id);
                }
            }
        });

        sdk.getDeviceInfo(function (result2) {
            if (typeof result2 == 'object' && result2 !== null) {
                if ( parseInt(result2.notch_screen_height) > 0 ) {
                    $('.mui-bar').css("padding-top", result2.notch_screen_height + "px");
                    $('.mui-bar').css("height", (parseInt(result2.notch_screen_height) + 44) + "px");
                    $('.taskHD').css("padding-top", (parseInt(result2.notch_screen_height) + 44) + "px");
                }
            }
        });

        //ios 240+ js 返回按钮
        $('.mui-icon').click(function () {
            sdk.navigateBack();
        });

    });
  </script>
</html>

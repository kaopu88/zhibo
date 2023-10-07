<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="charset" content="utf-8">
    <meta name="viewport"
          content="initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no, width=device-width">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <meta name="application-name" content="{$product_name}">
    <title>{$product_name}充值中心</title>
    <meta name="description" content="{$product_name}充值中心，公众号充值、微信充值、支付宝充值">
    <meta name="keywords" content="{$product_name}充值中心">
    <link rel="stylesheet" type="text/css" href="__VENDOR__/animate.min.css?v=__RV__"/>
    <link rel="stylesheet" type="text/css" href="__CSS__/index/recharge.css?v=__RV__"/>
    <link rel="stylesheet" type="text/css" href="/bx_static/h5.css"/>
    <script src="/bx_static/changeFont.js"></script>
    <style>
        body {
            font-family: PingFang SC,Helvetica Neue,Helvetica,Arial,Hiragino Sans GB,Heiti SC,Microsoft YaHei,WenQuanYi Micro Hei,sans-serif
        }
        .tooltips {
            width: 100%;
            position: relative;
            z-index: 99;
            overflow: hidden;
            -webkit-box-sizing: border-box;
            box-sizing: border-box;
            font-size: 0.14rem;
        }
        .tooltips-cnt {
            line-height: 0.46rem;
            height: 0.46rem;
            padding-left: 0.12rem;
            padding-right: 0.12rem;
            background-color: #fff;
            color: #000;
            max-width: 100%;
            display: flex;
            align-items: center;
        }
        .tooltips-cnt p{
            flex: 1;
            overflow: hidden;
            white-space: nowrap;
            text-overflow: ellipsis;
        }
        .close img{
            width: 20px;
            height: 20px;
            vertical-align: sub;
        }
        
    .bean_lists {
  display: flex;
  justify-content: center;
  align-items: center;
 }

.bean_lists li {
  text-align: center;
}
.selected{
  background-color:red;
}
.bean_lists li{
    width: 1.06rem;
    height: 0.64rem;
    margin-right: 0.08rem;
    margin-top: 0.06rem;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    background-color: #f8f8f8;
    cursor: pointer;
}
.gold_rows {
  display        : flex;
  justify-content: center;
}
.gold_rowss{


}

    </style>
    <script src="__VENDOR__/jquery.min.js?v=__RV__"></script>
    <script src="__VENDOR__/layer/mobile/layer.js?v=__RV__"></script>
    <script>
        var selfUrl = '{$self_url}';
        var getUserInfoUrl = '{:url("recharge/get_user_info")}';
        var payOrderUrl = '{:url("recharge/pay_order")}';
        var qrcodeImgUrl = '__IMAGES__/a802e7619673eca8ea22fa109474b859.png?v=__RV__';
        var product_name = '{$product_name}'
        var prefix_name = '{$prefix_name}'
        var bean_name = '{$bean_name}'
        var payMethods = [
            {type: 'wxpay', icon: '__IMAGES__/recharge/wxpay.png', name: '微信支付', is_wx: true},
            {type: 'wxwap', icon: '__IMAGES__/recharge/wxpay.png', name: '微信支付', is_wx: false},
            {type: 'alipay', icon: '__IMAGES__/recharge/alipay.png', name: '支付宝', is_wx: false},
            {type: 'hjpay', icon: '__IMAGES__/recharge/hjpay.png', name: '皇嘉支付', is_wx: false}
        ];
        var _order = {
            rel_no: '{$order.order_no}',
            bean_num: parseInt('{$order.bean_num}'),

        };
        var is_wxwap = {$is_wxwap};
        var rel_no = '{$rel_no}';
    </script>

</head>
<body>
<div class="tooltips tooltips-guide">
    <div class="tooltips-cnt border-b">
        <img src="/bx_static/image/info.png" width="20px" height="20px" style="margin-right: 5px;">
        <p>{:config('app.product_setting.name')}提醒您理性消费，如遇问题可拨打<a href="tel:{:config('app.product_setting.service_tel')}">客户服务热线</a>。</p>
        <a class="close">
            <img src="/bx_static/image/close.png">
        </a>
    </div>
</div>

    <div class="bg_pay">
        <div class="out-header">
            <div class="top">
                <div class="avatar">
                    <img class="avter_img" src="" alt="">
                </div>
                <div class="info">
                    <p class="nickname"></p>
                    <p class="uid"></p>
                </div>
                <div class="switch_account">切换账号</div>
            </div>
        </div>
        <div class="account_box">
            <div class="account_info">
                <h2 class="pay_title">充值用户:</h2>
                <div class="account_row">
                    <div class='down'></div>
                    <input placeholder="请输入您的用户ID" name="user_id" type="tel" class="account_input" value="{$_info.user_id}"/>
                    <div class="verify">确认</div>
                </div>
               <!--
                 <div class="account_hint">如何找到我的{$product_name}账号</div>
               -->
            </div>
        </div>
         <!--   
        <div class="guide-img"><img src="/bx_static/admin/assets/back.png" alt=""></div>-->
         <div class="bean_boxss opacity">
         <ul>
                <volist name="_payments" id="voo">
                            <li data-id="{$voo.id}" >
                               
                                    
                                    <div class="bean_nums" onclick="plays({$voo.id},'{$voo.class_name}')" style="font-size:10px;max-width: 100%;"><img src="{$voo.thumb}" style="max-width: 100%;"></div>
                              
                               
                            </li>
                </volist>
         </ul>
        
    </div>    
     
        <br>
        <div class="bean_box opacity">
            <h2>选择充值套餐:</h2>
            <ul class="bean_list">
                <volist name="_list" id="vo">
                    <li data-id="{$vo.id}" data-price="{$vo.price}" data-bean="{$vo.bean_num}" onclick="aa({$vo.id},{$vo.bean_num},{$vo.price},'{$_payments[0]['class_name']}')">
                        <div class="gold_row">
                            <div class="gold_icon"></div>
                            <span class="bean_num">{$vo.bean_num}</span>
                        </div>
                        <span class="price">{$vo.price}元</span>
                    </li>
                </volist>
            </ul>
            <div class="clear"></div>
            <div class="agreement">
                <span>充值即代表同意</span>
                <span class="read_agreement"><a href="/h5/about/detail/mark/protocol_recharge">{$product_name}充值协议</a></span>
            </div>
            
            <input type="hidden" id="bean_id" name="bean_id" value="{$_info.bean_id}"/>
            <input type="hidden" id="pay_method" name="pay_method" value="{$_info.pay_method}"/>
        </div>
        <div class="bean_box opacity" style="margin-top: 20px;">
            <h2>充值说明</h2>
            <p class="text">
                1、充值步骤：输入用户ID&nbsp;>&nbsp;点击确认&nbsp;>&nbsp;选择充值套餐&nbsp;>&nbsp;确认支付&nbsp;>&nbsp;充值完成。<br/>
                2、支付前请仔细确认账号无误，充值后不可退款。<br/>
                3、微信内不支持支付宝支付，如需支付宝支付请在浏览器中打开 。<br/>
                4、若您还没有用户账号，请先下载&nbsp;<a href="/h5/download.html">{$product_name}</a>&nbsp;并进行注册。
            </p>
        </div>
        <div class="bottom">Copyright 2018 - {:date('Y')} {$product_name}. All Rights Reserved</div>

        <div class="popup_box">
            <div class="popup_bg"></div>
            <div class="popup_panel animated"></div>
        </div>
        <div class="open_tip">
            <img src="__IMAGES__/open_tip.png?v=__RV__"/>
            <p>微信内不支持支付宝支付，请在浏览器中打开<br/><br/>
                关闭提示
            </p>
        </div>
    </div>
   
    <script src="/bx_static/toggle.js"></script>
    <script src="__JS__/index/recharge.js?v=__RV__23"></script>
<script>

   function plays(id,pay_method){
   
        $('#pay_method').val(pay_method);
        var url = "/h5/recharge/paymentid/id/"+id;    
             var str="";
             // 获取 ul 元素
        var ulElement = document.querySelector('.bean_list');
        while (ulElement.firstChild) {
          ulElement.removeChild(ulElement.firstChild);
        }
        console.log({$_info.pay_method});
        $.get(url,{}, function (re) {
       
    
            var taolistt =  re.data.taocan;
            
          
         taolistt.forEach(function(item) {
          
              
         var liElement = document.createElement('li');
          liElement.setAttribute('data-id', item.id);
          liElement.setAttribute('data-price', item.price);
          liElement.setAttribute('data-bean', item.bean_num);
          liElement.setAttribute('onclick', "aa("+item.id+","+item.price+","+item.bean_num+",'"+pay_method+"')");    
          var goldRowDiv = document.createElement('div');
          goldRowDiv.classList.add('gold_row');
          
          var goldIconDiv = document.createElement('div');
          goldIconDiv.classList.add('gold_icon');
          
          var beanNumSpan = document.createElement('span');
          beanNumSpan.classList.add('bean_num');
          beanNumSpan.textContent = item.bean_num;
          
          goldRowDiv.appendChild(goldIconDiv);
          goldRowDiv.appendChild(beanNumSpan);
          
          var priceSpan = document.createElement('span');
          priceSpan.classList.add('price');
          priceSpan.textContent = item.price + '元';
          
          liElement.appendChild(goldRowDiv);
          liElement.appendChild(priceSpan);
          
          
            // 添加点击事件处理程序
         
          ulElement.appendChild(liElement);
        
        
            })
            
        })
  }
  //$(".bean_nums").click(function(event){
    // 调用点击事件处理程序函数，并传递参数
   //console.log(event);
  //});
   function aa(a,b,c,d){
   
   //var liId = $(this).attr('data-id');
   console.log(a);
   console.log(b);
   console.log(c);
   console.log(d);
   
   select(a);
   
   
   }
   

    $(document).on("click", ".tooltips .close", function () {
        $(".tooltips-guide").fadeOut();
    });
</script>
</body>
</html>

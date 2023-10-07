<include file="public/head" />
<title>公众号充值支付</title>
<script type="text/javascript" src="http://apps.bdimg.com/libs/zepto/1.1.4/zepto.min.js"></script>
<body>

<div style="color: #000;text-align: center;font-weight: bold;">
    充值<br/><br/>
</div>
<div style="color: #000;text-align: center;font-weight: bold;line-height:50px;font-size:40px;">
    ￥ {$money}
</div>

<div align="center">
    <button style="width:210px; height:50px; border-radius: 5px;background-color:#1aad19; border:0px #179e16 solid; cursor: pointer;  color:white;  font-size:16px;" type="button" onclick="callpay()" >立即支付</button>
</div>

</body>
    <script type="text/javascript">

        function onBridgeReady(){
            WeixinJSBridge.invoke(
                'getBrandWCPayRequest', {$order},
                function(res){
                    alert(res.err_msg);
                    if(res.err_msg == "get_brand_wcpay_request:ok" ){
                        // 使用以上方式判断前端返回,微信团队郑重提示：
                        //res.err_msg将在用户支付成功后返回ok，但并不保证它绝对可靠。
                    }
                });
        }


        function callpay()
        {
            if (typeof WeixinJSBridge == "undefined"){
                if( document.addEventListener ){
                    document.addEventListener('WeixinJSBridgeReady', onBridgeReady, false);
                }else if (document.attachEvent){
                    document.attachEvent('WeixinJSBridgeReady', onBridgeReady);
                    document.attachEvent('onWeixinJSBridgeReady', onBridgeReady);
                }
            }else{
                onBridgeReady();
            }
        }

    </script>
</html>
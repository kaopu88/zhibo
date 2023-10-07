<include file="public/head" />
<title>{$title}</title>
<link rel="stylesheet" href="__H5__/css/share/common.css">
<style type="text/css">



    img{
        width: 100%;
    }

    .btn{
        position: absolute;
        top: 28rem;
        display: flex;
        width: 100%;
        justify-content: space-around;
    }
    .btn>div{
        width: 40%;
    }


    #user{
        opacity:0.3;
    }

    .section-body-avater{
        float: left;
        width: 2.5rem;
        height: 1rem;
        display: flex;
        align-items: center;
        justify-content: flex-end;

    }
    .section-body-avater span{
        font-size: 0.4rem;
    }

    .section-body-avater img{
        width: 1rem;
        border-radius: 100%;
        margin-left: .2rem;
    }

    .section-body-info{
        font-size: .25rem;
        float: left;
        width: 2.8rem;
        height: 1rem;
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        padding: .1rem 0;
        box-sizing: border-box;
        margin-left: .4rem;
    }

    .linght{
        color:#FFF;
        text-shadow: -5px -5px 20px #f600ff,
        5px -5px 20px #f600ff,
        5px 5px 20px #f600ff,
        -5px 5px 20px #f600ff;
    }

    .text-linght{
        color:#FFF;
        text-shadow: 0px -1px 10px #f600ff;
    }

    .layui-flow-more{
        font-size: .2rem !important;
        width: 100% !important;
        clear: both !important;
        text-align: center !important;
        padding-bottom: .2rem !important;
    }


    #user_rank{
        display: none;
    }

    .anchor_rank{
        width:1rem;
    }

    .anchor_rank>img{
        width: 100%;
        border-radius: 100%;
        margin-left: 0.3rem;
    }

    .anchor_cotr{
        float: right;
        width: 4.4rem;
    }

    .user_cotr_li{
        float: left;
        width: 100%;
        border-bottom: 1px dashed #70046e;
        padding-bottom: .3rem;
        margin-bottom: 0.3rem;
    }

    .user_info{
        background: url('__IMAGES__/live_act/gratitude/user_info.png') no-repeat;
        width: 55%;
        height: .6rem;
        background-size: 100% 100%;
        margin: 0 auto;
        line-height: .6rem;
        color: #B1298E;
        font-size: .26rem;
        text-align: center;
    }


    .marg{
        margin-top: 2rem;
    }


    .input{
        height: 1.8rem;
        width: 65%;
        border-radius: 3px;
        border: 1px solid #5588df;
        margin: 1rem;
        padding-left: 2px;
        color: #6271DB;
        font-size: 1rem;
    }

    .bugu{color: white;text-align: center;width: 100%;}




    /*活动规则*/
    .rule{
        overflow: hidden;
        text-overflow: ellipsis;
        color: rgb(98, 113, 219);
        font-size: 0.25rem;
        margin: 0 auto;
        text-align: justify;
        padding: 0 .3rem;
        height: 3rem;
    }
    .rule p:first-child{
        padding-top: .1rem;
    }

    .rule .arrow-up {
        display: inline-block;
        vertical-align: top;
        border-bottom: 6px solid #6271db;
        border-right: 6px solid transparent;
        border-left: 6px solid transparent;
        content: "";
    }
    .rule .arrow-down {
        display: inline-block;
        vertical-align: middle;
        border-top: 6px solid #6271db;
        border-right: 6px solid transparent;
        border-left: 6px solid transparent;
        content: "";
    }


    /*排名css*/
    .gratitude_rank{
        width: 100%;
        height: auto;
        padding-top: .3rem;
    }



    /*背景*/
    .container .bg{
        background: url('__IMAGES__/live_act/gratitude/mid.png') no-repeat;
        background-size: 100% 100%;
    }

    .container .title{
        position: relative;
    }

    .container .title>span{
        position: absolute;
        left: 0;
        top: .25rem;
        right: 0;
        text-align: center;
        color: #5970dc;
        font-weight: 600;
        font-size: .28rem;
    }
    .container .bottom, .container .title{
        max-height: .5rem;
    }

    .container .bottom>img, .container .title>img{
        vertical-align: text-top;
    }

    .container {
        width: 88%;
        margin: 0 auto;
    }


    /*获奖名单*/
    .winners{
        font-size: .3rem;
        color: #6271DB;
    }

    .winners .winners-title{
        display: flex;
        justify-content: space-around;
        font-weight: 600;
        font-size: 0.27rem;
        padding: .15rem 0;
    }

    .winners .winners-content{
        overflow: scroll;
        max-height: 3rem;
    }

    .winners .winners-content li{
        float: left;
        width: 32%;
        text-align: center;
        overflow: hidden;
        white-space: nowrap;
        text-overflow: ellipsis;
        font-size: .24rem;
    }


    /*转盘css*/
    .luck_rotate{
        position: relative;
        width: 100%;
    }
    .luck_rotate>div{
        margin: 0 auto;
        text-align: center;
        width: 80%;
    }

    .luck_rotate .luck_rotate_btn{
        width: 2rem;
        margin: 0 auto;
        display: block;
        position: absolute;
        top: 1.55rem;
        left: 2.21rem;
    }

</style>
<link rel="stylesheet" type="text/css" href="__H5__/zz/mobilecommon.css">
<link rel="stylesheet" type="text/css" href="__H5__/zz/mobile.css">
<script type="text/javascript" src="http://apps.bdimg.com/libs/zepto/1.1.4/zepto.min.js"></script>
</head>

<body>

<a href="tel:13143404169"><img src="__H5__/zz/service.png" alt="客服电话" class="same-myget"></a>

<!-- top公告 -->
<div class="topScrollline"></div>
<section class="topScrollMain">
    <div class="hot">
        <img src="__H5__/zz/brodast.png" alt="">
    </div>

    <div id="topScroll"><span><b>恭喜</b><strong>182***3539</strong><b>用户获得</b><i>创意雨伞</i></span><span><b>恭喜</b><strong>180***4725</strong><b>用户获得</b><i>富士拍立得</i></span><span><b>恭喜</b><strong>137***2099</strong><b>用户获得</b><i>创意雨伞</i></span><span><b>恭喜</b><strong>173***6386</strong><b>用户获得</b><i>富士拍立得</i></span><span><b>恭喜</b><strong>156***6904</strong><b>用户获得</b><i>创意雨伞</i></span><span><b>恭喜</b><strong>185***4939</strong><b>用户获得</b><i>ipaid</i></span><span><b>恭喜</b><strong>183***1373</strong><b>用户获得</b><i>创意雨伞</i></span><span><b>恭喜</b><strong>188***9498</strong><b>用户获得</b><i>创意小音箱</i></span><span><b>恭喜</b><strong>132***6215</strong><b>用户获得</b><i>创意小音箱</i></span><span><b>恭喜</b><strong>159***8597</strong><b>用户获得</b><i>创意雨伞</i></span><span><b></b><strong></strong><b></b><i></i></span><span><b>恭喜</b><strong>182***3539</strong><b>用户获得</b><i>创意雨伞</i></span><span><b>恭喜</b><strong>180***4725</strong><b>用户获得</b><i>富士拍立得</i></span><span><b>恭喜</b><strong>137***2099</strong><b>用户获得</b><i>创意雨伞</i></span><span><b>恭喜</b><strong>173***6386</strong><b>用户获得</b><i>富士拍立得</i></span><span><b>恭喜</b><strong>156***6904</strong><b>用户获得</b><i>创意雨伞</i></span><span><b>恭喜</b><strong>185***4939</strong><b>用户获得</b><i>ipaid</i></span><span><b>恭喜</b><strong>183***1373</strong><b>用户获得</b><i>创意雨伞</i></span><span><b>恭喜</b><strong>188***9498</strong><b>用户获得</b><i>创意小音箱</i></span><span><b>恭喜</b><strong>132***6215</strong><b>用户获得</b><i>创意小音箱</i></span><span><b>恭喜</b><strong>159***8597</strong><b>用户获得</b><i>创意雨伞</i></span><span><b></b><strong></strong><b></b><i></i></span></div>
</section>

<!-- 左面漂浮 -->
<a class="rocket" name="dt" href="https://www.bianxianguanjia.com/toActivityBydzp/10735746/166/7" style="display: none;">
    <img src="https://www.bianxianguanjia.com/toActivityBydzp/10735746/166/7" alt="">
</a>

<!-- 右面漂浮 -->
<a class="rocketRight" name="dt" href="https://www.bianxianguanjia.com/toActivityBydzp/10735746/166/7" style="display: none;">
    <img src="https://www.bianxianguanjia.com/toActivityBydzp/10735746/166/7" alt="">
</a>

<div class="emptyJp jqTip" style="display: none;">
    <a href="https://www.bianxianguanjia.com/toActivityBydzp/10735746/166/7">
        <img src="https://www.bianxianguanjia.com/toActivityBydzp/10735746/166/7" alt="">
        <img data-src="/img/plugIn/dzp/theme1/bg-btn.png" alt="">
    </a>
    <a href="http://kxyx.77cola.com/toActivityByLbx/10735746/154">
        <img src="https://www.bianxianguanjia.com/toActivityBydzp/10735746/166/7" alt="">
        <img data-src="/img/plugIn/dzp/theme1/bg-btn.png" alt="">
    </a>
    <div class="close_tip close_stop_tip">
        <img data-src="/img/plugIn/dzp/theme1/close.png" alt="">
    </div>
</div>

<div class="mainBody">

    <!-- 各类隐藏值 -->
    <input id="oneKey" type="hidden" value="ed501106-06a6-40e3-bcb1-03c56445af17">
    <input id="activityId" type="hidden" value="166">
    <input id="enterpriseNo" type="hidden" value="10735746">
    <input id="prizeId" type="hidden" value="18421" data-href="http://vip.hfjy.com/jjjp-m-dt1?adid=w820">

    <!--转盘主体-->
    <div class="main">
        <div class="headerBanner">
            <div class="way">
                <img src="__H5__/zz/wh.png" alt=""><span>规则</span>
            </div>
            <a id="linkMy" href="https://www.bianxianguanjia.com/toMyPrizeList/10735746/166?oneKey=aBLLA-777HdI-4Nj5Sz-DlAT6L-fiW1c">
                <img src="__H5__/zz/jp.png" alt="我的奖品">
            </a>
        </div>

        <!--转盘-->
        <div class="rotateMain">
            <div class="ramain" style="transform: rotate(0deg);">
                <canvas data-move="0" data-color0="#90e3fe" data-color1="#53aaff" class="item active" id="wheelcanvas"></canvas>
            </div>
            <img id="pointer" class="pointer" data-src1="__H5__/zz/zhizhen.png" data-src="__H5__/zz/zhizhen.png" src="__H5__/zz/zhizhen.png">
            <div class="bian active" style="transform: rotate(0deg);"></div>
            <div class="zhe"></div>
            <div class="ding"></div>
        </div>

        <!--奖品图片资源-->
        <div class="imgList" id="imgListDzp" style="display:none;">
            <img id="newImg0" src="__H5__/zz/1.png">
            <img id="newImg1" src="__H5__/zz/2.png">
            <img id="newImg2" src="__H5__/zz/3.png">
            <img id="newImg3" src="__H5__/zz/4.png">
            <img id="newImg4" src="__H5__/zz/5.png">
            <img id="newImg5" src="__H5__/zz/6.png">
        </div>

        <div class="counts">抽奖机会剩余<span class="numEgg">7</span>次</div>
    </div>


</div>

<!-- 规则弹窗 -->


<!-- 奖品弹窗 -->



<script>
    $(function(){
        if($('#wheelcanvas').attr('data-move') == 1){
            $('.mainBody').on('touchmove',function(){
                return false;
            })
        }

        /*var mulitImg = [];
        //奖品数据
        $.each(dataArr.prizeList,function(k,v){
            var imgBody = document.getElementById('imgListDzp');
            var imgNew = document.createElement("img");
            imgNew.setAttribute("id", "newImg");

            imgNew.src = v.prizeIconUrl;
            mulitImg.push(v.prizeIconUrl);
            if (v.prizeLevel == 'ONE') {
                imgNew.setAttribute("id", "newImg"+k);
            } else if (v.prizeLevel == 'TWO') {
                imgNew.setAttribute("id", "newImg"+k);
            } else if (v.prizeLevel == 'THREE') {
                imgNew.setAttribute("id", "newImg"+k);
            } else if (v.prizeLevel == 'FOUR') {
                imgNew.setAttribute("id", "newImg"+k);
            } else if (v.prizeLevel == 'FIVE') {
                imgNew.setAttribute("id", "newImg"+k);
            }
            imgBody.appendChild(imgNew);

        })

        var imgBody = document.getElementById('imgListDzp');
        var imgNew = document.createElement("img");
        imgNew.setAttribute("id", "newImg5");
        imgNew.src = '/img/plugIn/dzp/theme1/110.png';
        imgBody.appendChild(imgNew);
        mulitImg.push(imgNew.src);*/

        var numImg = 0
        var turnplate={
            restaraunts:[],       //大转盘奖品名称
            colors:[],            //大转盘奖品区块对应背景颜色
            outsideRadius:$('#wheelcanvas').attr('data-width') ? $('#wheelcanvas').attr('data-width') : 755,    //大转盘外圆的半径
            textRadius:620,       //大转盘奖品位置距离圆心的距离
            insideRadius:0,       //大转盘内圆的半径
            startAngle:0,         //开始角度

            bRotate:false         //false:停止;ture:旋转
        };
        turnplate.restaraunts = ["1", "2", "3", "4", "5 ", "6"];
        var color0 = $('#wheelcanvas').attr('data-color0');
        var color1 = $('#wheelcanvas').attr('data-color1');
        turnplate.colors = [color0,color1,color0,color1,color0,color1];

        function drawRouletteWheel()
        {
            var canvas = document.getElementById("wheelcanvas");

            if (canvas.getContext)
            {
                //根据奖品个数计算圆周角度
                var arc = Math.PI / (turnplate.restaraunts.length/2);
                var ctx = canvas.getContext("2d");
                canvas.width = '1688';
                canvas.height = '1688';
                $('#wheelcanvas').css({'width':'100%', 'height':'100%'});
                //在给定矩形内清空一个矩形
                ctx.clearRect(0,0,422,422);
                //strokeStyle 属性设置或返回用于笔触的颜色、渐变或模式
                ctx.strokeStyle = "rgba(0,0,0,0)";
                //font 属性设置或返回画布上文本内容的当前字体属性
                ctx.font = 'bold 18px Microsoft YaHei';
                for(var i = 0; i < turnplate.restaraunts.length; i++) {
                    var angle = turnplate.startAngle + i * arc;
                    ctx.fillStyle = turnplate.colors[i];
                    ctx.beginPath();
                    //arc(x,y,r,起始角,结束角,绘制方向) 方法创建弧/曲线（用于创建圆或部分圆）
                    ctx.arc(844, 844, turnplate.outsideRadius, angle, angle + arc, false);
                    ctx.arc(844, 844, turnplate.insideRadius, angle + arc, angle, true);
                    ctx.stroke();
                    ctx.fill();
                    //锁画布(为了保存之前的画布状态)
                    ctx.save();

                    //改变画布文字颜色
                    var b = i+2;
                    if(b%2){
                        ctx.fillStyle = "#003369";
                    }else{
                        ctx.fillStyle = "#042f35";
                    };

                    //----绘制奖品开始----

                    var text = turnplate.restaraunts[i];
                    var line_height = 17;
                    //translate方法重新映射画布上的 (0,0) 位置
                    ctx.translate(844 + Math.cos(angle + arc / 2) * turnplate.textRadius, 844 + Math.sin(angle + arc / 2) * turnplate.textRadius);

                    //rotate方法旋转当前的绘图
                    ctx.rotate(angle + arc / 2 + Math.PI / 2);

                    //添加对应图标
                    if(text.indexOf(turnplate.restaraunts[0])>=0){
                        var img0 = document.getElementById('newImg0');
                        ctx.drawImage(img0,-160,-70,320,320);
                    };

                    if(text.indexOf(turnplate.restaraunts[1])>=0){
                        var img1 = document.getElementById('newImg1');
                        ctx.drawImage(img1,-160,-70,320,320);
                    };
                    if(text.indexOf(turnplate.restaraunts[2])>=0){
                        var img2 = document.getElementById('newImg2');
                        ctx.drawImage(img2,-160,-70,320,320);
                    };
                    if(text.indexOf(turnplate.restaraunts[3])>=0){
                        var img3 = document.getElementById('newImg3');
                        ctx.drawImage(img3,-160,-70,320,320);
                    };
                    if(text.indexOf(turnplate.restaraunts[4])>=0){
                        var img4 = document.getElementById('newImg4');
                        ctx.drawImage(img4,-160,-70,320,320);
                    };
                    if(text.indexOf(turnplate.restaraunts[5])>=0){
                        var img5 = document.getElementById('newImg5');
                        ctx.drawImage(img5,-160,-70,320,320);
                    };

                    //把当前画布返回（调整）到上一个save()状态之前
                    ctx.restore();
                }
            }
        }

        /* var imgloade = [],
         flag = 0;
         var imgTotal = mulitImg.length;

         for(var i = 0 ; i < imgTotal ; i++){
            imgloade[i] = new Image()
            imgloade[i].src = mulitImg[i]
            imgloade[i].onload = function(){
              //第i张图片加载完成
              flag++
              if( flag == imgTotal ){
                 //全部加载完成
                  setTimeout(function(){
                      drawRouletteWheel();
                  },200)
              }
          }*/
        setTimeout(function(){
            drawRouletteWheel();
        },150)
    });


</script>




<div class="bugu" style="font-size: .15rem;">
    *本活动最终解释权归{:APP_NAME}官方所有*
</div>
</body>
<script src="__STATIC__/common/js/layer_mobile/layer.js"></script>
<script src="__H5__/js/css-base.js?v=__RV__"></script>
<script src="__VENDOR__/bugujsdk.js?v=__RV__"></script>

<!--<script>

    $(function () {

        $('#icon-bottom').click(function () {

            var rp = $('#rule-text-p');

            if (rp.height() < 250)
            {
                rp.height('auto');
                $('#rule-text').height('auto');
                $(this).removeClass('arrow-down').addClass('arrow-up');
            } else {
                $('#rule-text-p').height('15.5rem');
                $('#rule-text').height('10.5rem');
                $(this).removeClass('arrow-up').addClass('arrow-down');
            }
        });

        var uid = "{$uid}";

        if (uid == '')
        {
            bugu.ready(function (sdk) {
                sdk.getUser(function (result) {
                    if (typeof result == 'object' && result !== null) {
                        if (result.user_id != '')
                        {
                            $.post('/Live_act/getUserPrizeNum', {"user_id":result.user_id}, function (res) {
                                if (res.status == 0)
                                {
                                    $('#chance').html(res.data.score);
                                    uid = result.user_id;
                                }else {
                                    $('.user_info').html('请从直播间进入查看抽奖次数');
                                }
                            });
                        }
                    }
                });
            });
        }

        function disabled(key) {
            switch (key) {
                case "noStart":
                layer.open({
                    content: '活动尚未开始',
                    btn: '我知道了'
                });
                break;
                case "completed":
                layer.open({
                    content: '活动已结束',
                    btn: '我知道了'
                });
                break;
            }
        }


        function clickCallback() {
            var that = this;
            $.ajax({
                type:'GET',
                url:"{$ajaxUrl}",
                data:{"user_id":uid},
                success:function(result){
                    if(result.status == 0){
                        that.opts.success_msg = result.data.prize_name;
                        that.opts.is_material = result.data.is_material;
                        that.opts.is_register = result.data.is_register;
                        that.rotate(result.data.deg_start, result.data.deg_end);
                    }else{
                        layer.open({
                            content: result.message,
                            btn: '我知道了'
                        });
                    }
                },
                timeout:1500
            });
        }


        function end(deg) {
            var msg = this.success_msg;
            var material = this.is_material;
            var register = this.is_register;
            var user_id = uid.substr(0,1)+'***'+uid.substr(-1,1);
            var myDate = new Date();
            var d = myDate.getDate();
            d = d < 10 ? ('0' + d) : d;
            var shijian   = myDate.getFullYear()+'-'+(myDate.getMonth()+1)+'-'+d;
            $('#chance').html(function(index,html){
                return html-1;
            });

            $('.titlelist').before('<li style="list-style: none;float: left;width: 32%;text-align: center;line-height: 1.5rem;overflow:hidden;white-space:nowrap;text-overflow:ellipsis;font-size: 0.9rem;color: #6271DB;">'+user_id+'</li><li style="list-style: none;float: left;width: 32%;text-align: left;line-height: 1.5rem;overflow:hidden;white-space:nowrap;text-overflow:ellipsis;font-size: 0.9rem;color: #6271DB;">'+msg+'</li><li style="list-style: none;float: left;width: 32%;text-align: center;line-height: 1.5rem;overflow:hidden;white-space:nowrap;text-overflow:ellipsis;font-size: 0.9rem;color: #6271DB;">'+shijian+'</li>');

            if (material == 1)
            {
                layer.open({
                    content: '恭喜您获得'+ msg
                    ,btn: '我知道了'
                    ,yes : function (index) {
                        layer.close(index);
                        if (register == 0)
                        {
                            //弹出窗登录地址
                            layer.open({
                                type: 1
                                ,content: '<div>\n' +
                                '    <div style="color: #6271DB;padding-left: .5rem;text-align: right;">收件人:<input id="nickname" class="input" type="text" name="nickname"></div>\n' +
                                '    <div style="color: #6271DB;padding-left: .5rem;text-align: right;">联系方式:<input id="mobile" class="input" type="text" name="mobile"></div>\n' +
                                '    <div style="color: #6271DB;padding-left: .5rem;text-align: right;">收件地址:<input id="address" class="input" type="text" name="address"></div>\n' +
                                '</div>'
                                ,anim: 'up'
                                ,style: 'width: 85%; border:none;border-radius: 6px;'
                                ,btn : '提交'
                                ,yes : function (index) {
                                    var mobile = $('#mobile').val();
                                    var address = $('#address').val();
                                    var nickname = $('#nickname').val();
                                    if (mobile != '' || address != '' || nickname != '')
                                    {
                                        $.post('/live_act/addUserAddress', {"mobile":mobile, "address":address, "nickname":nickname, 'user_id':uid}, function (res) {
                                            if (res.status == 0)
                                            {
                                                layer.open({
                                                    content: res.message
                                                    ,skin: 'msg'
                                                    ,time: 2 //2秒后自动关闭
                                                });
                                            }
                                        });
                                    }

                                    layer.close(index);
                                }
                            });
                        }
                    }
                });

            }else {
                layer.open({
                    content: '您获得的"'+msg+'"系统已自动发放'
                    ,skin: 'msg'
                    ,time: 3 //2秒后自动关闭
                });
            }
        }


        new Turntable({
            rotateNum:8,
            body:"#rotate",
            direction:0,
            disabled : disabled,
            clickCallback : clickCallback,
            end:end,
            rotateBody : ".luck_rotate_content", //转盘旋转主体选择符
            trigger: ".luck_rotate_btn" //点击触发的选择符
        });
    });


</script>-->

</html>
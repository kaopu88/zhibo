<include file="public/head" />
<title>{:APP_NAME}创作号认证</title>
<style type="text/css">

    *{margin:0px;padding:0px;}
    html:root body, html:root input, html:root button, html:root textarea, html:root select{font-family:Tahoma, Geneva, 'Microsoft YaHei', 'SimSun';}
    a img{border:none;}
    a{text-decoration:none;color:#3598dc;text-overflow:ellipsis;white-space:nowrap;outline:none;}
    a:hover{color:#2b84c1;}
    input[type=button],button{-webkit-appearance:none;border:none;outline:none;font-family:'microsoft yahei', Verdana, Arial, Helvetica, sans-serif;}
    input{autocapitalize="off";autocorrect="off"}
    input:focus, textarea:focus, button, select,label:focus{outline:none;blr:expression_r(this.onFocus=this.blur());}
    button,input,select,textarea,label{margin:0;vertical-align:middle;border:none;outline:none;font-family:'microsoft yahei', Verdana, Arial, Helvetica, sans-serif;}
    button,input{*overflow:visible;line-height:normal;outline:none;}
    button::-moz-focus-inner,input::-moz-focus-inner{padding:0;border:0;}
    button,html input[type="button"],input[type="reset"],input[type="submit"]{-webkit-appearance:button;cursor:pointer;}
    input[type="search"]{-webkit-box-sizing:content-box;-moz-box-sizing:content-box;box-sizing:content-box;-webkit-appearance:textfield;}
    input[type="search"]::-webkit-search-decoration,input[type="search"]::-webkit-search-cancel-button{-webkit-appearance:none;}
    textarea{overflow:auto;vertical-align:top;resize:none;}
    i,em{font-style:normal;}
    ul,ol li{list-style:none;}
    body,html{height:100%;position:relative;background: #f4f4f4;}

    .section{
        width: 100%;
        /*height: 515px;*/
        background: #fff;
        margin: 4px 0;
    }
    .banner{
        width: 100%;
    }

    .liberty{
        width: 70%;
        height: 100%;
    }

    .tq{
        display: flex;
        /*flex-wrap: nowrap;
        justify-content: space-around;
        width: 100%;*/
        padding: 0px 10px 25px 10px;
    }

    .title{
        color: #373737;
        padding: 25px 0px 25px 20px;
        font-size: 20px;
    }

    .summary{
        padding: 0 20px 27px 20px;
        color: #373737;
        text-align: justify;
        font-size: 16px;
        line-height: 27px;
    }
    .tq > div{
        text-align: center;
        width: 25%;
    }
    .tq .explain{
        color: #373737;
        margin-top: 10px;
        font-size: 14px;
    }
    .summary{
        color: #373737;
        padding-left: 20px;
        line-height: 27px;
        padding-bottom: 28px;
    }
    .summary ol{
        margin-left: 20px;
    }
    .summary li{
        list-style: decimal;
    }
    .success{
        color: #fff;
        background-color: #5cb85c;
        border-color: #4cae4c;
    }

    .info{
        color: #fff;
        background-color: #5bc0de;
        border-color: #46b8da;
    }

    .warning{
        color: #fff;
        background-color: #f0ad4e;
        border-color: #eea236;
    }

    .btn{
        display: inline-block;
        width:98%;
        line-height: 50px;
        margin-bottom: 0;
        font-size: 16px;
        font-weight: 400;
        text-align: center;
        white-space: nowrap;
        vertical-align: middle;
        -ms-touch-action: manipulation;
        touch-action: manipulation;
        -webkit-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
        user-select: none;
        background-image: none;
        border: 1px solid transparent;
        border-radius: 1px;
    }

</style>
</head>

<body>
<section>
    <section class="section" style="margin-top: 0px">
        <img class="banner" src="__H5__/images/creative/banner.png" alt="">
    </section>

    <section class="section">
        <h3 class="title">什么是创作号</h3>
        <p class="summary">
            {:APP_PREFIX_NAME}创作号作为{:APP_PREFIX_NAME}特色视频创作者的统一称谓。坚持原创、展示创意才华、秀出自我魅力，就是{:APP_PREFIX_NAME}创作号。创作号将享有{:APP_PREFIX_NAME}平台全球范围曝光。
        </p>
    </section>

    <section class="section">
        <h3 class="title">创作号特权</h3>
        <div class="tq">
            <div>
                <p><img class="liberty" src="__H5__/images/creative/rz.png" alt=""></p>
                <p class="explain">专属认证</p>
            </div>

            <div>
                <p><img class="liberty" src="__H5__/images/creative/t.png" alt=""></p>
                <p class="explain">特权免费</p>
            </div>

            <div>
                <p><img class="liberty" src="__H5__/images/creative/ll.png" alt=""></p>
                <p class="explain">千万流量</p>
            </div>

            <div>
                <p><img class="liberty" src="__H5__/images/creative/rmb.png" alt=""></p>
                <p class="explain">商业变现</p>
            </div>

            <div>
                <p><img class="liberty" src="__H5__/images/creative/bg.png" alt=""></p>
                <p class="explain">全球曝光</p>
            </div>
        </div>
    </section>

    <section class="section">
        <h3 class="title">认证申请主要考核指标</h3>
        <div class="summary">
            <ol>
                <li>内容需要原创;</li>
                <li>视频内容有集中的风格，不过于日常或杂乱;</li>
                <li>保持帐号的活跃度，最新一周内有更新;</li>
                <if condition="$creation_report_record eq 1">
                    <li>遵守社区规则，没有违规或被举报记录;</li>
                </if>
            </ol>
        </div>
    </section>

    <section class="section">
        <h3 class="title">申请认证基础要求</h3>
        <div class="summary">
            <ol>
                <li>粉丝数≥{$creation_fans_num};</li>
                <li>原创视频数≥{$creation_film_num};</li>
                <if condition="$creation_report_record eq 1">
                    <li>遵守社区规则，没有违规或被举报记录;</li>
                </if>
            </ol>
        </div>
    </section>

    <section class="section">
        <h3 class="title">申请提醒</h3>
        <div class="summary">
            <ol>
                <li>系统会在5个工作日进行审核，并通知审核结果;</li>
                <li>认证只针对原创内容，非原创内容将无法通过认证;</li>
                <li>刷粉等作弊行为，一经查实，将取消认证，并限制其2个月内的认证申请;</li>
            </ol>
        </div>
    </section>

    <div id="apply" class="btn">
    </div>
</section>

</body>

<script type="text/javascript" src="__VENDOR__/jquery.min.js?v=__RV__"></script>
<script src="__VENDOR__/bugujsdk.js?v=__RV__"></script>
<script>

    var user_id = '';

    function show(data) {
        var btn = document.querySelector("#apply");
        var cls = btn.className;
        btn.innerHTML = '<p>'+data.msg+'</p>';

        switch (data.status)
        {
            case 0 :
                btn.className = cls+' info';
                break;

            case 1 :
                btn.className = cls+' success';
                break;

            case 2 :
                btn.className = cls+' warning';
                break;

            case 3 :
                btn.className = cls+' info';
                addEvent(btn, 'click', apply);
                break;

            case -1 :
                btn.className = cls+' warning';
                break;
        }
    }


    function addEvent(elem,event,fn) {
        if(elem.addEventListener){
            elem.addEventListener(event, fn, false);
        }else if (elem.attachEvent){
            elem.attachEvent('on'+event, fn);
        }else{
            elem['on'+event] = fn;
        }
    }


    function apply() {
        ajax('user_id='+user_id, '/h5/creative/apply', show);
    }


    function error(msg) {
        alert(msg);
    }


    var ajax = function (data, url, callback) {
        if(window.XMLHttpRequest) {
            var ajaxObj = new XMLHttpRequest();
        }
        else {
            var ajaxObj = new ActiveXObject("Microsoft.XMLHTTP");
        }
        ajaxObj.open("POST",url,true);
        ajaxObj.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        ajaxObj.send(data);
        ajaxObj.onreadystatechange=function() {
            if(ajaxObj.readyState == 4) {
                if(ajaxObj.status == 200) {
                    var info = JSON.parse(ajaxObj.responseText);
                    if (info.status == 0)
                    {
                        callback(info.data);
                    }else {
                        error(info.message);
                    }
                }
                else {
                    if(fnfiled !== undefined) {
                        error(ajaxObj.status);
                    }
                }
            }
        };
    };

    dssdk.ready(function (sdk) {
        sdk.getUser(function (result) {
            if (typeof result == 'object' && result !== null) {
                $.post('/h5/creative/get_fans_num', {'user_id':result.user_id}, function(res){
                    if (res.data.fans_num >= 1)
                    {
                        ajax("user_id="+result.user_id, '/h5/creative', show);

                        user_id = result.user_id;
                    }
                    else {
                        var data = {status:-1, msg: '未满足粉丝数要求'};
                        show(data);
                    }
                })
            }
        });
    });

</script>
</html>
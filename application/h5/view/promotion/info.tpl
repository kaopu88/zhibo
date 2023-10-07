<include file="public/head" />
<title>申请详情</title>
<link rel="stylesheet" href="__H5__/css/common.css?{:date('YmdHis')}">
<link rel="stylesheet" href="__H5__/css/promotion.css?{:date('YmdHis')}">
<script src="__VENDOR__/jquery.min.js?v=__RV__"></script>
<script type="text/javascript" src="__H5__/js/css-base.js?v=__RV__"></script>
<script src="__VENDOR__/bugujsdk.js?{:date('YmdHis')}"></script>
<script src="__VENDOR__/layer/layer.js?{:date('YmdHis')}"></script>
</head>
<body>
<div class="body-content">
    <div class="wrap">
        <div class="switch">
            <a class="btn" href="/promotion/applyview">返回列表</a>
        </div>
        <notempty name="info">
            <div class="info">
                <div class="ls">
                    <label>申请对象:</label>
                    {$info.user_info}
                </div>
                <div class="ls">
                    <label>申请时间:</label>
                    {$info.create_time|time_format='','date'}
                </div>
                <div class="ls">
                    <label>申请说明:</label>
                    {$info.remark}
                </div>
                <div class="ls">
                    <label style="display: block;padding-bottom: 10px;">附件:</label>
                    <div class="form-group" style="padding-bottom: 0;" id="layer-photos-demo">
                        <ul class="pic_list clearfix" style="padding-bottom: 0;">
                            <volist name="info.pic_list" id="vo">
                                <li style="width: 31%;">
                                    <div class="imgs">
                                        <img src="{$vo}" class="img-responsive">
                                    </div>
                                </li>
                            </volist>
                        </ul>
                    </div>
                </div>
                <div class="ls">
                    <label>审核状态:</label>
                    <switch name="info.status">
                        <case value="0">待审核</case>
                        <case value="1">已通过</case>
                        <case value="2">已驳回</case>
                    </switch>
                </div>
                <div class="ls">
                    <label>审核时间:</label>
                    {$info.review_time|time_format='','date'}
                </div>
                <div class="ls">
                    <label>审核备注:</label>
                    {$info.reason}
                </div>
            </div>
            <else/>
            <div style="font-size: .28rem;text-align: center">
                暂无数据
            </div>
        </notempty>
    </div>
</div>
<script>
    //调用示例
    layer.photos({
        photos: '#layer-photos-demo'
        ,anim: 5 //0-6的选择，指定弹出图片动画类型，默认随机（请注意，3.0之前的版本用shift参数）
    });
</script>
</body>
</html>
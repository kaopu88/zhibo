<include file="public/head" />
<title>经纪人关系绑定</title>
<link rel="stylesheet" href="__H5__/css/common.css?{:date('YmdHis')}">
<link rel="stylesheet" href="__H5__/css/promotion.css?{:date('YmdHis')}">
<script src="__VENDOR__/jquery.min.js?v=__RV__"></script>
<script src="__VENDOR__/vue.js?v=__RV__"></script>
<script type="text/javascript" src="__H5__/js/css-base.js?v=__RV__"></script>
<script src="__VENDOR__/bugujsdk.js?{:date('YmdHis')}"></script>
<script src="__VENDOR__/layer/layer.js?{:date('YmdHis')}"></script>
<script type="text/javascript" src="__H5__/js/promotion/apply.js?{:date('YmdHis')}"></script>
</head>
<body>
<div class="body-content" id="app">

    <div class="wrap">
        <div class="switch clearfix">
            <div class="title pull-left">
                <span v-if="type == 1" >申请列表</span>
                <span v-if="type == 2" >新增申请</span>
            </div>
            <div class="pull-right">
                <a class="btn" v-if="type == 1" @click="changeType(2)">新增绑定</a>
                <a class="btn" v-if="type == 2" @click="changeType(1)">返回列表</a>
            </div>
        </div>
        <div class="list" v-if="type == 1">
            <div class="applylist">
                <div v-if="items.length > 0">
                    <div class="apply clearfix" v-for="(v,index) in items" @click="applyinfo(v.id)">

                            <div>
                                <label>申请对象:</label>{{v.nickname}}(UID:{{v.user_id}})
                            </div>
                            <div>
                                <label>申请时间:</label>{{v.create_time}}
                            </div>
                            <div>
                                <label>审核状态:</label>
                                <span v-if="v.status==0">待审核</span>
                                <span v-if="v.status==1" style="color: #32ad35;">已通过</span>
                                <span v-if="v.status==2" class="red">已拒绝</span>
                            </div>

                    </div>
                </div>
                <div v-else style="font-size: .28rem;text-align: center">
                    <a @click="changeType(2)" >暂无数据,点击新增绑定</a>
                </div>
            </div>

            <div class="more" v-if="total > offset"><a @click="getData">更多</a></div>
        </div>
        <div class="account_box" v-if="type == 2">
            <div class="account_info">
                <input placeholder="请输入用户的用户ID" type="number" class="account_input" v-model="bind_uid" @blur="checkid()" />
            </div>
            <div class="tips" v-if="bind_user" style="font-size: .24rem;color:#999;padding-bottom: 10px;">{{bind_user}}</div>
            <div class="from">
                <div class="form-group">
                    <textarea class="form-control" rows="3" placeholder="申请说明" v-model="remark"></textarea>
                </div>
                <div class="form-group" >
                    <ul class="pic_list clearfix" v-if="picslist.length > 0">
                        <li v-for="(v,index) in picslist">
                            <div class="imgs">
                                <img v-bind:src="v" class="img-responsive">
                                <div class="del" @click="delpic(index)">删除</div>
                            </div>
                        </li>
                    </ul>
                    <a class="btn" @click="uploadFile" v-show="picslist.length < 9">上传图片</a>
                </div>
            </div>
            <button class="sub" @click="apply" :disabled="isDisabled">绑定</button>
        </div>
    </div>

    <div id="applyInfo" class="info" style="display: none;">
        <div class="ls">
            <label>申请对象:</label>
            {{apply_info.user_info}}
        </div>
        <div class="ls">
            <label>申请时间:</label>
            {{apply_info.create_time}}
        </div>
        <div class="ls">
            <label>申请说明:</label>
            {{apply_info.remark}}
        </div>
        <div class="ls">
            <label style="display: block;padding-bottom: 10px;">附件:</label>
            <div class="form-group" style="padding-bottom: 0;">
                <ul class="pic_list clearfix" style="padding-bottom: 0;">
                    <li style="width: 31%;" v-for="(pic,index) in apply_info.pic_list" @click="photos(apply_info.id,index)">
                        <div class="imgs">
                            <img v-bind:src="pic" class="img-responsive">
                        </div>
                    </li>
                </ul>
            </div>
        </div>
        <div class="ls">
            <label>审核状态:</label>
            <span v-if="apply_info.status==0">待审核</span>
            <span v-if="apply_info.status==1" style="color: #32ad35;">已通过</span>
            <span v-if="apply_info.status==2" class="red">已拒绝</span>
        </div>
        <div class="ls" v-if="apply_info.status>0">
            <label>审核时间:</label>
            {{apply_info.review_time}}
        </div>
        <div class="ls" v-if="apply_info.status==2">
            <label>审核备注:</label>
            {{apply_info.reason}}
        </div>
    </div>
</div>
</body>
</html>
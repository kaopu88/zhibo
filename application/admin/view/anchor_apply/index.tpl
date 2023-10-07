<extend name="public:base_nav"/>
<block name="css">
    <style>
        .thumb .thumb_img {
            flex: none;
            width: 100px;
    </style>
</block>

<block name="js">
    <script>
        var myConfig = {
            list: [

            ]
        };
    </script>
</block>

<block name="body">
    <div class="pa_20 p-0" style="padding-bottom: 1px !important;">
        <ul class="tab_nav mt_10">
            <li><a target="_self" class="<if condition="$status eq 0"> current </if>" href="{:url('anchor_apply/index')}">全部<span unread-types="" class="badge_unread">0</span></a></li>
            <li><a target="_self" class="<if condition="$status eq 1"> current </if>" href="{:url('anchor_apply/index',['status'=>1])}">待实名审核<span unread-types="user_verified" class="badge_unread" style="display: none;">0</span></a></li>
            <li><a target="_self" class="<if condition="$status eq 4"> current </if>" href="{:url('anchor_apply/index',['status'=>4])}">待公会审核<span unread-types="" class="badge_unread">0</span></a></li>
            <li><a target="_self" class="<if condition="$status eq 3"> current </if>" href="{:url('anchor_apply/index',['status'=>3])}">待平台审核<span unread-types="" class="badge_unread">0</span></a></li>
            <li><a target="_self" class="<if condition="$status eq 2"> current </if>" href="{:url('anchor_apply/index',['status'=>2])}">已通过<span unread-types="" class="badge_unread">0</span></a></li>
            <li><a target="_self" class="<if condition="$status eq 5"> current </if>" href="{:url('anchor_apply/index',['status'=>5])}">未通过<span unread-types="" class="badge_unread">0</span></a></li>
            <li><a target="_self" class="<if condition="$status eq 6"> current </if>" href="{:url('anchor_apply/index',['status'=>6])}">待支付<span unread-types="" class="badge_unread">0</span></a></li>
        </ul>
        <div class="bg_container">
            <div class="filter_box mt_10">
                <div class="filter_nav">
                    已选择&nbsp;>&nbsp;
                    <p class="filter_selected"></p>
                </div>

                <div class="filter_options">
                    <ul class="filter_list"></ul>
                    <div class="filter_order">
                        <auth rules="admin:anchorApply:reviews">
                            <div ajax="post" ajax-url="{:url('anchorApply/reviews',['status'=>'2'])}" ajax-target="list_id"
                                 class="base_button base_button_s base_button_gray">批量审核
                            </div>
                        </auth>
                        <div class="filter_search">
                            <input placeholder="用户ID" type="text" name="user_id" value="{:input('user_id')}"/>
                            <button class="filter_search_submit">搜索</button>
                        </div>
                        <div class="clear"></div>
                    </div>
                </div>
                <input type="hidden" name="status" value="{$get.status}"/>
            </div>
            <div class="table_slide">
                <table class="content_list mt_10">
                    <thead>
                    <tr>
                        <td style="width: 5%;"><input type="checkbox" checkall="list_id" value=""/></td>
                        <td style="width: 5%;">ID</td>
                        <td style="width: 15%;">用户信息</td>
                        <td style="width: 10%;">主播类型</td>
                        <td style="width: 10%;">处理描述</td>
                        <td style="width: 15%;">审核状态</td>
                        <td style="width: 10%;">操作</td>
                    </tr>
                    </thead>
                    <tbody>
                    <notempty name="_list">
                        <volist name="_list" id="vo">
                            <tr data-id="{$vo.id}">
                                <td><input class="list_id" type="checkbox" name="ids[]" value="{$vo.id}"/></td>
                                <td>{$vo.id}</td>
                                <td>
                                    <include file="recharge_app/user_info"/>
                                </td>

                                <td><span style="color: red"><if condition="$vo.agent_id != 0">公会主播 <br>(公会名称: {$vo.agent_id|get_agent})<else/>个人主播</if></span>
                                </td>

                                <td>


                                </td>
                                <td>
                                    申请：{$vo.create_time|time_format}<br/>
                                    处理：  <switch name="vo['status']">
                                        <case value="1">
                                            <a href="javascript:;" class="fc_blue">待实名处理<span style="color: blue">(实名审核状态:{$vo.reason})</span></a>
                                        </case>
                                        <case value="2">
                                            <a href="javascript:;" class="fc_green">已通过</a>
                                        </case>
                                        <case value="3">
                                            <a href="javascript:;" class="fc_blue">待平台审核</a>
                                        </case>
                                        <case value="4">
                                            <a href="javascript:;" class="fc_blue">待公会审核<span style="color: blue">(公会审核状态:{$vo.reason})</span></a>
                                        </case>
                                        <case value="5">
                                            <a href="javascript:;" class="fc_red">未通过</a>
                                        </case>
                                        <case value="6">
                                            <a href="javascript:;" class="fc_orange">待支付</a>
                                        </case>
                                    </switch>
                                </td>
                                <td>
                                    <switch name="vo['status']">
                                        <case value="3">
                                            <a ajax="get"  <if condition="$vo['status'] eq 3">  ajax-confirm="是否通过该申请？"  class="fc_gray" <else/>   class="fc_red"</if>  href="{:url('anchor_apply/review',['id'=>$vo.id,'status'=>2])}">审核</a>
                                            <a href="javascript:;" class="reject" data-id="{$vo.id}">驳回</a>
                                        </case>
                                    </switch>
                                </td>
                            </tr>
                        </volist>
                        <else/>
                        <tr>
                            <td>
                                <div class="content_empty">
                                    <div class="content_empty_icon"></div>
                                    <p class="content_empty_text">暂未查询到相关数据</p>
                                </div>
                            </td>
                        </tr>
                    </notempty>
                    </tbody>
                </table>
            </div>
            <div class="pageshow mt_10">{:htmlspecialchars_decode($_page);}</div>
        </div>
    </div>

    <script>
        $(function () {
            new SearchList('.filter_box',myConfig);

            $('.reject').on('click', function () {
                var id = $(this).data("id");
                layer.prompt({
                    formType: 2,
                    value: '管理员驳回',
                    title: '请输入驳回理由'
                },function(val, index){
                    $.ajax({
                        type: "POST",
                        url: '/admin/anchor_apply/review',
                        data: {id: id, reason: val, status: 5},
                        dataType: "json",
                        success: function (rs) {
                            layer.close(index);
                            if( rs.status == 0 ){
                                layer.msg('驳回成功', {}, function(){
                                    location.reload();
                                });
                            }
                        }
                    });

                });
            });
        });
    </script>

</block>


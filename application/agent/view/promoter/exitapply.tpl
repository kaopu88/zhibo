<extend name="public:base_nav"/>
<block name="css">
</block>

<block name="js">
    <script>
        var list = [

        ];
        var myConfig = {
            list: list
        };
    </script>
    <script src="__VENDOR__/layer/layer.js?v=__RV__"></script>
</block>

<block name="body">
    <div class="pa_20">
        <div class="content_title">
            <h1>{$agent_last.name}</h1>
            <a href="javascript:;" class="base_button base_button_gray base_button_s history_back">返回</a>
        </div>
        <div class="filter_box mt_10">
            <div class="filter_options">
                <ul class="filter_list"></ul>
                <div class="filter_order">
                    <div ajax="post" ajax-url="{:url('promoter/exitapproved',['status'=>1])}" ajax-target="list_id" class="base_button base_button_s base_button_gray">通过</div>
                    <div class="base_button base_button_s base_button_gray rejectids">驳回</div>
                    <div class="filter_search">
                        <input placeholder="用户ID" type="text" name="user_id" value="{:input('user_id')}"/>
                        <button class="filter_search_submit">搜索</button>
                    </div>
                    <div class="clear"></div>
                </div>
            </div>
        </div>
        <div class="data_title" style="margin-top: 20px;">列表信息</div>
        <div class="table_slide">
            <table class="content_list mt_10">
                <thead>
                <tr>
                    <td style="width: 5%;"><input type="checkbox" checkall="list_id" value="{$vo.id}"/></td>
                    <td style="width: 5%;">ID</td>
                    <td style="width: 10%;">对象用户</td>
                    <td style="width: 10%;">用户申请类型</td>
                    <td style="width: 8%;">申请时间</td>
                    <td style="width: 8%;">申请备注</td>

                    <td style="width: 8%;">状态</td>
                    <td style="width: 9%;">操作</td>
                </tr>
                </thead>
                <tbody>
                <notempty name="_list">
                    <volist name="_list" id="vo">
                        <tr data-id="{$vo.id}">
                            <td><input class="list_id" type="checkbox" name="ids[]" value="{$vo.id}"/></td>
                            <td>{$vo.id}</td>
                            <td>
                                <include link="{:url('anchor/detail',['user_id'=>$vo.user_id])}" file="user/user_info"/>
                            </td>
                            <td>
                                <switch name="vo.is_anchor">
                                    <case value="0">用户</case>
                                    <case value="1"><span class="fc_blue">主播</span></case>
                                </switch>
                            </td>
                            <td>{$vo.create_time|time_format}</td>
                            <td>{$vo.remark}</td>

                            <td>
                                <switch name="vo.status">
                                    <case value="0">待审核</case>
                                    <case value="1"><span class="fc_green">已通过</span></case>
                                    <case value="2"><span class="fc_red">已驳回</span></case>
                                </switch>
                            </td>
                            <td>
                                <eq name="vo.status" value="0">
                                    <a  <if condition="$vo['user_verified'] neq 1">href="javascript:;"  class="fc_gray" <else/>  ajax="get" ajax-confirm="是否通过用户退会该申请？"   class="fc_red"   href="{:url('exitreview',['id'=>$vo.id,'status'=>1])}"</if>>通过</a>
                                    <a href="javascript:;" class="reject" data-id="{$vo.id}">驳回</a>
                                </eq>
                                <gt name="vo.status" value="0">
                                    审核时间:{$vo.review_time|time_format}
                                </gt>
                                <eq name="vo.status" value="2">
                                    <br>
                                    理由:{$vo.reason}
                                </eq>
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
    <script>
        $(function () {
            new SearchList('.filter_box', myConfig);

            $('.reject').on('click', function () {
                var id = $(this).data("id");
                layer.prompt({
                    formType: 2,
                    value: '管理员驳回',
                    title: '请输入驳回理由'
                },function(val, index){
                    $.ajax({
                        type: "POST",
                        url: '/agent/promoter/exitreview',
                        data: {id: id, reason: val, status: 2},
                        dataType: "json",
                        success: function (rs) {
                            if( rs.status == 0 ){
                                layer.msg(rs.message, {}, function(){
                                    location.reload();
                                });
                            } else {
                                layer.msg(rs.message);
                            }
                        }
                    });

                });
            });

            $('.rejectids').on('click', function () {
                var ids = [];
                $("input[name='ids[]']").each(function() {
                    if ($(this).is(":checked")) {
                        ids.push($(this).val());
                    }
                });

                if( ids.length < 1 ){
                    layer.msg('请选择一条审核记录');
                    return false;
                }

                layer.prompt({
                    formType: 2,
                    value: '管理员驳回',
                    title: '请输入驳回理由'
                },function(val, index){
                    $.ajax({
                        type: "POST",
                        url: '/agent/promoter/exitapproved',
                        data: {ids: ids, reason: val, status: 2},
                        dataType: "json",
                        success: function (rs) {
                            console.log(rs);
                            layer.close(index);
                            if( rs.status == 0 ){
                                layer.msg(rs.message, {}, function(){
                                    location.reload();
                                });
                            } else {
                                layer.msg(rs.message);
                            }
                        }
                    });

                });
            });
        });
    </script>
</block>
<block name="layer">
</block>
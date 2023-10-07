<extend name="public:base_nav"/>
<block name="css">
</block>

<block name="js">
</block>

<block name="body">
    <div class="pa_20 p-0">

        <include file="components/tab_nav"/>
        <div class="bg_container">
            <div class="table_slide">
                <table class="content_list mt_10 md_width">
                    <thead>
                    <tr>
                        <td style="width: 5%;"><input type="checkbox" checkall="list_id"/></td>
                        <td style="width: 5%;">ID</td>
                        <td style="width: 10%;">审核单号</td>
                        <td style="width: 15%;">充值账号</td>
                        <td style="width: 10%;">申请人</td>
                        <td style="width: 10%;">充值金额</td>
                        <td style="width: 15%;">付款信息</td>
                        <td style="width: 10%;">审核状态</td>
                        <td style="width: 10%;">申请时间</td>
                        <td style="width: 10%;">操作</td>
                    </tr>
                    </thead>
                    <tbody>
                    <notempty name="_list">
                        <volist name="_list" id="vo">
                            <tr data-id="{$vo.id}">
                                <td><input class="list_id" type="checkbox" name="ids[]" value="{$vo.id}"/></td>
                                <td>{$vo.id}</td>
                                <td>{$vo.order_no}</td>
                                <td>
                                    <include file="recharge_app/user_info"/>
                                </td>
                                <td>
                                    {$vo.app_admin.id}<br/>
                                    {$vo.app_admin|user_name}
                                </td>
                                <td>
                                    人民币：{$vo.total_fee}<br/>
                                    {:APP_BEAN_NAME}：{$vo.bean}<br/>
                                    {$vo.total_fee|number_2_rmb}
                                </td>
                                <td><include file="recharge_app/pay_info"/></td>
                                <td><include file="components/audit_status"/></td>
                                <td>
                                    申请：{$vo.create_time|time_format}<br/>
                                    处理：{$vo.audit_time|time_format='未处理'}
                                </td>
                                <td>
                                    <switch name="vo['audit_status']">
                                        <case value="0">
                                            <auth rules="admin:recharge_app:audit">
                                                <a data-id="id:{$vo.id}" poplink="recharge_app_handler"
                                                href="javascript:;">处理</a><br/>
                                            </auth>
                                            <a data-query="id={$vo.id}&type=audit_recharge" poplink="task_transfer_box"
                                            href="javascript:;">转交</a>
                                        </case>
                                        <case value="1">
                                        </case>
                                        <case value="2">
                                            原因：{$vo.audit_remark}
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
        </div>
        <div class="pageshow mt_10">{:htmlspecialchars_decode($_page);}</div>
    </div>
</block>

<block name="layer">
    <include file="recharge_app/recharge_app_handler"/>
    <include file="components/task_transfer_box"/>
</block>
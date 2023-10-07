<extend name="public:base_nav"/>
<block name="css">
</block>

<block name="js">
</block>

<block name="body">
    <div class="pa_20">

        <div class="content_title">
            <h1>{$admin_last.name}</h1>
            <a href="javascript:;" class="base_button base_button_gray base_button_s history_back">返回</a>
        </div>

        <div class="content_toolbar mt_10">
            <div class="content_toolbar_search">
                <div class="base_group">

                    <div class="modal_select modal_select_s">
                        <span class="modal_select_text"></span>
                        <input name="audit_status" type="hidden" class="modal_select_value finder"
                               value="{:input('audit_status')}"/>
                        <ul class="modal_select_list">
                            <li class="modal_select_option" value="">全部</li>
                            <li class="modal_select_option" value="0">待审核</li>
                            <li class="modal_select_option" value="1">已通过</li>
                            <li class="modal_select_option" value="2">未通过</li>
                        </ul>
                    </div>
                    <input name="keyword" class="base_text base_text_s finder" style="width:180px;"
                           value="{:input('keyword')}" placeholder="搜索充值账号"/>
                    <a href="javascript:;" type="button" class="base_button base_button_s mr_10 finder">搜索</a>
                </div>
            </div>
        </div>
        <div class="table_slide">
        <table class="content_list mt_10">
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
                        <td>
                            <include file="recharge_app/pay_info"/>
                        </td>
                        <td>
                            <include file="components/audit_status"/>
                            <br/>
                            <notempty name="vo['audit_admin']">
                                <a admin-id="{$vo.audit_admin.id}" href="javascript:;">{$vo.audit_admin|user_name}</a>
                                <else/>
                                未分配
                            </notempty>
                        </td>
                        <td>
                            申请：{$vo.create_time|time_format}<br/>
                            处理：{$vo.audit_time|time_format='未处理'}
                        </td>
                        <td></td>
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
        new FinderController('.finder', '');
    </script>

</block>

<block name="layer">
    <include file="recharge_app/recharge_app_handler"/>
    <include file="components/task_transfer_box"/>
</block>
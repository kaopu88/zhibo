<extend name="public:base_nav"/>
<block name="css">

</block>

<block name="js">
    <script>
        var list = [
            {
                name: 'audit_status',
                title: '状态',
                opts: [
                    {name: '待审核', value: '0'},
                    {name: '已结算', value: '1'},
                    {name: '无效', value: '2'}
                ]
            }
        ];
        var myConfig = {
            list: list
        };
    </script>
</block>

<block name="body">
    <div class="pa_20">

        <div class="content_title">
            <h1>{$agent_last.name}</h1>
            <a href="javascript:;" class="base_button base_button_gray base_button_s history_back">返回</a>
        </div>
        <div class="filter_box mt_10">
            <div class="filter_nav">
                已选择&nbsp;>&nbsp;
                <p class="filter_selected"></p>
            </div>

            <div class="filter_options">
                <ul class="filter_list"></ul>

            </div>
            <input type="hidden" name="verify_status" value="{:input('audit_status')}"/>
        </div>
        <div class="table_slide">
            <table class="content_list mt_10 table_fixed">
                <thead>
                <tr>
                    <td style="width: 5%;"><input type="checkbox" checkall="list_id" value="{$vo.id}"/></td>
                    <td style="width: 5%;">ID</td>
                    <td style="width: 20%;">订单详情</td>
                    <td style="width: 10%;">结算收益</td>
                    <td style="width: 10%;">结算金额</td>
                    <td style="width: 10%;">扣除金额(主播部分)</td>
                    <td style="width: 5%;">实际结算金额</td>
                    <td style="width: 10%;">结算状态</td>
                    <td style="width: 10%;">结算时间</td>
                </tr>
                </thead>

                <tbody>
                <notempty name="list">
                    <volist name="list" id="vo">
                        <tr data-id="{$vo.id}">
                            <td><input class="list_id" type="checkbox" name="ids[]" value="{$vo.id}"/></td>
                            <td>{$vo.id}</td>
                            <td>订单号：{$vo.cash_no}</td>
                            <td>{$vo.millet}</td>
                            <td>{$vo.old_rmb}</td>
                            <td>{$vo.deduction_millet}</td>
                            <td>{$vo.rmb}</td>

                            <td>
                                <switch name="vo['audit_status']">
                                    <case value="2">
                                        <span class="fc_red">已拒绝 (原因：{$vo.admin_remark})</span>
                                    </case>
                                    <case value="1">
                                        <span class="fc_green" style="color: #32ad35">已结算</span><br/>
                                    </case>
                                    <case value="0">
                                        <span class="fc_blue">结算中</span><br/>
                                    </case>
                                </switch>
                            </td>
                            <td>
                                {$vo.create_time|time_format}<br/>
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
        new SearchList('.filter_box', myConfig);
    </script>

</block>
<block name="layer">
</block>
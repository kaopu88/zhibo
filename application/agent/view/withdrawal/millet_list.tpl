<div class="table_slide">
    <table class="content_list mt_10 table_fixed">
    <thead>
    <tr>
        <td style="width: 5%;"><input type="checkbox" checkall="list_id" value="{$vo.id}"/></td>
        <td style="width: 5%;">ID</td>

        <td style="width: 20%;">订单详情</td>
        <td style="width: 10%;">提现金额</td>
        <td style="width: 10%;">打款金额</td>
        <td style="width: 5%;">手续费</td>
        <td style="width: 5%;">税费</td>
        <td style="width: 15%;">提现账户</td>
        <td style="width: 10%;">提现状态</td>
        <td style="width: 10%;">提现时间</td>

    </tr>
    </thead>
    <tbody>
    <notempty name="list">
        <volist name="list" id="vo">
            <tr data-id="{$vo.id}">
                <td><input class="list_id" type="checkbox" name="ids[]" value="{$vo.id}"/></td>
                <td>{$vo.id}</td>
                <td>订单号：{$vo.descr}</td>
                <td>{$vo.millet}</td>
                <td>{$vo.rmb}</td>
                <td>{$vo.cash_fee}</td>
                <td>{$vo.cash_taxes}</td>
                <td>
                    <switch name="vo['casy_type']">
                        <case value="0">
                            <span class="fc_blue">支付宝</span><br/>
                        </case>
                        <case value="1">
                            <span class="fc_blue">微信</span><br/>
                        </case>
                        <case value="2">
                            <span class="fc_blue">银行卡</span><br/>
                        </case>

                    </switch>
                    姓名:{$vo.name}<br/>
                    联系方式:{$vo.contact_phone}<br/>
                    账户:{$vo.account}<br/>
                    <eq name="vo['casy_type']" value="2">
                        开户行:{$vo.card_name}<br/>
                    </eq>
                </td>
                <td>
                    <switch name="vo['audit_status']">
                        <case value="2">
                            <span class="fc_red">已拒绝 (原因：{$vo.admin_remark})</span>
                        </case>
                        <case value="1">
                            <span class="fc_green" style="color: #32ad35">已结算</span><br/>
                        </case>
                        <case value="0">
                            <span class="fc_blue">申请中</span><br/>
                        </case>
                    </switch>
                </td>
                <td>
                    申请：{$vo.create_time|time_format}<br/>
                    <notempty name="$vo.handler_time">处理：{$vo.handler_time|time_format='未处理'}</notempty>
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

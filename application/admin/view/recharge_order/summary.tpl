
<div class="data_block mt_10">
    <div class="data_title">汇总信息</div>
    <div class="table_slide">
        <table class="content_list sm_width">
        <thead>
        <tr>
            <td style="width: 10.28%;">支付宝</td>
            <td style="width: 10.28%;">支付宝手机页面</td>
            <td style="width: 10.28%;">微信支付</td>
            <td style="width: 10.28%;">微信公众号</td>
            <td style="width: 10.28%;">微信网页支付</td>
            <td style="width: 10.28%;">系统赠送</td>
            <td style="width: 10.28%;">Apple支付</td>
            <td style="width: 10.28%;">系统结算</td>
            <td style="width: 12.28%;">合计(单位：元)</td>
        </tr>
        </thead>
        <tbody>
        <notempty name="recharge_list">
            <tr class="today_data_tr">
                <td>{$summary.alipay_app|default=0}</td>
                <td>{$summary.alipay_wap|default=0}</td>
                <td>{$summary.wxpay_app|default=0}</td>
                <td>{$summary.wxpay_h5|default=0}</td>
                <td>{$summary.wxpay_wxwap|default=0}</td>
                <td>{$summary.system_free|default=0}</td>
                <td>{$summary.applepay_app|default=0}</td>
                <td>{$summary.system_pay|default=0}</td>
                <td>{$summary.summary|number_format='2'|default=0}</td>
            <else/>
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


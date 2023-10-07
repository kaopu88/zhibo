
<div class="data_block mt_10">
    <div class="data_title">汇总信息</div>
    <div class="table_slide">
        <table class="content_list xs_width">
        <thead>
        <tr>
            <td style="width: 14.28%;">已打款</td>
            <td style="width: 14.28%;">申请中</td>
            <td style="width: 14.28%;">拒绝</td>
        </tr>
        </thead>
        <tbody>
        <notempty name="cash_list">
            <tr class="today_data_tr">
                <td>{$summary.summary.success|default=0}</td>
                <td>{$summary.summary.wait|default=0}</td>
                <td>{$summary.summary.failed|default=0}</td>
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


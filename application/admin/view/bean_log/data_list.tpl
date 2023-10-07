<div class="table_slide">
<table class="content_list mt_10">
    <thead>
    <tr>
        <td style="width: 5%;">ID</td>
        <td style="width: 15%;">用户</td>
        <td style="width: 10%;">流水单号</td>
        <td style="width: 15%;">交易内容</td>
        <td style="width: 10%;">{:APP_BEAN_NAME}总额</td>
        <td style="width: 10%;">冻结{:APP_BEAN_NAME}</td>
        <td style="width: 10%;">剩余{:APP_BEAN_NAME}</td>
        <td style="width: 10%;">客户端内容</td>
        <td style="width: 15%;">创建时间</td>
    </tr>
    </thead>
    <tbody>
    <notempty name="_list">
        <volist name="_list" id="vo">
            <tr data-id="{$vo.id}">
                <td>{$vo.id}</td>
                <td><include file="recharge_app/user_info"/></td>
                <td>
                    {$vo.log_no}
                </td>
                <td>
                    交易类型：{$vo.trade_type|enum_name='trade_types'}<br/>
                    交易单号：{$vo.trade_no}<br/>
                    数额：<span class="{$vo.type=='inc' ? 'fc_green' : 'fc_red'}">{$vo.type=='inc' ? '+' : '-'}{$vo.total}</span>
                </td>
                <td>
                    交易前：{$vo.last_total_bean}<br/>
                    交易后：{$vo.total_bean}
                </td>
                <td>
                    交易前：{$vo.last_fre_bean}<br/>
                    交易后：{$vo.fre_bean}
                </td>
                <td>
                    交易前：{$vo.last_bean}<br/>
                    交易后：{$vo.bean}
                </td>
                <td>
                    客户端IP：{$vo.client_ip}<br/>
                    客户端版本：{$vo.app_v}
                </td>
                <td>
                    {$vo.create_time|time_format='Y-m-d H:i'}
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
<div class="pageshow async_container_pages mt_10">{:htmlspecialchars_decode($_page);}</div>
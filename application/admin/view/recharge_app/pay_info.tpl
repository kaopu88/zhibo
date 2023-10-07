<if condition="$vo.pay_method eq 'cash'">
    [现金]
    <else/>
    [{$vo.pay_method|enum_attr=recharge_pay_methods,###}]<br/>
    <notempty name="vo['pay_name']">{$vo.pay_name}<br/></notempty>
    <notempty name="vo['pay_account']">{$vo.pay_account}</notempty>
</if>
<notempty name="vo['remark']"><br/><span class="fc_gray">备注：{$vo.remark}</span></notempty>
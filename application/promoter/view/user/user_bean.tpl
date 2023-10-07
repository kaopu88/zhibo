<eq name="vo['pay_status']" value="1">
    <span class="icon-credit"></span>&nbsp;<span>{$vo.bean}</span><br/>
    <else/>
    <span class="icon-credit"></span>&nbsp;<span title="支付功能已禁用" class="fc_red">{$vo.bean}</span><br/>
</eq>
<span class="fc_gray">  <span class="icon-lock"></span>&nbsp;{$vo.fre_bean}</span>
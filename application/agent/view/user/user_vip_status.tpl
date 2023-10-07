<b>{$vo.city_info.name|default='未知'}</b><br/>
<switch name="vo['vip_status']">
    <case value="0">
        <span class="fc_gray">{$vo.vip_expire_str}</span>
    </case>
    <case value="1">
        <span class="fc_green">{$vo.vip_expire_str}</span>
    </case>
    <case value="2">
        <span class="fc_red">{$vo.vip_expire_str}</span>
    </case>
</switch>
<br/>
<eq name="vo['verified']" value="1">
    <span class="fc_green">已认证</span>
    <else/>
    <span class="fc_gray">未认证</span>
</eq>

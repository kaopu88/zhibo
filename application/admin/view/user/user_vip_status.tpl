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

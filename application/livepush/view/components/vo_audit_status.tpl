<switch name="vo['audit_status']">
    <case value="0">
        <span class="fc_gray">处理中</span>
    </case>
    <case value="1">
        <span class="fc_black">审核中</span>
    </case>
    <case value="2">
        <span class="fc_green">已通过</span>
    </case>
    <case value="3">
        <span class="fc_red">未通过</span>
        <notempty name="vo['reason']">
            <a class="video_reason" data-reason="{$vo.reason|htmlspecialchars}" data-id="{$vo.id}" href="javascript:;">原因</a>
        </notempty>
    </case>
</switch>
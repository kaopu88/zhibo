<div class="user_agent_info">
    <if condition="!$vo['agent_info']">
        直属用户<br/>
        <else/>
        <notempty name="vo['agent_info']">
            {:config('app.agent_setting.agent_name')}:[{$vo.agent_info.id}]{$vo.agent_info.name}<br/>
        </notempty>
    </if>
    <notempty name="vo['promoter_info']">
        {:config('app.agent_setting.promoter_name')}:[{$vo.promoter_info.user_id}]{$vo.promoter_info|user_name}
    </notempty>
</div>
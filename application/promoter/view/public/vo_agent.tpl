<div class="user_agent_info">
    <notempty name="vo['promoter_info']">
        {:config('app.agent_setting.promoter_name')}:[{$vo.promoter_info.user_id}]{$vo.promoter_info|user_name}
        <else/>
        无
    </notempty>
</div>
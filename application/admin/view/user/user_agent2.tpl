<div class="user_agent_info">
    <gt name="vo['agent_num']" value="0">
        <notempty name="vo['agent_list']">
            <volist name="vo['agent_list']" id="fo">
                {$fo.agent_name}<br/>
            </volist>
        </notempty>
        <notempty name="vo['promoter_info']">
            <br/>当前{:config('app.agent_setting.promoter_name')}:[{$vo.promoter_info.user_id}]{$vo.promoter_info.nickname}
        </notempty>
        <else/>
        <notempty name="vo['agent_info']">
            [{$vo['agent_info']['id']}]{$vo['agent_info']['name']}
            <else/>
            无公会
        </notempty>
    </gt>
</div>
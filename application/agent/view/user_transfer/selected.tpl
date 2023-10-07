<div class="content_title2">正在转移的{$call_name}</div>
<switch name="rel">
    <case value="agent">
        <div class="mt_10 fc_red">
            {:config('app.agent_setting.agent_name')} [{$_info.id}]&nbsp;&nbsp;{$_info.name}的所有{$call_name}（共计：{$_info.rel_num}名），由系统异步处理，所以需要等待1~5分钟。
        </div>
    </case>
    <case value="promoter">
        <div class="fc_red mt_10">
            {:config('app.agent_setting.promoter_name')} [{$_info.user_id}]&nbsp;&nbsp;{$_info.nickname}的所有{$call_name}（共计：{$_info.rel_num}名），由系统异步处理，所以需要等待1~5分钟。</div>
    </case>
    <default/>
    <include file="user_transfer/user_list"/>
</switch>
<extend name="public:base_nav"/>
<block name="css">
</block>

<block name="js">
    <script>
        var myConfig = {
            list: [
                {
                    name: 'transfer_type',
                    title: '转移类型',
                    opts: [
                        {name: '用户', value: 'user'},
                        {name: '{:config('app.agent_setting.promoter_name')}', value: 'promoter'}
                    ]
                }
            ]
        };
    </script>
</block>

<block name="body">
    <div class="pa_20">

        <div class="content_title">
            <h1>{$admin_last.name}</h1>
            <a href="javascript:;" class="base_button base_button_gray base_button_s history_back">返回</a>
        </div>
        <div class="filter_box mt_10">
            <div class="filter_nav">
                已选择&nbsp;>&nbsp;
                <p class="filter_selected"></p>
            </div>
            <div class="filter_options">
                <ul class="filter_list"></ul>
                <div class="filter_order">
                    <div class="filter_search">
                        <input placeholder="用户ID" type="text" name="user_id" value="{:input('user_id')}"/>
                        <button class="filter_search_submit">搜索</button>
                    </div>
                    <div class="clear"></div>
                </div>
            </div>
            <input type="hidden" name="transfer_type" value="{:input('transfer_type')}"/>
        </div>
        <div class="table_slide">
        <table class="content_list mt_10">
            <thead>
            <tr>
                <td style="width: 5%;">ID</td>
                <td style="width: 20%;">用户信息</td>
                <td style="width: 15%;">{:config('app.agent_setting.agent_name')}ID</td>
                <td style="width: 15%;">{:config('app.agent_setting.promoter_name')}ID</td>
                <td style="width: 15%;">管理员</td>
                <td style="width: 15%;">groupKEY</td>
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
                            转移前：{$vo.old_agent_id}<br/>
                            转移后：{$vo.agent_id}
                        </td>
                        <td>
                            转移前：{$vo.old_promoter_uid}<br/>
                            转移后：{$vo.promoter_uid}
                        </td>
                        <td>
                            {$vo.audit_admin|user_name}
                        </td>
                        <td>
                            {$vo.group_key}
                        </td>
                        <td>
                            {$vo.create_time|time_format='无'}
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
        <div class="pageshow mt_10">{:htmlspecialchars_decode($_page);}</div>
    </div>

    <script>
        $(function () {
            new SearchList('.filter_box',myConfig);
        });
    </script>

</block>
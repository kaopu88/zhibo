<extend name="public:base_nav"/>
<block name="css">
</block>

<block name="js">
    <script>
        var list = [
            {
                name: 'gender',
                title: '性别',
                opts: [
                    {name: '男', value: '1'},
                    {name: '女', value: '2'},
                    {name: '保密', value: '0'}
                ]
            }
        ];
        var add_sec = '{$agent.add_sec}';
        var is_root = '{$is_root}';
        if (add_sec=='1' && is_root=='1'){
            var item = {
                name: 'agent_id',
                title: '所属{:config("app.agent_setting.agent_name")}',
                get: '{:url("kpi_cons/get_agent")}'
            };
            list.push(item);
        }
        var myConfig = {
            list: list
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
                        <eq name="is_root" value="1">
                        <input placeholder="所属{:config('app.agent_setting.promoter_name')}ID" type="text" name="promoter_uid" value="{:input('promoter_uid')}"/>
                        </eq>
                        <input placeholder="新用户昵称或ID" type="text" name="keyword" value="{:input('keyword')}"/>
                        <button class="filter_search_submit">搜索</button>
                    </div>
                    <div class="clear"></div>
                </div>
            </div>
            <input type="hidden" name="gender" value="{$get.gender}"/>
            <input type="hidden" name="agent_id" value="{$get.agent_id}"/>
        </div>
        <include file="kpi_fans/fans_list"/>
        <div class="pageshow mt_10">{:htmlspecialchars_decode($_page);}</div>
    </div>
    <script>
        new SearchList('.filter_box', myConfig);
    </script>
</block>
<block name="layer">
</block>
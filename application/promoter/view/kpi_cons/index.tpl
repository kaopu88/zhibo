<extend name="public:base_nav"/>
<block name="css">
</block>

<block name="js">
    <script>
        var list = [
            {
                name: 'rel_type',
                title: '消费类型',
                opts: JSON.parse('{:json_encode(enum_array("bean_trade_types"))}')
            },
        ];
        var myConfig = {
            list: list
        };
    </script>
</block>

<block name="body">
    <div class="pa_20">
        <div class="content_title">
            <h1>{$promoter_last.name}</h1>
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
                        <input placeholder="所属{:config('app.agent_setting.promoter_name')}ID" type="text" name="promoter_uid" value="{:input('promoter_uid')}"/>
                        <input placeholder="ID、单号" type="text" name="keyword" value="{:input('keyword')}"/>
                        <button class="filter_search_submit">搜索</button>
                    </div>
                    <div class="clear"></div>
                </div>
            </div>
            <input type="hidden" name="rel_type" value="{$get.rel_type}"/>
            <input type="hidden" name="agent_id" value="{$get.agent_id}"/>
        </div>
        <include file="kpi_cons/cons_list"/>
        <div class="pageshow mt_10">{:htmlspecialchars_decode($_page);}</div>
    </div>
    <script>
        new SearchList('.filter_box', myConfig);
    </script>
</block>
<block name="layer">
</block>
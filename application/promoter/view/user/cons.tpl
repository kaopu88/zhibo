<extend name="public:base_nav"/>
<block name="css">
</block>

<block name="js">
    <script>
        var myConfig = {
            list: [
                {
                    name: 'rel_type',
                    title: '消费类型',
                    opts: JSON.parse('{:json_encode(enum_array("bean_trade_types"))}')
                }
            ]
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
                        <input placeholder="ID、单号" type="text" name="keyword" value="{:input('keyword')}"/>
                        <button class="filter_search_submit">搜索</button>
                    </div>
                    <div class="clear"></div>
                </div>
            </div>
            <input type="hidden" name="rel_type" value="{:input('rel_type')}"/>
            <input type="hidden" name="pay_platform" value="{:input('pay_platform')}"/>
        </div>
        <include file="user/cons_list"/>
        <div class="pageshow mt_10">{:htmlspecialchars_decode($_page);}</div>
    </div>
    <script>
        new SearchList('.filter_box',myConfig);
    </script>
</block>

<block name="layer">
    <include file="user/reg_pop"/>
    <include file="user/import_pop"/>
</block>
<extend name="public:base_nav"/>
<block name="css">

</block>

<block name="js">
    <script>
        var list = [
            {
                name: 'type',
                title: '类型',
                opts: [
                    {name: '收入', value: 'inc'},
                    {name: '支出', value: 'exp'},
                ]
            },
            {
                name: 'trade_type',
                title: '交易类型',
                opts: JSON.parse('{:json_encode(enum_array("agent_price_trade_type"))}')
            }
        ];
        var myConfig = {
            list: list
        };
    </script>
</block>

<block name="body">
    <div class="pa_20">

        <div class="content_title">
            <h1>{$agent_last.name}</h1>
            <a href="javascript:;" class="base_button base_button_gray base_button_s history_back">返回</a>
        </div>
        <div class="filter_box mt_10">
            <div class="filter_nav">
                已选择&nbsp;>&nbsp;
                <p class="filter_selected"></p>
            </div>

            <div class="filter_options">
                <ul class="filter_list"></ul>
            </div>

            <input type="hidden" name="type" value="{:input('type')}"/>
            <input type="hidden" name="trade_type" value="{:input('trade_type')}"/>
        </div>
        <div class="table_slide">
        <table class="content_list mt_10">
            <thead>
            <tr>
                <td style="width: 5%;"><input type="checkbox" checkall="list_id" value=""/></td>
                <td style="width: 10%;">流水单号</td>
                <td style="width: 10%;">交易内容</td>
                <td style="width: 10%;">变动余额</td>
                <td style="width: 10%;">变动前余额</td>
                <td style="width: 10%;">变动后余额</td>
                <td style="width: 10%;">备注</td>
                <td style="width: 10%;">变动时间</td>
            </tr>
            </thead>
            <tbody>
            <notempty name="list">
                <volist name="list" id="vo">
                    <tr data-id="{$vo.id}">
                        <td><input class="list_id" type="checkbox" name="ids[]" value="{$vo.id}"/></td>
                        <td>{$vo.log_no}</td>
                        <td>
                            交易类型：{$vo.trade_type|enum_name='agent_price_trade_type'}<br/>
                            交易单号：{$vo.trade_no}<br>
                            数额：<span class="{$vo.type=='inc' ? 'fc_green' : 'fc_red'}">{$vo.type=='inc' ? '+' : '-'}{$vo.total}</span>
                        </td>
                        <td>{$vo.total}</td>
                        <td>{$vo.last_price}</td>
                        <td>{$vo.price}</td>
                        <td>{$vo.remark}</td>
                        <td>{$vo.create_time|time_format}</td>
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
        new SearchList('.filter_box', myConfig);
    </script>

</block>
<block name="layer">
</block>
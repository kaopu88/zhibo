<extend name="public:base_nav"/>
<block name="css">
</block>

<block name="js">
    <script>
        var myConfig = {
            list: [
                {
                    name: 'type',
                    title: '类型',
                    opts: [
                        {name: '收入', value: 'inc'},
                        {name: '支出', value: 'exp'}
                    ]
                },
                {
                    name: 'trade_type',
                    title: '交易类型',
                    opts: JSON.parse('{:json_encode(enum_array("trade_types"))}')
                }
            ]
        };
    </script>
    <script src="__JS__/bean_log/index.js?v=__RV__"></script>
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
                    <notempty name="total_amount"><a href="javascipt:;">总计：{$total_amount}</a></notempty>
                    <div class="filter_search">
                        <input readonly="readonly" placeholder="开始时间" name="start_time" value="{:input('start_time')}" type="text" class="base_text flatpickr-input">
                        <input readonly="readonly" placeholder="结束时间" name="end_time" value="{:input('end_time')}" type="text" class="base_text flatpickr-input">
                        <input placeholder="用户ID" type="text" name="user_id" value="{:input('user_id')}"/>
                        <input placeholder="ID、流水单号、交易单号" type="text" name="keyword" value="{:input('keyword')}" style="width: 166px;"/>
                        <button class="filter_search_submit">搜索</button>
                    </div>
                    <div class="clear"></div>
                </div>
            </div>
            <input type="hidden" name="type" value="{$get.type}"/>
            <input type="hidden" name="trade_type" value="{$get.trade_type}"/>
        </div>

        <div class="async_container">

        </div>
    </div>

    <script>
        var startTime = $('[name=start_time]').flatpickr({
            dateFormat: 'Y-m-d',
            onChange: function (dateObj, dateStr, instance) {
                endTime.set('minDate', dateStr);
            }
        });
        var endTime = $('[name=end_time]').flatpickr({
            dateFormat: 'Y-m-d',
            onChange: function (dateObj, dateStr, instance) {
                startTime.set('maxDate', dateStr);
            }
        });
    </script>

</block>

<block name="layer">
    <include file="recharge_app/recharge_app_handler"/>
    <include file="components/task_transfer_box"/>
</block>
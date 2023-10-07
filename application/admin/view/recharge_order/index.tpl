<extend name="public:base_nav"/>
<block name="css">
</block>

<block name="js">
    <script>
        var myConfig = {
            list: [
                {
                    name: 'pay_method',
                    title: '支付方式',
                    opts: JSON.parse('{:json_encode(enum_array("pay_methods"))}')
                },
                {
                    name: 'pay_status',
                    title: '支付状态',
                    opts: [
                        {name: '已支付', value: '1'},
                        {name: '未支付', value: '0'}
                    ]
                },
                {
                    name: 'isvirtual',
                    title: '虚拟充值',
                    opts: [
                        {name: '虚拟', value: '1'},
                        {name: '正常', value: '0'}
                    ]
                },
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
                    <input readonly="readonly" placeholder="开始时间" name="start_time" value="{:input('start_time')}" type="text" class="base_text flatpickr-input">
                        <input readonly="readonly" placeholder="结束时间" name="end_time" value="{:input('end_time')}" type="text" class="base_text flatpickr-input">
                        <input placeholder="支付号" type="text" name="third_trade_no" value="{:input('third_trade_no')}"/>
                        <input placeholder="订单号" type="text" name="order_no" value="{:input('order_no')}"/>
                        <input placeholder="用户ID、用户呢称、手机号" type="text" name="keyword" value="{:input('keyword')}"/>
                        <button class="filter_search_submit">搜索</button>
                    </div>
                    <div class="clear"></div>
                </div>
            </div>
            <input type="hidden" name="isvirtual" value="{:input('isvirtual')}"/>
            <input type="hidden" name="pay_method" value="{:input('pay_method')}"/>
            <input type="hidden" name="pay_status" value="{:input('pay_status')}"/>
        </div>
        <include file="recharge_order/summary"/>
        <div class="data_title" style="margin-top: 20px;">列表信息</div>
        <include file="recharge_order/recharge_list"/>
        <div class="pageshow mt_10">{:htmlspecialchars_decode($_page);}</div>
    </div>
    <script>
        new SearchList('.filter_box', myConfig);
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
</block>
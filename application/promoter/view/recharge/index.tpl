<extend name="public:base_nav"/>
<block name="css">
</block>

<block name="js">
    <script>
        var list = [
            {
                name: 'pay_method',
                title: '支付方式',
                opts: JSON.parse('{:json_encode(enum_array("pay_methods"))}')
            },
            /* {
                 name: 'pay_status',
                 title: '支付方式',
                 opts: [
                        {name: '已支付', value: '1'},
                        {name: '未支付', value: '0'}
                    ]
            },*/
        ];
        var add_sec = '{$agent.add_sec}';
        var is_root = '{$is_root}';
        if (add_sec=='1' && is_root=='1'){
            var item = {
                name: 'agent_id',
                title: '所属{:config('app.agent_setting.agent_name')}',
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
                        <input readonly="readonly" placeholder="开始时间" name="start_time" value="{:input('start_time')}" type="text" class="base_text flatpickr-input">
                        <input readonly="readonly" placeholder="结束时间" name="end_time" value="{:input('end_time')}" type="text" class="base_text flatpickr-input">
                        <input placeholder="订单号" type="text" name="order_no" value="{:input('order_no')}"/>
                        <input placeholder="用户ID、昵称、手机号" type="text" name="keyword" value="{:input('keyword')}"/>
                        <button class="filter_search_submit">搜索</button>
                    </div>
                    <div class="clear"></div>
                </div>
            </div>
            <input type="hidden" name="pay_method" value="{:input('pay_method')}"/>
            <input type="hidden" name="pay_status" value="{:input('pay_status')}"/>
            <input type="hidden" name="agent_id" value="{:input('agent_id')}"/>
        </div>
        <include file="recharge/summary"/>
        <div class="data_title" style="margin-top: 20px;">列表信息</div>
        <include file="recharge/recharge_list"/>
        <div class="pageshow mt_10">{:htmlspecialchars_decode($_page);}</div>
    </div>
    <script>
        new SearchList('.filter_box', myConfig);
    </script>
</block>
<block name="layer">
</block>
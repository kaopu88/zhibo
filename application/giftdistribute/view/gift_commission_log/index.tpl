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
                    opts: JSON.parse('{:json_encode(enum_array("giftdistribute_trade_types"))}')
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


            <div class="table_slide">
                <table class="content_list mt_10">
                    <thead>
                    <tr>
                        <td style="width: 5%;">ID</td>
                        <td style="width: 15%;">接受用户</td>
                        <td style="width: 15%;">贡献用户</td>
                        <td style="width: 10%;">流水单号</td>
                        <td style="width: 15%;">交易内容</td>
                        <td style="width: 15%;">创建时间</td>
                    </tr>
                    </thead>
                    <tbody>
                    <notempty name="_list">
                        <volist name="_list" id="vo">
                            <tr data-id="{$vo.id}">
                                <td>{$vo.id}</td>
                                <td><include file="public/vo_user"/></td>
                                <td><include file="public/cont_user"/></td>
                                <td>
                                    {$vo.trade_no}
                                </td>
                                <td>
                                    交易类型：{$vo.trade_type|enum_name='trade_types'}<br/>
                                    数额：<span class="{$vo.type=='inc' ? 'fc_green' : 'fc_red'}">{$vo.type=='inc' ? '+' : '-'}{$vo.commission_money}</span>
                                </td>

                                <td>
                                    {$vo.create_time|time_format='Y-m-d H:i'}
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
            <div class="pageshow async_container_pages mt_10">{:htmlspecialchars_decode($_page);}</div>

    </div>

    <script>
        $(function () {
            new SearchList('.filter_box',myConfig);
        });

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
    <include file="components/task_transfer_box"/>
</block>
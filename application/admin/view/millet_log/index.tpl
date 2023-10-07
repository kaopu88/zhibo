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
                    opts: JSON.parse('{:json_encode(enum_array("admin_millet_trade_types"))}')
                },
                {
                    name: 'exchange_type',
                    title: '兑换类型',
                    opts: [
                        {name: '礼物', value: 'gift'},
                        {name: '{:APP_BEAN_NAME}', value: 'bean'}
                    ]
                },
                {
                    name: 'isvirtual',
                    title: '虚拟用户',
                    opts: [
                        {name: '是', value: '1'},
                        {name: '否', value: '0'}
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
                        <input placeholder="接收者ID" type="text" name="user_id" value="{:input('user_id')}"/>
                        <input placeholder="贡献者ID" type="text" name="cont_uid" value="{:input('cont_uid')}"/>
                        <input placeholder="ID、交易单号、流水号" type="text" name="keyword" value="{:input('keyword')}"/>
                        <button class="filter_search_submit">搜索</button>
                    </div>
                    <div class="clear"></div>
                </div>
            </div>
            <input type="hidden" name="type" value="{:input('type')}"/>
            <input type="hidden" name="trade_type" value="{:input('trade_type')}"/>
            <input type="hidden" name="exchange_type" value="{:input('exchange_type')}"/>
            <input type="hidden" name="isvirtual" value="{:input('isvirtual')}"/>
        </div>
        <div class="table_slide">
             <table class="content_list mt_10">
            <thead>
            <tr>
                <td style="width: 5%;">ID</td>
                <td style="width: 10%;">交易信息</td>
                <td style="width: 10%;">接收者信息</td>
                <td style="width: 10%;">贡献者信息</td>
                <td style="width: 10%;">兑换信息</td>
                <td style="width: 8%;">{:APP_MILLET_NAME}总额</td>
                <td style="width: 8%;">冻结{:APP_MILLET_NAME}</td>
                <td style="width: 8%;">剩余{:APP_MILLET_NAME}</td>
                <td style="width: 8%;">虚拟{:APP_MILLET_NAME}</td>
                <td style="width: 5%;">虚拟用户</td>
                <td style="width: 8%;">客户端内容</td>
                <td style="width: 10%;">创建时间</td>
            </tr>
            </thead>
            <tbody>
            <notempty name="_list">
                <volist name="_list" id="vo">
                    <tr data-id="{$vo.id}">
                        <td>{$vo.id}</td>
                        <td>
                            日志流水号：{$vo.log_no}<br/>
                            交易类型：{$vo.trade_type|enum_name='millet_trade_types'}<br/>
                            交易单号：{$vo.trade_no}<br/>
                            交易额：<span class="{$vo.type=='inc' ? 'fc_green' : 'fc_red'}">{$vo.type=='inc' ? '+' : '-'}{$vo.total}</span>
                        </td>
                        <td><include file="recharge_app/user_info"/></td>
                        <td>
                            <include file="recharge_app/user_info_a"/></td>
                        </td>
                        <td>
                            兑换类型：{$vo.trade_type|enum_name='admin_millet_trade_types'}
                            <!--<switch name="vo['exchange_type']">
                                <case value="gift">
                                    礼物
                                </case>
                                <case value="bean">
                                    {:APP_BEAN_NAME}
                                </case>
                                <case value="score">
                                    积分兑换金币
                                </case>

                            </switch>-->
                            <br/>
                            兑换数量：{$vo.exchange_total}
                            <notempty name="vo['gift_info']">
                                <div class="thumb">
                                    <a href="javascript:;" class="thumb_img thumb_img_avatar">
                                        <img src="{:img_url($vo['gift_info']['picture_url'],'200_200','avatar')}"/>
                                    </a>
                                    <p class="thumb_info">
                                        <a href="javascript:;">
                                            {$vo.gift_info.name}
                                        </a>
                                    </p>
                                </div>
                            </notempty>
                        </td>
                        <td>
                            变更前：{$vo.last_total_millet}<br/>
                            变更后：{$vo.total_millet}
                        </td>
                        <td>
                            变更前：{$vo.last_fre_millet}<br/>
                            变更后：{$vo.fre_millet}
                        </td>
                        <td>
                            变更前：{$vo.last_millet}<br/>
                            变更后：{$vo.millet}
                        </td>
                        <td>
                            变更前：{$vo.last_isvirtual_millet}<br/>
                            变更后：{$vo.isvirtual_millet}
                        </td>
                        <td>
                            <switch name="vo['isvirtual']">
                                <case value="0">
                                    否
                                </case>
                                <case value="1">
                                    是
                                </case>
                            </switch>
                        </td>
                        <td>
                            客户端IP：{$vo.client_ip}<br/>
                            客户端版本：{$vo.app_v}
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
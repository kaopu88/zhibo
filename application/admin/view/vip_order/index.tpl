<extend name="public:base_nav"/>
<block name="css">
</block>

<block name="js">
    <script>
        var myConfig = {
            list: [
                {
                    name: 'vip_id',
                    title: 'vip',
                    get: '{:url("vip/get_list")}'
                },
                {
                    name: 'unit',
                    title: '单位',
                    opts: [
                        {name: '日', value: 'd'},
                        {name: '周', value: 'w'},
                        {name: '月', value: 'm'},
                        {name: '年', value: 'y'}
                    ]
                },
                {
                    name: 'vip_status',
                    title: 'VIP状态',
                    opts: [
                        {name: '未开通', value: '0'},
                        {name: '服务中', value: '1'},
                        {name: '已过期', value: '2'},
                    ]
                },
                {
                    name: 'settlement',
                    title: '结算方式',
                    opts: [
                        {name: '人民币', value: 'rmb'},
                        {name: '{:APP_BEAN_NAME}', value: 'bean'}
                    ]
                },
                {
                    name: 'pay_method',
                    title: '支付方式',
                    opts: JSON.parse('{:json_encode(enum_array("pay_methods"))}')
                },
                {
                    name: 'pay_status',
                    title: '支付状态',
                    opts: [
                        {name: '待支付', value: '0'},
                        {name: '已支付', value: '1'}
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
                        <input placeholder="订单号、第三方订单号" type="text" name="keyword" value="{:input('keyword')}"/>
                        <button class="filter_search_submit">搜索</button>
                    </div>
                    <div class="clear"></div>
                </div>
            </div>
            <input type="hidden" name="vip_id" value="{:input('vip_id')}"/>
            <input type="hidden" name="vip_status" value="{:input('vip_status')}"/>
            <input type="hidden" name="unit" value="{:input('unit')}"/>
            <input type="hidden" name="settlement" value="{:input('settlement')}"/>
            <input type="hidden" name="pay_method" value="{:input('pay_method')}"/>
            <input type="hidden" name="pay_status" value="{:input('pay_status')}"/>
        </div>

        <div class="table_slide">
            <table class="content_list mt_10 table_fixed">
            <thead>
            <tr>
                <td style="width: 5%;">ID</td>
                <td style="width: 5%;">订单号</td>
                <td style="width: 7%;">第三方订单号</td>
                <td style="width: 10%;">用户信息</td>
                <td style="width: 10%;">vip</td>
                <td style="width: 7%;">apple 内购ID</td>
                <td style="width: 5%;">价格</td>
                <td style="width: 10%;">等值{::APP_BEAN_NAME}</td>
                <td style="width: 10%;">vip状态</td>
                <td style="width: 10%;">支付信息</td>
                <td style="width: 5%;">客户端</td>
                <td style="width: 6%;">APP版本</td>
                <td style="width: 10%;">创建时间</td>
            </tr>
            </thead>
            <tbody>
            <notempty name="_list">
                <volist name="_list" id="vo">
                    <tr data-id="{$vo.id}">
                        <td>{$vo.id}</td>
                        <td>{$vo.order_no}</td>
                        <td>{$vo.third_trade_no}</td>
                        <td><include file="recharge_app/user_info"/></td>
                        <td>
                            <div class="thumb">
                                <a href="javascript:;"
                                   class="thumb_img thumb_img_avatar">
                                    <img src="{:img_url($vo['thumb'],'200_200','thumb')}"/>
                                </a>
                                <p class="thumb_info">
                                    <a href="javascript:;">
                                        {$vo.name}
                                    </a>
                                </p>
                            </div>
                        </td>
                        <td>{$vo.apple_id}</td>
                        <td>{$vo.rmb}</td>
                        <td>{$vo.price}</td>
                        <td>
                            购买时VIP到期时间：<br/><notempty name="vo.vip_expire"><a href="javascript:;">{$vo.vip_expire|time_format}</a><br/></notempty>
                            延长后的过期时间：<br/><notempty name="vo.new_vip_expire"><a href="javascript:;">{$vo.new_vip_expire|time_format}</a><br/></notempty>
                            购买时VIP状态：<br/><a href="javascript:;">{$vo.vip_status_str|default='无'}</a>
                        </td>
                        <td>
                            结算方式：<br/><a href="javascript:;">{$vo.settlement_str|default='无'}</a><br/>
                            支付方式：<br/><a href="javascript:;">{$vo.pay_method|enum_name='pay_methods'|default='无'}</a><br/>
                            支付时间：<br/><notempty name="vo.pay_time"><a href="javascript:;">{$vo.pay_time|time_format}</a><br/></notempty>
                            支付状态：<br/><a href="javascript:;">{$vo.pay_status_str|default='无'}</a>
                        </td>
                        <td>{$vo.client_ip}</td>
                        <td>{$vo.app_v}</td>
                        <td>
                            {$vo.create_time|time_format}
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
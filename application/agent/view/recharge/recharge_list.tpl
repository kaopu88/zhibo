<div class="table_slide">
    <table class="content_list mt_10">
        <thead>
        <tr>
            <td style="width: 10%;"><input type="checkbox" checkall="list_id" value="{$vo.user_id}"/></td>
            <td style="width: 10%;">ID</td>
            <td style="width: 15%;">充值用户</td>
            <td style="width: 20%;">订单详情</td>
            <td style="width: 15%;">支付平台</td>
            <td style="width: 15%;">支付状态</td>
            <td style="width: 15%;">下单时间</td>
        </tr>
        </thead>
        <tbody>
        <notempty name="recharge_list">
            <volist name="recharge_list" id="vo">
                <tr data-id="{$vo.user_id}">
                    <td><input class="list_id" type="checkbox" name="ids[]" value="{$vo.id}"/></td>
                    <td>{$vo.id}</td>
                    <td>
                        <include link="{:url('user/detail',['user_id'=>$vo.user_id])}" file="user/user_info"/>
                    </td>
                    <td>
                        <eq name="vo['isvirtual']"  value="1">
                            <span class="fc_red">[协议充值]</span><br/>
                        </eq>
                        订单号：{$vo.order_no}<br/>
                        充值{$vo.total_bean}{:APP_BEAN_NAME}，金额：{$vo.total_fee}元
                    </td>
                    <td>
                        {$vo.pay_method|enum_name='pay_methods'}
                    </td>
                    <td>
                        <switch name="vo['pay_status']">
                            <case value="0">
                                <span class="fc_red">未支付</span>
                            </case>
                            <case value="1">
                                <span class="fc_green">已支付</span><br/>
                                {$vo.pay_time|time_format}
                            </case>
                        </switch>
                    </td>
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
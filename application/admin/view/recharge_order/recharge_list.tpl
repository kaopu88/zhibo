<div class="table_slide">
<table class="content_list mt_10 md_width">
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
                    <div class="thumb">
                        <a target="_blank" href="{:url('user/detail',['user_id'=>$vo.user_id])}"
                           class="thumb_img thumb_img_avatar">
                            <img src="{:img_url($vo.avatar,'200_200','avatar')}"/>
                            <div class="thumb_level_box">
                                <img title="{$vo.level_name}" src="{$vo.level_icon}"/>
                            </div>
                        </a>
                        <p class="thumb_info">
                            <a target="_blank" href="{:url('user/detail',['user_id'=>$vo.user_id])}">
                                [{$vo.user_id}]<br/>
                                {$vo|user_name}
                            </a>
                        </p>
                    </div>
                </td>
                <td>
                    <eq name="vo['isvirtual']"  value="1">
                        <span class="fc_red">[虚拟充值]</span><br/>
                    </eq>
                    订单号：{$vo.order_no}<br/>
                    支付号：{$vo.third_trade_no}
                    <br/>
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
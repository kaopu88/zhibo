<div dom-key="user_deduction_box" class="layer_box  user_deduction_box " title="填写扣款申请单"
     popbox-action="{:url('user/deduction_recharge')}"
     popbox-area="540px,450px">
    <div class="pa_10">
        <table class="content_info2">
            <tr>
                <td class="field_name">付款方式</td>
                <td>
                    <select name="deduction_method" class="base_select">
                        <option value="">请选择</option>
                        <volist name="deduction_methods" id="deduction_method">
                            <option value="{$deduction_method.value}">{$deduction_method.name}</option>
                        </volist>
                    </select>
                </td>
            </tr>
            <tr class="bean_tr">
                <td class="field_name">扣除金额</td>
                <td>
                    <input placeholder="单位：{:APP_BEAN_NAME}" name="bean_num" class="base_text" value=""/>
                    <p class="field_tip bean_tip">
                        <a href="">当前全部余额</a>&nbsp;&nbsp;单位：{:APP_BEAN_NAME}
                    </p>
                </td>
            </tr>
            <tr class="refund_tr">
                <td class="field_name">充值单号</td>
                <td>
                    <input placeholder="18位单号" name="order_no" class="base_text" value=""/>
                    <p class="field_tip bean_tip">请在后台查找&nbsp;&nbsp;<a class="order_no_btn" href="javascript:;">如何查看充值单号？</a></p>
                </td>
            </tr>
            <tr class="refund_tr">
                <td class="field_name">支付单号</td>
                <td>
                    <input placeholder="18位单号" name="third_trade_no" class="base_text" value=""/>
                    <p class="field_tip bean_tip">需要客户提供&nbsp;&nbsp;<a class="third_trade_no_btn" href="javascript:;">如何查看支付单号？</a></p>
                </td>
            </tr>
            <tr class="refund_tr">
                <td class="field_name">支付时间</td>
                <td>
                    <input placeholder="请选择时间" name="pay_time" class="base_text" value=""/>
                    <p class="field_tip bean_tip">客户可以通过查看通知短信、支付凭据获取，时间误差不能超出十分钟</p>
                </td>
            </tr>
            <tr class="refund_tr">
                <td class="field_name">操作提示</td>
                <td>
                    1、为了验证真实性，充值单号、支付单号和支付时间必须一致。<br/>
                    2、假设A错充到B账号100个{:APP_BEAN_NAME}，但是在后台退款处理期间，B已经消费了50个{:APP_BEAN_NAME}，那么最多给A退款50个{:APP_BEAN_NAME}的等值人民币。<br/>
                    3、先扣除{:APP_BEAN_NAME}，扣除成功后再按照建议退款金额退款给用户。
                </td>
            </tr>
            <tr class="remark_tr">
                <td class="field_name">备注</td>
                <td><textarea name="remark" class="base_textarea"></textarea></td>
            </tr>
            <tr>
                <td class="field_name"></td>
                <td>
                    <input type="hidden" name="user_id" value=""/>
                    <div class="base_button sub_btn">提交</div>
                </td>
            </tr>
        </table>
    </div>
</div>

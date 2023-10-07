<div dom-key="user_recharge_box" class="layer_box  user_recharge_box" title="填写充值申请单" popbox-action="{:url('user/recharge')}"
     popbox-area="520px,520px">
    <div class="pa_10">
        <table class="content_info2">
            <tr>
                <td class="field_name">付款方式</td>
                <td>
                    <select name="pay_method" class="base_select">
                        <option value="">请选择</option>
                        <volist name="recharge_pay_methods" id="pay_method">
                            <option value="{$pay_method.value}">{$pay_method.name}</option>
                        </volist>
                    </select>
                </td>
            </tr>
            <tr>
                <td class="field_name">充值金额</td>
                <td><input placeholder="单位：元" name="total_fee" class="base_text" value=""/></td>
            </tr>
            <tr class="capital_tr">
                <td class="field_name">大写金额</td>
                <td>
                    <input placeholder="请输入充值金额的大写读数" name="capital_fee" class="base_text" value=""/>
                    <p class="field_tip">示例：12050 壹万贰仟零伍拾元整，5000.5 伍仟元伍角整</p>
                </td>
            </tr>
            <tr>
                <td class="field_name">{:APP_BEAN_NAME}</td>
                <td>
                    <input readonly class="base_text bean_num" value="0"/>
                    <p class="field_tip bean_tip">请输入充值金额</p>
                </td>
            </tr>
            <tr class="pay_tr">
                <td class="field_name">付款人</td>
                <td><input name="pay_name" placeholder="如：马云" class="base_text" value=""/></td>
            </tr>
            <tr class="pay_tr">
                <td class="field_name">付款账号</td>
                <td><input name="pay_account" class="base_text" value=""/></td>
            </tr>
            <tr>
                <td class="field_name">备注</td>
                <td>
                    <textarea name="remark" class="base_textarea"></textarea>
                </td>
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

<div title="流失用户清算业绩" class="layer_box loss_audit_handler pa_10" dom-key="loss_audit_handler"
     popbox-action="{:url('loss/audit')}" popbox-get-data="{:url('loss/audit')}" popbox-area="550px,380px">
    <table class="content_info2">
        <tr>
            <td class="field_name">预计可获得</td>
            <td>
                <input name="bean" readonly class="base_text" value=""/>
                <p class="field_name mt_10">单位：{:config('app.product_info.bean_name')}，以清算时实际{:config('app.product_info.bean_name')}为准</p>
            </td>
        </tr>
        <tr>
            <td class="field_name">处理状态</td>
            <td>
                <label class="base_label2"><input type="radio" name="audit_status" value="1" />清算</label>
                <label class="base_label2"><input type="radio" name="audit_status" value="2" />驳回</label>
                <div class="field_tip mt_5">注意：清算后将会和{:config('app.agent_setting.agent_name')}、{:config('app.agent_setting.promoter_name')}解除业绩关系</div>
            </td>
        </tr>
        <tr class="reason_tr">
            <td class="field_name">驳回原因</td>
            <td>
                <textarea name="reason" class="base_textarea"></textarea>
            </td>
        </tr>
        <tr>
            <td class="field_name"></td>
            <td>
                <div class="base_button sub_btn mt_10">提交</div>
                <input type="hidden" name="id" readonly class="base_text" value=""/>
            </td>
        </tr>
    </table>
</div>
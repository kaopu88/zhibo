<div title="实名认证申请" class="layer_box verified_handler pa_10" dom-key="verified_handler" popbox-action="{:url('user_verified/handler')}">
    <table class="content_info2">
        <tr>
            <td class="field_name">处理状态</td>
            <td>
                <select name="status" class="base_select">
                    <option value="">请选择</option>
                    <option value="1">通过</option>
                    <option value="2">驳回</option>
                </select>
            </td>
        </tr>
        <tr>
            <td class="field_name">备注</td>
            <td>
                <textarea name="handle_desc" class="base_textarea"></textarea>
            </td>
        </tr>
        <tr>
            <td class="field_name"></td>
            <td>
                <input type="hidden" name="id" value="" />
                <div class="base_button sub_btn">提交</div>
            </td>
        </tr>
    </table>
</div>
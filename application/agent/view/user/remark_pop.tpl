<div dom-key="user_remark_box" class="layer_box user_remark_box pa_10" title="用户备注" popbox-action="{:url('user/remark')}"
     popbox-area="520px,280px" popbox-get-data="{:url('user/remark')}">
    <table class="content_info2">
        <tr>
            <td class="field_name">备注名称</td>
            <td>
                <input placeholder="" name="remark_name" class="base_text" value=""/>
            </td>
        </tr>
        <tr>
            <td class="field_name">备注信息</td>
            <td>
                <textarea name="remark" class="base_textarea"></textarea>
            </td>
        </tr>
        <tr>
            <td class="field_name"></td>
            <td>
                <input type="hidden" name="user_id" value=""/>
                <div class="base_button sub_btn">保存</div>
            </td>
        </tr>
    </table>
</div>

<div title="小程序评论审核" class="layer_box article_comment_handler pa_10" dom-key="article_comment_handler" popbox-action="{:url('article_comment/audit')}">
    <table class="content_info2">
        <tr>
            <td class="field_name">审核状态</td>
            <td>
                <select name="audit_status" class="base_select">
                    <option value="">请选择</option>
                    <option value="1">通过</option>
                    <option value="2">驳回</option>
                </select>
            </td>
        </tr>
        <tr>
            <td class="field_name">驳回原因</td>
            <td>
                <textarea name="reason" class="base_textarea"></textarea>
                <p class="field_tip">通过时可忽略此项，驳回时此项必填</p>
            </td>
        </tr>
        <tr>
            <td class="field_name"></td>
            <td>
                <a target="_blank" href="{:url('article_comment/audit_norm')}">《小程序评论审核规范》</a>
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
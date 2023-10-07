<div title="{:config('app.product_info.millet_name')}变更" class="layer_box millet_handler pa_10" dom-key="millet_handler" popbox-area="520px,200px" popbox-get-data="{:url('rank/millet_handler')}" popbox-action="{:url('rank/millet_handler')}">
    <table class="content_info2">
        <tr>
            <td class="field_name">{:APP_MILLET_NAME}</td>
            <td>
                <input placeholder="" name="millet" class="base_text" value=""/>
            </td>
        </tr>
        <tr>
            <td class="field_name"></td>
            <td>
                <input type="hidden" name="user_id" value="" />
                <input type="hidden" name="interval" value="" />
                <input type="hidden" name="name" value="" />
                <input type="hidden" name="rnum" value="" />
                <div class="base_button sub_btn">提交</div>
            </td>
        </tr>
    </table>
</div>
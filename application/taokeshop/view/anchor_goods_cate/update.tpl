<div title="橱窗分类编辑" class="layer_box anchor_cate_edit pa_10" dom-key="anchor_cate_edit"
     popbox-action="{:url('anchor_goods_cate/edit')}" popbox-get-data="{:url('anchor_goods_cate/edit')}" popbox-area="700px,550px">
    <table class="content_info2">
        <tr class="edit_tr">
            <td class="field_name">分类名称</td>
            <td>
                <input type="text" name="cate_name" value="{$_info.cate_name}" />
            </td>
        </tr>
        <tr class="edit_tr">
            <td class="field_name">用户ID</td>
            <td>
                <input type="text" name="user_id" value="{$_info.user_id}" disabled/>
            </td>
        </tr>
        <tr class="edit_tr">
            <td class="field_name">排序</td>
            <td>
                <input type="text" name="sort" value="{$_info.sort}" disabled/>
                <input type="hidden" name="cate_id" value="{$_info.cate_id}"/>
            </td>
        </tr>
        <tr class="edit_tr">
            <td class="field_name">状态</td>
            <td>
                <select class="base_select" name="status" selectedval="{$_info.status}">
                    <option value="1">启用</option>
                    <option value="0">禁用</option>
                </select>
            </td>
        </tr>
        <tr>
            <td class="field_name"></td>
            <td>
                <div class="base_button_div max_w_412">
                    <a href="javascript:;" class="base_button" ajax="post">提交</a>
                </div>
            </td>
        </tr>

    </table>
    <div style="height: 30px"></div>
</div>
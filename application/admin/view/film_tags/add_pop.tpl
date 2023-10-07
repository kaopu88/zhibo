<div class="add_box layer_box">
    <div class="pa_10">
        <table class="content_info2">
            <tr>
                <td class="field_name">上级</td>
                <td>
                    <div typecl="{:url('film_tags/get_tree')}">
                        <input type="hidden" class="type_val_select" name="pid" value="{:input('get.pid')}"/>
                    </div>
                </td>
            </tr>
            <tr>
                <td class="field_name">名称</td>
                <td><input name="name" class="base_text" type="text"/></td>
            </tr>
            <tr>
                <td class="field_name">权重</td>
                <td>
                    <input name="sort" class="base_text" type="text" value=""/>
                </td>
            </tr>
            <tr>
                <td class="field_name">描述</td>
                <td>
                    <textarea style="height: 60px;" name="descr" class="base_textarea"></textarea>
                </td>
            </tr>
            <tr>
                <td class="field_name"></td>
                <td>
                    <input type="hidden" name="id" value=""/>
                    <div class="base_button sub_btn">新增</div>
                </td>
            </tr>
        </table>
    </div>
</div>
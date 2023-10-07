<div dom-key="task_transfer_box" popbox="task_transfer_box" class="task_transfer_box layer_box" title="转交任务"
     popbox-action="{:url('personal/task_transfer')}" popbox-get-data="{:url('personal/task_transfer')}"
     popbox-area="510px,350px">
    <div class="pa_10">
        <table class="content_info2">
            <tr>
                <td>任务类型</td>
                <td>
                    <select name="type" class="base_select">
                        <option value="">请选择任务类型</option>
                        <volist name="work_types" id="work_type">
                            <option value="{$work_type.value}">{$work_type.name}</option>
                        </volist>
                    </select>
                </td>
            </tr>
            <tr>
                <td>任务ID</td>
                <td><input name="id" class="base_text" value="" readonly/></td>
            </tr>
            <tr>
                <td>接手人</td>
                <td>
                    <select class="base_select admin_select">
                        <volist name="work_types" id="work_type">
                            <option value="{$work_type.value}">{$work_type.name}</option>
                        </volist>
                    </select><br/>
                    <input name="aid" value="" placeholder="接手人的ID" class="base_text mt_10" />
                    <p class="field_tip">不选择接手人也可以直接填写接手人的ID</p>
                </td>
            </tr>
            <tr>
                <td></td>
                <td>
                  <div class="base_button_div max_w_412">
                    <div class="base_button sub_btn">确认转交</div>
                </div>
                </td>
            </tr>
        </table>
    </div>
</div>
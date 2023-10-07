<div title="处理举报申请" class="layer_box complaint_handler_user pa_10" dom-key="complaint_handler_user" popbox-action="{:url('complaint/handler_user')}">
    <table class="content_info2">
        <tr>
            <td class="field_name">处理状态</td>
            <td>
                <select name="audit_status" class="base_select">
                    <option value="">请选择</option>
                    <option value="1">通过</option>
                    <option value="11">通过并封禁</option>
                    <option value="2">驳回</option>
                </select>
            </td>
        </tr>
        <tr class="forbidden" style="display: none;">
            <td class="field_name">封禁时间</td>
            <td>
                <select name="disable_length" class="base_select">
                    <option value="">永久</option>
                    <option value="15 minutes">15分钟</option>
                    <option value="1 hours">1小时</option>
                    <option value="6 hours">6小时</option>
                    <option value="1 days">1天</option>
                    <option value="3 days">3天</option>
                    <option value="7 days">一周</option>
                    <option value="1 months">1个月</option>
                    <option value="3 months">3个月</option>
                    <option value="1 years">1年</option>
                </select>
            </td>
        </tr>
        <tr class="forbidden" style="display: none;">
            <td class="field_name">封禁原因</td>
            <td>
                <textarea placeholder="可选" name="disable_desc" class="base_textarea"></textarea>
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
                <input type="hidden" name="change_status" value="{:check_auth('admin:user:change_status')?'0':'1'}" />
                <div class="base_button_div max_w_412">
                    <div class="base_button sub_btn">提交</div>
                </div>
            </td>
        </tr>
    </table>
</div>
<script>
    $("select[name='audit_status']").change(function(){
        var checkValue = $(this).val();
        if(checkValue==11){
            $('.forbidden').show();
        }else{
            $('.forbidden').hide();
        }
    })
</script>
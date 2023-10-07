<div class="disable_user_box pa_10 layer_box">
    <table class="content_info2">
        <tr>
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
        <tr>
            <td class="field_name">封禁原因</td>
            <td>
                <textarea placeholder="可选" name="disable_desc" class="base_textarea"></textarea>
            </td>
        </tr>
        <tr>
            <td class="field_name"></td>
            <td>
                <input type="hidden" name="user_id" value=""/>
                <div class="base_button_div max_w_412">
                    <div class="base_button sub_disable_btn">封禁</div>
                </div>
            </td>
        </tr>
    </table>
</div>

<div title="添加信用记录" popbox="user_credit_box" dom-key="user_credit_box" class="layer_box user_credit_box pa_10" popbox-action="{:url('credit_log/add')}">
    <table class="content_info2">
        <tr>
            <td class="field_name">类型</td>
            <td>
                <select name="change_type" class="base_select">
                    <option value="">请选择</option>
                    <option value="exp">负面记录</option>
                    <option value="inc">积极记录</option>
                </select>
            </td>
        </tr>
        <tr>
            <td class="field_name">信用分值</td>
            <td>
                <input name="score" class="base_text" value="1"/>
                <p class="field_tip">正整数</p>
            </td>
        </tr>
        <tr>
            <td class="field_name">具体事项</td>
            <td>
                <textarea style="display: none" name="" class="base_textarea remark_textarea"></textarea>
                <select style="display: none" class="base_select remark_select" name="">
                    <option value="">请选择</option>
                    <option value="广告欺诈">广告欺诈</option>
                    <option value="淫秽色情">淫秽色情</option>
                    <option value="骚扰谩骂">骚扰谩骂</option>
                    <option value="反动政治">反动政治</option>
                    <option value="侵权（冒充他人、侵犯名誉等）">侵权（冒充他人、侵犯名誉等）</option>
                    <option value="发布不实信息">发布不实信息</option>
                    <option value="违法犯罪">违法犯罪</option>
                    <option value="账号可能被盗用">账号可能被盗用</option>
                    <option value="其它内容">其它内容</option>
                </select>

            </td>
        </tr>
        <tr>
            <td class="field_name"></td>
            <td>
                <input type="hidden" name="user_id" value=""/>
                <div class="base_button_div max_w_412">
                    <div class="base_button sub_btn">提交</div>
                </div>
            </td>
        </tr>
    </table>
</div>
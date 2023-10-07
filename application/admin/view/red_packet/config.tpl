<extend name="public:base_nav"/>
<block name="body">
    <div class="pa_20 p-0 show_bottom">
        <include file="components/tab_nav"/>
            <form action="{:url('config')}">
            <div class="table_slide">
                <table class="content_info2 mt_10 font_normal table_fixed sm_width">
                    <tr>
                        <td class="field_name" style="width:110px;">是否启用</td>
                        <td>
                            <select class="base_select" name="red_packet_is_open" selectedval="{$_info.red_packet_is_open ? '1' : '0'}">
                                <option value="1">启用</option>
                                <option value="0">禁用</option>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <td class="field_name"></td>
                        <td>
                            <div class="base_group">
                                <span class="base_label" style="width: 211px;">最小</span>
                                <span class="base_label" style="width: 201px;">最大</span>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td class="field_name">红包金额</td>
                        <td>
                            <input class="base_text" name="red_packet_min" type="number" style="width: 193px;" value="{$_info.red_packet_min}"/>
                            <input class="base_text" name="red_packet_max" type="number" style="width: 193px;" value="{$_info.red_packet_max}"/>
                        </td>
                    </tr>

                    <tr>
                        <td class="field_name">红包数量</td>
                        <td>
                            <input class="base_text" name="red_packet_num" type="number" style="width: 193px;" value="{$_info.red_packet_num}"/>
                            <span>表示红包最大个数,不填或者为0表示不控制数量</span>
                        </td>
                    </tr>

                    <tr>
                        <td class="field_name">红包过期时间</td>
                        <td>
                            <input class="base_text" name="red_packet_expire" type="number" style="width: 193px;" value="{$_info.red_packet_expire}"/>
                            <span>分钟 不填或者为0表示默认不过期</span>
                        </td>
                    </tr>

                    <tr>
                        <td class="field_name">说明</td>
                        <td>
                            <textarea name="red_packet_desc" class="base_text" style="height: 120px;">{$_info.red_packet_desc}</textarea>
                        </td>
                    </tr>

                </table>
                <div class="base_button_div" style="max-width:547px;">
                    <a href="javascript:;" class="base_button" ajax="post">提交</a>
                </div>
                </div>
            </form>
    </div>
</block>
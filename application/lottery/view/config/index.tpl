<extend name="public:base_nav"/>
<block name="body">
    <div class="pa_20 p-0 show_bottom">
        <include file="components/tab_nav"/>
            <form action="{:url('index')}">
            <div class="table_slide">
                <table class="content_info2 mt_10 font_normal table_fixed sm_width">
                    <tr>
                        <td class="field_name" style="width:110px;">是否启用</td>
                        <td>
                            <select class="base_select" name="lottery_is_open" selectedval="{$_info.lottery_is_open ? '1' : '0'}">
                                <option value="1">启用</option>
                                <option value="0">禁用</option>
                            </select>
                        </td>
                    </tr>


                    <tr>
                        <td class="field_name">说明</td>
                        <td>
                            <textarea name="lottery_desc" class="base_text" style="height: 120px;">{$_info.lottery_desc}</textarea>
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
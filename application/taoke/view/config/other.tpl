<extend name="public:base_nav"/>
<block name="body">
    <div class="pa_20 p-0 show_bottom">
        <include file="components/tab_nav"/>
        <form action="{:url('other')}">
            <table class="content_info2 mt_10">

                <tr>
                    <td class="field_name">淘口令符号设置</td>
                    <td>
                        <select class="base_select" name="keys_type" selectedval="{$_info.keys_type}">
                            <option value="1">€淘口令€</option>
                            <option value="2">《淘口令《</option>
                            <option value="3">(淘口令)</option>
                            <option value="4">£淘口令£</option>
                            <option value="5">₳淘口令₳</option>
                            <option value="6">¢淘口令¢</option>
                        </select>
                    </td>
                </tr>

            </table>
            <div class="base_button_div max_w_537">
                <a href="javascript:;" class="base_button" ajax="post">提交</a>
            </div>
        </form>
    </div>
</block>
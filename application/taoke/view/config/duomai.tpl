<extend name="public:base_nav"/>
<block name="body">
    <div class="pa_20 p-0 show_bottom">
        <include file="components/tab_nav"/>
        <form action="{:url('duomai')}">
            <table class="content_info2 mt_10">

                <tr>
                    <td class="field_name">多麦账户ID</td>
                    <td>
                        <input class="base_text" name="account_id" value="{$_info.account_id}"/>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">多麦媒体ID</td>
                    <td>
                        <input class="base_text" name="media_id" value="{$_info.media_id}"/>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">多麦密钥</td>
                    <td>
                        <input class="base_text" name="hash" value="{$_info.hash}"/>
                    </td>
                </tr>

            </table>
            <div class="base_button_div max_w_537">
                <a href="javascript:;" class="base_button" ajax="post">提交</a>
            </div>
        </form>
    </div>
</block>
<extend name="public:base_nav"/>
<block name="body">
    <div class="pa_20 p-0 show_bottom">
        <include file="components/tab_nav"/>
        <form action="{:url('kuaizhan')}">
            <table class="content_info2 mt_10">

                <tr>
                    <td class="field_name">APP_KEY</td>
                    <td>
                        <input class="base_text" name="appkey" value="{$_info.appkey}"/>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">APP_SECRET</td>
                    <td>
                        <input class="base_text" name="secret" value="{$_info.secret}"/>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">站点id</td>
                    <td>
                        <input class="base_text" name="siteid" value="{$_info.siteid}"/>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">快站域名</td>
                    <td>
                        <input class="base_text" name="domain" value="{$_info.domain}"/>
                    </td>
                </tr>

                <tr class="site">
                    <td class="field_name">生成快站短链接</td>
                    <td>
                        <select class="base_select" name="is_short" selectedval="{$_info.is_short}">
                            <option value="0">否</option>
                            <option value="1">是</option>
                        </select>
                    </td>
                </tr>

            </table>

            <div class="base_button_div max_w_537">
                <a href="javascript:;" class="base_button" ajax="post">提交</a>
            </div>
        </form>
    </div>

    <script>
    </script>
</block>
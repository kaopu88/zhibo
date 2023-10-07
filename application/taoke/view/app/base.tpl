<extend name="public:base_nav"/>
<block name="body">
    <div class="pa_20 p-0 show_bottom">
        <include file="components/tab_nav"/>
        <form action="{:url('base')}">
            <table class="content_info2 mt_10">

                <tr>
                    <td class="field_name">客服微信</td>
                    <td>
                        <input class="base_text" name="service_wx" value="{$_info.service_wx}"/>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">客服QQ</td>
                    <td>
                        <input class="base_text" name="service_qq" value="{$_info.service_qq}"/>
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
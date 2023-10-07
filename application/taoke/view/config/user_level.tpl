<extend name="public:base_nav"/>

<block name="body">
    <div class="pa_20 p-0">
        <include file="components/tab_nav"/>
        <form action="{:url('user_level')}">
            <table class="content_info2 mt_10">

                <tr>
                    <td class="field_name">超级会员名称</td>
                    <td>
                        <input class="base_text" name="super_member[name]" value="{:isset($_info.super_member.name) ? $_info.super_member.name : "超级会员"}"/>
                        <input type="hidden" value="1" name="super_member[type]">

                        <span>注：为空则默认此会员等级名称为“超级会员”</span>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">代理商名称</td>
                    <td>
                        <input class="base_text" name="agent[name]" value="{:isset($_info.agent.name) ? $_info.agent.name : "代理商"}"/>
                        <input type="hidden" value="2" name="agent[type]">
                        <span>注：为空则默认此会员等级名称为“代理商”</span>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">运营商名称</td>
                    <td>
                        <input class="base_text" name="operator[name]" value="{:isset($_info.operator.name) ? $_info.operator.name : "运营商"}"/>
                        <input type="hidden" value="3" name="operator[type]">
                        <span>注：为空则默认此会员等级名称为“运营商”</span>
                    </td>
                </tr>

            </table>
            <div class="base_button_div">
                <a href="javascript:;" class="base_button" ajax="post">提交</a>
            </div>
        </form>
    </div>

    <script>
    </script>
</block>
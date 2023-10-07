<extend name="public:base_nav"/>
<block name="body">
    <div class="pa_20 p-0 show_bottom">
        <include file="components/tab_nav"/>
        <form action="{:url('channel')}">
            <table class="content_info2 mt_10">
                <tr>
                    <td class="field_name">渠道功能</td>
                    <td>
                        <select class="base_select" name="is_open" selectedval="{$_info.is_open ? '1' : '0'}">
                            <option value="1">启用</option>
                            <option value="0">禁用</option>
                        </select>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">淘宝订单开始结算时间</td>
                    <td>
                        <input class="base_text" name="tb_order_time" value="{$_info.tb_order_time|time_format='','Y-m-d H:i:s'}"/>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">拼多多订单开始结算时间</td>
                    <td>
                        <input class="base_text" name="pdd_order_time" value="{$_info.pdd_order_time|time_format='','Y-m-d H:i:s'}"/>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">京东订单开始结算时间</td>
                    <td>
                        <input class="base_text" name="jd_order_time" value="{$_info.jd_order_time|time_format='','Y-m-d H:i:s'}"/>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">私域管理权限API</td>
                    <td>
                        <select class="base_select" name="publisher_api_auth" selectedval="{$_info.publisher_api_auth ? '1' : '0'}">
                            <option value="1">有</option>
                            <option value="0">无</option>
                        </select>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">渠道邀请码</td>
                    <td>
                        <input class="base_text" name="relation_inviteCode" value="{$_info.relation_inviteCode}"/>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">会员邀请码</td>
                    <td>
                        <input class="base_text" name="special_inviteCode" value="{$_info.special_inviteCode}"/>
                    </td>
                </tr>

            </table>
            <div class="base_button_div max_w_537">
                <a href="javascript:;" class="base_button" ajax="post">提交</a>
            </div>
        </form>
    </div>

    <script>
        $("[name=tb_order_time]").flatpickr({
            dateFormat: 'Y-m-d',
            enableTime: false,
        });

        $("[name=pdd_order_time]").flatpickr({
            dateFormat: 'Y-m-d',
            enableTime: false,
        });

        $("[name=jd_order_time]").flatpickr({
            dateFormat: 'Y-m-d',
            enableTime: false,
        });

    </script>
</block>
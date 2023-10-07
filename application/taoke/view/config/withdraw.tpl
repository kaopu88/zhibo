<extend name="public:base_nav"/>
<block name="body">
    <div class="pa_20 p-0 show_bottom">
        <include file="components/tab_nav"/>
        <form action="{:url('withdraw')}">
            <table class="content_info2 mt_10">

                <tr>
                    <td class="field_name">提现功能</td>
                    <td>
                        <select class="base_select" name="is_open" selectedval="{$_info.is_open ? '1' : '0'}">
                            <option value="1">开启</option>
                            <option value="0">关闭</option>
                        </select>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">提现最小金额</td>
                    <td>
                        <input class="base_text" name="limit_money" value="{$_info.limit_money}"/> 元
                    </td>
                </tr>

                <tr>
                    <td class="field_name">提现手续费</td>
                    <td>
                        <input class="base_text" name="service_fee_rate" value="{$_info.service_fee_rate}"/> %
                    </td>
                </tr>

                <tr>
                    <td class="field_name">指定提现日期</td>
                    <td>
                        <select class="base_select" name="fixed_date" selectedval="{$_info.fixed_date}" id="fixed">
                            <option value="0">否</option>
                            <option value="1">是</option>
                        </select>
                        <input class="base_text date" name="date" value="{$_info.date}" style="width: 200px;display: none;"/>
                        <span>1-30号</span>
                    </td>
                </tr>

                <tr>
                    <td class="field_name">每月提现次数</td>
                    <td>
                        <input class="base_text" name="extra_times" value="{$_info.extra_times}"/> %
                        <span>为0时表示不限制</span>
                    </td>
                </tr>

            </table>
            <div class="base_button_div max_w_537">
                <a href="javascript:;" class="base_button" ajax="post">提交</a>
            </div>
        </form>
    </div>
    <script>
        $(function(){
            var p = "{$_info.fixed_date ? $_info.fixed_date : 0}";
            if(p == 1){
                $(".date").show();
            }else {
                $(".date").hide();
            }
        });

        $("#fixed").change(function(){
            var checkValue = $("#fixed").val();
            if(checkValue == 1){
                $(".date").show();
            }else if (checkValue == 0) {
                $(".date").hide();
            }
        });
    </script>
</block>
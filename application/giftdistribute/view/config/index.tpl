<extend name="public:base_nav"/>

<block name="body">
    <style>
        .layui-hide{
            display: none;
        }
    </style>
    <div class="pa_20 p-0 show_bottom">
        <include file="components/tab_nav"/>
        <form action="{:url('index')}">
            <div class="layui-tab-content">
                <div class="content_title2">分销默认设置</div>
                <table class="content_info2 mt_10">
                    <tr>
                        <td class="field_name">分销功能</td>
                        <td>
                            &nbsp;<label>
                                <input  style="width: 20px;height: 20px;" type="radio" name="is_open" value="0" <if condition="$_info.is_open eq 0">checked</if>>&nbsp;关闭
                            </label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                            <label>
                                <input  style="width: 20px;height: 20px;" type="radio" name="is_open" value="1" <if condition="$_info.is_open eq 1">checked</if>>&nbsp;开启
                            </label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        </td>
                    </tr>
                        <tr class="distribution-index <if condition="$_info.is_open eq 0">layui-hide</if>"  style="margin-top: 30px;">
                            <td class="field_name">分销层级</td>
                            <td>
                                &nbsp;<label>
                                    <input  style="width: 20px;height: 20px;" type="radio" name="level" value="1" <if condition="$_info.level eq 1">checked</if>>&nbsp;一级分销
                                </label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                <label>
                                    <input  style="width: 20px;height: 20px;" type="radio" name="level" value="2" <if condition="$_info.level eq 2">checked</if>>&nbsp;二级分销
                                </label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                <label>
                                    <input  style="width: 20px;height: 20px;" type="radio" name="level" value="3" <if condition="$_info.level eq 3">checked</if>>&nbsp;三级分销
                                </label>
                            </td>
                        </tr>

                        <tr class="distribution-index <if condition="$_info.is_open eq 0">layui-hide</if>">
                            <td class="field_name">一级{$distribute_name}比例</td>
                            <td>
                                <input class="base_text" type="number"  name="one_rate" value="{$_info.one_rate ? $_info.one_rate : 0}"/> % <span style="color: #f00">送礼物直接推荐人返佣占比例</span>
                            </td>
                        </tr>

                        <tr class="distribution-index <if condition="$_info.is_open eq 0">layui-hide</if>">
                            <td class="field_name">二级{$distribute_name}比例</td>
                            <td>
                                <input class="base_text" type="number" name="two_rate" value="{$_info.two_rate ? $_info.two_rate : 0}"/> %  <span style="color: #f00">送礼物推荐人的上级返佣比例</span>
                            </td>
                        </tr>

                        <tr class="distribution-index <if condition="$_info.is_open eq 0">layui-hide</if>">
                            <td class="field_name">三级{$distribute_name}比例</td>
                            <td>
                                <input class="base_text" type="number" name="three_rate" value="{$_info.three_rate ? $_info.three_rate : 0}"/> % <span style="color: #f00">送礼物推荐人的上上级返佣比例</span>
                            </td>
                        </tr>
                </table>

                <div class="content_title2">佣金提现设置</div>
                <table class="content_info2 mt_10">
                    <tr>
                        <td class="field_name">佣金</td>
                        <td>
                            <input class="base_text" type="text" name="name" value="{$_info.name}"/>
                        </td>
                    </tr>
                    <tr>
                        <td class="field_name">平台提现</td>
                        <td>
                            <select class="base_select" name="commission_cash_on" selectedval="{$_info.commission_cash_on}">
                                <option value="0">禁用</option>
                                <option value="1">启用</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td class="field_name">提现比例</td>
                        <td>
                            <input class="base_text" type="text" name="commission_cash_rate" value="{$_info.commission_cash_rate}"/>
                            <span>小于或等于1的数值，1表示为等值转换</span>
                        </td>
                    </tr>

                    <tr>
                        <td class="field_name">单笔手续费</td>
                        <td>
                            <input class="base_text" name="commission_cash_fee" value="{$_info.commission_cash_fee}"/>
                            <span>通过比例转化后扣除的金额</span>
                        </td>
                    </tr>

                    <tr>
                        <td class="field_name">提现最低额度</td>
                        <td>
                            <input class="base_text" name="commission_cash_min" value="{$_info.commission_cash_min}"/>
                        </td>
                    </tr>
                    <tr>
                        <td class="field_name">月提现次数</td>
                        <td>
                            <input class="base_text" name="commission_cash_monthlimit" value="{$_info.commission_cash_monthlimit}"/>
                        </td>
                    </tr>

                </table>
            </div>
            <div class="base_button_div">
                <a href="javascript:;" class="base_button" ajax="post">提交</a>
            </div>
        </form>
    </div>
    <script>
        $("input[name='is_open']").click(function(){
            openDistribution = $(this).val();
            if (!parseInt(openDistribution)){
                $(".distribution-index").addClass('layui-hide');
                return;
            }
            $(".distribution-index").removeClass('layui-hide');
        });
    </script>
</block>
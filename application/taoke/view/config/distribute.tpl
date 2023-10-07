<extend name="public:base_nav"/>

<block name="body">
    <div class="pa_20 p-0 show_bottom">
        <include file="components/tab_nav"/>
        <form action="{:url('distribute')}">
                <div class="layui-tab-content">

                    <table class="content_info2 mt_10">
                        <tr>
                            <td class="field_name">分销功能</td>
                            <td>
                                <select class="base_select" name="is_open" selectedval="{$_info.is_open ? '1' : '0'}">
                                    <option value="1">开启</option>
                                    <option value="0">关闭</option>
                                </select>
                            </td>
                        </tr>

                        <tr>
                            <td class="field_name">普通会员自购佣金</td>
                            <td>
                                <input class="base_text" name="normal_rate" value="{$_info.normal_rate ? $_info.normal_rate : 0}"/> %
                            </td>
                        </tr>
                        <tr>
                            <td class="field_name">保留佣金比率</td>
                            <td>
                                <input class="base_text" name="retain_rate" value="{$_info.retain_rate ? $_info.retain_rate : 0}"/> %
                            </td>
                        </tr>

                        <tr>
                            <td class="field_name">淘宝扣减比率</td>
                            <td>
                                <input class="base_text" name="taobao_substr_rate" value="{$_info.taobao_substr_rate ? $_info.taobao_substr_rate : 0}"/> %
                            </td>
                        </tr>

                        <tr>
                            <td class="field_name">拼多多扣减比率</td>
                            <td>
                                <input class="base_text" name="pdd_substr_rate" value="{$_info.pdd_substr_rate ? $_info.pdd_substr_rate : 0}"/> %
                            </td>
                        </tr>

                        <tr>
                            <td class="field_name">京东扣减比率</td>
                            <td>
                                <input class="base_text" name="jd_substr_rate" value="{$_info.jd_substr_rate ? $_info.jd_substr_rate : 0}"/> %
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

    </script>
</block>
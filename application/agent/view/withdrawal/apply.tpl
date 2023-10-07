<extend name="public:base_nav"/>

<block name="css">
    <link rel="stylesheet" type="text/css" href="__VENDOR__/smart/smart_region/region.css?v=__RV__"/>
    <link rel="stylesheet" type="text/css" href="__VENDOR__/cropper/cropper.min.css?v=__RV__"/>
    <style>
        .field_name {
            width: 100px;
        }
    </style>
</block>

<block name="body">
    <div class="pa_20">
        <div class="content_title">
            <h1>{$agent_last.name}</h1>
            <a href="javascript:;" class="base_button base_button_gray base_button_s history_back">返回</a>
        </div>
        <form action="{:url('apply')}" method="post">

            <div class="panel mt_10">
                <div class="panel-heading">账户信息</div>
                <div class="panel-body">
                    <table class="content_info2">
                        <tr>
                            <td class="field_name">可提现数额:</td>
                            <td>
                                <span style="color: red;font-size: 16px;font-weight: bold">￥ {$total}</span>
                            </td>
                        </tr>
                        <tr>
                            <td class="field_name">提现金额:</td>
                            <td>
                                <input class="base_text" name="price" type="number" value="请输入要提现的金额"/>
                            </td>
                        </tr>
                        <tr>
                            <td class="field_name">结算账户:</td>
                            <td>
                                <select class="base_select" name="cash_accout_id" onchange="func()">
                                    <volist name="cash_account" id="vo">
                                    <option value="{$vo.id}">账户类型:
                                        <eq name="vo['account_type']" value="0"><span class="fc_green">支付宝</span></eq>
                                        <eq name="vo['account_type']" value="1"><span class="fc_green">微信</span></eq>
                                        <eq name="vo['account_type']" value="2"><span class="fc_green">银行卡</span></eq>---姓名:{$vo.name}---账号:{$vo.account}
                                    </option>
                                    </volist>
                                </select>
                            </td>
                        </tr>

                        <tr>
                            <td class="field_name">联系电话:</td>
                            <td>
                                <input class="base_text" name="contact_phone" value="{$account.contact_phone}"/>
                            </td>
                        </tr>

                        <tr>
                            <td class="field_name">手续费:</td>
                            <td>
                                <span style="font-size: 14px;font-weight: bold">  ￥{:config('app.cash_setting.agent_cash_fee') ?: 0}</span>
                            </td>
                        </tr>

                        <tr>
                            <td class="field_name">税费:</td>
                            <td>
                                <span style="font-size: 14px;font-weight: bold"> {:config('app.cash_setting.agent_cash_taxes') * 100 ?: 0} %</span>
                            </td>
                        </tr>

                    </table>
                </div>
            </div>

            <div class="base_button_div p_b_20">
                <a href="javascript:;" class="base_button" ajax="post">我要结算</a>
            </div>
        </form>
    </div>

    <script>
        function func(){
        }
    </script>

</block>
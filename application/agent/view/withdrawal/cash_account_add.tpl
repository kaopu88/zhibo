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
        <form action="{:isset($account['id'])?url('cash_account_edit'):url('cash_account_add')}" method="post">
            <input name="id" type="hidden" value="{$account.id}" />
            <div class="panel mt_10">
                <div class="panel-heading">账户信息</div>
                <div class="panel-body">
                    <table class="content_info2">
                        <tr>
                            <td class="field_name">账户类型</td>
                            <td>
                                <select class="base_select" id="select_type" name="account_type" selectedval="{$account.account_type}"  onchange="func()">
                                    <option value="0">支付宝</option>
                                    <option value="2">银行卡</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td class="field_name">姓名</td>
                            <td>
                                <input class="base_text" name="name" value="{$account.name}"/>
                            </td>
                        </tr>
                        <tr  <if condition="$account.account_type neq 2"> style="display: none" </if>  class="bank">
                            <td class="field_name">卡名</td>
                            <td>
                                <input class="base_text" name="card_name" value="{$account.card_name}"/>
                            </td>
                        </tr>
                        <tr>
                            <td class="field_name">账号</td>
                            <td>
                                <input class="base_text" name="account" value="{$account.account}"/>
                            </td>
                        </tr>

                        <tr>
                            <td class="field_name">是否默认</td>
                            <td>
                                <select class="base_select" name="is_default" selectedval="{$account.is_default ? '1' : '0'}">
                                    <option value="0">否</option>
                                    <option value="1">是</option>
                                </select>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="base_button_div p_b_20">
                <a href="javascript:;" class="base_button" ajax="post">添加</a>
            </div>
        </form>
    </div>

    <script>
        function func(){
            var vs = $('#select_type  option:selected').val();
            if (vs == 0 || vs == 1) {
                $(".bank").hide();
            } else {
                $(".bank").show();
            }
        }
    </script>
</block>
<extend name="public:base_iframe"/>
<block name="css">
</block>

<block name="js">
    <script>
        var countdown = '{$countdown}';
    </script>
    <script src="__JS__/user/reg.js"></script>
</block>

<block name="body">
    <div class="pa_10">
        <table class="content_info2">
            <tr>
                <td class="field_name">账号模式</td>
                <td>
                    <select class="base_select" name="mode">
                        <option value="normal">正常模式</option>
                       <!-- <option value="isvirtual">虚拟模式</option>-->
                    </select>
                  <!--    <p class="field_tip">虚拟模式下可以不输入手机号和验证码</p>-->
                </td>
            </tr>
            <tr>
                <td class="field_name">手机号：</td>
                <td>
                    <div class="base_group">
                        <input style="width: 309px !important;" name="phone" value="" type="text" class="base_text border_left_radius"/>
                        <a href="javascript:;" class="base_button base_button_gray send_btn border_right_radius">发送验证码</a>
                    </div>
                </td>
            </tr>
            <tr>
                <td class="field_name">验证码：</td>
                <td><input name="code" type="text" class="base_text" value=""/></td>
            </tr>
            <tr>
                <td class="field_name">{:config('app.agent_setting.promoter_name')}：</td>
                <td>
                    <div class="base_group">
                        <input placeholder="可选项" suggest-value="[name=promoter_uid]"
                               suggest="{:url('promoter/get_suggests')}" style="width: 309px !important;" value="" type="text"
                               class="base_text promoter_name border_left_radius"/>
                        <input type="hidden" name="promoter_uid" value=""/>
                        <a fill-value="[name=promoter_uid]" fill-name=".promoter_name"
                           layer-open="{:url('user/find',['user_type'=>'promoter'])}" href="javascript:;"
                           class="base_button base_button_gray border_right_radius">选择</a>
                    </div>
                </td>
            </tr>
            <tr>
                <td class="field_name">创建密码</td>
                <td>
                    <input name="password" type="password" class="base_text" value=""/>
                </td>
            </tr>
            <tr>
                <td class="field_name">确认密码</td>
                <td>
                    <input name="confirm_password" type="password" class="base_text" value=""/>
                </td>
            </tr>

        </table>

        <div class="mt_10 base_button_div max_w_528" style="text-align: center;">
            <div class="base_button reg_btn">注册</div>
        </div>
    </div>
</block>

<block name="layer">
</block>
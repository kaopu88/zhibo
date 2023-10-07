<div class="layer_box reg_box">
    <div class="pa_10">
        <ul class="reg_list">
            <li class="reg_li">
                <label>手机号：</label><input type="text" name="phone" class="base_text"/>
            </li>
            <li style="position: relative;" class="reg_li">
                <label>验证码：</label>
                <div class="base_group">
                    <input style="width: 209px;" name="code" value="" type="text" class="base_text border_left_radius"/>
                    <a href="javascript:;" class="base_button base_button_gray send_btn border_right_radius">发送验证码</a>
                </div>
            </li>

            <li class="reg_li">
                <label>创建密码：</label><input name="password" type="password" class="base_text" value=""/>
            </li>
            <li class="reg_li">
                <label>确认密码：</label><input name="confirm_password" type="password" class="base_text" value=""/>
            </li>
            <li class="reg_li">
                <label>{:config('app.agent_setting.promoter_name')}：</label>
                <div class="base_group">
                    <input placeholder="可选项" suggest-value="[name=promoter_uid]"
                           suggest="{:url('promoter/get_suggests')}" style="width: 209px;" value="" type="text"
                           class="base_text promoter_name border_left_radius"/>
                    <input type="hidden" name="promoter_uid" value=""/>
                    <a fill-value="[name=promoter_uid]" fill-name=".promoter_name" layer-open="{:url('user/find',['user_type'=>'promoter'])}" href="javascript:;" class="base_button base_button_gray border_right_radius">选择</a>
                </div>
                <div class="clear"></div>
            </li>
            <li class="reg_li">
                <label>&nbsp;</label>
                <div class="base_group" style="display: block;float: none;">
                    <span style="display: inline-block;line-height: 30px;">
                        <input checkedval="15" style="float: none;vertical-align: -2px;" type="checkbox" name="set_promoter" value="1">&nbsp;设置为{:config('app.agent_setting.promoter_name')}  </span>
                </div>
                <div class="clear"></div>
            </li>
        </ul>
        <div>
            <div class="base_button_div" style="max-width:403px;">
                <div class="base_button reg_sub_btn">注册</div>
            </div>
        </div>
    </div>
</div>
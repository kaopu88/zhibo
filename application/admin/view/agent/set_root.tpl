<extend name="public:base_nav"/>
<block name="js">
</block>

<block name="css">
</block>

<block name="body">
    <div class="pa_20">
        <div class="content_title">
            <h1>
                {$admin_last.name}&nbsp;【{$agent_info.name}】
            </h1>
            <a href="javascript:;" class="base_button base_button_gray base_button_s history_back">返回</a>
        </div>
        <form action="{:url('set_root')}">
            <table class="content_info2 mt_10">
                <tr>
                    <td class="field_name">用户名</td>
                    <td>
                        <input class="base_text" name="username" value="{$_info.username}"/>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">手机号</td>
                    <td>
                        <input placeholder="" class="base_text" name="phone" value="{$_info.phone}"/>
                        <p class="field_tip">手机号可用于登录、找回密码、安全验证、接收系统通知</p>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">登录密码</td>
                    <td>
                        <input type="password" class="base_text" name="password" value="{$_info.password}"/>
                        <p class="field_tip"<?php if($_info['password']){echo "hidden"; } ;?>>
                            6-16位，不能是纯数字&nbsp;&nbsp;<a class="random_btn" href="javascript:;">随机生成</a>
                            &nbsp;&nbsp;
                            <span class="pwd_tip"></span>
                        </p>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">确认密码</td>
                    <td>
                        <input type="password" class="base_text" name="confirm_password" value="{$_info.confirm_password}"/>
                    </td>
                </tr>
                <tr>
                    <td class="field_name"></td>
                    <td>
                        <input name="agent_id" type="hidden" value="{$_info.agent_id}"/>
                        __BOUNCE__
                        <a href="javascript:;" class="base_button" ajax="post">设置</a>
                    </td>
                </tr>
            </table>
        </form>
    </div>
    <script>
        $('.random_btn').click(function () {
            var pwd = $s.getUcode(2, 'a') + '' + $s.getUcode(6, '1');
            $('[name=password],[name=confirm_password]').val(pwd);
            $('.pwd_tip').text('密码是：' + pwd);
        });
    </script>
</block>
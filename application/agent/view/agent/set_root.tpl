<extend name="public:base_nav"/>
<block name="js">
</block>

<block name="css">
</block>

<block name="body">
    <div class="pa_20">
        <div class="content_title">
            <h1>
                {$agent_last.name}&nbsp;【{$agent_info.name}】
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
                    <td class="field_name">验证码</td>
                    <td>
                        <div class="base_group">
                            <input style="width: 308px;" name="code" value="" type="text" class="base_text">
                            <a href="javascript:;" class="base_button base_button_gray send_btn">获取验证码</a>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">登录密码</td>
                    <td>
                        <input type="password" class="base_text" name="password" value=""/>
                        <p class="field_tip">
                            6-16位，不能是纯数字&nbsp;&nbsp;<a class="random_btn" href="javascript:;">随机生成</a>
                            &nbsp;&nbsp;
                            <span class="pwd_tip"></span>
                        </p>
                    </td>
                </tr>
                <tr>
                    <td class="field_name">确认密码</td>
                    <td>
                        <input type="password" class="base_text" name="confirm_password" value=""/>
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

        var countdown = parseInt('{$countdown}'), isSend = false, sendTimer = null, $sendBtn;
        $(function () {
            $sendBtn = $('.send_btn');
            $sendBtn.click(function () {
                if (countdown > 0 || isSend) {
                    return false;
                }
                var phone = $('[name=phone]').val();
                if (isEmpty(phone)) {
                    return $s.error('请输入手机号');
                }
                if (!$s.validatePhone(phone)) {
                    return $s.error('手机号不正确');
                }
                $s.post(WEB_CONFIG.send_sms_code, {phone: phone, scene: 'set_root'}, function (result, next) {
                    if (result['status'] == 0) {
                        countdown = parseInt(result['data']['limit']);
                        checkSendTimer();
                    }
                    result['reload'] = false;
                    next();
                });
            });
            checkSendTimer();
        });

        function checkSendTimer() {
            if (sendTimer) {
                clearTimeout(sendTimer);
                sendTimer = null;
            }
            countdown = parseInt(countdown);
            countdown = (countdown === NaN) ? 0 : countdown;
            if (countdown > 0) {
                $sendBtn.text(countdown + 's后重试');
                sendTimer = setTimeout(function () {
                    countdown--;
                    countdown = countdown < 0 ? 0 : countdown;
                    sendTimer = null;
                    checkSendTimer();
                }, 1000);
            } else {
                $sendBtn.text('获取验证码');
            }
        }
    </script>
</block>
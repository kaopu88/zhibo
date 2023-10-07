layui.use('form', function() {
    var form = layui.form,
        $ = layui.jquery,
        check = true, // 手机号验证状态
        input_state = true, // 输入框状态
        all_input = ".password,.new_password,.password_app,.new_password_app,.re_password,.re_password_app", //全部密码框
        i = '.user_err,.password_err,.warn_password,.warn_code,.warn_user'; // 提示信息

    // 设置密码框的type为password
    $(all_input).attr('type', 'password');

    // 验证用户名
    function checkMobile(str) {
        var re = /^[\u4e00-\u9fa5_a-zA-Z0-9]+$/;
        if (re.test(str)) {
            check = true;
            return;
        } else {
            input_state = false;
            check = false;
            return;
        }
    }

    // 验证用户名
    function checkUser(v) {
        checkMobile($(v).val());
        if (check === false) {
            input_state = false;
            $('.user_err').text('您输入的用户名不合法').show();
            $('.hr_user').removeClass('removefocus');
            $('.hr_user').removeClass('getfocus');
            $('.hr_user').css('border-bottom', '2px solid rgb(250, 76, 85)');
        } else {
            input_state = true;
            $(i).hide();
            $('.hr_user').removeClass('removefocus');
            $('.hr_user').removeClass('getfocus');
            $(v).css('animation', 'none');
            $('.user,.user_app,.password,.password_app,.my_account,.code,.new_password,.re_password').removeClass('change');
            $('.hr').css('border-bottom', '1px solid #e6e6e6');
        }
    }

    // 表单数值为空时
    function form_null(err, hr, key, text) {
        $(err).text(text).show();
        $(hr).removeClass('removefocus').css('border-bottom', '2px solid rgb(250, 76, 85)');
        $(key).addClass('change');
        input_state = false;
        return false;
    }

    // 表单验证 lay-verify
    form.verify({
        user: function(value) {
            if (value == '') {
                form_null('.user_err', '.hr_user', '.user', '请输入用户名');
                return false;
            } else {
                checkUser('.user');
            }
        },
        password: function(value) {
            if (value == '') {
                form_null('.password_err', '.hr_password', '.password', '请输入密码');
            }
        },
        user_app: function(value) {
            if (value == '') {
                form_null('.user_err', '.hr_user', '.user_app', '请输入用户名');
            } else {
                checkUser('.user_app');
            }
        },
        password_app: function(value) {
            if (value == '') {
                form_null('.password_err', '.hr_passwordser', '.user_password_app', '请输入密码');
            }
        },
        my_account: function(value) {
            if (value == '') {
                form_null('.user_err', '.hr_user', '.my_account', '请输入用户名');
            } else {
                checkUser('.my_account');
            }
        },
        code: function(value) {
            if (value == '') {
                form_null('.warn_code', '.hr_code', '.code', '请输入验证码');
            }
        },
        new_password: function(value) {
            if (value == '') {
                form_null('.warn_password', '.hr_password', '.new_password', '请输入密码');
            } else {
                if (value !== $('.re_password').val()) {
                    $('.warn_password').text('与新密码不相同').show();
                    input_state = false;
                } else {
                    $('.warn_password').hide();
                }
            }
        },
        re_password: function(value) {
            if (value == '') {
                form_null('.warn_password', '.hr_repassword', '.re_password', '请输入密码');
            }
        },
        my_account_app: function(value) {
            if (value == '') {
                form_null('.user_err', '.hr_user', '.my_account_app');
            } else {
                checkUser('.my_account_app');
            }
        },
        code_app: function(value) {
            if (value == '') {
                form_null('.warn_code', '.hr_code', '.code_app');
            }
        },
        new_password_app: function(value) {
            if (value == '') {
                form_null('.warn_password', '.hr_password', '.new_password_app');
            } else {
                if (value !== $('.re_password_app').val()) {
                    $('.warn_password').text('与新密码不相同').show();
                    input_state = false;
                } else {
                    $('.warn_password').hide();
                }
            }
        },
        re_password_app: function(value) {
            if (value == '') {
                form_null('.warn_password', '.hr_repassword', '.re_password', '请输入密码');
            }
        },
    });

    $(".web_login_btn").click(function () {
        if ($('#username').val() !== '' && $('#password').val() !== '') {
            checkMobile($('#user').val());
        };

        var username = $('[name=username]').val();
        var password = $('[name=password]').val();
        var autoLogin = $('[name=auto_login]').prop('checked');
        if (isEmpty(username)) {
            form_null('.user_err', '.hr_user', '.my_account', '请输入用户名');
            return false;
        }
        if (isEmpty(password)) {
            form_null('.password_err', '.hr_password', '.password', '请输入密码');
            return false;
        }
        var data = {
            username: username,
            password: password,
            auto_login: autoLogin ? "1" : "0",
            redirect: $('[name=redirect]').val()
        };
        isLogin = true;
        $.post(loginUrl, data, function (result, next) {
            isLogin = false;
            if (result.status == 1) {
                form_null('.password_err', '.hr_password', '.password', result.message);
                return;
            }
            setTimeout(function () {
                window.location = result.url;
            }, 500);
        });
    });

    $(".app_login_btn").click(function () {
        if ($('#username').val() !== '' && $('#password').val() !== '') {
            checkMobile($('#user').val());
        };

        var username = $('[name=app_username]').val();
        var password = $('[name=app_password]').val();
        var autoLogin = $('[name=auto_login]').prop('checked');
        if (isEmpty(username)) {
            form_null('.user_err', '.hr_user', '.my_account', '请输入用户名');
            return false;
        }
        if (isEmpty(password)) {
            form_null('.password_err', '.hr_password', '.password', '请输入密码');
            return false;
        }
        var data = {
            username: username,
            password: password,
            auto_login: autoLogin ? "1" : "0",
            redirect: $('[name=redirect]').val()
        };
        isLogin = true;
        $.post(loginUrl, data, function (result, next) {
            isLogin = false;
            if (result.status == 1) {
                form_null('.password_err', '.hr_password', '.password', result.message);
                return;
            }
            setTimeout(function () {
                window.location = result.url;
            }, 500);
        });
    });

    // 点击显示密码
    var state = 0;
    $('.eye_icon').click(() => {
        if (state === 0) {
            $(".password,.new_password,.password_app,.new_password_app").attr("type", "text");
            $(".eye_icon").css("background-image", "url('/bx_static/admin/assets/icon-display-blue@3x.png')");
            state = 1;
            return state;
        } else {
            $(".password,.new_password,.password_app,.new_password_app").attr("type", "password");
            $(".eye_icon").css("background-image", "url('/bx_static/admin/assets/icon-display@3x.png')");
            state = 0;
            return state;
        }
    });

    // 输入框获取/失去焦点特效
    function special(key, value, text, err) {
        // 获取焦点
        $(key).focus(() => {
            if (input_state === true) {
                $(value).removeClass('removefocus');
                $(value).addClass('getfocus');
                $(key).css('animation', 'input_background 0.2s');
                $(key).attr('placeholder', '');
            }
        });

        // 失去焦点
        $(key).blur(() => {
            if ($(key).val() !== '') {
                if (key === '.user' || key === '.user_app' || key === '.my_account' || key === '.my_account_app') {
                    checkUser(key);
                } else if (key === '.re_password' || key === '.re_password_app') {
                    if ($(key).val() === $('.new_password').val() || $(key).val() === $('.new_password_app').val()) {
                        $('.warn_password').hide();
                        $(key).css('animation', 'none');
                        $('.hr_repassword').removeClass('getfocus').css('border-bottom', '1px solid #e6e6e6');
                        input_state = true;
                    } else {
                        $('.warn_password').text('与新密码不相同').show();
                        $('.hr_repassword').removeClass('getfocus').css('border-bottom', '2px solid #FF3065');
                        input_state = false;
                    }
                } else {
                    input_state = true;
                    $(value).removeClass('getfocus');
                    $(key).removeClass('change');
                    $(value).addClass('removefocus');
                    $(key).css('animation', 'none');
                    $(key).attr('placeholder', text);
                    $(err).hide();
                }
            } else {
                $(value).removeClass('getfocus');
                $(value).removeClass('removefocus');
                $(key).css('animation', 'none');
                $(value).css('border-bottom', '2px solid rgb(250, 76, 85)');
                $(err).text(text).show();
                $(key).attr('placeholder', text);
                $(key).addClass('change');
                input_state = false;
            }
        });
    }

    // 调用获取较焦点特效
    special('.password', '.hr_password', '请输入密码', '.password_err');
    special('.user', '.hr_user', '请输入用户名', '.user_err');
    special('.password_app', '.hr_password', '请输入密码', '.password_err');
    special('.user_app', '.hr_user', '请输入用户名', '.user_err');
    special('.my_account', '.hr_user', '用户名', '.user_err');
    special('.code', '.hr_code', '验证码', '.warn_code');
    special('.new_password', '.hr_password', '新密码', 'warn_user');
    special('.re_password', '.hr_repassword', '确认密码', '.warn_password');
    special('.my_account_app', '.hr_user', '用户名', '.user_err');
    special('.code_app', '.hr_code', '验证码', '.warn_code');
    special('.new_password_app', '.hr_password', '新密码', 'warn_user');
    special('.re_password_app', '.hr_repassword', '确认密码', '.warn_password');

    // 点击联系我们
    $('.contact_us').click(() => {
        $('.login_welcome').hide();
        $('.contact').show();
        $('.fogetpassword_reset').hide();
    });
    $('.fogetpassword_contact_us').click(() => {
        $('.contact').show();
        $('.fogetpassword_reset').hide();
    });

    // 点击返回
    $('.back_btn').click(() => {
        $('.login_welcome').show();
        $('.contact').hide();
        $('.fogetpassword_reset').hide();
    });
    $('.fogetpassword_back_btn').click(() => {
        $('.contact').hide();
        $('.fogetpassword_reset').show();
    });

    // 发送验证码
    var istime = true;
    $('.send_code').click(() => {
        console.log(istime);
        if (istime && $('.my_account').val() !== '' || $('.my_account_app').val() !== '') {
            getCode($('.send_code'), 60);
        } else {
            $('.user_err').text('请输入用户名').show();
            $('.my_account,.my_account_app').addClass('change');
            $('.hr_user').removeClass('removefocus').css('border-bottom', '2px solid rgb(250, 76, 85)');
            input_state = false;
        }
    });

    // 获取验证码（进入倒计时）
    function getCode(a, n) {
        let account = $('.my_account').val() || $('.my_account_app').val();
        checkMobile(account);
        if (check === true) {
            istime = false;
            input_state = true;
            a.text(n + "s");
            var times = setTimeout(changetime, 1000);

            function changetime() {
                if (n > 0) {
                    n--;
                    a.text(n + "s");
                    times = setTimeout(changetime, 1000);
                    $('.send_code').css('pointer-events', 'none');
                } else {
                    clearTimeout(times);
                    a.text("重新获取");
                    $('.send_code').css('pointer-events', 'auto');
                    istime = true;
                }
            }
        } else {
            $('.warn_user').text('您输入的手机号或邮箱不合法').show();
            $('hr_user').removeClass('removefocus').css('border-bottom', '2px solid rgb(250, 76, 85)');
            input_state = false;
        }
    }
})


function isEmpty(val) {
    if (typeof val == 'object') {
        if (Array.isArray(val)) {
            return val.length <= 0;
        } else {
            for (let key in val) {
                return false;
            }
            return true;
        }
    } else if (typeof val == 'undefined') {
        return true;
    } else {
        return (val === null || val === false || val === '' || val === 0 || val === '0') ? true : false;
    }
}
<?php

use bxkj_common\DataFactory as DataFactory;

$rules = [
    'create@user' => array(
    'allow' => 'phone_code,phone,password,confirm_password,code,anchor_uid,promoter_uid,agent_id,invite_code',
    'must' => 'phone,password',
    'validate' => array(
        //array('username', 'regex', 'require', '用户名不能为空'),
        //array('username', 'length', '4,25', '用户名4-25个字符'),
        //array('username', 'not_regex', 'number', '用户名不能为纯数字'),
        array('phone', 'regex', 'require', '手机号不能为空'),
        array('phone', 'regex', 'phone', '手机号格式不正确'),
        array('password', 'regex', 'require', '密码不能为空'),
        array('password', 'regex', 'no_blank', '密码不能包含空格'),
        array('password', 'not_regex', 'number', '密码不能为纯数字'),
        array('password', 'length', '6,16', '密码6-16位字符'),
        array('confirm_password', 'confirm', 'password', '两次密码输入不一致')
    ),
    'fill' => array(
        array('create_time', 'time', '', DataFactory::ANY)
    )
),
    'save@user' => array(
        'allow' => 'user_id,avatar,nickname,gender,birthday,city_id,sign,district_id,province_id,cover,album,weight,height,voice_sign,voice_time',
        'must' => 'user_id',
        'ignore' => 'nickname:keep',
        'validate' => array(
            array('user_id', 'regex', 'require', 'USER_ID不能为空'),
            array('avatar', 'regex', 'require', '请上传头像'),
            // array('avatar', '@validateAvatar', '', '头像地址不合法'),
            array('cover', 'regex', 'require', '请上传封面'),
            // array('cover', '@validateCover', '', '封面地址不合法'),
            array('nickname', 'regex', 'require', '昵称不能为空'),
            array('nickname', 'length', ',15', '昵称不能超过15个字符'),
            array('weight', 'length', ',4', '身高错误'),
            array('height', 'length', ',4', '昵称错误'),
            array('gender', 'in', '0,1,2', '性别不正确'),
            array('birthday', 'regex', 'require', '请选择生日'),
            array('birthday', 'regex', 'date', '生日格式不正确'),
            array('birthday', 'date_max', 'now', '生日不能大于当前时间'),
            array('province_id', 'regex', 'require', '请选择省份'),
            array('city_id', 'regex', 'require', '请选择城市'),
            array('district_id', 'regex', 'require', '请选择区县'),
        ),
        'fill' => array(
            array('update_time', 'time', '', DataFactory::ANY),
            array('birthday', 'strtotime', '', DataFactory::NOT_EMPTY),
        )
    ),
    'unifiedorder@third_trade' => array(
        'allow' => 'user_id,pay_method,extra_data,rel_type,rel_no,valid_period,client_ip,app_v,notify_url,return_url,openid',
        'must' => 'user_id,pay_method,rel_type,rel_no,notify_url',
        'validate' => array(
            array('user_id', 'regex', 'require', 'USER_ID不能为空'),
            array('rel_type', 'regex', 'require', '订单类型不能为空'),
            array('rel_no', 'regex', 'require', '订单号不能为空'),
            array('pay_method', 'regex', 'require', '支付方式不能为空'),
            array('pay_method', 'in_enum', 'pay_methods', '支付方式不支持'),
            //array('subject', 'regex', 'require', '订单主题不能为空'),
            //array('total_fee', 'regex', 'require', '交易金额不能为空'),
            //array('total_fee', 'currency', '', '交易金额不正确'),
            array('valid_period', 'regex', 'integer', '过期时间不正确'),
            array('notify_url', 'regex', 'require', '通知地址不能为空'),
        )
    ),
];



use think\facade\Env;

$env = Env::get('RUN_ENV');

if (!empty($env))
{
    $path = ROOT_PATH.'config/'.$env.'/rules.php';

    if (file_exists($path))
    {
        $env_config = require_once $path;

        $rules = array_merge($rules, $env_config);
    }
}

return $rules;
<?php
/**
 * Created by PhpStorm.
 * User: “崔鹏
 * Date: 2020/07/08
 * Time: 上午 3:20
 */

namespace app\h5\validate;

use think\Validate;

class User extends Validate
{
    protected $rule = [
        'username' => 'require|max:30',
        'password'   => 'require|max:20',

    ];
    protected $message = [
        'username.require' => '账号不能为空',
        'username.max'     => '账号最大长度不能超过30',
        'password.require'   => '密码不能为空',
        'password.max'     => '密码最大长度不能超过20',
    ];
    protected $scene = [
        'login' => ['username', 'password'],
    ];
}
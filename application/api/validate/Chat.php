<?php
/**
 * Created by PhpStorm.
 * User: “崔鹏
 * Date: 2020/06/20
 * Time: 上午 11:20
 */

namespace app\api\validate;

use think\Validate;

class Chat extends Validate
{
    protected $rule = [
        'messages' => 'require|max:2000',
        'to_uid'   => 'require|number',
        'from_uid' => 'require|number',
    ];
    protected $message = [
        'messages.require' => '内容不能为空',
        'messages.max'     => '最大长度不能超过2000',
        'to_uid.require'   => '接收人id不能为空',
        'to_uid.number'    => '接收人id必须为数字',
        'from_uid.require' => '查看用户id不能为空',
        'from_uid.number'  => '查看用户id必须为数字',
    ];
    protected $scene = [
        'sendMsg' => ['messages', 'to_uid'],
        'seeMsg'  => ['from_uid'],
        'taskPayment'  => ['to_uid'],
    ];
}
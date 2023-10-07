<?php
/**
 * Created by PhpStorm.
 * User: “崔鹏
 * Date: 2020/07/03
 * Time: 上午 13:25
 */

namespace app\api\validate;

use think\Validate;

class Menu extends Validate
{
    protected $rule = [
        'content' => 'require|max:2000',
        'menu_id' => 'require|number',
    ];
    protected $message = [
        'content.require' => '内容不能为空',
        'content.max'     => '最大长度不能超过2000',
        'menu_id.require' => '分类类型不能为空',
        'menu_id.number'  => '分类类型必须为数字',
    ];
    protected $scene = [
        'getSecondMenu' => ['menu_id'],
    ];
}
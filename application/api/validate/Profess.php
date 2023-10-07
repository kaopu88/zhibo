<?php
/**
 * Created by PhpStorm.
 * User: “崔鹏
 * Date: 2020/06/28
 * Time: 上午 15:25
 */

namespace app\api\validate;

use think\Validate;

class Profess extends Validate
{
    protected $rule = [
        'content' => 'require|max:2000',
        'classid' => 'require|number',
        'fcmid'   => 'require|number',
        'touid'   => 'number',
        'imgs'    => 'checkIMgs:图片后缀不正确！',
    ];
    protected $message = [
        'content.require' => '内容不能为空',
        'content.max'     => '最大长度不能超过2000',
        'classid.require' => '分类类型不能为空',
        'classid.number'  => '分类类型必须为数字',
        'fcmid.require'   => '表白id不能为空',
        'fcmid.number'    => '表白id必须为数字',
        'touid.number'    => '回复id必须为数字',
    ];
    protected $scene = [
        'getProfessClassfyList' => ['classid'],
        'leaveMsg'              => ['fcmid', 'content', 'touid'],
        'confessionlist'        => ['fcmid'],
    ];

    protected function checkIMgs($value, $rule, $data)
    {
        $val2Array = explode(',', $value);
        foreach ($val2Array as $k => $v) {
            if (!preg_match('/.*?(jpg|jpeg|gif|png)/', $v)) {
                return $rule;
            }
        }
        return true;
    }
}
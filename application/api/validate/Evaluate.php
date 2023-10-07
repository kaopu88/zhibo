<?php
/**
 * Created by PhpStorm.
 * User: “崔鹏
 * Date: 2020/06/20
 * Time: 上午 11:20
 */

namespace app\api\validate;

use think\Validate;

class Evaluate extends Validate
{
    protected $rule = [
        'content'      => 'require|max:1000',
        'commentid'    => 'require|number',
        'commentmsgid' => 'require|number',
        'evalid' => 'require|number',

    ];
    protected $message = [
        'content.require'      => '内容不能为空',
        'content.max'          => '最大长度不能超过1000',
        'commentid.require'    => '评论id不能为空',
        'commentid.number'     => '评论id必须为数字',
        'commentmsgid.require' => '留言id不能为空',
        'commentmsgid.number'  => '留言id必须为数字',
        'evalid.require' => 'id不能为空',
        'evalid.number'  => 'id必须为数字',
    ];
    protected $scene = [
        'evaluateMsg'  => ['content', 'commentid'],
        'evaluateLive' => ['commentmsgid'],
        'evaluateList' => ['commentid'],
        'delConfessionEvaluate'=>['evalid'],
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
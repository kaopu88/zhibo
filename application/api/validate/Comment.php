<?php
/**
 * Created by PhpStorm.
 * User: “崔鹏
 * Date: 2020/06/19
 * Time: 上午 10:05
 */

namespace app\api\validate;

use think\Validate;

class Comment extends Validate
{
    protected $rule = [
        'img'       => 'checkIMgs:图片后缀不正确！',
        'content'   => 'require|max:2000',
        'fcmid'     => 'require|number',
        'commentid' => 'require|number',
    ];
    protected $message = [
        'content.require'   => '内容不能为空',
        'content.max'       => '最大长度不能超过2000',
        'fcmid.require'     => '信息id不能为空',
        'fcmid.number'      => '信息id必须为数字',
        'commentid.require' => '评论id不能为空',
        'commentid.number'  => '评论id必须为数字',
    ];
    protected $scene = [
        'CommentMsg'    => ['img', 'fcmid'],
        'commentLive'   => ['commentid'],
        'commentList'   => ['fcmid'],
        'commentDetail' => ['commentid'],
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
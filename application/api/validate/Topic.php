<?php
/**
 * Created by PhpStorm.
 * User: “崔鹏
 * Date: 2020/06/20
 * Time: 上午 11:20
 */

namespace app\api\validate;

use think\Validate;

class Topic extends Validate
{
    protected $rule = [
        'content'    => 'require|max:1000',
        'classid'    => 'require|number',
        'topic_name' => 'require|max:20',
        'page_index' => 'require|number',
        'topic_id'   => 'require|number',
    ];
    protected $message = [
        'content.require'    => '内容不能为空',
        'content.max'        => '最大长度不能超过1000',
        'classid.require'    => '分类类型不能为空',
        'classid.number'     => '分类类型必须为数字',
        'topic_name.require' => '话题不能为空',
        'topic_name.max'     => '最大长度不能超过20',
        'topic_id.require'   => '话题id不能为空',
        'topic_id.number'    => '话题id必须为数字',
    ];
    protected $scene = [
        'addTopic'    => ['topic_name'],
        'getTopic'    => ['page_index'],
        'followTopic' => ['topic_id'],
    ];
}
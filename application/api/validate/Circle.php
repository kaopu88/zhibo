<?php
/**
 * Created by PhpStorm.
 * User: “崔鹏
 * Date: 2020/06/28
 * Time: 上午 10:20
 */

namespace app\api\validate;

use think\Validate;

class Circle extends Validate
{
    protected $rule = [
        'circle_name'           => 'require|max:200',
        'circle_describe'       => 'require|max:200',
        'circle_cover_img'      => 'require|checkIMgs:图片后缀不正确！',
        'circle_background_img' => 'require|checkIMgs:图片后缀不正确！',
        'circle_type'           => 'require|in:1,2',
        'key_words'             => 'require|max:100',
        'page_index'            => 'require|number',
        'circle_id'             => 'require|number',
        'uid'                   => 'require|number',
        'status'                => 'require|in:0,1,2',
       // 'update_status'         => 'require|in:1,2,3,4',
    ];
    protected $message = [
        'circle_name.require'     => '标题不能为空',
        'circle_name.max'         => '最大长度不能超过200',
        'circle_describe.require' => '内容不能为空',
        'circle_describe.max'     => '最大长度不能超过200',
        'to_uid.require'          => '接收人id不能为空',
        'to_uid.number'           => '接收人id必须为数字',
        'from_uid.require'        => '查看id不能为空',
        'from_uid.number'         => '查看id必须为数字',
        'key_words.require'       => '搜索内容不能为空',
        'key_words.max'           => '搜索内容最大长度不能超过100',
        'page_index.require'      => '分页id不能为空',
        'page_index.number'       => '分页id必须为数字',
        'circle_id.require'       => '圈子id不能为空',
        'circle_id.number'        => '圈子id必须为数字',
        'uid.require'             => '用户id不能为空',
        'uid.number'              => '用户id必须为数字',
    ];
    protected $scene = [
        'createCircle'         => ['circle_name', 'circle_describe', 'circle_cover_img', 'circle_background_img'],
        'getCircleMsg'         => ['circle_type', 'page_index'],
        'searchCircle'         => ['key_words', 'page_index'],
        'circleMyFollowed'     => ['page_index'],
        'followCircle'         => ['circle_id'],
        'circleMemeberManager' => ['circle_id'],
        'getCommonMember'      => ['circle_id'],
        'getEstoppelMember'    => ['circle_id'],
        'actEstoppel'          => ['uid', 'status', 'circle_id'],
        'actSetAdmin'          => ['uid', 'circle_id'],
        'actsetexpel'          => ['uid', 'circle_id'],
        'detailCircle'         => ['circle_id'],
        'saveCircle'           => ['circle_id','circle_name', 'circle_describe', 'circle_cover_img', 'circle_background_img'],
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
<?php
/**
 * Created by PhpStorm.
 * User: “崔鹏
 * Date: 2020/07/21
 * Time: 下午 14:56
 */

namespace app\api\validate;

use think\Validate;

class Sing extends Validate
{
    protected $rule = [
        'author_id' => 'require|number',
        'page_index' => 'require|number',
        'key' => 'require',
        'stype'=> 'require|in:1,2',
    ];
    protected $message = [
        'author_id.require' => '作者id不能为空',
        'author_id.number'  => '作者id必须为数字',
        'page_index.require' => '页号不能为空',
        'page_index.number'  => '页号必须为数字',
        'key.require' => '搜索词不能为空',
        'stype.require' => '搜索分类不能为空',
        'stype.in' => '搜索分类必须为1,2',

    ];
    protected $scene = [
        'getAuthorWorks' => ['author_id','page_index'],
        'searchList'    =>['key'],
        'lyricsorAuthor' =>['key','stype']
    ];
}
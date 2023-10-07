<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/9/29 0029
 * Time: 下午 7:38
 */
namespace app\giftdistribute\validate;

use think\Validate;

class GiftCommissionLevel extends Validate
{
    protected $rule = [
        'level_name' => 'require',
        'one_rate'=> 'number|between:1,100',
        'two_rate'=> 'number|between:1,100',
        'three_rate'=> 'number|between:1,100',
        'upgrade_type'=> 'require',
    ];

    protected $message = [
        'level_name.require' => '等级名称不能为空',
        'upgrade_type.require' => '升级条件必选',
        'one_rate.number' => '佣金比例不能为空',
        'one_rate.between'  => '佣金比例只能在1-100之间',
        'two_rate.number' => '佣金比例不能为空',
        'two_rate.between'  => '佣金比例只能在1-100之间',
        'three_rate.number' => '佣金比例不能为空',
        'three_rate.between'  => '佣金比例只能在1-100之间',
    ];
}
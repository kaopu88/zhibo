<?php
/**
 * Created by PhpStorm.
 * User: zack
 * Date: 2020/5/8 0008
 * Time: 下午 5:02
 */

namespace app\videotask\validate;

use think\Validate;

class SysConfig extends Validate
{
    protected $ruleArray = ['sign_reward', 'sign_continuity_reward', 'sign_day', 'sign_millet', 'sign_warn_reward', 'sign_bean', 'video_num', 'video_millet', 'video_bean'];

    protected $rule = [
        'value' => 'require|checkData',
    ];

    protected $message = [
        'value.require' => '不能为空',
    ];

    protected function checkData($value)
    {
        if ($value['is_sign_circle'] <= 0) return '签到周期必须是正整数';
        if ($value['is_sign_circle'] > 365 ) return '签到周期不能大于365天';
        if (mb_strlen($value['is_video_brief'],'UTF8') > 30 || mb_strlen($value['sign_brief'],'UTF8') > 30|| mb_strlen($value['is_withdraw_brief'],'UTF8') > 30 ) return '简要不能大于30个字符';
        if (!is_numeric($value['is_sign_circle']))  return '签到周期必须是数字';

        foreach ($value as $key => $va) {
            if (in_array($key, $this->ruleArray)) {
                foreach ($va as $k => $v) {
                    if ($key == 'sign_day') {
                        if ($v > $value['is_sign_circle']) return '连续签到不能大于签到周期';
                    }

                    if (empty($v)) continue;
                    if ($v < 0) return '必须是正整数';
                    if (!is_numeric($v))  return '必须是数字';
                }
            }
        }

        return true;
    }
}
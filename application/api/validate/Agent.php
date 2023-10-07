<?php
/**
 * Created by PhpStorm.
 * User: “崔鹏
 * Date: 2020/07/03
 * Time: 上午 13:25
 */

namespace app\api\validate;

use think\Validate;

class Agent extends Validate
{
    protected $rule = [
        'subject_type' => 'require|max:20',
        'name' => 'require',
        'legal_name' => 'require',
        'contact_phone'             => 'require|max:11|/^1[3-8]{1}[0-9]{9}$/',
        'legal_id'           => 'require|validation_filter_id_card:身份证号码格式不对',
        'contact_name' => 'require',
        'address_code' => 'require',
        'temppass' => 'require|min:8',
    ];
    protected $message = [
        'name.require' => '工会名称不能为空',
        'legal_name.require' =>'法人的姓名不能为空',
        'legal_id.require'           => '身份证号不能为空',
        'legal_id.idCard'            => '身份证号不符合规则',
        'temppass.require' => '密码不能为空',
        'temppass.min'     => '密码最小长度不能少于6位',
    ];
    protected $scene = [
        'applyCreateAgent' => ['name','legal_name','legal_id', 'temppass' ],
    ];

    /***
     * 身份证真实性验证规则
     */
    function validation_filter_id_card($id_card, $rule, $data)
    {
        if (strlen($id_card) == 18) {
            if (idcard_checksum18($id_card) == false) {
                return $rule;
            } else {
                return true;
            }
        } elseif ((strlen($id_card) == 15)) {
            $id_card = idcard_15to18($id_card);
            if (idcard_checksum18($id_card) == false) {
                return $rule;
            } else {
                return true;
            }
        } else {
            return $rule;
        }
    }
}
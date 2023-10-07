<?php

use bxkj_common\DataFactory as DataFactory;

$rules = [];

$rules['common@agent'] = [
    'deny' => 'create_time,update_time,sec_num,root_id,promoter_num,anchor_num',
    'validate' => array(
        array('name', 'regex', 'require', config('app.agent_setting.agent_name').'名称不能为空'),
        array('name', 'exc_unique', '', config('app.agent_setting.agent_name').'名称已存在'),
        array('grade', 'regex', 'require', '请选择'.config('app.agent_setting.agent_name').'级别'),
        array('grade', 'in_enum', 'agent_grades', config('app.agent_setting.agent_name').'级别不存在'),
        array('area_id', 'regex', 'require', '请选择地区'),
        array('area_id', 'region', '3', '地区不存在'),
        array('subject_type', 'regex', 'require', '请选择主体类型'),
        array('subject_type', 'in_enum', 'agent_subject_types', '主体类型不存在'),
        array('legal_name', 'regex', 'require', '法人姓名不能为空'),
        array('legal_id', 'regex', 'require', '法人身份证号不能为空'),
        array('legal_id', 'regex', '/^[1-9]\d{5}[1-9]\d{3}((0\d)|(1[0-2]))(([0|1|2]\d)|3[0-1])\d{3}([0-9]|X)$/i', '法人身份证号不正确'),
        array('contact_name', 'regex', 'require', '联系人不能为空'),
        array('contact_phone', 'regex', 'require', '联系电话不能为空'),
        array('contact_phone', 'regex', 'phone', '联系电话不正确'),
        array('contact_email', 'regex', 'email', '联系邮箱不正确', DataFactory::NOT_EMPTY),
        array('expire_time', 'regex', 'require', '请选择到期时间'),
        array('max_sec_num', '@validateMaxNum', '', '二级'.config('app.agent_setting.agent_name').'限额不正确或者允许新增的情况下限额值需要大于0'),
        array('max_promoter_num', '@validateMaxNum', '', config('app.agent_setting.promoter_name').'限额不正确或者允许新增的情况下限额值需要大于0'),
        array('max_anchor_num', '@validateMaxNum', '', '主播限额不正确或者允许新增的情况下限额值需要大于0'),
        array('max_virtual_num', '@validateMaxNum', '', '协议号限额不正确或者允许新增的情况下限额值需要大于0'),
    ),
    'fill' => array(
        array('expire_time', 'strtotime', '', DataFactory::NOT_EMPTY),
        array('area_id', 'region_extend', '1,2,3', DataFactory::NOT_EMPTY),
    )
];

$rules['add2@agent'] = [
    'extends' => 'common@agent',
    'must' => 'name,area_id,subject_type,contact_name,contact_phone',
    'validate' => array(
        array('expire_time', 'expire', '', '到期时间不正确'),
    ),
    'fill' => array(
        array('create_time', 'time', '', DataFactory::ANY),
        array('status', 'string', '1', DataFactory::NOT_HAS)
    )
];

$rules['update2@agent'] = [
    'extends' => 'common@agent',
    'ignore' => 'name:keep',
    'must' => 'id',
    'fill' => array(
        array('update_time', 'time', '', DataFactory::ANY),
    )
];

$rules['set_root@agent_admin'] = [
    'deny' => '',
    'must' => 'username,password,agent_id',
    'validate' => array(
        array('username', 'regex', 'require', '用户名不能为空'),
        array('username', 'strlen', '4,30', '用户名4-30位字符'),
        array('username', 'regex', 'no_blank', '用户名不能包含空白字符'),
        array('username', 'not_regex', 'phone', '用户名不能使用手机号格式'),
        array('username', 'not_regex', 'email', '用户名不能使用邮箱格式'),
        array('username', '@validateUsername', '', '用户名已经存在'),
        array('phone', 'regex', 'phone', '手机号不正确', DataFactory::NOT_EMPTY),
        array('phone', '@validatePhone', '', '手机号已经存在', DataFactory::NOT_EMPTY),
        array('password', 'regex', 'require', '密码不能为空'),
        array('password', 'regex', 'no_blank', '密码不能包含空格'),
        array('password', 'not_regex', 'number', '密码不能为纯数字'),
        array('password', 'length', '6,16', '密码6-16位字符'),
        array('confirm_password', 'confirm', 'password', '两次密码输入不一致'),
    ),
    'fill' => array(
        array('create_time', 'time', '', DataFactory::ANY),
        array('status', 'string', '1', DataFactory::NOT_HAS),
    )
];

/*** agent_admin ***/

$rules['common@agent_admin'] = [
    'deny' => 'create_time,update_time,login_time,login_ip',
    'alias' => 'username 用户名,password 密码,phone 手机号',
    'validate' => array(
        array('promoter_uid', 'regex', 'require', config('app.agent_setting.promoter_name').'必须选择'),
        array('username', 'regex', 'require', '用户名不能为空'),
        array('username', 'strlen', '4,30', '用户名4-30位字符'),
        array('username', 'regex', 'no_blank', '用户名不能包含空白字符'),
        array('username', 'not_regex', 'phone', '用户名不能使用手机号格式'),
        array('username', 'not_regex', 'email', '用户名不能使用邮箱格式'),
        array('username', 'unique', '', '用户名已经存在'),
        array('password', 'regex', 'require', '密码不能为空'),
        array('password', 'regex', 'no_blank', '密码不能包含空白字符'),
        array('password', 'strlen', '6,16', '密码6-16位字符'),
        array('confirm_password', 'confirm', 'password', '密码两次输入不一致'),
        array('phone', 'regex', 'phone', '手机号不正确', DataFactory::NOT_EMPTY),
        array('phone', 'unique', '', '手机号已经存在', DataFactory::NOT_EMPTY)
    ),
];

$rules['add@agent_admin'] = [
    'extends' => 'common@agent_admin',
    'must' => 'username,password,agent_id',
    'fill' => array(
        array('create_time', 'time', '', DataFactory::ANY),
        array('status', 'string', '1', DataFactory::NOT_HAS),
    )
];

$rules['update@agent_admin'] = [
    'extends' => 'common@agent_admin',
    'ignore' => 'username:keep,phone:keep',
    'deny' => 'username,realname,phone,password',
    'must' => 'id,agent_id',
    'fill' => array(
        array('update_time', 'time', '', DataFactory::ANY),
    )
];

return $rules;
<?php
use \bxkj_common\DataFactory;
return [
    'common@cash_account' => array(
        'deny' => 'card_name,verify_status,delete_time',
        'must' => 'type,account',
        'validate' => array(
            array('user_id', 'regex', 'require', 'USERID不能为空'),
            array('type', 'in_enum', 'cash_account_types', '账号类型不正确'),
            array('account', 'regex', 'require', '账号不能为空'),
            array('name', 'regex', 'require', '姓名不能为空'),
        ),
        'fill' => array(
            array('account', '@fillCardName', ''),
        )
    ),
    'add@cash_account' => array(
        'extends' => 'common@cash_account',
        'must' => 'user_id,name',
        'validate'=>array(
            array('account', '@validateAccount', '', '提现账号已存在'),
        ),
        'fill' => array(
            array('verify_status', 'string', '0', DataFactory::ANY),
            array('create_time', 'time', '', DataFactory::ANY)
        )
    ),
    'update@cash_account' => array(
        'extends' => 'common@cash_account',
        'must' => 'id',
        'validate' => array(
            array('id', 'regex', 'require', 'ID不能为空'),
        ),
    )
];


<?php

use \think\facade\Route as Route;

Route::rule('pay_success_return/:pay_method', 'recharge/pay_callback/success_return');
Route::rule('pay_success_notify/:pay_method', 'recharge/pay_callback/success_notify');

/*return [
    '__domain__' => [
        'manage.libx.com.cn' => 'admin',
    ],

];*/
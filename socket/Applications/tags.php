<?php

return [
    //创建直播间后
    'create_after' => [

    ],

    // 赠送礼物后
    'send_gift_after' => [

    ],

    // 更新pk收益后
    'update_pk_income_after' => [

    ],

    // 送背包礼物后
    'send_props_after' => [

    ],

    //完成pk后
    'complete_pk_after' => [

    ],

    //用户点亮后
    'light_after' => [

    ],

    //用户进直播间后
    'enter_room_after' => [
        'app\\service\\moniter\\LinkMicAudience',
    ],

    //用户发言后
    'send_message_after' => [
        'app\\service\\moniter\\HistoryMessage'
    ],

    //主播关播后
    'close_after' => [
        'app\\service\\moniter\\LinkMic'
    ],

    //用户退出直播后
    'exit_room_after' => [
        'app\\service\\moniter\\LinkMic'
    ],

    //用户关注主播后
    'follow_after' => [

    ],

];
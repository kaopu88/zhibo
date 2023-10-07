<?php
/**
 * Created by PhpStorm.
 * User: 李成
 * Date: 2020/5/13
 * Time: 15:17
 */
namespace app\common\service;

use bxkj_module\service\Message;

class Push extends Service
{
    public function sendMessage($userIds, $title, $desc="", $text="", $isNotice=0)
    {
        if (!is_array($userIds)) {
            $userIds = trim($userIds. ",");
            if(strpos(",", $userIds) !== false){
                $userIdsArr = explode(",", $userIds);
            }else{
                $userIdsArr[] = $userIds;
            }
        }
        $message = new Message();
        if (empty($userIdsArr) || empty($title)) return json_error('推送参数不正确');
        $message->setSender('', 'helper')->setReceiver($userIdsArr, "all")->sendPush([
            'title' => $title,
            'text' => $text,
            'url' => '',
            'summary' => $desc,
            'directly' => $isNotice,
        ]);
        return json_success([], '推送成功');

    }


}
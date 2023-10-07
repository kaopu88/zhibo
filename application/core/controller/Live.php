<?php

namespace app\core\controller;


use app\core\service\Redpacket;
use app\core\service\Socket;
use think\Request;
use app\core\service\Live as LiveService;

class Live extends Controller
{
    //超管关播
    public function superCloseRoom(Request $request)
    {
        $params = $request->post();

        if (empty($params['room_id'])) return json_error('房间id不能为空');

        $room_id = $params['room_id'];

        $msg = isset($params['msg']) && !empty($params['msg']) ? $params['msg'] : '该主播涉嫌违规，已被强制关播~';

        $liveService = new LiveService();

        $rs = $liveService->superDestroyRoom($room_id, $msg);


        //TODO 这个地方是主播关播没有领取完的红包退到发红包账户上
        // $redPacketService= new Redpacket();

        // $result=$redPacketService->backRedPacket($room_id);

        // if ($rs !== true && $result!==true) return json_error($liveService->getError());

        if ($rs !== true) return json_error($liveService->getError());

        return json_success(true);
    }


    //超管禁播
    public function superStop(Request $request)
    {
        $user_id = $request->post('user_id');

        $liveService = new LiveService();

        $rs = $liveService->bannedLive($user_id);

        if ($rs !== true) return json_error($liveService->getError());

        return json_success(true);
    }


    //正常关播
    public function closeRoom(Request $request)
    {
        $room_id = $request->post('room_id');

        $liveService = new LiveService();

        $rs = $liveService->destroyRoom($room_id);

        //TODO 这个地方是主播关播没有领取完的红包退到发红包账户上
       // $redPacketService= new Redpacket();

        //$result=$redPacketService->backRedPacket($room_id);

       // if ($rs !== true && $result!==true) return json_error($liveService->getError());
        if ($rs !== true ) return json_error($liveService->getError());

        return json_success(true);
    }

    public function backgroundCreate(Request $request)
    {
        $room_id = $request->post('room_id');
        $liveService = new LiveService();
        $rs = $liveService->backgroundCreate($room_id);

        if ($rs !== true) return json_error($liveService->getError());
        return json_success(true);
    }
}
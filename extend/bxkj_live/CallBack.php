<?php

namespace bxkj_live;


use bxkj_common\CoreSdk;
use think\Db;

abstract class CallBack
{
    protected $coreSdk;
    protected $live_config = [];

    public function __construct()
    {
        if (!$this->coreSdk instanceof CoreSdk) $this->coreSdk = new CoreSdk();

        if (empty($this->live_config)) $this->live_config = get_live_config();
    }


    abstract public function disconnect(array $params);


    abstract public function connect(array $params);


    //关闭直播间
    protected function callbackCloseRoom($stream)
    {
        $room = Db::name('live')->where(['stream'=>$stream])->field('id')->find();//未获取到房间说明以正常关播或确认推流环节出错

        if (empty($room)) return true;

        $rs = $this->coreSdk->post('live/superCloseRoom', ['room_id'=>$room['id'], 'msg'=>'直播网络不稳, 直播中断']);

        if ($rs === false) return make_error($this->coreSdk->getError()->error, 'wrong', ['stream'=>$stream]);

        return true;
    }


}
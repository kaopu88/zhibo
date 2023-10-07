<?php


namespace app\api\service\video;


use app\api\service\Video;
use bxkj_module\service\GiftLog;
use think\Db;

class GiveGift extends Video
{
    /**
     * 视频页送礼物
     * @param $gift_id
     * @param $num_id
     * @param $send_to_uid
     * @param string $from
     * @return \App\Common\BuguCommon\BaseError|array|\bxkj_common\BaseError
     */
    public function sendGift($gift_id, $num_id, $send_to_uid, $from='gift')
    {
        $type = 'bean';

        $gift_num_info = array_column(self::$gift_num, 'num', 'id');

        $num = $gift_num_info[$num_id];

        if ($from == 'pack') $type = 'user_package';

        $gift = new GiftLog();

        $pay = $gift->give([
            'gift_id' => $gift_id,
            'num' => $num,
            'user_id' => USERID,
            'to_uid' => $send_to_uid,
            'consume_order' => $type,
            'pay_scene' => 'video',
        ]);

        if (empty($pay)) return $this->setError($gift->getError());

        $bean = Db::name('bean')->where(['user_id'=>USERID])->field('bean')->find();

        return $bean['bean'];
    }
}
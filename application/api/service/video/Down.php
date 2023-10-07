<?php


namespace app\api\service\video;


use app\api\service\Video;
use app\common\service\User;
use think\Db;
use think\facade\Log;

class Down extends Video
{
    public function isDown($user_id, $filmId = null)
    {
        $userModel = new User();
        $downloadSwitch = $userModel->getUserSetting($user_id, 'download_switch');
        return $downloadSwitch == '1';
    }

    //写入观看记录
    public function insertDown($user_id, $id)
    {
        $isDown = Db::name('video_down_history')->where(['item_id' => $id, 'user_id' => $user_id])->value('id');

        if (empty($isDown)) {

            $data = [
                'item_id' => $id,
                'user_id' => $user_id,
                'create_time' => time(),
            ];
            $res = Db::name('video_down_history')->insert($data);

            Db::name('video')->where('id', $id)->setInc('down_sum');

            $userModel = new User();
            //用户的redis数据更新
            $userModel->updateData($user_id, ['download_num' => '+1']);
        }
        else {
            $data = [
                'create_time' => time(),
            ];

            $res = Db::name('video_down_history')->where(['id'=>$isDown])->update($data);
        }

        return $res !== false;
    }
}
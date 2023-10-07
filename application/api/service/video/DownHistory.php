<?php

namespace app\api\service\video;

use app\api\service\Video;
use app\common\service\User;
use think\Db;

class DownHistory extends Video{
    protected function getTableName()
    {
        return 'video_down_history';
    }

    //写入下载记录
    public function insertDown($user_id, $id)
    {
        $isDown = Db::name($this->getTableName())->field('id')->where(['item_id' => $id, 'user_id' => $user_id])->find();

        if (empty($isDown)) {
            $res = Db::name($this->getTableName())->insert([
                'item_id' => $id,
                'user_id' => $user_id,
                'create_time' => time(),
            ]);

            Db::name('video')->where('id', $id)->setInc('down_sum');

            $userModel = new User();

            //用户的redis数据更新
            $userModel->updateData($user_id, ['download_num' => 1]);
        }
        else {
            $res = Db::name($this->getTableName())->where($isDown)->update([
                'create_time' => time(),
            ]);
        }

        return $res !== false;
    }
}

<?php


namespace app\api\service\live;


use app\api\service\LiveBase2;
use think\Db;

class Channel extends LiveBase2
{
    //获取直播列表频道
    public function getLiveChannel($parent_id = 0)
    {
        $this->where['parent_id'] = $parent_id;

        $key = $parent_id == 0 ? 'parent' : 'childe' . $parent_id;

        $res = $this->redis->get(self::$liveChannel . $key);

        if (empty($res)) {

            $res = Db::name('live_channel')->where($this->where)->field('id, sub_channel, icon, name, description')->order('sort_order')->select();

            if (!empty($res)) {
                $res = json_encode($res);

                $this->redis->set(self::$liveChannel . $key, $res);

                $this->redis->expire(self::$liveChannel . $key, 86400);
            }
        }

        return json_decode($res ?: '{}', true);
    }
}
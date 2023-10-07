<?php

namespace app\api\service\location;

use app\api\service\Location;
use app\api\service\Region;
use think\Db;

/**
 * 收藏地点
 * Class Favorite
 * @package App\Domain\location
 */
class Favorite extends Location
{
    /**
     * 是否存在于收藏夹
     * @param $type
     * @param $itemId
     * @return int
     */
    public function isFavorite($location_id)
    {

        if (empty(USERID)) return 0;

        $key = 'collection:'.USERID.':location';

        if (!$this->redis->exists($key))
        {
            //重建收藏缓存
            $LocationModel = new Location();

            $favorites = $LocationModel->restoreFavorite(USERID);

            if (empty($favorites))
            {
                $this->redis->zAdd($key, 0, $location_id);
                return 0;
            }

            foreach ($favorites as $val)
            {
                $this->redis->zAdd($key, $val['create_time'], $val['location_id']);
            }
        }

        $score = $this->redis->zScore($key, $location_id);

        return empty($score) ? 0 : 1;
    }

    /**
     * 收藏夹列表
     */
    public function listByFavorite($user_id = USERID, $offset = 0, $length = 10)
    {
        $LocationModel = new Location();

        $res = $LocationModel->locationListByFavorite($user_id, $offset, $length);

        if (empty($res)) return [];

        $Region = new Region();

        foreach ($res as &$value)
        {
            if (empty($value['cover'])) $value['cover'] = '';

            if (!empty($value['city_id']) || !empty($value['province_id']))
            {
                $cid = empty($value['city_id']) ? $value['province_id'] : $value['city_id'];

                $name = $Region->getNameByCityId($cid);

                $value['city_name'] = $name;
            }
            else{
                $value['city_name'] = '';
            }

            $value['comment_score'] = '暂无评分';

            unset($value['city_id'], $value['province_id']);
        }

        return $res;
    }


    /**
     * 添加进收藏夹
     * @desc 收藏自已喜欢的位置
     */
    public function add($location_id)
    {
        $key = 'collection:'.USERID.':location';

        $LocationModel = new Location();

        $location_info = Db::name('location')->where('id', $location_id)->find();

        if (empty($location_info['cover']))
        {

            $videoCover = Db::name('video')->field('cover_url')->order('play_sum desc')->where(['location_id' => $location_id])->find();

            $cover = $videoCover['cover_url'];
        }
        else{
            $cover = $location_info['cover'];
        }

        $res = $LocationModel->addByFavorite(USERID, $location_id, $cover);
        
        Db::name('location')->where('id', $location_id)->setInc('collect_num');

        if (!$res) return make_error('收藏失败');

        $this->redis->zAdd($key, time(), $location_id);

        return [
            'status' => 1,
            'msg' => '收藏成功',
        ];
    }


    /**
     * 从收藏夹移除
     * @return array
     */
    public function remove($location_id)
    {
        $key = 'collection:'.USERID.':location';

        $LocationModel = new Location();

        $LocationModel->removeByFavorite(USERID, $location_id);

        if (!is_array($location_id)) $location_id = [$location_id];

        foreach ($location_id as $id)
        {
            $this->redis->zrem($key, $id);
        }

        return [
            'status' => 0,
            'msg' => '移除成功',
        ];
    }
}
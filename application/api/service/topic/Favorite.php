<?php

namespace app\api\service\topic;

use think\Db;
use app\common\service\Service;
use app\api\service\Favorite as FavoriteInterface;
use app\api\service\Topic as TopicModel;
use bxkj_common\RedisClient;

/**
 * 话题收藏
 * Class Topic
 * @package App\Domain\favorite
 */
class Favorite extends Service implements FavoriteInterface
{


    /**
     * 收藏夹列表
     */
    public function listByFavorite($user_id = USERID, $offset = 0, $length = 10)
    {
        $TopicModel = new TopicModel();

        $res = $TopicModel->topicListByFavorite($user_id, $offset, $length);

        return $res;
    }


    /**
     * 添加进收藏夹
     * @desc 收藏自已喜欢的话题
     */
    public function add($topic_id)
    {
        $redis = RedisClient::getInstance();

        $key = 'collection:'.USERID.':topic';

        $TopicModel = new TopicModel();

        $topic = $TopicModel->exists(['id' => $topic_id]);

        if (empty($topic)) return $this->setError('未找到该话题');

        $res = $TopicModel->addByFavorite(USERID, $topic_id);

        if (!$res) return $this->setError('收藏失败');

        $redis->zAdd($key, time(), $topic_id);

        $TopicModel->vectorUpdate($topic_id, 'collection_sum');

        return [
            'status' => 1,
            'msg' => '收藏成功',
        ];
    }


    /**
     * 从收藏夹移除
     * @return array
     */
    public function remove($topic_id)
    {
        $redis = RedisClient::getInstance();

        $key = 'collection:'.USERID.':topic';

        $TopicModel = new TopicModel();

        $TopicModel->removeByFavorite(USERID, $topic_id);

        $TopicModel->vectorUpdate($topic_id, 'collection_sum', '-');
        if (!is_array($topic_id)) $topic_id = [$topic_id];

        foreach ($topic_id as $id)
        {
            $redis->zrem($key, $id);
        }

        return [
            'status' => 0,
            'msg' => '移除成功',
        ];
    }


    /**
     * 是否存在于收藏夹
     * @param $type
     * @param $itemId
     * @return int
     */
    public function isFavorite($topic_id)
    {
        $redis = RedisClient::getInstance();

        if (empty(USERID)) return 0;

        $key = 'collection:'.USERID.':topic';

        if (!$redis->exists($key))
        {
            //重建收藏缓存
            $TopicModel = new TopicModel();

            $favorites = $TopicModel->restoreFavorite(USERID);

            if (empty($favorites))
            {
                $redis->zAdd($key, 0, $topic_id);
                return 0;
            }

            foreach ($favorites as $val)
            {
                $redis->zAdd($key, $val['create_time'], $val['topic_id']);
            }
        }

        $score = $redis->zScore($key, $topic_id);

        return empty($score) ? 0 : 1;
    }


    /**
     * 删除收藏内容
     * @param array $items
     * @return array
     */
    public function delete(array $items)
    {
        return $this->remove($items);
    }


}
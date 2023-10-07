<?php


namespace app\api\controller;


use app\common\controller\UserController;
use app\api\service\Topic as TopicModel;
use app\api\service\Topic_Relation;
use bxkj_common\RedisClient;
use think\Db;

class Topic extends UserController
{

    /**
     * 获取话题列表
     * @return mixed
     */
    public function getTopic()
    {
        $params = request()->param();
        $offset = isset($params['offset']) ? $params['offset'] : 0;
        $length = isset($params['length']) ? ($params['length'] > 100 ? 100 : $params['length']) : PAGE_LIMIT;

        $TopicModel = new TopicModel();

        $res = $TopicModel->getAll($offset, $length);

        return $this->success($res);
    }


    /**
     * 话题下的相关视频
     * @return \App\Common\BuguCommon\BaseError|\bxkj_common\BaseError
     */
    public function topicByVideo()
    {
        $topic_id = request()->param('topic_id');

        $topics = Db::name('topic')->where(['id'=>$topic_id])->field('title, descr, icon')->find();

        if (empty($topics)) return $this->jsonError('未查找到些话题');

        $topics['play_total'] = 0;

        $topics['video'] = [];

        $redis = RedisClient::getInstance();

        $zcore = empty(USERID) ? false : $redis->zScore('collection:'.USERID.':topic', $topic_id);

        $topics['is_collection'] = $zcore ? 1 : 0;

        $Topic_Relation = new Topic_Relation();

        $rs = $Topic_Relation->getVideoByTopic($topic_id);

        if (!empty($rs))
        {
            $videos = Db::name('topic_relation')->where(['topic_id' => $topic_id])->field('video_id')->select();

            $videos_ids = array_column($videos, 'video_id');

            $topics['play_total'] = Db::name('video')->where(['id'=>$videos_ids])->sum('play_sum');

            $VideoDomain = new \app\api\service\Video();

            $topics['video'] = $VideoDomain->initializeFilm($rs, \app\common\service\Video::$allow_fields['topic']);
        }

        return $this->success($topics);
    }


    /**
     * 话题下的相关视频分页
     * @return array
     */
    public function topicByVideoPages()
    {
        $params = request()->param();
        $offset = isset($params['offset']) ? $params['offset'] : 0;
        $topic_id = $params['topic_id'];

        $Topic_Relation = new Topic_Relation();

        $rs = $Topic_Relation->getVideoByTopic($topic_id, $offset);

        $Video = new \app\api\service\Video();

        $rs = $Video->initializeFilm($rs, \app\common\service\Video::$allow_fields['topic']);

        return $this->success($rs);
    }
}
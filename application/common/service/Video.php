<?php

namespace app\common\service;

use app\api\service\LiveBase2;
use app\api\service\Music as MusicModel;
use app\api\service\Topic;
use app\api\service\video\Activity;
use app\api\service\video\Favorite as FavoriteModel;
use app\api\service\video\Like;
use app\api\service\video\Reward;
use think\Db;
use bxkj_common\OpenSearch;
use OpenSearch\Util\SearchParamsBuilder;

class Video extends Service
{
    protected static $copy_right = 1, $admireTag = 'ADMIRE:', $payTag = 'PAY:', $playTag = 'PLAY:';

    protected $tableName, $prefix, $where, $order, $limit, $sql;

    public static $allow_fields = [
        'common' => ['id', 'video_id', 'describe', 'user_id', 'video_url', 'cover_url', 'zan_sum', 'comment_sum', 'play_sum', 'collection_sum', 'share_sum', 'duration', 'file_size', 'avatar', 'nickname', 'status', 'animate_url', 'is_ad', 'ad_url', 'goods_id', 'short_title', 'goods_type', 'cate_id','is_pay','price'],
        'user_videos' => ['id', 'video_id', 'animate_url', 'describe', 'user_id', 'video_url', 'cover_url', 'zan_sum', 'comment_sum', 'play_sum', 'collection_sum', 'share_sum', 'duration', 'file_size', 'avatar', 'nickname', 'is_ad', 'ad_url', 'goods_id', 'short_title', 'goods_type', 'cate_id'],
        'topic' => ['id', 'animate_url', 'video_id', 'describe', 'user_id', 'video_url', 'cover_url', 'zan_sum', 'comment_sum', 'play_sum', 'collection_sum', 'share_sum', 'duration', 'file_size', 'avatar', 'nickname', 'status', 'is_ad', 'ad_url', 'goods_id', 'short_title', 'goods_type', 'cate_id'],
        'user_dynamic' => ['id', 'animate_url', 'video_id', 'describe', 'user_id', 'video_url', 'cover_url', 'zan_sum', 'comment_sum', 'play_sum', 'collection_sum', 'share_sum', 'duration', 'file_size', 'avatar', 'nickname', 'status', 'is_ad', 'ad_url', 'goods_id', 'short_title', 'goods_type', 'cate_id'],
        'detail' => ['id', 'animate_url', 'avatar', 'nickname', 'video_id', 'describe', 'user_id', 'video_url', 'zan_sum', 'comment_sum', 'play_sum', 'collection_sum', 'share_sum', 'duration', 'file_size', 'is_ad', 'ad_url', 'goods_id', 'short_title', 'goods_type', 'cate_id'],
        'own_publish'=>['id', 'video_id', 'animate_url', 'describe', 'user_id', 'video_url', 'cover_url', 'zan_sum', 'comment_sum', 'play_sum', 'collection_sum', 'share_sum', 'duration', 'file_size', 'avatar', 'nickname', 'audit_status','visible', 'status', 'is_ad', 'ad_url', 'goods_id', 'short_title', 'goods_type', 'cate_id'],
        'new_publish'=>['avatar', 'nickname', 'id', 'animate_url', 'video_url', 'comment_sum', 'share_sum', 'status', 'describe', 'user_id', 'play_sum', 'zan_sum', 'duration', 'cover_url', 'file_size', 'is_ad', 'ad_url', 'goods_id', 'short_title', 'goods_type', 'cate_id']
    ];

    protected static $film_default_field = ['topic_group', 'friend_group', 'width', 'height', 'is_zan', 'is_collect', 'is_live', 'is_follow', 'is_self', 'location', 'publish_time', 'room_id', 'room_model', 'video_act', 'music', 'treasure_chest', 'long_video_limit', 'treasure_chest', 'reward_rank', 'jump', 'room_info'];

    public function initializeFilm(array $data, array $allow_field = [], $USERID = null, $room_exists=false)
    {
        $USERID = $USERID !== null ? $USERID : (defined('USERID') ? USERID : '');
        if (!empty($allow_field)) {
            $allow_field = array_merge($allow_field, self::$film_default_field);
            $allow_field = array_flip($allow_field);
        }
        return $this->parseVideo($data, $allow_field, $USERID, $room_exists);
    }

    //解析视频信息
    protected function parseVideo(array $data, array $allow_field = [], $USERID = null, $room_exists=false)
    {
        if (empty($data)) return [];

        $VideoFavorite = new FavoriteModel();

        $Follow = new Follow();

        $userModel = new User();

        $Live = new LiveBase2();

        $Like = new Like();

        $Music = new MusicModel();

        $TopicModel = new Topic();

        $Activity = new Activity();

        $Reward = new Reward();

        foreach ($data as $key => &$val)
        {
            if (empty($val) || !is_array($val))
            {
                unset($data[$key]);
                continue;
            }

            $val['topic_group'] = $val['friend_group'] = [];

            $val['is_live'] = $val['is_zan'] = $val['is_collect'] = $val['is_follow'] = $val['is_self'] = $val['treasure_chest'] = 0;

            $val['room_id'] = $val['room_model'] = '0';

            $val['music'] = $val['reward_rank'] = (object)[];

            //当前用户是否点赞
            !empty($USERID) && $val['is_zan'] = $Like->isLike($val['id']);

            //当前用户是否收藏
            !empty($USERID) && $val['is_collect'] = $VideoFavorite->isFavorite($val['id']);

            //当前用户是否关注了发布者
            !empty($USERID) && $val['is_follow'] = (int)$Follow->isFollow($val['user_id']);

            //是为本人视频
            !empty($USERID) && $val['is_self'] = (int)($val['user_id'] == $USERID);

            //视频大小格式化
            !empty($val['file_size']) && $val['file_size'] = format_bytes($val['file_size']);

            //视频封面格式化
            !empty($val['cover_url']) && $val['cover_url'] = img_url($val['cover_url'], 'film');

            //前端容错处理
           // !empty($val['animate_url']) || $val['animate_url'] = '';
            !empty($val['animate_url']) || $val['animate_url'] = img_url($val['cover_url'], 'film');
            //格式化时长
            $val['duration'] = duration_format($val['duration']);

            //格式化发布时间
            $val['publish_time'] = isset($val['create_time']) && !empty($val['create_time']) ? time_before($val['create_time'], '前') : '';

            //长视频限位值
            $val['long_video_limit'] = 30;

            isset($val['play_sum2']) && $val['play_sum'] += $val['play_sum2'];

            isset($val['zan_sum2']) && $val['zan_sum'] += $val['zan_sum2'];

            if (!empty($val['comment_sum']))
            {
                $sensitive_comment_num = $this->getSensitiveCommentNum($val['id']);

                $val['comment_sum'] = $val['comment_sum'] >= $sensitive_comment_num ? $val['comment_sum'] - $sensitive_comment_num : 0;
            }

            $is_live = (int)$this->redis->sismember('BG_LIVE:Living', $val['user_id']);
            //是否直播中
            if ($is_live)
            {
                //是否在直播
                $room = $Live->getRoomByUserId($val['user_id']);

                if (!empty($room))
                {
                    $val['is_live'] = 1;

                    $val['room_id'] = $room['room_id'];

                    $val['room_model'] = $room['room_model'];

                    $val['jump'] = getJump('enter_room', ['room_id' => $room['room_id'], 'from' => 'video']);

                    $val['room_info'] = $room_exists ? $room : (object)[];
                }
            }
            else{
                $val['jump'] = getJump('personal', ['user_id' => $val['user_id']]);
            }

            //位置信息
            $val['location']['name'] = empty($val['location_id']) || empty($val['location_name']) ? '' : $val['location_name'];
            $val['location']['url'] = '';
            $val['location']['location_id'] = empty($val['location_id']) || empty($val['location_name']) ? '' : $val['location_id'];
            $val['location']['level'] = empty($val['location_id']) || empty($val['location_name']) ? '' : $val['region_level'];

            //音乐信息
            if ($val['music_id'])
            {
                $music = Db::name('music')->where('id', $val['music_id'])->find();

                $Music->formatImage($music, 'turntable');

                $val['music'] = [
                    'music_id' => $val['music_id'],
                    'title' => $music['title'],
                    'image' => $music['image'],
                    'singer' => $music['singer'],
                    'link' => ''
                ];
            }

            //话题信息
            if (!empty($val['topic']))
            {
                $topics = explode(',', $val['topic']);

                $val['topic_group'] = $TopicModel->findAll('id in (' . implode(',', array_unique($topics)) . ')', 'id topic_id, title, descr, participate_num');
            }

            //@好友信息
            if (!empty($val['friends'])) $val['friend_group'] = json_decode($val['friends'], true);

            //发布者用户信息
            if (!isset($val['nickname']) || !isset($val['avatar']))
            {
                $users = $userModel->getUser($val['user_id']);

                $val['nickname'] = $users['nickname'];

                $val['avatar'] = $users['avatar'];
            }

            //视频活动信息
            $val['video_act'] = $Activity->getActivity($val);

            //视频打赏前三名
            $val['reward_rank'] = $Reward->rank($val['id'], 0, 3);

            $this->formatData($val, ['zan_sum', 'comment_sum', 'play_sum', 'collection_sum', 'share_sum']);

            !empty($allow_field) && $val = array_intersect_key($val, $allow_field);

            unset($val['friends'], $val['location_id'], $val['music_id']);

            $this->fieldsToString($val, 'id,user_id,down_sum,zan_sum,comment_sum,share_sum,width,height,copy_right,region_level,visible,play_sum');
        }

        $ios_audit_status = 1;//$this->redis->get('config:ios:review');

        if (APP_OS_NAME == 'ios' && $ios_audit_status == 1) return $data;

        $is_open = $Reward->isOpenReward();

        $index = array_rand($data);

        $data[$index]['treasure_chest'] = $is_open;

        return $data;
    }

    private function fieldsToString(&$val, $fields)
    {
        $fields = str_to_fields($fields);
        foreach ($fields as $field) {
            if (isset($val[$field])) $val[$field] = (string)$val[$field];
        }
    }

    //当前视频下的总敏感评论数 sensitive_comment_sum
    //当前视频下当前用户发布的所有敏感评论数 user_sensitive_comment
    //当前视频可以展示给用户看到的所有评论数 comment_sum-sensitive_comment_sum+user_sensitive_comment
    private function getSensitiveCommentNum($video_id)
    {
        $sensitive_comment_num = Db::name('video_comment')
            ->where(['video_id' => $video_id, 'is_sensitive' => 1])
            ->where('user_id', '<>', USERID)
            ->count();
        return $sensitive_comment_num;
    }

    public function __get($name)
    {
        if (property_exists($this, $name)) return $this->$name;

        return null;
    }

    public function setWhere(array $where = [])
    {
        $this->prefix = config('database.prefix');
        $this->tableName = 'video';
        $str = '';
        $ios_audit_status = config('app.ios_debug');
        APP_OS_NAME == 'ios' && $ios_audit_status == 1 && $where['copy_right'] = self::$copy_right; //当前正在审核并且是苹果的系统

        foreach ($where as $key => $value) {
            $str .= $this->prefix . $this->tableName . '.' . $key . '=' . $value;
            $str .= ' AND ';
        }

        $this->where = rtrim($str, ' AND');

        return $this;
    }

    public function setOrder($orderField)
    {
        $this->order = sprintf(' ORDER BY %s%s.%s', $this->prefix, $this->tableName, $orderField);

        return $this;
    }

    public function setLimit($offset, $length = 10)
    {
        if (strpos($offset, ',')) list($offset, $length) = explode(',', $offset);

        $this->limit = sprintf(' limit %s, %s', $offset, $length);

        return $this;
    }

    public function setSql()
    {
        $table = $this->prefix . $this->tableName;

        $onTable = $this->prefix . 'user';

        $sql = sprintf('SELECT %s.*, %s.avatar, %s.nickname FROM %s INNER JOIN %s ON %s.user_id=%s.user_id', $table, $onTable, $onTable, $table, $onTable, $table, $onTable);

        !empty($this->where) && $sql .= ' AND ' . $this->where;

        !empty($this->order) && $sql .= $this->order;

        !empty($this->limit) && $sql .= $this->limit;

        return $sql;
    }

    public function getFilmDetailOne($where)
    {
        $prefix = config('database.prefix');

        $sql = 'select * from {$prefix}video WHERE ' . $where . ' limit 1';

        $res = Db::query($sql);

        return $res[0];
    }

    //获取搜索结果
    public function getSearchResult($get, $offset = 0, $length = 10)
    {
        $searchClient = OpenSearch::createSearchClient();
        $params = OpenSearch::createSearchParamsBuilder('app_film');
        //索引模式
        if ($get['mode'] == 'index' || $get['mode'] == 'count') {
            $params->setFetchFields(['film_id']);
        }
        //limit
        if ($get['mode'] == 'count') {
            $params->setStart(0);
            $params->setHits(1);
        } else {
            $params->setStart($offset);
            $params->setHits($length);
            $this->setSearchSort($params, $get);
        }
        $keyword = addcslashes($get['keyword'], '\'');
        $params->setQuery("(title:'{$keyword}' OR nickname:'{$keyword}' OR user_id:'{$keyword}') RANK title:'{$keyword}'");
        if ($get['user_id'] != '') {
            $params->addFilter("user_id={$get['user_id']}");
        }
        $ret = $searchClient->execute($params->build());
        $newList = [];
        if ($ret->status == 'FAIL') return ['total' => 0, 'list' => $newList];
        if ($get['mode'] != 'count') {
            $filmIds = $ret->getIds('film_id');
            if ($filmIds) {
                $prefix = config('database.prefix');
                $sql = "select video.id,video.describe,video.user_id,video.video_id,video.video_url,video.animate_url,video.cover_url,video.zan_sum,video.comment_sum,video.play_sum,video.collection_sum,video.share_sum,video.duration,video.file_size,user.avatar,video.friends,video.topic,video.tags,video.location_lng,video.location_lat,video.city_id,video.city_name,video.location_name,video.source,video.duration,video.copy_right,video.create_time,user.nickname,user.sign,user.level,user.is_creation,video.location_id,video.music_id,video.region_level,video.visible from {$prefix}video video join {$prefix}user `user` on user.user_id=video.user_id where video.id in (?) limit " . count($filmIds);
                $filmList = Db::query($sql, [implode(',', $filmIds)]);
                $videoService = new Video();
                $list = $videoService->initializeFilm($filmList, self::$allow_fields['common'], $get['self_uid']);
                $newList = $ret->sortList($filmIds, 'id', $list);
            }
        }
        return ['total' => $ret->getTotal(), 'list' => $newList];
    }

    protected function setSearchSort(SearchParamsBuilder &$params, $get)
    {
    }

    public function followNewPublishTime($ids)
    {
        if (empty($ids)) return [];

        is_array($ids) && $ids = implode(',', $ids);

        $prefix = config('database.prefix');

        $sql = "SELECT user_id, create_time FROM {$prefix}video WHERE user_id in ({$ids}) GROUP BY user_id ORDER BY user_id desc";

        $res = Db::query($sql);

        if (empty($res)) return [];

        return array_column($res, 'create_time', 'user_id');
    }

    //获取搜索结果
    public function getSearchResultnew($get, $offset = 0, $length = 10)
    {

        $db = Db::name('video');
        $keyword = addcslashes($get['keyword'], '\'');
        $result = $db->field("id")->where([["user_id|describe|tags",'like',"%{$keyword }%"]])
        ->limit($offset, $length)->order('create_time desc')->select();

        $newList = [];
        if (empty($result)) return ['total' => 0, 'list' => $newList];

        if ($get['mode'] != 'count') {
            foreach ($result as $k=>$v){
                $filmIds[] = $v['id'];
            }

            $rs =  implode(',', $filmIds);
            if ($filmIds) {
                $prefix = config('database.prefix');
                $sql = "select video.id,video.describe,video.user_id,video.video_id,video.video_url,video.animate_url,video.cover_url,video.zan_sum,video.comment_sum,video.play_sum,video.collection_sum,video.share_sum,video.duration,video.file_size,user.avatar,video.friends,video.topic,video.tags,video.location_lng,video.location_lat,video.city_id,video.city_name,video.location_name,video.source,video.duration,video.copy_right,video.create_time,user.nickname,user.sign,user.level,user.is_creation,video.location_id,video.music_id,video.region_level,video.visible from {$prefix}video video join {$prefix}user `user` on user.user_id=video.user_id where video.id in ({$rs}) limit " . count($filmIds);
                $filmList = Db::query($sql, []);
                $videoService = new Video();
                $newList = $videoService->initializeFilm($filmList, self::$allow_fields['common'], $get['self_uid']);

            }
        }
        return ['total' => count($result), 'list' => $newList];
    }
}
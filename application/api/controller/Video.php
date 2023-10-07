<?php

namespace app\api\controller;

use app\admin\service\VideoTeenager;
use app\common\controller\UserController;
use app\api\service\Region;
use app\api\service\Reward;
use app\api\service\topic\TopicRelation;
use app\api\service\video\Down;
use app\friend\service\FriendCircleMessage;
use bxkj_common\Prophet;
use bxkj_module\service\Task;
use bxkj_module\service\User;
use bxkj_common\RabbitMqChannel;
use think\Db;
use app\api\service\video\Location;
use app\api\service\video\PlayHistory;
use app\api\service\Video as VideoService;
use app\api\service\location\ParseLocation;
use app\api\service\Like;

/**
 * 视频类(新)
 * v1版本
 * Class Films
 * @package App\Api
 */
class Video extends UserController
{
    /**
     * 电影发布
     * @desc APP端完成上传后请求的发布接口
     * @return string msg
     */
    public function publish()
    {
        $params = request()->param();
        if (!empty($params['describe'])) {
            if (mb_strlen($params['describe']) > 55) {
                return $this->jsonError('视频描述不能超过55个字');
            }
        }
        $userService = new User();
        $user_info   = $userService->getUser(USERID);
        $goods_id    = isset($params['goods_id']) ? $params['goods_id'] : 0;
        $goods_type  = isset($params['goods_type']) ? $params['goods_type'] : 0;
        $short_title = '';
        if (!empty($goods_id)) {
            $goods = Db::name('anchor_goods')->where(['user_id' => USERID, 'goods_id' => $goods_id])->find();
            if (empty($goods)) return $this->jsonError('你选择的商品不存在');
            if ($goods['status'] != 1) return $this->jsonError('该商品已下架');
            if (empty($goods_type)) {
                $goodsRes                = Db::name('goods')->field('img, short_title, cate_id')->where(['id' => $goods_id])->find();
                $goodsRes['short_title'] = $goodsRes['short_title'] ? $goodsRes['short_title'] : '';
                $short_title             = $goods['goods_title'] ? $goods['goods_title'] : $goodsRes['short_title'];
            } elseif ($goods_type == 1) {
                $goodsRes                = Db::name('shop_goods_sku')->field(' goods_name as title,sku_image as img, goods_state as status, category_id as cate_id')->where(['sku_id' => $goods_id, 'goods_state' => 1])->find();
                $goodsRes['short_title'] = $goodsRes['title'] ? $goodsRes['title'] : '';
                $short_title             = $goods['goods_title'] ? $goods['goods_title'] : $goodsRes['short_title'];
            }
        }
        if (!$user_info['film_status']) return $this->jsonError('视频上传功能受限');
        if (empty($params['video_id']) || empty($params['video_url'])) return $this->jsonError('上传失败');
        $now = time();
        if (!empty($params['location_name']) && $end = strpos($params['location_name'], '(')) $params['location_name'] = substr($params['location_name'], 0, $end);
        $data = [
            'create_time'    => $now,
            'app_time'       => $now,
            'user_id'        => USERID,
            'describe'       => $params['describe'] ? $params['describe'] : '',
            'video_id'       => $params['video_id'],
            'video_url'      => $params['video_url'],
            'cover_url'      => $params['cover_url'] ? $params['cover_url'] : '',
            'audit_status'   => '0',
            'location_lng'   => $params['lng'] ? $params['lng'] : 0,
            'location_lat'   => $params['lat'] ? $params['lat'] : 0,
            'topic'          => $params['topic'],
            'friends'        => $params['friends'],
            'location_name'  => !empty($params['location_name']) ? $params['location_name'] : '',
            'poi_id'         => $params['poi_id'] ? $params['poi_id'] : '',
            'visible'        => (int)$params['visible'],
            'music_id'       => (int)$params['music_id'],
            'region_level'   => !empty($params['region_level']) ? $params['region_level'] : 4,
            'source'         => 'user',
            'process_status' => '3',
            'goods_id'       => isset($goods['id']) ? $goods['id'] : 0,
            'cate_id'        => isset($goodsRes['cate_id']) ? $goodsRes['cate_id'] : 0,
            'goods_type'     => $goods_type,
            'short_title'    => $short_title,
            'basic_info'     => '',
            'ad_url'         => '',
            'is_pay'         =>$params['is_pay']?:0,
            'price'          =>$params['price']?:0
        ];
        if (!empty($data['friends'])) {
            $friends         = explode(',', $data['friends']);
            $friend_info     = $userService->getUsers(array_unique($friends), null, 'user_id, nickname');
            $data['friends'] = json_encode($friend_info);
        }
        $videoID = Db::name('video_unpublished')->insertGetId($data);
        if (!$videoID) return $this->jsonError('发布错误');
        if (!empty($data['topic'])) {
            $topics        = explode(',', $data['topic']);
            $TopicRelation = new TopicRelation();
            $TopicRelation->inserts(array_unique($topics), $videoID);
        }
        //发完视频自动发一条相关的视频动态
        $is_dynamic_open = config('friend.is_open') ? config('friend.is_open') : 0;
        if ($params['is_synchro'] == 1 && !empty($is_dynamic_open)) {
            //获取小视频的相关信息
            $systemplus = json_encode(['videoID' => $videoID]);
            $config = config('app.vod.audit_config');
            if ($config['status'] == 1) {
                $status_examine = 0;
            } else {
                $status_examine = 1;
            };
            if (!empty($params['describe'])) {
                $countmsg = $params['describe'];
            } else {
                $countmsg = '我上传了一段视频请大家围观';
            }
            $privateid = $params['friends'] ? trim($params['friends']) : '';
            $title     = $params['topic'] ? trim($params['topic']) : '';
            $rest      = systemSend(USERID, $countmsg, '', $data['video_url'], ''
                , '', 2, 1, $title, 2,
                $privateid, 0, $systemplus, '',
                '', 20, $data['cover_url'], $data['describe'], $data['location_name'], $status_examine, '', '');
        }
        $rabbitChannel = new RabbitMqChannel(['video.create_before']);
        $rabbitChannel->exchange('main')->sendOnce('video.create.upload', ['id' => $videoID]);
        return $this->success(['id' => $videoID], '发布成功');
    }

    public function testVideo()
    {
        $rabbitChannel = new RabbitMqChannel(['video.create_before']);
        $rabbitChannel->exchange('main')->sendOnce('video.create.upload', ['id' => 2576]);
    }

    /**
     * 具体位置附近的视频集
     * @return array
     */
    public function videosByLocation()
    {
        $params        = request()->param();
        $location_id   = $params['location_id'];
        $offset        = isset($params['offset']) ? $params['offset'] : 0;
        $length        = isset($params['length']) ? ($params['length'] > 100 ? 100 : $params['length']) : PAGE_LIMIT;
        $LocationModel = new Location();
        $VideoService  = new VideoService();
        $video_list    = $LocationModel->videosByLocation($location_id, $offset, $length);
        $videos        = $VideoService->initializeFilm($video_list);
        return $this->success($videos);
    }

    /**
     * 位置详情
     * @return array
     */
    public function locationDetail()
    {
        $params        = request()->param();
        $location_id   = $params['location_id'];
        $lng           = $params['lng'];
        $lat           = $params['lat'];
        $ParseLocation = new ParseLocation();
        $res           = $ParseLocation->getLocation($location_id, $lng, $lat);
        if (!$res) return $this->jsonError($ParseLocation->getError());
        return $this->success($res);
    }

    /**
     * 视频点赞
     * @desc 用户对影片点赞,参数id为影片的ID
     * @return int
     */
    public function support()
    {
        $params = request()->param();
        $msg    = ['点赞成功', '取消点赞'];
        $id     = $params['id'];
        $status = $params['status'];
        $Like   = new Like();
        switch ($status) {
            case 1:
                $res = $Like->unLike($id);
                break;
            default:
                $res = $Like->like($id);
                //这里添加今日点赞任务奖励
                $taskMod = new Task();
                $data    = [
                    'user_id'    => USERID,
                    'task_type'  => 'thumbsVideo',
                    'task_value' => 1
                ];
                $taskMod->subTask($data);
                break;
        }
        if (!$res) return $this->jsonError($Like->getError());
        return $this->success($res, $msg[$status]);
    }

    /**
     * 影片详情
     * @desc 影片详细信息
     */
    public function detail()
    {
        $params = request()->param();
        $id     = $params['id'];
        $video  = Db::name('video')->where(['id' => $id])->find();
        if (empty($video)) return $this->jsonError('视频已删除或已下架');
        $user              = new user();
        $users             = $user->getUser($video['user_id']);
        $video['avatar']   = $users['avatar'];
        $video['nickname'] = $users['nickname'];
        $VideoService      = new VideoService();
        $res               = $VideoService->initializeFilm([$video], VideoService::$allow_fields['detail']);
        return $this->success($res[0]);
    }

    /**
     * 获取历史浏览记录
     * @return mixed
     */
    public function historyBrowseRecords()
    {
        $params      = request()->param();
        $offset      = isset($params['offset']) ? $params['offset'] : 0;
        $length      = isset($params['length']) ? ($params['length'] > 100 ? 100 : $params['length']) : PAGE_LIMIT;
        $PlayHistory = new PlayHistory();
        $res         = $PlayHistory->historyBrowseRecords($params['id'], $offset, $length);
        $regionModel = new Region();
        foreach ($res['list'] as &$value) {
            $value['city']        = $regionModel->getNameByCityId($value['city']);
            $value['create_time'] = time_before($value['create_time'], '前');
        }
        return $this->success($res);
    }

    /**
     * 个人发布的影片
     * @desc 获取个人发布的影片(个人中心)
     */
    public function ownPublishFilm()
    {
        $params         = request()->param();
        $offset         = isset($params['offset']) ? $params['offset'] : 0;
        $length         = isset($params['length']) ? ($params['length'] > 100 ? 100 : $params['length']) : PAGE_LIMIT;
        $statusDescribe = ['处理中', '待审核', '已通过', '审核驳回'];
        $user_films     = Db::name('video_unpublished')
            ->where(['user_id' => USERID, 'delete_time' => null])
            ->limit($offset, $length)
            ->order('id desc')
            ->select();
        $VideoService   = new VideoService();
        $res            = $VideoService->initializeFilm($user_films, VideoService::$allow_fields['own_publish']);
        $nickname       = $this->user['nickname'];
        foreach ($res as $key => &$val) {
            $val['is_pay']      = 1; //当前用户是否已购买
            $val['status_desc'] = $val['audit_status'] == 2 && $val['status'] == 0 ? '已下架' : $statusDescribe[$val['audit_status']];
            $val['status']      = $val['audit_status'] == 2 && $val['status'] == 0 ? '0' : $val['audit_status'];
            if ($val['audit_status'] == 2 && $val['visible'] == 2) {
                $val['status_desc'] = '私密';
                $val['status']      = '-1';
            }
            !empty($nickname) && $val['nickname'] = $nickname;
            unset($val['audit_status']);
        }
        return $this->success($res);
    }

    /**
     * 下载统计
     * @return \App\Common\BuguCommon\BaseError|array|\bxkj_common\BaseError
     */
    public function downFilm()
    {
        $params = request()->param();
        $id     = $params['id'];
        $video  = Db::name('video')->where(['id' => $id])->find();
        if (empty($video)) return $this->jsonError('未查找到该视频');
        $isTestUser = config('app.test_user');
        $Down       = new Down();
        if ($video['user_id'] != USERID || !in_array(USERID, $isTestUser)) {
            switch ($video['is_down']) {
                case 1:
                    $is_down = $video['is_down'];
                    break;
                case 0:
                    $user     = new user();
                    $filmUser = $user->getUser($video['user_id']);
                    $is_down  = $filmUser['is_creation'] ? $video['is_down'] : $Down->isDown($video['user_id']);
                    break;
                default:
                    $is_down = $Down->isDown($video['user_id']);
                    break;
            }
            //if (!$is_down) return $this->jsonError('该视频无法离线缓存');
        }
        $res = $Down->insertDown(USERID, $id);
        if ($res === false) return $this->jsonError('离线缓存错误');
        return $this->success(['down_url' => $video['video_url'], 'user_id' => $video['user_id']], '已缓存');
    }

    public function download()
    {
        $params = request()->param();
        $id     = $params['id'];
        $video  = Db::name('video')->where(['id' => $id])->find();
        if (empty($video)) return $this->jsonError('未查找到该视频');
        $isTestUser = config('app.test_user');
        $Down       = new Down();
        if ($video['user_id'] != USERID || !in_array(USERID, $isTestUser)) {
            switch ($video['is_down']) {
                case 1:
                    $is_down = $video['is_down'];
                    break;
                case 0:
                    $user     = new user();
                    $filmUser = $user->getUser($video['user_id']);
                    $is_down  = $filmUser['is_creation'] ? $video['is_down'] : $Down->isDown($video['user_id']);
                    break;
                default:
                    $is_down = $Down->isDown($video['user_id']);
                    break;
            }
            //if (!$is_down) return $this->jsonError('该视频无法离线缓存');
        }
        $res = $Down->insertDown(USERID, $id);
        if ($res === false) return $this->jsonError('离线缓存错误');
        return $this->success(['down_url' => $video['video_url'], 'user_id' => $video['user_id']], '已缓存');
    }

    /**
     * 播放影片信息记录
     * @desc 在点击播放时所请求的接口，用于记录影片播放次数
     */
    public function playFilm()
    {
        $params      = request()->param();
        $PlayHistory = new PlayHistory();
        $params['duration'] = isset($params['duration']) ? $params['duration'] : '';
        $PlayHistory->insertPlayHistory($params['id'], $params['duration']);
        return $this->success([], '成功');
    }

    /**
     * 删除个人影片
     * @desc 删除个人发布的,参数id为影片的ID
     */
    public function delOwnFilm()
    {
        $params      = request()->param();
        $find        = Db::name('video_unpublished')->where(['id' => $params['id'], 'user_id' => USERID])->find();
        $findFridend = Db::name('friend_circle_message')->where(['uid' => USERID, 'video' => $find['video_url']])->find();
        if (!empty($findFridend)) {
            $ids    = [$findFridend['id']];
            $friend = new FriendCircleMessage();
            $num    = $friend->del($ids);
        }
        Db::name('video_unpublished')->where(['id' => $params['id'], 'user_id' => USERID])->update(['delete_time' => time()]);
        $rabbitChannel = new RabbitMqChannel(['video.delete']);
        $rabbitChannel->exchange('main')->sendOnce('video.delete', ['id' => $params['id']]);
        return $this->success([], '删除成功');
    }

    /**
     * 不感兴趣
     * @return mixed
     */
    public function unLike()
    {
        return $this->success([], '操作成功，将减少此类推荐信息');
    }

    /**
     * 获取打赏礼物资源
     * @return array
     */
    public function getRewardGift()
    {
        $params   = request()->param();
        $video_id = $params['video_id'];
        $Reward   = new Reward();
        $rs       = $Reward->rewardList($video_id);
        return $this->success($rs);
    }

    /**
     * 获取打赏榜单
     * @return array
     */
    public function getRewardRank()
    {
        $params   = request()->param();
        $video_id = $params['video_id'];
        $offset   = isset($params['offset']) ? $params['offset'] : 0;
        $length   = isset($params['length']) ? ($params['length'] > 100 ? 100 : $params['length']) : PAGE_LIMIT;
        $Reward   = new Reward();
        $rs       = $Reward->rank($video_id, $offset, $length);
        return $this->success($rs);
    }

    /**
     * 开启打赏宝箱
     *
     */
    public function openReward()
    {
        $params   = request()->param();
        $video_id = $params['video_id'];
        $digg_num = $params['digg_num'];
        $Reward   = new Reward();
        //$rs       = $Reward->openRewardGift($video_id, $digg_num);
        return $this->success([]);
    }

    /**
     * 打赏发布者
     *
     */
    public function giveReward()
    {
        $params          = request()->param();
        $video_id        = $params['video_id'];
        $publish_user_id = $params['publish_user_id'];
        $gift_id         = $params['gift_id'];
        $message         = $params['message'];
        //100字长度信息
        if (mb_strlen($message) > 100) return $this->jsonError('留言内容不能超过100个字符');
        $Reward = new Reward();
        $rs     = $Reward->giveReward($gift_id, $publish_user_id, $video_id, $message);
        if (!$rs) return $this->jsonError($Reward->getError());
        $rank       = $Reward->rank($video_id, 0, 3);
        $rs['rank'] = !empty($rank) ? $rank : [];
        return $this->success($rs, '感谢您的打赏~');
    }

    /**
     * 开启宝箱前置检查
     * @return \App\Common\BuguCommon\BaseError|\bxkj_common\BaseError
     */
    public function preOpenReward()
    {
        $params   = request()->param();
        $video_id = $params['video_id'];
        $Reward   = new Reward();
        $rs       = $Reward->preOpenReward($video_id);
        if (!$rs) return $this->jsonError($Reward->getError());
        return $this->success($rs);
    }

    public function getTeenagerVideoList()
    {
        $data                 = [];
        $params               = request()->param();
        $offset               = empty($params['offset']) ? 0 : $params['offset'];
        $length               = isset($params['length']) ? ($params['length'] > 100 ? 100 : $params['length']) : PAGE_LIMIT;
        $videoTeenagerService = new VideoTeenager();
        $list                 = $videoTeenagerService->getList(["status" => 1], $offset, $length);
        if ($list) {
            foreach ($list as $key => $value) {
                $info['id']               = $value['id'];
                $info['describe']         = $value['desc'];
                $info['user_id']          = "";
                $info['video_id']         = "";
                $info['video_url']        = $value['video_url'];
                $info['animate_url']      = "";
                $info['cover_url']        = $value['cover_url'];
                $info['zan_sum']          = 0;
                $info['comment_sum']      = "";
                $info['play_sum']         = "";
                $info['collection_sum']   = "";
                $info['share_sum']        = "";
                $info['duration']         = "";
                $info['width']            = 0;
                $info['height']           = 0;
                $info['file_size']        = "";
                $info['is_ad']            = 0;
                $info['ad_url']           = "";
                $info['goods_id']         = 0;
                $info['short_title']      = "";
                $info['goods_type']       = 0;
                $info['cate_id']          = 0;
                $info['friend_group']     = [];
                $info['topic_group']      = [];
                $info['treasure_chest']   = 0;
                $info['is_self']          = 0;
                $info['is_follow']        = 1;
                $info['is_collect']       = 0;
                $info['is_zan']           = 0;
                $info['is_live']          = 0;
                $info['room_model']       = "0";
                $info['room_id']          = "0";
                $info['reward_rank']      = [];
                $info['music']            = new \ArrayObject();
                $info['publish_time']     = isset($val['add_time']) && !empty($val['add_time']) ? time_before($val['add_time'], '前') : '';
                $info['long_video_limit'] = 0;
                $info['jump']             = "";
                $info['location']         = new \ArrayObject();
                $info['nickname']         = '';
                $info['avatar']           = '';
                $info['video_act']        = new \ArrayObject();
                $data[]                   = $info;
            }
        }
        return $this->jsonSuccess($data, "获取成功");
    }
}
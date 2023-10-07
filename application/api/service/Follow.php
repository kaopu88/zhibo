<?php

namespace app\api\service;

use app\common\service\DsSession;
use app\common\service\Service;
use app\api\service\live\Lists;
use app\api\service\Video;
use bxkj_common\Console;
use bxkj_module\service\Task;
use think\Db;
use app\api\service\Recommend_Content;
use bxkj_common\RedisClient;
use bxkj_common\RabbitMqChannel;
use bxkj_module\service\User;

class Follow extends Service
{
    protected static $fansKey = 'fans:', $followKey = 'follow:';
    protected static $pnum = 10;

    //添加关注
    public function addFollow($user_id, $is_return = 0, $exists = [])
    {

        $redis = RedisClient::getInstance();
        if ($redis->zscore('blacklist:' . $user_id, USERID)) return $this->setError('对方已将您加入黑名单，无法关注对方');
        $nowTime   = time();
        $recommend = (object)[];
        $data      = [
            'user_id'     => USERID,
            'follow_id'   => $user_id,
            'type'        => 1,
            'create_time' => $nowTime
        ];
        $is_follow = $this->is_follow($user_id, USERID);
        if ($is_follow) {
            Db::name('follow')->where(array('user_id' => $user_id, 'follow_id' => USERID))->update(['ismutual' => 1]);
            $data['ismutual'] = 1;
        }
        $res = Db::name('follow')->insert($data);
        if ($res === false) return $this->setError('关注ID为' . $user_id . '的用户失败');
        $redis->zadd(self::$fansKey . $user_id, $nowTime, USERID);
        $redis->zadd(self::$followKey . USERID, $nowTime, $user_id);
        $fans_num = $redis->zcard(self::$fansKey . $user_id);
        $fans_num >= 10000 && $fans_num = number_format2($fans_num);
        if ($is_return) {
            $recommendModel = new Recommend_Content();
            $recommends = !empty($exists) ? $recommendModel->getMasterByAddFollow(USERID, 0, 1, $exists) : $recommendModel->getMaster(USERID, 0, 1);
            if (!empty($recommends[0])) {
                $recommend = $recommends[0];
                $recommend['is_follow'] = 0;
            }
        }
        Db::name('user_address_book')->where(['user_id' => USERID, 'friend_id' => $user_id])->update(['is_follow' => 1]);

        //关注
        $rabbitChannel = new RabbitMqChannel(['user.behavior']);
        $rabbitChannel->exchange('main')->sendOnce('user.behavior.follow', [
            'behavior' => 'follow',
            'data'     => [
                'user_id' => USERID,
                'to_uid'  => $user_id
            ]
        ]);
        return [
            'status'       => 1,
            'fans_num_str' => (string)$fans_num,
            'recommend'    => $recommend,
        ];
    }

    //取消关注
    public function unFollow($user_id)
    {
        $res = Db::name('follow')->where(array('user_id' => USERID, 'follow_id' => $user_id))->delete();
        Db::name('follow')->where(array('user_id' => $user_id, 'follow_id' => USERID))->update(['ismutual' => 0]);
        $redis = RedisClient::getInstance();
        if (!$res) return $this->setError('取消关注的用户' . $user_id . '失败');
        $redis->zrem(self::$fansKey . $user_id, USERID);
        $redis->zrem(self::$followKey . USERID, $user_id);
        Db::name('user_address_book')->where(['user_id' => USERID, 'friend_id' => $user_id])->update(['is_follow' => 0]);
        //取消关注
        $rabbitChannel = new RabbitMqChannel(['user.behavior']);
        $rabbitChannel->exchange('main')->sendOnce('user.behavior.cancel_follow', [
            'behavior' => 'cancel_follow',
            'data'     => [
                'user_id' => USERID,
                'to_uid'  => $user_id
            ]
        ]);
        $fans_num = $redis->zcard(self::$fansKey . $user_id);
        $fans_num >= 10000 && $fans_num = number_format2($fans_num);
        return ['status' => 0, 'fans_num_str' => (string)$fans_num];
    }

    //粉丝列表
    public function fansList($user_id, $start)
    {
        $fansList = Db::name('follow')->field('user_id, type')->where(['follow_id' => $user_id])->limit($start, PAGE_LIMIT)->order('id desc')->select();
        if (empty($fansList)) return [];
        $userModel = new user();
        $fansIds = array_column($fansList, 'user_id');
        $lists = $userModel->getUsers($fansIds, USERID, 'user_id, avatar, is_follow, nickname, gender, level, is_official, verified, is_creation, sign, vip_status');
        foreach ($lists as $key => &$value) {
            if (!isset($value['user_id']) || empty($value['user_id'])) continue;
            if ($value['user_id'] == USERID) $value['is_follow'] = 1;
        }
        return $lists;
    }

    //关注列表
    public function followList($user_id, $offset, $length)
    {
        
        $redis      = RedisClient::getInstance();
        $followList = Db::name('follow')->field('follow_id')->where(['user_id' => $user_id])->limit($offset, $length)->order('id desc')->select();
        if (empty($followList)) return [];
        $userModel = new user();
        $followIds = array_column($followList, 'follow_id');
        $lists = $userModel->getUsers($followIds, USERID, 'user_id,avatar,is_follow,nickname,gender,is_official,level,verified,is_creation,sign,vip_status');
        foreach ($lists as &$value) {
            if (!isset($value['user_id']) || empty($value['user_id'])) continue;
            if ($value['user_id'] == USERID || $user_id == USERID) $value['is_follow'] = 1;
            $value['is_live'] = (int)$redis->sismember(self::$livePrefix . 'Living', $value['user_id']);
        }
        return $lists;
    }

    //好友列表(互关)
    public function mutualList($user_id, $offset, $length)
    {
//        $redis = RedisClient::getInstance();
        $followList = Db::name('follow')->field('follow_id')->where(['user_id' => $user_id, 'ismutual' => 1])->limit($offset, $length)->order('id desc')->select();
        if (empty($followList)) return [];
        $userModel = new user();
        $followIds = array_column($followList, 'follow_id');
        $lists = $userModel->getUsers($followIds, USERID, 'user_id,avatar,is_follow,nickname,gender,is_official,level,verified,is_creation,sign,vip_status');
        foreach ($lists as &$value) {
            if (!isset($value['user_id']) || empty($value['user_id'])) continue;
            if ($value['user_id'] == USERID || $user_id == USERID) $value['is_follow'] = 1;
        }
        return $lists;
    }

    public function currentFollow($user_id, $offset, $length)
    {
        $redis = RedisClient::getInstance();
        //最新关注列表
        $followList = Db::name('follow')->field('follow_id')->where(['user_id' => $user_id])->limit($offset, $length)->order('id desc')->select();
        if (empty($followList)) return [];
        $userModel = new user();
        $followIds = array_column($followList, 'follow_id');
        $lists = $userModel->getUsers($followIds, USERID, 'user_id,avatar,is_follow,nickname,gender,level,verified,is_creation,sign,vip_status');
        $FilmModel = new Video();
        $newPublish = $FilmModel->followNewPublishTime($followIds);
        foreach ($lists as &$value) {
            if (!isset($value['user_id']) || empty($value['user_id'])) continue;
            if ($value['user_id'] == USERID || $user_id == USERID) $value['is_follow'] = 1;
            $value['room_id'] = $value['room_model'] = '0';
            $value['jump'] = getJump('personal', ['user_id' => $value['user_id']]);
            $value['is_see'] = 1;
            //是否在直播中
            $value['is_live'] = (int)$redis->sismember(self::$livePrefix . 'Living', $value['user_id']);
            if ($value['is_live']) {
                $Live = new Lists();
                $room = $Live->getRoomByUserId($value['user_id']);
                $value['room_id'] = $room['room_id'];
                $value['room_model'] = $room['room_model'];
                $value['jump'] = getJump('enter_room', ['room_id' => $room['room_id'], 'from' => 'follow']);
            }
            if (array_key_exists($value['user_id'], $newPublish)) {
                //获取查看当前用户视频的最后
                $see_time = DsSession::get('video_view_time.' . $value['user_id']);
                if (!empty($see_time)) {
                    $newPublish[$value['user_id']] > $see_time && $value['is_see'] = 0;
                }
            }
        }
        usort($lists, function ($a, $b) {
            if ($a['is_live'] || $b['is_live']) {
                if ($a['is_live'] == $b['is_live']) return 0;
                return ($a['is_live'] < $b['is_live']) ? 1 : -1;
            } else {
                if ($a['is_see'] == $b['is_see']) return 0;
                return ($a['is_see'] < $b['is_see']) ? -1 : 1;
            }
        });
        return $lists;
    }

    public function recommend($offset = 0, $length = 3)
    {
        $redis = RedisClient::getInstance();
        $his = empty(USERID) ? APP_MEID : USERID;
        if (empty($his)) $his = ACCESS_TOKEN;
        $key            = 'cache:recommend:' . $his;
        $offset         = $redis->get($key);
        $recommendModel = new Recommend_Content();
        $offset         = !empty($offset) ? $offset : 0;
        $recommends     = $recommendModel->getMaster(USERID, $offset, $length);
        if (count($recommends) < $length) {
            $offset         = 0;
            $length         = $length - count($recommends);
            $recommends_old = $recommendModel->getMaster(USERID, $offset, $length);
            $recommends     = array_merge($recommends, $recommends_old);
        }
        if (empty($recommends)) return [];
        $redis->set($key, $length + $offset);
//        $now = time();
        foreach ($recommends as &$val) {
            $val['is_follow'] = 0;
            /*$is_follow = $this->isFollow($val['user_id']);

            $val['is_follow'] = (int)$is_follow;

            $val['vip_status'] = $val['vip_expire'] < $now ? '0' : '1';

            $val['is_live'] = (int)$this->redis->sismember(self::$livePrefix.'Living', $val['user_id']);*/
        }
        return $recommends;
    }

    private function getRecommendOffset($init = false)
    {
        $redis = RedisClient::getInstance();
        $his = empty(USERID) ? APP_MEID : USERID;
        if (empty($his)) $his = ACCESS_TOKEN;
        $key = 'cache:recommend:' . $his;
        if ($init) $redis->del($key);
        $redis->incr($key);
        return $redis->get($key);
    }

    public function isFollow($user_id)
    {
        if (empty(USERID)) return false;
        $res = $this->getFollowInfo(USERID, $user_id);
        return $res['is_follow'];
    }

    public function is_follow($user_id, $follow_id)
    {
        if (empty(USERID)) return false;
        $res = $this->getFollowInfo($user_id, $follow_id);
        return $res['is_follow'];
    }

    public function fansCount($user_id)
    {
        return Db::name('follow')->where('follow_id', $user_id)->count('user_id');
    }

    public function followCount($user_id)
    {
        return Db::name('follow')->where('user_id', $user_id)->count('follow_id');
    }

    public function getAllFollow($user_id)
    {
        return Db::name('follow')->field('follow_id')->where('user_id', $user_id)->select();
    }

    //获取关注信息
    public function getFollowInfo($user_id, $followBuguid)
    {
        $followInfo = ['is_follow' => 0, 'follow_time' => ''];
        if (empty($user_id)) return $followInfo;
        $redis = RedisClient::getInstance();
        $key   = "follow:{$user_id}";
        //重建
        if (!$redis->exists($key)) {
            $followDb   = Db::name('follow');
            $where      = ['user_id' => $user_id];
            $followList = $followDb->field('follow_id,create_time')->where($where)->find();
            $redis->zAdd($key, time(), 0);
            if (!empty($followList)) {
                foreach ($followList as $follow) {
                    if (empty($follow)) continue;
                    $redis->zAdd($key, $follow['create_time'], $follow['follow_id']);
                    if ($follow['follow_id'] == $followBuguid) {
                        $followInfo['is_follow']   = 1;
                        $followInfo['follow_time'] = $follow['create_time'];
                    }
                }
                return $followInfo;
            }
        }
        $score = $redis->zScore($key, $followBuguid);
        if ($score) {
            $followInfo['is_follow']   = 1;
            $followInfo['follow_time'] = $score;
        }
        return $followInfo;
    }

    //获取最新发布的视频
    public function getNewPublish($user_id, $offset = 0, $length = 10)
    {
        $res = Db::name('follow')->alias('f')
            ->join('__VIDEO__ v', 'f.follow_id=v.user_id')
            ->field('v.*')
            ->where(['f.user_id' => $user_id])
            ->order('v.id desc')
            ->paginate(['list_rows' => $length, 'page' => $offset]);
        if ($res->isEmpty()) return [];
        return $res->items();
    }

    public function getFriendNewPublish($user_id, $offset = 0, $length = 10)
    {
        $prefix = config('database.prefix');
        $sql    = sprintf('SELECT v.* FROM ' . $prefix . 'follow follow INNER JOIN ' . $prefix . 'video v ON follow.follow_id=v.user_id WHERE follow.user_id=%s and follow.ismutual=1 ORDER BY v.id desc LIMIT %s, %s', $user_id, $offset, $length);
        return Db::query($sql);
    }

    //返回好友id(互关)
    public function mutualArray($user_id)
    {
        $followList = Db::name('follow')->field('follow_id')->where(['user_id' => $user_id, 'ismutual' => 1])->order('id desc')->select();
        if (empty($followList)) return [];
        $followIds = array_column($followList, 'follow_id');
        $followIds[] = (int)$user_id;
        return $followIds;
    }

    public function mutualArrayNoMY($user_id)
    {
        $followList = Db::name('follow')->field('follow_id')->where(['user_id' => $user_id, 'ismutual' => 1])->order('id desc')->select();
        if (empty($followList)) return [];
        $followIds = array_column($followList, 'follow_id');
        return $followIds;
    }

    public function getAllFollowUser($userId)
    {
        $redis = new RedisClient();
        $follow = $redis->zRange(self::$followKey . $userId,0, -1);
        if (empty($follow)) return [];
        return $follow;
    }
}

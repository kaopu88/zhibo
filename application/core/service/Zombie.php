<?php

namespace app\core\service;


use app\core\model\Live as LiveModel;
use app\core\model\Robot;
use app\core\service\User as UserService;
use bxkj_common\CoreSdk;
use bxkj_common\RedisClient;
use think\Collection;
use think\Db;
class Zombie
{
    protected static $livePrefix = 'BG_LIVE:';
    protected static $guardKey = 'BG_GUARD:';
    protected static $zombiePoolKey = 'zombiePool';

    protected static $zombieSuppliesKey = 'supplies',
        $suppPageKey = 'suppliesPage',
        $audienceKey = ':audience',
        $roomZombieKey = ':robot',
        $roomTaskKey = ':robotTask';

    protected static $max_timeout = 86400;

    protected static $length = 20;


    //补充僵尸粉(注:并发时还要加一个锁)
    protected static function supplies($least)
    {
        $allZombieList = [];

        $redis = RedisClient::getInstance();

        /*     $allRoomId = LiveModel::where('status', 1)->field('id')->select();

             $allRoom = Collection::make($allRoomId)->toArray();

             foreach($allRoom as $value)
             {
                 $zombieRoomList = $redis->smembers(self::$livePrefix.$value['id'].self::$roomZombieKey);

                 $allZombieList = array_unique(array_merge($allZombieList, $zombieRoomList));
             }

             $freeZombie = $redis->smembers(self::$zombiePoolKey);

             $allZombieList = array_unique(array_merge($allZombieList, $freeZombie));//所有的僵尸粉*/
             

        $offset = $redis->get(self::$suppPageKey);

        $offset = empty($offset) ? 1 : $offset;

        $start = ($offset-1)*self::$length;

        $ZombieList = (new Robot())->field('user_id')->where('status=\'1\'')->limit($start, self::$length)->select();

        $ZombieList = Collection::make($ZombieList)->toArray();

        if (empty($ZombieList))
        {
            $redis->setex(self::$zombieSuppliesKey, 43200, 1);//标识用于确定是否补充(临时用过期时间处理下)

            return false; //没有过多的僵尸粉
        }

        $redis->incr(self::$suppPageKey);

        //$suppliesData = array_diff(array_column($ZombieList, 'user_id'), $allZombieList);//待补充的僵尸粉集合

        $suppliesData = array_column($ZombieList, 'user_id');//待补充的僵尸粉集合

        //array_unshift($suppliesData, self::$zombiePoolKey);

        if (!empty($suppliesData)) {
            foreach ($suppliesData as $key => $value) {
                $redis->sadd(self::$zombiePoolKey, $value);
            }
        }

        //call_user_func_array([$redis, 'sadd'], $suppliesData); //补充僵尸粉

        $redis->del(self::$zombieSuppliesKey); //删除标识

        //没有过多的僵尸粉或不满足最低需求量
        if (count($suppliesData) < $least)
        {
            $redis->setex(self::$zombieSuppliesKey, 43200, 1);//标识用于确定是否补充(临时用过期时间处理下)
        }

        return true;
    }


    //分配僵尸粉
    public static function handleZombie($roomId, $number)
    {
        $redis = RedisClient::getInstance();

        $zombie = config('app.live_setting.robot');

        if ($number > $zombie['max']) $number = $zombie['max'];

        $currentCount = $redis->zcard(self::$livePrefix.$roomId.self::$roomZombieKey);; //计算该房间现有机器人数量

        if($number > $currentCount) //用户所需求的僵尸粉数量大于当前已存在的僵尸粉数量
        {
            $needTotal = $number - $currentCount;

            $freeTotal = $redis->scard(self::$zombiePoolKey); //机器人总数量

            if($needTotal > $freeTotal)
            {
                if (!$redis->exists(self::$zombieSuppliesKey)) //是否补充
                {
                    $supplies = self::supplies($needTotal); //补充僵尸粉

                    if ($supplies) return self::handleZombie($roomId, $number);
                }

                if(!empty($freeTotal) || isset($supplies) && $supplies === false)
                {
                    return self::handleZombie($roomId, $freeTotal); //分配最后剩的僵尸粉
                }

                return false;
            }
            else{
                return self::addZombie($needTotal, $roomId); //加入任务队列
            }
        }
        else if($number < $currentCount)//用户所需求的僵尸粉数量小于当前已存在的僵尸粉数量 则减少退出部分僵尸粉
        {
            $removeTotal = $currentCount - $number;

            return self::removeZombie($removeTotal, $roomId);
        }
        else{
            return true;//处理完毕
        }
    }


    //分配僵尸粉
    protected static function addZombie($need_total, $room_id)
    {
        $redis = RedisClient::getInstance();

        $coreSdk = new CoreSdk();

        for($i = 1; $i <= $need_total; $i++)
        {
            $robot_uid = $redis->spop(self::$zombiePoolKey); //在池内随机拿一个机器人

            $robot_info = $coreSdk->getUser($robot_uid);//获取僵尸粉的基础信息

            if (empty($robot_info)) continue;

            $zombieTaskData = [
                'avatar' => $robot_info['avatar'],
                'zid' => $robot_uid,
                'zname' => $robot_info['nickname'],
                'zlevel' => $robot_info['level'],
            ];

            $zombieTaskStr = json_encode($zombieTaskData);

            $redis->lpush(self::$livePrefix.$room_id.self::$roomTaskKey, $zombieTaskStr);
        }

        return true;
    }

    //移除机器人
    protected static function removeZombie($need_total, $room_id)
    {
        $redis = RedisClient::getInstance();

        $robot_uid = $redis->zrevrange(self::$livePrefix.$room_id.self::$roomZombieKey, 0, $need_total-1);//移除一个随机的机器人

        foreach ($robot_uid as $user_id=>$level)
        {
            $redis->sadd(self::$zombiePoolKey, $level);//加入总池内
        }

        $redis->zremrangebyrank(self::$livePrefix.$room_id.self::$roomZombieKey, 0, $need_total-1);//移除机器人

        return true;
    }


    //获取直播间观众数
    public static function getAudienceNum(array $roomIds)
    {
        $arr = [];

        $redis = RedisClient::getInstance();

        foreach ($roomIds as $key => $room_id)
        {
            $realAudience = $redis->zcard(self::$livePrefix.$room_id.self::$audienceKey);

            $zombie = $redis->zcard(self::$livePrefix.$room_id.self::$roomZombieKey);

            $realAudience *= 2;

            $zombie *= 20;

            $sum = $realAudience + $zombie;

            $arr[$room_id] = empty($sum) ? 480 : $sum;
        }

        return $arr;
    }


    //获取在线观众集合，包括机器人
    public static function getAudienceList($room_id)
    {
        $rs = [];
        $num = 30;
        $length = 3;
        $redis = RedisClient::getInstance();
        $audiens = $redis->zrevrange(self::$livePrefix.$room_id.self::$audienceKey, 0, $num-1, 1);
        if (!empty($audiens))
        {
            $userIds = array_keys($audiens);
            $anchor_id=self::getAnchorIdByRoomId($room_id); //当前房间主播id 用room_id获取
            //获取有没有守护观众
            foreach ($userIds as $k=>$v){
                $is_status = $redis->zscore('BG_GUARD:'.$anchor_id, $v);
                if($is_status){
                    $current_guards[]=$v; //存储到当前房间
                    unset($v);
                }
            }
            
            $userServer = new UserService();

            $users = $userServer->getUsers($userIds);

            foreach ($users as $value)
            {
                if (empty($value)) continue;

                $rs[] = [
                    'user_id' => $value['user_id'],
                    'avatar' => $value['avatar'],
                    'nickname' => $value['nickname'],
                    'level' => $value['level'],
                    'gender' => $value['gender'],
                ];
            }
        }

        if (empty($audiens) || count($audiens) < $num)
        {
            $robot = [];

            $robots_num = $num-count($audiens);

            $robotList = $redis->zrevrange(self::$livePrefix.$room_id.self::$roomZombieKey, 0, $robots_num-1, 1);

            foreach ($robotList as $robot_id=>$robot_level)
            {
                $robot_info = $redis->get('robot:'.$robot_id);

                if (empty($robot_info)) continue;

                $robot_info = json_decode($robot_info, true);

                $robot[] = [
                    'user_id' => $robot_id,
                    'avatar' => $robot_info['avatar'],
                    'nickname' => $robot_info['nickname'],
                    'level' => $robot_level,
                    'gender' => rand(0,1),
                ];
            }
            $rs = array_merge($rs, $robot);
        }

        return $rs;
    }



    //根据房间ID获取主播UID
    public static  function getAnchorIdByRoomId($roomId)
    {
        $redis = RedisClient::getInstance();
        $roomAnchorIdKey = 'BG_LIVE:' . $roomId . ':anchorInfo';
        $anchorId = $redis->get($roomAnchorIdKey);
        if (empty($anchorId)) {
            $res = Db::name('live')->field('id,user_id ')->where('id', '=', $roomId)->find();
            $anchorInfo = $res[0];
            $anchorId = $anchorInfo['user_id'];
            $redis->set($roomAnchorIdKey, $anchorId);
            $redis->expire($roomAnchorIdKey, 4 * 3600);
        }
        return $anchorId;
    }


    // 获取直播间实时观众数
    public static function getAudienceCount($room_id)
    {
        return RedisClient::getInstance()->zcard(self::$livePrefix.$room_id.self::$audienceKey);
    }


    // 僵尸粉统计
    public static function getZombieCount($room_id)
    {
        return RedisClient::getInstance()->zcard(self::$livePrefix.$room_id.self::$roomZombieKey);
    }


    //回收机器人
    public static function releaseZombieResource($room_id)
    {
        $redis = RedisClient::getInstance();

        $robotList = $redis->zrevrange(self::$livePrefix.$room_id.self::$roomZombieKey, 0, -1, true);//获取该房间已分配的机器人

        if(!empty($robotList))
        {
            foreach ($robotList as $uid=>$level)
            {
                $redis->sadd(self::$zombiePoolKey, $uid);
            }
        }

        if ($redis->exists(self::$livePrefix.$room_id.self::$roomTaskKey))
        {
            $unTaskRobot = $redis->lrange(self::$livePrefix.$room_id.self::$roomTaskKey, 0, -1);

            foreach ($unTaskRobot as $info)
            {
                $data = json_decode($info, true);

                $redis->sadd(self::$zombiePoolKey, $data['zid']);
            }
        }

        $redis->del([self::$livePrefix.$room_id.self::$roomZombieKey, self::$livePrefix.$room_id.self::$roomTaskKey]);
    }


    /**
     * 手动分配机器人
     * @param $room_id
     * @param $number
     * @return bool
     */
    public static function addRobot($room_id, $number)
    {
        $redis = RedisClient::getInstance();

        $currentCount = $redis->zcard(self::$livePrefix.$room_id.self::$roomZombieKey);; //计算该房间现有机器人数量

        if ($currentCount > 300) return make_error('机器人已达上限');

        //重置上限
        if ($number > (300-$currentCount)) $number = 300-$currentCount;
        $coreSdk = new CoreSdk();

        for($i = 1; $i <= $number; $i++)
        {
            $robot_uid = $redis->spop(self::$zombiePoolKey); //在池内随机拿一个机器人

            if (empty($robot_uid))
            {
                $rs = self::supplies($number-$i);

                if (!$rs) return make_error('没有更多机器人资源~');
            }

            $key =  "robot:{$robot_uid}";
            $userJson = $redis->get($key);
            $robot_info = $userJson ? json_decode($userJson, true) : null;
            if (empty($robot_info) || empty($robot_info['user_id']) || $robot_info['cache_expired_time'] <= time()) {
                $userService = new User();
                $robot_info =  $userService->getInfoArr($robot_uid, false, true);;//获取僵尸粉的基础信息
                if ($robot_info) {
                    $user['cache_expired_time'] = time() + (3600 * 24 * 7);
                    $redis->set($key, json_encode($robot_info));
                }
            }

            if (empty($robot_info))
            {
                $i--;
                continue;
            };

            $isRobot = $redis->sIsMember('robot_sets', $robot_uid);
            if (!$isRobot) $redis->sAdd("robot_sets", $robot_uid);

            $redis->zadd(self::$livePrefix.$room_id.self::$roomZombieKey, $robot_info['level'], $robot_uid);
        }

        return true;
    }


    /**
     * 手动移除机器人
     * @param $room_id
     * @param $number
     * @return \App\Common\BuguCommon\BaseError|bool|\bxkj_common\BaseError
     */
    public static function removeRobot($room_id, $number)
    {
        $redis = RedisClient::getInstance();

        $currentCount = $redis->zcard(self::$livePrefix.$room_id.self::$roomZombieKey);; //计算该房间现有机器人数量

        if ($currentCount < $number) return make_error('机器人已达下限');

        $start = $currentCount-$number;

        //$redis->zremrangebyrank(self::$livePrefix.$room_id.self::$roomZombieKey, $start, $currentCount-1);
        self::removeZombie($number, $room_id);
        return true;
    }


    //获取直播间观众数
    public static function getAudiences(array $roomIds)
    {
        $arr = [];

        $redis = RedisClient::getInstance();

        foreach ($roomIds as $key => $room_id)
        {
            $realAudience = $redis->zcard(self::$livePrefix.$room_id.self::$audienceKey);

            $arr[$room_id] = $realAudience;
        }

        return $arr;
    }



    //获取直播间机器人数
    public static function getRobots(array $roomIds)
    {
        $arr = [];

        $redis = RedisClient::getInstance();

        foreach ($roomIds as $key => $room_id)
        {
            $zombie = $redis->zcard(self::$livePrefix.$room_id.self::$roomZombieKey);

            $arr[$room_id] = $zombie;
        }

        return $arr;
    }


}
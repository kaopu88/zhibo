<?php
/**
 * Created by PhpStorm.
 * User: zack
 * qq: 840855344
 * phone：18156825246
 */

namespace app\api\service\live;

use app\api\service\LiveBase2;

use app\common\service\User;
use bxkj_common\RedisClient;
use think\Db;

class Zombie extends LiveBase2
{
    protected static $zombieSuppliesKey = 'supplies',
        $suppPageKey = 'suppliesPage',
        $audienceKey = ':audience',
        $roomZombieKey = ':robot',
        $roomTaskKey = ':robotTask';

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

            $userServer = new User();

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
                    'birthday' => $value['birthday'],
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
}
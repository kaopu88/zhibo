<?php

namespace app\h5\service;

use bxkj_module\service\GiftLog;
use bxkj_module\service\Service;
use bxkj_common\RedisClient;
use bxkj_module\service\User;
use bxkj_module\service\Bean;
use think\Db;

class CoverStar extends Service
{
    public function getranks($length=20)
    {

        $m = date('Ym');
        $key = "coverstar:m:{$m}";
        $redis = RedisClient::getInstance();
        $ranks = $redis->zRevRange($key, 0, $length-1, true);
        $star = $this->getstar(date("Ym",strtotime("last month")));
        $list = [];
        $rankList = [];
        if (!empty($ranks)) {
            $i = 0;
            foreach ($ranks as $member => $votes) {
                $memberIds[] = $member;
                $list[] = array(
                    'user_id' => $member,
                    'votes' => $votes,
                    'rank' => $i+1
                );
                $i++;
            }

            $members = [];
            if (!empty($memberIds)) {
                $userModel = new user();
                $members = $userModel->getUsers($memberIds, null, 'user_id, nickname, avatar');
                $members = $members ? $members : [];
            }

            foreach ($list as &$item) {
                $user = $this->getItemByList($item['user_id'], $members, 'user_id');
                $item = array_merge_notrepeat($item, $user ? $user : array(), 'user_');
                if (isset($item['user_id'])) $item['user_id'] = $item['user_id'];
                $rankList[] = $item;
            }
        }

        return [
            'rankList' => $rankList,
            'star' => $star
        ];
    }

    public function getstar($ym)
    {
        $key = "coverstar:m:{$ym}";
        $redis = RedisClient::getInstance();
        $last_first = $redis->zrevrange($key, 0, 0, true);
        $star = [];
        if( !empty($last_first) ){
            $user_id = key($last_first);
            $userModel = new user();
            $star_info = $userModel->getUser($user_id,null,'user_id, nickname, avatar');
            return $star_info;
        }
        return $star;
    }

    public function startovote($user_id,$to_user_id,$vote)
    {
        $userModel = new user();

        $anchor = $userModel->getUser($to_user_id, null, 'user_id, nickname, avatar, is_anchor');

        if(!$anchor || $anchor['is_anchor'] != '1') return $this->setError('主播信息有误');

        $data = [
            'user_id' => $user_id,
            'total' => $vote*100,
            'to_uid' => $to_user_id,
            'trade_no' => get_order_no('cover_star_vote'),
        ];

        $beanService = new GiftLog();

        $beanRes = $beanService->coverStartVote($data);

        if (!$beanRes) return $this->setError($beanService->getError());

        $m = date('Ym');
        $key = "coverstar:m:{$m}";

        $redis = RedisClient::getInstance();
        $redis->zincrby($key, $vote, $to_user_id);

        $logData['trade_no'] = $data['trade_no'];
        $logData['user_id'] = $user_id;
        $logData['to_user_id'] = $to_user_id;
        $logData['votes'] = $vote;
        $logData['bean'] = $vote * 100;
        $logData['create_time'] = time();

        Db::name('cover_star_vote_log')->insertGetId($logData);

        return true;
    }
}
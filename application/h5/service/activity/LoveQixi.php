<?php

namespace app\h5\service\activity;


use app\h5\service\Activity;
use think\Db;

class LoveQixi extends Activity
{

    protected static $activity_config_name = 'love_qixi';

    protected static $page = 30;


    protected $where = [
        ['mark', 'eq', 'love_qixi'],
    ];


    public function getRank($p=1)
    {
        $activity_config = $this->getConfig();

        $anchor_rank_key = $activity_config['rule']['redis_key'].$activity_config['rule']['current_act'].$activity_config['rule']['anchor_key'];

        $start = ($p-1)*self::$page;

        $end = $p*self::$page;

        $rank = $start+1;

        $res = [];

        $rankList = $this->redis->zrevrange($anchor_rank_key, $start, $end-1, true);

        if (empty($rankList)) return $res;

        $uids = array_keys($rankList);

        $users = $this->getUsersInfo($uids);

        foreach ($rankList as $user_id => $user_score)
        {
            if (!array_key_exists($user_id, $users)) continue;

            $fans_key = $activity_config['rule']['redis_key'].$activity_config['rule']['current_act'].$activity_config['rule']['fans_key'].$user_id;

            $is_follow = USERID ? ($this->redis->zscore('follow:'.$user_id, USERID) ? 1 : 0) : 0;

            $living = $this->redis->sismember('BG_LIVE:Living', $user_id);

            $tmp = [
                'rank' => $rank,
                'avatar' => $users[$user_id]['avatar'],
                'nickname' => $users[$user_id]['nickname'],
                'score' => number_format2($user_score),
                'uri' => getJump('personal', ['user_id' => $user_id]),
                'fans' => $this->getFansRank($fans_key),
                'is_follow' => $is_follow,
                'is_living' => $living
            ];

            array_push($res, $tmp);

            $rank++;
        }

        return $res;
    }


    protected function getFansRank($key)
    {
        $res = [];

        $rankList = $this->redis->zrevrange($key, 0, 2, true);

        if (empty($rankList)) return [];

        $uids = array_keys($rankList);

        $users = $this->getUsersInfo($uids);

        foreach ($rankList as $user_id => $user_score)
        {
            if (!array_key_exists($user_id, $users)) continue;

            $tmp = [
                'avatar' => $users[$user_id]['avatar'],
                'nickname' => $users[$user_id]['nickname'],
                'score' => number_format2($user_score),
                'uri' => getJump('personal', ['user_id' => $user_id])
            ];

            array_push($res, $tmp);
        }

        return $res;
    }


    public function getConfig()
    {
        $config = Db::name('activity')
            ->field('name, rule, start_time, end_time, icon, mark')
            ->where($this->where)
            ->find();

        $config['rule'] = json_decode($config['rule'], true);

        return $config;
    }




}
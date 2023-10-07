<?php


namespace app\h5\service;

use bxkj_module\service\Service;
use bxkj_common\RedisClient;
use bxkj_module\service\User;
use think\Db;

class PkRank extends Service
{
    protected static $list_rows = 20;

    public function getTopThree()
    {
        $data = [];
        $redis = RedisClient::getInstance();
        $rediskey = "activity:pk_rank:pk_rank_points";
        $TopThree = $redis->Zrevrange($rediskey, 0, 2);

        $userModel = new user();
        $emptyInfo = [
            'user_id' => 0,
            'nickname' => '暂无用户',
            'avatar' => img_url('','','avatar'),
            'is_live' => 0,
            'level' => 0,
            'score' => 0
        ];

        for ($i=0; $i<=2; $i++) {
            if( isset($TopThree[$i]) ){
                $uid = $TopThree[$i];
                $_info = $userModel->getUser($uid, null, 'user_id, nickname, avatar');
                $_info['rank'] = $i+1;
                $_info['is_live'] = (int)$redis->sismember('BG_LIVE:Living', $uid);
                $_info['score'] = (int)$redis->Zscore($rediskey, $uid);
                $_info['level'] = $this->getLevel($_info['score']);
                $data[] = $_info;
            } else {
                $_info = $emptyInfo;
                $_info['rank'] = $i+1;
                $data[] = $_info;
            }
        }

        $this->formatData($data, ['score']);

        $result = [];
        $result[0] = $data[1];
        $result[1] = $data[0];
        $result[2] = $data[2];

        return $result;
    }

    public function getAnchorList($level)
    {
        $data = [];
        $redis = RedisClient::getInstance();
        $list_key = "activity:pk_rank:pk_rank_points";

        $min = '';
        $max = '';
        switch ($level) {
            case 5:
                $min = '(2000';
                $max = '+inf';
                break;
            case 4:
                $min = '(1400';
                $max = '2000';
                break;
            case 3:
                $min = '(900';
                $max = '1400';
                break;
            case 2:
                $min = '(500';
                $max = '900';
                break;
            case 1:
                $min = '200';
                $max = '500';
                break;
        }

        if( empty($min) || empty($max) ){
            return $data;
        }

        $list = $redis->Zrangebyscore($list_key,$min, $max, array('withscores' => TRUE));
        $total = count($list);
        arsort($list);

        $anchors = array_slice($list, 0, 20,true);

        $userModel = new user();
        $i = 1;
        foreach ( $anchors as $key=>$val ) {
            $_info = $userModel->getUser($key, null, 'user_id, nickname, avatar');
            $_info['user_id'] = $key;
            $_info['rank'] = $i;
            $_info['is_live'] = (int)$redis->sismember('BG_LIVE:Living', $key);
            $_info['level'] = $level;
            $_info['score'] = $this->formatData($val);
            $data[] = $_info;
            $i++;
        }
        $this->formatData($data, ['score']);
        return ['list'=>$data, 'total'=>$total];
    }

    public function getFansList()
    {
        $data = [];
        $redis = RedisClient::getInstance();
        $list_key = "activity:pk_rank:pk_rank_fans";
        $total = $redis->Zcard($list_key);
        $list = $redis->Zrevrange($list_key, 0, 49, true);

        $userModel = new user();
        $i = 1;
        foreach ( $list as $key=>$val ) {
            $_info = $userModel->getUser($key, null, 'user_id, nickname, avatar');
            $_info['user_id'] = $key;
            $_info['rank'] = $i;
            $_info['score'] = $this->formatData($val);
            $data[] = $_info;
            $i++;
        }

        return ['list'=>$data, 'total'=>$total];
    }

    public function getpklist()
    {
        $redis = RedisClient::getInstance();
        $data = [];
        $list = Db::name('live_pk')->where(['status'=>0,'pk_type'=>'pk_rank'])->limit(5)->select();
        $userModel = new user();

        $day = date('Ymd').':';

        $key = "activity:pk_rank:" . $day . "pk_rank_win:";

        foreach ( $list as $val ){
            $left_user = $userModel->getUser($val['active_id'], null, 'user_id, nickname, avatar');
            $left_user['score'] = $val['active_income'];
            $left_user['con_win'] = (int)$redis->get($key.$val['active_id']);

            $right_user = $userModel->getUser($val['target_id'], null, 'user_id, nickname, avatar');
            $right_user['score'] = $val['target_income'];
            $right_user['con_win'] = (int)$redis->get($key.$val['target_id']);

            $_data = [
                'left_user' => $left_user,
                'right_user' => $right_user,
            ];

            $data[] = $_data;
        }

        return $data;
    }

    protected function getLevel($score)
    {
        switch (true) {
            case $score>2000:
                return 5;
                break;
            case $score>1400:
                return 4;
                break;
            case $score>900:
                return 3;
                break;
            case $score>500:
                return 2;
                break;
            case $score>200:
                return 1;
                break;
            default:
                return 0;
        }
    }

    //格式化数字
    protected function formatData(&$data, $field = [])
    {
        if (is_array($data) && !empty($field) && is_array($field))
        {
            foreach ($field as $key => $val)
            {
                key_exists($val, $data) && $data[$val] = $this->formatData($data[$val]);
            }
        }
        else {
            if ($data >= 100000000) {
                $real = sprintf("%.3f", $data / 100000000);
                $data = $real . 'e';
            }else if ($data >= 10000){
                $real = sprintf("%.1f", $data / 10000);
                $data = $real . 'w';
            }
        }
        return (string)$data;
    }
}
<?php

namespace app\push\controller;

use app\push\section\RechargeOrder;
use bxkj_common\DateTools;
use bxkj_common\RedisClient;
use bxkj_common\SectionManager;
use bxkj_module\service\Kpi;
use bxkj_module\service\Service;
use bxkj_module\service\User;
use think\Db;

class Tools extends Api
{
    public function redis_accuracy()
    {
        $this->persistent();
        $redis = RedisClient::getInstance();
        $agents = Db::name('agent')->select();
        $thatTime = mktime(0, 0, 0, 10, 1, 2018);
        $nodes = DateTools::getMonthNodes('d', $thatTime, 'Ymd');
        $month = date('Ym', $thatTime);
        $details = [];
        foreach ($agents as $agent) {
            $agentId = $agent['id'];
            $item = [];
            $key = "kpi:agent:all:cons:m:{$month}";
            $item['current'] = $redis->zScore($key, $agentId);
            $days_total = 0;
            foreach ($nodes as $node) {
                $days_total += $redis->zScore("kpi:agent:all:cons:d:{$node}", $agentId);
            }
            $promoters_total = 0;
            $key2 = "kpi:promoter:{$agentId}:cons:m:{$month}";
            $list = $redis->zRange($key2, 0, -1, true);
            foreach ($list as $member => $score) {
                $promoters_total += $score;
            }
            $item['promoters_total'] = $promoters_total;
            $item['days_total'] = $days_total;
            $real_total = Db::name('kpi_cons')->where([
                ['create_time', '>=', mktime(0, 0, 0, date('m', $thatTime), date('d', $thatTime), date('Y', $thatTime))],
                ['create_time', '<=', mktime(23, 59, 59, date('m', $thatTime), date('d', $thatTime), date('Y', $thatTime))],
                ['agent_id', 'eq', $agentId]
            ])->sum('total_fee');
            $item['real_total'] = $real_total;
            $item['name'] = $agent['name'];
            $item['id'] = $agent['id'];
            $item['current_real'] = $item['current'] - $item['real_total'];
            $item['current_days_total'] = $item['current'] - $item['days_total'];
            $item['current_promoters_total'] = $item['current'] - $item['promoters_total'];
            $details[] = $item;
        }
        $this->assign('details', $details);
        return $this->fetch();
    }
    
    public function import_robot()
    {
        exit();
        $robots = Db::name('robot2')->select();
        $redis = RedisClient::getInstance();
        $total = 0;
        foreach ($robots as $robot) {
            $userId = User::generateUserId();
            $avatar = $robot['avatar'];
            $matches = [];
            if (preg_match('/^http\:\/\/q\.qlogo\.cn\/qqapp\/(\d{1,30})\/([a-zA-Z0-9_]{0,64})\/\d{0,6}$/', $avatar, $matches)) {
                $avatar = "http://q.qlogo.cn/qqapp/{$matches[1]}/{$matches[2]}/640";
            }
            $userData = [
                'user_id' => $userId,
                'type' => 'robot',
                'isvirtual' => '1',
                'username' => 'ds_' . $userId,
                'nickname' => $robot['nickname'] ? $robot['nickname'] : uniqid(),
                'gender' => $robot['gender'] ? $robot['gender'] : '0',
                'avatar' => $avatar ? $avatar : img_url('', '', 'avatar'),
                'birthday' => $robot['birthday'],
                'province_id' => $robot['province_id'],
                'city_id' => $robot['city_id'],
                'district_id' => $robot['district_id'],
                'phone_code' => $robot['phone_code'],
                'phone' => $robot['phone'],
                'agent_id' => $robot['agent_id'],
                'status' => $robot['status'],
               // 'disable_time' => $robot['disable_time'],
               // 'disable_desc' => $robot['disable_desc']?$robot['disable_desc']:'',
                //'disable_length' => $robot['disable_length'],
                'exp' => $robot['exp'],
                //'points' => $robot['points'],
                'level' => $robot['level'],
                'live_status' => $robot['live_status'],
                'film_status' => $robot['film_status'],
                'last_upgrade_time' => $robot['last_upgrade_time'],
                'last_renick_time' => $robot['last_renick_time'],
                'verified' => $robot['verified'],
                'is_creation' => $robot['is_creation'],
                'password' => $robot['password'],
                'salt' => $robot['salt'],
                'promoter_uid' => 0,
                'anchor_uid' => 0,
                'cover' => '',
                'sign' => $robot['sign'] ? $robot['sign'] : '空空如也',
                'reg_way' => $robot['reg_way'],
                'isset_pwd' => '0',
                'like_num' => 0,
                'fans_num' => 0,
                'follow_num' => 0,
                'collection_num' => 0,
                'download_num' => 0,
                'film_num' => 0,
                'is_promoter' => '0',
                'is_anchor' => '0',
                'credit_score' => 100,
                'create_time' => $robot['create_time'] ? $robot['create_time'] : time()
            ];
            $res = Db::name('robot')->insert($userData);
            if ($res) {
                $redis->sAdd('robot_sets', $userId);
                $total++;
            }
        }
        var_dump($total);
    }


    public function user_agent()
    {
        exit();
        $this->persistent();
        $total = 0;
        $list = Db::name('user')->where([['agent_id', 'gt', 0]])->select();
        foreach ($list as $item) {
            $data = [
                'user_id' =>  $item['user_id'],
                'agent_id' =>  $item['agent_id'],
                'promoter_uid' =>  $item['promoter_uid']
            ];

            $is_have = Db::name('promotion_relation')->where($data)->find();
            if( $is_have ){
                continue;
            }

            $data['create_time'] = time();
            $res = Db::name('promotion_relation')->insertGetId($data);
            if ($res) {
                $total++;
            }
        }
        var_dump($total);
    }


    public function user_promoter()
    {
        exit();
        $this->persistent();
        $total = 0;
        Db::name('promoter')->where([['delete_time', 'gt', 0]])->delete();

        $list = Db::name('promoter')->select();
        foreach ($list as $item) {
            $data = [
                'promoter_uid' =>  $item['user_id'],
                'agent_id' =>  $item['agent_id']
            ];

            $client_num = Db::name('promotion_relation')->where($data)->count();

            $res = Db::name('promoter')->where(['user_id'=>$item['user_id']])->update(['client_num'=>$client_num]);
            if ($res) {
                $total++;
            }
        }
        var_dump($total);
    }

    public function user_anchor()
    {
        exit();
        $this->persistent();
        $total = 0;
        $res = Db::name('anchor')->where([['delete_time', 'gt', 0]])->delete();
        var_dump($res);
    }


}

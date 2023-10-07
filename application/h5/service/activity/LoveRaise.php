<?php

namespace app\h5\service\activity;


use app\h5\service\Activity;
use bxkj_common\CoreSdk;
use bxkj_common\RedisClient;
use think\Db;


/**
 * 爱的供养
 * Class LoveRaise
 * @package app\h5\service\activity
 */
class LoveRaise extends Activity
{

    public static $template = ['myPotTemplate', 'lovePotTemplate', 'rankTemplate'];

    public static $menu = [
        ['id' => 1, 'name' => '供养罐'],
        ['id' => 2, 'name' => '表白罐'],
        ['id' => 3, 'name' => '供养榜'],
    ];

    public static $redis_key = 'activity:', $act_name = 'love_raise:', $container = 'container';


    //生成主播罐子
    public function generateContainer($user_id, $num=10)
    {
        $container = [];

        $now = time();

        while ($num)
        {
            $tmp = [
                'user_id' => $user_id,
                'status' => 0, //初始化状态
                'limit_energy' => 9999, //能量阀值
                'provider_id' => 0, //供养人
                'create_time' => $now,
            ];

            array_push($container, $tmp);

            $num--;
        }

        Db::name('activity_love_container')->insertAll($container);

        $key = self::$redis_key.self::$act_name.self::$container;

        $this->redis->zadd($key, 1, $user_id);

        return true;
    }


    public function getAllRankByUser($p=1)
    {
        $start = ($p-1)*self::$page;

        $end = $p*self::$page;

        $rank = $start+1;

        $data = [];

        $rankList = $this->redis->zrevrange(self::$redis_key.self::$act_name.'provider', $start, $end-1, true);

        if (empty($rankList)) return $data;

        $uids = array_keys($rankList);

        $users = $this->getUsersInfo($uids);

        foreach ($rankList as $user_id=>$user_score)
        {
            $user_score /= 10;

            $user = [
                'rank' => $rank,
                'avatar' => $users[$user_id]['avatar'],
                'nickname' => $users[$user_id]['nickname'],
                'energy' => number_format2($user_score),
                'uri' => getJump('personal', ['user_id' => $user_id])
            ];

            $rank++;

            array_push($data, $user);
        }

        return $data;
    }


    public function getAllRankByAnchor($p=1)
    {
        $p = ($p-1)*self::$page;

        $res = Db::name('activity_love_container')
            ->field('user_id, limit_energy, count(is_complete) num')
            ->where([['status', 'eq', 2], ['is_complete', 'eq', 1]])
            ->group('user_id')
            ->order('num desc')
            ->limit($p, self::$page)
            ->select();

        if (empty($res)) return [];

        $uids = array_column($res, 'user_id');

        $users = $this->getUsersInfo($uids);

        $rank = 1;

        $data = [];

        foreach ($res as &$value)
        {
            $user = [
                'rank' => $rank,
                'avatar' => $users[$value['user_id']]['avatar'],
                'nickname' => $users[$value['user_id']]['nickname'],
                'num' => $value['num'],
                'uri' => getJump('personal', ['user_id' => $value['user_id']]),
                'fans_group' => $this->getAnchorEnergyRank($value['user_id']),
            ];

            $rank++;

            array_push($data, $user);
        }

        return $data;
    }


    protected function getAnchorEnergyRank($anchor_id)
    {
        $rs = Db::name('activity_love_container')
            ->field('provider_id, id')
            ->where([['status', 'eq', 2], ['user_id', 'eq', $anchor_id]])
            ->order('create_time desc')
            ->limit(4)
            ->select();

        $uids = array_column($rs, 'provider_id');

        $users = $this->coreSdk->getUsers($uids, null, 'avatar');

        return $users;
    }




    //获取罐子的当前能量值
    protected function getContainerEnergy($anchor_id, $pot_id)
    {
        $support_key = self::$redis_key.self::$act_name.$anchor_id;

        //当前用户供养的表白罐增量
        $energy = $this->redis->zscore($support_key, $pot_id);

        return $energy/10;
    }


    //获取用户资料
    public function getUsersInfo($userIds, $visitor=null, $field='_all')
    {
        $arr = [];

        if (!is_array($userIds)) $userIds = [$userIds];

        $users = $this->coreSdk->getUsers($userIds, $visitor, $field);

        if (empty($users)) return $arr;

        foreach ($users as &$info)
        {
            isset($info['avatar']) && $info['avatar'] .= '?imageView2/1/w/50/h/50';

            $arr[$info['user_id']] = $info;
        }

        return $arr;
    }


    //获取用户供养的罐子状态与数据
    public function getUserPot($user_id)
    {
        //用户id对应的不同主播下的罐子
        $containers = Db::name('activity_love_container')
            ->field('user_id, id, limit_energy')
            ->where(['provider_id' => $user_id, 'status' => 2])
            ->select();

        if (empty($containers)) return [];

        $uids = array_column($containers, 'user_id');

        $anchor_info = $this->getUsersInfo($uids);

        $day = date('Ymd');

        foreach ($containers as &$pots)
        {
            $decay_key = self::$redis_key.self::$act_name.$day;
            $is_decay = $this->redis->zscore($decay_key, $pots['id']);
            $energy = $this->getContainerEnergy($pots['user_id'], $pots['id']);
            $pots['energy'] = $energy;
            $pots['nickname'] = $anchor_info[$pots['user_id']]['nickname'];
            $pots['surplus_energy'] = $pots['limit_energy'] - $energy;
            $pots['pot_image'] = $this->getUserPotImage($energy, $pots['limit_energy']);
            $pots['decay_energy'] = empty($is_decay) ? $energy/10 : 0;

            unset($pots['user_id'], $pots['limit_energy']);
        }

        return $containers;
    }


    //获取用户供养罐子不同状态的图标
    protected function getUserPotImage($energy, $limit_energy)
    {
        $uri = '/static/h5/images/activity/love_raise/';

        if (empty($energy)) return $uri.'pot.png';

        if ($energy >= $limit_energy) return $uri.'real_love_pot.png';

        $pot_image = ['pot_1.png', 'pot_2.png', 'pot_3.png', 'pot_4.png', 'pot_5.png', 'pot_6.png', 'pot_7.png', 'pot_8.png', 'pot_9.png'];

        $energy_key = (int)(ceil($energy/(round($limit_energy/9))));

        if (!isset($pot_image[$energy_key])) return $uri.'real_love_pot.png';

        return $uri.$pot_image[$energy_key];
    }


    //获取主播的罐子状态与数据
    public function getAnchorPot($user_id, $visitor=null)
    {
        //主播自已查看
        //用户查看

        $containers = Db::name('activity_love_container')->field('id, provider_id, status, user_id')->where('user_id', $user_id)->select();

        if (!empty($visitor))
        {
            $reply_info = Db::name('activity_love_container_reply')
                ->field('container_id, handle_status')
                ->where(['user_id' => $user_id, 'provider_id' => $visitor])
                ->select();

            if (!empty($reply_info)) $reply_info = array_column($reply_info, 'handle_status', 'container_id');
        }

        $list = [];

        foreach ($containers as $pots)
        {
            $tmp = [];

            $energy = $this->getContainerEnergy($pots['user_id'], $pots['id']);

            if (isset($reply_info))
            {
                if (array_key_exists($pots['id'], $reply_info) && $reply_info[$pots['id']] == 0)
                {
                    $pots['status'] = 1;
                }
                else if (in_array(1, $reply_info) && $pots['status'] != 2){
                    $pots['status'] = 3;
                }
            }

            switch ($pots['status'])
            {
                case 0:
                    $tmp['pot_data']['pot_name'] = '表白罐';
                    $tmp['pot_data']['pot_reply'] = '点击申请';
                    $tmp['pot_data']['pot_id'] = $pots['id'];
                    $tmp['pot_status'] = $pots['status'];
                    $tmp['pot_energy'] = $energy;
                    $tmp['user_info'] = [];
                    break;
                case 1:
                    $tmp['pot_data']['pot_name'] = '表白罐';
                    $tmp['pot_data']['pot_reply'] = '申请中...';
                    $tmp['pot_data']['pot_id'] = $pots['id'];
                    $tmp['pot_status'] = $pots['status'];
                    $tmp['pot_energy'] = $energy;
                    $tmp['user_info'] = [];
                    break;
                case 2:
                    $users = $this->getUsersInfo([$pots['provider_id']], $visitor, 'user_id, is_follow, avatar');
                    $users[$pots['provider_id']]['uri'] = getJump('personal', ['user_id' => $pots['provider_id']]);
                    $tmp['pot_data']['pot_name'] = $users[$pots['provider_id']]['avatar'];
                    $tmp['pot_data']['pot_reply'] = '供养中...';
                    $tmp['pot_data']['pot_id'] = $pots['id'];
                    $tmp['pot_status'] = $pots['status'];
                    $tmp['pot_energy'] = $energy;
                    $tmp['user_info'] = $users[$pots['provider_id']];
                    break;
                case 3:
                    $tmp['pot_data']['pot_name'] = '表白罐';
                    $tmp['pot_data']['pot_reply'] = '点击申请';
                    $tmp['pot_data']['pot_id'] = $pots['id'];
                    $tmp['pot_status'] = 3;
                    $tmp['pot_energy'] = $energy;
                    $tmp['user_info'] = [];
                    break;
            }

            array_push($list, $tmp);
        }

        return $list;
    }


    //检查用户与主播供养关系
    public function checkRaiseRelation($user_id, $anchor_id)
    {
        $exists = Db::name('activity_love_container')->where(['provider_id' => $user_id, 'user_id' => $anchor_id, 'status' => 2])->count();

        return boolval($exists);
    }


    //爱的供养-用户申请
    public function userReply($pot_id, $user_id)
    {
        $pots = Db::name('activity_love_container')->where(['id' => $pot_id])->find();

        if (empty($pots)) return make_error('白表罐错误');

        if ($pots['status'] == 2) return make_error('白表罐已被供养');

        if ($this->checkRaiseRelation($user_id, $pots['user_id'])) return make_error('您仅可申领该主播名下的一个表白罐');

        if ($user_id == $pots['user_id']) return make_error('主播身份不可以供养');

        $is_reply = Db::name('activity_love_container_reply')->where(['provider_id' => $user_id, 'user_id' => $pots['user_id'], 'handle_status'=>0])->find();

        if ($is_reply) return make_error('请勿重复申请');

        $pot_reply = Db::name('activity_love_container_reply')->where(['provider_id' => $user_id, 'container_id' => $pot_id])->find();

        if (!empty($pot_reply) && $pot_reply['handle_status'] == 0) return make_error('同一白表罐只能申请一次');

        $reply_log = [
            'provider_id' => $user_id,
            'user_id' => $pots['user_id'],
            'container_id' => $pot_id,
            'reply_time' => time(), //申请时间
            'handle_status' => 0, //初始化处理状态
            'handle_time' => 0, //处理时间
        ];

        $rs = Db::name('activity_love_container_reply')->insert($reply_log);

        if (!$rs) return make_error('申请出错,请重试');

        return [];
    }


    //爱的供养-主播处理
    public function anchorHandle($reply_id, $user_id, $type)
    {
        //主播可以同意或拒绝
        $reply_info = Db::name('activity_love_container_reply')->where(['id' => $reply_id, 'user_id' => $user_id, 'handle_status' => 0])->find();

        if (empty($reply_info)) return make_error('已建立供养~');

        if ($this->checkRaiseRelation($reply_info['provider_id'], $user_id)) return make_error('该用户已供养您的表白罐,不能供养更多');

        Db::startTrans();

        $handle_status = $type == 'agree' ? 1 : 2;

        //处理申请
        $reply_update = Db::name('activity_love_container_reply')
            ->where('id', $reply_id)
            ->update([
                'handle_status' => $handle_status,
                'handle_time' => time()
            ]);

        if (!$reply_update)
        {
            Db::rollback();

            return make_error('处理异常请重试[1]');
        }

        //只有同意才处理对这个罐子的状态变更
        if ($handle_status == 1)
        {
            $pot_update = Db::name('activity_love_container')->where(['id' => $reply_info['container_id']])->update([
                'provider_id' => $reply_info['provider_id'],
                'status' => 2
            ]);

            if (!$pot_update)
            {
                Db::rollback();

                return make_error('处理异常请重试[2]');
            }

            //将其它对这个罐子的申请全部拒绝
            $reply_update2 = Db::name('activity_love_container_reply')
                ->where([
                    ['container_id' , 'eq', $reply_info['container_id']],
                    ['id', 'neq', $reply_id]
                ])
                ->update([
                    'handle_status' => 2,
                    'handle_time' => time()
                ]);

            //将这个用户对这个主播的所有申请全部拒绝
            $reply_update3 = Db::name('activity_love_container_reply')
                ->where([
                    ['provider_id' , 'eq', $reply_info['provider_id']],
                    ['user_id' , 'eq', $reply_info['user_id']],
                    ['id', 'neq', $reply_id]
                ])
                ->update([
                    'handle_status' => 2,
                    'handle_time' => time()
                ]);

            if ($reply_update2 === false || $reply_update3 === false)
            {
                Db::rollback();

                return make_error('处理异常请重试[3]');
            }
        }

        Db::commit();

        return $reply_info['container_id'];
    }


    //爱的供养-主播供养榜
    public function getSupportRank($user_id, $visitor=null)
    {
        $containers = Db::name('activity_love_container')
            ->field('id, status, user_id, provider_id')
            ->where(['user_id' => $user_id, 'status' => 2])
            ->select();

        if (empty($containers)) return [];

        $uids = array_column($containers, 'provider_id');

        $user_info = $this->getUsersInfo($uids, $visitor, 'user_id, avatar, nickname, is_follow');

        foreach ($containers as $key => &$pots)
        {
            $pots['nickname'] = $user_info[$pots['provider_id']]['nickname'];
            $pots['avatar'] = $user_info[$pots['provider_id']]['avatar'];
            $pots['is_follow'] = $user_info[$pots['provider_id']]['is_follow'];
            $pots['uri'] = getJump('personal', ['user_id' => $pots['provider_id']]);
            $pots['energy'] = $this->getContainerEnergy($user_id, $pots['id']);
        }

        usort($containers, function ($a, $b) {

            if ($a['energy'] == $b['energy']) return 0;

            return ($a['energy'] > $b['energy']) ? -1 : 1;
        });

        return $containers;
    }


    //爱的供养-用户申请列表
    public function userReplyList($user_id)
    {
        $size = 20;

        $status_str = ['处理中', '已供养', '已拒绝', '已释放'];

        $reply_list = Db::name('activity_love_container_reply')
            ->field('handle_status, id, user_id')
            ->where('provider_id', $user_id)
            ->order('reply_time desc')
            ->paginate($size);

        $total = $reply_list->total();

        if (empty($total)) return ['list' => [], 'total' => 0];

        $data = $reply_list->items();

        $uids = array_column($data, 'user_id');

        $anchor_info = $this->getUsersInfo($uids, $user_id, 'user_id, nickname, avatar, is_follow');

        foreach ($data as &$replys)
        {
            $replys['avatar'] = $anchor_info[$replys['user_id']]['avatar'];
            $replys['nickname'] = $anchor_info[$replys['user_id']]['nickname'];
            $replys['is_follow'] = $anchor_info[$replys['user_id']]['is_follow'];
            $replys['status_str'] = $status_str[$replys['handle_status']];
        }

        return ['list' => $data, 'total' => ceil($total/$size)];
    }


    //爱的供养-主播审核列表
    public function anchorReviewList($user_id, $p=1)
    {
        $size = 20;

        $reply_list = Db::name('activity_love_container_reply')
            ->field('handle_status, id, provider_id, container_id')
            ->where('user_id', $user_id)
            ->order('reply_time desc')
            ->paginate($size);

        $total = $reply_list->total();

        if (empty($total)) return ['list' => [], 'total' => 0];

        $data = $reply_list->items();

        $uids = array_column($data, 'provider_id');

        $user_info = $this->getUsersInfo($uids, $user_id, 'user_id, nickname, avatar, is_follow');

        foreach ($data as &$replys)
        {
            $replys['avatar'] = $user_info[$replys['provider_id']]['avatar'];
            $replys['nickname'] = $user_info[$replys['provider_id']]['nickname'];
            $replys['is_follow'] = $user_info[$replys['provider_id']]['is_follow'];
        }

        return ['list' => $data, 'total' => ceil($total/$size)];
    }

}
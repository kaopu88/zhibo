<?php

namespace app\common\service;

use bxkj_common\RedisClient;
use bxkj_module\exception\ApiException;
use app\admin\service\SysConfig;
use think\Db;
use think\route\Rule;

//首冲获取礼物等公共类
class Firstinvest extends Service
{
    protected static $rechargePrefix = 'BG_RECHARGE:', $rechargeKey = 'recharge';
    protected $mehtod = ['1' => 'UserPackage', '2' => 'Bean', '3' => 'UserProps'];

    //获取价格区间
    public function getPrice($userId, $price)
    {
        $fristinvest_status = $this->redis->get('fristinvest_status');
        if (empty($fristinvest_status)) return 0;

        $isFirstBuy = $this->isFirstBuy($userId);
        if ($isFirstBuy) return 0;

        $invest = $this->redis->get(self::$rechargePrefix . self::$rechargeKey);
        if (empty($invest)) {
            $invest = Db::name('activity_invest')->where(['status' => '1'])->order(['price' => 'asc'])->select();
            $invest = json_encode($invest);
            $this->redis->setex(self::$rechargePrefix . self::$rechargeKey, 86400, $invest);
        }

        $invest = json_decode($invest, true);
        foreach ($invest as $key => $value) {
            if ($price >= $value['price']) $whichBuy = $key;
        }

        if (!is_numeric($whichBuy)) return;
        $giftList = $this->getGift($invest[$whichBuy]['id']);
        if (empty($giftList)) return;
        $bean = 0;
        foreach ($giftList as $k => $v) {
            $method = $this->mehtod[$v['type']];
            if (empty($method)) continue;
            if (!method_exists($this, 'reward' . $method)) continue;
            $data = call_user_func_array([$this, 'reward' . $method], [$v, $userId]);
            $bean = $data + $bean;
        }

        return $bean;
    }

    protected function rewardUserPackage($parmas, $userId = '')
    {
        $data = [
            'name' => $parmas['gift_name'],
            'icon' => $parmas['img'],
            'num' => $parmas['num'],
            'user_id' => $userId,
            'gift_id' => $parmas['giftid'],
            'access_method' => 'fistInvest',
            'create_time' => time(),
            'gift_type' => 1
        ];
        try {
            $res = Db::name('user_package')->insertGetId($data);
        } catch (ApiException $e) {
            return 0;
        }
        return 0;
    }

    protected function rewardUserProps($parmas, $userId = '')
    {
        $data = [
            'props_id' => $parmas['props'],
            'name' => $parmas['gift_name'],
            'num' => 1,
            'user_id' => $userId,
            'length' => $parmas['num'],
            'icon' => $parmas['img'],
            'create_time' => time(),
        ];
        try {
            $this->addUserProps($data);
        } catch (ApiException $e) {
            return 0;
        }
        return 0;
    }

    protected function rewardBean($params, $userId = '')
    {
        return $params['num'] ? $params['num'] : 0;
    }

    //获取活动对应的奖品
    private function getGift($activityid)
    {
        $giftList = Db::name('activity_invest_gift')->field('id,type,giftid,coin,carnum,giftnum,props,diamond_pic')->where(['status' => 1, 'activity_id' => $activityid])->order(['sort' => 'desc'])->select();
        foreach ($giftList as $key => $value) {
            switch ($value['type']) {
                case 1://礼物类型
                    $giftInfo = Db::name('gift')->where(['id' => $value['giftid'], 'status' => '1'])->find();
                    $giftList[$key]['img'] = $giftInfo['picture_url'];
                    $giftList[$key]['gift_name'] = $giftInfo['name'];
                    $giftList[$key]['num'] = $value['giftnum'];
                    break;
                case 2://钻石类型
                    $giftList[$key]['img'] = $value['diamond_pic'];
                    $giftList[$key]['gift_name'] = '钻石';
                    $giftList[$key]['num'] = $value['coin'];
                    break;
                case 3://座驾
                    $props = Db::name('props')->where(['status' => 1])->field('name, cover_icon as icon, id, describe')->find();
                    $giftList[$key]['img'] = $props['icon'];
                    $giftList[$key]['gift_name'] = $props['name'];
                    $giftList[$key]['num'] = $value['carnum'];
                    break;
            }
        }
        return $giftList;
    }

    //获取当前买单者是否第一次充值
    private function isFirstBuy($userId)
    {
        $num = Db::name('recharge_order')->where(['user_id' => $userId, 'pay_status' => '1'])->count();
        return $num?: 0;
    }

    //新增用户道具
    public function addUserProps($data)
    {
        $props = Db::name('user_props')->where(['props_id' => $data['props_id'], 'user_id' => $data['user_id']])->find();
        $now = time();
        $expire_time = $now + $data['length'] * 86400;
        unset($data['length']);
        if (empty($props)) {
            $data['expire_time'] = $expire_time;
            try {
                $res = Db::name('user_props')->insert($data);
            } catch (ApiException $e) {
                return false;
            }
        } else {
            if ($props['expire_time'] < $now) $props['expire_time'] = $now;
            $expire_time = $props['expire_time'] + ($expire_time - $now);
            $res = Db::name('user_props')->where(['id' => $props['id']])->update([
                'num' => Db::raw("`num` + 1"),
                'expire_time' => $expire_time,
                'status' => 1,
                'update_time' => $data['create_time']
            ]);
        }
        return $res;
    }
}
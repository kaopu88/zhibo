<?php

namespace app\api\controller;
use app\common\controller\UserController;
use app\admin\service\SysConfig;
use bxkj_module\exception\ApiException;
use think\Db;

class Firstinvest extends UserController
{
    public function __construct()
    {
        parent::__construct();
        $first_status = config('fristinvest.is_open') ? config('fristinvest.is_open') : '0';
        if (empty($first_status)) {
            $errorMsg = '';
            throw new ApiException((string)$errorMsg, 1);
        }
    }

    //获取首充有礼初始化信息
    public function investInfo()
    {
        $ser = new SysConfig();
        $info = $ser->getConfig("fristinvest");
        $investList = Db::name('activity_invest')->field('id,title,price')->where(['status' => 1])->order(['sort' => 'desc'])->select();
        foreach ($investList as $key => $value) {
            $investList[$key]['gift'] = $this->getGift($value['id']);
        }
        return $this->success(['info' => json_decode($info['value']), 'data' => $investList]);
    }



    //获取当前买单者是否第一次充值
    public function isFirstBuy()
    {
        $num = Db::name('recharge_order')->where(
            array(
                'user_id' => USERID,
                'pay_status' => '1'
            ))->count();
        return $this->success($num);
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

    //新增用户道具
    public function addUserProps($data)
    {
        $props = Db::name('user_props')->where(['props_id' => $data['props_id'], 'user_id' => $data['user_id']])->find();
        $expire_time = mktime(23, 59, 59, date('m'), date('d') + $data['length'], date('y'));
        unset($data['length']);
        if (empty($props)) {
            $data['expire_time'] = $expire_time;
            try {
                $res = Db::name('user_props')->insert($data);
            } catch (ApiException $e) {
                return false;
            }
        } else {
            if ($props['expire_time'] < time()) {
                $props['expire_time'] = time();
            }
            $expire_time = $props['expire_time'] + ($expire_time - time());
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
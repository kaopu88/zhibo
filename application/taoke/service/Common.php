<?php
/**
 * Created by PhpStorm.
 * User: 李成
 * Date: 2020/7/4
 * Time: 8:46
 */
namespace app\taoke\service;

use app\admin\service\SysConfig;
use app\admin\service\User;
use bxkj_module\service\Service;
use think\Db;

class Common extends Service
{
    /**
     * 获取平台手续费
     * @param int $type 1：淘宝；2：拼多多；3：京东；
     * @return array
     */
    public function getServiceFeeRate($type=1)
    {
        $resutl = [];
        $config = new SysConfig();
        $distriConfig = $config->getConfig("distribute");
        $distriConfig = json_decode($distriConfig['value'], true);
        switch ($type) {
            case 'B':
            case 'C':
                $rate = $distriConfig['taobao_substr_rate'] ? (100 - $distriConfig['taobao_substr_rate']) / 100 : 1;
                break;
            case 'P':
                $rate = $distriConfig['pdd_substr_rate'] ? (100 - $distriConfig['pdd_substr_rate']) / 100 : 1;
                break;
            case 'J':
                $rate = $distriConfig['jd_substr_rate'] ? (100 - $distriConfig['jd_substr_rate']) / 100 : 1;
                break;
            default:
                $rate = $distriConfig['taobao_substr_rate'] ? (100 - $distriConfig['taobao_substr_rate']) / 100 : 1;
                break;
        }
        $resutl['rate'] = $rate;
        $resutl['normal'] = empty($distriConfig['normal_rate']) ? 0 : $distriConfig['normal_rate'];
        return $resutl;
    }

    /**
     * 用户自购佣金
     * @param $commission
     * @param int $type
     * @param int $userId
     * @return float|int
     */
    public function getPurchaseCommission($commission, $type=1, $userId=0)
    {
        $money = 0;
        if($userId){
            $rateSetting = $this->getServiceFeeRate($type);
            $rate = $rateSetting['rate'];
            $normalRate = $rateSetting['normal'];//普通用户佣金比率

            $promotionRate = 0;
            $user = new User();
            $userInfo = $user->getUserInfo(["user_id" => $userId]);
            if($userInfo['taoke_level'] != 0){
                $level = new Level();
                $levelInfo = $level->getLevelInfo(["id" => $userInfo['taoke_level']]);
                if($levelInfo['purchase']){
                    $promotionRate = $levelInfo['purchase'] * $rate;//用户对应等级自购佣金
                }
            }else{
                $promotionRate = $normalRate * $rate;//普通用户的佣率（扣除手续费等）
            }
            $money = round($commission * $promotionRate / 100, 2);//普通用户佣金
        }
        return $money;
    }

    /**
     * 获取下一等级的佣金，若无则返回当前等级的佣金即达到最高等级
     * @param $commission
     * @param int $type
     * @param int $userId
     * @return float|int
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getUpCommission($commission, $type=1, $userId=0)
    {
        $money = 0;
        if($userId){
            $rateSetting = $this->getServiceFeeRate($type);
            $rate = $rateSetting['rate'];
            $normalRate = $rateSetting['normal'];//普通用户佣金比率

            $promotionRate = 0;
            $user = new User();
            $userInfo = $user->getUserInfo(["user_id" => $userId]);
            if($userInfo['taoke_level'] != 0){
                $levelList = Db::name("taoke_level")->where("id >".$userInfo['taoke_level'])->where(["status" => 1])->order("level ASC")->select();
                if($levelList){
                    $levelInfo = $levelList[0];//下一等级
                }else{
                    $level = new Level();
                    $levelInfo = $level->getLevelInfo(["id" => $userInfo['taoke_level']]);//当前等级
                }
                if($levelInfo['purchase']){
                    $promotionRate = $levelInfo['purchase'] * $rate;//自购佣金比率
                }
            }else{
                $promotionRate = $normalRate * $rate;//普通用户的佣率（扣除手续费等）
            }
            $money = round($commission * $promotionRate / 100, 2);//普通用户佣金
        }
        return $money;
    }

    /**
     * 获取用户最高等级佣金
     * @param $commission
     * @param int $type
     * @param int $userId
     * @return float|int
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getHighCommission($commission, $type=1, $userId=0)
    {
        $money = 0;
        if($userId){
            $rateSetting = $this->getServiceFeeRate($type);
            $rate = $rateSetting['rate'];

            $promotionRate = 0;
            $user = new User();
            $userInfo = $user->getUserInfo(["user_id" => $userId]);
            $levelList = Db::name("taoke_level")->where("id >".$userInfo['taoke_level'])->where(["status" => 1])->order("level ASC")->select();
            if($levelList){//有高等级
                $levelInfo = $levelList[0];//最高等级
                if($levelInfo['purchase']){
                    $promotionRate = $levelInfo['purchase'] * $rate;//自购佣金比率
                }
            }else{//没有高等级
                if($userInfo['taoke_level'] != 0) {//当前等级
                    $level = new Level();
                    $levelInfo = $level->getLevelInfo(["id" => $userInfo['taoke_level']]);
                    if($levelInfo['purchase']){
                        $promotionRate = $levelInfo['purchase'] * $rate;
                    }
                }else{//普通用户
                    $normalRate = config('distribute.normal_rate');
                    $promotionRate = $normalRate * $rate;
                }
            }
            $money = round($commission * $promotionRate / 100, 2);
        }
        return $money;
    }
}
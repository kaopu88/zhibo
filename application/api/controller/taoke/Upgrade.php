<?php
/**
 * Created by PhpStorm.
 * User: 李成
 * Date: 2020/5/11
 * Time: 10:25
 */
namespace app\api\controller\taoke;

use app\taoke\service\Upgrade as upgradeModel;
use app\admin\service\User;
use app\common\controller\UserController;
use app\common\service\UpgradeLog;
use think\Db;

class Upgrade extends UserController
{
    /**
     * 判断用户是否可升级
     * @param $userId
     * @param $taokeLevel
     * @param $type
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function upgradeLevel()
    {
        $status = true;//是否满足升级条件

        $userId = USERID;
        $userInfo = $this->user;
        $where = [
            ['status', '=', 1],
            ['id', '>', $userInfo['taoke_level']],
        ];
        $levelList = Db::name("taoke_level")->where($where)->order("id ASC")->select();
        if (empty($levelList)) {//没有可升级的等级
            return $this->jsonError("升级失败，已达到最高等级");
        }
        $nextLevel = $levelList[0];
        $deep = $nextLevel['promotion_level'];//升级下一等级 需要的推广 一、二、三级别的条件

        $upgrade = new UpgradeLog();
        $upgradeLog = $upgrade->getLogInfo(["user_id" => $userId, "level" => $nextLevel['id'], "type" => "taoke"]);//升级记录
        if ($nextLevel['upgrade_type'] == 1 && $upgradeLog) {//需要后台手动审核
            return $this->jsonError("升级申请已提交，请勿重复申请");
        }

        $conditionArr = [];
        if($nextLevel['upgrade_condition']) {
            $upgradeCondition = json_decode($nextLevel['upgrade_condition'], true);
            foreach ($upgradeCondition as $value) {
                if ($value == "order") {
                    $conditionArr['order'] = $nextLevel['order_condition'];
                    $orderCondition = json_decode($nextLevel['order_condition'], true);
                    if (!$this->orderCondition($userId, $orderCondition, $deep)) {
                        $status = false;
                    }

                } elseif ($value == "commission") {
                    $conditionArr['commission'] = $nextLevel['commission_condition'];
                    $commissionCondition = json_decode($nextLevel['commission_condition'], true);
                    if (!$this->commissionCondition($userId, $commissionCondition, $deep)) {
                        $status = false;
                    }

                } elseif ($value == "people") {
                    $conditionArr['people'] = $nextLevel['people_condition'];
                    $peopleCondition = json_decode($nextLevel['people_condition'], true);
                    if (!$this->peopleCondition($userId, $peopleCondition, $deep)) {
                        $status = false;
                    }

                }
            }
        }

        if($status){//升级条件已满足
            $data['user_id'] = $userId;
            $data['type'] = "taoke";
            $data['level'] = $nextLevel['id'];
            $data['add_time'] = time();
            if($conditionArr) {//有升级条件
                $data['upgrade_condition'] = json_encode($conditionArr, true);
                if ($nextLevel['upgrade_type'] == 0) {
                    $data['status'] = 1;
                }
            }
            $result = $upgrade->addLog($data);
            if($result > 0 && $nextLevel['upgrade_type'] == 0){
                $user = new User();
                $status = $user->updateData($userId, ["taoke_level" => $nextLevel['id']]);
                if($status){
                    return $this->jsonSuccess("", "升级成功");
                }
            }
        }
        return $this->jsonError("升级失败");
    }

    /**
     * 升级条件--人数
     * @param $userId
     * @param $condtionArr
     * @param $level
     * @return bool
     */
    public function peopleCondition($userId, $condtionArr, $level)
    {
        $upStatus = true;
        $teamCondition = $condtionArr['team'];//升级团队需要人数

        $upgrade = new upgradeModel();
        $peopleProgress = $upgrade->getPeopleProgress($userId, $level);

        if($teamCondition > 0) {
            if ($teamCondition >= $peopleProgress['fans_team_num']) {//团队需要人数
                $upStatus = false;
            }
        }

        $everyLevelNeedPeople = $condtionArr['distri'];//各个分销层级需要的人数
        for ($i=0; $i<$level; $i++){
            if($everyLevelNeedPeople[$i] > 0) {
                if ($everyLevelNeedPeople[$i] > $peopleProgress['fans_level_num'][$i]) {
                    $upStatus = false;
                }
            }
        }
        return $upStatus;
    }

    /**
     * 升级条件--订单
     * @param $userId
     * @param $condtionArr
     * @param $level
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function orderCondition($userId, $condtionArr, $level)
    {
        $upStatus = true;

        $upgrade = new upgradeModel();
        $orderProgress = $upgrade->getOrderProcess($userId, $level);

        $orderNumSelfCondition = $condtionArr['order_num']['self'];//自购订单数
        $orderNumTeamCondition = $condtionArr['order_num']['team'];//团队订单数
        if($orderNumSelfCondition > 0) {//自购订单数
            if($orderNumSelfCondition > $orderProgress['order_num_self']){
                $upStatus = false;
            }
        }

        if($orderNumTeamCondition > 0) {//团队订单数
            if ($orderNumSelfCondition > $orderProgress['order_num_team']) {
                $upStatus = false;
            }
        }

        $orderPriceSelfCondition = $condtionArr['order_money']['self'];//自购订单付款金额
        $orderPriceTeamCondition = $condtionArr['order_money']['team'];//团队订单付款金额
        if($orderPriceSelfCondition > 0) {//自购订单付款金额
            if($orderPriceSelfCondition > $orderProgress['order_price_self']){
                $upStatus = false;
            }
        }

        if($orderPriceTeamCondition > 0) {//团队订单付款金额
            if($orderPriceTeamCondition > $orderProgress['order_price_team']){
                $upStatus = false;
            }
        }

        $fansNeedOrder = $condtionArr['order_num']['distri'];//各个分销层级需要的订单数
        $fansNeedOrderPrice = $condtionArr['order_money']['distri'];//各个分销层级需要的订单付款金额
        for ($i=0; $i<$level; $i++){
            if($fansNeedOrder[$i] > 0) {//对应层级粉丝的订单数条件
                if ($fansNeedOrder[$i] > $orderProgress['order_num_fans'][$i]) {
                    $upStatus = false;
                }
            }

            if($fansNeedOrderPrice[$i] > 0) {//对应层级粉丝的订单付款金额
                if ($fansNeedOrderPrice[$i] > $orderProgress['orderPriceFans'][$i]) {
                    $upStatus = false;
                }
            }
        }

        return $upStatus;
    }

    /**
     * 升级条件--佣金
     * @param $userId
     * @param $condtionArr
     * @param $level
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function commissionCondition($userId, $condtionArr, $level)
    {
        $upStatus = true;
        $upgrade = new upgradeModel();
        $commissionProgress = $upgrade->getCommissionProgress($userId, $level);

        $commiSelfCondition = $condtionArr['self'];//自购佣金
        $commiTeamCondition = $condtionArr['team'];//团队佣金

        if($commiSelfCondition > 0) {//自购佣金
            if ($commiSelfCondition > $commissionProgress['commission_self']) {
                $upStatus = false;
            }
        }

        if($commiTeamCondition > 0) {//团队佣金
            if($commiTeamCondition > $commissionProgress['commission_team']){
                $upStatus = false;
            }
        }

        $fansNeedCommission = $condtionArr['distri'];//各个分销层级需要的佣金
        for ($i=0; $i<$level; $i++){
            if($fansNeedCommission[$i] > 0) {//对应层级粉丝的佣金条件
                if ($fansNeedCommission[$i] > $commissionProgress['commission_fans'][$i]) {
                    $upStatus = false;
                }
            }
        }

        return $upStatus;
    }

    /**
     * 获取用户升级到下一级别的状态
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getUpgradeCondition()
    {
        $result = [];
        $userInfo = $this->user;
        $userId = USERID;
        $where = [
            ['status', '=', 1],
            ['id', '>', $userInfo['taoke_level']],
        ];
        $levelList = Db::name("taoke_level")->where($where)->order("id ASC")->select();
        if (empty($levelList)) {//没有可升级的等级
            return $this->jsonError("已达到最高等级");
        }
        $nextLevel = $levelList[0];
        $deep = $nextLevel['promotion_level'];//升级下一等级 需要的推广 一、二、三级别的条件
        $result['id'] = $nextLevel['id'];
        $result['name'] = $nextLevel['name'];
        $result['img'] = $nextLevel['img'];
        $result['desc'] = $nextLevel['desc'];
        $result['upgrade_type'] = $nextLevel['upgrade_type'];

        if($nextLevel['upgrade_condition']) {
            $data = [];
            $upgrade = new upgradeModel();
            $upgradeCondition = json_decode($nextLevel['upgrade_condition'], true);
            foreach ($upgradeCondition as $value) {
                if ($value == "order") {
                    $orderCondition  = json_decode($nextLevel['order_condition'], true);
                    $data['order']['codition']['order_num_self'] = $orderCondition['order_num']['self'];
                    $data['order']['codition']['order_num_team'] = $orderCondition['order_num']['team'];
                    $data['order']['codition']['order_price_self'] = $orderCondition['order_money']['self'];
                    $data['order']['codition']['order_price_team'] = $orderCondition['order_money']['team'];
                    $data['order']['codition']['order_num_fans'] = $orderCondition['order_num']['distri'];
                    $data['order']['codition']['order_price_fans'] = $orderCondition['order_money']['distri'];
                    $data['order']['complete'] = $upgrade->getOrderProcess($userId, $deep);

                } elseif ($value == "commission") {
                    $commiCondition = json_decode($nextLevel['commission_condition'], true);
                    $data['commission']['codition']['commission_self'] = $commiCondition['self'];
                    $data['commission']['codition']['commission_team'] = $commiCondition['team'];
                    $data['commission']['codition']['commission_fans'] = $commiCondition['distri'];
                    $data['commission']['complete'] = $upgrade->getCommissionProgress($userId, $deep);

                } elseif ($value == "people") {
                    $peopleCondition = json_decode($nextLevel['people_condition'], true);
                    $data['people']['codition']['fans_team_num'] = $peopleCondition['team'];
                    $data['people']['codition']['fans_level_num'] = $peopleCondition['distri'];
                    $data['people']['complete'] = $upgrade->getPeopleProgress($userId, $deep);

                }
            }
            $result['progress'] = $data;
        }

        return $this->jsonSuccess($result, "获取成功");
    }
}
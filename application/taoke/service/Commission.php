<?php
/**
 * Created by PhpStorm.
 * User: 李成
 * Date: 2020/5/11
 * Time: 14:40
 */
namespace app\taoke\service;

use app\admin\service\SysConfig;
use app\admin\service\User;
use bxkj_module\service\Service;
use think\Db;

class Commission extends Service
{
    protected $commissionArray = [];

    protected $rate = 100;

    /**
     * 计算预估
     * @param $orderValue
     * @return bool
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function getEstiCommission($orderValue)
    {
        $type = '1';
        if ($orderValue['commission']) {
            $orderValue['commission'] = $orderValue['commission'] * 10000;
        }
        if ($orderValue['shop_type'] == '拼多多') {
            $type = '2';
        }
        if ($orderValue['shop_type'] == '京东') {
            $type = '3';
        }

        $orderCount = Db::name('estimate_commission_log')->where(['goods_order' => $orderValue['goods_order'], 'goods_sonorder' => $orderValue['goods_sonorder']])->select();
        if (isset($orderCount[0])) {
            if (isset($orderValue['goods_sonorder']) && $orderValue['goods_sonorder'] && !$orderCount[0]['goods_sonorder']) {
                Db::name('estimate_commission_log')->where('id', $orderCount[0]['id'])->update(['goods_sonorder' => $orderValue['goods_sonorder']]);
            }
            return false;
        }

        if (isset($orderValue['user_id']) && $orderValue['user_id']) {
            $distriArr = $this->calCommission($orderValue, $orderValue['user_id'], 0, 0, 0, 0, $type);
            if ($distriArr) {
                $createTime = strtotime(date('Y-m', $orderValue['create_time']));//当月时间

                foreach ($distriArr as $key => $value) {
                    $commissionLog = new CommissionLog();
                    $log = $commissionLog->getLogInfo(['user_id' => $value['user_id'], 'create_time' => $createTime, 'type' => 0]);
                    if ($log) {
                        $predict_money = $value['money'] + $log['predict_money'];
                        $commissionLog->updateLogInfo(['id' => $log['id']], ['predict_money' => $predict_money]);
                    } else {
                        $data = [];
                        $data['user_id'] = $value['user_id'];
                        $data['create_time'] = $createTime;
                        $data['predict_money'] = $value['money'];
                        $commissionLog->addLog($data);
                    }

                    $money = round($value['money'] / 10000, 2);
                    if ($money > 0) {
                        $extra = [
                            'orderId' => $orderValue['goods_order'],
                            'goods_name' => $orderValue['title'],
                            'orderType' => $orderValue['shop_type'],
                            'orderPrice' => round($orderValue['pay_price'], 2),
                            'level' => $value['level'],
                            'commission' => $money,
                            'name' => $value['username'],
                            'fansName' => $value['nickname'],
                            'time' => $orderValue['create_time']
                        ];
                        $push = new Push();
                        if ($orderValue['user_id'] == $value['user_id']) {
                            $push->send('buy', $value['user_id'], $extra);
                        } else {
                            $push->send('fansBuy', $value['user_id'], $extra);
                        }
                    }
                }
                $datae['goods_order'] = $orderValue['goods_order'];
                $datae['goods_sonorder'] = $orderValue['goods_sonorder'];
                $datae['value'] = json_encode(fix_number_precision($distriArr, 2), true);
                $datae['type'] = '0';
                $datae['add_time'] = time();
                $estimate = new Estimate();
                $estimate->addLog($datae);

                $this->setMoney($orderValue['commission'], $createTime, '1');

                return true;
            } else {
                return false;
            }

        } else {
            return false;
        }
    }

    /**
     * 结算
     * @param $orderValue
     * @return bool
     */
    public function getSettleCommission($orderValue)
    {
        $type = '1';
        if ($orderValue['commission']) {
            $orderValue['commission'] = $orderValue['commission'] * 10000;
        }
        if ($orderValue['shop_type'] == '拼多多') {
            $type = '2';
        }
        if ($orderValue['shop_type'] == '京东') {
            $type = '3';
        }
        if (isset($orderValue['user_id']) && $orderValue['user_id']) {
            $distriArr = $this->calCommission($orderValue, $orderValue['user_id'], 0, 0, 0, 0, $type);
            if ($distriArr) {
                $createTime = strtotime(date('Y-m', $orderValue['create_time']));//当月时间

                $commissionLog = new CommissionLog();
                foreach ($distriArr as $key => $value) {
                    $money = 0;

                    $log = $commissionLog->getLogInfo(["user_id" => $value['user_id'], "create_time" => $createTime, "type" => 0]);
                    $money = $value['money'];
                    if ($money > 0) {
                        if ($log) {
                            $rebateMoney = $log['rebate_money'] + $money;
                            $settlementPay = $log['settlement_pay'] + $money;

                            $settlementPrice = $money + $log['settlement_price'];
                            //把已提现的钱累加进入记录
                            $commissionLog->updateLogInfo(["id" => $log['id']], ['rebate_money' => $rebateMoney, 'settlement_pay' => $settlementPay, 'settlement_price' => $settlementPrice]);
                            //进行返利
                            $this->addIncomeLog($value['user_id'], round($money / 10000, 2), '1');

                        } else {
                            $datas = [];
                            $datas['user_id'] = $value['user_id'];
                            $datas['create_time'] = $createTime;
                            $datas['settlement_price'] = $money;
                            //把已提现的钱累加进入记录
                            $datas['rebate_money'] = $money;
                            $datas['settlement_pay'] = $money;
                            //进行返利
                            $this->addIncomeLog($value['user_id'], round($money / 10000, 2), '1');
                            $commissionLog->addLog($datas);
                        }
                        $this->setMoney($money, $createTime, '3', '1');
                    }

                    $teamCommission = $value['team_money'];
                    if ($teamCommission) {
                        if ($log) {
                            //把已提现的钱累加进入记录
                            $rebateMoney = $log['rebate_money'] + $teamCommission;
                            $teamPay = $log['team_pay'] + $teamCommission;
                            $teamIncome = $teamCommission + $log['team_income'];
                            //把已提现的钱累加进入记录
                            $commissionLog->updateLogInfo(["id" => $log['id']], ['rebate_money' => $rebateMoney, 'team_pay' => $teamPay, 'team_income' => $teamIncome]);
                            //进行返利
                            $this->addIncomeLog($value['user_id'], round($teamCommission / 10000, 2), '2');

                        } else {
                            $datas = [];
                            $datas['user_id'] = $value['user_id'];
                            $datas['create_time'] = $createTime;
                            $datas['team_income'] = $teamCommission;
                            //把已提现的钱累加进入记录
                            $datas['rebate_money'] = $teamCommission;
                            $datas['team_pay'] = $teamCommission;
                            //进行返利
                            $this->addIncomeLog($value['user_id'], round($teamCommission / 10000, 2), '2');
                            $commissionLog->addLog($datas);
                        }
                        $this->setMoney($teamCommission, $createTime, '3', '2');
                    }

                    //订单平级分佣记录
                    $levelCommission = $value['level_money'];
                    if ($levelCommission) {
                        if ($log) {
                            //判断返利状态 为随时提现
                            $rebate_money = $log['rebate_money'] + $levelCommission;
                            $levelPay = $log['level_pay'] + $levelCommission;
                            $levelIncome = $levelCommission + $log['level_income'];
                            //把已提现的钱累加进入记录
                            $commissionLog->updateLogInfo(["id" => $log['id']], ['rebate_money' => $rebate_money, 'level_pay' => $levelPay, 'level_income' => $levelIncome]);
                            //进行返利
                            $this->addIncomeLog($value['user_id'], round($levelCommission / 10000, 2), '3');
                        } else {
                            $datas = [];
                            $datas['user_id'] = $value['user_id'];
                            $datas['create_time'] = $createTime;
                            $datas['level_income'] = $levelCommission;
                            //把已提现的钱累加进入记录
                            $datas['rebate_money'] = $levelCommission;
                            $datas['level_pay'] = $levelCommission;
                            //进行返利
                            $this->addIncomeLog($value['user_id'], round($levelCommission / 10000, 2), '3');
                            $commissionLog->addLog($datas);
                        }
                        $this->setMoney($levelCommission, $createTime, '3', '3');
                    }

                }

                $datae['goods_order'] = $orderValue['goods_order'];
                $datae['goods_sonorder'] = $orderValue['goods_sonorder'];
                $datae['value'] = json_encode(fix_number_precision($distriArr, 2), true);
                $datae['type'] = '0';
                $datae['add_time'] = time();
                $rebate = new Rebate();
                $rebate->addLog($datae);

                $this->setMoney($orderValue['commission'], $createTime, '2');

                return true;
            } else {
                return false;
            }

        } else {
            return false;
        }
    }

    /**
     * 用户当月收益汇总
     * @param $money
     * @param $time
     * @param string $type
     * @param string $static
     */
    public function setMoney($money, $time, $type = '1', $static = '0')
    {
        if ($money) {
            $commissionLog = new CommissionLog();
            $log = $commissionLog->getLogInfo(['user_id' => '999999', 'type' => '1', 'create_time' => $time]);
            $where = ['id' => $log['id']];
            if ($commissionLog) {
                //累加当月的结算收益
                switch ($type) {
                    case '1':
                        $predictMoney = $log['predict_money'] + $money;
                        $commissionLog->updateLogInfo($where, ['predict_money' => $predictMoney]);
                        break;
                    case '2':
                        $settlementPrice = $log['settlement_price'] + $money;
                        $commissionLog->updateLogInfo($where, ['settlement_price' => $settlementPrice]);
                        break;
                    case '3':
                        $rebateMoney = $log['rebate_money'] + $money;
                        $commissionLog->updateLogInfo($where, ['rebate_money' => $rebateMoney]);
                        break;
                }
                switch ($static) {
                    case '1':
                        $settlementPay = $log['settlement_pay'] + $money;
                        $commissionLog->updateLogInfo($where, ['settlement_pay' => $settlementPay]);
                        break;
                    case '2':
                        $teamPay = $log['team_pay'] + $money;
                        $commissionLog->updateLogInfo($where, ['team_pay' => $teamPay]);
                        break;
                    case '3':
                        $levelPay = $log['level_pay'] + $money;
                        $commissionLog->updateLogInfo($where, ['level_pay' => $levelPay]);
                        break;
                }
            } else {
                $data['user_id'] = '999999';
                $data['create_time'] = $time;
                switch ($type) {
                    case '1':
                        $data['predict_money'] = $money;
                        break;
                    case '2':
                        $data['settlement_price'] = $money;
                        break;
                    case '3':
                        $data['rebate_money'] = $money;
                        break;
                }
                switch ($static) {
                    case '1':
                        $data['settlement_pay'] = $money;
                        break;
                    case '2':
                        $data['team_pay'] = $money;
                        break;
                    case '3':
                        $data['level_pay'] = $money;
                        break;
                }
                $data['type'] = '1';
                $commissionLog->addLog($data);
            }
        }
    }

    /**
     * 用户淘客收益明细记录
     * @param $userId
     * @param $money
     * @param string $type
     * @param string $static
     */
    public function addIncomeLog($userId, $money, $type = '1', $static = '')
    {
        $userService = new User();
        $user = $userService->getInfo($userId);
        $userMoney = 0;
        if ($userId && $money && $user) {
            if ($static == '1') {
                $userMoney = $user['taoke_money'] - $money;
            } else {
                $userMoney = $user['taoke_money'] + $money;
            }
            $bool = $userService->updateData($userId, ['taoke_money' => $userMoney]);
            if ($bool) {
                switch ($type) {
                    case '1':
                        $name = 'orderIncome';
                        break;
                    case '2':
                        $name = 'teamIncome';
                        break;
                    case '3':
                        $name = 'levelIncome';
                        break;
                    case '4':
                        $name = 'rightsPay';
                        break;
                    case '5':
                        $name = 'duomaiIncome';
                        break;
                    default:
                        $name = 'orderIncome';
                        break;
                }
                $income = new IncomeLog();
                $income->addLog(['user_id' => $userId, 'name' => $name, 'money' => $money, 'create_time' => time()]);
            }
        }
    }

    /**
     * 计算三级分销分佣
     * @param $orderInfo
     * @param string $uid
     * @param string $level
     * @param string $fpid
     * @param string $tuid
     * @param string $teamRate
     * @param string $type
     * @return array
     */
    public function calCommission($orderInfo, $uid = '0', $level = '0', $fpid = '0', $tuid = '0', $teamRate = '0', $type = '1')
    {
        if ($level >= 4) {
            return $this->commissionArray;
        }
        $user = new User();
        $userInfo = $user->getBasicInfo($uid);
        $fuserInfo = $user->getBasicInfo($fpid);//下级用户的用户信息
        $sysConfig = new SysConfig();
        $disConfig = $sysConfig->getConfig("distribute");
        $disConfig = json_decode($disConfig["value"], true);
        $remainRate = $disConfig["retain_rate"];//平台保留比率
        $normalRate = $disConfig["normal_rate"];//注册用户自购比率

        $status = true;
        $money = 0;//用户得到的佣金

        switch ($type) {
            case '1':
                //扣除淘宝扣减比例
                $deductRate = $disConfig['taobao_substr_rate'] ? (100 - $disConfig['taobao_substr_rate']) / 100 : 1;
                break;
            case '2':
                //扣除拼多多扣减比例
                $deductRate = $disConfig['pdd_substr_rate'] ? (100 - $disConfig['pdd_substr_rate']) / 100 : 1;
                break;
            case '3':
                //扣除京东扣减比例
                $deductRate = $disConfig['jd_substr_rate'] ? (100 - $disConfig['jd_substr_rate']) / 100 : 1;
                break;
        }
        $commission = round($orderInfo['commission'] * $deductRate, 2);//扣除比例后的剩余佣金
        $taokeLevel = new Level();
        if ($userInfo['taoke_level'] != 0) {
            $levelInfo = $taokeLevel->getLevelInfo(["id" => $userInfo['taoke_level']]);//会员对应等级信息
            if ($level == 0) {//自购
                $promotionRate = $levelInfo['purchase'];
            } else if ($level <= 3) {//三级分销
                $promotionArr = json_decode($levelInfo['promotion'], true);
                $promotionRate = $promotionArr[$level - 1];
            }

            $leftRate = $this->rate - $promotionRate;//计算剩余比例
            if ($leftRate < $remainRate) { //剩余佣金比例小于设置保留的比例
                $promotionRate = $this->rate - $remainRate;
                if ($promotionRate <= 0) {
                    $promotionRate = 0;
                }
                $this->rate = $this->rate - $promotionRate;
                $status = false;
            } else {
                $this->rate = $leftRate;
            }
            $money = round($commission * $promotionRate / 100, 2);//多级分销

            $moneys = 0;
            if ($levelInfo['team_reward'] > 0) {
                if ($tuid == 0) {
                    $tuid = $userInfo['user_id'];
                    $promotionRate = $levelInfo['team_reward'];
                    $teamRate = $levelInfo['team_reward'];
                } else {
                    $tuserInfo = $user->getBasicInfo($tuid);//下级中最高等级的用户信息
                    $tlevelInfo = $taokeLevel->getLevelInfo(["id" => $tuserInfo['taoke_level']]);//下级中最高等级用户的等级信息
                    if ($levelInfo['level'] > $tlevelInfo['level']) {
                        if ($teamRate > 0 && $levelInfo['team_reward'] > $teamRate) {
                            $promotionRate = $levelInfo['team_reward'] - $teamRate;
                            $teamRate += $promotionRate;
                            $tuid = $userInfo['user_id'];
                        }
                    }
                }
                $leftRate = $this->rate - $promotionRate;//计算剩余比例
                if ($leftRate < $remainRate) { //剩余佣金比例小于设置保留的比例
                    $promotionRate = $this->rate - $remainRate;
                    if ($promotionRate <= 0) {
                        $promotionRate = 0;
                    }
                    $this->rate = $this->rate - $promotionRate;
                    $status = false;
                } else {
                    $this->rate = $leftRate;
                }
                $moneys = round($commission * $promotionRate / 100, 2);
            }
            $money = $money + $moneys;

        } else {
            if ($level == 0 && $normalRate > 0) {
                $promotionRate = $normalRate;
                $money = round($commission * $promotionRate / 100, 2);//普通用户分佣
            }
        }

        if ($money > 0) {
            $data = array(
                'user_id' => $uid,
                'nickname' => $userInfo['nickname'],
                'money' => $money,
                'promotion_rate' => $promotionRate,
                'level' => $level,
            );
            array_push($this->commissionArray, $data);
        }

        if ($userInfo['pid'] && $status) {
            $level++;
            return $this->calCommission($orderInfo, $userInfo['pid'], $level, $uid, $tuid, $teamRate, $type);
        } else {
            return $this->commissionArray;
        }
    }

}
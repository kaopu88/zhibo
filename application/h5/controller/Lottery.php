<?php

namespace app\h5\controller;

use bxkj_module\service\Bean;
use bxkj_module\service\User;
use think\Db;
use think\Exception;
use think\facade\Request;

class Lottery extends LoginController
{
    protected $config;

    public function __construct()
    {
        parent::__construct();
        try {
            $this->config = config('activity.');
            $status = $this->config['lottery_egg_is_open'];
            if ($status == 0) throw new Exception('砸金蛋活动未开启~', 1);
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    public function activeEgg()
    {
        $user = new User();
        $user_info = $user->getUser($this->data['user']['user_id']);
        $this->assign('token', $this->token);
        $this->assign('user_id', $this->data['user']['user_id']);
        $this->assign('bean', $user_info['bean']);
        $this->assign('bean_name', APP_BEAN_NAME);
        $this->assign('config', $this->config);
        return $this->fetch();
    }

    public function ajaxGoldegg()
    {
        $param = Request::post();
        try {
            $user = new User();
            $user_info = $user->getUser($this->data['user']['user_id']);
            apiAsserts($user_info['bean'] < $this->config['lottery_egg_bean'], '余额不足', 3);
            $bean = new Bean();
            $payRes = $bean->exp(array(
                'user_id' => $this->data['user']['user_id'],
                'trade_type' =>  'egg',
                'trade_no' => get_order_no('egg'),
                'total' => $this->config['lottery_egg_bean'],
            ));

            apiAsserts ($payRes === false, $bean->getError(), 0);
            $eggGiftService = new \app\lottery\service\LotteryEggGift();
            $gift = $eggGiftService->getList(['status' => 1], 0, 100);
            apiAsserts (empty($gift), '您暂未中奖', 0);
            $res = $this->getProbability($gift);
            apiAsserts($res['code'] <= 0, '您暂未中奖1', 0);
        } catch (Exception $e) {
            return $this->jsonError($e->getMessage(), $e->getCode());
        }

        $result = ['id' => $res['prize']['id'],'gift_id' => $res['prize']['gift_id'], 'name' => $res['prize']['name'], 'image' => $res['prize']['image'], 'type' => 'gift', 'is_win' => 1, 'user_id' => $this->data['user']['user_id']];
        $lotteryModel = new \app\h5\service\activity\Lottery();
        if ($lotteryModel->addUserGift($result) === false) return $this->jsonError('出错啦~');
        if ($lotteryModel->addLotteryLog($result) === false) return $this->jsonError('出错啦1~');

        return $this->jsonSuccess($result, '获取成功');
    }

    /**
     * @param array $list
     * @return int -1 表示未中奖
     */
    protected function getProbability(array $list = [])
    {
        if (empty($list)) return ['code' => 0];
        $result = array_column($list, 'probability');
        $probability_sum = array_sum($result);
        $sum = $probability_sum < 100 ? 100 : $probability_sum;
        $index_num = mt_rand(1,$sum);
        if ($index_num > $probability_sum) return ['code' => -1];
        foreach ($list as $key => $value) {
            $randNum = mt_rand(1, $probability_sum);
            if ($randNum <= $value['probability']) {
                $gift_id = $value['gift_id'];
                $prize = $list[$key];
                break;
            } else {
                $probability_sum -= $value['probability'];
            }
        }
        return ['code' => 1, 'prize' => $prize];
    }

}
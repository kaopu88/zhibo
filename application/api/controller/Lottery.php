<?php

namespace app\api\controller;

use app\common\controller\UserController;
use bxkj_module\service\Kpi;
use think\Db;
use app\api\service\Lottery as LotteryModel;
use bxkj_common\ClientInfo;
use bxkj_common\CoreSdk;
use bxkj_common\RedisClient;
use app\core\service\Socket;

class Lottery extends UserController
{
    protected static $buyLotteryType = 'buyLottery';
    const FIRST_TIME = 1535299200;//2018-08-27 00:00:00

    //获取大转盘类型
    public function getLotteryType()
    {
        $lotteryTypeList = Db::name('lottery_type')->field('id,name,sort')->where(['status' => '1'])->order('sort desc,id')->select();
        return $this->success($lotteryTypeList ?: []);
    }

    //获取大转盘详情
    public function getLotteryDetail()
    {
        $redis = new RedisClient();
        $params = request()->param();
        $LotteryDetailData = $redis->hget('cache:LotteryDetail', $params['lottery_type']);
        $result = json_decode($LotteryDetailData, true);
        if (!$result) {
            $lotteryContent = Db::name('lottery')->where(['status' => '1', 'lottery_type' => $params['lottery_type']])->find();
            if (!$lotteryContent) {
                return $this->success([]);
            }
            $result['id'] = $lotteryContent['id'];
            $result['lottery_name'] = $lotteryContent['name'];
            if ($lotteryContent['pay_num']) {
                $result['draw_count'] = explode(',', $lotteryContent['pay_num']);
                $result['draw_money'] = explode(',', $lotteryContent['pay_money']);
            } else {
                $result['draw_count'] = [$lotteryContent['total']];
            }
            $result['image'] = $lotteryContent['image'];
            $result['description'] = $lotteryContent['description'];
            $result['lucky'] = intval($lotteryContent['lucky']);
            $result['lucky_color'] = $lotteryContent['lucky_color'] ? $lotteryContent['lucky_color'] : '#F8DEBB';
            $lotteryGift = Db::name('lottery_gift')->field('id,name,price,image,gift_id')->where(['status' => '1', 'activity_id' => $lotteryContent['id']])->order('sort desc,id')->select();
            $result['lottery_gift'] = $lotteryGift;
            unset($lotteryContent);
            $redis->hset('cache:LotteryDetail', $params['lottery_type'], json_encode($result));
            $redis->expire('cache:LotteryDetail', 4 * 3600);
        }
        return $this->success($result ?: []);
    }

    //点击抽奖
    public function getReward()
    {
        $params = request()->param();
        $lotteryId = $params['lottery_id'];
        $room_id = $params['room_id'] ? $params['room_id'] : 30;
        $check = $this->checkSubmit('lottery_' . $lotteryId);
        if (!$check) {
            return $this->jsonError("操作频繁，请稍后再试!");
        }
        $lotteryContent = Db::name('lottery')->field('id,name,total,price,pay_num,pay_money')->where(['status' => '1', 'id' => $lotteryId])->find();
        if (!$lotteryContent) {
            return $this->jsonError("没有对应活动抽奖");
        }
        $temreWard = array();
        if ($lotteryContent['total']) {
            $beginToday = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
            $endToday = mktime(0, 0, 0, date('m'), date('d') + 1, date('Y')) - 1;
            $joinCount = Db::name('lottery_join')->where(['user_id' => USERID, 'lottery_id' => $lotteryId, 'lottery_num' => 0])->where('addtime', 'between', [$beginToday, $endToday])->count();
            if ($joinCount >= $lotteryContent['total']) {
                return $this->jsonError("您没有参与抽奖机会啦，明天再来吧");
            }
            $lotteryGift = Db::name('lottery_gift')->field('id,probability,name,price,gift_id,image')->where(['status' => '1', 'activity_id' => $lotteryContent['id']])->order('sort desc,id')->select();
            if (!$lotteryGift) {
                return $this->jsonError("很遗憾,奖品库存不足了");
            }
            foreach ($lotteryGift as $value) {
                $temreWard[$value['id']] = $value['probability'];
            }
            $reward_id = $this->getRand($temreWard);
            $rewardGift = array();
            foreach ($lotteryGift as $key => $value) {
                if ($value['id'] == $reward_id) {
                    $rewardGift = $value;
                }
            }
            unset($lotteryGift);
            if (empty($rewardGift)) {
                return $this->jsonError("非法操作啦！");
            }
            $lotteryModel = new LotteryModel();
            //添加参与抽奖数据
            $join_data = array(
                'lottery_id' => $params['lottery_id'],
                'lottery_tag' => $lotteryContent['name'],
                'user_id' => USERID,
                'lottery_num' => 0,
                'addtime' => time()
            );
            $insertId = $lotteryModel->addLotteryJoin($join_data);
            if (!$insertId) {
                return $this->jsonError("出现错误啦！");
            }
            //添加中奖log
            $log_data = array(
                'lottery_id' => $params['lottery_id'],
                'gift_id' => $rewardGift['gift_id'],
                'name' => $rewardGift['name'],
                'user_id' => USERID,
                'nike_name' => $this->user['nickname'],
                'price' => $rewardGift['price'],
                'create_time' => time()
            );
            $insertId = $lotteryModel->addLotteryLog($log_data);
            if (!$insertId) {
                return $this->jsonError("出现错误啦！");
            }
            $data = [
                'name' => $rewardGift['name'],
                'icon' => $rewardGift['image'],
                'user_id' => USERID,
                'gift_id' => $rewardGift['gift_id'],
                'access_method' => 'lottery',
                'create_time' => time(),
                'gift_type' => 1
            ];
            if ($lotteryModel->addUserGift($data) === false) return $this->jsonError('出错啦~');
            $live_info = Db::name('live')->field('id,nickname')->where(['status' => '1', 'id' => $room_id])->find();
            $content = $this->user['nickname'] . '在' . $live_info['nickname'] . '直播间抽中 1个' . $rewardGift['name'];
            $socket = new Socket();
            $message = [
                'mod' => 'Live',
                'act' => 'pushLotteryRoom',
                'args' => ['content' => $content],
                'web' => 1
            ];
            $res = $socket->connectSocket($message);
            if (!$res) $this->error($socket->getError());
            return $this->success([$rewardGift] ?: []);
        } else {
            $lotteryId = $params['lottery_id'];//这是哪一个活动
            $num = $numCount = $params['num'];//购买次数
            $lotteryContent = Db::name('lottery')->where(['status' => '1', 'id' => $lotteryId])->find();
            if (!$lotteryContent) {
                return $this->jsonError("非法操作啦！");
            }
            $result = [];
            if ($lotteryContent['pay_num']) {
                $result['draw_count'] = explode(',', $lotteryContent['pay_num']);
            }
            if (!in_array($num, $result['draw_count'])) {
                return $this->jsonError("非法操作啦");
            }
            $joinCount = Db::name('lottery_join')->where(['user_id' => USERID, 'lottery_id' => $lotteryId, 'lottery_num' => 1])->count();
            if ($joinCount <= 0 || $joinCount < $num) {
                return $this->jsonError("您没有购买抽奖次数或者抽奖次数不够");
            }
            $hasJoinCount = Db::name('lottery_join')->where(['user_id' => USERID, 'lottery_id' => $lotteryId, 'lottery_num' => 0])->count();
            //添加幸运值
            $luckyGift = array();
            $luckyValue = intval($lotteryContent['lucky']);//幸运值
            if ($luckyValue && $hasJoinCount) {
                //获取参与了次数
                $lotteryLucky = Db::name('lottery_lucky')->where(['user_id' => USERID, 'lottery_id' => $lotteryId])->find();
                if ($lotteryLucky) {
                    if ($lotteryLucky['has_own']) {
                        $hasCount = $hasJoinCount - ($lotteryLucky['has_own'] * $luckyValue);
                        if ($hasCount >= $luckyValue) {
                            $num = $num - 1;
                            $luckyGift = $this->commonLucky($lotteryContent['id'], $lotteryId);
                        }
                    } else {
                        if ($hasJoinCount >= $luckyValue) {
                            $num = $num - 1;
                            $luckyGift = $this->commonLucky($lotteryContent['id'], $lotteryId);
                        }
                    }
                } else {
                    $has_own = intval($hasJoinCount / $luckyValue);
                    $data = [
                        'user_id' => USERID,
                        'has_own' => $has_own,
                        'lottery_tag' => $lotteryContent['name'],
                        'lottery_id' => $lotteryId,
                        'addtime' => time()
                    ];
                    $insertid = Db::name('lottery_lucky')->insertGetId($data);
                }
            }
            if ($num) {
                $lotteryGift = Db::name('lottery_gift')->field('id,probability,name,price,gift_id,image')->where(['status' => '1', 'activity_id' => $lotteryContent['id']])->order('sort desc,id')->select();
                if (!$lotteryGift) {
                    return $this->jsonError("很遗憾,奖品库存不足了");
                }
                foreach ($lotteryGift as $value) {
                    $temreWard[$value['id']] = $value['probability'];
                }
                $rewardId = [];
                $i = 1;
                while ($i <= $num) {
                    $rewardId[] = $this->getRand($temreWard);
                    ++$i;
                }
                $rewardGift = array();
                foreach ($rewardId as $k => $v) {
                    foreach ($lotteryGift as $key => $value) {
                        if ($value['id'] == $v) {
                            $rewardGift[] = $value;
                        }
                    }
                }
            }
            if (!empty($luckyGift)) {
                $rewardGift[] = $luckyGift;
            }
            Db::name('lottery_join')
                ->where(['user_id' => USERID, 'lottery_id' => $lotteryId, 'lottery_num' => 1])
                ->order('id asc')
                ->limit($numCount)
                ->setDec('lottery_num');
            $i = 0;
            $lotteryModel = new LotteryModel();
            while ($i <= ($numCount - 1)) {
                $log_data = array(
                    'lottery_id' => $lotteryId,
                    'gift_id' => $rewardGift[$i]['gift_id'],
                    'name' => $rewardGift[$i]['name'],
                    'user_id' => USERID,
                    'nike_name' => $this->user['nickname'],
                    'price' => $rewardGift[$i]['price'],
                    'create_time' => time()
                );
                if ($lotteryModel->addLotteryLog($log_data) === false) return $this->jsonError('出错啦~');
                $data = [
                    'name' => $rewardGift[$i]['name'],
                    'icon' => $rewardGift[$i]['image'],
                    'user_id' => USERID,
                    'gift_id' => $rewardGift[$i]['gift_id'],
                    'access_method' => 'lottery',
                    'create_time' => time(),
                    'gift_type' => 1
                ];
                if ($lotteryModel->addUserGift($data) === false) return $this->jsonError('出错啦~');
                ++$i;
            }
            $rewardGiftString = array_column($rewardGift, 'name');
            $rewardGiftString = array_count_values($rewardGiftString);
            $content = '';
            foreach ($rewardGiftString as $k => $v) {
                $content .= $v . '个' . $k . ',';
            }
            $content = trim($content, ',');
            $live_info = Db::name('live')->field('id,nickname')->where(['status' => '1', 'id' => $room_id])->find();
            $content = $this->user['nickname'] . '在' . $live_info['nickname'] . '直播间抽中 ' . $content;
            $socket = new Socket();
            $message = [
                'mod' => 'Live',
                'act' => 'pushLotteryRoom',
                'args' => ['content' => $content],
                'web' => 1
            ];
            $res = $socket->connectSocket($message);
            if (!$res) $this->error($socket->getError());
            return $this->success($rewardGift ?: []);
        }
    }

    //获取奖品
    public function reward()
    {
        $params = request()->param();
        $lottery_id = $params['lottery_id'];//这是哪一个活动
        $reward_id = $params['reward_id'];//奖品id
        if (empty($lottery_id) || empty($reward_id)) {
            return $this->jsonError("非法操作啦！");
        }
        $lotteryContent = Db::name('lottery')->field('id,name,total,price,pay_num,pay_money')->where(['status' => '1', 'id' => $lottery_id])->find();
        if (!$lotteryContent) return $this->jsonError("没有对应活动抽奖");
        if ($lotteryContent['total']) {
            $lotteryGift = Db::name('lottery_gift')->field('id,name,price,image')->where(['status' => '1', 'id' => $reward_id])->find();
            if (!$lotteryGift) {
                return $this->jsonError("非法操作啦");
            }
            return $this->success('恭喜您已获得' . $lotteryGift['name'] ?: []);
        }
    }

    //剩余次数
    public function hasLeftChanges()
    {
        $params = request()->param();
        $lotteryContent = Db::name('lottery')->field('id,name,total,price,pay_num,pay_money')->where(['status' => '1', 'id' => $params['lottery_id']])->find();
        if (!$lotteryContent) {
            return $this->jsonError("没有对应活动抽奖",1045);
        }
        $bean = Db::name('bean')->where(['user_id' => USERID])->field('bean')->find();
        if ($lotteryContent['total']) {
            $beginToday = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
            $endToday = mktime(0, 0, 0, date('m'), date('d') + 1, date('Y')) - 1;
            $joinCount = Db::name('lottery_join')->where(['user_id' => USERID, 'lottery_id' => $params['lottery_id'], 'lottery_num' => 0])->where('addtime', 'between', [$beginToday, $endToday])->count();
            return $this->success(array('hasTotal' => ($lotteryContent['total'] - $joinCount), 'isFree' => 0, 'bean' => $bean['bean']));
        } else {
            $joinCount = Db::name('lottery_join')->where(['user_id' => USERID, 'lottery_id' => $params['lottery_id'], 'lottery_num' => 1])->count();
            return $this->success(array('hasTotal' => $joinCount, 'isFree' => 1, 'bean' => $bean['bean']));
        }
    }

    //购买抽奖资格
    public function buyLottery()
    {
        $params = request()->param();
        $lotteryId = $params['lottery_id'];//这是哪一个活动
        $num = $params['num'];//购买次数
        $lotteryContent = Db::name('lottery')->where(['status' => '1', 'id' => $lotteryId])->find();
        if (!$lotteryContent) {
            return $this->jsonError("非法操作啦！");
        }

        $result = [];
        if ($lotteryContent['pay_num']) {
            $result['draw_count'] = explode(',', $lotteryContent['pay_num']);
            $result['draw_money'] = explode(',', $lotteryContent['pay_money']);
        }
        if(empty($result)){
            return $this->jsonError("后台没有设置抽奖次数和需要钻石数或者是免费抽奖不需要购买！");
        }

        if (!in_array($num, $result['draw_count'])) {
            return $this->jsonError("非法操作啦！");
        }
        $lotteryPay = array_combine($result['draw_count'], $result['draw_money']);
        //获取最新帐户余额
        $bean = Db::name('bean')->where(['user_id' => USERID])->field('bean')->find();
        if ($bean['bean'] < $lotteryPay[$num]) {
            return $this->jsonError("您的余额不足赶紧去充值吧！", 888);
        }
        $tradeNo = get_order_no(self::$buyLotteryType);
        $coreSdk = new CoreSdk();
        $pay = $coreSdk->payBean([
            'user_id' => USERID,
            'trade_type' => self::$buyLotteryType,
            'trade_no' => $tradeNo,
            'total' => $lotteryPay[$num],
            'client_seri' => ClientInfo::encode()
        ]);
        if (empty($pay)) return $this->jsonError($coreSdk->getError());
        $beanLog = Db::name('bean_log')->where(['user_id' => USERID, 'trade_no' => $tradeNo])->field('log_no,trade_type,trade_no,total')->find();
        $user = $this->getUser(USERID);
        $kpi = new Kpi(time());
        $kpi->cons(0, $user, $beanLog);
        $i = 1;
        while ($i <= $num) {
            $join_data = array(
                'lottery_id' => $lotteryId,
                'lottery_tag' => $lotteryContent['name'],
                'user_id' => USERID,
                'lottery_num' => 1,
                'addtime' => time()
            );
            $insertid = Db::name('lottery_join')->insertGetId($join_data);
            ++$i;
        }
        return $this->success('购买成功');
    }

    //获取中奖记录
    public function getLotteryLog()
    {
        $params = request()->param();
        $lotteryId = $params['lottery_id'];
        $where = [];
        if (intval($lotteryId) > 0) {
            $where['lottery_id'] = array('eq', $lotteryId);
        }
        $lotteryLogList = Db::name('lottery_record_log')->where($where)->order('id desc')->limit(100)->select();
        return $this->success($lotteryLogList ?: []);
    }

    //获取幸运值
    public function getLuckyValue()
    {
        $params = request()->param();
        $lotteryId = $params['lottery_id'];
        $lotteryContent = Db::name('lottery')->field('id,name,total,price,pay_num,pay_money,lucky')->where(['status' => '1', 'id' => $lotteryId])->find();
        if (!$lotteryContent) {
            return $this->jsonError("没有对应活动抽奖");
        }
        $hasJoinCount = Db::name('lottery_join')->where(['user_id' => USERID, 'lottery_id' => $lotteryId, 'lottery_num' => 0])->count();
        $myLucky = 0;
        $Lucky = intval($lotteryContent['lucky']);//幸运值
        if ($Lucky && $hasJoinCount) {
            //获取参与了次数
            $lotteryLucky = Db::name('lottery_lucky')->where(['user_id' => USERID, 'lottery_id' => $lotteryId])->find();
            if (!$lotteryLucky) return $this->success(intval(($hasJoinCount / $Lucky) * 100));
            if ($lotteryLucky['has_own']) {
                $hasCount = $hasJoinCount - ($lotteryLucky['has_own'] * $Lucky);
                $myLucky = (intval(($hasCount / $Lucky) * 100)) < 100 ? intval(($hasCount / $Lucky) * 100) : 100;
            } else {
                $myLucky = (intval(($hasJoinCount / $Lucky) * 100) < 100) ? intval(($hasJoinCount / $Lucky) * 100) : 100;
            }
        }
        return $this->success($myLucky);
    }

    private function checkSubmit($redis_key, $time = 2)
    {
        $redis = new RedisClient();
        if ($redis->setnx($redis_key, time())) {
            $redis->expireAt($redis_key, time() + $time);
        } else {
            return false;
        }
        return true;
    }


    private function commonLucky($activity_id, $lotteryId)
    {
        $luckyGift = Db::name('lottery_gift')->field('id,probability,name,price,gift_id,image')->where(['status' => '1', 'ismust' => '1', 'activity_id' => $activity_id])->find();
        if (empty($luckyGift)) { //如果后台没有勾选幸运礼物直接获取一个
            $luckyGift = Db::name('lottery_gift')->field('id,probability,name,price,gift_id,image')->where(['status' => '1', 'activity_id' => $activity_id])->find();
        }
        Db::name('lottery_lucky')
            ->where(['user_id' => USERID, 'lottery_id' => $lotteryId])
            ->setInc('has_own');

        return $luckyGift;
    }

    //大转盘通用算法
    private function getRand($proArr)
    {
        $result = '';
        $proSum = array_sum($proArr);
        foreach ($proArr as $key => $proCur) {
            $randNum = mt_rand(1, $proSum);
            if ($randNum <= $proCur) {
                $result = $key;
                break;
            }
            $proSum -= $proCur;
        }
        unset($proArr);
        return intval($result);
    }

    //业绩统计
    protected function cons($user, $log)
    {
        $user = is_array($user) ? $user : ['user_id' => $user];
        //如果是虚似号则不统计
        if (!$user || $user['isvirtual'] != '0' || ($log['total'] <= 0)) return true;
        $rel = Db::name('promotion_relation')->where(['user_id' => $user['user_id']])->field('agent_id,promoter_uid')->find();
        //记录明细
        $data = [];
        $data['agent_id'] = isset($rel['agent_id']) ? (int)$rel['agent_id'] : 0;
        $data['promoter_uid'] = isset($rel['promoter_uid']) ? (int)$rel['promoter_uid'] : 0;
        $data['trade_no'] = $log['log_no'];//实际上这里是log_no
        $data['rel_type'] = $log['trade_type'];
        $data['rel_no'] = $log['trade_no'] ? $log['trade_no'] : '';
        $data['total_fee'] = $log['total'];
        $data['loss_total'] = 0;
        $data['subject'] = '';
        $data['pay_method'] = '';
        $data['pay_platform'] = '';
        $data['cons_uid'] = $user['user_id'];
        $data['cons_phone'] = $user['phone'] ? $user['phone'] : '';
        $this->supplementaryTime($data, $log['create_time']);
        $id = Db::name('kpi_cons')->insertGetId($data);
        if (!$id) return $this->jsonError("错误");
        return true;
    }

    //补充时间参数
    protected function supplementaryTime(&$data, $time)
    {
        $data['year'] = date('Y', $time);
        $data['month'] = date('Ym', $time);
        $data['day'] = date('Ymd', $time);
        $data['create_time'] = time();
        $data['week'] = $this->getWeekNum($time);
        $data['fnum'] = $this->getFortNum($time);
    }

    protected function getUser($userId)
    {
        $key = "old_user:{$userId}";
        $redis = RedisClient::getInstance();
        $json = $redis->get($key);
        $oldUser = $json ? json_decode($json, true) : null;
        if (empty($oldUser)) {
            $oldUser = Db::name('user')->where(['user_id' => $userId])->find();
            if ($oldUser) $redis->set($key, json_encode($oldUser));
        }
        return $oldUser;
    }

    protected function getWeekNum($time = null)
    {
        $time = isset($time) ? $time : time();
        $diff = $time - self::FIRST_TIME;
        $dayNum = $diff / (3600 * 24);
        return floor($dayNum / 7);
    }

    //半月刊号
    protected function getFortNum($time = null)
    {
        $time = isset($time) ? $time : time();
        $month = date('Ym', $time);
        $day = date('d', $time);
        return $day <= 15 ? ($month . '1') : $month . '2';
    }

    /**
     * 更新缓存推广员和代理商业绩
     *
     * @param $log
     * @return bool
     */
    public function consRebuildByLog($log)
    {
        $this->refreshTime($log['create_time']);
        //统计推广员的业绩
        if ($log['promoter_uid']) {
            if (!$this->incr('promoter', $log['promoter_uid'], 'cons', $log['total_fee'])) return false;
        }
        //代理商自身的业绩统计
        $selfAgentId = $this->getSelfAgentId($log);
        if (!empty($selfAgentId)) {
            if (!$this->incr('agent', $selfAgentId, 'cons', $log['total_fee']))
                return false;
        }
        return true;
    }

}
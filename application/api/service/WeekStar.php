<?php
/**
 * Created by PhpStorm.
 * User: 李成
 * Date: 2020/8/7
 * Time: 15:06
 */

namespace app\api\service;

use app\admin\service\User;
use app\admin\service\WeekStarGift;
use app\admin\service\WeekStarLog;
use bxkj_common\DateTools;
use bxkj_common\RedisClient;
use bxkj_module\service\Bean;
use bxkj_module\service\Millet;
use bxkj_module\service\Service;
use think\Db;

class WeekStar extends Service
{
    /**
     * 获取周星礼物排行榜
     * @param $giftId
     * @param $limit
     * @return array|bool|mixed|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getStarUserList($giftId)
    {
        $list = [];
        $result = [];
        $now = date("Y-m-d");
        $w = date('w', strtotime($now));
        $startTime = strtotime("$now -" . ($w ? $w - 1 : 6) . ' days');//本周开始时间
        $endTime = strtotime(date("Y-m-d", $startTime) . "+7 days");//本周结束时间
        $week = DateTools::getWeekNum();
        $redis = RedisClient::getInstance();
        $key = "week_star:gift_id:" . $week . ':' . $giftId;
        $userList = $redis->zrevrange($key, 0, 49, true);

        if (empty($userList)) return $result;

        foreach ($userList as $uid => $score) {
            $data['gnum'] = $score;
            $data['uid'] = $uid;
            $list[] = $data;
        }

        $starGift = new WeekStarGift();
        $giftInfo = $starGift->getInfo(["gift_id" => $giftId, "start_time" => $startTime, "end_time" => $endTime]);
        $limit = $giftInfo['min_num'];
        $result = $this->getListUserDetail($list, $giftId, $limit);
        return $result;
    }

    /**
     * 获取排行榜各用户及金主相关信息
     * @param $list
     * @param $giftId
     * @param $limit
     * @return array|bool|mixed|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    protected function getListUserDetail($list, $giftId, $limit)
    {
        if (empty($list)) return [];
        $result = [];
        $user = new \bxkj_module\service\User();
        foreach ($list as &$value) {
            $value['user_id'] = $value['uid'];
            $value['gift_num'] = $value['gnum'];
            if ($value['gnum'] >= $limit) {
                $toUserInfo = $user->getUser($value['uid']);
                $value['username'] = $toUserInfo['username'];
                $value['avatar'] = $toUserInfo['avatar'];
            }
            $result[] = $value;
        }
        return $result;
    }

    /**
     * 获取礼物消费排行
     * @param $giftId
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getConsumerList($giftId)
    {
        $result = [];
        $week = DateTools::getWeekNum();
        $redis = RedisClient::getInstance();
        $key = "week_star_user:gift_id:" . $week . ':' . $giftId;
        $userList = $redis->zrevrange($key, 0, 49, true);
        $user = new \bxkj_module\service\User();
        if (empty($userList)) return [];
        foreach ($userList as $uid => $score) {
            $data['gift_num'] = $score;
            $userInfo = $user->getUser($uid);
            $data['username'] = isset($userInfo['username']) ? $userInfo['username'] : '';
            $data['avatar'] = isset($userInfo['avatar']) ? $userInfo['avatar'] : '';
            $result[] = $data;
        }
        return $result;
    }

    public function getSystemList()
    {
        $config = config('live.');
        $data['rule'] = $config['description'];//规则描述
        if ($config['rank_reward_type'] == 3) {
            $anchorReward = [];
            foreach ($config['rank_reward'] as $value) {
                if ($value['gift_expire'] == 0) {
                    $anchorReward[] = $value['gift_name'];
                } else {
                    $anchorReward[] = $value['gift_name'] . $value['gift_expire'] . "天";
                }
            }
            $richReward = [];
            foreach ($config['rich_reward'] as $value) {
                if ($value['gift_expire'] == 0) {
                    $richReward[] = $value['gift_name'];
                } else {
                    $richReward[] = $value['gift_name'] . $value['gift_expire'] . "天";
                }
            }
        } elseif ($config['rank_reward_type'] == 2) {
            $anchorReward = [];
            foreach ($config['rank_reward'] as $value) {
                $anchorReward[] = APP_BEAN_NAME . $value['reward'] . "个";
            }
            $richReward = [];
            foreach ($config['rich_reward'] as $value) {
                $richReward[] = APP_BEAN_NAME . $value['reward'] . "个";
            }
        } else {
            $anchorReward = [];
            foreach ($config['rank_reward'] as $value) {
                $anchorReward[] = APP_MILLET_NAME . $value['reward'] . "个";
            }
            $richReward = [];
            foreach ($config['rich_reward'] as $value) {
                $richReward[] = APP_MILLET_NAME . $value['reward'] . "个";
            }
        }
        $data['anchor_reward'] = $anchorReward;
        $data['rich_reward'] = $richReward;
        return $data;
    }

    /**
     * 获取上周榜单数据
     * @param $giftId
     * @return array|bool|mixed|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getLastWeekData($giftId, $limit = 0)
    {
        $data = [];
        $now = date("Y-m-d");
        $w = date('w', strtotime($now));
        $weekTime = strtotime("$now -" . ($w ? $w - 1 : 6) . ' days');//本周开始时间
        $startTime = strtotime(date("Y-m-d", $weekTime) . "-7 days");//上周开始时间

        $status = config('live.week_star_status');
        if ($status == 1) {
            $redis = RedisClient::getInstance();
            $key = "last_week_star_data:gift_id:" . $giftId;
            if (!empty($data = $redis->get($key))) {
                $data = json_decode($data, true);

            } else {
                $where['wsg.start_time'] = $startTime;
                $where['wsg.end_time'] = $weekTime;
                $where['wsg.gift_id'] = $giftId;
                $field = "awsl.*";
                $data = Db::name("week_star_gift")
                    ->alias("wsg")->field($field)
                    ->leftJoin("activity_week_star_log awsl", "awsl.gift_id=wsg.gift_id")
                    ->leftJoin("gift g", "g.id=wsg.gift_id")
                    ->where($where)->group("to_uid")->order("awsl.gift_num desc")->select();
                if ($data) {
                    $redis->set($key, json_encode($data), 3600);
                }
            }
            if (!empty($data) && $limit > 0) {
                $user = new User();
                foreach ($data as $key => $value) {
                    if ($value['gift_num'] < $limit) {
                        unset($data[$key]);
                    }
                    $userInfo = $user->getInfo($value['user_id']);
                    $data[$key]['nickname'] = $userInfo['nickname'];
                    $data[$key]['avatar'] = $userInfo['avatar'];
                    $goldUserInfo = $user->getInfo($value['gold_user_id']);
                    $data[$key]['gold_nickname'] = $goldUserInfo['nickname'];
                    $data[$key]['gold_avatar'] = $goldUserInfo['avatar'];
                }
            }
        }
        return $data;
    }

    /**
     * 周星奖励
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function weekStarReward()
    {
        $status = config('live.week_star_status');
        if ($status == 1) {
            $now = date("Y-m-d");
            $w = date('w', strtotime($now));
            $weekTime = strtotime("$now -" . ($w ? $w - 1 : 6) . ' days');//本周开始时间
            $startTime = strtotime(date("Y-m-d", $weekTime) . "-7 days");//上周开始时间

            $giftStar = new \app\admin\service\WeekStarGift();
            $where['start_time'] = $startTime;
            $where['end_time'] = $weekTime;
            $total = $giftStar->getTotal($where);
            $data = $giftStar->getList($where, 0, $total);
            if ($data) {
                $config = Db::name('sys_config')->where(['mark' => 'week_star', 'classified' => 'live'])->value('value');
                $config = json_decode($config, true);
                foreach ($data as $value) {
                    $this->anchorReward($value['gift_id'], $startTime, $weekTime, $config);
                    $this->richReward($value['gift_id'], $startTime, $weekTime, $config);
                }
            }
        }
    }

    /**
     * 主播排行奖励
     * @param $giftId
     * @param $startTime
     * @param $endTime
     * @param $config
     * @return bool
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    protected function anchorReward($giftId, $startTime, $endTime, $config)
    {
        self::startTrans();
        $map['gl.gift_id'] = $giftId;
        $field = "gl.to_uid as uid, sum(gl.num) as gnum";
        $rankList = Db::name("gift_log")->field($field)->alias("gl")->leftJoin("gift g", "g.id=gl.gift_id")
            ->where($map)->whereBetweenTime("gl.create_time", $startTime, $endTime)->group("gl.to_uid")->order("gnum desc")->select();
        if ($rankList) {
            $weekStarLog = new WeekStarLog();
            foreach ($rankList as $level => $val) {
                if ($val['gnum'] > 0) {
                    $res = $this->getAnchorReward($val['uid'], $level, $giftId, $config);
                    //礼物冠名
                    if ($config['rename_gift_status'] == 1) {
                        $log['has_rename'] = 1;
                        $log['rename_days'] = $config['rename_reward_days'];
                        $log['rename_profit_rate'] = $config['rename_reward_rate'];
                    }
                    //连胜
                    if ($config['win_setting_status'] == 1 && !empty($res) && $level == 0) {
                        $log['winning_reward_type'] = $config['win_reward_type'];
                        $log['winning_streak'] = $res['winning_streak'];
                        $log['winning_reward'] = $config['winning_reward'];
                    }
                    //狙击
                    if ($config['snipe_setting_status'] == 1 && !empty($res) && $level == 0) {
                        $log['winning_reward_type'] = $config['snipe_reward_type'];
                        $log['is_snipe'] = $res['is_snipe'];
                        $log['snipe_reward'] = $config['snipe_reward'];
                    }

                    $log['user_id'] = $val['uid'];

                    $where['gift_id'] = $giftId;
                    $where['to_uid'] = $val['uid'];
                    $list = Db::name("gift_log")->field("user_id,sum(num) as gnum")->where($where)->whereBetweenTime("create_time", $startTime, $endTime)->group("user_id")->order("gnum desc")->limit(1)->select();
                    $log['gold_user_id'] = $list[0]['user_id'];
                    $log['gift_id'] = $giftId;
                    $log['gift_num'] = $val['gnum'];
                    $log['activity_start_time'] = $startTime;
                    $log['rank_level'] = $level + 1;
                    $log['add_time'] = time();
                    $log['type'] = 'anchor';
                    $status = $weekStarLog->addLog($log);
                    if (!$status) {
                        self::rollback();
                        return false;
                    }
                }
            }
        }
        self::commit();
        return true;
    }

    /**
     * 礼物富豪奖励
     * @param $giftId
     * @param $startTime
     * @param $endTime
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    protected function richReward($giftId, $startTime, $endTime, $config)
    {
        self::startTrans();
        $map['gl.gift_id'] = $giftId;
        $field = "gl.user_id as uid, sum(gl.num) as gnum";
        $rankList = Db::name("gift_log")->field($field)->alias("gl")->leftJoin("gift g", "g.id=gl.gift_id")
            ->where($map)->whereBetweenTime("gl.create_time", $startTime, $endTime)->group("gl.user_id")->order("gnum desc")->select();
        if ($rankList) {
            $weekStarLog = new WeekStarLog();
            foreach ($rankList as $level => $rval) {
                $res = $this->getRichReward($rval['uid'], $level, $config);
                if (isset($res['reward_type']) && isset($res['reward'])) {
                    $log['rich_reward_type'] = $res['reward_type'];
                    $log['rich_reward'] = $res['reward'];
                }
                $log['user_id'] = $rval['uid'];
                $log['gift_id'] = $giftId;
                $log['gift_num'] = $rval['gnum'];
                $log['activity_start_time'] = $startTime;
                $log['rank_level'] = $level + 1;
                $log['add_time'] = time();
                $log['type'] = 'rich';
                $status = $weekStarLog->addLog($log);
                if (!$status) {
                    self::rollback();
                    return false;
                }
            }
        }
        self::commit();
        return true;
    }

    /**
     * 获取主播奖励
     * @param $userId
     * @param $rankLevel
     * @param $giftId
     * @param $config
     * @return array|bool
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    protected function getAnchorReward($userId, $rankLevel, $giftId, $config)
    {
        $data = [];
        self::startTrans();
        if ($rankLevel > 2) {//目前只有前三名有奖励
            return false;
        }
        $now = date("Y-m-d");
        $w = date('w', strtotime($now));
        $weekTime = strtotime("$now -" . ($w ? $w - 1 : 6) . ' days');//本周开始时间
        $startTime = strtotime(date("Y-m-d", $weekTime) . "-7 days");//上周开始时间

        $millet = new Millet();
        $bean = new Bean();

        $type = $config['rank_reward_type'];
        $reward = [];
        foreach ($config['rank_reward'] as $value) {
            if ($type != 3) {//1:金币;2:钻石;3:期限礼物
                $reward[] = $value['reward'];
            } else {
                $gift['name'] = $value['gift_name'];
                $gift['expire_time'] = $value['gift_expire'];
                $gift['gift_id'] = $value['gift_id'];
                $reward[] = $gift;
            }
        }

        if (!empty($reward) && $config['rank_reward_status'] == 1) {
            $incRankRes = true;
            if ($type == 1 && $reward[$rankLevel] > 0) {//金币
                $incRankRes = $millet->incMill(array(
                    'cont_uid' => $userId,
                    'user_id' => $userId,
                    'trade_type' => 'week_star_rank',
                    'trade_no' => get_order_no('week_star'),
                    'total' => $reward[$rankLevel],
                ));

            } elseif ($type == 2 && $reward[$rankLevel] > 0) {//钻石
                $incRankRes = $bean->reward([
                    'user_id' => $userId,
                    'type' => 'week_star_rank_reward_bean',
                    'bean' => $reward[$rankLevel],
                ]);

            } elseif ($type == 3 && !empty($reward[$rankLevel]['gift_id'])) {
                $giftInfo = Db::name("gift")->where(["id" => $reward[$rankLevel]['gift_id']])->find();
                $giftLog = [
                    'name' => $reward[$rankLevel]['name'],
                    'icon' => $giftInfo['picture_url'],
                    'num' => 1,
                    'user_id' => $userId,
                    'gift_id' => $reward[$rankLevel]['gift_id'],
                    'access_method' => 'week_star_rank',
                    'create_time' => time(),
                    'expire_time' => ($reward[$rankLevel]['gift_expire'] == 0) ? 0 : $weekTime + 86400 * $reward[$rankLevel]['gift_expire'],
                    'gift_type' => 1
                ];
                $incRankRes = Db::name('user_package')->insertGetId($giftLog);
            }
            if (!$incRankRes) {
                self::rollback();
                return false;
            }
        }

        $where["activity_start_time"] = $startTime;
        $where["rank_level"] = 1;
        $where["gift_id"] = $giftId;
        if ($rankLevel == 0) {
            $lastLog = Db::name("activity_week_star_log")->where($where)->find();//上周第一名
        }

        //第一名连胜奖励
        if (isset($config['win_setting_status']) && $config['win_setting_status'] == 1 && $rankLevel == 0) {
            $streakNum = 1;
            $streakReward = 0;
            if (!empty($lastLog) && $lastLog['user_id'] == $userId) {//上周第一名和本周第一名为同一人
                $streakNum = $lastLog['winning_streak'] + 1;
            }
            foreach ($config['win_setting'] as $winValue) {
                if ($streakNum == $winValue['times'] && $winValue['reward'] > 0) {
                    $streakReward = $winValue['reward'];//连胜奖励
                }
            }
            if ($streakReward > 0) {
                $incWinRes = true;
                if ($config['win_reward_type'] == 1) {
                    $incWinRes = $millet->incMill(array(
                        'cont_uid' => $userId,
                        'user_id' => $userId,
                        'trade_type' => 'win_streak',
                        'trade_no' => get_order_no('week_star'),
                        'total' => $streakReward
                    ));

                } elseif ($config['win_reward_type'] == 2) {
                    $incWinRes = $bean->reward([
                        'user_id' => $userId,
                        'type' => 'week_star_rank_reward_bean',
                        'bean' => $streakReward
                    ]);

                }
                if (!$incWinRes) {
                    self::rollback();
                    return false;
                }
            }
            $data['winning_streak'] = $streakNum;//连胜次数
            $data['winning_reward'] = $streakReward;//连胜奖励
        }

        //第一名奖励--是否终结其他用户连胜
        if (isset($config['snipe_setting_status']) && $config['snipe_setting_status'] == 1 && $rankLevel == 0) {
            $snipeNum = 1;
            $snipeReward = 0;
            if (!empty($lastLog) && $lastLog['user_id'] != $userId) {
                if ($lastLog['winning_streak'] > 0) {
                    $snipeNum = $lastLog['winning_streak'];//上周第一名的连胜次数
                }
            }
            foreach ($config['snipe_setting'] as $sniValue) {
                if ($snipeNum == $sniValue['times'] && $sniValue['reward'] > 0) {
                    $snipeReward = $sniValue['reward'];//狙击奖励
                }
            }
            if ($snipeReward > 0) {
                $incSniRes = true;
                if ($config['win_reward_type'] == 1) {
                    $incSniRes = $millet->incMill(array(
                        'cont_uid' => $userId,
                        'user_id' => $userId,
                        'trade_type' => 'snipe_reward',
                        'trade_no' => get_order_no('week_star'),
                        'total' => $snipeReward
                    ));

                } elseif ($config['win_reward_type'] == 2) {
                    $incSniRes = $bean->reward([
                        'user_id' => $userId,
                        'type' => 'week_star_rank_reward_bean',
                        'bean' => $snipeReward
                    ]);

                }
                if (!$incSniRes) {
                    self::rollback();
                    return false;
                }
            }
            $data['is_snipe'] = 1;//狙击连胜次数
            $data['snipe_reward'] = $snipeReward;//狙击奖励
        }

        //第一名奖励--礼物冠名
        if (isset($config['rename_gift_status']) && $config['rename_gift_status'] == 1 && $rankLevel == 0) {
            $renameDays = $config['rename_reward_days'];//冠名天数
            $renameRate = $config['rename_reward_rate'];//冠名奖励
            $res = Db::name("week_star_gift")->where(["gift_id" => $giftId, "start_time" => $startTime])->update([
                "rename_expire_time" => time() + 86400 * $renameDays,
                "rename_profit_rate" => $renameRate,
                "rename_uid" => $userId
            ]);
            if (!$res) {
                self::rollback();
                return false;
            }
        }

        self::commit();
        return $data;
    }

    /**
     * 获取富豪奖励
     * @param $userId
     * @param $rankLevel
     * @param $config
     * @return array|bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    protected function getRichReward($userId, $rankLevel, $config)
    {
        $data = [];
        self::startTrans();
        if ($rankLevel > 2) {//目前只有前三名有奖励
            return false;
        }
        $now = date("Y-m-d");
        $w = date('w', strtotime($now));
        $weekTime = strtotime("$now -" . ($w ? $w - 1 : 6) . ' days');//本周开始时间

        $millet = new Millet();
        $bean = new Bean();
        $type = $config['rank_reward_type'];
        $reward = [];
        foreach ($config['rich_reward'] as $value) {
            if ($type != 3) {//1:金币;2:钻石;3:期限礼物
                $reward[] = $value['reward'];
            } else {
                $gift['name'] = $value['gift_name'];
                $gift['expire_time'] = $value['gift_expire'];
                $gift['gift_id'] = $value['gift_id'];
                $reward[] = $gift;
            }
        }

        if (!empty($reward) && $config['rich_reward_status'] == 1) {
            $incRankRes = true;
            if ($type == 1 && $reward[$rankLevel] > 0) {//金币
                $incRankRes = $millet->incMill(array(
                    'cont_uid' => $userId,
                    'user_id' => $userId,
                    'trade_type' => 'week_star_rich',
                    'trade_no' => get_order_no('week_star'),
                    'total' => $reward[$rankLevel],
                ));
                $richReward = $reward[$rankLevel];

            } elseif ($type == 2 && $reward[$rankLevel] > 0) {//钻石
                $incRankRes = $bean->reward([
                    'user_id' => $userId,
                    'type' => 'week_star_rich_reward_bean',
                    'bean' => $reward[$rankLevel],
                ]);
                $richReward = $reward[$rankLevel];

            } elseif ($type == 3 && !empty($reward[$rankLevel]['gift_id'])) {
                $giftInfo = Db::name("gift")->where(["id" => $reward[$rankLevel]['gift_id']])->find();
                $giftLog = [
                    'name' => $reward[$rankLevel]['name'],
                    'icon' => $giftInfo['picture_url'],
                    'num' => 1,
                    'user_id' => $userId,
                    'gift_id' => $reward[$rankLevel]['gift_id'],
                    'access_method' => 'week_star_rich',
                    'create_time' => time(),
                    'expire_time' => ($reward[$rankLevel]['gift_expire'] == 0) ? 0 : $weekTime + 86400 * $reward[$rankLevel]['gift_expire'],
                    'gift_type' => 1
                ];
                $incRankRes = Db::name('user_package')->insertGetId($giftLog);
                $richReward = $reward[$rankLevel]['gift_id'] . "," . $reward[$rankLevel]['expire_time'];
            }
            if (!$incRankRes) {
                self::rollback();
                return false;
            }
            $data['reward_type'] = $type;
            $data['reward'] = $richReward;
        }
        self::commit();
        return $data;
    }

}

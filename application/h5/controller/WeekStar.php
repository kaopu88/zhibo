<?php
/**
 * Created by PhpStorm.
 * User: 李成
 * Date: 2020/8/13
 * Time: 10:08
 */

namespace app\h5\controller;

use app\admin\service\WeekStarGift;
use bxkj_common\DateTools;
use bxkj_common\RedisClient;
use think\Db;
use think\Exception;

class WeekStar extends Controller
{
    protected $mehtod = ['1' => 'StarUser', '2' => 'Consumer', '3' => 'System'];
    protected $config;

    public function __construct()
    {
        parent::__construct();
        try {
            $this->config = config('live.');
            $status = $this->config['week_star_status'];
            if ($status == 0) throw new Exception('周星活动未开启~', 1);
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * @return mixed
     */
    public function index()
    {
        $data = [];
        $now = date("Y-m-d");
        $w = date('w', strtotime($now));
        $startTime = strtotime("$now -" . ($w ? $w - 1 : 6) . ' days');//本周开始时间
        $endTime = strtotime(date("Y-m-d", $startTime) . "+7 days");//本周结束时间
        $lastStartTime = strtotime(date("Y-m-d", $startTime) . "-7 days");//上周开始时间

        $starGift = new WeekStarGift();
        $where['start_time'] = $startTime;
        $where['end_time'] = $endTime;
        $total = $starGift->getTotal($where);
        $data['now'] = $starGift->getList($where, 0, $total);

        $map['start_time'] = $lastStartTime;
        $map['end_time'] = $startTime;
        $lastTotal = $starGift->getTotal($map);
        $data['last'] = $starGift->getList($map, 0, $lastTotal);

        $this->assign('rank_reward_status', isset($this->config['rank_reward_status']) ? $this->config['rank_reward_status'] : 0);
        $this->assign('rich_reward_status', isset($this->config['rich_reward_status']) ? $this->config['rich_reward_status'] : 0);
        $this->assign('list', $data);
        return $this->fetch();
    }

    /**
     * 获取本周礼物排行
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getGiftRank()
    {
        $data = [];
        $params = request()->param();
        $giftId = $params['gift_id'];
        $type = empty($params['type']) ? 1 : $params['type'];

        if ($type != 3) {
            if (empty($giftId)) return json(['status' => 0, 'msg' => '礼物id不能为空']);
            $giftInfo = Db::name("gift")->where(["id" => $giftId])->find();
            if (empty($giftInfo)) return json(['status' => 0, 'msg' => '礼物不存在']);
        }

        $star = new \app\api\service\WeekStar();
        $method = $this->mehtod[$type];
        if (empty($method)) return ['code' => 0, 'msg' => '类型不存在'];
        if (!method_exists($star, 'get' . $method . 'List')) return json(['code' => 9103, 'msg' => '方法不存在']);
        $data = call_user_func_array([$star, 'get' . $method . 'List'], [$giftId]);

        return json(['status' => 1, 'msg' => '获取成功', 'data' => $data]);
    }

    public function getLastRank()
    {
        $data = [];
        $params = request()->param();
        $giftId = $params['gift_id'];
        if (empty($giftId)) return json(['status' => 0, 'msg' => '礼物id不能为空']);

        $giftInfo = Db::name("gift")->where(["id" => $giftId])->find();
        if (empty($giftInfo)) return json(['status' => 0, 'msg' => '礼物不存在']);

        $now = date("Y-m-d");
        $w = date('w', strtotime($now));
        $weekTime = strtotime("$now -" . ($w ? $w - 1 : 6) . ' days');//本周开始时间
        $startTime = strtotime(date("Y-m-d", $weekTime) . "-7 days");//上周开始时间
        $week = DateTools::getWeekNum($startTime);
        $redis = RedisClient::getInstance();
        $lastAnchorkey = "week_star:gift_id:" . $week . ':' . $giftId;
        $lastUserKey = "week_star_user:gift_id:" . $week . ':' . $giftId;
        $user = new \bxkj_module\service\User();
        $anchorList = $redis->zrevrange($lastAnchorkey, 0, 2, true);
        if (!empty($anchorList)) {
            foreach ($anchorList as $uid => $score) {
                $anchorData['gift_num'] = $score;
                $anchorData['user_id'] = $uid;
                $userInfo = $user->getUser($uid);
                $anchorData['nickname'] = $userInfo['nickname'];
                $anchorData['avatar'] = $userInfo['avatar'];
                $newAnchorlist[] = $anchorData;
            }
        }
        $data['anchor'] = $newAnchorlist ? $newAnchorlist : [];

        $userList = $redis->zrevrange($lastUserKey, 0, 2, true);
        if (!empty($userList)) {
            foreach ($userList as $uid => $score) {
                $userData['gift_num'] = $score;
                $userData['user_id'] = $uid;
                $userInfo = $user->getUser($uid);
                $userData['nickname'] = $userInfo['nickname'];
                $userData['avatar'] = $userInfo['avatar'];
                $newUserlist[] = $userData;
            }
        }
        $data['rich'] = $newUserlist ? $newUserlist : [];

        return json(['status' => 1, 'msg' => '获取成功', 'data' => $data]);
    }
}
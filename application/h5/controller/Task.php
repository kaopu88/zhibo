<?php

namespace app\h5\controller;

use app\api\controller\taoke\Withdraw;
use bxkj_module\service\User;
use bxkj_module\service\User_task;
use bxkj_module\service\UserPoint;
use think\Db;
use think\Request;

class Task extends Controller
{
    public function index(Request $request)
    {
        $params  = $request->param();
        $task    = new User_task();
        $user_id = (int)$params['user_id'];
        if (!empty($user_id)) {
            $user                     = new User();
            $user_info                = $user->getUser($user_id);
            $user_info['today_point'] = Db::name('user_point_log')->where(['user_id' => $user_id, 'change_type' => 'inc'])->whereBetweenTime('create_time', date("Y-m-d"))->sum('point');
            $today_play               = Db::name('user_task_log')->where(['user_id' => $user_id, 'task_type' => 'watchVideo'])->whereBetweenTime('create_time', date("Y-m-d"))->value('task_value');
            $user_info['today_play']  = !empty($today_play) ? floor(($today_play % 3600000) / 60000) : 0;
            $this->assign('user_info', $user_info);
        }
        $milletname = APP_REWARD_NAME;
        $this->assign('milletname', $milletname);
        $tasklist = $task->userTaskList($user_id, 1);
        $this->assign('user_id', $user_id);
        $this->assign('tasklist', $tasklist);
        return $this->fetch();
    }

    public function redeem(Request $request)
    {
        $params          = $request->param();
        $user_id         = (int)$params['user_id'];
        $cash_setting = config('app.cash_setting');
        $exchangePercent = (isset($cash_setting['exchange_percent']) && !empty($cash_setting['exchange_percent'])) ? $cash_setting['exchange_percent'] : 10;
        if (!empty($user_id)) {
            $user      = new User();
            $user_info = $user->getUser($user_id);
            $this->assign('user_info', $user_info);
        }
        $exchangNotice         = Db::name('article')->where('mark', 'exchange_explain')->find();
        $exchangExplain        = Db::name('article')->where('mark', 'exchange_notice')->find();
        $exchangeIntegral      = $cash_setting['exchange_integral'];
        $exchangeIntegralArray = explode(',', $exchangeIntegral);
        foreach ($exchangeIntegralArray as $k => $v) {
            $exchangeArray[] =
                ['number'   => floor($v / $exchangePercent),
                 'integral' => $v];
        }
        $diamonds   = floor($user_info['points'] / $exchangePercent);
        $milletname = APP_REWARD_NAME;
        $this->assign('milletname', $milletname);
        $this->assign('diamonds', $diamonds);
        $this->assign('exchangNotice', $exchangNotice);
        $this->assign('exchangExplain', $exchangExplain);
        $this->assign('exchangePercent', $exchangePercent);
        $this->assign('exchangeArray', $exchangeArray);
        $this->assign('user_id', $user_id);
        return $this->fetch();
    }

    public function daily_check(Request $request)
    {
        $params  = $request->param();
        $user_id = $params['user_id'];
        $task_id = $params['task_id'];
        // var_dump($task_id);die;
        if (empty($user_id) || empty($task_id)) return json(['status' => 0, 'msg' => '信息不完整']);
        $task = new User_task();
        $res  = $task->addUserCheck($user_id, $task_id);
        if ($res === false) return json(['status' => 0, 'msg' => $task->getError()->message]);
        return json(['status' => 1, 'data' => $res, 'msg' => '签到成功']);
    }

    //任务规则说明
    public function explain(Request $request)
    {
        $params  = $request->param();
        $user_id = (int)$params['user_id'];
        $explain = DB::name('article')->field('title, content')->where('mark', 'task_explain')->find();
        if (empty($explain)) {
            $explain = [
                'title'   => '活动规则',
                'content' => '暂无内容'
            ];
        }
        $this->assign('user_id', $user_id);
        $this->assign('explain', $explain);
        return $this->fetch();
    }

    /**
     * 领取积分
     */
    public function receive(Request $request)
    {
        $params  = $request->param();
        $user_id = $params['user_id'];
        $task_id = $params['task_id'];
        if (empty($user_id) || empty($task_id)) return json(['status' => 0, 'msg' => '信息不完整']);
        $userPoint = new \bxkj_module\service\Task();
        $res       = $userPoint->finish($user_id, $task_id);
        if (!$res) {
            return json(['status' => 0, 'msg' => '领取失败']);
        }
        return json(['status' => 1, 'data' => $res, 'msg' => '领取成功']);
    }

    /**
     * 测试
     */
    public function test(Request $request)
    {
//        $rest = systemSend(USERID, '我上次了一段视频请大家围观', '', 'http://1300599505.vod2.myqcloud.com/eee3b1eevodsh1300599505/ddb845315285890797540425741/sUe7kTxm3joA.mp4', ''
//            , '', 2, 1, '', 2,
//            '', 0, '', '',
//            '', 4, '16/9461d13ff4930a40ed1ec1f29386777b.jpeg', '视频描述', '莲花产业园', 1, '', '');
        $params  = $request->param();
        $user_id = $params['user_id'];
//        $taskMod = new  \app\taoke\service\Withdraw();
//        $data = [
//            'user_id' => $user_id,
//            'task_type' => 'followFriends',
//            'task_value' => 1,
//            'status' => 0
//        ];
        $taskMod = new \bxkj_module\service\Task();
        $data    = [
            'user_id'    => $user_id,
            'task_type'  => $params['task_type'],
            'task_value' => $params['task_value'],
            'status'     => $params['status'] ? $params['status'] : 0,
        ];
        $rest    = $taskMod->subTask($data);
        var_dump($rest);
    }

    /**
     * 兑换积分
     */
    public function exchangeIntegral(Request $request)
    {
        $params  = $request->param();
        $user_id = $params['user_id'];
        if (empty($user_id) || empty($params['point'])) return json(['status' => 0, 'msg' => '信息不完整']);
        if ($params['point'] <= 0)  return json(['status' => 0, 'msg' => '非法操作']);
        $usermsg = userMsg($user_id, 'user_id,points');
        if ($params['point'] > $usermsg['points']) {
            return json(['status' => 0, 'msg' => '您的积分不足']);
        }
        $point                 = new UserPoint();
        $params['change_type'] = 'exp';
        $params['source_id']   = 100;
        $params['type']        = "exchange";
        $is_millet = 1;
        $rest                  = $point->record('exchange', $params, $is_millet);
        if (!$rest) {
            return json(['status' => 0, 'msg' => '兑换失败']);
        }
        return json(['status' => 1, 'data' => $rest, 'msg' => '兑换成功']);
    }

    /**
     * 积分兑换记录
     */
    public function exchangeList(Request $request)
    {
        $params  = $request->param();
        $user_id = $params['user_id'];
        if (empty($user_id)) return json(['status' => 0, 'msg' => '信息不完整']);
        $point      = new UserPoint();
        $usermsg    = userMsg($user_id, 'user_id,points');
        $sum        = $point->sumQurey(['user_id' => $user_id, 'type' => 'exchange'], 'point');
        $milletname = APP_REWARD_NAME;
        $this->assign('milletname', $milletname);
        $this->assign('points', $usermsg['points']);
        $this->assign('pointsexchange', floor($usermsg['points'] / config('app.cash_setting.exchange_percent')));
        $this->assign('sum', $sum);
        $this->assign('user_id', $user_id);
        return $this->fetch();
    }

    /**
     * ajax 积分兑换记录
     */
    public function GetList(Request $request)
    {
        $params     = $request->param();
        $user_id    = $params['user_id'];
        $page_index = $params['page'] ? $params['page'] : 1;
        $page_size  = $params['page_size'] ? $params['page_size'] : 10;
        $point      = new UserPoint();
        $rest       = $point->pageQuery($page_index, $page_size, ['user_id' => $user_id], 'id desc', '*');
        foreach ($rest['data'] as $k => $v) {
            if ($v['change_type'] == 'inc') {
                $changetype = '+';
            } else {
                $changetype = '-';
            }
            $rest['data'][$k]['acttime'] = date('Y-m-d H:i:s', $v['create_time']);
            $rest['data'][$k]['point']   = $changetype . $v['point'];
        }
        return json(['status' => 1, 'data' => $rest, 'msg' => '获取成功']);
    }

    /**
     * ajax 积分兑换钻石记录
     */
    public function GetExchangeList(Request $request)
    {
        $params     = $request->param();
        $user_id    = $params['user_id'];
        $page_index = $params['page'] ? $params['page'] : 1;
        $page_size  = $params['page_size'] ? $params['page_size'] : 10;
        $point      = new UserPoint();
        $rest       = $point->pageQuery($page_index, $page_size, ['user_id' => $user_id, 'type' => 'exchange'], 'id desc', '*');
        foreach ($rest['data'] as $k => $v) {
            if ($v['change_type'] == 'inc') {
                $changetype = '+';
            } else {
                $changetype = '-';
            }
            $rest['data'][$k]['acttime'] = date('Y-m-d H:i:s', $v['create_time']);
            $rest['data'][$k]['point']   = $changetype . $v['point'];
        }
        return json(['status' => 1, 'data' => $rest, 'msg' => '获取成功']);
    }
}
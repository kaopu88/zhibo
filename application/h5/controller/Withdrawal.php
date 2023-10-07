<?php

namespace app\h5\controller;

use app\admin\service\MilletLog;
use app\api\service\Millet;
use bxkj_common\RedisClient;
use bxkj_module\service\Cash;
use bxkj_module\service\CashLog;
use bxkj_module\service\Tree;
use bxkj_module\service\User;
use bxkj_module\service\User_task;
use bxkj_module\service\UserPoint;
use bxkj_module\service\UserTaskLog;
use think\Db;
use think\Request;

class Withdrawal extends BxController
{
    protected $task_type = [
        'followFriends' => '关注好友',
        'postVideo' => '发布视频',
        'dailyLogin' => '每日登录',
        'watchVideo' => '观看视频',
        'shareVideo' => '分享视频',
        'inviteFriends'=>'邀请好友',
        'commentVideo' => '视频评论',
        'thumbsVideo' => '视频点赞',
        'score'      => '积分兑换',
        'video_reward'=>'视频奖励',


    ];
    public function earnings(Request $request)
    {
        $params = $request->param();
        //我的信息；金币收益；现金收益；
        $userId            = (int)$params['user_id'];
        $mydetail          = userMsg($userId, 'user_id,nickname,avatar,phone,level,remark_name,millet,total_millet,his_millet,cash,points');
        //累计收益
        if (empty($mydetail))
            $mydetail['millet'] = number_format($mydetail['millet'], 0);
        $cashModle = new CashLog();
        $sumcash   = $cashModle->sumcash($userId);
        $this->assign('sumcash', number_format($sumcash, '2'));
        $this->assign('user_id', $userId);
        $this->assign('userMsg', $mydetail);
        return $this->fetch();
    }

    public function millloglist(Request $request)
    {
        $params       = $request->param();
        $userId       = $params['user_id'];
        $page_index   = $params['page_index'] ? $params['page_index'] : 1;
        $page_size    = $params['page_size'] ? $params['page_size'] : 10;
        $milllogModel = new MilletLog();
        $milllogList  = $milllogModel->pageQuery($page_index, $page_size, ['user_id' => $userId], 'id desc', '*');
        foreach ($milllogList['data'] as $k => $v) {
            if ($v['type'] == 'inc') {
                $changetype = '+';
            } else {
                $changetype = '-';
            }
            $milllogList['data'][$k]['content'] = $this->task_type[$v['trade_type']];
            $milllogList['data'][$k]['acttime'] = date('Y-m-d H:i:s', $v['create_time']);
            $milllogList['data'][$k]['point']   = $changetype . $v['total'];
        }
       return json(['status' => 1, 'data' => $milllogList, 'msg' => '获取成功']);
    }

    public function pointloglist(Request $request)
    {
        $params     = $request->param();
        $userId     = $params['user_id'];
        $page_index = $params['page_index'] ? $params['page_index'] : 1;
        $page_size  = $params['page_size'] ? $params['page_size'] : 10;
        $point      = new UserPoint();
        $rest       = $point->pageQuery($page_index, $page_size, ['user_id' => $userId, 'change_type' => 'inc'], 'id desc', '*');
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

    public function cashloglist(Request $request)
    {

        $params      = $request->param();
        $userId      = $params['user_id'];
        $page_index  = $params['page_index'] ? $params['page_index'] : 1;
        $page_size   = $params['page_size'] ? $params['page_size'] : 10;
        $cashogModel = new  CashLog();
        $cashlogList = $cashogModel->pageQuery($page_index, $page_size, ['user_id' => $userId], 'id desc', '*');
        foreach ($cashlogList['data'] as $k => $v) {
            if ($v['type'] == 'inc') {
                $changetype = '+';
            } else {
                $changetype = '-';
            }
            $cashlogList['data'][$k]['content'] = $this->task_type[$v['trade_type']];
            $cashlogList['data'][$k]['acttime'] = date('Y-m-d H:i:s', $v['create_time']);
            $cashlogList['data'][$k]['total']   = $changetype . $v['total'];
        }
        return json(['status' => 1, 'data' => $cashlogList, 'msg' => '获取成功']);
    }

    public function withdrawal_activities(Request $request)
    {
        $params            = $request->param();
        $params['user_id'] = 10004364;
        $userId            = (int)$params['user_id'];
        $mydetail          = userMsg($userId, 'user_id,nickname,avatar,phone,level,remark_name,millet,total_millet,his_millet,cash,points');
        $task              = new User_task();
        $user_id           = (int)$params['user_id'];
        if (!empty($user_id)) {
            $user                     = new User();
            $user_info                = $user->getUser($user_id);
            $user_info['today_point'] = Db::name('user_point_log')->where(['user_id' => $user_id, 'change_type' => 'inc'])->whereBetweenTime('create_time', date("Y-m-d"))->sum('point');
            $today_play               = Db::name('user_task_log')->where(['user_id' => $user_id, 'task_type' => 'watchVideo'])->whereBetweenTime('create_time', date("Y-m-d"))->value('task_value');
            $user_info['today_play']  = !empty($today_play) ? floor(($today_play % 3600000) / 60000) : 0;
            $this->assign('user_info', $user_info);
        }
        $product    = Db::name('sys_config')->where(['mark' => 'product'])->value('value');
        $info1      = json_decode($product, true);
        $milletname = $info1['product_setting']['millet_name'];
        $this->assign('milletname', $milletname);
        $tasklist = $task->userTaskList($user_id, 0);
        foreach ($tasklist as $k => $v) {
            if (!empty($v['timearray'])) {
                $this->assign('_watchArray', json_encode($v['timearray']));
                $this->assign('arrayAll', $v['detail']['arrayAll']);
                $this->assign('steps', $v['detail']['timekey']);
                $this->assign('watchtips', $v['tips']);
            }
        }
        $this->assign('tasklist', $tasklist);
        $this->assign('user_id', $userId);
        $this->assign('userMsg', $mydetail);
        return $this->fetch();
    }

    public function strategy()
    {
        $mark       = 'money_strategy';
        $artService = new \app\admin\service\Article();
        $get        = input();
        $catTree    = new Tree('category');
        $catList    = $catTree->getCategoryByMark($mark);
        foreach ($catList as $k => $v) {
            $get['cat_id']           = $v['id'];
            $artList                 = $artService->getList($get, 0, 100);
            $catList[$k]['children'] = $artList;
        }
        $this->assign('_list', $catList);
        return $this->fetch();
    }

    public function invite_friends(Request $request)
    {
        //播报信息：活动说明：邀请码：我的邀请信息
        $params            = $request->param();
        $params['user_id'] = 10004364;
        $inventModel       = new Cash();
        $rest              = $inventModel->Queryreworldcash();
        foreach ($rest as $k => $v) {
            $adv[] = [
                'username' => userMsg($v['user_id'], 'user_id,avatar,nickname,gender')['nickname'],
                'reward'   => sprintf("%.2f", $v['total']) . '元现金',
            ];
        }
        if (empty($rest)) {
            $maskLogModel = new UserTaskLog();
            $rest         = $maskLogModel->Queryreword('inviteFriends', 100);
            foreach ($rest as $k1 => $v1) {
                $adv[] = [
                    'username' => userMsg($v['user_id'], 'user_id,avatar,nickname,gender')['nickname'],
                    'reward'   => sprintf("%.2f", $v['point']) . APP_REWARD_NAME,
                ];
            }
        }
        $mydetal = userMsg($params['user_id'], 'user_id,avatar,nickname,gender,invite_code');
        //邀请了多少好友，赚了多少钱
        $cashLogModel = new CashLog();
        $invent       = $cashLogModel->queryInvent($params['user_id']);
        if (!empty($invent)) {
            $mydetal['count']     = $invent['count'];
            $mydetal['inventSum'] = sprintf("%.2f", $invent['inventSum']);
        }
        $this->assign('_mydetail', $mydetal);
        $this->assign('_list', $adv);
        return $this->fetch();
    }

    public function activety_set()
    {
        return $this->fetch();
    }

    public function drawalCash(Request $request)
    {
        $params            = $request->param();
       //这里需要类型，金额

    }
}
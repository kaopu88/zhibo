<?php

namespace app\agent\controller;

use app\agent\service\AnchorKpi;
use bxkj_module\service\Trend;
use bxkj_common\DateTools;
use bxkj_common\RedisClient;
use think\Db;
use think\facade\Request;

class Anchor extends Controller
{
    public function index()
    {
        $userService = new \app\agent\service\Anchor();
        $get = input();
        $total = $userService->getIndexTotal($get);
        $page = $this->pageshow($total);
        $users = $userService->getIndex($get, $page->firstRow, $page->listRows);
        $this->assign('_list', $users);
        $this->assign('get', $get);
        return $this->fetch();
    }
    public function millet()
    {
        $get = input();
        $get['runit'] = $get['runit'] ? $get['runit'] : 'd';
        $get['rnum'] = $get['rnum'] ? $get['rnum'] : date('Y-m-d');
        $timeRangerConfig = DateTools::getTimeRangerConfig();
        $this->assign('time_ranger_json', json_encode($timeRangerConfig));
        $anchorKpi = new AnchorKpi();
        $total = $anchorKpi->getTotal($get);
        $page = $this->pageshow($total);
        $list = $anchorKpi->getMilletList($get, $page->firstRow, $page->listRows);
        $this->assign('_list', $list);
        $this->assign('get', $get);
        return $this->fetch();
    }

    public function get_suggests()
    {
        $anchorService = new \app\agent\service\Anchor();
        $result = $anchorService->getSuggests(input('keyword'));
        return json_success($result ? $result : []);
    }

    public function detail()
    {
        $userId = input('user_id');
        $userService = new \app\agent\service\User();
        $user = $userService->getInfo($userId);
        if (empty($user)) $this->error('用户不存在');
        $anchorService = new \app\agent\service\Anchor();
        $anchor = $anchorService->getInfo($userId);
        if (empty($anchor)) $this->error('主播不存在');
        $this->assign('anchor', $anchor);
        $this->assign('user', $user);

        //获得米粒记录
        $anchorGet = ['anchor_uid' => $userId];
        $kpiMilletService = new \app\agent\service\KpiMillet();
        $total = $kpiMilletService->getTotal($anchorGet);
        $list = $kpiMilletService->getList($anchorGet, 0, 5);
        $this->assign('millet_list', $list);
        $this->assign('millet_total', $total);

        $redis = RedisClient::getInstance();
        $day = date('Ymd');
        $dayActive = $redis->zScore("kpi:anchor:all:active:d:{$day}", $anchor['user_id']);
        $month = date('Ym');
        $monthActive = $redis->zScore("kpi:anchor:all:active:m:{$month}", $anchor['user_id']);
        $this->assign('day_active', $dayActive ? $dayActive : 0);
        $this->assign('month_active', $monthActive ? $monthActive : 0);
        return $this->fetch();
    }

    public function get_millet_trend()
    {
        if (Request::isPost()) {
            $userId = input('user_id');
            $start = input('start');
            $end = input('end');
            $unit = input('unit');
            if (empty($start) || empty($end) || empty($unit) || empty($userId))
                $this->error('请检查参数是否正确');
            if (DateTools::strToTime($start) > DateTools::strToTime($end))
                $this->error('时间段设置不正确，可能是结束时间小于开始时间');
            $AGENT_ID = AGENT_ID;
            $types = array(
                array('mark' => "kpi:anchor:{$AGENT_ID}:millet", 'name' => '获得'.APP_MILLET_NAME, 'member' => $userId),
            );
            $trend = new Trend('');
            $data = $trend->getSeriesData($types, $start, $end, $unit);
            $data['title'] = DateTools::getRangeTitle($start, $end, $unit) . '数据';
            $this->success('获取成功', $data);
        }
    }

    public function cancel()
    {
        $userId = input('user_id');
        if (empty($userId)) $this->error('请选择用户');
        $anchorService = new \app\agent\service\Anchor();
        $res = $anchorService->cancel([$userId]);
        if (!$res) $this->error($anchorService->getError());
        $this->success('已取消主播');
    }

    public function location()
    {
        if (Request::isGet()) {
            $userId = input('user_id');
            if (empty($userId)) $this->error('请选择用户');
            $anchor = new \app\agent\service\Anchor();
            $data = $anchor->getLocation($userId);
            $data['user_id'] = $userId;
            return json_success($data);
        } else {
            $post = input();
            $anchor = new \app\agent\service\Anchor();
            $res = $anchor->setLocation($post);
            if (!$res) $this->error($anchor->getError());
            return json_success([], '设置成功');
        }
    }

    public function cash()
    {
        if (Request::isGet()) {
            $userId = input('user_id');
            if (empty($userId)) $this->error('请选择用户');
            $anchor= Db::name('anchor')->where(['user_id' => $userId, 'agent_id' => AGENT_ID])->find();
            if (empty($anchor)) $this->error('主播不存在');
            return json_success($anchor);
        } else {
            $post = input();
            $anchor= Db::name('anchor')->where(['user_id' => $post['user_id'], 'agent_id' => AGENT_ID])->find();
            if (empty($anchor)) $this->error('主播不存在');
            $cash_rate = $post['cash_rate'];
            if ($cash_rate < 0 || $cash_rate > 1) $this->error('比例设置应在0到1之间');
            $anchorRes= Db::name('anchor')->where(['user_id' => $anchor['user_id']])->update(['cash_rate' => $cash_rate]);
            return json_success([], '设置成功');
        }
    }
}

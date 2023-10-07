<?php

namespace app\agent\controller;

use app\agent\service\PromoterKpi;
use app\agent\service\PromotionExitApply;
use  app\agent\service\Trend;
use bxkj_common\DateTools;
use think\Db;
use think\facade\Request;

class Promoter extends Controller
{

    public function index()
    {
        $promoterService = new \app\agent\service\Promoter();
        $get = input();
        $total = $promoterService->getIndexTotal($get);
        $page = $this->pageshow($total);
        $users = $promoterService->getIndex($get, $page->firstRow, $page->listRows);
        $this->assign('_list', $users);
        $this->assign('get', $get);
        return $this->fetch();
    }

    public function find()
    {
        $promoterService = new \app\agent\service\Promoter();
        $get = input();
        $total = $promoterService->getIndexTotal($get);
        $page = $this->pageshow($total);
        $users = $promoterService->getIndex($get, $page->firstRow, $page->listRows);
        $this->assign('_list', $users);
        $this->assign('get', $get);
        return $this->fetch();
    }

    public function cons()
    {
        $get = input();
        $get['runit'] = $get['runit'] ? $get['runit'] : 'd';
        $get['rnum'] = $get['rnum'] ? $get['rnum'] : date('Y-m-d');
        $timeRangerConfig = DateTools::getTimeRangerConfig();
        $this->assign('time_ranger_json', json_encode($timeRangerConfig));
        $promoterKpi = new PromoterKpi();
        $total = $promoterKpi->getTotal($get);
        $page = $this->pageshow($total);
        $list = $promoterKpi->getConsList($get, $page->firstRow, $page->listRows);
        $this->assign('_list', $list);
        $this->assign('get', $get);
        return $this->fetch();
    }

    public function get_suggests()
    {
        $promoterService = new \app\agent\service\Promoter();
        $result = $promoterService->getSuggests(input('keyword'));
        return json_success($result ? $result : []);
    }

    //TA的客户
    public function clients()
    {
        $userService = new \app\agent\service\User();
        $get = input();
        if (empty($get['promoter_uid'])) $this->error('请选择' . config('app.agent_setting.promoter_name'));
        $total = $userService->getClientTotal($get);
        $page = $this->pageshow($total);
        $users = $userService->getClientList($get, $page->firstRow, $page->listRows);
        $this->assign('promoter_uid', $get['promoter_uid']);
        $this->assign('_list', $users);
        return $this->fetch();
    }

    public function detail()
    {
        $userId = input('user_id');
        $userService = new \app\agent\service\User();
        $user = $userService->getInfo($userId);
        if (empty($user)) $this->error('用户不存在');
        $promoterService = new \app\agent\service\Promoter();
        $promoter = $promoterService->getInfo($userId);
        if (empty($promoter)) $this->error(config('app.agent_setting.promoter_name') . '不存在');
        $this->assign('promoter', $promoter);
        $this->assign('user', $user);

        //消费记录
        $consGet = ['promoter_uid' => $userId];
        $kpiConsService = new \app\agent\service\KpiCons();
        $consTotal = $kpiConsService->getTotal($consGet);
        $consList = $kpiConsService->getList($consGet, 0, 5);
        $this->assign('cons_list', $consList);
        $this->assign('cons_total', $consTotal);

        //拉新记录
        $fansGet = ['promoter_uid' => $userId];
        $kpiFansService = new \app\agent\service\KpiFans();
        $consTotal = $kpiFansService->getTotal($fansGet);
        $consList = $kpiFansService->getList($fansGet, 0, 5);
        $this->assign('fans_list', $consList);
        $this->assign('fans_total', $consTotal);

        return $this->fetch();
    }


    public function get_cons_trend()
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
                array('mark' => "promoter:cons:{$AGENT_ID}", 'name' => '客消（' . APP_BEAN_NAME . '）', 'member' => $userId),
            );
            $trend = new Trend('');
            $data = $trend->getSeriesData($types, $start, $end, $unit);
            $data['title'] = DateTools::getRangeTitle($start, $end, $unit) . '数据';
            $this->success('获取成功', $data);
        }
    }

    public function correct_client_num()
    {
        $userId = input('user_id');
        if (empty($userId)) $this->error('请选择用户');
        $num = Db::name('promotion_relation')->where([
            'promoter_uid' => $userId,
            'agent_id' => AGENT_ID
        ])->count();
        $res = Db::name('promoter')->where(['user_id' => $userId])->update([
            'client_num' => $num ? $num : 0
        ]);
        if (!$res) $this->error('校正完成，数据没有变化');
        $this->success('校正成功');
    }

    public function cancel()
    {
        $userId = input('user_id');
        if (empty($userId)) $this->error('请选择用户');
        $promoterService = new \app\agent\service\Promoter();
        $res = $promoterService->cancel([$userId], null, [
            'type' => 'agent',
            'id' => AID
        ]);
        if (!$res) $this->error($promoterService->getError());
        $this->success('已取消'.config('app.agent_setting.promoter_name').'身份');
    }

    public function create()
    {
        $userId = input('user_id');
        $agentId = input('agent_id');
        $force = input('force', '0');
        if (empty($userId)) $this->error('请选择用户');
        if (empty($agentId)) $this->error('请选择'.config('app.agent_setting.agent_name'));
        $promoterService = new \app\agent\service\Promoter();
        $res = $promoterService->create([
            'agent_id' => $agentId,
            'user_id' => $userId,
            'force' => $force,
            'admin' => [
                'type' => 'agent',
                'id' => AID
            ]]);
        if (!$res) $this->error($promoterService->getError());
        $this->success('已设置为'.config('app.agent_setting.promoter_name'));
    }

    //绑定申请列表
    public function apply()
    {
        $promoterApplyService = new \app\agent\service\PromoterApply();
        $get = input();
        $total = $promoterApplyService->getTotal($get);
        $page = $this->pageshow($total);
        $list = $promoterApplyService->getList($get, $page->firstRow, $page->listRows);
        $this->assign('_list', $list);
        return $this->fetch();
    }

    /**
     * 公会审核绑定用户
     *
     */
    public function review()
    {
        $id = input('id');
        $status = input('status');
        $reason = input('reason');
        $is_transfer = input('is_transfer', 1);
        if (empty($id)) $this->error('请选择一条审核记录');
        $promoterApplyService = new \app\agent\service\PromoterApply();
        $reslut = $promoterApplyService->approved($id, $status, $reason, $is_transfer);
        if (!$reslut) $this->error($promoterApplyService->getError());
        return $this->success('审核完成');
    }

    public function approved()
    {
        $ids = input('ids');
        $status = input('status');
        $reason = input('reason');
        if (empty($ids)) return $this->error('请选择一条审核记录');
        $promoterApplyService = new \app\agent\service\PromoterApply();
        $num = 0;
        foreach ($ids as $id) {
            $reslut = $promoterApplyService->approved($id, $status, $reason);
            if ($reslut) {
                $num++;
            }
        }
        $txt = $status == 1 ? '审核' : '驳回';
        return $this->success($txt . '成功,共完成' . $num . '条');
    }

    public function exitapply()
    {
        $get = input();
        $promoterExitService = new PromotionExitApply();
        $total = $promoterExitService->getTotal($get);
        $page = $this->pageshow($total);
        $list = $promoterExitService->getList($get, $page->firstRow, $page->listRows);
        $this->assign('_list', $list);
        return $this->fetch();
    }

    public function exitreview()
    {
        $id = input('id');
        $status = input('status');
        $reason = input('reason');
        if (empty($id)) $this->error('请选择一条审核记录');
        $promoterExitService = new PromotionExitApply();
        $reslut = $promoterExitService->approved($id, $status, $reason);
        if (!$reslut) $this->error($promoterExitService->getError());
        return $this->success('审核完成');
    }

    public function exitapproved()
    {
        $ids = input('ids');
        $status = input('status');
        $reason = input('reason');
        if (empty($ids)) return $this->error('请选择一条审核记录');
        $promoterExitService = new PromotionExitApply();
        $num = 0;
        foreach ($ids as $id) {
            $reslut = $promoterExitService->approved($id, $status, $reason);
            if ($reslut) {
                $num++;
            }
        }
        $txt = $status == 1 ? '通过' : '驳回';
        return $this->success($txt . '成功,共完成' . $num . '条');
    }
}

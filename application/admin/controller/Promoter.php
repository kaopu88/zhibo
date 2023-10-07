<?php

namespace app\admin\controller;

use bxkj_common\DateTools;
use think\Db;
use think\facade\Request;

class Promoter extends Controller
{

    public function index()
    {
        $this->checkAuth('admin:promoter:select');
        $promoterService = new \app\admin\service\Promoter();
        $get = input();
        $total = $promoterService->getProTotal($get);
        $page = $this->pageshow($total);
        $users = $promoterService->getProList($get, $page->firstRow, $page->listRows);
        $this->assign('_list', $users);
        return $this->fetch();
    }

    public function detail()
    {
        $this->checkAuth('admin:promoter:select');
        $userId = input('user_id');
        $userService = new \app\admin\service\User();
        $user = $userService->getInfo($userId);
        if (empty($user)) $this->error('用户不存在');
        $promoterService = new \app\admin\service\Promoter();
        $promoter = $promoterService->getInfo($userId);
        if (empty($promoter)) $this->error(config('app.agent_setting.promoter_name') . '不存在');
        $this->assign('promoter', $promoter);
        $this->assign('user', $user);

        //消费记录
        $consGet = ['promoter_uid' => $userId];
        $kpiConsService = new \app\admin\service\KpiCons();
        $consTotal = $kpiConsService->getTotal($consGet);
        $consList = $kpiConsService->getList($consGet, 0, 5);
        $this->assign('cons_list', $consList);
        $this->assign('cons_total', $consTotal);

        //拉新记录
        $fansGet = ['promoter_uid' => $userId];
        $kpiFansService = new \app\admin\service\KpiFans();
        $consTotal = $kpiFansService->getTotal($fansGet);
        $consList = $kpiFansService->getList($fansGet, 0, 5);
        $this->assign('fans_list', $consList);
        $this->assign('fans_total', $consTotal);

        return $this->fetch();
    }

    public function get_suggests()
    {
        $promoterService = new \app\admin\service\Promoter();
        $result = $promoterService->getSuggests(input('keyword'));
        return json_success($result ? $result : []);
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
            $promoter = Db::name('promoter')->where(['user_id' => $userId])->find();
            $types = array(
                array('mark' => "promoter:cons:{$promoter['agent_id']}", 'name' => '客消（' . APP_BEAN_NAME . '）', 'member' => $userId),
            );
            $trend = new \app\admin\service\Trend('');
            $data = $trend->getSeriesData($types, $start, $end, $unit);
            $data['title'] = DateTools::getRangeTitle($start, $end, $unit) . '数据';
            $this->success('获取成功', $data);
        }
    }

    public function correct_client_num()
    {
        $userId = input('user_id');
        if (empty($userId)) $this->error('请选择' . config('app.agent_setting.promoter_name'));
        $promoter = Db::name('promoter')->where(['user_id' => $userId])->find();
        if (empty($promoter)) $this->error(config('app.agent_setting.promoter_name').'不存在');
        $where = ['promoter_uid' => $userId, 'agent_id' => $promoter['agent_id']];
        $num = Db::name('promotion_relation')->where($where)->count();
        $res = Db::name('promoter')->where(['user_id' => $userId])->update([
            'client_num' => $num ? $num : 0
        ]);
        if (!$res) $this->error('校正完成，数据没有变化');
        alog("user.promoter.correct", '校正经纪人USER_ID： '.$userId.' 客户数据');
        $this->success('校正成功');
    }

    //TA的客户
    public function clients()
    {
        $this->checkAuth('admin:promoter:select');
        $userService = new \app\admin\service\User();
        $get = input();
        if (empty($get['promoter_uid'])) $this->error('请选择' . config('app.agent_setting.promoter_name'));
        $total = $userService->getClientTotal($get);
        $page = $this->pageshow($total);
        $users = $userService->getClientList($get, $page->firstRow, $page->listRows);
        $this->assign('promoter_uid', $get['promoter_uid']);
        $this->assign('_list', $users);
        return $this->fetch();
    }

    //取消config('app.agent_setting.promoter_name')身份
    public function cancel()
    {
        $userId = input('user_id');
        if (empty($userId)) $this->error('请选择用户');
        $promoterService = new \app\admin\service\Promoter();
        $res = $promoterService->cancel([$userId], null, [
            'type' => 'erp',
            'id' => AID
        ]);
        if (!$res) $this->error($promoterService->getError());
        alog("user.promoter.cancel", '取消 USER_ID：'.$userId." 经纪人身份");
        $this->success('已取消' . config('app.agent_setting.promoter_name') . '身份');
    }

    //创建config('app.agent_setting.promoter_name')身份
    public function create()
    {
        $userId = input('user_id');
        $agentId = input('agent_id');
        $force = input('force', '0');
        if (empty($userId)) $this->error('请选择用户');
        if (empty($agentId)) $this->error('请选择' . config('app.agent_setting.agent_name'));
        $promoterService = new \app\admin\service\Promoter();
        $res = $promoterService->create([
            'agent_id' => $agentId,
            'user_id' => $userId,
            'force' => $force,
            'admin' => [
                'type' => 'erp',
                'id' => AID
            ]]);
        if (!$res) $this->error($promoterService->getError());
        alog("user.promoter.add", '添加经纪人 USER_ID：'.$userId);
        $this->success('已设置为' . config('app.agent_setting.promoter_name'));
    }

    public function tranfer()
    {
        $userId = input('user_id');
        if (empty($userId)) $this->error('请选择用户');
        $promoterService = new \app\admin\service\Promoter();
        $relation = new \app\admin\service\PromotionRelation();
        $promoter = $promoterService->getInfo($userId);
        if (empty($promoter)) $this->error(config('app.agent_setting.promoter_name') . '不存在');
        $res = $relation->tranfer($userId);
        if ($res['code'] != 200) $this->error($res['msg']);
        $agentId = $res['data']['agent_id'];
        $res = Db::name('promoter')->where(['user_id' => $userId])->update(['agent_id' => $agentId]);
        if (!$res) {
            $this->error('操作失败');
        }
        alog("user.promoter.transfer", '转移经纪人 USER_ID：'.$userId." 客户");
        $this->success('成功转移' . config('app.agent_setting.promoter_name'));
    }
}

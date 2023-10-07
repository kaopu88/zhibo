<?php

namespace app\admin\controller;

use app\admin\service\AgentKpi;
use bxkj_common\DateTools;
use think\Db;
use think\facade\Request;

class Agent extends Controller
{
    public function home()
    {
        return $this->fetch();
    }

    public function index()
    {
        $this->checkAuth('admin:agent:select');
        $get = input();
        $agentService = new \app\admin\service\Agent();
        $total = $agentService->getTotal($get);
        $page = $this->pageshow($total);
        $list = $agentService->getList($get, $page->firstRow, $page->listRows);
        $this->assign('_list', $list);
        return $this->fetch();
    }

    public function my()
    {
        $this->checkAuth('admin:agent:select_my');
        $get = input();
        $get['aid'] = AID;
        $agentService = new \app\admin\service\Agent();
        $total = $agentService->getTotal($get);
        $page = $this->pageshow($total);
        $list = $agentService->getList($get, $page->firstRow, $page->listRows);
        $this->assign('aid', AID);
        $this->assign('_list', $list);
        return $this->fetch();
    }

    public function add()
    {
        $this->checkAuth('admin:agent:add');
        if (Request::isPost()) {
            $adminService = new \app\admin\service\Agent();
            $id = $adminService->add(Request::post());
            if (!$id) $this->error($adminService->getError());
            $url = url('agent/set_root', ['id' => $id]);
            $redirect = input('redirect');
            alog("sociaty.agent.add", '新增公会 ID：'.$id);
            $this->success('添加成功,请设置登录主账号', $id, $url . ($redirect ? '?redirect=' . $redirect : ''));
        } else {
            $admins = Db::name('admin')->where(['status' => 1, 'delete_time' => null])->select();
            $aid = input('aid');
            $info = array();
            if ($aid) {
                $info['aid'] = $aid;
            }
            $this->assign('_info', $info);
            $this->assign('admins', $admins);
            return $this->fetch();
        }
    }

    public function del()
    {
        $this->checkAuth('admin:agent:del');
        $ids = get_request_ids();
        $adminService = new \app\admin\service\Agent();
        $id = $adminService->delete($ids);
        if (!$id) $this->error($adminService->getError());
        alog("sociaty.agent.del", '删除公会 ID：'.implode(",", $ids));
        $this->success('删除成功');
    }

    public function set_root()
    {
        $this->checkAuth('admin:agent:add');
        if (Request::isPost()) {
            $agentAdminService = new \app\admin\service\AgentAdmin();
            $id = $agentAdminService->setRoot(Request::post());
            if (!$id) $this->error($agentAdminService->getError());
            alog("sociaty.agent.edit", '设置主账号 ID：'.$id);
            $this->success('设置成功');
        } else {
            $id = input('id');
            if (empty($id)) $this->error('请选择'.config('app.agent_setting.agent_name'));
            $agentInfo = Db::name('agent')->where(['id' => $id, 'delete_time' => null])->find();
            if ($agentInfo['root_id']) $this->error('已设置主账号');
            $this->assign('agent_info', $agentInfo);
            $info['agent_id'] = $agentInfo['id'];
            $info['phone'] = $agentInfo['contact_phone'] ? $agentInfo['contact_phone'] : '';
            $info['username'] = $agentInfo['name'];
            if(!empty($agentInfo['temppass'])){
                $info['password'] = $agentInfo['temppass'];
                $info['confirm_password'] = $agentInfo['temppass'];

            }
            $this->assign('_info', $info);
            return $this->fetch();
        }
    }


    public function edit()
    {
        $this->checkAuth('admin:agent:update');
        $id = input('id');
        $agentService = new \app\admin\service\Agent();
        if (Request::isPost()) {
            $post = Request::post();

            if (!empty($post['password'])) {
                $agentInfo = Db::name('agent_admin')->where(['agent_id' => $post['id'], 'is_root' => '1'])->find();

                if (!empty($agentInfo)) {
                    $data['salt'] = sha1(uniqid() . get_ucode());
                    $data['password'] = sha1($post['password'] . $data['salt']);
                    Db::name('agent_admin')->where(['id' => $agentInfo['id']])->update($data);
                }
            }

            $num = $agentService->update($post);
            if (!$num) $this->error($agentService->getError());
            alog("sociaty.agent.edit", '编辑公会 ID：'.$post['id']);
            $this->success('编辑成功');
        } else {
            $info = $agentService->getInfo($id);
            if (empty($info)) $this->error(config('app.agent_setting.agent_name').'不存在');
            $admins = Db::name('admin')->where(['status' => 1, 'delete_time' => null])->select();
            $this->assign('admins', $admins);
            $this->assign('_info', $info);
            return $this->fetch('add');
        }
    }

    public function change_status()
    {
        $this->checkAuth('admin:agent:update');
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择'.config('app.agent_setting.agent_name'));
        $agent_id = $ids[0];
        $status = input('status');
        $agentService = new \app\admin\service\Agent();
        $result = $agentService->change_status($agent_id, $status);
        if (!$result) $this->error($agentService->getError());
        alog("sociaty.agent.edit", '编辑公会 ID：'.implode(",", $ids)." 修改状态：".($status == 1 ? "启用" : "禁用"));
        $this->success('更新成功');
    }

    public function change_cash_on()
    {
        $this->checkAuth('admin:agent:update');
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择'.config('app.agent_setting.agent_name'));
        $agent_id = $ids[0];
        $cash_on = input('cash_on');
        $result = Db::name('agent')->where(['id' => $agent_id])->update(['cash_on' => $cash_on]);
        if (!$result) $this->error('更新失败');
        alog("sociaty.agent.edit", '编辑公会 ID：'.implode(",", $ids)." 修改提现状态：".($cash_on == 1 ? "启用" : "禁用"));
        $this->success('更新成功');
    }

    //传送到config('app.agent_setting.agent_name')后台
    public function transfer()
    {
        $this->checkAuth('admin:agent:transfer');
        $id = input('id');
        if (empty($id)) $this->error('请选择'.config('app.agent_setting.agent_name'));
        $params['time'] = time();
        $params['code'] = get_ucode(8, '1a');
        $params['id'] = $id;
        $params['sign'] = generate_sign($params, TRANSFER_AGENT_KEY);
        $url = url('@agent/account/transfer', $params);
        return redirect($url);
    }

    public function save_logo()
    {
        $this->checkAuth('admin:agent:update,admin:agent:add');
        $id = input('id');
        $logo = input('logo');
        if (empty($id)) $this->error('请选择'.config('app.agent_setting.agent_name'));
        if (empty($logo)) $this->error('logo地址不能为空');
        if (!validate_qiniu_url($logo, 'agent_logo', ['agent_id' => $id]))
            $this->error('logo地址不合法');
        $num = Db::name('agent')->where('id', $id)->update(['logo' => $logo]);
        if (!$num) $this->error('保存失败');
        alog("sociaty.agent.edit", '编辑公会 ID：'.$id." 保存logo");
        $this->success('保存成功');
    }

    public function save_remark()
    {
        $this->checkAuth('admin:agent:update,admin:agent:add');
        $id = input('id');
        $remark = input('remark');
        if (empty($id)) $this->error('请选择'.config('app.agent_setting.agent_name'));
        $num = Db::name('agent')->where('id', $id)->update(['remark' => $remark]);
        if (!$num) $this->error('保存失败');
        alog("sociaty.agent.edit", '编辑公会 ID：'.$id. " 保存备注");
        $this->success('保存成功');
    }

    //查询客消
    public function cons()
    {
        $this->checkAuth('admin:agent:select_cons,admin:agent:select_millet');
        $get = input();
        $get['runit'] = $get['runit'] ? $get['runit'] : 'd'; //d 按日查询 f按半月旬查询 m按月查询 total历史累计查询
        $get['rnum'] = $get['rnum'] ? $get['rnum'] : date('Y-m-d'); //日期
        $this->assign('get', $get);
        $timeRangerConfig = DateTools::getTimeRangerConfig();
        $this->assign('time_ranger_json', json_encode($timeRangerConfig));
        $agentApi = new AgentKpi();
        if (AID != 1) {
            $get['aid'] = AID;
        }
        $total = $agentApi->getTotal($get);
        $page = $this->pageshow($total);
        $list = $agentApi->getConsList($get, $page->firstRow, $page->listRows);
        $total = 0;
        if (AID != 1 && !empty($list)) {
            foreach ($list as $key => $value) {
                $total += $value['recharge_num'];
            }
        } else {
            $total = $agentApi->getTotalRecharge($get['runit'], $get['rnum']);
        }
        $this->assign('aid', AID);
        $this->assign('total', $total ? $total : 0);
        $this->assign('_list', $list ? $list : []);
        return $this->fetch();
    }

    //查询米粒 暂未用上
    public function millet()
    {
        $this->checkAuth('admin:agent:select_millet');
        $get = input();
        $get['runit'] = $get['runit'] ? $get['runit'] : 'd';
        $get['rnum'] = $get['rnum'] ? $get['rnum'] : date('Y-m-d');
        $this->assign('get', $get);
        $timeRangerConfig = DateTools::getTimeRangerConfig();
        $this->assign('time_ranger_json', json_encode($timeRangerConfig));
        $agentApi = new AgentKpi();
        $total = $agentApi->getTotal($get);
        $page = $this->pageshow($total);
        $list = $agentApi->getMilletList($get, $page->firstRow, $page->listRows);
        $this->assign('_list', $list ? $list : []);
        return $this->fetch();
    }

    public function detail()
    {
        $this->checkAuth('admin:agent:select');
        $this->error(config('app.agent_setting.agent_name').'详情页正在开发中');
    }

    public function manager()
    {
        $this->checkAuth('admin:agent:update');
        $id = input('id');
        if (empty($id)) $this->error('请选择'.config('app.agent_setting.agent_name'));
        $info = Db::name('agent')->where('id', $id)->find();
        if (empty($info)) $this->error(config('app.agent_setting.agent_name').'不存在');
        $info['client_num'] = Db::name('promotion_relation')->where(['agent_id' => $info['id']])->count();
        $info['anchor_num'] = Db::name('anchor')->where(['agent_id' => $info['id']])->count();
        $info['promoter_num'] = Db::name('promoter')->where(['agent_id' => $info['id']])->count();
        $info['isvirtual_num'] = Db::name('promotion_relation')
            ->alias('pr')
            ->join('__USER__ user', 'pr.user_id = user.user_id')
            ->where(['pr.agent_id' => $info['id'], 'delete_time' => null, 'isvirtual' => '1'])->count();
        $this->assign('_info', $info);
        return $this->fetch();
    }

    public function handler()
    {
        $this->checkAuth('admin:agent:audit');
        $post = Request::post();
        if (empty($post['id'])) $this->error('请选择申请记录');
        $viewbackService =   new \app\admin\service\Agent();
        $result = $viewbackService->handler($post, AID);
        if (!$result) return $this->error($viewbackService->getError());
        alog("operate.agent.audit", "工会申请审核 ID:".$post['id']);
        $this->success('处理成功');
    }

    //查看申请信息
    public function show()
    {
        $this->checkAuth('admin:agent:show');
        $id = input('id');
        $agentService = new \app\admin\service\Agent();

            $info = $agentService->getInfo($id);
            if (empty($info)) $this->error(config('app.agent_setting.agent_name').'不存在');
            $admins = Db::name('admin')->where(['status' => 1, 'delete_time' => null])->select();
            $this->assign('admins', $admins);
            $this->assign('_info', $info);
            return $this->fetch('show');

    }
}

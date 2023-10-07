<?php

namespace app\agent\controller;


use app\agent\service\AgentKpi;
use bxkj_common\CoreSdk;
use bxkj_common\DateTools;
use think\Db;
use think\facade\Request;

class Agent extends Controller
{

    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        if ($this->agent['add_sec'] != '1') {
            $this->error('您还没有二级'.config('app.agent_setting.agent_name').'管理权限');
        }
        $get = input();
        $get['query'] = 'child';
        $agentService = new \app\agent\service\Agent();
        $total = $agentService->getTotal($get);
        $page = $this->pageshow($total);
        $list = $agentService->getList($get, $page->firstRow, $page->listRows);
        $this->assign('_list', $list);
        return $this->fetch();
    }

    public function add()
    {
        if ($this->agent['add_sec'] != '1') {
            $this->error('您还没有二级'.config('app.agent_setting.agent_name').'管理权限');
        }
        if (Request::isPost()) {
            $agentService = new \app\agent\service\Agent();
            $id = $agentService->add(Request::post());
            if (!$id) $this->error($agentService->getError());
            $url = url('agent/set_root', ['id' => $id]);
            $redirect = input('redirect');
            $this->success('添加成功,请设置登录主账号', $id, $url . ($redirect ? '?redirect=' . $redirect : ''));
        } else {
            $info = [
                'max_anchor_num' => $this->agent['max_anchor_num'],
                'max_promoter_num' => $this->agent['max_promoter_num'],
                'max_virtual_num' => $this->agent['max_virtual_num'],
                'add_anchor' => $this->agent['add_anchor'],
                'add_promoter' => $this->agent['add_promoter'],
                'add_virtual' => $this->agent['add_virtual'],
            ];
            $this->assign('_info', $info);
            return $this->fetch();
        }
    }

    public function edit()
    {
        $id = input('id');
        $agentService = new \app\agent\service\Agent();
        if (Request::isPost()) {
            $num = $agentService->update(Request::post());
            if (!$num) $this->error($agentService->getError());
            $this->success('编辑成功');
        } else {
            $info = $agentService->getInfo($id, AGENT_ID);
            if (empty($info)) $this->error(config('app.agent_setting.agent_name').'不存在');
            $this->assign('_info', $info);
            return $this->fetch('add');
        }
    }

    public function set_root()
    {
        if (Request::isPost()) {
            $post = Request::post();
            $core = new CoreSdk();
            if (empty($post['phone'])) $this->error('请输入手机号');
            $res = $core->post('common/check_sms_code', array(
                'phone' => $post['phone'],
                'scene' => 'set_root',
                'code' => $post['code']
            ));
            if (!$res) $this->error($core->getError());
            unset($post['code']);
            $agentAdminService = new \app\agent\service\AgentAdmin();
            $id = $agentAdminService->setRoot($post, AGENT_ID);
            if (!$id) $this->error($agentAdminService->getError());
            $this->success('设置成功');
        } else {
            $this->lastSendTime();
            $id = input('id');
            if (empty($id)) $this->error('请选择'.config('app.agent_setting.agent_name'));
            $agentInfo = Db::name('agent')->where(['id' => $id, 'delete_time' => null, 'pid' => AGENT_ID])->find();
            if (empty($agentInfo)) $this->error(config('app.agent_setting.agent_name').'不存在');
            if ($agentInfo['root_id']) $this->error('已设置主账号');
            $this->assign('agent_info', $agentInfo);
            $info['agent_id'] = $agentInfo['id'];
            $info['phone'] = $agentInfo['contact_phone'] ? $agentInfo['contact_phone'] : '';
            $info['username'] = $agentInfo['name'];
            $this->assign('_info', $info);
            return $this->fetch();
        }
    }

    public function change_status()
    {
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择'.config('app.agent_setting.agent_name'));
        $status = input('status');
        $where['pid'] = AGENT_ID;
        $where['delete_time'] = null;
        $result = Db::name('agent')->where($where)->whereIn('id', $ids)->update(array('status' => $status));
        if (!$result) $this->error('更新失败');
        $this->success('更新成功');
    }

    //传送到config('app.agent_setting.agent_name')后台
    public function transfer()
    {
        $id = input('id');
        if (empty($id)) $this->error('请选择'.config('app.agent_setting.agent_name'));
        $where['pid'] = AGENT_ID;
        $where['id'] = $id;
        $where['delete_time'] = null;
        $agent = Db::name('agent')->where($where)->find();
        if (empty($agent)) $this->error(config('app.agent_setting.agent_name').'不存在');
        $params['time'] = time();
        $params['code'] = get_ucode(8, '1a');
        $params['id'] = $id;
        $params['sign'] = generate_sign($params, TRANSFER_AGENT_KEY);
        $url = AGENT_URL . url('/account/transfer', $params);
        $this->assign('url', $url);
        return $this->fetch();
    }

    public function save_logo()
    {
        $id = input('id');
        $logo = input('logo');
        if (empty($id)) $this->error('请选择'.config('app.agent_setting.agent_name'));
        if (empty($logo)) $this->error('logo地址不能为空');
        if (!validate_qiniu_url($logo, 'agent_logo', ['agent_id' => $id]))
            $this->error('logo地址不合法');
        $num = Db::name('agent')->where('id', $id)->update(['logo' => $logo]);
        if (!$num) $this->error('保存失败');
        $this->success('保存成功');
    }

    public function performance()
    {
        $get = input();
        $get['runit'] = $get['runit'] ? $get['runit'] : 'd';
        $get['rnum'] = $get['rnum'] ? $get['rnum'] : date('Y-m-d');
        if ($get['status'] != '' || $get['level'] != '' || $get['grade'] != '' || $get['keyword'] != '' || $get['pid'] != '') {
            $get['sort'] = 'complex';
            $get['sort_by'] = '';
        }
        $this->assign('get', $get);
        $timeRangerConfig = DateTools::getTimeRangerConfig();
        $this->assign('time_ranger_json', json_encode($timeRangerConfig));
        $get['id']=AGENT_ID;
        $agentApi = new AgentKpi();
        $total = $agentApi->getTotal($get);
        $page = $this->pageshow($total);
        $list = $agentApi->getList($get, $page->firstRow, $page->listRows);
        $this->assign('_list', $list ? $list : []);
        return $this->fetch();
    }

}

<?php
namespace app\agent\controller;


use think\Db;
use think\facade\Request;

class Controller extends \bxkj_module\controller\Agent
{
    //登录检查白名单方法
    protected $allows = array(
        "account" => array("login", "sync", "transfer"),
        "common" => array('__un__')
    );
    protected $agent;

    public function __construct()
    {
        parent::__construct();
        $agent_status = config('app.agent_setting.agent_status');
        $close_info = config('app.agent_setting.close_info');
        if (!$agent_status){
            echo $close_info;exit;
        }
        //未登录
        if (!is_allow($this->allows)) {
            if (empty($this->admin)) $this->redirect($this->loginRedirect);
            $agentService = new \app\agent\service\Agent();
            $agentInfo = $agentService->checkLogin($this->admin['agent_id']);
            if (!$agentInfo) $this->errorLogout((string)$agentService->getError());
            $this->agent = $agentInfo['agent'];
            define('PAGENT_ID', $agentInfo['pid']);
            define('AGENT_LV', $agentInfo['level']);
            define('AGENT_ID', $agentInfo['agent_id']);
            $this->assign('AGENT_LV', AGENT_LV);
            $this->assign('IS_ROOT', $this->admin['is_root']);
            define('ROOT_UID', $this->agent['root_id']);
            $this->assign('agent', $this->agent);
            $this->pageInfo['company_name'] = $this->agent['name'];
            $this->pageInfo['company_full_name'] = $this->agent['name'];
        }
        if (Request::isGet()) {
            $this->createMenu('agent', 'agent', AID, 'current');
        }
        if (!empty($this->admin) && is_allow(['index' => ['index']])) {
            if (weak_password($this->admin['salt'], $this->admin['password'], $this->admin['username'])) {
                $this->error('密码过于简单，请修改密码', 1, url('agent_admin/change_pwd') . '?redirect=' . urlencode(Request::url()));
                exit();
            }
        }
        $time = mktime(8, 0, 0, 10, 13, 2018);
        $this->assign('is_test', time() < $time ? '1' : '');

    }

    public function errorLogout($message, $status = 1)
    {
        $sysName = 'agent_admin';
        session($sysName, null);
        cookie("{$sysName}_auto_login", null);
        $this->error($message, $status, url('account/login'));
        exit();
    }


}

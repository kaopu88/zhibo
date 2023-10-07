<?php

namespace app\agent\controller;
use think\Db;
use think\facade\Request;

class AgentAdmin extends Controller
{

    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        $get = input();
        $adminService = new \app\agent\service\AgentAdmin();
        $total = $adminService->getTotal($get);
        $page = $this->pageshow($total);
        $list = $adminService->getList($get, $page->firstRow, $page->listRows);
        $this->assign('_list', $list);
        return $this->fetch();
    }

    public function add()
    {
        if (Request::isPost()) {
            $adminService = new \app\agent\service\AgentAdmin();
            $post = Request::post();
            $post['agent_id'] = AGENT_ID;
            $id = $adminService->add($post);
            if (!$id) $this->error($adminService->getError());
            $this->success('添加成功');
        } else {
            return $this->fetch();
        }
    }

    public function edit()
    {
        $id = input('id');
        $adminService = new \app\agent\service\AgentAdmin();
        if (Request::isPost()) {
            if ($id == ROOT_UID) $this->error('超级管理员账号受保护');
            $post = Request::post();
            $post['agent_id'] = AGENT_ID;
            $num = $adminService->update($post);
            if (!$num) $this->error($adminService->getError());
            $this->success('编辑成功');
        } else {
            if ($id == ROOT_UID) $this->error('超级管理员账号受保护');
            $info = Db::name('agent_admin')->where(array('id' => $id))->find();
            if ($info['promoter_uid'])
            {
                $promoter_arr = Db::name('user')->field('user_id, nickname user_name')->where('user_id', 'in', $info['promoter_uid'])->select();
                $info['promoter_arr'] = json_encode($promoter_arr);
            }
            $this->assign('_info', $info);
            $this->assign('promoter_arr', $promoter_arr);
            return $this->fetch('add');
        }
    }

    public function del()
    {
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择管理员');
        if (in_array(ROOT_UID, $ids)) $this->error('超级管理员账号受保护');
        $adminService = new \app\agent\service\AgentAdmin();
        $num = $adminService->delete($ids);
        if (!$num) $this->error('删除失败');
        $this->success('删除成功');
    }

    public function change_status()
    {
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择管理员');
        $status = input('status');
        if (in_array(ROOT_UID, $ids)) $this->error('超级管理员账号受保护');
        $result = Db::name('agent_admin')->whereIn('id', $ids)->update(array('status' => $status));
        if (!$result) $this->error('更新失败');
        $this->success('更新成功');
    }

    public function change_pwd()
    {
        if (Request::isPost()) {
            $oldPwd = input('old_password');
            $pwd = input('password');
            $confirmPwd = input('confirm_password');
            if (empty($oldPwd)) $this->error('请填写原密码');
            if (empty($pwd)) $this->error('请填写新密码');
            if (isset($confirmPwd) && $pwd != $confirmPwd) {
                $this->error('密码两次输入不一致');
            }
            if (strlen($pwd) < 6 || strlen($pwd) > 16) $this->error('密码6-16位数字或英文字母组合');
            if (!validate_regex($pwd, 'no_blank')) $this->error('密码不能包含空格');
            $admin = Db::name('agent_admin')->where(array('id' => AID))->find();
            if (empty($admin)) $this->error('管理员不存在');
            $auth = \app\agent\service\AgentAdmin::verifyPassword($admin, $oldPwd);
            if (!$auth) $this->error('原密码不正确');
            $salt = sha1(uniqid() . get_ucode());
            $sha1 = sha1($pwd . $salt);
            if (weak_password($salt, $sha1, $admin['username'])) $this->error('密码过于简单');
            $num = Db::name('agent_admin')->where(array('id' => AID))->update(array(
                'password' => $sha1,
                'salt' => $salt
            ));
            if (!$num) $this->error('修改失败');
            $this->success('修改成功');
        } else {
            return $this->fetch();
        }
    }

}

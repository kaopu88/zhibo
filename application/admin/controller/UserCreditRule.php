<?php

namespace app\admin\controller;

use think\Db;
use think\facade\Request;

class UserCreditRule extends Controller
{

    public function index()
    {
        $this->checkAuth('admin:user_credit_rule:select');
        $userCreditRuleService = new \app\admin\service\UserCreditRule();
        $get = input();
        $total = $userCreditRuleService->getTotal($get);
        $page = $this->pageshow($total);
        $artList = $userCreditRuleService->getList($get, $page->firstRow, $page->listRows);
        $this->assign('get', $get);
        $this->assign('_list', $artList);
        return $this->fetch();
    }

    public function add()
    {
        $this->checkAuth('admin:user_credit_rule:add');
        if (Request::isGet()) {
            return $this->fetch();
        } else {
            $userCreditRuleService = new \app\admin\service\UserCreditRule();
            $post = input();
            $result = $userCreditRuleService->add($post);
            if (!$result) $this->error($userCreditRuleService->getError());
            alog("user.credit_rule.add", '新增信用规则 ID：'.$result);
            $this->success('新增成功', $result);
        }
    }

    public function edit()
    {
        $this->checkAuth('admin:user_credit_rule:update');
        if (Request::isGet()) {
            $id = input('id');
            $info = Db::name('user_credit_rule')->where('id', $id)->find();
            if (empty($info)) $this->error('规则不存在');
            $this->assign('_info', $info);
            return $this->fetch('add');
        } else {
            $userCreditRuleService = new \app\admin\service\UserCreditRule();
            $post = input();
            $result = $userCreditRuleService->update($post);
            if (!$result) $this->error($userCreditRuleService->getError());
            alog("user.credit_rule.edit", '编辑信用规则 ID：'.$post['id']);
            $this->success('编辑成功', $result);
        }
    }

    public function delete(){
        $this->checkAuth('admin:user_credit_rule:delete');
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择记录');
        $num = Db::name('user_credit_rule')->whereIn('id', $ids)->delete();
        if (!$num) $this->error('删除失败');
        alog("user.credit_rule.del", '删除信用规则 ID：'.implode(",", $ids));
        $this->success("删除成功，共计删除{$num}条记录",'','user_credit_rule/index');
    }

}


<?php

namespace app\admin\controller;

use bxkj_module\service\UserCreditLog;
use think\Db;
use think\facade\Request;
use bxkj_common\RabbitMqChannel;

class CreditLog extends Controller
{
    public function _list()
    {
        $this->checkAuth('admin:credit_log:select');
        $get = input();
        $creditLogService = new \app\admin\service\CreditLog();
        $total = $creditLogService->getTotal($get);
        $page = $this->pageshow($total);
        $list = $creditLogService->getList($get, $page->firstRow, $page->listRows);
        $userService = new \app\admin\service\User();
        $user = $userService->getInfo($get['user_id']);
        $this->assign('user', $user);
        $this->assign('_list', $list);
        $this->assign('get', $get);
        return $this->fetch();
    }

    public function index()
    {
        $this->checkAuth('admin:credit_log:select');
        $get = input();
        $creditLogService = new \app\admin\service\CreditLog();
        $total = $creditLogService->getTotal($get);
        $page = $this->pageshow($total);
        $list = $creditLogService->getList($get, $page->firstRow, $page->listRows);
        $userService = new \app\admin\service\User();
        $user = $userService->getInfo($get['user_id']);
        $this->assign('user', $user);
        $this->assign('_list', $list);
        $this->assign('get', $get);
        return $this->fetch('list');
    }

    public function get_type()
    {
        $types = Db::name('user_credit_rule')->field('type value,name')->order(['create_time' => 'desc'])->select();
        return json_success($types ? $types : [], '获取成功');
    }

    public function add()
    {
        $this->checkAuth('admin:credit_log:add');
        if (Request::isGet()) {
        } else {
            $post = input();
            $userId = $post['user_id'];
            $changeType = $post['change_type'];
            if (empty($userId)) $this->error('请选择用户ID');
            if (!in_array($changeType, ['inc', 'exp'])) $this->error('类型不正确');
            $type = $changeType == 'inc' ? 'erp_inc' : 'erp_exp';

            //对接rabbitMQ
            $rabbitChannel = new RabbitMqChannel(['user.credit']);
            $rabbitChannel->exchange('main')->sendOnce('user.credit.'.$type, ['user_id' => $userId,'value' => $post['score'],'change_type' => $changeType,'remark' => $post['remark'] ? $post['remark'] : '','aid' => AID]);
            $this->success('添加成功');
        }
    }
}

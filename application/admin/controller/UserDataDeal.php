<?php

namespace app\admin\controller;

use bxkj_module\service\Admin;
use bxkj_common\BaseError;
use bxkj_module\service\UserRedis;
use bxkj_common\RabbitMqChannel;
use think\Db;
use think\facade\Request;
use app\admin\service\Work;

class UserDataDeal extends Controller
{
    public function index()
    {
        $this->checkAuth('admin:user_data_deal:select');
        $get = input();
        $user_data_dealService = new \app\admin\service\UserDataDeal();
        $total = $user_data_dealService->getTotal($get);
        $page = $this->pageshow($total);
        $list = $user_data_dealService->getList($get, $page->firstRow, $page->listRows);
        $this->assign('_list', $list);
        return $this->fetch();
    }

    public function check()
    {
        $this->checkAuth('admin:user_data_deal:check');
        $get = input();
        $get['aid'] = AID;
        if ($get['audit_status'] == '0') {
            Work::read(AID, 'user_data_deal');
        }
        $user_data_dealService = new \app\admin\service\UserDataDeal();
        $total = $user_data_dealService->getTotal($get);
        $page = $this->pageshow($total);
        $list = $user_data_dealService->getList($get, $page->firstRow, $page->listRows);
        $this->assign('_list', $list);
        $this->assign('audit_status', isset($get['audit_status']) ? $get['audit_status'] : '');
        return $this->fetch();
    }

    public function submit_mq()
    {
        $get = input();
        $id = $get['id'];
        //对接rabbitMQ
        $rabbitChannel = new RabbitMqChannel(['user.user_data_deal']);
        $rabbitChannel->exchange('main')->sendOnce('user.user_data_deal.audit', ['id' => $id]);
        $this->success('处理成功');
    }

    public function handler()
    {
        $this->checkAuth('admin:user_data_deal:check');
        $post = Request::post();
        if (empty($post['id'])) $this->error('请选择申请记录');
        $user_data_dealService = new \app\admin\service\UserDataDeal();
        $result = $user_data_dealService->handler($post, AID);
        if (!$result) return $this->error($user_data_dealService->getError());
        alog("user.user.audit", "审核用户 USER_ID：".$post['id']);
        $this->success('处理成功');
    }

    public function batch_deal()
    {
        $this->checkAuth('admin:user_data_deal:check');
        $audit_status = input('audit_status');
        $user_data_dealService = new \app\admin\service\UserDataDeal();
        if ($audit_status == '1') {
            $ids = get_request_ids();
            if (empty($ids)) $this->error('请选择申请记录');
            $num = $user_data_dealService->batch_handler($ids, AID);
            if (!$num) $this->error($user_data_dealService->getError());
            alog("user.user.audit", "批量审核用户 USER_ID：".implode(",", $ids)."共".$num."个用户");
            $this->success('审核成功，共计通过了' . $num . '个记录');
        }
    }
}

<?php
namespace app\admin\controller;

use think\Db;
use think\facade\Request;

class Work extends Controller
{
    public function index()
    {
        $this->checkAuth('admin:work:select');
        $workService = new \app\admin\service\Work();
        $get = input();
        $total = $workService->getTotal($get);
        $page = $this->pageshow($total);
        $packages = $workService->getList($get, $page->firstRow, $page->listRows);
        $this->assign('_list', $packages);
        $this->assign('get',Request::param());

        return $this->fetch();
    }

    public function change_sms_status()
    {
        $aid = input('aid');
        $type = input('id');
        $status = input('sms_status');
        $workService = new \app\admin\service\Work();
        $res = $workService->changeSmsStatus($aid, $type, $status);
        if (!$res) $this->error($workService->getError());
        $actName = $status == '0' ? '关闭' : '开启';
        alog("system.work.edit", '编辑工作人员 type：'.Db::name('work_types')->where(['type'=>$type])->value('name')." aid:".$aid."<br>修改短信状态：".$actName);
        $this->success($actName . '成功');
    }

    public function change_status()
    {
        $aid = input('aid');
        $type = input('id');
        $status = input('status');
        $workService = new \app\admin\service\Work();
        $res = $workService->changeStatus($aid, $type, $status);
        if (!$res) $this->error($workService->getError());
        $actName = $status == '0' ? '下线' : '上线';
        alog("system.work.edit", '编辑工作人员 type：'.Db::name('work_types')->where(['type'=>$type])->value('name')." aid:".$aid."<br>修改短信状态：".$actName);
        $this->success($actName . '成功');
    }

    public function del()
    {
        $this->checkAuth('admin:work:delete');
        $id = input('id');
        $workService = new \app\admin\service\Work();
        $num = $workService->delete($id);
        if (!$num) $this->error($workService->getError());
        alog("system.work.del", '删除工作人员 ID：'.$id);
        $this->success('删除成功');
    }
}
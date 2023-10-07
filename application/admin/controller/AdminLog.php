<?php

namespace app\admin\controller;

use think\Db;

class AdminLog extends Controller
{
    public function index()
    {
        $this->checkAuth('admin:admin_log:index');
        $get = input();
        $adminLogService = new \app\admin\service\AdminLog();
        $total = $adminLogService->getTotal($get);
        $page = $this->pageshow($total);
        $list = $adminLogService->getList($get,$page->firstRow,$page->listRows);
        $this->assign('get',$get);
        $this->assign('_list',$list);

        $types = $adminLogService->getAllTypes();
        $this->assign('types',$types);

        $admin = new \app\admin\service\Admin();
        if(AID != 1){
            $where['id'] = AID;
        }
        $where['gid'] = "";
        $userTotal = $admin->getTotal($where);
        $adminUsers = $admin->getList($where, 0, $userTotal);
        $this->assign('users',$adminUsers);

        return $this->fetch();
    }

    public function del()
    {
        $this->checkAuth('admin:admin_log:delete');
        if(AID != 1){
            $this->error('你没有权限删除操作记录');
        }
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择记录');
        $num = Db::name('admin_log')->whereIn('id', $ids)->delete();
        if (!$num) $this->error('删除失败');
        $this->success("删除成功，共计删除{$num}条记录",'','admin_log/index');
    }

    public function flush()
    {
        $this->checkAuth('admin:admin_log:flush');
        if(AID != 1){
            $this->error('你没有权限删除操作记录');
        }
        $num = Db::name('admin_log')->where("1=1")->delete();
        if (!$num) $this->error('删除失败');
        $this->success("删除成功，共计删除{$num}条记录",'','admin_log/index');
    }
}
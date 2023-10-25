<?php
namespace app\admin\controller;

use think\Db;
use think\facade\Request;

class Payments extends Controller
{
    public function index()
    {
        $this->checkAuth('admin:payments:select');
        $get = input();
        $vipService = new \app\admin\service\Payments();
        $total = $vipService->getTotal($get);
        $page = $this->pageshow($total);
        // var_dump($vipService);die;
        $list = $vipService->getList($get, $page->firstRow, $page->listRows);
        $this->assign('_list', $list);
        return $this->fetch();
    }

    public function add()
    {   
        $this->checkAuth('admin:payments:add');
        if (Request::isGet()){
            $list = Db::name('recharge_bean')->where([
                    ['apple_id', 'eq', ''],
                    ['status', 'eq', '1']
                ])->order('sort desc,create_time desc')->select();
            $this->assign('rechargeList', $list);
            return $this->fetch();
        }else{
            $vipService = new \app\admin\service\Payments();
            $post = input();
            $result = $vipService->add($post);
            
            if (!$result) $this->error($vipService->getError());
            alog("manager.payments.add", '新增payments套餐 ID：'.$result);
            $this->success('新增成功', $result);
        }
    }

    public function edit()
    {
        $this->checkAuth('admin:payments:update');
        if (Request::isGet()){
            $id = input('id');
            $info = Db::name('payments')->where('id', $id)->find();
            if (empty($info)) $this->error('套餐不存在');
            $list = Db::name('recharge_bean')->where([
                    ['apple_id', 'eq', ''],
                    ['status', 'eq', '1']
                ])->order('sort desc,create_time desc')->select();
            $this->assign('rechargeList', $list);
         
            $this->assign('_info', $info);
            return $this->fetch('add');
        }else{
            $vipService = new \app\admin\service\Payments();
            $post = input();
            
            $post["coin_type"]=implode(',', $post["coin_type"]);
            $result = $vipService->update($post);
            if (!$result) $this->error($vipService->getError());
            alog("manager.payments.edit", '编辑payments套餐 ID：'.$post['id']);
            $this->success('编辑成功', $result);
        }
    }

    public function delete()
    {
        $this->checkAuth('admin:payments:delete');
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择记录');
        $num = Db::name('payments')->whereIn('id', $ids)->delete();
        if (!$num) $this->error('删除失败');
        alog("manager.payments.del", '删除payments套餐 ID：'.implode(",", $ids));
        $this->success("删除成功，共计删除{$num}条记录",'','payments/index');
    }

    public function change_status()
    {
        $this->checkAuth('admin:payments:update');
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择记录');
        $status = input('status');
        if (!in_array($status, ['0', '1'])) $this->error('状态值不正确');
        $vipService = new \app\admin\service\Payments();
        $num = $vipService->changeStatus($ids, $status);
        if (!$num) $this->error('切换状态失败');
        alog("manager.payments.edit", '编辑payments套餐 ID：'.implode(",", $ids)."<br>修改状态：".($status == 1 ? "启用" : "禁用"));
        $this->success('切换成功');
    }

    public function get_list()
    {
        $result = Db::name('payments')->field('id value,name')->select();
        $this->success('获取成功', $result);
    }
}


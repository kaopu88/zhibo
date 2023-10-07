<?php
/**
 * user zack
 */

namespace app\admin\controller;

use think\Db;
use think\facade\Request;

class Ip extends Controller
{
    public function index()
    {
        $this->checkAuth('admin:ip:index');
        $get = input();
        $ipService = new \app\admin\service\Ip();
        $total = $ipService->getTotal($get);
        $page = $this->pageshow($total);
        $list = $ipService->getList($get, $page->firstRow, $page->listRows);
        $this->assign('_list', $list);
        return $this->fetch();
    }

    public function add()
    {
        $this->checkAuth('admin:ip:add');
        if (Request::isGet()) {
            return $this->fetch();
        } else {
            $post = input();
            $ipService = new \app\admin\service\Ip();
            $result = $ipService->add($post);
            if ($result['code'] != 200) return $this->error($result['msg']);
            $this->success('新增成功');
        }
    }

    public function edit()
    {
        $this->checkAuth('admin:ip:edit');
        if (Request::isGet()) {
            $id = input('id');
            $info = Db::name('ip')->where('id', $id)->find();
            if (empty($info)) return $this->error('信息不存在');
            $this->assign('_info', $info);
            return $this->fetch('add');
        } else {
            $post = input();
            $ipService = new \app\admin\service\Ip();
            $result = $ipService->update_ip($post);
            if ($result['code'] != 200) return $this->error($result['msg']);
            return $this->success('编辑成功', $result);
        }
    }

    public function delete()
    {
        $this->checkAuth('admin:ip:delete');
        $ids = get_request_ids();
        if (empty($ids)) return $this->error('请选择记录');
        $num = Db::name('ip')->whereIn('id', $ids)->delete();
        if (!$num) return $this->error('删除失败');
        return $this->success("删除成功，共计删除{$num}条记录", '', 'ip/index');
    }
}
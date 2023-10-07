<?php
namespace app\admin\controller;

use app\admin\service\Work;
use think\Db;
use think\facade\Request;

class LotteryType extends Controller
{
    public function category()
    {
        $this->checkAuth('admin:lottery_type:select');
        $get = input();
        $lotteryTypeService = new \app\admin\service\LotteryType();
        $total = $lotteryTypeService->getCategoryTotal($get);
        $page = $this->pageshow($total);
        $list = $lotteryTypeService->getCategory($get, $page->firstRow, $page->listRows);
        $this->assign('_list', $list);
        return $this->fetch();
    }

    public function category_add()
    {
        $this->checkAuth('admin:lottery_type:category_add');
        if (Request::isGet()) {
            return $this->fetch();
        } else {
            $lotteryTypeService = new \app\admin\service\LotteryType();
            $post = input();
            $result = $lotteryTypeService->category_add($post);
            if (!$result) $this->error($lotteryTypeService->getError());
            alog("live.lottery_type.add", '新增大转盘类型 ID：' . $result);
            $this->success('新增成功', $result);
        }
    }

    public function category_edit()
    {
        $this->checkAuth('admin:lottery_type:category_edit');
        if (Request::isGet()) {
            $id = input('id');
            $info = Db::name('lottery_type')->where('id', $id)->find();
            if (empty($info)) $this->error('类型不存在');
            $this->assign('_info', $info);
            return $this->fetch('category_add');
        } else {
            $lotteryTypeService = new \app\admin\service\LotteryType();
            $post = input();
            $result = $lotteryTypeService->category_edit($post);
            if (!$result) $this->error($lotteryTypeService->getError());
            alog("live.lottery_type.edit", '编辑大转盘分类 ID：' . $post['id']);
            $this->success('编辑成功', $result);
        }
    }

    public function category_delete()
    {
        $this->checkAuth('admin:lottery_type:delete');
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择记录');
        $num = Db::name('lottery_type')->whereIn('id', $ids)->delete();
        if (!$num) $this->error('删除失败');
        alog("live.lottery_type.del", '删除大转盘分类 ID：' . implode(",", $ids));
        $this->success("删除成功，共计删除{$num}条记录");
    }

    public function change_status()
    {
        $this->checkAuth('admin:lottery_type:update');
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择记录');
        $status = input('status');
        if (!in_array($status, ['0', '1'])) $this->error('状态值不正确');
        $num = Db::name('lottery_type')->whereIn('id', $ids)->update(['status' => $status]);
        if (!$num) $this->error('切换状态失败');
        alog("live.lottery_type.edit", '编辑大转盘分类 ID：' . implode(",", $ids) . " 修改状态：" . ($status == 1 ? "启用" : "禁用"));
        $this->success('切换成功');
    }

    public function get_category()
    {
        $where = array();
        $target_type = input('target_type');
        $where['status'] = '1';
        $where['target'] = $target_type;
        $result = Db::name('complaint_category')->where($where)->field('id value,name')->select();
        $result = $result ? $result : array();
        $this->success('获取成功', $result);
    }
}
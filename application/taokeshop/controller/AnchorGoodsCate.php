<?php
/**
 * Created by PhpStorm.
 * User: 李成
 * Date: 2020/4/28
 * Time: 16:48
 */
namespace app\taokeshop\controller;

use app\admin\service\User;
use think\Db;
use think\facade\Request;

class AnchorGoodsCate extends Controller
{
    public function index()
    {
        $this->checkAuth("taokeshop:anchor_goods_cate:index");

        $get = input();
        $AnchorGoodsCateService = new \app\taokeshop\service\AnchorGoodsCate();
        $total = $AnchorGoodsCateService->getTotal($get);
        $page = $this->pageshow($total);
        $list = $AnchorGoodsCateService->getList($get, $page->firstRow, $page->listRows);
        $this->assign('_list', $list);
        return $this->fetch();
    }

    public function add()
    {
        $this->checkAuth('taokeshop:anchor_goods_cate:add');
        if (Request::isGet()) {
            $info = [];
            $this->assign('_info', $info);
            return $this->fetch("add");
        }else{
            $AnchorGoodsCateService = new \app\taokeshop\service\AnchorGoodsCate();
            $post = input();
            if(isset($post['user_id'])){
                $user = new User();
                $userInfo = $user->getInfo($post['user_id']);
                $post['shop_id'] = $userInfo['shop_id'];
            }
            $post['create_time'] = time();
            $result = $AnchorGoodsCateService->add($post);
            if (!$result) $this->error($AnchorGoodsCateService->getError());
            alog("taokeshop.goods_cate.add", '新增橱窗分类 ID：'.$result);
            $this->success('新增成功', $result);
        }
    }

    public function edit()
    {
        $this->checkAuth('taokeshop:anchor_goods_cate:update');
        if (Request::isGet()) {
            $cateId = input('cate_id');
            if (empty($cateId)) $this->error('请选择记录');
            $AnchorGoodsCateService = new \app\taokeshop\service\AnchorGoodsCate();
            $cateInfo = $AnchorGoodsCateService->getCateInfo(["cate_id" => $cateId]);
            $this->assign('_info', $cateInfo);
            return $this->fetch('add');
        } else {
            $post = Request::post();
            $AnchorGoodsCateService = new \app\taokeshop\service\AnchorGoodsCate();
            $res = $AnchorGoodsCateService->update($post);
            if (!$res) $this->error($AnchorGoodsCateService->getError());
            alog("taokeshop.goods_cate.edit", '编辑橱窗分类 ID：'.$post['id']);
            $this->success('操作成功');
        }
    }

    public function change_status()
    {
        $this->checkAuth('taokeshop:anchor_goods_cate:update');
        $ids = get_request_ids("cate_id");
        if (empty($ids)) $this->error('请选择记录');
        $status = input('status');
        if (!in_array($status, ['0', '1'])) $this->error('状态值不正确');
        $num = Db::name('anchor_goods_cate')->whereIn('cate_id', $ids)->update(['status' => $status]);
        if (!$num) $this->error('切换状态失败');
        alog("taokeshop.goods_cate.edit", '编辑橱窗分类 ID：'.implode(",", $ids)." 修改状态：".($status == 1 ? "启用" : "禁用"));
        $this->success('切换成功');
    }

    public function del()
    {
        $this->checkAuth('taokeshop:anchor_goods_cate:delete');
        $ids = get_request_ids("cate_id");
        if (empty($ids)) $this->error('请选择类目');
        $num = Db::name('anchor_goods_cate')->whereIn('cate_id', $ids)->delete();
        if (!$num) $this->error('删除失败');
        alog("taokeshop.goods_cate.del", '删除橱窗分类 ID：'.implode(",", $ids));
        $this->success("删除成功，共计删除{$num}条记录");
    }


}
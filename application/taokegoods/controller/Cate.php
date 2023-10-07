<?php
/**
 * Created by PhpStorm.
 * User: 李成
 * Date: 2020/5/19
 * Time: 15:21
 */
namespace app\taokegoods\controller;

use think\Db;
use think\facade\Request;

class Cate extends Controller
{

    public function index()
    {
        $this->checkAuth('taokegoods:cate:index');

        $get = input();
        $cateService = new \app\taokegoods\service\Cate();
        $total = $cateService->getTotal($get);
        $page = $this->pageshow($total);
        $list = $cateService->getList($get, $page->firstRow, $page->listRows);
        $this->assign('_list', $list);
        return $this->fetch();
    }

    public function add()
    {
        $this->checkAuth('taokegoods:cate:add');
        if (Request::isGet()) {
//            $dtkCate = Db::name("dtk_cate")->select();
//            $this->assign('cate_list', $dtkCate);
            return $this->fetch();
        } else {
            $cateService = new \app\taokegoods\service\Cate();
            $post = input();
            $data['name'] = $post['name'];
            $data['img'] = $post['img'];
            $data['desc'] = $post['desc'];
            $data['status'] = $post['status'];
            $data['dtk_cate_id'] = $post['dtk_cate_id'];
            $data['sort'] = $post['sort'];
            $result = $cateService->add($data);
            if($result === false){
                $this->error('新增失败');
            }
            alog("taoke.goods_cate.add", '新增商品分类 ID：'.$result);
            $this->success('新增成功', $result);
        }
    }

    public function edit()
    {
        $this->checkAuth('taokegoods:cate:update');
        $cateService = new \app\taokegoods\service\Cate();
        if (Request::isGet()) {
            $id = input('id');
            $info = $cateService->getInfo(["cate_id" => $id]);
            if (empty($info)) $this->error('分类不存在');
            $this->assign('_info', $info);
//            $dtkCate = Db::name("dtk_cate")->select();
//            $this->assign('cate_list', $dtkCate);
            return $this->fetch('add');
        } else {
            $post = input();
            $where['cate_id'] = $post['id'];
            $data['name'] = $post['name'];
            $data['img'] = $post['img'];
            $data['desc'] = $post['desc'];
            $data['status'] = $post['status'];
            $data['dtk_cate_id'] = $post['dtk_cate_id'];
            $data['sort'] = $post['sort'];
            $result = $cateService->update($where, $data);
            if($result === false){
                $this->error('编辑失败');
            }
            alog("taoke.goods_cate.edit", '编辑商品分类 ID：'.$post['id']);
            $this->success('编辑成功', $result);
        }
    }

    public function change_status()
    {
        $this->checkAuth('taokegoods:cate:update');
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择记录');
        $status = input('status');
        if (!in_array($status, ['0', '1'])) $this->error('状态值不正确');
        $cateService = new \app\taokegoods\service\Cate();
        $num = $cateService->update(['cate_id' => $ids], ['status' => $status]);
        if (!$num) $this->error('切换状态失败');
        alog("taoke.goods_cate.edit", '编辑商品分类 ID：'.implode(",", $ids)." 修改状态：".($status == 1 ? "启用" : "禁用"));
        $this->success('切换成功');
    }

    public function del()
    {
        $this->checkAuth('taokegoods:cate:delete');
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择分类');
        $where[] = ['cate_id', "in", $ids];
        $cateService = new \app\taokegoods\service\Cate();
        $num = $cateService->delete($where);
        if (!$num) $this->error('删除失败');
        alog("taoke.goods_cate.del", '删除商品分类 ID：'.implode(",", $ids));
        $this->success("删除成功，共计删除{$num}条记录");
    }

}
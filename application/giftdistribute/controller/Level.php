<?php
namespace app\giftdistribute\controller;

use think\Db;
use think\facade\Request;

class Level extends Controller
{

    //分销等级列表
    public function index()
    {
        $ser = new \app\admin\service\SysConfig();
        $info = $ser->getConfig("giftdistribute");
        $info = json_decode($info['value'], true);
        $this->assign('_info', $info);
        $this->checkAuth('giftdistribute:Level:lists');
        $get = input();
        $Level = new \app\giftdistribute\service\Level();
        $total = $Level->getTotal($get);
        $page = $this->pageshow($total);
        $list = $Level->getList($get, $page->firstRow, $page->listRows);
        $this->assign('_list', $list);
        return $this->fetch();
    }


    //添加分销等级
    public function add(){
        $this->checkAuth('giftdistribute:Level:add');
        if (Request::isGet()) {
            return $this->fetch();
        } else {
            $Level = new \app\giftdistribute\service\Level();
            $post = input();
            $result = $Level->add($post);
            if (!$result) $this->error($Level->getError());
            alog('gift.level.add', "新增分销等级 ID：".$result);
            $this->success('新增成功', $result);
        }
    }


    //修改分销等级
    public function edit(){
        $this->checkAuth('giftdistribute:Level:edit');
        if (Request::isGet()) {
            $id = input('id');
            $info = Db::name('gift_commission_level')->where('id', $id)->find();
            if (empty($info)) $this->error('类型不存在');
            $this->assign('_info', $info);
            return $this->fetch('add');
        } else {
            $Level = new \app\giftdistribute\service\Level();
            $post = input();
            $result = $Level->edit($post);
            if (!$result) $this->error($Level->getError());
            alog('gift.level.edit', "编辑分销等级 ID：".$post['id']);
            $this->success('编辑成功', $result);
        }
    }


    //删除分销等级
    public function del(){
        $this->checkAuth('giftdistribute:Level:delete');
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择记录');
        $num = Db::name('gift_commission_level')->whereIn('id', $ids)->delete();
        if (!$num) $this->error('删除失败');
        alog('gift.level.del', "删除分销等级 ID：".implode(",", $ids));
        $this->success("删除成功，共计删除{$num}条记录");
    }


    public function change_status()
    {
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择记录');
        $status = input('status');
        if (!in_array($status, ['0', '1'])) $this->error('状态值不正确');
        $where = [
            ['id', '<>', $ids[0]]
        ];
        $num = Db::name('gift_commission_level')->where(['id' => $ids[0]])->update(['status' => 1]);
        $num = Db::name('gift_commission_level')->where($where)->update(['status' => 0]);
        if (!$num) $this->error('切换状态失败');
        alog('gift.level.edit', "编辑分销等级 ID：".implode(",", $ids)."修改状态:".($status == 1 ? "启用" : "禁用"));
        $this->success('切换成功');
    }
}
<?php
/**
 * Created by PhpStorm.
 * User: 李成
 * Date: 2020/5/6
 * Time: 17:14
 */
namespace app\taoke\controller;

use app\admin\service\User;
use think\Db;
use think\facade\Request;

class Level extends Controller
{
    public function index()
    {
        $this->checkAuth('taoke:level:index');

        $get = input();
        $levelService = new \app\taoke\service\Level();
        $total = $levelService->getTotal($get);
        $page = $this->pageshow($total);
        $list = $levelService->getList($get, $page->firstRow, $page->listRows);
        $this->assign('_list', $list);
        return $this->fetch();
    }

    public function add()
    {
        $this->checkAuth('taoke:level:add');
        if (Request::isPost()) {
            $post = input();
            $level = $post['level'];
            if($level <= 0){
                return $this->error("等级必须为正整数");
            }
            $map['level'] = $level;
            $levelService = new \app\taoke\service\Level();
            if($levelService->getLevelInfo($map)){
                return $this->error("此等级已存在，请选择其他等级");
            }
            $result = $levelService->addLevel($post);
            if($result){
                alog("taoke.level.add", "新增淘客会员等级 ID：".$result);
                return $this->success("添加成功");
            }else{
                return $this->error("添加失败");
            }
        }
        return $this->fetch();
    }

    public function edit()
    {
        $this->checkAuth('taoke:level:update');
        if (Request::isGet()) {
            $id = input('id');
            $levelService = new \app\taoke\service\Level();
            $levelInfo = $levelService->getLevelInfo(["id"=>$id]);
            if (empty($levelInfo)) $this->error('等级不存在');
            $this->assign('_info', $levelInfo);
            return $this->fetch('edit');
        } else {
            $levelService = new \app\taoke\service\Level();
            $post = input();
            $where["id"] = $post['id'];
            unset($post['id']);
            $result = $levelService->updateLevel($where, $post);
            if($result){
                alog("taoke.level.edit", "编辑淘客会员等级 ID：".$where['id']);
                $this->success('编辑成功', $result);
            }else{
                $this->error('编辑失败');
            }
        }
    }

    public function changeStatus()
    {
        $this->checkAuth('taoke:level:update');
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择记录');
        $status = input('status');
        if (!in_array($status, ['0', '1'])) $this->error('状态值不正确');
        $num = Db::name('taoke_level')->whereIn('id', $ids)->update(['status' => $status]);
        if (!$num) $this->error('切换状态失败');
        alog("taoke.level.edit", "编辑淘客会员等级 ID：".implode(",", $ids)."<br>切换状态 ：".($status == 1 ? "启用" : "禁用"));
        $this->success('切换成功');
    }

    public function upgrade_pop()
    {
        if (Request::isGet()) {
            $id = input("id");
            $levelService = new \app\taoke\service\Level();
            $levelInfo = $levelService->getLevelInfo(["id"=>$id]);
            $this->assign('info', $levelInfo);
            return $this->fetch();
        }
    }

    public function del()
    {
        $this->checkAuth('taoke:level:delete');
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择记录');
        $user = new User();
        $count = 0;
        foreach($ids as $id){
            $count += $user->getTotal(["taoke_level"=>$id]);
        }
        if($count > 0){
            $this->error('删除失败，当前删除等级有用户未处理');
        }
        $num = Db::name('taoke_level')->whereIn('id', $ids)->delete();
        if (!$num) $this->error('删除失败');
        alog("taoke.level.del", "删除淘客会员等级 ID：".implode(",", $ids));
        $this->success("删除成功，共计删除{$num}条记录");
    }
}
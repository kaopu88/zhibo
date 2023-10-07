<?php

namespace app\admin\controller;

use bxkj_common\RedisClient;
use think\Db;
use think\facade\Request;

class ExpLevel extends Controller
{
    public function index()
    {
        $this->checkAuth('admin:exp_level:select');
        $expLevelService = new \app\admin\service\ExpLevel();
        $get = input();
        $total = $expLevelService->getTotal($get);
        $page = $this->pageshow($total);
        $artList = $expLevelService->getList($get, $page->firstRow, $page->listRows);
        $this->assign('_list', $artList);
        return $this->fetch();
    }

    public function add()
    {
        $this->checkAuth('admin:exp_level:add');
        if (Request::isGet()) {
            return $this->fetch();
        } else {
            $expLevelService = new \app\admin\service\ExpLevel();
            $post = input();
            $result = $expLevelService->add($post);
            if (!$result) $this->error($expLevelService->getError());
            $redis = RedisClient::getInstance();
            $redis->del('cache:level:list');
            $redis->del('config:exp_level');
            alog("user.level.add", '新增用户等级 ID：'.$result);
            $this->success('新增成功', $result);
        }
    }

    public function edit()
    {
        $this->checkAuth('admin:exp_level:update');
        if (Request::isGet()) {
            $id = input('id');
            $info = Db::name('exp_level')->where('levelid', $id)->find();
            if (empty($info)) $this->error('等级不存在');
            $this->assign('_info', $info);
            return $this->fetch('add');
        } else {
            $expLevelService = new \app\admin\service\ExpLevel();
            $post = input();
            $result = $expLevelService->update($post);
            if (!$result) $this->error($expLevelService->getError());
            $redis = RedisClient::getInstance();
            $redis->del('cache:level:list');
            $redis->del('config:exp_level');
            alog("user.level.edit", '编辑用户等级 ID：'.$post['levelid']);
            $this->success('编辑成功', $result);
        }
    }

    public function delete()
    {
        $this->checkAuth('admin:exp_level:delete');
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择记录');
        $num = Db::name('exp_level')->whereIn('levelid', $ids)->delete();
        if (!$num) $this->error('删除失败');
        $redis = RedisClient::getInstance();
        $redis->del('cache:level:list');
        $redis->del('config:exp_level');
        alog("user.level.del", '删除用户等级 ID：'.implode(",", $ids));
        $this->success("删除成功，共计删除{$num}条记录",'','exp_level/index');
    }

    public function check_name()
    {
        $post = input();
        $name = $post['name'];
        $levelid = $post['levelid'];
        if (!$name) {
            $this->error('请填写标题');
        }
        if (!preg_match('#^LV#i', $name, $m)){
            $this->error('标题格式不正确');
        }
        $find = Db::name('exp_level')->where('levelname',$name)->find();
        if (($find && !$levelid) || ($levelid && $find['levelid'] != $levelid)) {
            $this->error('标题已存在');
        }else{
            $mark = str_replace('LV','',$name);
            $this->success('', $mark);
        }
    }
}

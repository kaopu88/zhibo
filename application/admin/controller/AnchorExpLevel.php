<?php

namespace app\admin\controller;

use bxkj_common\RedisClient;
use think\Db;
use think\facade\Request;

class AnchorExpLevel extends Controller
{
    public function index()
    {
        $this->checkAuth('admin:anchor_exp_level:select');
        $anchorExpLevelService = new \app\admin\service\AnchorExpLevel();
        $get = input();
        $total = $anchorExpLevelService->getTotal($get);
        $page = $this->pageshow($total);
        $artList = $anchorExpLevelService->getList($get, $page->firstRow, $page->listRows);
        $this->assign('_list', $artList);
        return $this->fetch();
    }

    public function add()
    {
        $this->checkAuth('admin:anchor_exp_level:add');
        if (Request::isGet()) {
            return $this->fetch();
        } else {
            $anchorExpLevelService = new \app\admin\service\AnchorExpLevel();
            $post = input();
            $result = $anchorExpLevelService->add($post);
            if (!$result) $this->error($anchorExpLevelService->getError());
            $redis = RedisClient::getInstance();
            $redis->del('config:anchor_exp_level');
            alog("user.anchor_level.add", '新增主播等级 ID：'.$result);
            $this->success('新增成功', $result);
        }
    }

    public function edit()
    {
        $this->checkAuth('admin:anchor_exp_level:update');
        if (Request::isGet()) {
            $id = input('id');
            $info = Db::name('anchor_exp_level')->where('levelid', $id)->find();
            if (empty($info)) $this->error('等级不存在');
            $this->assign('_info', $info);
            return $this->fetch('add');
        } else {
            $anchorExpLevelService = new \app\admin\service\AnchorExpLevel();
            $post = input();
            $result = $anchorExpLevelService->update($post);
            if (!$result) $this->error($anchorExpLevelService->getError());
            $redis = RedisClient::getInstance();
            $redis->del('config:anchor_exp_level');
            alog("user.anchor_level.edit", '编辑主播等级 ID：'.$post['levelid']);
            $this->success('编辑成功', $result);
        }
    }

    public function delete()
    {
        $this->checkAuth('admin:anchor_exp_level:delete');
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择记录');
        $num = Db::name('anchor_exp_level')->whereIn('levelid', $ids)->delete();
        if (!$num) $this->error('删除失败');
        $redis = RedisClient::getInstance();
        $redis->del('config:anchor_exp_level');
        alog("user.anchor_level.del", '删除主播等级 ID：'.implode(",", $ids));
        $this->success("删除成功，共计删除{$num}条记录",'','anchor_exp_level/index');
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
        $find = Db::name('anchor_exp_level')->where('levelname',$name)->find();
        if (($find && !$levelid) || ($levelid && $find['levelid'] != $levelid)) {
            $this->error('标题已存在');
        }else{
            $mark = str_replace('LV','',$name);
            $this->success('', $mark);
        }
    }
}

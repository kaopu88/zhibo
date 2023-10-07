<?php

namespace app\lottery\controller;

use think\Db;
use think\facade\Request;

class LotteryEggGift extends Controller
{
    public function index()
    {
        $this->checkAuth('lottery:lottery_egg_gift:index');
        $get = input();
        $eggGiftService = new \app\lottery\service\LotteryEggGift();
        $total = $eggGiftService->getTotal($get);
        $page = $this->pageshow($total);
        $list = $eggGiftService->getList($get, $page->firstRow, $page->listRows);
        $this->assign('_list', $list);
        return $this->fetch();
    }

    public function add()
    {
        $this->checkAuth('lottery:lottery_egg_gift:add');
        if (Request::isGet()) {
            return $this->fetch();
        } else {
            $post = input();
            $eggGiftService = new \app\lottery\service\LotteryEggGift();
            $where['gift_id'] = $post['gift_id'];
            $has_repeat = Db::name('lottery_egg_gift')->where($where)->find();
            if($has_repeat) $this->error('已经添加过了');
            $info = Db::name('gift')->where('id', $post['gift_id'])->find();
            if (empty($info)) $this->error('奖品不存在');

            $result = $eggGiftService->add($post);
            if (!$result) $this->error($eggGiftService->getError());
            $this->success('新增成功', $result);
        }
    }

    public function edit()
    {
        $this->checkAuth('lottery:lottery_egg_gift:edit');
        $eggGiftService = new \app\lottery\service\LotteryEggGift();
        if (Request::isGet()) {
            $id = input('id');
            $info = $eggGiftService->getOne(['leg.id' => $id]);
            if (empty($info)) $this->error('类型不存在');
            $this->assign('_info', $info);
            return $this->fetch('add');
        } else {
            $post = input();
            $post = input();
            if (empty($post['gift_id'])) $this->error('请选择奖品');
            if (empty($post['probability'])) $this->error('请填写中奖概率');
            $info = Db::name('gift')->where('id', $post['gift_id'])->find();
            if (empty($info)) $this->error('奖品不存在');
            $result = $eggGiftService->edit($post);
            if (!$result) $this->error($eggGiftService->getError());
            $this->success('编辑成功', $result);
        }
    }

    public function del()
    {
        $this->checkAuth('lottery:lottery_egg_gift:edit');
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择记录');
        $num = Db::name('lottery_egg_gift')->whereIn('id', $ids)->delete();
        if (!$num) $this->error('删除失败');
        $this->success("删除成功，共计删除{$num}条记录");
    }

    public function change_status()
    {
        $this->checkAuth('lottery:lottery_egg_gift:edit');
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择记录');
        $status = input('status');
        if (!in_array($status, ['0', '1'])) $this->error('状态值不正确');
        $num = Db::name('lottery_egg_gift')->whereIn('id', $ids)->update(['status' => $status]);
        if (!$num) $this->error('切换状态失败');
        $this->success('切换成功');
    }
}
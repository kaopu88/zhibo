<?php

namespace app\lottery\controller;
use think\Db;
use think\facade\Request;

class lotteryGift extends Controller
{

    public function index()
    {
        $this->checkAuth('lottery:lottery_gift:select');
        $get = input();

        $lotteryGift= new \app\lottery\service\LotteryGift();

        $total = $lotteryGift->getTotal($get);
        $page = $this->pageshow($total);
        $list = $lotteryGift->getList($get, $page->firstRow, $page->listRows);
        $this->assign('_list', $list);
        $this->assign('activity_id', $get['id']);
        return $this->fetch();
    }

    public function add()
    {
       $this->checkAuth('lottery:lottery_gift:add');
        if (Request::isGet()) {
            $get = input();
            $this->assign('activity_id', $get['activity_id']);
            return $this->fetch();
        } else {
            $lotteryGift = new \app\lottery\service\LotteryGift();
            $post = input();
            $count = Db::name('lottery_gift')->where('activity_id', $post['activity_id'])->count();
            if($count==8) $this->error('只能添加8个礼物不能再添加了');
            if (empty($post['gift_id'])) $this->error('请选择奖品');
            if (empty($post['probability'])) $this->error('请填写中奖概率');
            $where['gift_id'] = $post['gift_id'];
            $where['activity_id'] = $post['activity_id'];
            $has_repeat = Db::name('lottery_gift')->where($where)->find();
            if($has_repeat)$this->error('已经添加过了');
            $info = Db::name('gift')->where('id', $post['gift_id'])->find();
            if (empty($info)) $this->error('奖品不存在');
            $post['name']=$info['name'];
            $post['price']=$info['price'];
            $post['image']=$info['picture_url'];
            $post['discount']=$info['discount'];//折扣
            $post['conv_millet']=$info['conv_millet'];//等值钻石
            $post['type']=$info['type'];
            $post['cid']=$info['cid'];
            $post['create_time']=time();
            unset($post['redirect']);
            $result = $lotteryGift->add($post);
            if (!$result) $this->error($lotteryGift->getError());
            alog("live.lottery_gift.add", "新增大转盘奖品 ID：".$result);
            $this->success('新增成功', $result);
        }
    }


    public function edit()
    {
        $this->checkAuth('lottery:lottery_gift:edit');
        if (Request::isGet()) {
            $id = input('id');
            $info = Db::name('lottery_gift')->where('id', $id)->find();
            if (empty($info)) $this->error('类型不存在');
            $this->assign('_info', $info);
            $this->assign('activity_id', $info['activity_id']);
            return $this->fetch('add');
        } else {
            $lotteryGift = new \app\lottery\service\LotteryGift();
            $post = input();
            if (empty($post['gift_id'])) $this->error('请选择奖品');
            if (empty($post['probability'])) $this->error('请填写中奖概率');
            $info = Db::name('gift')->where('id', $post['gift_id'])->find();
            if (empty($info)) $this->error('奖品不存在');
            $post['name']=$info['name'];
            $post['price']=$info['price'];
            $post['image']=$info['picture_url'];
            $post['discount']=$info['discount'];//折扣
            $post['conv_millet']=$info['conv_millet'];//等值钻石
            $post['type']=$info['type'];
            $post['cid']=$info['cid'];
            $post['create_time']=time();
            unset($post['redirect']);
            $result = $lotteryGift->edit($post);
            if (!$result) $this->error($lotteryGift->getError());
            alog("live.lottery_gift.edit", "编辑大转盘奖品 ID：".$post['id']);
            $this->success('编辑成功', $result);
        }
    }

    public function del()
    {
        $this->checkAuth('lottery:lottery_gift:del');
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择记录');
        $num = Db::name('lottery_gift')->whereIn('id', $ids)->delete();
        if (!$num) $this->error('删除失败');
        alog("live.lottery_gift.del", "删除大转盘奖品 ID：".implode(",", $ids));
        $this->success("删除成功，共计删除{$num}条记录");
    }


    public function change_status()
    {
        $this->checkAuth('lottery:lottery_gift:edit');
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择记录');
        $status = input('status');
        if (!in_array($status, ['0', '1'])) $this->error('状态值不正确');
        $num = Db::name('lottery_gift')->whereIn('id', $ids)->update(['status' => $status]);
        if (!$num) $this->error('切换状态失败');
        alog("live.lottery_gift.edit", "编辑大转盘奖品 ID：".implode(",", $ids)."<br>修改状态：".($status == 1 ? "启用" : "禁用"));
        $this->success('切换成功');
    }

    //设置必中奖品
    public function change_ismust()
    {
        $this->checkAuth('lottery:lottery_gift:edit');
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择记录');
        $ismust = input('ismust');
        $activity_id = input('activity_id');
        if (!in_array($ismust, ['0', '1'])) $this->error('状态值不正确');
        $nums=Db::name('lottery_gift')->whereIn('activity_id', $activity_id)->update(['ismust' => 0]);
        $num = Db::name('lottery_gift')->whereIn('id', $ids)->update(['ismust' => $ismust]);
       // if (!$num) $this->error('切换状态失败');
        alog("live.lottery_gift.edit", "编辑大转盘奖品 ID：".implode(",", $ids)."<br>设置必中状态：".($ismust == 1 ? "是" : "否"));
        $this->success('切换成功');
    }

}
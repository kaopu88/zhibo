<?php
namespace app\lottery\controller;
use think\Db;
use think\facade\Request;

class Lottery extends Controller
{

    public function index()
    {
        $this->checkAuth('lottery:lottery:select');
        $get = input();
        $lotteryService = new \app\lottery\service\Lottery();

        $total = $lotteryService->getLotteryTotal($get);
        $page = $this->pageshow($total);
        $list = $lotteryService->getLottery($get, $page->firstRow, $page->listRows);
        $this->assign('_list', $list);
        return $this->fetch();
    }

    public function lottery_add()
    {
        $this->checkAuth('lottery:lottery:lottery_add');
        if (Request::isGet()) {
            $where['status'] = '1';
            $lottery_type_list = Db::name('lottery_type')->where($where)->select();
            $this->assign('type_list', $lottery_type_list);
            return $this->fetch();
        } else {
            $lotteryService = new \app\lottery\service\Lottery();
            $post = input();
            if (count($post['pay_num']) >2)  return  $this->error('抽奖条数不能超过3条');
            if(!intval($post['total'])){
                if(empty($post['pay_num']) || empty($post['pay_money']) ){
                    $this->error('请设置抽奖次数');
                }
                $post['pay_num']=implode(",", $post['pay_num']);
                $post['pay_money']=implode(",", $post['pay_money']);
            }
            $result = $lotteryService->lottery_add($post);
            if (!$result) $this->error($lotteryService->getError());
            alog("live.lottery.add", '新增大转盘活动 ID：'.$result);
            $this->success('新增成功', $result);
        }
    }

    public function lottery_edit()
    {
        $this->checkAuth('lottery:lottery:lottery_edit');
        if (Request::isGet()) {
            $id = input('id');
            $info = Db::name('lottery')->where('id', $id)->find();
            if (empty($info)) $this->error('类型不存在');

            if(!empty($info['pay_num'])){
                $pay_num=explode(',',$info['pay_num']);
                $pay_money=explode(',',$info['pay_money']);
                $items=array();
                foreach ($pay_num as $key=>$value) {
                    $items[$key]['pay_num'] = $value;
                    $items[$key]['pay_money'] = $pay_money[$key];
                }
                $this->assign('items', $items);
            }
            $this->assign('_info', $info);
            $where['status'] = '1';
            $lottery_type_list = Db::name('lottery_type')->where($where)->select();
            $this->assign('type_list', $lottery_type_list);
            return $this->fetch('lottery_add');
        } else {
            $lotteryService = new \app\lottery\service\Lottery();
            $post = input();
            if (count($post['pay_num']) >2)  return  $this->error('抽奖条数不能超过3条');
            if(!intval($post['total'])){
                if(empty($post['pay_num']) || empty($post['pay_money']) ){
                    $this->error('请设置抽奖次数');
                }
                $post['pay_num']=implode(",", $post['pay_num']);
                $post['pay_money']=implode(",", $post['pay_money']);
            }

            $result = $lotteryService->lottery_edit($post);
            if (!$result) $this->error($lotteryService->getError());
            alog("live.lottery.edit", '编辑大转盘活动 ID：'.$post['id']);
            $this->success('编辑成功', $result);
        }
    }

    public function lottery_delete()
    {
        $this->checkAuth('lottery:lottery:lottery_delete');
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择记录');
        $num = Db::name('lottery')->whereIn('id', $ids)->delete();
        if (!$num) $this->error('删除失败');
        alog("live.lottery.del", '删除大转盘活动 ID：'.implode(",", $ids));
        $this->success("删除成功，共计删除{$num}条记录");
    }

    public function change_status()
    {
        $this->checkAuth('lottery:lottery:lottery_update');
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择记录');
        $status = input('status');
        if (!in_array($status, ['0', '1'])) $this->error('状态值不正确');
        $num = Db::name('lottery')->whereIn('id', $ids)->update(['status' => $status]);
        if (!$num) $this->error('切换状态失败');
        alog("live.lottery.edit", '编辑大转盘活动 ID：'.implode(",", $ids)." 修改状态：".($status == 1 ? "启用" : "禁用"));
        $this->success('切换成功');
    }
}
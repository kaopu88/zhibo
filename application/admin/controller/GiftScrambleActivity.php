<?php

namespace app\admin\controller;


use bxkj_common\CoreSdk;
use think\Db;
use think\facade\Request;


class GiftScrambleActivity extends Activity
{

    private static $redis_key = 'activity:', $act_name = 'gift_scramble:', $anchor_key = 'anchor:', $consumer_key = 'consumer:';

    public function update()
    {
        $this->checkAuth('admin:activity:select');
        if (Request::isGet()) {
            $data = [];
            $period = input('period');
            if (empty($period))
            {
                $auditService = new \app\admin\service\activity\GiftScramble();
                $extra_data = $auditService->getMaxPeriod();
                $extra_data['type'] = 'add';
            }
            else{
                $data = Db::name('activity_gift_scramble')->where('period', $period)->select();
                if (empty($data)) $this->error('赛道不存在');
                $extra_data = [
                    'start_time' => $data[0]['start_time'],
                    'end_time' => $data[0]['end_time'],
                    'start_time_str' => date('Y-m-d H:i:s', $data[0]['start_time']),
                    'end_time_str' => date('Y-m-d H:i:s', $data[0]['end_time']),
                    'period' => $data[0]['period'],
                    'period_str' => '第'.($data[0]['period']).'期',
                    'type' => 'edit',
                ];
            }

            return json_success(['arr' => json_encode($data), 'extra_data' => $extra_data], '获取成功');
        }
        else {
            $post = Request::post();
            $auditService = new \app\admin\service\activity\GiftScramble();
            $num = $auditService->edit($post);
            if (!$num) $this->error($auditService->getError());
            $this->success('操作成功');
        }
    }



    public function getAnchorRank()
    {
        $this->assign('rank', []);

        return $this->fetch('activity/gift_scramble/anchor_rank');
    }



    public function getConsumerRank()
    {
        $this->assign('rank', []);

        return $this->fetch('activity/gift_scramble/consumer_rank');
    }



    public function getHof()
    {
        $get = input();

        $total = $this->getTotal();

        $page = $this->pageshow($total);

        $rank = $this->getList($page->firstRow, $page->listRows);

        $user_ids = array_column($rank, 'user_id');

        $users = $this->getUsersInfo($user_ids);

        foreach ($rank as &$value)
        {
            if (array_key_exists($value['user_id'], $users))
            {
                $value['nickname'] = $users[$value['user_id']]['nickname'];
                $value['avatar'] = $users[$value['user_id']]['avatar'];
            }

            $value['points'] = number_format2($value['points']);
        }

        $this->assign('rank', $rank);

        return $this->fetch('activity/gift_scramble/hof');
    }


    private function getUsersInfo($userIds)
    {
        $arr = [];

        $CoreSdk = new CoreSdk();

        $users = $CoreSdk->getUsers($userIds);

        if (empty($users)) return $arr;

        foreach ($users as $info)
        {
            $arr[$info['user_id']] = $info;
        }

        return $arr;
    }


    private function getTotal()
    {
        return Db::name('activity_gift_scramble_hof')->count();
    }


    //获取列表
    private function getList($offset, $length)
    {
        $result = Db::name('activity_gift_scramble_hof')->order('num, points desc')->limit($offset, $length)->select();
        if (empty($result)) return [];
        return $result;
    }





}
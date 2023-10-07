<?php

namespace app\push\section;

use bxkj_common\DateTools;
use bxkj_common\RedisClient;
use bxkj_module\service\Service;
use bxkj_common\SectionMarkExecuter;
use think\Db;

class RechargeOrder extends SectionMarkExecuter
{
    protected $lockName = 'recharge_order';

    public function complete($data)
    {
        if ($data['status'] == 0) {
        }
    }

    public function handler($length = 10)
    {
        $total = 0;
        $this->wait();
        $this->lock();
        Service::startTrans();
        $where = [['processed', 'eq', '0']];
        $list = Db::name('recharge_order')->where($where)->order('id asc')->limit($length)->select();
        if (empty($list)) {
            Service::rollback();
            $this->unlock();
            return $this->success(true, $total);
        }
        $ids = [];
        foreach ($list as $item) {
            $ids[] = $item['id'];
        }
        if (!empty($ids)) {
            Db::name('recharge_order')->whereIn('id', $ids)->update([
                'processed' => 1
            ]);
        }
        Service::commit();
        $this->unlock();
        $userIds = Service::getIdsByList($list, 'user_id');
        $userIds = array_unique($userIds);
        $userList = [];
        if ($userIds) {
            $userList = Db::name('user')->whereIn('user_id', $userIds)->field('user_id,agent_id,promoter_uid,isvirtual')->select();
        }
        $userList = $userList ? $userList : [];
        foreach ($list as $item) {
            $user = Service::getItemByList($item['user_id'], $userList, 'user_id');
            $now = $item['create_time'];
            $update = [
                'year' => date('Y', $now),
                'month' => date('Ym', $now),
                'day' => date('Ymd', $now),
                'fnum' => DateTools::getFortNum($now)
            ];
            if ($user) {
                $update['isvirtual'] = $user['isvirtual'];
                $update['promoter_uid'] = $user['promoter_uid'];
                $update['agent_id'] = $user['agent_id'];
            }
            $num = Db::name('recharge_order')->where('id', $item['id'])->update($update);
            $total++;
        }
        return $this->success(false, $total);
    }


}
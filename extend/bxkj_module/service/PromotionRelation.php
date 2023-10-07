<?php

namespace bxkj_module\service;

use bxkj_common\Console;
use bxkj_common\HttpClient;
use bxkj_common\RedisClient;
use think\Db;

class PromotionRelation extends Service
{
    protected $httpClient;

    //释放当前推广员下的用户
    public function releaseByPromoter($promoter)
    {
        $where = ['promoter_uid' => $promoter['user_id'], 'agent_id' => $promoter['agent_id']];
        $num = Db::name('promotion_relation')->where($where)->update(['promoter_uid' => 0]);
        if ($num) {
            Db::name('promoter')->where(['user_id' => $promoter['user_id']])->update(['client_num' => 0]);
        }
        return $num;
    }

    //客户和代理商、推广员绑定
    public function bind($data)
    {
        if (empty($data['agent_id'])) return $this->setError('请选择'.config('app.agent_setting.agent_name'));
        if (empty($data['user_id'])) return $this->setError('请选择客户');
        $promoterUid = $data['promoter_uid'] ? $data['promoter_uid'] : 0;
        $where = ['agent_id' => $data['agent_id'], 'user_id' => $data['user_id']];
        Service::startTrans();
        $rel = Db::name('promotion_relation')->where($where)->find();
        if ($rel) {
            Service::rollback();
            return $this->setError('绑定关系已存在');
        }
        $user = Db::name('user')->where(['user_id' => $data['user_id']])->find();
        if (empty($user)) {
            Service::rollback();
            return $this->setError('用户不存在');
        }
        $id = Db::name('promotion_relation')->insertGetId([
            'promoter_uid' => $promoterUid,
            'user_id' => $data['user_id'],
            'agent_id' => $data['agent_id'],
            'create_time' => time()
        ]);
        if (!$id) {
            Service::rollback();
            return $this->setError('绑定失败');
        }
        $res = $this->afterBindHandler($user, $data['agent_id'], $promoterUid, ['type' => '', 'id' => 0], true);
        if (!$res) {
            Service::rollback();
            return $this->setError('绑定失败02');
        }
        Service::commit();
        if (!empty($res['transfer_id'])) {
            $this->requestKpiTransfer($res['transfer_id']);
        }
        return $id;
    }

    //取消和代理商的绑定
    public function unbindWithAgent($userIds, $agentId)
    {
        $userIds = is_array($userIds) ? $userIds : explode(',', trim($userIds));
        $hasLog = Db::name('kpi_transfer_log')->where(['user_id' => $userIds, 'status' => '0'])->count();
        if ($hasLog > 0) return $this->setError('取消失败等待业绩转移');
        $relations = Db::name('promotion_relation')->where([
            'user_id' => $userIds,
            'agent_id' => $agentId
        ])->select();
        $promoterUids = [];
        $total = 0;
        Db::name('promotion_relation_apply')->where(['user_id' => $userIds, 'agent_id' => $agentId])->delete();
        foreach ($relations as $relation) {
            $delRes = Db::name('promotion_relation')->where(['id' => $relation['id']])->delete();
            if ($delRes) {
                //将主播更换成平台主播
                $anchor = Db::name('anchor')->where(['user_id' => $relation['user_id']])->find();
                if (!empty($anchor) && $anchor['agent_id']) {
                    Db::name('anchor')->where(['user_id' => $relation['user_id']])->update(['agent_id' => 0]);
                }
                if (!empty($relation['promoter_uid'])) {
                    $promoterUids[] = $relation['promoter_uid'];
                }
                $total++;
            }
        }
        $promoterUids = array_unique($promoterUids);
        if (!empty($promoterUids)) {
            foreach ($promoterUids as $promoterUid) {
                $client_num = Db::name('promotion_relation')->where(['promoter_uid' => $promoterUid])->count();
                Db::name('promoter')->where(['user_id' => $promoterUid])->update(['client_num' => $client_num]);
            }
        }
        return $total;
    }

    //取消和指定推广员的绑定
    public function unbindWithPromoter($userIds, $promoterUid)
    {

    }

    //取消所有绑定关系
    public function unbindAll($userIds)
    {

    }

    protected function unbindRelations($relations)
    {
        $total = 0;
        $promoterUids = [];
        foreach ($relations as $relation) {
            $delRes = Db::name('promotion_relation')->where(['id' => $relation['id']])->delete();
            if ($delRes) {
                if (!empty($relation['promoter_uid'])) {
                    $promoterUids[] = $relation['promoter_uid'];
                }
                $total++;
            }
        }
        $promoterUids = array_unique($promoterUids);
        if (!empty($promoterUids)) {
            foreach ($promoterUids as $promoterUid) {
                $client_num = Db::name('promotion_relation')->where(['promoter_uid' => $promoterUid])->count();
                Db::name('promoter')->where(['user_id' => $promoterUid])->update(['client_num' => $client_num]);
            }
        }
    }


    /**
     * 绑定之后的处理
     *
     * @param $user
     * @param UserTransfer $UTransfer
     * @return array|bool
     */
    public function afterBindHandler($user, UserTransfer $UTransfer)
    {
        if (empty($user)) return false;

        $update = [];

        if (empty($user['first_agent_id'])) $update['first_agent_id'] = $UTransfer->target['agent_id'];
        if (empty($user['first_promoter_uid']) && !empty($UTransfer->target['promoter_uid'])) $update['first_promoter_uid'] = $UTransfer->target['promoter_uid'];

        //绑定首次经纪人与公会
        if ($update)
        {
            $userUpRes = Db::name('user')->where(['user_id' => $user['user_id']])->update($update);
            if ($userUpRes) User::updateRedis($user['user_id'], $update);
        }

        $transferId = 0;

        if (!empty($UTransfer->target['promoter_uid']))
        {
            if (empty($UTransfer->target['agent_id'])) return false;

            //50%机率更新终端数
            $tmp = mt_rand(0, 100);
            if ($tmp > 50) {
                Db::name('promoter')
                    ->where(['user_id' => $UTransfer->target['promoter_uid']])
                    ->setInc('client_num', 1);
            } else {
                $sum = Db::name('promotion_relation')
                    ->where(['promoter_uid' => $UTransfer->target['promoter_uid'], 'agent_id' => $UTransfer->target['agent_id']])
                    ->count();
                Db::name('promoter')
                    ->where(['user_id' => $UTransfer->target['promoter_uid']])
                    ->update(['client_num' => (int)$sum]);
            }

            $log = [
                'user_id' => $user['user_id'],
                'aid' => $UTransfer->admin['id'] ?: 0,
                'agent_id' => $UTransfer->target['agent_id'] ?: 0,
                'promoter_uid' => $UTransfer->target['promoter_uid'] ?: 0,
                'status' => '0',
                'admin_type' => $UTransfer->admin['type'] ?: 'erp',
                'create_time' => time()
            ];

            if (!empty($UTransfer->previous))
            {
                $log['old_agent_id'] = $UTransfer->previous['agent_id'];
                $log['old_promoter_uid'] = $UTransfer->previous['promoter_uid'];
            }

            $transferId = Db::name('kpi_transfer_log')->insertGetId($log);

            if (!$transferId) return false;

            // 转移业绩(设置转移并不是异步模式)
            if (!$UTransfer->async && $UTransfer->is_transfer)
            {
                $kpiTransfer = new KpiTransfer();

                //只转移当前用户在目标公会的无属业绩或当前用户无公会无经纪人的业绩(但现在设定为用户刷礼物后自动绑定主播所属公会)
                $kpiUser = [
                    'user_id' => $user['user_id'],
                    'agent_id' => $UTransfer->target['agent_id'],
                    'promoter_uid' => 0
                ];

                $transferRes = $kpiTransfer
                    ->setUser($kpiUser)
                    ->setReceiver($UTransfer->target['agent_id'], $UTransfer->target['promoter_uid'])
                    ->transfer($transferId);

                if (!$transferRes) return false;
            }
        }

        return ['transfer_id' => $transferId, 'update' => $update];
    }


    //异步请求
    public function requestKpiTransfer($transferId)
    {
        if (!isset($this->httpClient)) {
            $this->httpClient = new HttpClient([
                'timeout' => 0.2,
                'base' => PUSH_URL
            ]);
        }
        $this->httpClient->post('/kpi_transfer/handler', ['id' => $transferId]);
    }


}
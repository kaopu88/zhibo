<?php

namespace bxkj_module\service;

use bxkj_common\HttpClient;
use think\Db;

class Promoter extends KpiQuery
{
    //创建推广员
    public function create($inputData)
    {
        $agentId = $inputData['agent_id'];
        $userId = $inputData['user_id'];
        //强制创建，如果已经是其他代理商名下的了，则先解除，解除时将客户也一并转移到新代理商名下
        $force = $inputData['force'];
        if (empty($userId)) return $this->setError('请选择用户');
        if (empty($agentId)) return $this->setError('请选择'.config('app.agent_setting.agent_name'));
        if (!in_array($force, ['0', '1'])) return $this->setError('force参数错误');
        $agent = Db::name('agent')->where(['id' => $agentId, 'delete_time' => null])->find();
        if (empty($agent)) return $this->setError(config('app.agent_setting.agent_name').'不存在');
        $max_promoter_num = $agent['max_promoter_num'];
        if ($agent['promoter_num'] >= $max_promoter_num) return $this->setError(config('app.agent_setting.promoter_name').'人数已达到最大限额');
        Service::startTrans();
        $user = Db::name('user')->where([['user_id', '=', $userId], ['delete_time', 'null']])->find();
        if (empty($user)) {
            Service::rollback();
            return $this->setError('用户不存在');
        }
        //老的推广员身份
        $previous = Db::name('promoter')->where(['user_id' => $userId])->find();
        if ($previous) {
            if ($force != '1') {
                Service::rollback();
                return $this->setError('用户已是'.config('app.agent_setting.promoter_name'));
            }
            if ($previous['agent_id'] == $agentId) {
                Service::rollback();
                return $this->setError(config('app.agent_setting.promoter_name').'身份重复');
            }
            //解除老的推广员身份
            $removeNum = $this->remove($previous, null, $inputData['admin']);
            if (!$removeNum) {
                Service::rollback();
                return $this->setError('强制创建失败');
            }
        }
        //创建新的推广员身份
        $promoterData = [
            'agent_id' => $agentId,
            'user_id' => $userId,
            'create_time' => time(),
            'refresh_date' => time(),
            'client_num' => 0
        ];
        $res = Db::name('promoter')->insert($promoterData);
        if (!$res) {
            Service::rollback();
            return $this->setError('创建失败');
        }
        //记录操作日志
        $log = [
            'type' => 'add',
            'user_id' => $userId,
            'agent_id' => $promoterData['agent_id'],
            'aid' => $inputData['admin']['id'],
            'admin_type' => $inputData['admin']['type'],
            'act_time' => time(),
            'detail' => ''
        ];
        Db::name('promoter_log')->insertGetId($log);
        $update = ['is_promoter' => '1'];
        $num = Db::name('user')->where('user_id', $userId)->update($update);
        if ($num) {
            Db::name('agent')->where('id', $promoterData['agent_id'])->setInc('promoter_num', 1);
            \bxkj_module\service\User::updateRedis($userId, $update);
        }
        Service::commit();
        return $promoterData;
    }

    //取消推广员身份
    public function cancel($userIds, $agentId = null, $admin = null)
    {
        $total = 0;
        $userIds = is_array($userIds) ? $userIds : explode(',', $userIds);
        foreach ($userIds as $userId) {
            $res = $this->remove($userId, $agentId, $admin);
            if ($res) $total++;
        }
        return $total;
    }

    //取消代理商下面的所有推广员身份
    public function cancelByAgent($agentId, $admin)
    {
        $where = [['agent_id', 'eq', $agentId]];
        $promoters = Db::name('promoter')->where($where)->select();
        if (empty($promoters)) return $this->setError(config('app.agent_setting.agent_name').'名下没有'.config('app.agent_setting.promoter_name'));
        $total = 0;
        foreach ($promoters as $promoter) {
            $res = $this->remove($promoter, null, $admin);
            if ($res) $total++;
        }
        return $total;
    }

    //移除推广身份
    protected function remove($promoterUid, $agentId = null, $admin = null, $isRelease = true)
    {
        Service::startTrans();
        if (is_array($promoterUid) && isset($promoterUid['agent_id'])) {
            $promoter = $promoterUid;
        } else {
            $where = [['user_id', 'eq', $promoterUid]];
            Agent::agentWhere($where, ['agent_id' => $agentId]);
            $promoter = Db::name('promoter')->where($where)->find();
        }
        if (empty($promoter)) {
            Service::rollback();
            return $this->setError(config('app.agent_setting.promoter_name').'不存在');
        }
        $has = $this->hasTransferLog($promoter['user_id']);
        if ($has) {
            Service::rollback();
            return $this->setError(config('app.agent_setting.promoter_name').'有业绩尚未转移完成');
        }
        //释放名下的客户
        if ($isRelease) {
            $proRel = new PromotionRelation();
            $releaseRes = $proRel->releaseByPromoter($promoter);
        }
        $num = Db::name('promoter')->where('user_id', $promoter['user_id'])->delete();
        if (!$num) {
            Service::rollback();
            return $this->setError('取消失败');
        }
        $update = ['is_promoter' => '0'];
        $num2 = Db::name('user')->where('user_id', $promoter['user_id'])->update($update);
        if ($num2) {
            Db::name('agent')->where('id', $promoter['agent_id'])->setDec('promoter_num', 1);
            User::updateRedis($promoter['user_id'], $update);
        }
        //记录操作日志
        $log = [
            'type' => 'remove',
            'user_id' => $promoter['user_id'],
            'agent_id' => $promoter['agent_id'],
            'act_time' => time(),
            'detail' => json_encode($promoter),
            'admin_type' => $admin['type'],
            'aid' => $admin['id']
        ];
        Db::name('promoter_log')->insertGetId($log);
        Service::commit();
        return $num;
    }

    public function hasTransferLog($promoterUid)
    {
        $num = Db::name('kpi_transfer_log')->where(function ($query) use ($promoterUid) {
            $query->whereOr([
                ['old_promoter_uid', 'eq', $promoterUid],
                ['promoter_uid', 'eq', $promoterUid]
            ]);
        })->where('status', '0')->count();
        return $num;
    }

}
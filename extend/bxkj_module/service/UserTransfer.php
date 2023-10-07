<?php

namespace bxkj_module\service;

use bxkj_common\Console;
use bxkj_common\HttpClient;
use bxkj_common\RedisClient;
use think\Db;

class UserTransfer extends Service
{
    protected $httpClient = null;
    protected $from = [];
    protected $target = [];
    protected $async = false;//异步模式
    protected $admin = [];
    protected $ownAgentId = 0;
    protected $ownAgentIds = [];
    protected $groupKey;
    protected $callback;
    protected $is_transfer = 1;
    protected $previous = [];

    public function __get($name)
    {
        if (property_exists($this, $name))
            return $this->$name;
        else
            return '';
    }


    public function setTransfer($transfer)
    {
        $this->is_transfer = $transfer;
        return $this;
    }


    //设置指定用户
    public function setFromUsers($userIds)
    {
        $this->from['user_ids'] = is_array($userIds) ? $userIds : explode(',', trim($userIds));
        return $this;
    }

    //设置来源于推广员的所有客户
    public function setFromPromoter($promoterUid)
    {
        $this->from['promoter_uid'] = $promoterUid;
        return $this;
    }

    //设置来源于代理商的所有客户
    public function setFromAgent($agentId)
    {
        $this->from['agent_id'] = $agentId;
        return $this;
    }

    //设置目标推广员
    public function setTargetPromoter($promoterUid)
    {
        $this->target['promoter_uid'] = $promoterUid;
        return $this;
    }

    //设置目标代理商
    public function setTargetAgent($agentId)
    {
        $this->target['agent_id'] = $agentId;
        return $this;
    }

    //设置管理员信息
    public function setAdmin($type, $id)
    {
        $this->admin = ['type' => $type, 'id' => $id];
        return $this;
    }

    //设置所属代理商
    public function setOwnAgent($agentId)
    {
        $this->ownAgentId = $agentId;
        return $this;
    }

    //异步模式
    public function setAsync($async)
    {
        $this->async = $async;
        return $this;
    }

    public function setCallback($callback)
    {
        $this->callback = $callback;
        return $this;
    }

    //检查参数
    protected function checkParams()
    {
        //来源
        if (empty($this->from['user_ids']) && empty($this->from['agent_id']) && empty($this->from['promoter_uid'])) {
            return $this->setError('请选择来源');
        }

        if ($this->ownAgentId) {
            $ownAgent = Db::name('agent')->where([['id', 'eq', $this->ownAgentId], ['delete_time', 'null']])->find();
            if (empty($ownAgent)) return $this->setError('所属' . config('app.agent_setting.agent_name') . '不存在');
            $ownAgents = Db::name('agent')->where(['pid' => $ownAgent['id'], 'delete_time' => null])->select();
            $ownAgents = $ownAgents ? $ownAgents : [];
            $this->ownAgentIds = array_column($ownAgents, 'id');
            $this->ownAgentIds[] = $ownAgent['id'];
        }

        if (!empty($this->from['promoter_uid'])) {
            $where = ['user_id' => $this->from['promoter_uid']];
            if ($this->ownAgentIds) $where['agent_id'] = $this->ownAgentIds;
            $promoter = Db::name('promoter')->where($where)->find();
            if (empty($promoter)) return $this->setError('来源'.config('app.agent_setting.promoter_name').'不存在');
            $this->from['agent_id'] = $promoter['agent_id'];
        } else {
            $this->from['promoter_uid'] = 0;
        }
        //目标
        if (empty($this->target['promoter_uid']) && empty($this->target['agent_id'])) {
            return $this->setError('请选择目标');
        }
        if (!empty($this->target['promoter_uid'])) {
            $where2 = ['user_id' => $this->target['promoter_uid']];
            if ($this->ownAgentIds) $where2['agent_id'] = $this->ownAgentIds;
            $promoter2 = Db::name('promoter')->where($where2)->find();
            if (empty($promoter2)) return $this->setError('目标'.config('app.agent_setting.promoter_name').'不存在');
            $this->target['agent_id'] = $promoter2['agent_id'];
        } else {
            $this->target['promoter_uid'] = 0;
        }
        //管理员
        if (empty($this->admin['type']) || empty($this->admin['id'])) {
            return $this->setError('请设置管理员');
        }
        //权限范围
        if ($this->ownAgentId) {
            if (empty($this->from['user_ids']) && !in_array($this->from['agent_id'], $this->ownAgentIds)) {
                return $this->setError('来源' . config('app.agent_setting.agent_name') . '不在范围内');
            }
            if (!in_array($this->target['agent_id'], $this->ownAgentIds)) {
                return $this->setError('目标' . config('app.agent_setting.agent_name') . '不在范围内');
            }
        }
        return true;
    }

    /**
     * 转移用户
     *
     * @return bool|int|string
     */
    public function transfer()
    {
        $checkRes = $this->checkParams();
        if (!$checkRes) return false;

        //异步模式
        if ($this->async) {
            if (!$this->httpClient) {
                $this->httpClient = new HttpClient([
                    'timeout' => 3,
                    'base' => PUSH_URL
                ]);
            }
            $key = sha1(uniqid() . get_ucode());
            $inputData = [
                'from' => $this->from,
                'target' => $this->target,
                'admin' => $this->admin,
                'ownAgentId' => $this->ownAgentId,
                'callback' => $this->callback ? $this->callback : '',
                'task_key' => $key,
                'is_transfer' => $this->is_transfer,
            ];
            $this->httpClient->post('/user_transfer/handler', ['data' => json_encode($inputData)]);
            return $key;
        }

        $this->groupKey = 'bp_' . sha1(uniqid() . get_ucode());
        $offset = 0;
        $length = 200;
        $redis = RedisClient::getInstance();
        $vrKey = "transfer_uids:{$this->groupKey}";

        do {
            $users = $this->getSourceUsers($offset, $length);
            if ($users) {
                foreach ($users as $tmp) {
                    $redis->sAdd($vrKey, $tmp['user_id']);
                }
            }
            $offset += count($users);
            if (empty($users) || count($users) < $length) break;
        } while (true);

        $total = 0;

        if ($offset > 0) {
            $redis->setOption(\Redis::OPT_SCAN, \Redis::SCAN_RETRY);
            while ($arr_mems = $redis->sScan($vrKey, $iterator, '*', 100)) {
                foreach ($arr_mems as $vUserId) {
                    $res = $this->handlerOne(['user_id' => $vUserId]);
                    if ($res) $total++;
                }
            }

            $redis->del($vrKey);
        }
        return $total;
    }

    //获取来源用户(分批获取)
    protected function getSourceUsers($offset = 0, $length = 200)
    {
        //转移指定的用户
        if (!empty($this->from['user_ids']))
        {
            //先查看是否为公会现有名下
            if (!empty($this->ownAgentIds))
            {
                $users = Db::name('promotion_relation')->field('user_id')->where([
                    'user_id' => $this->from['user_ids'],
                    'agent_id' => $this->ownAgentIds
                ])->limit($offset, $length)->select();
            } //在用户表中获取指定的用户
            else {
                $users = Db::name('user')->alias('user')->join('__PROMOTION_RELATION__ rel', 'rel.user_id=user.user_id', 'LEFT')
                    ->field('user.user_id')
                    ->whereIn('user.user_id', $this->from['user_ids'])
                    ->limit($offset, $length)->select();
            }
        }
        else {
            //转移经纪人名下用户
            if ($this->from['promoter_uid']) {
                $where = [['promoter_uid', 'eq', $this->from['promoter_uid']]];
                $users = Db::name('promotion_relation')->field('user_id')->where($where)->limit($offset, $length)->select();
            } //转移公会名下用户
            else if ($this->from['agent_id']) {
                $where = [['agent_id', 'eq', $this->from['agent_id']]];
                $users = Db::name('promotion_relation')->field('user_id')->where($where)->limit($offset, $length)->select();
            }
        }
        return $users ? $users : [];
    }


    /**
     * 处理绑定
     *
     * @param $tmpUser
     * @return bool
     */
    protected function handlerOne($tmpUser)
    {
        $user = Db::name('user')
            ->field('user_id,isvirtual,first_agent_id,first_promoter_uid')
            ->where(['user_id' => $tmpUser['user_id'], 'delete_time' => null])
            ->find();

        if (empty($user)) return $this->setError('用户不存在');

        //以前的绑定关系
        $previous = Db::name('promotion_relation')->where(['user_id' => $user['user_id']])->select();

        if (!empty($previous))
        {
            $agent_ids = array_column($previous, 'agent_id');

            if (!in_array($this->target['agent_id'], $agent_ids) || count($agent_ids) > 1) return $this->setError('先解绑公会关系~');
        }

        if (!empty($previous))
        {
            $oldPromoterUid = (int)$previous[0]['promoter_uid'];
            $oldAgentId = (int)$previous[0]['agent_id'];

            if (!empty($this->target['promoter_uid']))
            {
                if ($oldPromoterUid == $this->target['promoter_uid']) return $this->setError('请勿重复转移到同一' . config('app.agent_setting.promoter_name') . '名下');
            } else {
                if ($oldAgentId == $this->target['agent_id']) return $this->setError('请勿重复转移到同一' . config('app.agent_setting.agent_name') . '名下');
            }

            $this->previous = $previous[0];
        }

        //如果有业绩未转移完不处理
        $has = Db::name('kpi_transfer_log')->where(['user_id' => $user['user_id'], 'status' => '0'])->count();

        if ($has > 0) return $this->setError('业绩转移尚未完成');

        $relData = [
            'agent_id' => $this->target['agent_id'],
            'promoter_uid' => $this->target['promoter_uid'] ?: 0,
            'user_id' => $user['user_id']
        ];

        $relNum = Db::name('promotion_relation')->where($relData)->count();

        if ($relNum > 0) return $this->setError('代理关系已存在');

        Service::startTrans();

        $relData['create_time'] = time();
        $relId = Db::name('promotion_relation')->insertGetId($relData);
        if (!$relId) {
            Service::rollback();
            return $this->setError('转移失败');
        }

        $logData = [
            'user_id' => $user['user_id'],
            'aid' => $this->admin['id'] ?: 0,
            'admin_type' => $this->admin['type'] ?: 'erp',
            'agent_id' => $this->target['agent_id'] ?: 0,
            'promoter_uid' => $this->target['promoter_uid'] ?: 0,
            'group_key' => $this->groupKey,
            'create_time' => time()
        ];
        if (!empty($this->previous))
        {
            $logData['old_agent_id'] = $this->previous['agent_id'];
            $logData['old_agent_id'] = $this->previous['promoter_uid'];
        }

        $logId = Db::name('user_transfer_log')->insertGetId($logData);
        if (!$logId) {
            Service::rollback();
            return $this->setError('转移失败02');
        }

        //如果已有绑定关系则删除
        if (!empty($previous))
        {
            $delNum = Db::name('promotion_relation')->where(['id' => $this->previous['id']])->delete();

            if (!$delNum) {
                Service::rollback();
                return $this->setError('转移失败03');
            }

            //上一个推广员的客户数量递减1
            if (!empty($this->previous['promoter_uid'])) Db::name('promoter')->where(['user_id' => $this->previous['promoter_uid']])->setDec('client_num', 1);
        }

        //绑定之后处理
        $proRel = new PromotionRelation();

        $relRes = $proRel->afterBindHandler($user, $this);

        if (!$relRes) {
            Service::rollback();
            return $this->setError('转移失败04');
        }

        Service::commit();

        //业绩记录特别多，需要异步转移
        if ($this->async && !empty($relRes['transfer_id']) && $this->is_transfer)
        {
            $proRel->requestKpiTransfer($relRes['transfer_id']);
        }

        return true;
    }


}
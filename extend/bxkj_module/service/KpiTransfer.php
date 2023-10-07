<?php

namespace bxkj_module\service;

use think\Db;

class KpiTransfer extends Service
{
    protected $user;
    protected $startTime;
    protected $endTime = null;
    protected $newAgent;
    protected $newPromoterUid = 0;
    protected $hasError = false;
    protected $updateRedis = true;

    public function setUser($user)
    {
        $this->user = $user;
        return $this;
    }

    public function setUpdateRedis($updateRedis = true)
    {
        $this->updateRedis = $updateRedis;
        return $this;
    }

    public function setReceiver($agent, $promoterUid = 0)
    {
        $this->newPromoterUid = 0;
        $this->newAgent = [];
        $agentId = is_array($agent) ? $agent['id'] : $agent;
        $promoter = null;
        if (!empty($promoterUid)) {
            $promoter = Db::name('promoter')->where(['user_id' => $promoterUid])->find();
            if (!empty($promoter)) {
                $this->newPromoterUid = $promoter['user_id'];
                //没有设置代理商则默认和推广员相同
                if (empty($agentId)) {
                    $agent = [];
                    $agentId = $promoter['agent_id'];
                }
            }
        }
        if (empty($agent) || !is_array($agent)) {
            $agent = Db::name('agent')->where(['id' => $agentId, 'delete_time' => null])->find();
            if (!empty($agent)) $this->newAgent = $agent;
        } else {
            $this->newAgent = $agent;
        }
        return $this;
    }

    public function timeLimit($startTime = 0, $endTime = null)
    {
        $this->startTime = $startTime;
        $this->endTime = $endTime;
        return $this;
    }

    //转移用户随之转移业绩
    public function transfer($id = null)
    {
        $detail = [];
        $detail['cons'] = $this->transferCons();
        $detail['fans'] = $this->transferFans();
        if ($id) {
            $update = ['result' => json_encode($detail), 'status' => '1'];
            Db::name('kpi_transfer_log')->where(['id' => $id])->update($update);
        }
        return $detail;
    }

    protected function transferCons()
    {
        $newAgentMark = $this->newAgent['id'] ? $this->newAgent['id'] : 0;
        $db = Db::name('kpi_cons');
        $this->setAttributionWhere($db, 'cons_uid');
        $list = $db->order('id asc')->select();
        $ids = [];
        $transferTotal = 0;//转移的总额
        $update = [];
        $this->setAgentUpdateData($update);
        foreach ($list as $item) {
            $num = Db::name('kpi_cons')->where('id', $item['id'])->update($update);
            $isTransfer = false;
            $agentMark = $item['agent_id'];
            $create_time = $item['create_time'];
            $total = $item['total_fee'];
            if ($num) {
                if ($this->updateRedis) {
                    $kpi = new Kpi($create_time);
                    if ($newAgentMark != $agentMark) {
                        if (!empty($agentMark)) {
                            //消除原来的
                            $res = $kpi->incr('agent', $agentMark, 'cons', 0 - $total, 1);
                            if (!$res) return false;
                            KpiQuery::clearCache("agent:all:{$agentMark}:cons", $create_time);
                        }
                        if ($newAgentMark) {
                            //加给现在的
                            $res = $kpi->incr('agent', $newAgentMark, 'cons', $total, 1);
                            if (!$res) return false;
                            $isTransfer = true;
                            KpiQuery::clearCache("agent:all:{$newAgentMark}:cons", $create_time);
                        }
                    }
                    if ($this->newPromoterUid != $item['promoter_uid']) {
                        if ($item['promoter_uid']) {
                            $res = $kpi->incr('promoter', $item['promoter_uid'], 'cons', 0 - $total, 1);
                            if (!$res) return false;
                            KpiQuery::clearCache("promoter:all:{$item['promoter_uid']}:cons", $create_time);
                            if (!empty($agentMark)) {
                                KpiQuery::clearCache("promoter:{$agentMark}:{$item['promoter_uid']}:cons", $create_time);
                            }
                        }
                        if ($this->newPromoterUid) {
                            $res = $kpi->incr('promoter', $this->newPromoterUid, 'cons', $total, 1);
                            if (!$res) return false;
                            KpiQuery::clearCache("promoter:all:{$this->newPromoterUid}:cons", $create_time);
                            if (!empty($this->newAgent)) {
                                KpiQuery::clearCache("promoter:{$this->newAgent['id']}:{$this->newPromoterUid}:cons", $create_time);
                            }
                            $isTransfer = true;
                        }
                    }
                }
                $ids[] = $item['id'];
            }
            if ($isTransfer) $transferTotal += $total;
        }
        return [
            'num' => count($ids),
            'transfer_total' => $transferTotal
        ];
    }

    //拉新记录
    protected function transferFans()
    {
        $newAgentMark = $this->newAgent['id'] ? $this->newAgent['id'] : 0;
        $db = Db::name('kpi_fans');
        $this->setAttributionWhere($db, 'user_id');
        $list = $db->order('id asc')->select();
        $ids = [];
        $transferTotal = 0;
        $update = [];
        $this->setAgentUpdateData($update);
        foreach ($list as $item) {
            $isTransfer = false;
            $agentMark = $item['agent_id'];
            $create_time = $item['create_time'];
            $total = 1;
            $num = Db::name('kpi_fans')->where('id', $item['id'])->update($update);
            if ($num) {
                if ($this->updateRedis) {
                    $kpi = new Kpi($create_time);
                    if ($newAgentMark != $agentMark) {
                        if (!empty($agentMark)) {
                            //消除原来的
                            $res = $kpi->incr('agent', $agentMark, 'fans', 0 - $total, 1);
                            if (!$res) return false;
                        }
                        if ($newAgentMark) {
                            //加给现在的
                            $res = $kpi->incr('agent', $newAgentMark, 'fans', $total, 1);
                            if (!$res) return false;
                            $isTransfer = true;
                        }
                    }
                    if ($this->newPromoterUid != $item['promoter_uid']) {
                        if ($item['promoter_uid']) {
                            $res = $kpi->incr('promoter', $item['promoter_uid'], 'fans', 0 - $total, 1);
                            if (!$res) return false;
                        }
                        if ($this->newPromoterUid) {
                            $res = $kpi->incr('promoter', $this->newPromoterUid, 'fans', $total, 1);
                            if (!$res) return false;
                            $isTransfer = true;
                        }
                    }
                }
                $ids[] = $item['id'];
            }
            if ($isTransfer) $transferTotal += $total;
        }
        return [
            'num' => count($ids),
            'transfer_total' => $transferTotal
        ];
    }


    //设置归属范围
    protected function setAttributionWhere(MyQuery &$db, $attrName)
    {
        $where = [
            [$attrName, 'eq', $this->user['user_id']]
        ];
        if (isset($this->startTime)) array_push($where, ['create_time', 'egt', $this->startTime]);

        if (isset($this->endTime)) array_push($where, ['create_time', 'lt', $this->endTime]);

        if (isset($this->user['promoter_uid'])) array_push($where, ['promoter_uid', 'eq', $this->user['promoter_uid']]);

        if (isset($this->user['agent_id'])) array_push($where, ['agent_id', 'eq', $this->user['agent_id']]);

        $db->where($where);

        return $db;
    }

    //设置代理信息
    protected function setAgentUpdateData(&$update)
    {
        $newPromoterUid = $this->newPromoterUid ? $this->newPromoterUid : 0;
        $update['promoter_uid'] = $newPromoterUid;
        $update['agent_id'] = $this->newAgent['id'] ? $this->newAgent['id'] : 0;
    }

}
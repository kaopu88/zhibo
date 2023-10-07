<?php

namespace app\service;

class PromotionRelation
{
    protected static $message;//错误消息
    protected static $code = 0;//错误码

    protected static function setError($message, $code = 1)
    {
        self::$message = $message;
        self::$code = $code;
        return false;
    }

    public function bind($data)
    {
        if (empty($data['agent_id'])) return $this->setError('请选择代理商');
        if (empty($data['user_id'])) return $this->setError('请选择客户');
        $promoterUid = isset($data['promoter_uid']) ? $data['promoter_uid'] : 0;
        $where = ['agent_id' => $data['agent_id'], 'user_id' => $data['user_id']];
        Db::startTrans();
        $rel = Db::findItem('promotion_relation', $where);
        if ($rel) {
            Db::rollback();
            return $this->setError('绑定关系已存在');
        }
        $user = Db::findItem('user', ['user_id' => $data['user_id']]);
        if (empty($user)) {
            Db::rollback();
            return $this->setError('用户不存在');
        }
        $id = Db::insert('promotion_relation', [
            'promoter_uid' => $promoterUid,
            'user_id' => $data['user_id'],
            'agent_id' => $data['agent_id'],
            'create_time' => time()
        ]);
        if (!$id) {
            Db::rollback();
            return $this->setError('绑定失败');
        }
        $res = $this->afterBindHandler($user, $data['agent_id'], $promoterUid, ['type' => '', 'id' => 0], true);
        if (!$res) {
            Db::rollback();
            return $this->setError('绑定失败02');
        }
        Db::commit();
        if (!empty($res['transfer_id'])) {
            $this->requestKpiTransfer($res['transfer_id']);
        }
        return $id;
    }

    //绑定之后的处理
    public function afterBindHandler($user, $agentId, $promoterUid, $admin, $async = true)
    {
        if (empty($user)) return false;
        $update = [];
        if (empty($user['first_agent_id'])) $update['first_agent_id'] = $agentId;
        if (empty($user['first_promoter_uid']) && !empty($promoterUid)) $update['first_promoter_uid'] = $promoterUid;
        if ($update) {
            $userUpRes = Db::update('user', 'user_id', $user['user_id'], $update);
            if ($userUpRes) User::updateRedis($user['user_id'], $update);
        }
        $transferId = 0;
        if (!empty($promoterUid)) {
            if (empty($agentId)) return false;
            $tmp = mt_rand(0, 100);
            if ($tmp > 50) {
                Db::setInc('promoter', 'user_id', $promoterUid, 'client_num', 1);
            } else {
                $sum = Db::count('promotion_relation', 'id', ['promoter_uid' => $promoterUid, 'agent_id' => $agentId]);
                Db::update('promoter', 'user_id', $promoterUid, ['client_num' => (int)$sum]);
            }
            //首次绑定推广员转移业绩
            if (empty($user['first_promoter_uid'])) {
                $transfer = $this->createTransferKpi([
                    'own_agent_id' => $agentId,
                    'user_id' => $user['user_id'],
                    'admin' => $admin,
                    'agent_id' => $agentId,
                    'promoter_uid' => $promoterUid
                ]);
                if (!$transfer) return false;
                $transferId = $transfer['id'];
                if (!$async) return false;
            }
        }
        return ['transfer_id' => $transferId, 'update' => $update];
    }

    protected function createTransferKpi($data)
    {
        $admin = $data['admin'];
        $log = [
            'user_id' => $data['user_id'],
            'aid' => $admin['id'] ? $admin['id'] : 0,
            'admin_type' => $admin['type'] ? $admin['type'] : 'erp',
            'old_agent_id' => $data['own_agent_id'],
            'old_promoter_uid' => 0,
            'agent_id' => $data['agent_id'] ? $data['agent_id'] : 0,
            'promoter_uid' => $data['promoter_uid'] ? $data['promoter_uid'] : 0,
            'start_time' => $data['start'],
            'end_time' => $data['end'],
            'status' => '0',
            'create_time' => time()
        ];
        $id = Db::insert('kpi_transfer_log', $log);
        if (!$id) return false;
        $log['id'] = $id;
        return $log;
    }

    //异步请求
    public function requestKpiTransfer($transferId)
    {
        global $config;
        $push_service_url = $config['push_service_url'];
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, rtrim($push_service_url, '/') . '/kpi_transfer/handler');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HEADER, 1);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_TIMEOUT_MS, 300);
        //设置post数据
        $post_data = ['id' => $transferId];
        curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
        $data = curl_exec($curl);
        curl_close($curl);
    }
}
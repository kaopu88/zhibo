<?php

namespace app\admin\service;

use bxkj_module\service\DsIM;
use bxkj_module\service\Service;
use think\Db;
use bxkj_module\service\UserRedis;
use bxkj_common\RabbitMqChannel;

class UserDataDeal extends Service
{
    public function getTotal($get){
        $this->db = Db::name('user_data_deal');
        $this->setWhere($get);
        return $this->db->count();
    }

    public function setWhere($get){
        $where = array();
        if (!empty($get['aid'])) {
            $where['aid'] = $get['aid'];
        }
        if ($get['audit_status'] != '') {
            $where['audit_status'] = $get['audit_status'];
        }
        $this->db->setKeywords(trim($get['keyword']),'','number user_id','');
        $this->db->where($where);
        return $this;
    }

    public function setOrder($get){
        $order = array();
        if (empty($get['sort'])) {
            if ($get['audit_status'] == '0') {
                $order['create_time'] = 'desc';
                $order['audit_status'] = 'asc';
            } else if (empty($get['audit_status'])) {
                $order['audit_status'] = 'asc';
                $order['create_time'] = 'desc';
            } else {
                $order['handle_time'] = 'desc';
                $order['id'] = 'desc';
            }
        }
        $this->db->order($order);
        return $this;
    }

    public function getList($get,$offset,$lenth){
        $this->db = Db::name('user_data_deal');
        $this->setWhere($get)->setOrder($get);
        $result = $this->db->limit($offset,$lenth)->select();
        $result = $result ? $result : [];
        $this->parseList($get,$result);
        return $result;
    }

    public function parseList($get,&$result){
        $relKey = 'user_id';
        $outKey = 'user';
        $recAccounts = $this->getRelList($result, [new User(), 'getUsersByIds'], 'user_id');

        $auditAdmins = [];
        if (empty($get['aid'])) {
            $auditAids = self::getIdsByList($result, 'aid');
            $auditAdmins = $auditAids ? Db::name('admin')->whereIn('id', $auditAids)->select() : [];
        }
        foreach ($result as &$item) {
            if (!empty($item['user_id'])) {
                $item[$outKey] = self::getItemByList($item['user_id'], $recAccounts, $relKey);
            }
            if (!empty($item['data'])) {
                $item['data'] = json_decode($item['data'],true);
            }
            if (empty($get['aid']) && !empty($item['aid'])) {
                $item['audit_admin'] = self::getItemByList($item['aid'], $auditAdmins);
            }
        }
    }

    public function handler($inputData, $aid = null)
    {
        $status = $inputData['audit_status'];
        $where = [['id', '=', $inputData['id']]];
        if (isset($aid)) $where[] = ['aid', '=', $aid];
        Service::startTrans();
        $order = Db::name('user_data_deal')->where($where)->find();
        if (empty($order)) return $this->setError('申请记录不存在');
        if ($order['audit_status'] != '0') return $this->setError('审核状态不正确');
        if (!in_array($status, ['1', '2'])) return $this->setError('处理状态不正确');
        if ($status == '2' && empty($inputData['handle_desc'])) return $this->setError('请填写备注信息');
        $update = ['handle_time' => time(), 'audit_status' => $status];
        $update['handle_desc'] = $inputData['handle_desc'] ? $inputData['handle_desc'] : '';
        $num = Db::name('user_data_deal')->where('id', $order['id'])->update($update);
        if (!$num) return $this->setError('处理失败');
        if ($status==1) {
            $data = json_decode($order['data'],true);
            $res = Db::name('user')->where('user_id', $order['user_id'])->update($data);
            if (!$res) Service::rollback();
            UserRedis::updateData($order['user_id'], $data);
            if ($data['avatar']){
                $DsIM = new DsIM();
                $DsIM->updateUserData($order['user_id']);
            }
        }
        if ($status==2) {
            //对接rabbitMQ
            $rabbitChannel = new RabbitMqChannel(['user.credit']);
            $rabbitChannel->exchange('main')->sendOnce('user.credit.user_data_turndown', ['user_id' => $order['user_id'], 'reason'=>$inputData['handle_desc']]);
        }
        Service::commit();
        return array_merge($order, $update);
    }

    public function batch_handler($ids, $aid = null)
    {
        $ids = is_array($ids) ? $ids : explode(',', $ids);
        if (empty($ids)) return $this->setError('请选择申请记录');
        $total = 0;
        foreach ($ids as $id) {
            $where = ['id' => $id, 'audit_status' => '0'];
            Service::startTrans();
            $order = Db::name('user_data_deal')->where($where)->find();
            $data = json_decode($order['data'],true);
            if (empty($order)) {
                Service::rollback();
                continue;
            }
            $update = ['handle_time' => time(), 'audit_status' => 1];
            $num = Db::name('user_data_deal')->where('id', $order['id'])->update($update);
            if (!$num) {
                Service::rollback();
                continue;
            }
            $res = Db::name('user')->where('user_id', $order['user_id'])->update($data);
            if (!$res) {
                Service::rollback();
                continue;
            }
            UserRedis::updateData($order['user_id'], $data);
            if ($data['avatar']){
                $DsIM = new DsIM();
                $DsIM->updateUserData($order['user_id']);
            }
            Service::commit();
            $total++;
        }
        if (!$total) return $this->setError('审核失败');
        return $total;
    }
}
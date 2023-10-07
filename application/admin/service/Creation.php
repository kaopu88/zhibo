<?php

namespace app\admin\service;

use bxkj_module\service\Message;
use bxkj_module\service\Service;
use bxkj_module\service\UserRedis;
use think\Db;

class Creation extends Service
{
    public function getTotal($get){
        $this->db = Db::name('creation');
        $this->setWhere($get);
        return $this->db->count();
    }

    public function setWhere($get){
        $where = array();
        if (!empty($get['aid'])) {
            $where['aid'] = $get['aid'];
        }
        if ($get['status'] != '') {
            $where['status'] = $get['status'];
        }
        $this->db->setKeywords(trim($get['keyword']),'','number user_id','');
        $this->db->where($where);
        return $this;
    }

    public function setOrder($get){
        $order = array();
        if (empty($get['sort'])) {
            if ($get['status'] == '0') {
                $order['create_time'] = 'asc';
                $order['id'] = 'asc';
            } else if (empty($get['status'])) {
                $order['create_time'] = 'desc';
                $order['id'] = 'desc';
            } else {
                $order['audit_time'] = 'desc';
                $order['id'] = 'desc';
            }
        }
        $this->db->order($order);
        return $this;
    }

    public function getList($get,$offset,$lenth){
        $this->db = Db::name('creation');
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
            if (empty($get['aid']) && !empty($item['aid'])) {
                $item['audit_admin'] = self::getItemByList($item['aid'], $auditAdmins);
            }
        }
    }

    public function handler($inputData, $aid = null)
    {
        $status = $inputData['status'];
        $where = [['id', '=', $inputData['id']]];
        if (isset($aid)) $where[] = ['aid', '=', $aid];
        Service::startTrans();
        $order = Db::name('creation')->where($where)->find();
        if (empty($order)) return $this->setError('申请记录不存在');
        if ($order['status'] != '0') return $this->setError('审核状态不正确');
        if (!in_array($status, ['1', '2'])) return $this->setError('处理状态不正确');
        if ($status == '2' && empty($inputData['remark'])) return $this->setError('请填写备注信息');
        $update = ['audit_time' => time(), 'status' => $status];
        $update['remark'] = $inputData['remark'] ? $inputData['remark'] : '';
        $num = Db::name('creation')->where('id', $order['id'])->update($update);
        $is_creation = $status=='1' ? '1' : '0';
        Db::name('user')->where('user_id', $order['user_id'])->update(['is_creation'=>$is_creation]);
        UserRedis::updateData($order['user_id'], ['is_creation'=>$is_creation]);
        if (!$num) return $this->setError('处理失败');
        $title = $status=='1' ? '您的创作号申请已经通过' : '您的创作号申请被驳回';
        $summary = $status=='1' ? '恭喜您已经是创作号了' : '很抱歉！您的创作号申请被驳回了';
        $message = new Message();
        $message->setSender('', 'helper')->setReceiver($order['user_id'])->sendNotice([
            'title' => $title,
            'text' => '',
            'url' => H5_URL.'/creative',
            'summary' => $summary
        ]);
        Service::commit();
        return array_merge($order, $update);
    }
}
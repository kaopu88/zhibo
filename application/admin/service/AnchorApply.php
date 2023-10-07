<?php

namespace app\admin\service;

use bxkj_module\service\Service;
use think\Db;

class AnchorApply extends Service
{
    public function getTotal($get)
    {
        $this->db = Db::name('anchor_apply');
        $this->setWhere($get);
        return $this->db->count();
    }

    public function setWhere($get)
    {
        $where = array();
        if ($get['status'] != '') {
            $where[] = ['status', '=', $get['status']];
        }
        if (trim($get['user_id']) != '') {
            $where[] = ['user_id', '=', trim($get['user_id'])];
        }

        $this->db->where($where);
        return $this;
    }

    public function setOrder()
    {
        $order = array();
        $order['id'] = 'desc';
        $order['create_time'] = 'asc';
        $this->db->order($order);
        return $this;
    }

    public function getList($get, $offset, $lenth)
    {
        $this->db = Db::name('anchor_apply');
        $this->setWhere($get)->setOrder($get);
        $result = $this->db->limit($offset, $lenth)->select();
        $result = $result ? $result : [];
        $this->parseList($result);
        return $result;
    }

    public function parseList(&$result)
    {
        $relKey = 'user_id';
        $outKey = 'user';
        $recAccounts = $this->getRelList($result, [new User(), 'getUsersByIds'], 'user_id');

        foreach ($result as &$item) {
            if (!empty($item['user_id'])) {
                $item[$outKey] = self::getItemByList($item['user_id'], $recAccounts, $relKey);
            }
            if ($item['status'] == 1) {
                $resVerify = Db::name('user_verified')->where(['user_id' => $item['user_id']])->order('id desc')->find();
                $item['reason'] = '待审核';
                if ($resVerify['status'] == '1') {
                    $item['reason'] = '已通过';
                }
                if ($resVerify['status'] == '2') {
                    $item['reason'] = '已驳回';
                }

            }
            if ($item['status'] == 4) {
                $resPromotionVerify = Db::name('promotion_relation_apply')->where(['user_id' => $item['user_id']])->order('id desc')->find();
                $item['reason'] = '待审核';
                if ($resPromotionVerify['status'] == 1) {
                    $item['reason'] = '已通过';
                }
                if ($resPromotionVerify['status'] == 2) {
                    $item['reason'] = '已驳回';
                }
            }
        }

    }

    public function approved($id, $status, $reason = '')
    {
        $apply = Db::name('anchor_apply')->where(['id' => $id])->find();
        if (empty($apply)) {
            return $this->setError('错误的信息');
        }

        $user = new \bxkj_module\service\User();
        // if ($status == 2) {
        //     $user_info = $user->getUser($apply['user_id']);
        //     if ($user_info['verified'] != '1') return $this->setError('用户实名还未通过');
        // }

        if ($apply['pay_status'] == 0) {
            $reason = !empty($reason) ? $reason : '';
            $data = ['status' => $status, 'reason' => $reason, 'review_time' => time()];
            if ($status == 2) {
                $type = $apply['agent_id'] ? 0 : 1;
                $anchorService = new \app\admin\service\Anchor();
                $res = $anchorService->create([
                    'agent_id' => $apply['agent_id'],
                    'user_id' => $apply['user_id'],
                    'force' => 0,
                    'admin' => [
                        'type' => 'erp',
                        'id' => AID
                    ]], $type);
                if (!$res) return $this->setError($anchorService->getError());
            }
        } else {
            $data = ['status' => 6];
        }
        if ($status == 5) $data = ['status' => $status];

        $res = Db::name('anchor_apply')->where(['id' => $id])->update($data);
        return true;
    }
}

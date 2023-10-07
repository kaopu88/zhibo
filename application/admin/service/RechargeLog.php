<?php

namespace app\admin\service;

use bxkj_module\service\Service;
use bxkj_common\CoreSdk;
use think\Db;

class RechargeLog extends Service
{
    public static function exchangeBeanQuantity($totalFee)
    {
        return $totalFee > 0 ? round($totalFee * 10) : 0;
    }

    public function add($inputData)
    {
        $data = $this->df->process('add@recharge_log', $inputData)->output();
        if ($data === false) return $this->setError($this->df->getError());
        $where2 = ['user_id' => $data['rec_account'], 'delete_time' => null];
        $user = Db::name('user')->where($where2)->field('user_id,isvirtual')->find();
        if ($data['pay_method'] == 'isvirtual' && $user['isvirtual'] == '0') return $this->setError('用户不是虚拟号');
        if ($data['pay_method'] != 'isvirtual' && $user['isvirtual'] == '1') return $this->setError('请选择虚拟号充值');
        $where = ['audit_status' => '0', 'rec_type' => $data['rec_type']];
        $where['rec_account'] = $data['rec_account'];
        $num = Db::name('recharge_log')->where($where)->count();
        if ($num > 0) return $this->setError('有一笔充值正在审核中');
        $data['order_no'] = get_order_no('recharge_log');
        $data['app_aid'] = AID;
        if ($data['pay_method'] == 'isvirtual') {
            $data['audit_aid'] = AID;
        } else {
            $workService = new Work();
            $data['audit_aid'] = $workService->allocation('audit_recharge', $data['rec_account'], $data['order_no']);
        }
        $data['audit_status'] = '0';
        $data['bean'] = self::exchangeBeanQuantity($data['total_fee']);
        unset($data['capital_fee']);
        $id = Db::name('recharge_log')->insertGetId($data);
        if (!$id) return $this->setError('提交失败');
        //虚拟号不需要审核
        if ($data['pay_method'] == 'isvirtual') {
            $order = Db::name('recharge_log')->where(['id' => $id])->find();
            if (empty($order)) return $this->setError('免审核失败01');
            $update = ['audit_time' => time(), 'audit_status' => '1'];
            $update['audit_remark'] = '虚拟号充值免审核';
            $res = $this->handlerSuccess($order);
            if (!$res) return $this->setError('免审核失败02');
            $num2 = Db::name('recharge_log')->where('id', $order['id'])->update($update);
            if (!$num2) return $this->setError('免审核失败03');
        }
        return $id;
    }

    public function validatePayInfo($value, $rule, $data = null, $more = null)
    {
        if ($data['pay_method'] == 'cash' || $data['pay_method'] == 'free' || $data['pay_method'] == 'isvirtual') {
            return true;
        } else {
            return !empty($value);
        }
    }

    public function validateRecAccount($value, $rule, $data = null, $more = null)
    {
        $type = $data['rec_type'];
        if ($type == 'user') {
            $num = Db::name('user')->where(['user_id' => $value, 'delete_time' => null])->count();
            return $num == 1;
        }
        return false;
    }


    public function getTotal($get)
    {
        $this->db = Db::name('recharge_log');
        $this->setWhere($get);
        return $this->db->count();
    }

    //获取列表
    public function getList($get, $offset, $length)
    {
        $this->db = Db::name('recharge_log');
        $this->setWhere($get)->setOrder($get);
        $result = $this->db->limit($offset, $length)->select();
        $result = $result ? $result : [];
        $this->parseList($get, $result);
        return $result;
    }

    protected function parseList($get, &$result)
    {
        $appAdmins = $this->getRelList($result, [new Admin(), 'getAdminsByIds'], 'app_aid');
        $recAccounts = [];
        $relKey = '';
        $outKey = '';
        if ($get['rec_type'] == 'user') {
            $relKey = 'user_id';
            $outKey = 'user';
            $recAccounts = $this->getRelList($result, [new User(), 'getUsersByIds'], 'rec_account');
        }

        $auditAdmins = [];
        if (empty($get['audit_aid'])) {
            $auditAids = self::getIdsByList($result, 'audit_aid');
            $auditAdmins = $auditAids ? Db::name('admin')->whereIn('id', $auditAids)->select() : [];
        }

        foreach ($result as &$item) {
            if (!empty($item['app_aid'])) {
                $item['app_admin'] = self::getItemByList($item['app_aid'], $appAdmins, 'id');
            }
            if (!empty($item['rec_account'])) {
                $item[$outKey] = self::getItemByList($item['rec_account'], $recAccounts, $relKey);
            }

            if (empty($get['audit_aid']) && !empty($item['audit_aid'])) {
                $item['audit_admin'] = self::getItemByList($item['audit_aid'], $auditAdmins);
            }

        }
    }

    private function setWhere($get)
    {
        $where = [['rec_type', '=', $get['rec_type']]];
        if ($get['audit_status'] != '') {
            $where[] = ['audit_status', '=', $get['audit_status']];
        }
        $this->db->where($where);
        $this->db->setKeywords(trim($get['keyword']), '', 'number rec_account', 'pay_account,number order_no');
        return $this;
    }

    //设置排序规则
    private function setOrder($get)
    {
        $order = array();
        if (empty($get['sort'])) {
            $order['create_time'] = 'desc';
            $order['id'] = 'desc';
        }
        $this->db->order($order);
        return $this;
    }


    //获取总记录数
    public function getAuditTotal($get)
    {
        $this->db = Db::name('recharge_log');
        $this->setAuditWhere($get);
        return $this->db->count();
    }

    //获取列表
    public function getAuditList($get, $offset, $length)
    {
        $this->db = Db::name('recharge_log');
        $this->setAuditWhere($get)->setAuditOrder($get);
        $result = $this->db->limit($offset, $length)->select();
        $result = $result ? $result : [];
        $this->parseList($get, $result);
        return $result;
    }

    //设置查询条件
    private function setAuditWhere($get)
    {
        $where = [['rec_type', '=', $get['rec_type']]];
        if ($get['audit_aid'] != '') {
            $where[] = ['audit_aid', '=', $get['audit_aid']];
        }
        if ($get['audit_status'] != '') {
            $where[] = ['audit_status', '=', $get['audit_status']];
        }
        $this->db->where($where);
        return $this;
    }

    //设置排序规则
    private function setAuditOrder($get)
    {
        $order = array();
        if (empty($get['sort'])) {
            if ($get['audit_status'] == '0') {
                $order['create_time'] = 'asc';
                $order['id'] = 'asc';
            } else if (empty($get['audit_status'])) {
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

    public function handler($inputData, $aid = null)
    {
        $status = $inputData['audit_status'];
        $where = [['id', '=', $inputData['id']]];
        if (isset($aid)) $where[] = ['audit_aid', '=', $aid];
        Service::startTrans();
        $order = Db::name('recharge_log')->where($where)->find();
        if (empty($order)) return $this->setError('申请记录不存在');
        if ($order['audit_status'] != '0') return $this->setError('审核状态不正确');
        if (!in_array($status, ['1', '2'])) return $this->setError('处理状态不正确');
        if ($status == '2' && empty($inputData['audit_remark'])) return $this->setError('请填写备注信息');
        $update = ['audit_time' => time(), 'audit_status' => $status];
        $update['audit_remark'] = $inputData['audit_remark'] ? $inputData['audit_remark'] : '';
        if ($status == '1') {
            $res = $this->handlerSuccess($order);
            if (!$res) return $this->setError('处理失败');
        }
        $num = Db::name('recharge_log')->where('id', $order['id'])->update($update);
        if (!$num) return $this->setError('处理失败');
        Service::commit();
        return array_merge($order, $update);
    }

    public function handlerSuccess($order)
    {
        $recOrderService = new RechargeOrder();
        $recOrder = $recOrderService->createByLog($order);
        if (!$recOrder) return $this->setError($recOrderService->getError());
        $thirdData['rel_no'] = $recOrder['order_no'];
        $thirdData['trade_no'] = uniqid() . get_ucode();
        $thirdData['pay_method'] = ($order['pay_method'] != 'free' && $order['pay_method'] != 'isvirtual') ? 'system_pay' : 'system_free';
        $result2 = $recOrderService->paySuccess($thirdData);
        if (!$result2) return $this->setError($recOrderService->getError());
        return true;
    }

}
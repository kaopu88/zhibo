<?php

namespace app\admin\service;

use bxkj_common\RedisClient;
use bxkj_module\service\ExpLevel;
use bxkj_module\service\Kpi;
use bxkj_module\service\Service;
use bxkj_module\service\UserRedis;
use think\Db;

class Loss extends Service
{
    public function getTotal($get)
    {
        $this->db = Db::name('loss');
        $this->setWhere($get);
        $total = $this->db->count();
        return (int)$total;
    }

    public function getList($get, $offset = 0, $length = 20)
    {
        $this->db = Db::name('loss');
        $this->setWhere($get);
        $this->setOrder($get);
        $fields = 'user.user_id,user.nickname,user.remark_name,user.phone,user.isvirtual,user.avatar,user.sign,user.level,user.gender,user.vip_expire,user.millet,user.fre_millet,user.millet_status,user.live_status,user.status';
        $this->db->field('loss.id,loss.bean,loss.audit_status,loss.audit_aid,loss.audit_time,loss.promoter_uid,loss.agent_id,loss.create_time,loss.reason');
        $list = $this->db->field($fields)->limit($offset, $length)->select();
        $this->parseList($list);
        return $list ? $list : [];
    }

    protected function setWhere($get)
    {
        $this->db->alias('loss');
        $where = [];
        if ($get['promoter_uid'] != '') {
            $where[] = ['loss.promoter_uid', '=', $get['promoter_uid']];
        } else if ($get['agent_id'] != '') {
            $where[] = ['loss.agent_id', '=', $get['agent_id']];
        }
        if ($get['audit_status'] != '') {
            $where[] = ['loss.audit_status', '=', $get['audit_status']];
        }
        if ($get['audit_aid'] != '') {
            $where[] = ['loss.audit_aid', '=', $get['audit_aid']];
        }
        $this->db->setKeywords(trim($get['keyword']), 'phone user.phone', 'number loss.user_id', 'number user.phone,user.nickname');
        $this->db->join('__USER__ user', 'user.user_id=loss.user_id', 'LEFT');
        $this->db->where($where);
        return $this;
    }

    protected function setOrder($get)
    {
        $this->db->order('loss.create_time asc,loss.id asc');
    }

    public function parseList(&$list)
    {
        $agentService = new Agent();
        $userService = new User();
        list($agentIds, $promoterIds, $userIds) = self::getIdsByList($list, 'agent_id|promoter_uid|user_id', true);
        $promoterList = $userService->getUsersByIds($promoterIds);
        $agentList = $agentService->getAgentsByIds($agentIds);
        $beans = $userIds ? Db::name('bean')->field('user_id,last_pay_time,bean')->whereIn('user_id', $userIds)->select() : [];
        $admins = $this->getRelList($list, function ($aids) {
            $adminService = new Admin();
            $admins = $adminService->getAdminsByIds($aids);
            return $admins;
        }, 'audit_aid');
        $loss_after_months = config('app.loss_after_months');
        $loss_min_bean = config('app.loss_min_bean');
        $time = strtotime("-{$loss_after_months} months");//两个月前
        $invalidIds = [];
        foreach ($list as $i => &$item) {
            $item = array_merge($item, ExpLevel::getLevelInfo($item['level']));
            $item['bean_info'] = self::getItemByList($item['user_id'], $beans, 'user_id');
            if (!empty($item['audit_aid'])) {
                $item['audit_admin'] = self::getItemByList($item['audit_aid'], $admins, 'id');
            }
            $item['promoter_info'] = self::getItemByList($item['promoter_uid'], $promoterList, 'user_id');
            $item['agent_info'] = self::getItemByList($item['agent_id'], $agentList, 'id');
            $item['invalid'] = '0';
            if (($item['bean_info']['last_pay_time'] > $time || $item['bean_info']['bean'] < $loss_min_bean) && $item['audit_status'] == '0') {
                $item['invalid'] = '1';
                $invalidIds[] = $item['id'];
            }
        }
        if (!empty($invalidIds)) {
            Db::name('loss')->whereIn('id', $invalidIds)->delete();
        }
    }

    public function clear($ids, $aid = null)
    {
        $ids = is_array($ids) ? $ids : explode(',', $ids);
        if (empty($ids)) return $this->setError('请选择申请记录');
        $loss_after_months = config('app.loss_after_months');
        $time = strtotime("-{$loss_after_months} months");//两个月前
        $loss_min_bean = config('app.loss_min_bean');
        $now = time();
        $invalidIds = [];
        $userService = new User();
        $userTransferService = new UserTransfer();
        $total = 0;
        foreach ($ids as $id) {
            $where = ['id' => $id, 'audit_status' => '0'];
            if (!empty($aid)) $where['audit_aid'] = $aid;
            self::startTrans();
            $lossInfo = Db::name('loss')->where($where)->find();
            if (empty($lossInfo)) {
                self::rollback();
                continue;
            }
            $beanInfo = Db::name('bean')->where('user_id', $lossInfo['user_id'])->find();
            if (empty($beanInfo)) {
                self::rollback();
                continue;
            }
            //用户最后消费时间变化,或者斗币消费完了，所以申请无效，自动删除
            $userId = $lossInfo['user_id'];
            $bean = $beanInfo['bean'] - $beanInfo['loss_bean'];
            if ($beanInfo['last_pay_time'] > $time || $bean < $loss_min_bean) {
                $invalidIds[] = $id;
                self::rollback();
                continue;
            }
            //结算给config('app.agent_setting.promoter_name')和config('app.agent_setting.agent_name')
            $kpi = new Kpi($now);
            $user = $userService->getBasicInfo($userId, null);
            if (empty($user) || empty($user['agent_id'])) {
                self::rollback();
                continue;
            }
            $kpiLog = [
                'total' => $bean,
                'loss_total' => 0,
                'log_no' => $id,
                'trade_type' => 'loss',
                'trade_no' => $id
            ];
            $consRes = $kpi->cons($user, $kpiLog);
            if (!$consRes) {
                self::rollback();
                continue;
            }
            $lossNum = Db::name('loss')->where('id', $id)->update([
                'audit_status' => '1',
                'audit_time' => $now,
                'bean' => $bean
            ]);
            if (!$lossNum) {
                self::rollback();
                continue;
            }
            $update2 = ['loss_bean' => $beanInfo['loss_bean'] + $bean];
            $beanNum = Db::name('bean')->where('user_id', $userId)->update($update2);
            if (!$beanNum) {
                self::rollback();
                continue;
            }
            UserRedis::updateData($userId, $update2);
            $res2 = $userTransferService->clearUaByUser($userId, $lossInfo['audit_aid']);
            if (!$res2) {
                self::rollback();
                continue;
            }
            self::commit();
            $total++;
        }
        if (!empty($invalidIds)) {
            Db::name('loss')->whereIn('id', $invalidIds)->delete();
        }
        if (!$total) return $this->setError('清算失败');
        return $total;
    }

    public function turnDown($inputData, $aid = null)
    {
        $id = $inputData['id'];
        if (empty($id)) return $this->setError('请选择申请记录');
        if (empty($inputData['reason'])) return $this->setError('请填写驳回原因');
        $where = ['id' => $id, 'audit_status' => '0'];
        if (!empty($aid)) $where['audit_aid'] = $aid;
        $num = Db::name('loss')->where($where)->update([
            'audit_status' => '2',
            'audit_aid' => $aid,
            'audit_time' => time(),
            'reason' => $inputData['reason'] ? $inputData['reason'] : ''
        ]);
        if (!$num) return $this->setError('驳回失败');
        return $num;
    }

}
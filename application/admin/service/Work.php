<?php

namespace app\admin\service;

use bxkj_common\RedisClient;
use bxkj_module\service\Service;
use think\facade\Env;
use think\Db;
use think\facade\Log;

class Work extends \bxkj_module\service\Work
{
    protected $workTypes;

    public function __construct()
    {
        parent::__construct();
//        $this->workTypes = config('enum.work_types');
        $this->workTypes = Db::name('work_types')->field('name, type as value, default_aid')->select();
    }

    //获取总记录数
    public function getTotal($get)
    {
        $this->db = Db::name('admin_work');
        $this->setWhere($get);
        return $this->db->count();
    }

    //获取列表
    public function getList($get, $offset, $length)
    {
        $this->db = Db::name('admin_work');
        $this->setWhere($get)->setOrder($get);

        $result = $this->db->limit($offset, $length)->select();

        foreach ($result as &$item) {
            $item['type_name'] = Db::name('work_types')->where(['type'=>$item['type']])->value('name');
            $item['admin_info'] = Db::name('admin')->where(['id' => $item['aid']])->find();
        }
        return $result;
    }

    //设置查询条件
    private function setWhere($get)
    {
        $where = array();
        $this->db->setKeywords(trim($get['keyword']), '', 'number aid');
        if (!empty($get['type'])) {
            $where['type'] = $get['type'];
        }
        $this->db->where($where);
        return $this;
    }

    //设置排序规则
    private function setOrder($get)
    {
        $order = array();
        $order['create_time'] = 'desc';
        $order['id'] = 'desc';
        $this->db->order($order);
        return $this;
    }


    public function getWorks($aid)
    {
        $groupWorks = $this->getWorksByAidGroup($aid);
        $redis = RedisClient::getInstance();
        $list = [];
        foreach ($this->workTypes as $workType) {
            if (in_array($workType['value'], $groupWorks)) {
                $item = [
                    'name' => $workType['name'],
                    'value' => $workType['value'],
                    'status' => '0',
                    'task_num' => 0,
                    'unread_num' => 0,
                    'sms_status' => '0'
                ];
                $row = Db::name('admin_work')->where(['type' => $workType['value'], 'aid' => $aid])->find();
                if ($row) {
                    $item['status'] = $row['status'];
                    $item['sms_status'] = $row['sms_status'];
                    $item['task_num'] = $row['task_num'];
                    $item['unread_num'] = $row['unread_num'];
                }
                if ($item['status'] == '0') $item['sms_status'] = '0';
                $list[] = $item;
            }
        }
        return $list;
    }

    protected function getWorksByAidGroup($aid)
    {
        $groupWorks = [];
        if ($aid == ROOT_UID) {
            foreach ($this->workTypes as $workType) {
                $groupWorks[] = $workType['value'];
            }
            return $groupWorks;
        }
        $groupAccess = Db::name('admin_group_access')->where([['uid', 'eq', $aid]])->select();
        $groupIds = self::getIdsByList($groupAccess, 'gid');

        if ($groupIds) {
            $groups = Db::name('admin_group')->whereIn('id', $groupIds)->select();
            foreach ($groups as $group) {
                if (!empty($group['works'])) {
                    $works = explode(',', $group['works']);
                    $groupWorks = array_merge($groupWorks, $works);
                }
            }
        }
        return $groupWorks;
    }


    public function changeStatus($aid, $type, $status)
    {
        if (!in_array($status, ['0', '1'])) return $this->setError('状态不合法');
        $groupWorks = $this->getWorksByAidGroup($aid);

        if (!in_array($type, $groupWorks)) return $this->setError('您没有权限开启此项工作');
        $row = Db::name('admin_work')->where([
            ['aid', 'eq', $aid],
            ['type', 'eq', $type]
        ])->find();

        $update = ['status' => $status];
        $actName = '';
        if ($status == '1') {
            $update['online_time'] = time();
            $actName = '上线';
        } else {
            $update['offline_time'] = time();
            $actName = '下线';
        }
        if (empty($row)) {
            $row = [];
            $row['aid'] = $aid;
            $row['type'] = $type;
            $row['sms_status'] = '1';
            $row['create_time'] = time();
            $row = array_merge($row, $update);
            $id = Db::name('admin_work')->insertGetId($row);
            if (!$id) return $this->setError($actName . '失败');
            $row['id'] = $id;
        } else {
            $num = Db::name('admin_work')->where('id', $row['id'])->update($update);
            if (!$num) return $this->setError($actName . '失败');
        }
        $redis = RedisClient::getInstance();
        $redis->lRem("allocation:{$type}", $aid, 0);
        if ($status == '1') $redis->lPush("allocation:{$type}", $aid);
        return $row;
    }

    public function changeSmsStatus($aid, $type, $status)
    {
        if (!in_array($status, ['0', '1'])) return $this->setError('状态不合法');
        $groupWorks = $this->getWorksByAidGroup($aid);
        if (!in_array($type, $groupWorks)) return $this->setError('您没有权限开启此项工作');
        $row = Db::name('admin_work')->where([['aid', 'eq', $aid], ['type', 'eq', $type]])->find();
        if ($status == '1' && (empty($row) || $row['status'] == '0')) return $this->setError('请先将工作状态设置为在线');
        $update = ['sms_status' => $status];
        $num = Db::name('admin_work')->where('id', $row['id'])->update($update);
        $actName = $status == '0' ? '关闭' : '开启';
        if (!$num) return $this->setError($actName . '失败');
        return $num;
    }

    public function taskTransfer($inputData, $aid)
    {
        $id = $inputData['id'];
        $type = $inputData['type'];
        $receiveAid = $inputData['aid'];
        if (empty($id)) return $this->setError('请选择任务');
        if (!enum_in($type, 'work_types')) return $this->setError('任务类型不支持');
        if (empty($receiveAid)) return $this->setError('请选择接手人');
        if ($receiveAid == $aid) return $this->setError('接手人不能是自己');
        $where = ['id' => $receiveAid, 'status' => '1'];
        Service::startTrans();
        $receiveAdmin = Db::name('admin')->where($where)->find();
        if (empty($receiveAdmin)) return $this->setError('接手人不存在');
        $funName = parse_name($type, 1, false) . 'Transfer';
        if (!method_exists($this, $funName)) return $this->setError('任务类型不支持');
        $res = call_user_func_array([$this, $funName], [$id, $aid, $receiveAid]);
        if (!$res) {
            Service::rollback();
            return false;
        }
        $log = [
            'aid' => $aid,
            'receive_aid' => $receiveAid,
            'type' => $type,
            'rel_id' => $id,
            'status' => '1',
            'handler_time' => time(),
            'create_time' => time()
        ];
        $id = Db::name('task_transfer_log')->insertGetId($log);
        if (!$id) {
            Service::rollback();
            return $this->setError('转交失败');
        }
        Service::commit();
        return $id;
    }

    protected function auditRechargeTransfer($id, $aid, $receiveAid)
    {
        $where = ['id' => $id, 'audit_aid' => $aid, 'audit_status' => '0'];
        $order = Db::name('recharge_log')->where($where)->find();
        if (!$order) return $this->setError('任务不存在');
        $num = Db::name('recharge_log')->where('id', $id)->update(['audit_aid' => $receiveAid]);
        if (!$num) return $this->setError('任务更新失败');
        $this->incr($receiveAid, 'audit_recharge', 1, $order['order_no']);
        return true;
    }

    protected function auditWxappCommentTransfer($id, $aid, $receiveAid)
    {
        $where = ['id' => $id, 'audit_aid' => $aid, 'audit_status' => '0'];
        $num = Db::name('article_comment')->where($where)->update(['audit_aid' => $receiveAid]);
        if (!$num) return $this->setError('任务更新失败');
        $this->incr($receiveAid, 'audit_wxapp_comment', 1, $id);
        return true;
    }

    protected function auditFilmTransfer($id, $aid, $receiveAid)
    {
        $where = ['id' => $id, 'aid' => $aid, 'audit_status' => '0'];
        $num = Db::name('film')->where($where)->update(['aid' => $receiveAid]);
        if (!$num) return $this->setError('任务更新失败');
        $this->incr($receiveAid, 'audit_film', 1, $id);
        return true;
    }

    public function delete($id)
    {
        $work = Db::name('admin_work')->where(['id'=>$id])->find();
        if (empty($work)) return $this->setError('请选择任务');
        $num = Db::name('admin_work')->where(['id'=>$id])->delete();
        if (!$num) return $this->setError('删除失败');
        $redis = RedisClient::getInstance();
        $redis->lRem("allocation:{$work['type']}", $work['aid'], 0);
        return $num;
    }

}
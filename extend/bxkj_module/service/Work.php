<?php

namespace bxkj_module\service;

use bxkj_common\DateTools;
use bxkj_common\RedisClient;
use think\Db;

class Work extends Service
{
    protected $workConfig;

    public function allocation($type, $relId = '', $orderNo = '', $incr = 1)
    {
        $this->workConfig = Db::name('work_types')->where(['type' => $type])->find();
        if (empty($this->workConfig)) return 0;
        $aid = 0;
        if (!empty($relId)) {
            $hisAllocation = Db::name('admin_allocation')->where([
                ['type', 'eq', $type],
                ['rel_id', 'eq', $relId]
            ])->order(['create_time' => 'desc'])->find();
            if ($hisAllocation) {
                $online = $this->checkOnline($type, $hisAllocation['aid']);
                if ($online) $aid = $hisAllocation['aid'];
            }
        }
        $key = "allocation:{$type}";
        $redis = RedisClient::getInstance();
        if (empty($aid)) {
            $aid = $redis->rpoplpush($key, $key);
            if (empty($aid)) {
                $aid = (int)$this->workConfig['default_aid'];
            }
        }
        if (!empty($aid)) {
            if ($incr > 0) $this->incr($aid, $type, $incr, $orderNo);
            if (!empty($relId)) {
                Db::name('admin_allocation')->insertGetId([
                    'aid' => $aid,
                    'rel_id' => $relId,
                    'type' => $type,
                    'create_time' => time()
                ]);
            }
        }
        return $aid;
    }

    public function checkOnline($type, $aid)
    {
        $info = Db::name('admin_work')->where(['aid' => $aid, 'type' => $type])->find();
        return $info ? $info['status'] == '1' : false;
    }

    public function incr($aid, $type, $incr, $orderNo = '')
    {
        if ($incr <= 0) return false;
        Service::startTrans();
        $work = Db::name('admin_work')->where(['aid' => $aid, 'type' => $type])->find();
        if ($work) {
            Db::name('admin_work')->where('id', $work['id'])->update([
                'unread_num' => $work['unread_num'] + $incr,
                'task_num' => $work['task_num'] + $incr,
                'last_time' => time()
            ]);
            $now = time();
            $diff = $now - $work['last_time'];
            $allow = ($now >= mktime(8, 0, 0) && $now < mktime(23, 30, 0)) ? true : false;//工作时间8：:00-23:30
            //发送短信
            if ($work['sms_status'] == '1' && $allow && ($work['unread_num'] == 0 || $diff >= mt_rand(3600, 7200))) {
                $admin = Db::name('admin')->where('id', $aid)->find();
                if (!empty($admin['phone'])) {
                    $template = enum_attr('sms_code_scenes', 'admin_task',  'sms_tpl');
                   /* Sms::send($admin['phone'], $template, [
                        'task_name' => $this->workConfig['name'] . '任务',
                        'task_num' => $orderNo ? $orderNo : date('Y-m-d H:i:s')
                    ]);*/
                }
            }
            Service::commit();
        } else {
            Service::rollback();
        }
    }

    public static function read($aid, $type = null)
    {
        $where = [['aid', 'eq', $aid], ['unread_num', 'neq', 0]];
        if (!empty($type)) $where[] = ['type', 'eq', $type];
        Db::name('admin_work')->where($where)->update(['unread_num' => 0, 'read_time' => time()]);
    }

}
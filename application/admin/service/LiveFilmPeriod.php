<?php

namespace app\admin\service;

use bxkj_module\service\Service;
use think\Db;

class LiveFilmPeriod extends Service
{
    public function getAnchorTotal($get)
    {
        $this->db = Db::name('anchor');
        $this->setAnchorWhere($get);
        $total = $this->db->count();
        return (int)$total;
    }

    public function getAnchorList($get, $offset = 0, $length = 20)
    {
        $this->db = Db::name('anchor');
        $this->setAnchorWhere($get);
        $this->setAnchorOrder($get);
        $fields = 'user.user_id,user.nickname,user.remark_name,user.phone,user.isvirtual,user.avatar,user.sign,user.level,user.gender,user.vip_expire,user.millet,anchor.create_time,user.fre_millet,user.millet_status,user.live_status,user.status,anchor.agent_id,anchor.total_millet,anchor.total_duration';
        $list = $this->db->field($fields)->limit($offset, $length)->select();
        $anchorService = new Anchor();
        $anchorService->parseList($list);
        $startTime = $get['start_time'] ? strtotime($get['start_time']) : 0;
        $endTime = $get['end_time'] ? strtotime($get['end_time']) : 0;
        foreach ($list as &$item) {
            $item['available_status'] = '1';
            if ($startTime && $endTime) {
                $db = Db::name('live_film_period');
                $db->where(['user_id' => $item['user_id']]);
                $this->checkOccupyWhere($db, $startTime, $endTime, '');
                $row = $db->order('start_time asc,id asc')->find();
                if ($row) {
                    $item['available_status'] = '0';
                    $item['conflict_start_time'] = $row['start_time'];
                    $item['conflict_end_time'] = $row['end_time'];
                }
            }
        }
        return $list ? $list : [];
    }

    protected function setAnchorWhere($get)
    {
        $this->db->alias('anchor');
        $where = [['anchor.delete_time', 'null'], ['anchor.join_live_film', 'eq', '1']];
        $this->db->setKeywords(trim($get['keyword']), 'phone user.phone', 'number anchor.user_id', 'number user.phone,user.nickname');
        $startTime = $get['start_time'] ? strtotime($get['start_time']) : 0;
        $endTime = $get['end_time'] ? strtotime($get['end_time']) : 0;
        if ($get['available_status'] != '' && !empty($startTime) && !empty($endTime)) {
            if ($get['available_status'] == '1') {
                $this->db->where(function ($query) use ($startTime, $endTime) {
                    $query->where([['period.id', 'null']]);
                    $query->whereOr(function ($query2) use ($startTime, $endTime) {
                        $query2->where([['period.id', 'not null']]);
                        $query2->where(function ($query3) use ($startTime, $endTime) {
                            $query3->whereOr([
                                ['period.end_time', '<=', $startTime],
                                ['period.start_time', '>=', $endTime],
                            ]);
                        });
                    });
                });
            } else {
                $this->checkOccupyWhere($this->db, $startTime, $endTime);
            }
        }
        $this->db->group('anchor.user_id');
        $this->db->join('__USER__ user', 'user.user_id=anchor.user_id', 'LEFT');
        $this->db->join('__LIVE_FILM_PERIOD__ period', 'period.user_id=anchor.user_id', 'LEFT');
        $this->db->where($where);
        return $this;
    }

    protected function setAnchorOrder($get)
    {
        $this->db->order('anchor.create_time desc,anchor.id desc');
    }

    public static function checkOccupyWhere(&$db, $startTime, $endTime, $prefix = 'period.')
    {
        /*
         * 现有一个时间区间[10-12] 求与其重合的区间，满足以下条件与其重合
         * 开始时间>=10 且 小于12 或者 结束时间>10且开始时间小于等于12
         * */
        $db->where(function ($query) use ($prefix, $startTime, $endTime) {
            $query->where(function ($query2) use ($prefix, $startTime, $endTime) {
                $query2->where([
                    ["{$prefix}start_time", '>=', $startTime],
                    ["{$prefix}start_time", '<', $endTime]
                ]);
            });
            $query->whereOr(function ($query2) use ($prefix, $startTime, $endTime) {
                $query2->where([
                    ["{$prefix}end_time", '>', $startTime],
                    ["{$prefix}start_time", '<=', $endTime]
                ]);
            });
        });
    }

    public function getInfo($anchorUid, $startTimeStr = '', $endTimeStr = '')
    {
        $startTime = $startTimeStr ? strtotime($startTimeStr) : 0;
        $endTime = $endTimeStr ? strtotime($endTimeStr) : 0;
        $this->db = Db::name('anchor');
        $this->db->alias('anchor');
        $fields = 'user.user_id,user.nickname,user.remark_name,user.phone,user.isvirtual,user.avatar,user.sign,user.level,user.gender,user.vip_expire,user.millet,anchor.create_time,user.fre_millet,user.millet_status,user.live_status,user.status,anchor.agent_id,anchor.total_millet,anchor.total_duration';
        $this->db->join('__USER__ user', 'user.user_id=anchor.user_id', 'LEFT');
        $where = [['anchor.delete_time', 'null'], ['anchor.join_live_film', 'eq', '1']];
        $where[] = ['user.user_id', '=', $anchorUid];
        $info = $this->db->where($where)->field($fields)->find();
        if ($info) {
            $info['available_status'] = '1';
            if ($startTime && $endTime) {
                $db = Db::name('live_film_period');
                $db->where(['user_id' => $info['user_id']]);
                $this->checkOccupyWhere($db, $startTime, $endTime, '');
                $row = $db->order('start_time asc,id asc')->find();
                if ($row) {
                    $info['available_status'] = '0';
                    $info['conflict_start_time'] = $row['start_time'];
                    $info['conflict_end_time'] = $row['end_time'];
                    $info['conflict_start_time_str'] = date('m-d H:i:s', $info['conflict_start_time']);
                    $info['conflict_end_time_str'] = date('m-d H:i:s', $info['conflict_end_time']);
                }
            }
        }
        return $info;
    }


}
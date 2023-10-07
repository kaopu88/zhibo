<?php

namespace app\admin\service;

use bxkj_module\service\ExpLevel;
use bxkj_module\service\Service;
use think\Db;

class RecommendContentFilm extends Service
{
    //获取总记录数
    public function getTotal($get)
    {
        $this->db = Db::name('recommend_content');
        $this->setWhere($get)->setJoin();
        return $this->db->count();
    }

    //获取列表
    public function getList($get, $offset, $length)
    {
        $this->db = Db::name('recommend_content');
        $this->setWhere($get)->setOrder($get)->setJoin();
        $result = $this->db->limit($offset, $length)->select();
        $admins = $this->getRelList($result, function ($aids) {
            $adminService = new Admin();
            $admins = $adminService->getAdminsByIds($aids);
            return $admins;
        }, 'aid');
        $musics = $this->getRelList($result, function ($musicsIds) {
            $musicsService = new Music();
            $musics = $musicsService->getmusicsByIds($musicsIds);
            return $musics;
        }, 'music_id');
        foreach ($result as &$item) {
            if (!empty($item['aid'])) {
                $item['audit_admin'] = self::getItemByList($item['aid'], $admins, 'id');
            }
            if (!empty($item['music_id'])) {
                $item['music'] = self::getItemByList($item['music_id'], $musics, 'id');
            }
            $item['duration_str'] = duration_format($item['duration']);
            $item['file_size_str'] = format_bytes($item['file_size']);
            $item['rec_num'] = Db::name('recommend_content')->where(['rel_type' => 'film', 'rel_id' => $item['id']])->count();
            $item['user'] = [
                'user_id' => $item['user_id'],
                'nickname' => $item['nickname'],
                'avatar' => $item['avatar'],
                'phone' => $item['phone'],
                'level' => $item['level'],
                'remark_name' => $item['remark_name']
            ];
            $item['user'] = array_merge($item['user'], ExpLevel::getLevelInfo($item['user'] ['level']));
            $item['status_abnormal'] = '0';
            if (!empty($item['online_id']) && $item['status'] == '0') {
                $item['status_abnormal'] = '1';
            }
            if (empty($item['online_id']) && $item['status'] == '1') {
                $item['status_abnormal'] = '1';
            }
        }
        return $result;
    }

    private function setJoin()
    {
        $this->db->alias('rc')->join('__VIDEO_UNPUBLISHED__ vu', 'rc.rel_id=vu.id', 'LEFT');
        $this->db->join('__RECOMMEND_SPACE__ rs', 'rc.rec_id=rs.id', 'LEFT');
        $this->db->join('__USER__ user', 'user.user_id=vu.user_id', 'LEFT');
        $this->db->join('__VIDEO__ v', 'v.id=vu.id', 'LEFT');
        $this->db->field('rc.id rc_id,rc.sort');
        $this->db->field('rs.name rs_name');
        $this->db->field('user.user_id,user.nickname,user.avatar,user.phone,user.level,user.remark_name,user.is_creation');
        $this->db->field('vu.id,vu.describe,vu.user_id,vu.video_id,vu.video_url,vu.animate_url,vu.cover_url,vu.topic,vu.friends,vu.tags,vu.tag_names,vu.location_lng,vu.location_lat,vu.city_id,vu.city_name,vu.audit_status,vu.audit_time,vu.aid,vu.reason,vu.source,vu.duration,vu.width,vu.height,vu.file_size,vu.copy_right,
        vu.rating,vu.status,v.score,vu.location_name,vu.location_id,vu.music_id,vu.visible,vu.process_status,vu.create_time');
        $this->db->field('v.zan_sum,v.comment_sum,v.collection_sum,v.play_sum,v.share_sum,v.watch_sum,v.watch_duration,v.id online_id');
        return $this;
    }

    //设置查询条件
    private function setWhere($get)
    {
        $where = [];
        if ($get['rec_id'])
        {
            $where['rc.rec_id'] = $get['rec_id'];
        }
        if ($get['rel_type'])
        {
            $where['rc.rel_type'] = $get['rel_type'];
        }
        $this->db->where($where);
        $this->db->setKeywords(trim($get['keyword']), '', 'number vu.id', 'vu.describe,number vu.id');
        $this->db->setKeywords(trim($get['user_keyword']), 'phone user.phone', 'number user.user_id', 'user.nickname,number user.user_id');
        return $this;
    }

    //设置排序规则
    private function setOrder($get)
    {
        $order = array();
        $order['rc.sort'] = 'desc';
        $order['rc.create_time'] = 'desc';
        $this->db->order($order);
        return $this;
    }

}
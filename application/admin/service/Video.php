<?php

namespace app\admin\service;

use bugu_film\task\VideoManage;
use bxkj_common\RabbitMqChannel;
use bxkj_module\service\ExpLevel;
use bxkj_module\service\Service;
use think\Db;

class Video extends Service
{
    //获取总记录数
    public function getTotal($get)
    {
        $this->db = Db::name('video_unpublished');
        $this->setWhere($get)->setJoin();
        return $this->db->count();
    }

    //获取列表
    public function getList($get, $offset, $length)
    {
        $this->db = Db::name('video_unpublished');
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
            $item['zan_sum']=$item['zan_sum']+$item['zan_sum2'];
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
            $item['goods'] = '';
            if (!empty($item['goods_id'])) {
                $anchorGoods = Db::name('anchor_goods')->field('id,goods_id,goods_title')->where(['id' => $item['goods_id']])->find();
                if (!empty($anchorGoods) && empty($item['goods_type'])) {
                    $goods = Db::name('goods')->field('img,title,short_title,shop_type')->where(['id' => $anchorGoods['goods_id']])->find();
                    $goods['short_title'] = $anchorGoods['goods_title'] ? $anchorGoods['goods_title'] : ($goods['short_title'] ? $goods['short_title'] : $goods['title']);
                    $item['goods'] = $goods;
                } elseif (!empty($anchorGoods) && ($item['goods_type'] == 1)) {
                    $goods = Db::name('shop_goods_sku')->field(' goods_name as title,sku_image as img, goods_state as status, category_id as cate_id')->where(['sku_id' => $anchorGoods['goods_id'], 'goods_state' => 1])->find();
                    $goods['short_title'] = $anchorGoods['goods_title'] ? $anchorGoods['goods_title'] : $goods['title'];
                    $goods['shop_type'] = 'Z';
                    $item['goods'] = $goods;
                }
            }
        }
        return $result;
    }

    private function setJoin()
    {
        $this->db->alias('vu')->join('__USER__ user', 'user.user_id=vu.user_id', 'LEFT');
        $this->db->join('__VIDEO__ v', 'v.id=vu.id', 'LEFT');
        $this->db->field('user.user_id,user.nickname,user.avatar,user.phone,user.level,user.remark_name,user.is_creation');
        $this->db->field('vu.id,vu.describe,vu.user_id,vu.video_id,vu.video_url,vu.animate_url,vu.cover_url,vu.topic,vu.friends,vu.tags,vu.tag_names,vu.location_lng,vu.location_lat,vu.city_id,vu.city_name,vu.audit_status,vu.audit_time,vu.aid,vu.reason,vu.source,vu.duration,vu.width,vu.height,vu.file_size,vu.copy_right,
        vu.rating,vu.status,v.score,vu.location_name,vu.location_id,vu.music_id,vu.visible,vu.process_status,vu.create_time,vu.is_ad,vu.ad_url,vu.goods_id,vu.short_title,vu.goods_type,vu.cate_id');
        $this->db->field('v.zan_sum,v.comment_sum,v.collection_sum,v.play_sum,v.share_sum,v.watch_sum,v.watch_duration,v.id online_id,v.zan_sum2,sco_zan_sum,v.played_out_sum,v.switch_sum,v.general_sum,v.sco_comment_sum');
        return $this;
    }

    //设置查询条件
    private function setWhere($get)
    {
        $where = [];
        if ($get['aid'] != '') {
            $where[] = ['vu.aid', '=', $get['aid']];
        }
        if ($get['user_id'] != '') {
            $where[] = ['vu.user_id', '=', $get['user_id']];
        }
        if ($get['audit_status'] != '') {
            $where[] = ['vu.audit_status', '=', $get['audit_status']];
        }
        if ($get['status'] != '') {
            $where[] = ['vu.status', '=', $get['status']];
        }
        if ($get['source'] != '') {
            $where[] = ['vu.source', '=', $get['source']];
        }
        if ($get['visible'] != '') {
            $where[] = ['vu.visible', '=', $get['visible']];
        }
        if ($get['is_ad'] == -1) {
            $where[] = ['vu.is_ad', '>', 0];
        }
        if (!empty($get['is_ad']) && $get['is_ad'] != -1) {
            $where[] = ['vu.is_ad', '=', $get['is_ad']];
        }
        if (empty($get['is_ad'])) {
            $where[] = ['vu.is_ad', '=', 0];
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
        if ($get['sort'] == 'play') {
            $order['v.play_sum'] = empty($get['sort_by']) ? 'desc' : $get['sort_by'];
            $order['vu.create_time'] = 'desc';
        } else if ($get['sort'] == 'zan') {
            $order['v.zan_sum'] = empty($get['sort_by']) ? 'desc' : $get['sort_by'];
            $order['vu.create_time'] = 'desc';
        } else if ($get['sort'] == 'comment') {
            $order['v.comment_sum'] = empty($get['sort_by']) ? 'desc' : $get['sort_by'];
            $order['vu.create_time'] = 'desc';
        } else if ($get['sort'] == 'score') {
            $order['v.score'] = empty($get['sort_by']) ? 'desc' : $get['sort_by'];
            $order['vu.create_time'] = 'desc';
        } else {
            $order['vu.create_time'] = 'desc';
            $order['vu.id'] = 'desc';
        }
        $this->db->order($order);
        return $this;
    }

    public function delete($ids)
    {
        $ids = is_array($ids) ? $ids : explode(',', $ids);
        if (empty($ids)) return $this->setError('请选择视频');
        $num = Db::name('video_unpublished')->whereIn('id', $ids)->update(['delete_time' => time()]);
        if (!$num) return $this->setError('删除视频失败');
        $rabbitChannel = new RabbitMqChannel(['video.delete']);
        foreach ($ids as $id) {
            $rabbitChannel->exchange('main')->send('video.delete', ['id' => $id]);
        }
        $rabbitChannel->close();
        return $num;
    }

    public function changeStatus($ids, $status)
    {
        if (empty($ids)) return $this->setError('请选择视频');
        if (!in_array($status, ['0', '1'])) return $this->setError('状态值不正确');
        $rabbitChannel = new RabbitMqChannel(['video.update', 'video.create_publish', 'user.credit']);
        $num = 0;
        if ($status == '0') {
            foreach ($ids as $id) {
                $rabbitChannel->exchange('main')->send('video.update.offline', ['type' => 'offline', 'data' => ['id' => $id]]);
                $video = Db::name('video_unpublished')->where(['id' => $id])->find();
                $rabbitChannel->exchange('main')->send('user.credit.lower_shelf_video', ['user_id' => $video['user_id'], 'video_id' => $id, 'aid' => AID]);
                $num++;
            }
        } else {
            foreach ($ids as $id) {
                $video = Db::name('video_unpublished')->where(['id' => $id])->find();
                if($video['default_audit_status']==2)$video['audit_status'] =2;
                if (empty($video)) return $this->setError("{$id}视频不存在");
                if ($video['visible'] == '2') return $this->setError("{$id}私密视频");
                if ($video['audit_status'] != '2') return $this->setError("{$id}未通过审核");
                // if ($video['process_status'] <= 2) return $this->setError("{$id}正在处理中");
                $aa = $rabbitChannel->exchange('main')->send('video.create.publish', ['id' => $id, 'new_upload' => '0']);
                $num++;
            }
        }
        $rabbitChannel->close();
        return $num;
    }

    public function getVideosByIds($videoIds)
    {
        if (empty($videoIds)) return [];
        $result = Db::name('video')->whereIn('id', $videoIds)->field('id,animate_url,cover_url')->limit(count($videoIds))->select();
        return $result ? $result : [];
    }

    public function find($where){
     return   Db::name('video_unpublished')->where($where)->find();
    }


}
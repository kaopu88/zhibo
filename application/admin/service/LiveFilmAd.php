<?php

namespace app\admin\service;

use bxkj_common\HttpClient;
use bxkj_common\RedisClient;
use bxkj_module\service\Service;
use Qiniu\Auth;
use think\Db;

class LiveFilmAd extends Service
{

    //获取总记录数
    public function getTotal($get)
    {
        $this->db = Db::name('live_film_ad');
        $this->setWhere($get);
        return $this->db->count();
    }

    //获取列表
    public function getList($get, $offset, $length)
    {
        $this->db = Db::name('live_film_ad');
        $this->setWhere($get)->setOrder($get);
        $result = $this->db->limit($offset, $length)->select();
        $admins = $this->getRelList($result, function ($aids) {
            $adminService = new Admin();
            $admins = $adminService->getAdminsByIds($aids);
            return $admins;
        }, 'aid');
        foreach ($result as &$item) {
            if (!empty($item['aid'])) {
                $item['admin'] = self::getItemByList($item['aid'], $admins, 'id');
            }
            $item['video_duration_str'] = duration_format($item['video_duration']);
        }
        return $result;
    }

    //设置查询条件
    private function setWhere($get)
    {
        $where = array();
        if ($get['status'] != '') {
            $where[] = ['status', '=', $get['status']];
        }
        if (trim($get['video_id']) != '') {
            $where[] = ['video_id', '=', trim($get['video_id'])];
        }
        $this->db->setKeywords(trim($get['keyword']), '', 'number id', 'ad_title');
        $this->db->where($where);
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

    public function add($inputData)
    {
        $this->preInputData($inputData);
        $data = $this->df->process('add@live_film_ad', $inputData)->output();
        if ($data === false) return $this->setError($this->df->getError());
        if (empty($inputData['video_url'])) {
            return $this->setError('请上传视频');
        }
        $data['aid'] = AID;
        $id = Db::name('live_film_ad')->insertGetId($data);
        if (!$id) return $this->setError('新增失败');
        return $id;
    }

    public function preInputData(&$inputData)
    {
        $video_duration = 0;
        $inputData['video_duration'] = $video_duration;
        $inputData['video_rate'] = '';
        if (!empty($inputData['video_duration_h'])) {
            $video_duration += ((int)$inputData['video_duration_h'] * 3600);
        }
        if (!empty($inputData['video_duration_i'])) {
            $video_duration += ((int)$inputData['video_duration_i'] * 60);
        }
        if (!empty($inputData['video_duration_s'])) {
            $video_duration += ((int)$inputData['video_duration_s'] * 1);
        }
        if ($video_duration > 0) {
            $inputData['video_duration'] = $video_duration;
        }
        if (!empty($inputData['video_width']) && !empty($inputData['video_height'])) {
            $inputData['video_rate'] = (string)round($inputData['video_width'] / $inputData['video_height'], 2);
        }
        unset($inputData['video_duration_s'], $inputData['video_duration_i'], $inputData['video_duration_h']);
        unset($inputData['video_width'], $inputData['video_height']);
    }

    public function getSuggests($keyword, $length = 10)
    {
        $this->db = Db::name('live_film_ad');
        $this->db->setKeywords($keyword, '', 'number id', 'number id,ad_title');
        $this->db->field('id,ad_title');
        $this->db->order(['create_time' => 'desc']);
        $result = $this->db->limit(0, $length)->select();
        $arr = [];
        foreach ($result as $item) {
            $arr[] = [
                'value' => $item['id'],
                'name' => $item['ad_title']
            ];
        }
        return $arr;
    }

    public function getInfo($filmId)
    {
        $info = Db::name('live_film')->where(['id' => $filmId])->find();
        if ($info) {
            $info['video_duration_str'] = duration_format($info['video_duration']);
        }
        return $info;
    }


}
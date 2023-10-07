<?php
namespace app\admin\service;

use bxkj_module\service\Service;
use think\Db;

class VideoRewardRank extends Service
{
    public function getTotal($get)
    {
        $this->db = Db::name('video_reward_rank');
        $this->setWhere($get)->setOrder();
        return $this->db->count();
    }

    public function setWhere($get)
    {
        $where = [];

        if (trim($get['gift_id']) != '') {
            $where[] = ['gift_id', '=', trim($get['gift_id'])];
        }

        if (trim($get['user_id']) != '') {
            $where[] = ['user_id', '=', trim($get['user_id'])];
        }

        if (trim($get['to_uid']) != '') {
            $where[] = ['to_uid', '=', trim($get['to_uid'])];
        }

        if (trim($get['video_id']) != '') {
            $where[] = ['video_id', '=', trim($get['video_id'])];
        }

        $this->db->where($where);
        return $this;
    }

    public function setOrder()
    {
        $order = [];
        $order['create_time'] = 'desc';
        $order['id'] = 'desc';
        $this->db->order($order);
        return $this;
    }

    public function getList($get, $offset, $lenth)
    {
        $this->db = Db::name('video_reward_rank');
        $this->setWhere($get)->setOrder();
        $result = $this->db->limit($offset, $lenth)->select();
        if (!$result) return [];
        $this->parseList($result);
        return $result;
    }

    public function parseList(&$result){
        $relKey = 'user_id';
        $outKey = 'user';
        $recAccounts = $this->getRelList($result, [new User(), 'getUsersByIds'], 'user_id');
        $recAccounts_b = $this->getRelList($result, [new User(), 'getUsersByIds'], 'to_uid');
        list($giftIds, $videoIds) = self::getIdsByList($result, 'gift_id|video_id', true);
        $giftService = new Gift();
        $giftList = $giftService->getGiftsByIds($giftIds);
        $videoService = new Video();
        $videoList = $videoService->getVideosByIds($videoIds);
        foreach ($result as &$item) {
            if (!empty($item['to_uid'])) {
                $item['to_user'] = self::getItemByList($item['to_uid'], $recAccounts_b, $relKey);
            }
            if (!empty($item['user_id'])) {
                $item[$outKey] = self::getItemByList($item['user_id'], $recAccounts, $relKey);
            }
            $item['gift_info'] = self::getItemByList($item['gift_id'], $giftList, 'id');
            $item['video_info'] = self::getItemByList($item['video_id'], $videoList, 'id');
        }
    }
}
<?php
namespace app\admin\service;

use bxkj_module\service\Service;
use think\Db;

class LivePayOrder extends Service
{
    protected static $room_model = ['直播','录像','电影','游戏'];
    //获取总记录数
    public function getTotal($get)
    {
        $this->db = Db::name('live_pay_order');
        $this->setWhere($get);
        return $this->db->count();
    }

    //获取列表
    public function getList($get, $offset, $length)
    {
        $this->db = Db::name('live_pay_order');
        $this->setWhere($get)->setOrder($get);
        $result = $this->db->limit($offset, $length)->select();
        if (!$result) return [];
        $this->parseList($get,$result);
        return $result;
    }

    public function parseList($get,&$result){
        $relKey = 'user_id';
        $outKey = 'user';
        $recAccounts = $this->getRelList($result, [new User(), 'getUsersByIds'], 'user_id');
        $recAccounts_b = $this->getRelList($result, [new User(), 'getUsersByIds'], 'anchor_id');
        foreach ($result as &$item) {
            if (!empty($item['anchor_id'])) {
                $item['to_user'] = self::getItemByList($item['anchor_id'], $recAccounts_b, $relKey);
            }
            if (!empty($item['user_id'])) {
                $item[$outKey] = self::getItemByList($item['user_id'], $recAccounts, $relKey);
            }
            $item['room_model_txt'] = self::$room_model[$item['room_model']];
        }
    }

    //设置查询条件
    private function setWhere($get)
    {
        $where = array();

        if (trim($get['user_id']) != '')
        {
            $where['user_id'] = trim($get['user_id']);
        }
        if (trim($get['anchor_id']) != '')
        {
            $where['anchor_id'] = trim($get['anchor_id']);
        }
        if ($get['room_model'] != '')
        {
            $where['room_model'] = $get['room_model'];
        }

        $this->db->setKeywords(trim($get['keyword']),'','number room_id','trade_no,room_title,number room_id');
        $this->db->where($where);
        return $this;
    }

    protected function setOrder($get)
    {
        $order = array();
        if (empty($get['sort'])) {
            $order['create_time'] = 'DESC';
            $order['id'] = 'DESC';
        }
        $this->db->order($order);
        return $this;
    }
}



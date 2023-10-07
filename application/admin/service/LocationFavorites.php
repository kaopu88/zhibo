<?php

namespace app\admin\service;

use bxkj_module\service\Service;
use think\Db;

class LocationFavorites extends Service
{
    public function getTotal($get)
    {
        $this->db = Db::name('location_favorites');
        $this->setJoin()->setWhere($get);
        $total = $this->db->count();
        return $total;
    }

    public function setJoin()
    {
        $this->db->alias('lf')->join('__USER__ user', 'user.user_id=lf.user_id');
        $this->db->join('__LOCATION__ l','lf.location_id=l.id');
        $this->db->field('lf.id,lf.user_id,lf.create_time,l.name,l.cover,l.street_address');
        return $this;
    }

    public function setWhere($get)
    {
        $where = [];
        $this->db->setKeywords(trim($get['keyword']),'','number l.id','l.name,number l.id');
        $this->db->setKeywords(trim($get['user_keyword']), 'phone user.phone', 'number user.user_id', 'user.nickname,number user.user_id');
        $this->db->where($where);
        return $this;
    }

    public function setOrder($get)
    {
        $order = [];
        $order['lf.create_time'] = 'desc';
        $order['lf.id'] = 'desc';
        $this->db->order($order);
        return $this;
    }

    public function getList($get, $offset, $lenth)
    {
        $this->db = Db::name('location_favorites');
        $this->setJoin()->setWhere($get)->setOrder($get);
        $result = $this->db->limit($offset, $lenth)->select();
        $result = $result ? $result : [];
        $this->parseList($result);
        return $result;
    }

    public function parseList(&$result)
    {
        $relkey = 'user_id';
        $outKey = 'user';
        $relAccount = self::getRelList($result,[new User(), 'getUsersByIds'],'user_id');
        if ($result){
            foreach ($result as &$item)
            {
                if ($item['user_id'])
                {
                    $item[$outKey] = self::getItemByList($item['user_id'], $relAccount, $relkey);
                }
            }
        }
        return $result;
    }
}

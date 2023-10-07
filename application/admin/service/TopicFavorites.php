<?php
namespace app\admin\service;

use bxkj_module\service\Service;
use think\Db;

class TopicFavorites extends Service
{
    public function getTotal($get)
    {
        $this->db = Db::name('topic_favorites');
        $this->setWhere($get)->setJoin();
        return $this->db->count();
    }

    public function setJoin()
    {
        $this->db->alias('tf')->join('__USER__ user','tf.user_id=user.user_id');
        $this->db->join('__TOPIC__ t', 'tf.topic_id=t.id' );
        $this->db->field('tf.id,tf.user_id,tf.create_time,t.icon,t.title,t.descr');
        return $this;
    }

    public function setWhere($get)
    {
        $where = [];
        $this->db->setKeywords(trim($get['keyword']),'','number t.id','t.title,t.descr,number t.id');
        $this->db->setKeywords(trim($get['user_keyword']),'','number user.user_id','user.nickname,user.phone,number user.user_id');
        $this->db->where($where);
        return $this;
    }

    public function setOrder($get)
    {
        $order = [];
        $order['tf.create_time'] = 'desc';
        $order['tf.id'] = 'desc';
        $this->db->order($order);
        return $this;
    }

    public function getList($get, $offset, $lenth)
    {
        $this->db = Db::name('topic_favorites');
        $this->setWhere($get)->setJoin()->setOrder($get);
        $result = $this->db->limit($offset, $lenth)->select();
        $result = $result ? $result : [];
        $this->parseList($result);
        return $result;
    }

    public function parseList(&$result)
    {
        $relkey = 'user_id';
        $outkey = 'user';
        $relAccounts = $this->getRelList($result, [new User(), 'getUsersByIds'], 'user_id');
        foreach ($result as &$item){
            if ($item['user_id'])
            {
                $item[$outkey] = self::getItemByList($item['user_id'], $relAccounts, $relkey);
            }
        }
        unset($item);
        return $result;
    }
}

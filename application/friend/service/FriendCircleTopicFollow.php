<?php
/**
 * Created by PhpStorm.
 * User: “崔鹏
 * Date: 2020/07/03
 * Time: 下午 11:27
 */

namespace app\friend\service;

use bxkj_common\RedisClient;
use bxkj_module\service\Service;
use think\Db;

class FriendCircleTopicFollow extends Service
{
    public function follow($data1)
    {
        $redis            = new RedisClient();
        $data['ctime']    = time();
        $data['topic_id'] = $data1['topic_id'];
        $data['is_follow'] = 1;
        $data =[
            'ctime'=> time(),
            'topic_id'=> $data1['topic_id'],
            'is_follow'=> 1,
            'uid' =>$data1['uid'],
        ];

        $find              = Db::name('friend_circle_topic_follow')->where(['uid' => $data['uid'], 'topic_id' => $data['topic_id']])->find();
        Db::startTrans();
        if ($find) {
            $updata = [
                'is_follow' => abs($find['is_follow'] - 1),
                'ctime'     => time(),
            ];
            $rest   = Db::name('friend_circle_topic_follow')->where(['uid' => $data['uid'], 'topic_id' => $data['topic_id']])->update($updata);
            $redis->setex('usertopic_follow:' . $data['uid'], 30, $updata['is_follow']);
        } else {
            $rest = Db::name('friend_circle_topic_follow')->insertGetId($data);
            $redis->setex('usertopic_follow:' . $data['uid'], 30, 1);
        }
        if ($find['is_follow'] == 0) {
            $upNum = Db::name('friend_circle_topic')->where('topic_id', $data['topic_id'])->setInc('hot');
        } else {
            $upNum = Db::name('friend_circle_topic')->where('topic_id', $data['topic_id'])->setDec('hot');
        }
        if ($rest && $upNum) {
            Db::commit();
        } else {
            Db::rollback();
            return 0;
        }
        return $rest;
    }

    public function countTotal($where)
    {
        $this->db = Db::name('friend_circle_topic_follow');
        $count    = $this->db->where($where)->count();
        return (int)$count;
    }

    public function find($where, $order)
    {
        $this->db = Db::name('friend_circle_topic_follow');
        $info     = $this->db->where($where)->order($order)->find();
        return $info;
    }

    public function pageQuery($page_index, $page_size, $condition, $order, $field)
    {
        $this->db = Db::name('friend_circle_topic_follow');
        $count    = $this->db->where($condition)->count();
        if ($page_size == 0) {
            $list       = $this->db->field($field)
                ->where($condition)
                ->order($order)
                ->select();
            $page_count = 1;
        } else {
            $start_row = $page_size * ($page_index - 1);
            $list      = $this->db->field($field)
                ->where($condition)
                ->order($order)
                ->limit($start_row . "," . $page_size)
                ->select();
            if ($count % $page_size == 0) {
                $page_count = $count / $page_size;
            } else {
                $page_count = (int)($count / $page_size) + 1;
            }
        }
        return array(
            'data'        => $list,
            'total_count' => $count,
            'page_count'  => $page_count
        );
    }

    public function getTotal($get)
    {
        $this->db = Db::name('friend_circle_topic_follow');
        $this->setWhere($get);
        $count = $this->db->count();
        return (int)$count;
    }

    public function getList($get, $offset = 0, $length = 20)
    {
        $this->db = Db::name('friend_circle_topic_follow');
        $this->setWhere($get)->setOrder($get);
        $result = $this->db->limit($offset, $length)->select();
        return $result;
    }

    protected function setWhere($get)
    {
        $where  = array();
        $where1 = array();
        if ($get['uid'] != 0) {
            $where['uid'] = $get['uid'];
        }
        if ($get['fcmid'] != '') {
            $where['fcmid'] = $get['fcmid'];
        }
        if ($get['keyword'] != '') {
            $where1[] = ['content', 'like', '%' . $get['keyword'] . '%'];
        }
        $this->db->where($where)->where($where1);
        return $this;
    }

    protected function setOrder($get)
    {
        $order = array();
        if (empty($get['sort'])) {
            $order['create_time'] = 'DESC';
        } else {
            $order = $get;
        }
        $this->db->order($order);
        return $this;
    }

    public function getQuery($condition, $field, $order)
    {
        $this->db = Db::name('friend_circle_topic_follow');
        $list     = $this->db->field($field)->where($condition)->order($order)->select();
        return $list;
    }

    public function memberManger($topic_id, $power)
    {
        $this->db = Db::name('friend_circle_topic_follow');
        return $this->db->where(['topic_id' => $topic_id, 'power' => $power])->order('id')->select();
    }

    public function checkPower($uid, $topic_id)
    {
        $this->db = Db::name('friend_circle_topic_follow');
        return $this->db->where(['uid' => $uid, 'topic_id' => $topic_id])->column('power');
    }

    public function setPower($uid, $topic_id, $power)
    {
        return Db::name('friend_circle_topic_follow')->where(['uid' => $uid, 'topic_id' => $topic_id])->update($power);
    }
}
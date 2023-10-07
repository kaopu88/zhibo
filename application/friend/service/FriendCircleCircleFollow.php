<?php
/**
 * Created by PhpStorm.
 * User: “崔鹏
 * Date: 2020/06/24
 * Time: 下午 13:45
 */

namespace app\friend\service;

use bxkj_common\RedisClient;
use bxkj_module\service\Service;
use think\Db;

class FriendCircleCircleFollow extends Service
{
    public function follow($data1)
    {
        $redis             = new RedisClient();
        $data = [
            'ctime' => time(),
            'circle_id'=>$data1['circle_id'],
            'uid'=>$data1['uid'],
        ];
        $data['is_follow'] = 1;
        $find              = Db::name('friend_circle_circle_follow')->where(['uid' => $data['uid'], 'circle_id' => $data['circle_id']])->find();
        Db::startTrans();
        if ($find) {
            $updata = [
                'is_follow' => abs($find['is_follow'] - 1),
                'ctime'     => time(),
            ];
            $rest   = Db::name('friend_circle_circle_follow')->where(['uid' => $data['uid'], 'circle_id' => $data['circle_id']])->update($updata);
            $redis->setex('usercircle_follow:' . $data['uid'], 30, $updata['is_follow']);
        } else {
            $rest = Db::name('friend_circle_circle_follow')->insertGetId($data);
            $redis->setex('usercircle_follow:' . $data['uid'], 30, 1);
        }
        if ($find['is_follow'] == 0) {
            $upNum = Db::name('friend_circle_circle')->where('circle_id', $data['circle_id'])->setInc('follow');
        } else {
            $upNum = Db::name('friend_circle_circle')->where('circle_id', $data['circle_id'])->setDec('follow');
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
        $this->db = Db::name('friend_circle_circle_follow');
        $count    = $this->db->where($where)->count();
        return (int)$count;
    }

    public function find($where, $order)
    {
        $this->db = Db::name('friend_circle_circle_follow');
        $info     = $this->db->where($where)->order($order)->find();
        return $info;
    }

    public function pageQuery($page_index, $page_size, $condition, $order, $field)
    {
        $this->db = Db::name('friend_circle_circle_follow');
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
        $this->db = Db::name('friend_circle_circle_follow');
        $this->setWhere($get);
        $count = $this->db->count();
        return (int)$count;
    }

    public function getList($get, $offset = 0, $length = 20)
    {
        $this->db = Db::name('friend_circle_circle_follow');
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
        $this->db = Db::name('friend_circle_circle_follow');
        $list     = $this->db->field($field)->where($condition)->order($order)->select();
        return $list;
    }

    public function memberManger($circle_id, $power)
    {
        $this->db = Db::name('friend_circle_circle_follow');
        return $this->db->where(['circle_id' => $circle_id])->where('power >='.$power)->order('id')->select();
    }

    public function checkPower($uid, $circle_id)
    {
        $this->db = Db::name('friend_circle_circle_follow');
        return $this->db->where(['uid' => $uid, 'circle_id' => $circle_id])->column('power');
    }

    public function setPower($uid, $circle_id, $power)
    {
        return Db::name('friend_circle_circle_follow')->where(['uid' => $uid, 'circle_id' => $circle_id])->update($power);
    }
}
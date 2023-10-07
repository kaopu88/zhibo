<?php
/**
 * Created by PhpStorm.
 * User: “崔鹏
 * Date: 2020/06/24
 * Time: 下午 1:51
 */

namespace app\friend\service;

use app\api\service\Follow as FollowModel;
use bxkj_common\RedisClient;
use bxkj_module\service\Service;
use think\Db;

class FriendCircleMessageFilter extends Service
{
    public function add($data1)
    {
        $data          = [
            'uid'           => $data1['uid'],
            'filter_id'     => $data1['filter_id'],
            'msgTpye'       => $data1['msgTpye'],
            'filter_type'   => $data1['filter_type'],
            'filter_msg_id' => $data1['filter_msg_id'] ? $data1['filter_msg_id'] : 0,
        ];
        $data['ctime'] = time();
        $id            = Db::name('friend_circle_message_filter')->insertGetId($data);
        return $id;
    }

    public function check($data)
    {
        $findall = Db::name('friend_circle_message_filter')
            ->where(['filter_type' => 1, 'uid' => USERID, 'filter_id' => $data['filter_id'], 'msgTpye' => $data['msgTpye']])
            ->count();
        $find = Db::name('friend_circle_message_filter')
            ->where(['filter_type' => 2, 'uid' => USERID, 'filter_id' => $data['filter_id'], 'filter_msg_id' => $data['filter_msg_id']])
            ->count();
        if (empty($findall) && empty($find)) {
            return 0;
        } else {
            return 1;
        }
    }

    public function filterUserArray($uid, $msgTpye = 2)
    {
        $rest = Db::name('friend_circle_message_filter')->where(['uid' => $uid, 'filter_type' => 1, 'msgTpye' => $msgTpye])->column('filter_id');
        foreach ($rest as $k => $v) {
            $msgidArray = Db::name('friend_circle_message')->where(['uid' => $v])->column('id');
            if (!empty($msgidArray)) {
                foreach ($msgidArray as $k => $v) {
                    $filter[] = $v;
                }
            }
        }
        $restbyid = Db::name('friend_circle_message_filter')->where(['uid' => $uid, 'filter_type' => 2])->column('filter_msg_id');
        if (!empty($restbyid)) {
            foreach ($restbyid as $k1 => $v1) {
                $filter[] = $v1;
            }
        }
        if (!empty($filter)) {
            return array_unique($filter);
        } else {
            return [];
        }
    }

    public function getTotal($get)
    {
        $this->db = Db::name('friend_circle_message_filter');
        $this->setWhere($get);
        $count = $this->db->count();
        return (int)$count;
    }

    public function getList($get, $offset = 0, $length = 20)
    {
        $this->db = Db::name('friend_circle_message_filter');
        $this->setWhere($get)->setOrder($get);
        $result = $this->db->limit($offset, $length)->select();
        return $result;
    }

    protected function setWhere($get)
    {
        $where  = array();
        $where1 = array();
        if ($get['type'] != 0) {
            $where['type'] = $get['type'];
        }
        if ($get['msg_type'] != '') {
            $where['msg_type'] = $get['msg_type'];
        }
        if ($get['keyword'] != '') {
            $where1[] = ['title|content', 'like', '%' . $get['keyword'] . '%'];
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

    public function changeStatus($ids, $status)
    {
        if (!in_array($status, [0, 1])) return false;
        Db::name('friend_circle_message_filter')->whereIn('id', $ids)->update(['status' => $status]);
        $redis = RedisClient::getInstance();
        $rest  = Db::name('friend_circle_message_filter')->find(['id' => $ids[0]]);
        $redis->set("bx_friend_msg:" . $ids[0], json_encode($rest));
        return Db::name('friend_circle_timeline')->whereIn('fcmid', $ids)->update(['status' => $status]);
    }

    public function getQuery($condition, $field, $order)
    {
        $this->db = Db::name('friend_circle_message_filter');
        $list     = $this->db->field($field)->where($condition)->order($order)->select();
        return $list;
    }

    public function del($ids)
    {
    }
}
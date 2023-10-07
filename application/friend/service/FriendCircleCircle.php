<?php
/**
 * Created by PhpStorm.
 * User: “崔鹏
 * Date: 2020/06/24
 * Time: 上午 10:18
 */

namespace app\friend\service;

use bxkj_module\service\Service;
use think\Db;

class FriendCircleCircle extends Service
{
    public function add($data1)
    {
        $data = [
            'uid'=>$data1['uid'],
            'circle_name'=>emoji_encode($data1['circle_name']),
            'circle_describe'=>emoji_encode($data1['circle_describe']),
            'circle_cover_img'=>$data1['circle_cover_img'],
            'circle_background_img'=>$data1['circle_background_img'],
        ];

        $data['ctime'] = time();
        $countTotal    = $this->countTotal(['circle_name' => $data['circle_name']]);
        if ($countTotal > 0) {
            return -1;
        }
        $id = Db::name('friend_circle_circle')->insertGetId($data);
        Db::name('friend_circle_circle_follow')->insertGetId(['uid' => $data['uid'], 'circle_id' => $id, 'ctime' => time(), 'is_follow' => 1, 'power' => 1]);
        return $id;
    }

    public function checkDaySend($uid, $num)
    {
        $start_time = strtotime(date("Y-m-d"), time());
        $end_time   = time();
        $where[]    = ['ctime', 'between', [$start_time, $end_time]];
        $where[]    = ["uid", 'eq', $uid];
        $countTotal = $this->countTotal($where);
        if ($countTotal >= $num) {
            return -1;
        }
        return 1;
    }

    public function countTotal($where)
    {
        $this->db = Db::name('friend_circle_circle');
        $count    = $this->db->where($where)->count();
        return (int)$count;
    }

    public function find($where, $order='')
    {
        $this->db = Db::name('friend_circle_circle');
        $info     = $this->db->where($where)->order($order)->find();
        return $info;
    }

    public function pageQuery($page_index, $page_size, $condition, $order, $field)
    {
        $this->db = Db::name('friend_circle_circle');
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
        foreach ($list as $k => $v) {
            $list[$k]['circle_name'] = emoji_decode($list[$k]['circle_name']);
            $list[$k]['circle_describe'] = emoji_decode($list[$k]['circle_describe']);
        }
        return array(
            'data'        => $list,
            'total_count' => $count,
            'page_count'  => $page_count
        );
    }

    public function getTotal($get)
    {
        $this->db = Db::name('friend_circle_circle');
        $this->setWhere($get);
        $count = $this->db->count();
        return (int)$count;
    }

    public function getList($get, $offset = 0, $length = 20)
    {
        $this->db = Db::name('friend_circle_circle');
        $this->setWhere($get)->setOrder($get);
        $result = $this->db->limit($offset, $length)->select();
        foreach ($result as $k => $v) {
            $result[$k]['user'] = userMsg($v['uid'], 'user_id,nickname,avatar,phone,level,remark_name');
            $result[$k]['circle_name'] = emoji_decode($result[$k]['circle_name']);
            $result[$k]['circle_describe'] = emoji_decode($result[$k]['circle_describe']);
        }
        return $result;
    }

    protected function setWhere($get)
    {
        $where  = array();
        $where1 = array();
        if ($get['uid'] != 0) {
            $where['uid'] = $get['uid'];
        }
        if ($get['status'] != '') {
            $where['status'] = $get['status'];
        }
        if ($get['keyword'] != '') {
            $where1[] = ['circle_name', 'like', '%' . $get['keyword'] . '%'];
        }
        $this->db->where($where)->where($where1);
        return $this;
    }

    protected function setOrder($get)
    {
        $order = array();
        if (empty($get['sort'])) {
            $order['ctime'] = 'DESC';
        } else {
            $order = $get;
        }
        $this->db->order($order);
        return $this;
    }

    public function del($ids)
    {
        $status = 0;
        return Db::name('friend_circle_circle')->whereIn('circle_id', $ids)->update(['status' => $status]);

    }

    public function changeRecom($ids, $status)
    {
        if (!in_array($status, [0, 1])) return false;
        return Db::name('friend_circle_circle')->whereIn('circle_id', $ids)->update(['is_recom' => $status]);
    }

    public function changeStatus($ids, $status)
    {
        if (!in_array($status, [0, 1])) return false;
        return Db::name('friend_circle_circle')->whereIn('circle_id', $ids)->update(['status' => $status]);
    }


    public function changeDismiss($id)
    {
        return Db::name('friend_circle_circle')->whereIn('circle_id', $id)->update(['dismiss' => 1,'dismiss_time'=>time(),'status'=>0]);
    }

    public function backstageadd($data)
    {
        unset($data['access_token']);
        unset($data['release_time']);
        unset($data['redirect']);
        $data['ctime'] = time();
        return Db::name('friend_circle_circle')->insertGetId($data);
    }

    public function backstageedit($data)
    {
        $datas = [
            "circle_name"           => $data['circle_name'],
            "circle_describe"       => $data['circle_describe'],
            "circle_cover_img"      => $data['circle_cover_img'],
            "circle_background_img" => $data['circle_background_img'],
            "status"                => $data['status'],
            "is_recom"              => $data['is_recom'],
            "uid"              => $data['uid'],
            "utime"                 => time(),
        ];
        return Db::name('friend_circle_circle')->where(['circle_id' => $data['id']])->update($datas);
    }

    public function getQuery($condition, $field, $order)
    {
        $this->db = Db::name('friend_circle_circle');
        $list     = $this->db->field($field)->where($condition)->order($order)->select();
        return $list;
    }

    public function getQueryNum($condition, $field, $order,$num)
    {
        $this->db = Db::name('friend_circle_circle');
        $list     = $this->db->field($field)->where($condition)->order($order)->limit($num)->select();
        return $list;
    }

    public function clume($condition)
    {
        $this->db = Db::name('friend_circle_circle');
        return Db::name('friend_circle_circle')->where($condition)->column('circle_id,circle_name');
    }

}
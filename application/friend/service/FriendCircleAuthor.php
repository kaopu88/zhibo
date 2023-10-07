<?php
/**
 * Created by PhpStorm.
 * User: “崔鹏
 * Date: 2020/07/21
 * Time: 上午 09:50
 */

namespace app\friend\service;

use bxkj_common\RedisClient;
use bxkj_module\service\Service;
use think\Db;

class FriendCircleAuthor extends Service
{
    public function add($data)
    {
        $data['ctime'] = time();
        $countTotal    = $this->countTotal(['name' => $data['name']]);
        if ($countTotal > 0) {
            return -1;
        }
        $id = Db::name('friend_circle_author')->insertGetId($data);
        return $id;
    }

    public function getTopic($condition, $field, $order, $limit)
    {
        $this->db = Db::name('friend_circle_author');
        $list     = $this->db->field($field)->where($condition)->order($order)->limit($limit)->select();
        return $list;
    }

    public function getQuery($condition, $field, $order)
    {
        $this->db = Db::name('friend_circle_author');
        $list     = $this->db->field($field)->where($condition)->order($order)->select();
        return $list;
    }

    public function countTotal($where)
    {
        $this->db = Db::name('friend_circle_author');
        $count    = $this->db->where($where)->count();
        return (int)$count;
    }

    public function find($where)
    {
        $this->db = Db::name('friend_circle_author');
        $info     = $this->db->where($where)->find();
        return $info;
    }

    public function pageQuery($page_index, $page_size, $condition, $order, $field)
    {
        $this->db = Db::name('friend_circle_author');
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
        $this->db = Db::name('friend_circle_author');
        $this->setWhere($get);
        $count = $this->db->count();
        return (int)$count;
    }

    public function getList($get, $offset = 0, $length = 20)
    {
        $this->db = Db::name('friend_circle_author');
        $this->setWhere($get)->setOrder($get);
        $result = $this->db->limit($offset, $length)->select();
        foreach ($result as $k => $v) {
            $result[$k]['user'] = userMsg($v['uid'], 'user_id,nickname,avatar,phone,level,remark_name');
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
        if ($get['is_recom'] != '') {
            $where['is_recom'] = $get['is_recom'];
        }
        if ($get['status'] != '') {
            $where['status'] = $get['status'];
        }
        if ($get['keyword'] != '') {
            $where1[] = ['name', 'like', '%' . $get['keyword'] . '%'];
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

    public function changeStatus($ids, $status)
    {
        if (!in_array($status, [0, 1])) return false;
        return Db::name('friend_circle_author')->whereIn('id', $ids)->update(['status' => $status]);
    }

    public function backstageadd($data)
    {
        $find = Db::name('friend_circle_author')->where(['name' => $data['name']])->find();
        if ($find) {
            return -1;
        }
        $datainst = [
            'name'       => $data['name'],
            'type'        => $data['type']?$data['type']:0,
            'initial'     => getFirstCharter($data['name']),
            'status'     =>$data['status']?$data['status']:1,
            'content'       => $data['content'],
            'ctime' => time(),
        ];
        return Db::name('friend_circle_author')->insertGetId($datainst);
    }

    public function del($ids)
    {
        return Db::name('friend_circle_author')->whereIn('id', $ids)->delete();
    }

    public function backstageedit($data)
    {
        $datas = [
            'name'       => $data['name'],
            'type'        => $data['type']?$data['type']:0,
            'initial'     => getFirstCharter($data['name']),
            'status'     =>$data['status']?$data['status']:1,
            'content'       => $data['content'],
            'ctime' => time(),
        ];
        return Db::name('friend_circle_author')->where(['id' => $data['id']])->update($datas);
    }

}

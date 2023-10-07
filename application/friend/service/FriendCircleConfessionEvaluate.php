<?php
/**
 * Created by PhpStorm.
 * User: “崔鹏
 * Date: 2020/08/14
 * Time: 下午 4:18
 */

namespace app\friend\service;

use app\api\service\Follow as FollowModel;
use bxkj_common\RedisClient;
use bxkj_module\service\Message;
use bxkj_module\service\Service;
use think\Db;

class FriendCircleConfessionEvaluate extends Service
{

    public function add($data1)
    {
        unset($data1['access_token']);
        $data                = [
            'fcmid' => $data1['fcmid'],
            'uid'       => $data1['uid'],
            'touid'     => $data1['touid'] ? $data1['touid'] : 0,
            'content' => emoji_encode($data1['content']),
            'imgs'      => $data1['imgs'] ? $data1['imgs'] : '',
            'create_time' => time()
        ];
        Db::startTrans();
        $id    = Db::name('friend_circle_confession_evaluate')->insertGetId($data);
        $upNum = Db::name('friend_circle_message')->where('id', $data['fcmid'])->setInc('comment_num');
        if ($id && $upNum) {
            Db::commit();
            $msg           = new Message();
            $commentdetail = Db::name('friend_circle_confession_evaluate')->where('id', $id)->find();
            $comment       = Db::name('friend_circle_message')->where('id', $data['fcmid'])->find();
            $result        = $msg->setReceiver($comment['uid'])->setSender($commentdetail['uid'])->sendFriendCommentEvaluate(['msg_id' => $data['fcmid'],'comment_id' =>$id,'comment_evaluate_id'=>$id, 'fcomment_title' => $comment['dynamic_title'] ? $comment['dynamic_title']  : '',  'cover_url' => $comment['picture'] ? $comment['picture'] : '',  'user_id' => $commentdetail['uid'], 'touid' => $commentdetail['touid']]);
        } else {
            Db::rollback();
            return 0;
        }
        return $id;
    }

    public function countTotal($where)
    {
        $this->db = Db::name('friend_circle_confession_evaluate');
        $count    = $this->db->where($where)->count();
        return (int)$count;
    }

    public function find($where, $order)
    {
        $this->db = Db::name('friend_circle_confession_evaluate');
        $info     = $this->db->where($where)->order($order)->find();
        return $info;
    }

    public function pageQuery($page_index, $page_size, $condition, $order, $field)
    {
        $this->db = Db::name('friend_circle_confession_evaluate');
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
        $this->db = Db::name('friend_circle_confession_evaluate');
        $this->setWhere($get);
        $count = $this->db->count();
        return (int)$count;
    }

    public function getList($get, $offset = 0, $length = 20)
    {
        $this->db = Db::name('friend_circle_confession_evaluate');
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
        if ($get['fcmid'] != 0) {
            $where['fcmid'] = $get['fcmid'];
        }
        if (is_numeric($get['status'])) {
            $where['status'] = $get['status'];
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

    public function backstageadd($databa)
    {
        $data = [
            'commentid'   => $databa['commentid'],
            'uid'         => $databa['uid'],
            'content'     => $databa['content'] ? $databa['content'] : '',
            'like_count'  => $databa['like_count'] ? $databa['like_count'] : '',
            'create_time' => time(),
        ];
        $id   = Db::name('friend_circle_confession_evaluate')->insertGetId($data);
        return $id;
    }

    public function backstageedit($databa)
    {
        $data = [
            'fcmid'   => $databa['fcmid'],
            'uid'         => $databa['uid'],
            'content'     => $databa['content'] ? $databa['content'] : '',
            'like_count'  => $databa['like_count'] ? $databa['like_count'] : '',
            'imgs'         => $databa['imgs']?$databa['imgs']:'',
            'status'      => $databa['status'],
            'create_time' => time(),
        ];
        $id   = Db::name('friend_circle_confession_evaluate')->where(array('id' => $databa['id']))->update($data);
        return $id;
    }

    public function del($ids)
    {
        Db::startTrans();
        $evaluateDel = Db::name('friend_circle_confession_evaluate')->whereIn('id' , $ids)->delete();

        if ($evaluateDel) {
            Db::commit();
        } else {
            Db::rollback();
            return 0;
        }
        return 1;
    }

    public function changeStatus($ids, $status)
    {
        if (!in_array($status, [0, 1])) return false;
        return Db::name('friend_circle_confession_evaluate')->whereIn('id', $ids)->update(['status' => $status]);
    }

    public function getQueryNum($condition, $field, $order,$num)
    {
        $this->db = Db::name('friend_circle_confession_evaluate');
        $list     = $this->db->field($field)->where($condition)->order($order)->limit($num)->select();

        foreach ($list as $k => $v) {
            $list[$k]['userdetail'] = userMsg($v['uid'], 'user_id,nickname,avatar');
            if (!empty($v['touid'])) {
                $list[$k]['touid'] = userMsg($v['touid'], 'user_id,nickname,avatar');
            } else {
                $list[$k]['touid'] = null;
            }
            $list[$k]['create_time'] = diff_time(time() - $v['create_time']);
        }
        return $list;
    }
}
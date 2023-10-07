<?php
/**
 * Created by PhpStorm.
 * User: “崔鹏
 * Date: 2020/06/20
 * Time: 上午 10:18
 */

namespace app\friend\service;

use app\api\service\Follow as FollowModel;
use bxkj_common\RedisClient;
use bxkj_module\service\Message;
use bxkj_module\service\Service;
use think\Db;

class FriendCircleCommentEvaluate extends Service
{
    public function add($data1)
    {
        unset($data1['access_token']);
        $data                = [
            'commentid' => $data1['commentid'],
            'uid'       => $data1['uid'],
            'touid'     => $data1['touid'] ? $data1['touid'] : 0,
            'content'   => emoji_encode($data1['content']),
            'status'    => $data1['status'],
        ];
        $data['create_time'] = time();
        Db::startTrans();
        $id    = Db::name('friend_circle_comment_evaluate')->insertGetId($data);
        $upNum = Db::name('friend_circle_comment')->where('id', $data['commentid'])->setInc('evaluate_count');
        if ($id && $upNum) {
            Db::commit();
            $msg           = new Message();
            $commentdetail = Db::name('friend_circle_comment_evaluate')->where('id', $id)->find();
            $comment       = Db::name('friend_circle_comment')->where('id', $data['commentid'])->find();
           if($comment['uid']!=$commentdetail['uid']){
               $result        = $msg->setReceiver($comment['uid'])->setSender($commentdetail['uid'])->sendFriendCommentEvaluate(['msg_id' => $comment['fcmid'],'comment_id' =>$comment['id'],'comment_evaluate_id'=>$id, 'fcomment_title' => $comment['content'] ? $comment['content']  : '', 'comment_evaluate_title' => $commentdetail['content'], 'cover_url' => $comment['imgs'] ? $comment['imgs'] : '',  'user_id' => $commentdetail['uid'], 'touid' => $commentdetail['touid']]);
           }

        } else {
            Db::rollback();
            return 0;
        }
        return $id;
    }

    public function countTotal($where)
    {
        $this->db = Db::name('friend_circle_comment_evaluate');
        $count    = $this->db->where($where)->count();
        return (int)$count;
    }

    public function find($where, $order)
    {
        $this->db = Db::name('friend_circle_comment_evaluate');
        $info     = $this->db->where($where)->order($order)->find();
        return $info;
    }

    public function pageQuery($page_index, $page_size, $condition, $order, $field)
    {
        $this->db = Db::name('friend_circle_comment_evaluate');
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
        foreach ($list as $k=>$v){
            $list[$k]['content'] = emoji_decode($v['content']);
        }
        return array(
            'data'        => $list,
            'total_count' => $count,
            'page_count'  => $page_count
        );
    }

    public function getTotal($get)
    {
        $this->db = Db::name('friend_circle_comment_evaluate');
        $this->setWhere($get);
        $count = $this->db->count();
        return (int)$count;
    }

    public function getList($get, $offset = 0, $length = 20)
    {
        $this->db = Db::name('friend_circle_comment_evaluate');
        $this->setWhere($get)->setOrder($get);
        $result = $this->db->limit($offset, $length)->select();
        foreach ($result as $k => $v) {
            $result[$k]['user'] = userMsg($v['uid'], 'user_id,nickname,avatar,phone,level,remark_name');
            $result[$k]['content'] = emoji_decode($v['content']);
        }
        return $result;
    }

    protected function setWhere($get)
    {
        $where  = array();
        $where1 = array();
        if ($get['commentid'] != 0) {
            $where['commentid'] = $get['commentid'];
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
        $id   = Db::name('friend_circle_comment_evaluate')->insertGetId($data);
        return $id;
    }

    public function backstageedit($databa)
    {
        $data = [
            'commentid'   => $databa['commentid'],
            'uid'         => $databa['uid'],
            'content'     => $databa['content'] ? $databa['content'] : '',
            'like_count'  => $databa['like_count'] ? $databa['like_count'] : '',
            'status'      => $databa['status'],
            'create_time' => time(),
        ];
        $id   = Db::name('friend_circle_comment_evaluate')->where(array('id' => $databa['id']))->update($data);
        return $id;
    }

    public function del($ids)
    {
        //这里还有删除回复表，回复点赞表
        $evaluatelive = 1;
        Db::startTrans();
        $evaluateDel = Db::name('friend_circle_comment_evaluate')->whereIn('id' , $ids)->delete();
        if (Db::name('friend_circle_comment_evaluate_live')->whereIn('commentmsgid' , $ids)->count()) {
            $evaluatelive = 0;
            $evaluatelive = Db::name('friend_circle_comment_evaluate_live')->whereIn('commentmsgid' , $ids)->delete();
        }
        if ($evaluateDel && $evaluatelive) {
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
        return Db::name('friend_circle_comment_evaluate')->whereIn('id', $ids)->update(['status' => $status]);
    }

    public function getQueryNum($condition, $field, $order,$num)
    {
        $this->db = Db::name('friend_circle_comment_evaluate');
        $list     = $this->db->field($field)->where($condition)->order($order)->limit($num)->select();

        foreach ($list as $k => $v) {
            $list[$k]['userdetail'] = userMsg($v['uid'], 'user_id,nickname,avatar');
            if (!empty($v['touid'])) {
                $list[$k]['touid'] = userMsg($v['touid'], 'user_id,nickname,avatar');
            } else {
                $list[$k]['touid'] = null;
            }
            $list[$k]['create_time'] = diff_time(time() - $v['create_time']);
            $list[$k]['content'] = emoji_decode($v['content']);
        }
        return $list;
    }
}
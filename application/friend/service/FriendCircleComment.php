<?php
/**
 * Created by PhpStorm.
 * User: “崔鹏
 * Date: 2020/06/19
 * Time: 上午 10:18
 */

namespace app\friend\service;

use app\api\service\Follow as FollowModel;
use bxkj_common\RedisClient;
use bxkj_module\service\Message;
use bxkj_module\service\Service;
use think\Db;

class FriendCircleComment extends Service
{
    public function add($data1)
    {
        unset($data1['access_token']);
        $data                = [
            'uid'     => $data1['uid'],
            'content' => emoji_encode($data1['content']),
            'imgs'    => $data1['imgs'] ? $data1['imgs'] : '',
            'voice'   => $data1['voice'] ? $data1['voice'] : '',
            'fcmid'   => $data1['fcmid'],
            'status'  => $data1['status'],
        ];
        $data['create_time'] = time();
        Db::startTrans();
        $id    = Db::name('friend_circle_comment')->insertGetId($data);
        $upNum = Db::name('friend_circle_message')->where('id', $data['fcmid'])->setInc('comment_num');
        if ($id && $upNum) {
            Db::commit();
            $rest          = changeRedisMsg($data['fcmid']);
            $msg           = new Message();
            $commentdetail = Db::name('friend_circle_comment')->where('id', $id)->find();
            $messagedetail = Db::name('friend_circle_message')->where('id', $data['fcmid'])->find();
            if ($messagedetail['uid'] != $data['uid']) {
                $result = $msg->setReceiver($messagedetail['uid'])->setSender($data['uid'])->sendFriendComment(['msg_id' => $data['fcmid'], 'fcomment_title' => $commentdetail['content'] ? $commentdetail['content'] : '', 'cover_url' => $commentdetail['imgs'] ? $commentdetail['imgs'] : '', 'comment_id' => $id, 'user_id' => $commentdetail['uid']]);
            }
        } else {
            Db::rollback();
            return 0;
        }
        return $id;
    }

    public function countTotal($where)
    {
        $this->db = Db::name('friend_circle_comment');
        $count    = $this->db->where($where)->count();
        return (int)$count;
    }

    public function find($where, $order)
    {
        $this->db = Db::name('friend_circle_comment');
        $info     = $this->db->where($where)->order($order)->find();
        return $info;
    }

    public function pageQuery($page_index, $page_size, $condition, $order, $field)
    {
        $this->db = Db::name('friend_circle_comment');
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
        $this->db = Db::name('friend_circle_comment');
        $this->setWhere($get);
        $count = $this->db->count();
        return (int)$count;
    }

    public function getList($get, $offset = 0, $length = 20)
    {
        $this->db = Db::name('friend_circle_comment');
        $this->setWhere($get)->setOrder($get);
        $result = $this->db->limit($offset, $length)->select();
        foreach ($result as $k => $v) {
            $result[$k]['user']    = userMsg($v['uid'], 'user_id,nickname,avatar,phone,level,remark_name');
            $result[$k]['imgs']    = array_filter(explode(',', $v['imgs']));
            $result[$k]['content'] = emoji_decode($v['content']);
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
        if ($get['fcmid'] != '') {
            $where['fcmid'] = $get['fcmid'];
        }
        if ($get['keyword'] != '') {
            $where1[] = ['content', 'like', '%' . $get['keyword'] . '%'];
        }
        if (is_numeric($get['status'])) {
            $where['status'] = $get['status'];
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
        Db::startTrans();
        $data  = [
            'fcmid'          => $databa['fcmid'],
            'uid'            => $databa['uid'],
            'content'        => $databa['content'] ? $databa['content'] : '',
            'imgs'           => $databa['images'] ? $databa['images'] : '',
            'evaluate_count' => $databa['evaluate_count'] ? $databa['evaluate_count'] : '',
            'like_count'     => $databa['like_count'] ? $databa['like_count'] : '',
            'create_time'    => time(),
        ];
        $id    = Db::name('friend_circle_comment')->insertGetId($data);
        $upNum = Db::name('friend_circle_message')->where('id', $databa['fcmid'])->setInc('comment_num');
        if ($id && $upNum) {
            Db::commit();
            $rest = changeRedisMsg($databa['fcmid']);
        } else {
            Db::rollback();
            return 0;
        }
        return $id;
    }

    public function backstageedit($databa)
    {
        $data = [
            'fcmid'          => $databa['fcmid'],
            'uid'            => $databa['uid'],
            'content'        => $databa['content'] ? $databa['content'] : '',
            'imgs'           => $databa['images'] ? $databa['images'] : '',
            'evaluate_count' => $databa['evaluate_count'] ? $databa['evaluate_count'] : '',
            'like_count'     => $databa['like_count'] ? $databa['like_count'] : '',
            'status'         => $databa['status'],
            'create_time'    => time(),
        ];
        $id   = Db::name('friend_circle_comment')->where(array('id' => $databa['id']))->update($data);
        return $id;
    }

    public function del($arr)
    {
        $ids = $arr['commentid'];
        $find    = Db::name('friend_circle_comment')->where('id', $ids)->find();
        $msgid = $find['fcmid'];
        $findeva = Db::name('friend_circle_comment_evaluate')->where(['commentid' => $find['id']])->find();
        //这里还有删除评论表，评论点赞表，回复表，回复点赞表
        $commentLiveDel = $evaluateDel = $evaluatelive = 1;
        Db::startTrans();
        $commentDel = Db::name('friend_circle_comment')->where('id', $ids)->delete();
        if (Db::name('friend_circle_comment_live')->where(['commentid' => $find['id']])->count()) {
            $commentLiveDel = 0;
            $commentLiveDel = Db::name('friend_circle_comment_live')->where(['commentid' => $find['id']])->delete();
        }
        if (Db::name('friend_circle_comment_evaluate')->where(['commentid' => $find['id']])->count()) {
            $evaluateDel = 0;
            $evaluateDel = Db::name('friend_circle_comment_evaluate')->where(['commentid' => $find['id']])->delete();
        }
        if (Db::name('friend_circle_comment_evaluate_live')->where(['commentmsgid' => $findeva['id']])->count()) {
            $evaluatelive = 0;
            $evaluatelive = Db::name('friend_circle_comment_evaluate_live')->where(['commentmsgid' => $findeva['id']])->delete();
        }
        if ($commentDel && $commentLiveDel && $evaluateDel && $evaluatelive) {
            Db::commit();
            Db::name('friend_circle_message')->where('id', $msgid)->setDec('comment_num');
            changeRedisMsg($msgid);
        } else {
            Db::rollback();
            return 0;
        }
        return 1;
    }



    public function changeStatus($ids, $status)
    {
        if (!in_array($status, [0, 1])) return false;
        return Db::name('friend_circle_comment')->whereIn('id', $ids)->update(['status' => $status]);
    }

    public function getQuery($condition, $field, $order)
    {
        $this->db = Db::name('friend_circle_comment');
        $list     = $this->db->field($field)->where($condition)->order($order)->select();
        return $list;
    }

    public function delids($ids){
        $find    = Db::name('friend_circle_comment')->whereIn('id', $ids)->select();
        //这里还有删除评论表，评论点赞表，回复表，回复点赞表
        foreach ($find as $k=>$v){
            $commentLiveDel = $evaluateDel = $evaluatelive = 1;
            $findeva = Db::name('friend_circle_comment_evaluate')->where(['commentid' => $v['id']])->find();
            Db::startTrans();
            $commentDel = Db::name('friend_circle_comment')->where('id', $v['id'])->delete();
            if (Db::name('friend_circle_comment_live')->where(['commentid' => $v['id']])->count()) {
                $commentLiveDel = 0;
                $commentLiveDel = Db::name('friend_circle_comment_live')->where(['commentid' => $v['id']])->delete();
            }
            if (Db::name('friend_circle_comment_live')->where(['commentid' => $v['id']])->count()) {
                $commentLiveDel = 0;
                $commentLiveDel = Db::name('friend_circle_comment_live')->where(['commentid' => $v['id']])->delete();
            }
            if (Db::name('friend_circle_comment_evaluate')->where(['commentid' => $v['id']])->count()) {
                $evaluateDel = 0;
                $evaluateDel = Db::name('friend_circle_comment_evaluate')->where(['commentid' => $v['id']])->delete();
            }
            if (Db::name('friend_circle_comment_evaluate_live')->where(['commentmsgid' => $findeva['id']])->count()) {
                $evaluatelive = 0;
                $evaluatelive = Db::name('friend_circle_comment_evaluate_live')->where(['commentmsgid' => $findeva['id']])->delete();
            }
            if ($commentDel && $commentLiveDel && $evaluateDel && $evaluatelive) {
                Db::commit();

            } else {
                Db::rollback();
                return 0;
            }
        }
        return 1;

    }
}
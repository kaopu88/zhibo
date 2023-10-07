<?php
/**
 * Created by PhpStorm.
 * User: “崔鹏
 * Date: 2020/06/15
 * Time: 下午 2:51
 */

namespace app\friend\service;

use app\api\service\Follow as FollowModel;
use bxkj_common\RedisClient;
use bxkj_module\service\Message;
use bxkj_module\service\Service;
use think\Db;

class FriendCircleMessage extends Service
{
    public function add($data)
    {
        $instF = 1;
        $instP = 1;
        Db::startTrans();
        $id             = Db::name('friend_circle_message')->insertGetId($data);
        $friendTimeline = new FriendCircleTimelin();
        $instdata       = [
            'uid'         => $data['uid'],
            'fcmid'       => $id,
            'is_own'      => 1,
            'create_time' => time(),
            'type'        => $data['type'],
            'msg_type'    => $data['msg_type'],
            'status'      => $data['status'],
            'extend_type' => $data['extend_type'],
            'is_recommend' => isset($data['is_recommend']) ? $data['is_recommend'] : 0,
        ];
        $inst           = $friendTimeline->add($instdata);
        if ($data['msg_type'] == 2) {
            $instF       = 0;
            $followModel = new FollowModel();
            $myFriendsList = $followModel->mutualArray(USERID);
            foreach ($myFriendsList as $k => $v) {
                $instdataF = [
                    'uid'         => $v,
                    'fcmid'       => $id,
                    'is_own'      => 0,
                    'create_time' => time(),
                    'type'        => $data['type'],
                    'msg_type'    => $data['msg_type'],
                    'status'      => $data['status'],
                    'extend_type' => $data['extend_type'],
                    'is_recommend' => isset($data['is_recommend'])  ? $data['is_recommend'] : 0,
                ];
                $instF          = $friendTimeline->add($instdataF);
                $friendTemp[$v] = $instF;
            };
        }
        if (!empty($data['privateid'])) {
            if ($data['msg_type'] == 4) {
                $instP        = 0;
                $friendTemp   = [];
                $privateArray = explode(',', $data['privateid']);
                foreach ($privateArray as $k1 => $v1) {
                    $instdataP       = [
                        'uid'         => $v1,
                        'fcmid'       => $id,
                        'is_own'      => 0,
                        'create_time' => time(),
                        'type'        => $data['type'],
                        'msg_type'    => $data['msg_type'],
                        'extend_type' => $data['extend_type'],
                        'is_recommend' =>isset($data['is_recommend'])  ? $data['is_recommend'] : 0,
                    ];
                    $instP           = $friendTimeline->add($instdataP);
                    $friendTemp[$v1] = $instP;
                }
            }
        }
        if ($id && $inst && $instF && $instP) {
            // 提交事务
            Db::commit();
            $redis = RedisClient::getInstance();
            if ($data['msg_type'] != 4 && $data['msg_type'] != 2) {
                if (!empty($data['privateid'])) {
                    $privateArray = explode(',', $data['privateid']);
                    foreach ($privateArray as $key => $val) {
                        $msg     = new Message();
                        $gotourl = LOCAL_PROTOCOL_DOMAIN . 'dynamic?id=' . $id;
                        $result  = $msg->setReceiver($val)->setSender($data['uid'])->sendAtFriend(['scene' => 'friend_push_dynamic', 'dynamic_id' => $inst, 'dynamic_title' => $data['dynamic_title'], 'cover_url' => $data['cover_url'], 'friend_type' => $data['type'], 'url' => $gotourl]);
                    }
                }
            } else {
                if (empty($data['privateid'])) {
                    //这里需不需要发通知
                } else {
                    $privateArray = explode(',', $data['privateid']);
                    foreach ($privateArray as $key => $val) {
                        $msg     = new Message();
                        $gotourl = LOCAL_PROTOCOL_DOMAIN . 'dynamic?id=' . $id;
                        $result  = $msg->setReceiver($val)->setSender($data['uid'])->sendAtFriend(['scene' => 'friend_push_dynamic', 'dynamic_id' => $friendTemp[$val], 'dynamic_title' => $data['dynamic_title'], 'cover_url' => $data['cover_url'], 'friend_type' => $data['type'], 'url' => $gotourl]);
                    }
                }
            }
        } else {
            // 回滚事务
            Db::rollback();
            return 0;
        }
        return $inst;
    }

    public function getTotal($get)
    {
        $this->db = Db::name('friend_circle_message');
        $this->setWhere($get);
        $count = $this->db->count();
        return (int)$count;
    }

    public function getList($get, $offset = 0, $length = 20)
    {
        $this->db = Db::name('friend_circle_message');
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
        if ($get['extend_type'] != 0) {
            $where['extend_type'] = $get['extend_type'];
        }
        if ($get['msg_type'] != '') {
            $where['msg_type'] = $get['msg_type'];
        }
        if (is_numeric($get['status'])) {
            $where['status'] = $get['status'];
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

    public function pageQuery($page_index, $page_size, $condition, $condition1, $order, $field)
    {
        $this->db = Db::name('friend_circle_message');
        $count    = $this->db->where($condition)->whereOr($condition1)->count();
        if ($page_size == 0) {
            $list       = $this->db->field($field)
                ->where($condition)
                ->whereOr($condition1)
                ->order($order)
                ->select();
            $page_count = 1;
        } else {
            $start_row = $page_size * ($page_index - 1);
            $list      = $this->db->field($field)
                ->where($condition)
                ->whereOr($condition1)
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

    public function changeRecom($ids, $status)
    {
        if (!in_array($status, [0, 1])) return false;
        Db::name('friend_circle_message')->whereIn('id', $ids)->update(['is_recommend' => $status]);
        $redis = RedisClient::getInstance();
        $rest  = Db::name('friend_circle_message')->where(['id' => $ids[0]])->select();
        $redis->set("bx_friend_msg:" . $ids[0], json_encode($rest));
        return Db::name('friend_circle_timeline')->whereIn('fcmid', $ids)->update(['is_recommend' => $status]);
    }

    public function changeStatus($ids, $status)
    {
        if (!in_array($status, [0, 1])) return false;
        Db::name('friend_circle_message')->whereIn('id', $ids)->update(['status' => $status]);
        $redis = RedisClient::getInstance();
        $rest  = Db::name('friend_circle_message')->where(['id' => $ids[0]])->select();;
        $redis->set("bx_friend_msg:" . $ids[0], json_encode($rest));
        return Db::name('friend_circle_timeline')->whereIn('fcmid', $ids)->update(['status' => $status]);
    }

    public function backstageadd($databa)
    {
        $data  = [
            'uid'            => $databa['uid'],
            'title'          => $databa['title'] ? $databa['title'] : '',
            'content'        => $databa['content'] ? $databa['content'] : '',
            'picture'        => $databa['images'] ? $databa['images'] : '',
            'video'          => $databa['video'] ? $databa['video'] : '',
            'voice'          => $databa['audio'] ? $databa['audio'] : '',
            'location'       => $databa['location'] ? $databa['location'] : '',
            'type'           => $databa['type'],
            'extend_type'    => $databa['extend_type'] ? $databa['extend_type'] : '',
            'msg_type'       => $databa['msg_type'],
            'is_recommend'   => $databa['is_recommend'],
            'status'         => $databa['status'],
            'comment_status' => $databa['comment_status'] ? $databa['comment_status'] : 1,
            'comment_num'    => $databa['comment_num'] ? $databa['comment_num'] : '',
            'like_num'       => $databa['like_num'] ? $databa['like_num'] : '',
            'dynamic_title'  => $databa['dynamic_title'] ? $databa['dynamic_title'] : '',
            'create_time'    => time(),
        ];
        $instF = 1;
        Db::startTrans();
        $id             = Db::name('friend_circle_message')->insertGetId($data);
        $friendTimeline = new FriendCircleTimelin();
        $instdata       = [
            'uid'          => $data['uid'],
            'fcmid'        => $id,
            'is_own'       => 1,
            'create_time'  => time(),
            'type'         => $data['type'],
            'msg_type'     => $data['msg_type'],
            'is_recommend' => $data['is_recommend'] ? $data['is_recommend'] : 0,
            'status'       => $data['status'],
            'extend_type'  => $data['extend_type'],
        ];
        $inst           = $friendTimeline->add($instdata);
        if ($data['msg_type'] == 2) {
            $instF         = 0;
            $followModel   = new FollowModel();
            $myFriendsList = $followModel->mutualArray($data['uid']);
            foreach ($myFriendsList as $k => $v) {
                $instdata = [
                    'uid'          => $v,
                    'fcmid'        => $id,
                    'is_own'       => 0,
                    'create_time'  => time(),
                    'type'         => $data['type'],
                    'msg_type'     => $data['msg_type'],
                    'is_recommend' => $data['is_recommend'] ? $data['is_recommend'] : 0,
                    'status'       => $data['status'],
                    'extend_type'  => $data['extend_type'],
                ];
                $res[]    = $instdata;
            };
            $num   = 100;//每次导入条数
            $limit = ceil(count($res) / $num);
            for ($i = 1; $i <= $limit; $i++) {
                $offset = ($i - 1) * $num;
                $data   = array_slice($res, $offset, $num);
                $instF  = $friendTimeline->insertAll($data);
            };
        }
        if ($id && $inst && $instF) {
            // 提交事务
            Db::commit();
            $redis = RedisClient::getInstance();
            $friendMsg = new FriendCircleMessage();
            $rest1     = $friendMsg->getQuery(['id' => $id], '*', 'id');
            $redis->set("bx_friend_msg:" .$id, json_encode($rest1));
        } else {
            // 回滚事务
            Db::rollback();
            return 0;
        }
        return $id;
    }

    public function backstageedit($databa)
    {
        $data  = [
            'uid'            => $databa['uid'],
            'title'          => $databa['title'] ? $databa['title'] : '',
            'content'        => $databa['content'] ? $databa['content'] : '',
            'picture'        => $databa['images'] ? $databa['images'] : '',
            'video'          => $databa['video'] ? $databa['video'] : '',
            'voice'          => $databa['audio'] ? $databa['audio'] : '',
            'location'       => $databa['location'] ? $databa['location'] : '',
            'type'           => $databa['type'],
            'extend_type'    => $databa['extend_type'] ? $databa['extend_type'] : '',
            'msg_type'       => $databa['msg_type'],
            'is_recommend'   => $databa['is_recommend'] ? $databa['is_recommend'] : 0,
            'status'         => $databa['status'],
            'comment_status' => $databa['comment_status'],
            'comment_num'    => $databa['comment_num'] ? $databa['comment_num'] : '',
            'like_num'       => $databa['like_num'] ? $databa['like_num'] : '',
            'dynamic_title'  => $databa['dynamic_title'] ? $databa['dynamic_title'] : '',
            'create_time'    => time(),
        ];
        $instF = 1;
        Db::startTrans();
        $id             = Db::name('friend_circle_message')->where(array('id' => $databa['id']))->update($data);
        $timeDel        = Db::name('friend_circle_timeline')->where(['fcmid' => $databa['id']])->delete();
        $friendTimeline = new FriendCircleTimelin();
        $instdata       = [
            'uid'          => $data['uid'],
            'fcmid'        => $databa['id'],
            'is_own'       => 1,
            'create_time'  => time(),
            'type'         => $data['type'],
            'msg_type'     => $data['msg_type'],
            'is_recommend' => $data['is_recommend'],
            'status'       => $data['status'],
            'extend_type'  => $data['extend_type'],
        ];
        $inst           = $friendTimeline->add($instdata);
        if ($data['msg_type'] == 2) {
            $instF         = 0;
            $followModel   = new FollowModel();
            $myFriendsList = $followModel->mutualArray($data['uid']);
            foreach ($myFriendsList as $k => $v) {
                $instdata = [
                    'uid'          => $v,
                    'fcmid'        => $databa['id'],
                    'is_own'       => 0,
                    'create_time'  => time(),
                    'type'         => $data['type'],
                    'msg_type'     => $data['msg_type'],
                    'is_recommend' => $data['is_recommend'] ? $data['is_recommend'] : 0,
                    'status'       => $data['status'],
                    'extend_type'  => $data['extend_type'],
                ];
                $res[]    = $instdata;
            };
            $num   = 100;//每次导入条数
            $limit = ceil(count($res) / $num);
            for ($i = 1; $i <= $limit; $i++) {
                $offset = ($i - 1) * $num;
                $data   = array_slice($res, $offset, $num);
                $instF  = $friendTimeline->insertAll($data);
            };
        }
        if ($id && $inst && $instF) {
            // 提交事务
            Db::commit();
            $redis = RedisClient::getInstance();
            $friendMsg = new FriendCircleMessage();
            $rest1     = $friendMsg->getQuery(['id' => $databa['id']], '*', 'id');
            $redis->set("bx_friend_msg:" .$databa['id'], json_encode($rest1));

        } else {
            // 回滚事务
            Db::rollback();
            return 0;
        }
        return $id;
    }

    public function hotTopic($where, $order, $limit)
    {
        $this->db = Db::name('friend_circle_message');
        $rest     = $this->db->field('id,title')->where($where)->order($order)->limit($limit)->select();
        return $rest;
    }

    public function getQuery($condition, $field, $order)
    {
//        $now            = date("Y-m-d ", time() + 24 * 3600);
//        $lastMonthDatys = getlastMonthDays($now)[0];
        //       $list           = $this->db->field($field)->where($condition)->whereTime('create_time', 'between', [$lastMonthDatys, $now])->order($order)->select();
        $this->db = Db::name('friend_circle_message');
        $list     = $this->db->field($field)->where($condition)->order($order)->select();
        return $list;
    }

    public function del($ids)
    {
        Db::startTrans();
        $num     = Db::name('friend_circle_message')->whereIn('id', $ids)->delete();
        $timeDel = Db::name('friend_circle_timeline')->whereIn('fcmid', $ids)->delete();
        $rest    = Db::name('friend_circle_comment')->whereIn('fcmid', $ids)->select();

        if (!empty($rest)) {
            $friendCircleComment = new FriendCircleComment();
            foreach ($rest as $k => $v) {
                $friendCircleComment->del($v['id']);
            }
        }
        $redis = RedisClient::getInstance();
        foreach ($ids as $id) {
            $redis->del("bx_friend_msg:" . $id);
        }
        if ($num && $timeDel) {
            Db::commit();
        } else {
            Db::rollback();
            return 0;
        }
        return 1;
    }

    public function checkown($uid, $id)
    {
        return Db::name('friend_circle_message')->where(['uid' => $uid, 'id' => $id])->count();
    }

    public function atelyConnect($uid)
    {
        $resArray    = [];
        $followModel = new FollowModel();
        $myFollows   = $followModel->mutualArray($uid);
        $rest        = Db::name('chat_message')->field('id,from_uid,ctime')->whereIn('from_uid', $myFollows)->where(['to_uid' => $uid])->order('id desc')->select();
        if ($rest) {
            foreach ($rest as $k => $v) {
                $resArray[$v['ctime']] = $v['from_uid'];
            }
        }
        return array_unique($resArray);
    }

    public function mySender($page_index, $page_size, $condition, $order, $field)
    {
        $this->db = Db::name('friend_circle_message');
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

    public function countTatal($condition)
    {
        $res = Db::name('friend_circle_message')->where($condition)->count();
        return (int)$res;
    }

    public function find($where)
    {
        $rest =  Db::name('friend_circle_message')->find($where);
        return $rest;
    }

    public function searchQuery($page_index, $page_size, $condition, $order, $field)
    {
        $this->db = Db::name('friend_circle_message');
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


}
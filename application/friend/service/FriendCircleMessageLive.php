<?php
/**
 * Created by PhpStorm.
 * User: “崔鹏
 * Date: 2020/06/19
 * Time: 下午 1:45
 */

namespace app\friend\service;

use bxkj_common\RedisClient;
use bxkj_module\service\Message;
use bxkj_module\service\Service;
use think\Db;

class FriendCircleMessageLive extends Service
{
    public function live($data1)
    {
        $redis              = new RedisClient();
        $data['lived_time'] = time();
        $data['status']     = 1;
        $data['fcmid']      = $data1['fcmid'];
        $data['uid']        = $data1['uid'];
        $find = Db::name('friend_circle_message_live')->where(['uid' => $data['uid'], 'fcmid' => $data['fcmid']])->find();
        Db::startTrans();
        if ($find) {
            $updata = [
                'status'     => abs($find['status'] - 1),
                'lived_time' => time(),
            ];
            $commentid = $find['id'];
            $rest   = Db::name('friend_circle_message_live')->where(['uid' => $data['uid'], 'fcmid' => $data['fcmid']])->update($updata);
            $redis->setex('usermsg_live:' . $data['uid'], 30, $updata['status']);
        } else {
            $rest = Db::name('friend_circle_message_live')->insertGetId($data);
            $commentid = $rest;
            $redis->setex('usermsg_live:' . $data['uid'], 30, 1);
            finish_task($data1['uid'],'liveDynamic',1,0);
        }
        if ($find['status'] == 0) {
            $upNum = Db::name('friend_circle_message')->where('id', $data['fcmid'])->setInc('like_num');
        } else {
            $upNum = Db::name('friend_circle_message')->where('id', $data['fcmid'])->setDec('like_num');
        }
        if ($rest && $upNum) {
            // 提交事务
            Db::commit();
            changeRedisMsg($data['fcmid']);
            $msg       = new Message();
            $msgdetail = Db::name('friend_circle_message')->where('id', $data['fcmid'])->find();
            if (empty($find)){
            $result    = $msg->setReceiver($msgdetail['uid'])->setSender($msgdetail['uid'])->sendLikefriendMsg(['msg_id' => $data['fcmid'], 'msg_title' => $msgdetail['dynamic_title'], 'dynamic_title' => $msgdetail['dynamic_title'], 'cover_url' => $msgdetail['cover_url'], 'comment_id' => $commentid, 'user_id' => $msgdetail['uid']]);
            }
            } else {
            // 回滚事务
            Db::rollback();
            return 0;
        }
        return $rest;
    }

    public function countTotal($where)
    {
        $this->db = Db::name('friend_circle_message_live');
        $count    = $this->db->where($where)->count();
        return (int)$count;
    }

}
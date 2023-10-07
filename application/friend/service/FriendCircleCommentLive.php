<?php
/**
 * Created by PhpStorm.
 * User: “崔鹏
 * Date: 2020/06/19
 * Time: 下午 3:55
 */

namespace app\friend\service;

use bxkj_common\RedisClient;
use bxkj_module\service\Message;
use bxkj_module\service\Service;
use think\Db;

class FriendCircleCommentLive extends Service
{
    public function commentlive($dataC)
    {
        $redis = new RedisClient();
        unset($dataC['access_token']);
        $data['lived_time'] = time();
        $data['commentid']  = $dataC['commentid'];
        $data['uid'] = $dataC['uid'];
        $data['status']     = 1;
        $find               = Db::name('friend_circle_comment_live')->where(['uid' => $data['uid'], 'commentid' => $data['commentid']])->find();
        Db::startTrans();
        if ($find) {
            $updata = [
                'status'     => abs($find['status'] - 1),
                'lived_time' => time(),
            ];
            $rest   = Db::name('friend_circle_comment_live')->where(['uid' => $data['uid'], 'commentid' => $data['commentid']])->update($updata);
            $redis->setex('usercomment_live:' . $data['uid'], 1, $updata['status']);
        } else {
            $rest = Db::name('friend_circle_comment_live')->insertGetId($data);
            $redis->setex('usercomment_live:' . $data['uid'], 1, 1);
        }
        if ($find['status'] == 0) {
            $upNum = Db::name('friend_circle_comment')->where('id', $data['commentid'])->setInc('like_count');
        } else {
            $upNum = Db::name('friend_circle_comment')->where('id', $data['commentid'])->setDec('like_count');
        }
        if ($rest && $upNum) {
            Db::commit();
            $msg           = new Message();
            $commentdetail = Db::name('friend_circle_comment')->where('id', $data['commentid'])->find();
            $result        = $msg->setReceiver($commentdetail['uid'])->setSender($data['uid'])->sendLikefriendComment(['msg_id'=>$commentdetail['fcmid'], 'comment_id' => $data['commentid'],'fcomment_title' => $commentdetail['content'] ? $commentdetail['content'] : '','cover_url' => $commentdetail['content'] ? $commentdetail['content'] : '', 'user_id' => $commentdetail['uid']]);
        } else {
            Db::rollback();
            return 0;
        }
        return $rest;
    }

    //查询自己是否点过赞，点过就返还相关信息
    public function queryUserLive($data)
    {
        return Db::name('friend_circle_comment_live')->where(['uid' => $data['uid'], 'commentid' => $data['id']])->find();
    }
}
<?php
/**
 * Created by PhpStorm.
 * User: “崔鹏
 * Date: 2020/06/20
 * Time: 下午 2:10
 */

namespace app\friend\service;

use bxkj_common\RedisClient;
use bxkj_module\service\Message;
use bxkj_module\service\Service;
use think\Db;

class FriendCircleCommentEvaluateLive extends Service
{
    public function Evaluatelive($data)
    {
        $redis = new RedisClient();
        unset($data['access_token']);
        $data['lived_time'] = time();
        $data['status']     = 1;
        $find               = Db::name('friend_circle_comment_evaluate_live')->where(['uid' => $data['uid'], 'commentmsgid' => $data['commentmsgid']])->find();
        Db::startTrans();
        if ($find) {
            $updata = [
                'status'     => abs($find['status'] - 1),
                'lived_time' => time(),
            ];
            $rest   = Db::name('friend_circle_comment_evaluate_live')->where(['uid' => $data['uid'], 'commentmsgid' => $data['commentmsgid']])->update($updata);
            if($rest>0){
                $rest   = $find['id'];
            }

            $redis->setex('usercommentevaluate_live:' . $data['uid'], 30, $updata['status']);
        } else {
            $rest = Db::name('friend_circle_comment_evaluate_live')->insertGetId($data);
            $redis->setex('usercommentevaluate_live:' . $data['uid'], 30, 1);
        }
        if ($find['status'] == 0) {
            $upNum = Db::name('friend_circle_comment_evaluate')->where('id', $data['commentmsgid'])->setInc('like_count');
        } else {
            $upNum = Db::name('friend_circle_comment_evaluate')->where('id', $data['commentmsgid'])->setDec('like_count');
        }
        if ($rest && $upNum) {
            Db::commit();
            $msg            = new Message();
            $livedetail  = Db::name('friend_circle_comment_evaluate_live')->where('id', $rest)->find();
            $eveluatedetail = Db::name('friend_circle_comment_evaluate')->where('id', $data['commentmsgid'])->find();
            $commentdetail = Db::name('friend_circle_comment')->where('id',$eveluatedetail['commentid'])->find();
            $result         = $msg->setReceiver($eveluatedetail['uid'])->setSender($data['uid'])->sendLikefriendEvaluate(['msg_id'=>$commentdetail['fcmid'],'comment_id' => $commentdetail['id'],'evaluate_id'=>$eveluatedetail['id'], 'fcomment_title' => $commentdetail['content'] ? $commentdetail['content'] : '','cover_url' => $commentdetail['imgs'] ? $commentdetail['imgs'] : '', 'user_id' => $commentdetail['uid']]);
        } else {
            Db::rollback();
            return 0;
        }
        return $rest;
    }

    //查询自己是否点过赞，点过就返还相关信息
    public function queryUserLive($data)
    {
        return Db::name('friend_circle_comment_evaluate_live')->where(['uid' => $data['uid'], 'commentmsgid' => $data['commentmsgid']])->find();
    }
}
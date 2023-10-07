<?php

namespace app\common\service;

use bxkj_common\RedisClient;
use think\Db;

class Message extends Service
{

    //获取未读消息总数
    public function getUnreadTotal($catType, $userId, $groupId, $regTime)
    {
        $systemTotal = 0;
        $rewardTotal = 0;
        $otherTotal = 0;

        if ($catType == 'system' || $catType == '') {
            $systemTotal = $this->getSystemUnreadTotal($userId, $groupId, $regTime);
        }
        if ($catType == 'reward' || $catType == '') {
            $rewardTotal = $this->getRewardUnreadTotal($userId);
        }
        if (($catType != 'system' && $catType != 'reward') || $catType == '') {
            $where = array('user_id' => $userId, 'delete_time' => null, 'read_status' => '0');
            if ($catType != '') $where['cat_type'] = $catType;
            $otherTotal = Db::name('message')->where($where)->count();
        }
        return $systemTotal + $rewardTotal + $otherTotal;
    }

    public function getUnreadTotalCache($catType, $userId, $groupId, $regTime)
    {
        $catType = !empty($catType) ? $catType : 'all';
        $cacheKey = "messages:unread_cache:{$userId}:{$catType}";
        $redis = RedisClient::getInstance();
        $total = $redis->get($cacheKey);
        if ($total === false) {
            $total = $this->getUnreadTotal($catType, $userId, $groupId, $regTime);
            $redis->set($cacheKey, $total, 10);
        }

        return (int)$total;
    }

    public function getSystemUnreadTotal($userId, $groupId, $regTime)
    {
        $db = Db::name('system_message');
        $total = $db->where([
            ['create_time', 'egt', $regTime],
            ['delete_time', 'null'],
            ['group_id', 'eq', $groupId]
        ])->count();
        $redis = RedisClient::getInstance();
        $readNum = $redis->zCount("messages:read:push:{$groupId}:{$userId}", '-inf', '+inf');
        $total2 = Db::name('message')->where(array('user_id' => $userId, 'delete_time' => null, 'cat_type' => 'system', 'read_status' => '0'))->count();
        return ($total - $readNum) + ((int)$total2);
    }

    public function getRewardUnreadTotal($userId)
    {
        $total = Db::name('gift_log')->where(['scene' => 'video', 'to_uid' => $userId, 'msg_status' => '0'])->count();
        return (int)$total;
    }

    public function getLastMessage($catType, $userId, $regTime)
    {
        $get['cat_type'] = $catType;
        $get['user_id'] = $userId;
        $get['reg_time'] = $regTime;
        $get['_summary'] = '1';
        $list = $this->getList($get, 0, 1);
        return $list ? $list[0] : [];
    }

    //阅读单播消息
    public function read($userId, $id = null)
    {
        $where = array('user_id' => $userId, 'delete_time' => null, 'read_status' => '0');
        if (isset($id)) $where['id'] = $id;
        $num = Db::name('message')->where($where)->update(array(
            'read_status' => '1',
            'read_time' => time()
        ));
        return (int)$num;
    }

    //阅读广播消息
    public function readPush($userId, $groupId, $regTime, $id = null)
    {
        $where = [
            ['delete_time', 'null']
        ];
        $db = Db::name('system_message');
        if (isset($id)) {
            if (is_array($id)) {
                $where[] = ['id', 'in', $id];
            } else {
                $where[] = ['id', 'eq', $id];
            }
        } else {
            $where[] = ['create_time', 'egt', $regTime];
            $where[] = ['group_id', 'eq', $groupId];
        }
        $list = $db->field('id')->where($where)->select();
        $list = $list ? $list : [];
        $ids = self::getIdsByList($list, 'id');
        $ids = is_array($ids) ? $ids : [];
        $key = "messages:read:push:{$groupId}:{$userId}";
        $redis = RedisClient::getInstance();
        $readIds = $redis->zRange($key, 0, -1);
        $readIds = $readIds ? $readIds : [];
        $arr = array_diff($ids, $readIds);
        $total = 0;
        foreach ($arr as $unreadId) {
            if ($redis->zAdd($key, time(), $unreadId)) {
                $total++;
            }
        }
        return $total;
    }

    public function readGiftMsg($userId, $unGiftMsgIds = null)
    {
        $where = ['to_uid' => $userId, 'msg_status' => '0'];
        if (isset($unGiftMsgIds)) $where['id'] = $unGiftMsgIds;
        $num = Db::name('gift_log')->where($where)->update(array(
            'msg_status' => '1'
        ));
        return (int)$num;
    }

    public function getList($get = array(), $offset = 0, $length = 10)
    {
        $catType = $get['cat_type'];
        $userId = $get['user_id'];
        $groupId = 'all';
        if ($catType == 'system') {
            $list = $this->getSystemList($userId, $groupId, $get['reg_time'], $offset, $length);
        } else if ($catType == 'reward') {
            $list = $this->getRewardList($userId, $offset, $length);
        } else {
            $db = Db::name('message');
            $this->setWhere($db, $get)->setOrder($db, $get);
            $list = $db->limit($offset, $length)->select();
            $list = $list ? $list : [];
        }
        $unReadIds = [];
        $unReadPushIds = [];
        $unGiftMsgIds = [];
        $arr = $this->parseList($list, $unReadIds, $unReadPushIds, $unGiftMsgIds);
        if ($get['_summary'] != '1') {
            if (!empty($unReadIds)) {
                $this->read($userId, $unReadIds);
            }
            if (!empty($unReadPushIds)) {
                $this->readPush($userId, $groupId, $get['reg_time'], $unReadPushIds);
            }
            if (!empty($unGiftMsgIds)) {
                $this->readGiftMsg($userId, $unGiftMsgIds);
            }
        }
        return $arr;
    }

    protected function getRewardList($userId, $offset, $length)
    {
        $giftLogs = Db::name('gift_log')->order('create_time desc,id desc')
            ->where(['scene' => 'video', 'to_uid' => $userId])->limit($offset, $length)->select();
        $giftLogs = $giftLogs ? $giftLogs : [];
        $giftIds = [];
        $videoIds = [];
        foreach ($giftLogs as $giftLog) {
            $giftIds[] = $giftLog['gift_id'];
            $videoIds[] = $giftLog['video_id'];
        }
        $giftIds = array_unique($giftIds);
        $videoIds = array_unique($videoIds);
        $gifts = [];
        $videos = [];
        if (!empty($giftIds)) {
            $gifts = Db::name('gift')->whereIn('id', $giftIds)->select();
        }
        if (!empty($videoIds)) {
            $videos = Db::name('video')->whereIn('id', $videoIds)->select();
        }
        foreach ($giftLogs as &$giftLog) {
            $giftLog['send_uid'] = $giftLog['user_id'];
            $giftLog['cat_type'] = 'reward';
            $giftLog['type'] = 'reward_gift';
            $gift = Service::getItemByList($giftLog['gift_id'], $gifts, 'id');
            $video = Service::getItemByList($giftLog['video_id'], $videos, 'id');
            $gift = $gift ? $gift : [];
            $giftLog['gift_name'] = $gift['name'] ? $gift['name'] : '未知礼物';
            $giftLog['picture_url'] = $gift['picture_url'] ? $gift['picture_url'] : '';
            $giftLog['privileges'] = $gift['privileges'] ? $gift['privileges'] : '';
            $giftLog['gift_cid'] = isset($gift['gift_cid']) ? $gift['gift_cid'] : '';
            $giftLog['read_status'] = $giftLog['msg_status'] == '0' ? '0' : '1';
            $giftLog['cover_url'] = $video['cover_url'] ? $video['cover_url'] : '';
            $giftLog['user_id'] = $giftLog['to_uid'];
        }
        return $giftLogs;
    }

    //获取系统消息列表
    protected function getSystemList($userId, $groupId, $regTime, $offset, $length)
    {
        $redis = RedisClient::getInstance();
        //notice是单播的，push是一个组广播的
        $keys = ["messages:notice_sets:{$userId}", "messages:push_sets:{$groupId}"];
        $key = "messages:system_sets:{$userId}";
        $redis->zUnion($key, $keys, NULL, 'MAX');
        //还未注册前的所有消息都过滤掉
        $redis->zRemRangeByScore($key, 0, $regTime - 1);
        $ranks = $redis->zRevRange($key, $offset, $offset + $length - 1, true);
        $noticeIds = [];
        $pushIds = [];
        $noticeList = [];
        $pushList = [];
        foreach ($ranks as $member => $createTime) {
            list($memberType, $memberId) = explode(':', $member);
            if ($memberType == 'notice') {
                $noticeIds[] = $memberId;
            } else if ($memberType == 'push') {
                $pushIds[] = $memberId;
            }
        }
        if (!empty($noticeIds)) {
            $noticeList = Db::name('message')->where(['delete_time' => null])->whereIn('id', $noticeIds)->limit(count($noticeIds))->select();
        }
        if (!empty($pushIds)) {
            $pushList = Db::name('system_message')->where(['delete_time' => null])->whereIn('id', $pushIds)->limit(count($pushIds))->select();
        }
        $list = [];
        foreach ($ranks as $member => $createTime) {
            list($memberType, $memberId) = explode(':', $member);
            $item = [];
            if ($memberType == 'notice') {
                $item = self::getItemByList($memberId, $noticeList, 'id');
            } else if ($memberType == 'push') {
                $item = self::getItemByList($memberId, $pushList, 'id');
            }
            if ($item) {
                $item['user_id'] = $userId;
                $item['group_id'] = $groupId;
                $item['create_time'] = $createTime;
                $item['cat_type'] = 'system';
                $item['type'] = $memberType;
                $list[] = $item;
            }
        }
        return $list;
    }

    protected function parseList($list, &$unReadIds, &$unReadPushIds, &$unGiftMsgIds)
    {
        $arr = [];
        //发送用户
        $sendUids = self::getIdsByList($list, 'send_uid');
        $sendUsers = [];
        if (!empty($sendUids)) {
            $sendUsers = Db::name('user')->field('user_id,avatar,nickname,gender')->whereIn('user_id', $sendUids)->where(['delete_time' => null])->select();
        }
        foreach ($list as &$item) {
            $tmp = [];
            $tmp['id'] = $item['id'];
            $tmp['cat_type'] = $item['cat_type'];
            $tmp['type'] = $item['type'];
            //消息发送人
            if ($item['cat_type'] == 'system') {
                $tmp['send_uid'] = '10000';
                $tmp['send_avatar'] = img_url('', '', 'official_avatar');
                $tmp['send_nickname'] = APP_PREFIX_NAME . '小助手';
                if ($item['type'] == 'notice') {
                    $tmp['read_status'] = $item['read_status'];
                } else {
                    $redis = RedisClient::getInstance();
                    $readTime = (int)$redis->zScore("messages:read:push:{$item['group_id']}:{$item['user_id']}", $item['id']);
                    $tmp['read_status'] = $readTime > 0 ? '1' : '0';
                }
            } else {
                $tmp['send_uid'] = (string)$item['send_uid'];
                $tmp['send_avatar'] = $item['send_avatar'];
                $tmp['send_nickname'] = $item['send_nickname'];
                $sendUser = self::getItemByList($item['send_uid'], $sendUsers, 'user_id');
                if ($sendUser) {
                    $tmp['send_avatar'] = $sendUser['avatar'];
                    $tmp['send_nickname'] = $sendUser['nickname'];
                    $tmp['gender'] = $sendUser['gender'];
                }
                $tmp['read_status'] = $item['read_status'];
            }
            $tmp['create_time_s'] = (int)$item['create_time'];
            $tmp['time_before'] = time_before($item['create_time'], '前');
            $tmp['time_detail'] = time_detail($item['create_time']);
            $content = $item['content'] ? json_decode($item['content'], true) : [];
            $contentId = $item['content_id'] ? $item['content_id'] : '';
            switch ($item['type']) {
                case 'like_film':
                    $tmp['film_id'] = $contentId;
                    $filmInfo = Db::name('video')->field('`describe`,cover_url')->where(['id' => $contentId])->find();
                    $tmp['film_title'] = '视频已删除';
                    $tmp['cover_url'] = '';
                    if ($filmInfo) {
                        $tmp['film_title'] = $filmInfo['describe'];
                        $tmp['cover_url'] = $filmInfo['cover_url'] ? $filmInfo['cover_url'] : '';
                    }
                    $tmp['alt'] = $tmp['send_nickname'] . "赞了你的作品";
                    $tmp['at_alt'] = "赞了你的作品";
                    break;
                case 'like_comment':
                    $tmp['film_id'] = $content['film_id'];
                    $tmp['film_title'] = $content['film_title'];
                    $tmp['cover_url'] = $content['cover_url'];
                    $tmp['comment_id'] = $contentId;
                    $tmp['alt'] = $tmp['send_nickname'] . "赞了你的评论";
                    $tmp['at_alt'] = "赞了你的评论";
                    break;
                case 'comment':
                    $comment = Db::name('video_comment')->where(['id' => $contentId])->find();
                    $comment = $comment ? $comment : [];
                    $tmp['friend_group'] = $comment['friends'] ? json_decode($comment['friends'], true) : [];
                    $tmp['content'] = $comment['content'] ? $comment['content'] : '';
                    $tmp['is_reply'] = empty($comment['reply_id']) ? 0 : 1;
                    $tmp['film_id'] = $content['film_id'];
                    $tmp['film_title'] = $content['film_title'];
                    $tmp['cover_url'] = $content['cover_url'];
                    $tmp['comment_id'] = $contentId;
                    $tmp['alt'] = $tmp['send_nickname'] . "评论了你的作品";
                    $tmp['at_alt'] = "评论了你的作品";
                    break;
                case 'reply':
                    $comment = Db::name('video_comment')->where(['id' => $contentId])->find();
                    $comment = $comment ? $comment : [];
                    $tmp['friend_group'] = $comment['friends'] ? json_decode($comment['friends'], true) : [];
                    $tmp['content'] = $comment['content'] ? $comment['content'] : '';
                    $tmp['is_reply'] = empty($comment['reply_id']) ? 0 : 1;
                    $tmp['film_id'] = $content['film_id'];
                    $tmp['film_title'] = $content['film_title'];
                    $tmp['cover_url'] = $content['cover_url'];
                    $tmp['to_comment_id'] = $content['to_comment_id'];
                    $tmp['comment_id'] = $contentId;
                    $tmp['alt'] = $tmp['send_nickname'] . "回复了你的评论";
                    $tmp['at_alt'] = "回复了你的评论";
                    break;
                case 'follow':
                    $where = ['user_id' => $item['user_id'], 'follow_id' => $item['send_uid']];
                    $followInfo = Db::name('follow')->where($where)->limit(1)->find();
                    $tmp['is_follow'] = 0;
                    $tmp['follow_time'] = '';
                    if ($followInfo) {
                        $tmp['is_follow'] = 1;
                        $tmp['follow_time'] = date('Y-m-d', $followInfo['create_time']);
                    }
                    $tmp['alt'] = $tmp['send_nickname'] . "关注了你";
                    $tmp['at_alt'] = "关注了你";
                    break;
                case 'at_publish_film':
                    $tmp['film_id'] = $contentId;
                    $filmInfo = Db::name('video')->field('describe,cover_url')->where(['id' => $contentId])->find();
                    $tmp['film_title'] = '视频已删除';
                    $tmp['cover_url'] = '';
                    if ($filmInfo) {
                        $tmp['film_title'] = $filmInfo['describe'];
                        $tmp['cover_url'] = $filmInfo['cover_url'] ? $filmInfo['cover_url'] : '';
                    }
                    $tmp['alt'] = $tmp['send_nickname'] . "在发布新作品时@了你";
                    $tmp['at_alt'] = "在发布新作品时提到了你";
                    break;
                case 'at_gift_reply':
                    $log = Db::name('gift_log')->where(['id' => $contentId])->find();
                    $filmInfo = Db::name('video')->field('describe,cover_url')->where(['id' => $log['video_id']])->find();
                    $tmp['film_title'] = '视频已删除';
                    $tmp['film_id'] = $log['video_id'];
                    $tmp['cover_url'] = '';
                    if ($filmInfo) {
                        $tmp['film_title'] = $filmInfo['describe'];
                        $tmp['cover_url'] = $filmInfo['cover_url'] ? $filmInfo['cover_url'] : '';
                    }
                    $tmp['alt'] = $tmp['send_nickname'] . "在回复时@了你";
                    $tmp['alt'] = $log['reply_msg'] ? $log['reply_msg'] : $tmp['alt'];
                    $tmp['at_alt'] = "在回复消息中提到了你";
                    break;
                case 'at_comment':
                    $comment = Db::name('video_comment')->where(['id' => $contentId])->find();
                    $comment = $comment ? $comment : [];
                    $tmp['film_title'] = '视频已删除';
                    $tmp['film_id'] = $comment['video_id'] ? $comment['video_id'] : 0;
                    $tmp['cover_url'] = '';
                    $tmp['friend_group'] = $comment['friends'] ? json_decode($comment['friends'], true) : [];
                    if ($comment['video_id']) {
                        $filmInfo = Db::name('video')->field('describe,cover_url')->where(['id' => $comment['video_id']])->find();
                        if ($filmInfo) {
                            $tmp['film_title'] = $filmInfo['describe'];
                            $tmp['cover_url'] = $filmInfo['cover_url'] ? $filmInfo['cover_url'] : '';
                        }
                    }
                    $tmp['alt'] = $comment['content'] ? $comment['content'] : '';
                    $tmp['at_alt'] = "在视频评论中提到了你";
                    break;
                case 'push':
                    $tmp['msg_title'] = $item['title'] ? $item['title'] : '系统通知';
                    $tmp['url'] = $item['url'] ? $item['url'] : H5_URL . "/messages/push_detail/id/{$item['id']}";
                    $tmp['summary'] = $item['summary'] ? $item['summary'] : '查看详情';
                    $tmp['alt'] = $tmp['msg_title'];
                    break;
                case 'notice':
                    $tmp['msg_title'] = $item['title'] ? $item['title'] : '系统通知';
                    $tmp['url'] = $content['url'] ? $content['url'] : H5_URL . "/messages/notice_detail/id/{$item['id']}";
                    $tmp['summary'] = $item['summary'] ? $item['summary'] : '查看详情';
                    $tmp['alt'] = $tmp['msg_title'];
                    break;
                case 'reward_gift':
                    $tmp['gift_id'] = $item['gift_id'];
                    $tmp['gift_cid'] = $item['gift_cid'];
                    $tmp['gift_name'] = $item['gift_name'];
                    $tmp['picture_url'] = $item['picture_url'];
                    $tmp['privileges'] = $item['privileges'];
                    $tmp['msg_status'] = $item['msg_status'];
                    $tmp['leave_msg'] = $item['leave_msg'] ? $item['leave_msg'] : '';
                    $tmp['reply_msg'] = $item['reply_msg'] ? $item['reply_msg'] : '';
                    $tmp['alt'] = "收到礼物{$item['gift_name']}";
                    $tmp['cover_url'] = $item['cover_url'];
                    $tmp['video_id'] = $item['video_id'] ? (int)$item['video_id'] : 0;
                    $tmp['price'] = (string)$item['price'];
                    break;
                case 'comment_friend_msg':
                    $comment = Db::name('friend_circle_comment')->where(['id' => $contentId])->find();
                    $msgDetail = Db::name('friend_circle_message')->where(['id' => $comment['fcmid']])->find();
                    $comment = $comment ? $comment : [];
                    $tmp['is_reply'] = empty($comment['reply_id']) ? 0 : 1;
                    $tmp['fcmid'] = $comment['fcmid'];
                    $tmp['content'] = $comment['content'] ? $comment['content'] : '';
                    $tmp['cover_url'] = explode(',', trim($comment['imgs']))[0];
                    $tmp['comment_id'] = $contentId;
                    $tmp['alt'] = $tmp['send_nickname'] . "评论了你的作品";
                    $tmp['at_alt'] = "评论了你的作品";
                    $tmp['msg_img'] = explode(',', trim($msgDetail['picture']))[0];
                    $tmp['msg_video'] = $msgDetail['video'] ? $msgDetail['video'] : '';
                    $tmp['msg_voice'] = $msgDetail['voice'] ? $msgDetail['voice'] : '';
                    $tmp['msg_content'] = $msgDetail['content'] ? emoji_decode($msgDetail['content']) : '';
                    $tmp['msg_cover_url'] = $msgDetail['cover_url'] ? $msgDetail['cover_url'] : '';
                    break;
                case 'comment_friend_msg_comment':
                    $comment = Db::name('friend_circle_comment_evaluate')->where(['id' => $contentId])->find();
                    $comment = $comment ? $comment : [];
                    $tmp['is_reply'] = empty($comment['reply_id']) ? 0 : 1;
                    $tmp['comment_id'] = $comment['commentid'];
                    $tmp['content'] = $comment['content'] ? $comment['content'] : '';
                    $tmp['cover_url'] = $comment['cover_url'] ? $comment['cover_url'] : '';
                    $tmp['eval_id'] = $contentId;
                    $tmp['alt'] = $tmp['send_nickname'] . "评论了你的评论";
                    $tmp['at_alt'] = "评论了你的评论";
                    break;
                case 'at_friend_push_dynamic':
                    $msgtimeline = Db::name('friend_circle_timeline')->where(['id' => $contentId])->find();
                    $comment = Db::name('friend_circle_message')->where(['id' => $msgtimeline['fcmid']])->find();
                    $comment = $comment ? $comment : [];
                    $tmp['is_reply'] = empty($comment['reply_id']) ? 0 : 1;
                    $tmp['comment_id'] = $comment['id'];
                    $tmp['content'] = $comment['dynamic_title'];
                    $tmp['cover_url'] = $comment['cover_url'] ? $comment['cover_url'] : '';
                    $tmp['fcmid'] = $comment['id'];
                    $tmp['alt'] = $tmp['send_nickname'] . "发布动态@了你";
                    $tmp['at_alt'] = "发布动态@了你";
                    $tmp['msg_img'] = explode(',', trim($comment['picture']))[0];
                    $tmp['msg_video'] = $comment['video'] ? $comment['video'] : '';
                    $tmp['msg_voice'] = $comment['voice'] ? $comment['voice'] : '';
                    $tmp['msg_content'] = $comment['content'] ? emoji_decode($comment['content']) : '';
                    $tmp['msg_cover_url'] = $comment['cover_url'] ? $comment['cover_url'] : '';
                    break;

                case 'like_friend_msg':
                    $msgcomment = Db::name('friend_circle_message_live')->where(['id' => $contentId])->find();
                    $comment = Db::name('friend_circle_message')->where(['id' => $msgcomment['fcmid']])->find();
                    $comment = $comment ? $comment : [];
                    $tmp['is_reply'] = empty($comment['reply_id']) ? 0 : 1;
                    $tmp['comment_id'] = $comment['id'];
                    $tmp['content'] = $comment['dynamic_title'];
                    $tmp['cover_url'] = $comment['cover_url'] ? $comment['cover_url'] : '';
                    $tmp['fcmid'] = $comment['id'];
                    $tmp['alt'] = $tmp['send_nickname'] . "对你的动态点赞";
                    $tmp['at_alt'] = "对你的动态点赞";
                    $tmp['msg_img'] = explode(',', trim($comment['picture']))[0];
                    $tmp['msg_video'] = $comment['video'] ? $comment['video'] : '';
                    $tmp['msg_voice'] = $comment['voice'] ? $comment['voice'] : '';
                    $tmp['msg_content'] = $comment['content'] ? emoji_decode($comment['content']) : '';
                    $tmp['msg_cover_url'] = $comment['cover_url'] ? $comment['cover_url'] : '';
                    break;

                default:
                    $tmp['msg_title'] = $item['title'] ? $item['title'] : '系统通知';
                    $tmp['url'] = H5_URL . "/messages/notice_detail/id/{$item['id']}";
                    $tmp['summary'] = $item['summary'] ? $item['summary'] : '查看详情';
                    $tmp['alt'] = $tmp['msg_title'];
                    break;

            }
            $tmp['content'] = isset($tmp['content']) ? emoji_decode($tmp['content']) : '';

            $tmp['msg_img'] = isset($tmp['msg_img']) ? $tmp['msg_img'] : '';
            $tmp['msg_video'] = isset($tmp['msg_video']) ? $tmp['msg_video'] : '';
            $tmp['msg_voice'] = isset($tmp['msg_voice']) ? $tmp['msg_voice'] : '';;
            $tmp['msg_content'] = isset($tmp['msg_content']) ? $tmp['msg_content'] : '';
            $tmp['msg_cover_url'] = isset($tmp['msg_cover_url']) ? $tmp['msg_cover_url'] : '';
            if ($tmp['read_status'] == '0') {
                if ($tmp['type'] == 'push') {
                    $unReadPushIds[] = $tmp['id'];
                } elseif ($tmp['type'] == 'reward_gift') {
                    $unGiftMsgIds[] = $tmp['id'];
                } else {
                    $unReadIds[] = $tmp['id'];
                }
            }
            $tmp['content_msg'] = $content ? $content : (object)[];
            $arr[] = $tmp;
        }
        return $arr;
    }


    protected function setWhere(&$db, $get)
    {
        $where = array('user_id' => $get['user_id'], 'delete_time' => null);
        if ($get['cat_type'] != '') $where['cat_type'] = $get['cat_type'];
        $db->where($where);
        if (isset($get['type']) && $get['type'] != '') {
            $type = explode(",", $get['type']);
            $where1[] = ['type', 'in', $type];
            $db->where($where1);
        }
        return $this;
    }

    protected function setOrder(&$db, $get)
    {
        if (empty($get['sort'])) {
            $db->order('create_time desc,id desc');
        }
        return $this;
    }
}
<?php


namespace app\api\service\comment;

use app\api\service\Comment;
use bxkj_common\KeywordCheck;
use bxkj_module\service\User;
use think\Db;
use bxkj_common\RabbitMqChannel;

class Publish extends Comment
{
    //添加评论
    public function addComment(array $params)
    {
        $reply_count = 0;

        $params['like_count'] = $params['reply_count'] = '0';

        $video_info = Db::name('video')->where(['id'=>$params['video_id']])->field('user_id, comment_sum')->find();

        if (empty($video_info)) return $this->setError('视频已删除');

        //用户黑名单检查
        if ($this->redis->zScore('blacklist:' . $video_info['user_id'], USERID)) return $this->setError('作者已关闭评论功能');

        //格式化图片资源
        if( !empty($params['pictures']) )
        {
            $pics = explode(',',$params['pictures']);
            $params['pictures'] = $this->pictureFormat($pics);
        }

        //敏感词验证
        $KeywordCheck = new KeywordCheck();
        $sensitive_word = $KeywordCheck->isLegal($params['content']);

        if (!$sensitive_word)
        {
            $params['is_sensitive'] = 1;
            //对接rabbitMQ
            $rabbitChannel = new RabbitMqChannel(['user.credit']);
            $rabbitChannel->exchange('main')->sendOnce('user.credit.illegal_comment', ['user_id' => USERID, 'keyword'=>$params['content']]);
        }

        $mq_friend = $params['friends'];

        if (!empty($params['friends']))
        {
            $friends = explode(',', $params['friends']);

            $user = new User();

            $friend_info = $user->getUsers(array_unique($friends),null,'user_id, nickname');

            $params['friends'] = json_encode($friend_info);
        }

        if(!empty($params['reply_id']))
        {

            $master_comment = Db::name('video_comment')->where(['id'=>$params['reply_id']])->find();

            if (empty($master_comment)) return $this->setError('回复评论出错,无此评论');

            $params['master_id'] = $master_comment['master_id'] == 0 ? $master_comment['id'] : $master_comment['master_id'];

            $params['master_uid'] = $master_comment['master_id'] == 0 ? $master_comment['user_id'] : $master_comment['master_uid'];

            $params['reply_uid'] = $master_comment['user_id'];

            $reply_count = ++$master_comment['reply_count'];
        }

        $params['is_anchor'] = (int)($video_info['user_id'] == USERID);

        $data = $params;

        unset($data['credit_score'], $data['avatar'], $data['nickname']);

        $insert_id = Db::name('video_comment')->insertGetId($data);

        if ($insert_id === false) return $this->setError('写入评论错误');

        $type = empty($params['reply_id']) ? 'comment' : 'reply';

        //发送评论和回复事件
        $rabbitChannel = new RabbitMqChannel(['user.behavior']);

        $rabbitChannel->exchange('main')->send('user.behavior.'.$type, [
            'id' => $insert_id,
            'is_sensitive' => $params['is_sensitive']
        ]);

        // @好友事件
        if (!empty($params['friends']))
        {
            $rabbitChannel->exchange('main')->send('user.behavior.at_friend', [
                'friend_uids' => $mq_friend,
                'scene' => 'comment',
                'comment_id' => $insert_id,
                'user_id' => USERID
            ]);
        }

        $rabbitChannel->close();

        $video_info['comment_sum']++;

        // 格式化视频评论数量
        $this->formatData($video_info['comment_sum']);

        $params['id'] = $insert_id;
        $res = [$params];

        $this->initializeComment($res);

        return ['video_comment_count' => $video_info['comment_sum'], 'reply_comment_count' => $reply_count, 'now_comment_data'=>$res[0]];
    }
}
<?php
/**
 * Created by PhpStorm.
 * User: “崔鹏
 * Date: 2020/06/19
 * Time: 上午 9:29
 */

namespace app\api\controller\friend;

use app\admin\service\SysConfig;
use app\common\controller\UserController;
use app\friend\service\FriendCircleComment;
use app\friend\service\FriendCircleCommentEvaluate;
use app\friend\service\FriendCircleCommentEvaluateLive;
use app\friend\service\FriendCircleCommentLive;
use app\friend\service\FriendCircleMessage;
use bxkj_common\RedisClient;
use bxkj_module\exception\ApiException;

class Comment extends UserController
{
    public function __construct()
    {
        parent::__construct();
        $redis       = new RedisClient();
        $cacheFriend = $redis->exists('cache:friend_config');
        if (empty($cacheFriend)) {
            $arr  = [];
            $ser  = new SysConfig();
            $info = $ser->getConfig("friend");
            if (empty($info)) return [];
            $redis->setex('cache:friend_config', 4 * 3600, $info['value']);
        }
        $friendConfigRes       = $redis->get('cache:friend_config');
        $this->friendConfigRes = json_decode($friendConfigRes, true);
        if ($this->friendConfigRes['is_open'] == 0) {
            $errorMsg = '未开启交友功能';
            if (!empty($errorMsg)) {
                throw new ApiException((string)$errorMsg, 1);
            }
        }
    }

    /**
     * 对消息进行评论功能
     * @return \think\response\Json
     */
    public function CommentMsg()
    {
        $submit = submit_verify('friendsub' . USERID);
        if (!$submit) return $this->jsonError('您提交太频繁了~~~');
        $userId   = USERID;
        $params   = request()->param();
        $validate = new \app\api\validate\Comment();
        $result   = $validate->scene('CommentMsg')->check($params);
        if (true !== $result) {
            return $this->jsonError($validate->getError());
        }
        $params['uid'] = $userId;
        $friendComment = new FriendCircleComment();
        if ($this->friendConfigRes['msg_commment_examine'] == 1) {
            $params['status'] = 0;
        } else {
            $params['status'] = 1;
        }
        $fcnum = $friendComment->countTotal(['uid' => USERID, 'fcmid' => $params['fcmid']]);
        if ($fcnum + 1 > $this->friendConfigRes['comment_total_num']) return $this->jsonError('最多只能发'.$fcnum.'条');
        $rest = $friendComment->add($params);
         finish_task(USERID,'commentDynamic',1,0);
        if (!$rest) return $this->jsonError('操作失败');
        return $this->success($rest, '发布成功');
    }

    /**
     * 对评论进行点赞
     * @return \think\response\Json
     */
    public function commentLive()
    {
        $userId = USERID;
        $submit = submit_verify('commentLive' . USERID);
        if (!$submit) return $this->jsonError('您提交太频繁了~~~');
        $params   = request()->param();
        $validate = new \app\api\validate\Comment();
        $result   = $validate->scene('commentLive')->check($params);
        if (true !== $result) {
            return $this->jsonError($validate->getError());
        }
//        $comment = new FriendCircleComment();
//        $commentdetail = $comment->find(['id'=>$params['commentid']]);
//        if($userId==$commentdetail['uid']){
//            return $this->jsonError('您不能点赞自己~~~');
//        }
        $params['uid'] = $userId;
        $commentLive   = new FriendCircleCommentLive();
        $rest          = $commentLive->commentlive($params);
        if (!$rest) return $this->jsonError('操作失败');
        $redis = new RedisClient();
        $data  = $redis->get('usercomment_live:' . $userId);
        return $this->success($data, '点赞成功');
    }

    /**
     * 获取信息的评论列表
     * @return \think\response\Json
     */
    public function commentList()
    {
        $userId   = USERID;
        $params   = request()->param();
        $validate = new \app\api\validate\Comment();
        $result   = $validate->scene('commentList')->check($params);
        if (true !== $result) {
            return $this->jsonError($validate->getError());
        }
        $page_index    = $params['page_index'] ? $params['page_index'] : 1;
        $page_size     = $params['page_size'] ? $params['page_size'] : 10;
        $msgComment    = new FriendCircleComment();
        $rest          = $msgComment->pageQuery($page_index, $page_size, ['fcmid' => $params['fcmid'],'status'=>1], 'id desc', '*');
        $queryUserLive = new FriendCircleCommentLive();
        if (!empty($rest['data'])) {
            foreach ($rest['data'] as $k => $v) {
                $rest['data'][$k]['userdetail'] = userMsg($v['uid'], 'user_id,nickname,avatar');
                //如果有自己，自己是否点过赞
                if ($v['uid'] == $userId) {
                    $userLive = $queryUserLive->queryUserLive(['uid' => $v['uid'], 'id' => $v['id']]);
                    if ($userLive['status'] == 1) {
                        $rest['data'][$k]['userlivecheck'] = 1;
                    } else {
                        $rest['data'][$k]['userlivecheck'] = 0;
                    }

                } else {
                    $rest['userlivecheck'] = 0;
                }
                $rest['data'][$k]['create_time'] = time_before($v['create_time'], '前');
                $rest['data'][$k]['smallimgs'] = actPicture($v['imgs'],1);
                //添加该条评论的留言列表
                $evaluste              = new FriendCircleCommentEvaluate();
                $content   =  $evaluste->getTotal(['commentid' => $v['id']]);
                $rest['data'][$k]['total'] = $content;
                $restly                = $evaluste->getQueryNum(['commentid' => $v['id']], '*', 'id desc',3);
                $rest['data'][$k]['Evaluate'] = $restly;

            }

        }
        return $this->success($rest, '查询成功');
    }

    /**
     * 获取信息的评论详情
     * @return \think\response\Json
     */
    public function commentDetail()
    {
        $params   = request()->param();
        $validate = new \app\api\validate\Comment();
        $result   = $validate->scene('commentDetail')->check($params);
        if (true !== $result) {
            return $this->jsonError($validate->getError());
        }
        $msgComment         = new FriendCircleComment();
        $rest               = $msgComment->getQuery(['id' => $params['commentid']], '*', 'id')[0];
        $rest['userdetail'] = userMsg($rest['uid'], 'user_id,nickname,avatar');
        //如果有自己，自己是否点过赞
        $queryUserLive = new FriendCircleCommentLive();
        if ($rest['uid'] == USERID) {
            $userLive = $queryUserLive->queryUserLive(['uid' => $rest['uid'], 'id' => $params['commentid']]);
            if ($userLive['status'] == 1) {
                $rest['userlivecheck'] = 1;
            } else {
                $rest['userlivecheck'] = 0;
            }
        } else {
            $rest['userlivecheck'] = 0;
        }
        $rest['smallimgs'] = actPicture($rest['imgs'],1);
        $evaluste              = new FriendCircleCommentEvaluate();
        $restly                = $evaluste->getQueryNum(['commentid' => $params['commentid']], '*', 'id desc',100);
        $rest['Evaluate'] = $restly;
        return $this->success($rest, '查询成功');
    }

    /**
     * 删除评论
     * @return \think\response\Json
     */
    public function commentdel(){
        $userId = USERID;
        $submit = submit_verify('commentLive' . USERID);
        if (!$submit) return $this->jsonError('您提交太频繁了~~~');
        $params   = request()->param();
        $validate = new \app\api\validate\Comment();
        $result   = $validate->scene('commentLive')->check($params);
        if (true !== $result) {
            return $this->jsonError($validate->getError());
        }
        $params['uid'] = $userId;
        $comment   = new FriendCircleComment();
        //判断用户权限
        $commentdetail = $comment->find(['id'=>$params['commentid']],'id desc');
        $actArray = [];
        array_push($actArray,$commentdetail['uid']);
        //这里暂时屏蔽
//        $fcmid = $commentdetail['fcmid'];
//        $circleMsg = new FriendCircleMessage();
//        $msgDeatil = $circleMsg->find(['id'=>$fcmid ]);
//         array_push($actArray,$msgDeatil['uid']);

        if(!in_array($userId,$actArray)){
            return $this->jsonError('您没有操作权限');
        };
        $ids = [];
         array_push($ids,$params['commentid']);

        $rest          = $comment->del($params);
        if (!$rest) return $this->jsonError('操作失败');
        return $this->success($rest, '删除成功');
    }
}
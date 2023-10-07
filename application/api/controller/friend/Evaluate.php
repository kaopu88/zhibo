<?php
/**
 * Created by PhpStorm.
 * User: “崔鹏
 * Date: 2020/06/20
 * Time: 上午 8:50
 */

namespace app\api\controller\friend;

use app\admin\service\SysConfig;
use app\common\controller\UserController;
use app\friend\service\FriendCircleComment;
use app\friend\service\FriendCircleCommentEvaluate;
use app\friend\service\FriendCircleCommentEvaluateLive;
use app\friend\service\FriendCircleTimelin;
use bxkj_common\RedisClient;
use bxkj_module\exception\ApiException;

class Evaluate extends UserController
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
     * 对评论进行留言
     * @return \think\response\Json
     */
    public function evaluateMsg()
    {
        $submit = submit_verify('evaluate' . USERID);
        if (!$submit) return $this->jsonError('您提交太频繁了~~~');
        $userId   = USERID;
        $params   = request()->param();
        $validate = new \app\api\validate\Evaluate();
        $result   = $validate->scene('evaluateMsg')->check($params);
        if (true !== $result) {
            return $this->jsonError($validate->getError());
        }

        $commentEvaluate = new FriendCircleCommentEvaluate();
        $comment         = new FriendCircleComment();
        $res             = $comment->countTotal(['id' => $params['commentid']]);
        if (!$res) {
            return $this->jsonError('评论不存在');
        }
        $evnum = $commentEvaluate->countTotal(['uid' => USERID, 'commentid' => $params['commentid']]);
        if ($evnum + 1 > $this->friendConfigRes['comment_evaluate_total_num']) return $this->jsonError('最多只能发'.$evnum.'条');

        if ($this->friendConfigRes['msg_commment_evaluate_examine'] == 1) {
            $params['status'] = 0;
        } else {
            $params['status'] = 1;
        }
        $params['uid']   = $userId;
        $rest = $commentEvaluate->add($params);
        if (!$rest) return $this->jsonError('操作失败');
        return $this->success($rest, '发布成功');
    }

    /**
     * 对评论留言点赞
     * @return \think\response\Json
     */
    public function evaluateLive()
    {
        $submit = submit_verify('evaluateLive' . USERID);
        if (!$submit) return $this->jsonError('您提交太频繁了~~~');
        $userId   = USERID;
        $params   = request()->param();
        $validate = new \app\api\validate\Evaluate();
        $result   = $validate->scene('evaluateLive')->check($params);
        if (true !== $result) {
            return $this->jsonError($validate->getError());
        }
        $params['uid']       = $userId;
        $commentEvaluateLive = new FriendCircleCommentEvaluateLive();
        $rest                = $commentEvaluateLive->Evaluatelive($params);
        if (!$rest) return $this->jsonError('操作失败');
        $redis = new RedisClient();
        $data  = $redis->get('usercommentevaluate_live:' . $userId);
        return $this->success($data, '点赞成功');
    }

    /**
     * 获取信息的评论留言列表
     * @return \think\response\Json
     */
    public function evaluateList()
    {
        $userId   = USERID;
        $params   = request()->param();
        $validate = new \app\api\validate\Evaluate();
        $result   = $validate->scene('evaluateList')->check($params);
        if (true !== $result) {
            return $this->jsonError($validate->getError());
        }
        $page_index            = $params['page_index'] ? $params['page_index'] : 1;
        $page_size             = $params['page_size'] ? $params['page_size'] : 10;
        $evaluste              = new FriendCircleCommentEvaluate();
        $rest                  = $evaluste->pageQuery($page_index, $page_size, ['commentid' => $params['commentid'],'status'=>1], 'id desc', '*');
        $queryUserEvaluateLive = new FriendCircleCommentEvaluateLive();
        if (!empty($rest['data'])) {
            foreach ($rest['data'] as $k => $v) {
                $rest['data'][$k]['userdetail'] = userMsg($v['uid'], 'user_id,nickname,avatar');
                if ($v['touid']) {
                    $rest['data'][$k]['touserdetail'] = userMsg($v['touid'], 'user_id,nickname,avatar');
                } else {
                    $rest['data'][$k]['touserdetail'] = [];
                }
                $rest['data'][$k]['create_time'] =  time_before($rest['data'][$k]['create_time'], '前') ;
            }
        }
        return $this->success($rest, '查询成功');
    }

    /**
     * 删除留言
     * @return \think\response\Json
     */
    public function evaluateDel(){
        $submit = submit_verify('evaluateDel' . USERID);
        if (!$submit) return $this->jsonError('您提交太频繁了~~~');
        $userId   = USERID;
        $params   = request()->param();
        $validate = new \app\api\validate\Evaluate();
        $result   = $validate->scene('evaluateLive')->check($params);
        if (true !== $result) {
            return $this->jsonError($validate->getError());
        }
        $params['uid']       = $userId;
        $commentEvaluste = new FriendCircleCommentEvaluate();
        $commentEvalusteDeatil = $commentEvaluste->find(['id'=>$params['commentmsgid']],'id desc');
        $actArray = [];

        array_push($actArray,$commentEvalusteDeatil['uid']);
        $comment = new FriendCircleComment();
        $commentDetail = $comment->find(['id'=>$commentEvalusteDeatil['commentid']],'id desc');
        array_push($actArray,$commentDetail['uid']);
//        if(!in_array($userId,$actArray)){
//            return $this->jsonError('您没有操作权限');
//        };
        $ids = [];
        array_push($ids,$params['commentmsgid']);
        $commentEvaluste = new FriendCircleCommentEvaluate();
        $del  = $commentEvaluste->del($ids);
        if (!$del) return $this->jsonError('操作失败');
        return $this->success($del, '删除成功');
    }
}
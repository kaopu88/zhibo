<?php


namespace app\api\controller;

use app\common\controller\UserController;
use app\common\service\Validate;
use app\api\service\comment\Del;
use app\api\service\comment\Like;
use app\api\service\Region;
use app\api\service\comment\Publish;
use app\api\service\comment\Lists;
use bxkj_module\service\Task;
use bxkj_module\service\User;

class Comment extends UserController
{
    //发表评论
    public function comment()
    {
        $params = request()->param();

        $params = [
            'video_id'=>$params['video_id'],
            'content'=>$params['content'],
            'reply_id'=> (int)$params['reply_id'],
            'lng'=>$params['lng'],
            'lat'=>$params['lat'],
            'pictures'=>$params['pictures'],
            'friends' => $params['friends'],
            'create_time' => time(),
            'user_id' => USERID,
        ];

        $userModel = new User();
        $user = $userModel->getUser(USERID);

        $params['credit_score'] = $user['credit_score'];
        $params['avatar'] = $user['avatar'];
        $params['nickname'] = $user['nickname'];

        $rules  = [
            'credit_score' => ['rule' => 'lt:60', 'error_msg' => '帐户受限，暂时无法评论~'],
            'content'=> ['rule' => 'notEmpty|str_gt:500', 'error_msg' => 'notEmpty:内容不能为空|str_gt:内容不能超过{$str_gt}个字符']
        ];

        $validate = new Validate();

        $rs = $validate->check($params, $rules);

        if ($rs)
        {
            $errors = $validate->getError();
            $error = array_shift($errors);

            return $this->jsonError($error['error_msg'], $error['error_code']);
        }

        $address = get_position_Lng_lat($params['lng'], $params['lat'], 'base');

        $Region = new Region();

        if (!empty($address['regeocode']['addressComponent']))
        {
            $position = $address['regeocode']['addressComponent'];

            $city = $Region->likeRegion($position['city'], 2);

            $districts = $Region->likeRegion($position['district'], 3);

            $params['city_id'] = $city['id'];

            $params['district_id'] = $districts['id'];
        }

        $res = (new Publish())->addComment($params);

        if (!$res) return $this->jsonError('发布错误');
        //视频评论任务
        $taskMod = new Task();
        $data = [
            'user_id' => $params['user_id'],
            'task_type' => 'commentVideo',
            'task_value' => 1,
            'status' => 0
        ];
        $taskMod->subTask($data);
        return $this->success($res,'评论成功');
    }


    //获取评论列表数据
    public function commentList()
    {
        $params = request()->param();

        $video_id = (int)$params['video_id'];//项目id

        $last_id = $params['last_id'];

        $offset = isset($params['offset']) ? $params['offset'] : 0;
        $length = isset($params['length']) ? ($params['length'] > 100 ? 100 : $params['length']) : PAGE_LIMIT;

        $selected_id = (int)$params['selected_id'];

        $Comment = new Lists();

        $res = $Comment->commentList($video_id, $offset, $length, $last_id, 0, $selected_id);

        return $this->success($res,'获取成功');
    }


    //获取子评论数据
    public function subCommentList()
    {
        $params = request()->param();

        $comment_id = (int)$params['comment_id'];

        $video_id = (int)$params['video_id'];

        $last_id = $params['last_id'];

        $offset = isset($params['offset']) ? $params['offset'] : 0;
        $length = isset($params['length']) ? ($params['length'] > 100 ? 100 : $params['length']) : PAGE_LIMIT;

        $Comment = new Lists();

        $res = $Comment->commentList($video_id, $offset, $length, $last_id, $comment_id);

        return $this->success($res,'获取成功');
    }

    //点赞评论
    public function like()
    {
        $params = request()->param();

        $comment_id = (int)$params['comment_id'];

        $Like = new Like();

        $res = $Like->isLike($comment_id) ? $Like->unLike($comment_id) : $Like->like($comment_id);

        if (!$res) return $this->jsonError($Like->getError());

        $msg = $res['status'] == 1 ? '点赞成功' : '已取消点赞';

        return $this->success($res,$msg);
    }


    //删除评论
    public function delete()
    {
        $params = request()->param();

        $comment_id = (int)$params['comment_id'];

        $Del = new Del();

        $res = $Del->delete($comment_id, USERID);

        if (!$res) return $this->jsonError($Del->getError());

        return $this->success($res,'删除成功');
    }
}
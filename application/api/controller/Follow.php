<?php

namespace app\api\controller;

use app\common\controller\UserController;
use app\api\service\Follow AS FollowModel;
use app\api\service\comment\Lists;
use app\api\service\Video;
use bxkj_common\CoreSdk;
use bxkj_common\RedisClient;
use bxkj_module\service\User;
use think\Db;
use think\facade\Request;
use bxkj_common\Prophet;


class Follow extends UserController
{

    /**
     * 关注他人
     *
     * @return \think\response\Json
     */
    public function follow()
    {
        $params = request()->param();

        $user_id = $params['user_id'];

        $is_return = isset($params['is_return']) ? $params['is_return'] :0;

        $exists = isset($params['exists']) ? $params['exists'] : [];

        if ($user_id == USERID) return $this->jsonError('无法关注自己');

        if (empty($user_id)) return $this->jsonError('传参有误');

        $followModel = new FollowModel();

        $res = $followModel->isFollow($user_id) ? $followModel->unFollow($user_id) : $followModel->addFollow($user_id, $is_return, $exists);

        if (!$res) return $this->jsonError($followModel->getError());

        $msg = $res['status'] == 1 ? '关注成功' : '已取消关注';

        return $this->success($res, $msg);
    }


    /**
     * 关注
     *
     * @return \think\response\Json
     */
    public function addFollow()
    {
        $params = request()->param();

        $user_id = $params['user_id'];

        if ($user_id == USERID) return $this->jsonError('无法关注自己');

        $followModel = new FollowModel();

        $res = $followModel->isFollow($user_id) ? ['status' => 1] : $followModel->addFollow($user_id);

        if (!$res) return $this->jsonError('操作异常');

        return $this->success($res, '已关注');
    }


    /**
     * 取关
     *
     * @return \think\response\Json
     */
    public function unFollow()
    {
        $params = request()->param();

        $user_id = $params['user_id'];

        $followModel = new FollowModel();

        $res = $followModel->isFollow($user_id) ? $followModel->unFollow($user_id) : ['status' => 0];

        if (!$res) return $this->jsonError('操作异常');

        return $this->success($res, '已取消关注');
    }


    /**
     * 粉丝列表
     *
     * @return \think\response\Json
     */
    public function fansList()
    {
        $params = request()->param();

        $offset = empty($params['offset']) ? 0 : $params['offset'];

        $followModel = new FollowModel();

        $res = $followModel->fansList($params['user_id'], $offset);

        return $this->success($res, '获取成功');
    }


    /**
     * 关注列表
     *
     * @return \think\response\Json
     */
    public function followList()
    {
        $params = request()->param();

        $offset = $params['offset'] ? $params['offset'] : 0;

        $length = $params['length'] ? $params['length'] : 10;

        $followModel = new FollowModel();

        $res = $followModel->followList($params['user_id'], $offset, $length);

        return $this->success($res, '获取成功');
    }


    /**
     * 当前关注列表
     *
     * @return \think\response\Json
     */
    public function currentFollow()
    {
        $params = request()->param();

        $offset = isset($params['offset']) ? $params['offset'] : 0;

        $length = isset($params['length']) ? $params['length'] : 0;

        $redis = RedisClient::getInstance();

        $totals = $redis->zcard('follow:' . USERID);

        $res = [];

        $followModel = new FollowModel();

        if (!empty(($totals - 1))) $res = $followModel->currentFollow(USERID, $offset, $length);

        return $this->success(['totals' => (int)$totals - 1, 'list' => $res], '获取成功');
    }


    /**
     * 推荐关注列表
     *
     * @return \think\response\Json
     */
    public function recommend()
    {
        $params = request()->param();

        $offset = isset($params['offset']) ? $params['offset'] : 0;

        $length = isset($params['length']) ? $params['length'] : 3;

        $followModel = new FollowModel();

        $res = $followModel->recommend($offset, $length);

        return $this->success($res, '获取成功');
    }


    /**
     * 最新发布
     *
     * @return \think\response\Json
     */
    public function newPublish()
    {
        $params = request()->param();

        $offset = $params['offset'] ? $params['offset'] : 0;

        $length = $params['length'] ? $params['length'] : 10;

        $followModel = new FollowModel();

        $userModel = new User();

        $filmModel = new Video();

        $rs = $followModel->getNewPublish(USERID, $offset, $length);

        if (empty($rs)) {

            $sql = $filmModel->setWhere()->setOrder('zan_sum desc')->setLimit($offset, $length)->setSql();

            $rs = Db::query($sql);

        }

        $rs = $filmModel->initializeFilm($rs, Video::$allow_fields['new_publish']);
        $Comment = new Lists();

        foreach ($rs as &$val) {
            $user = $userModel->getUser($val['user_id']);

            $val['comment_list'] = $Comment->commentList($val['id'], 0, 4);

            $val['nickname'] = $user['nickname'] ? $user['nickname'] : '';

            $val['avatar'] = img_url($user['avatar'], '', 'avatar');
        }

        return $this->success($rs, '获取成功');
    }



    /**
     * 动态
     *
     * @return \think\response\Json
     */
    public function dynamics()
    {
        $offset = Request::param('offset', 0);

        $FollowModel = new FollowModel();

        $VideoModel = new Video();

        //获取最新关注的人所发布的视频
        $videos = $FollowModel->getNewPublish(USERID, $offset);

        if (empty($videos))
        {
            $prophet = new Prophet(USERID, APP_MEID);

            $videos = $prophet->getList($offset, 10);
        }

        $videos = $VideoModel->initializeFilm($videos, Video::$allow_fields['new_publish'], USERID, true);

        $list = $this->initDynamics($videos);

        return $this->success($list, '获取成功');
    }


    protected function initDynamics($data)
    {
        $Comment = new Lists();

        $coreSdk = new CoreSdk();

        $live = [];

        foreach ($data as $key => &$val)
        {
            if ($val['is_live'])
            {
                $val['room_info']['cover_url'] = img_url($val['room_info']['cover_url'], 'live');
                $val['room_info']['jump'] = getJump('enter_room', ['room_id' => $val['room_info']['room_id'], 'from' => 'follow']);
                $audience = $coreSdk->post('zombie/getRoomAudience', ['room_id' => $val['room_info']['room_id']]);
                $val['room_info']['audience'] = (int)$audience[$val['room_info']['room_id']];
                if (!array_key_exists($val['user_id'], $live))
                {
                    $val['room_info']['index'] = mt_rand(count($live), $key)+1;
                    $live[$val['user_id']] = $val['room_info'];
                }
            }

            unset($val['room_info']);

            $val['comment_list'] = $Comment->commentList($val['id'], 0, 4);
        }

        $live = array_values($live);

        return ['video' => $data, 'live' => $live];
    }

}
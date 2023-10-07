<?php
/**
 * Created by PhpStorm.
 * User: “崔鹏
 * Date: 2020/06/24
 * Time: 上午 9:27
 */

namespace app\api\controller\friend;

use app\admin\service\SysConfig;
use app\api\controller\User;
use app\api\service\Follow;
use app\common\controller\UserController;
use app\friend\service\ChatMessage;
use bxkj_common\RedisClient;
use bxkj_module\exception\ApiException;

class Chat extends UserController
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

    public function sendMsg()
    {
        $submit = submit_verify('friendsub' . USERID);
        if (!$submit) return $this->jsonError('您提交太频繁了~~~');
        $params   = request()->param();
        $validate = new \app\api\validate\Chat();
        $result   = $validate->scene('sendMsg')->check($params);
        if (true !== $result) {
            return $this->jsonError($validate->getError());
        }
        $followModel = new Follow();
        $myFollows   = $followModel->mutualArray(USERID);
        $chat        = new ChatMessage();
        if (in_array($params['to_uid'], $myFollows) != 1) {
            $noReadTotal = $chat->countTotal(['to_uid' => $params['to_uid'], 'status' => 0]);
            if ($noReadTotal > $this->friendConfigRes['chat_num']) {
                return $this->jsonError('您非对方好友，发送条数受限');
            }
        }
        $params['from_uid'] = USERID;
        $rest               = $chat->add($params);
        if (!$rest) return $this->jsonError('操作失败');
        return $this->success($rest, '发送悄悄话成功');
    }

    public function seeMsg()
    {
        $params   = request()->param();
        $validate = new \app\api\validate\Chat();
        $result   = $validate->scene('seeMsg')->check($params);
        if (true !== $result) {
            return $this->jsonError($validate->getError());
        }
        $page_index = $params['page_index'] ? $params['page_index'] : 1;
        $page_size  = $params['page_size'] ? $params['page_size'] : 10;
        $chat       = new ChatMessage();
        $rest       = $chat->seeMsg($page_index, $page_size, [$params['from_uid'], USERID], 'id desc', "id,messages,status,ctime,messages_type");
        if (!empty($rest['data'])){
            foreach ($rest['data'] as $k => $v) {
                $rest['data'][$k]['fromDetail'] = userMsg($v['from_uid'], 'user_id,avatar,nickname,gender');
                $rest['data'][$k]['toDetail']   = userMsg($v['to_uid'], 'user_id,avatar,nickname,gender');
            }
        }

        return $this->success($rest, '获取');
    }

    public function getMaxCanSend(){

        return $this->success($this->friendConfigRes['chat_num'], '获取成功');
    }
}
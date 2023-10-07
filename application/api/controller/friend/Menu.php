<?php
/**
 * Created by PhpStorm.
 * User: “崔鹏
 * Date: 2020/07/03
 * Time: 下午 1:29
 */

namespace app\api\controller\friend;

use app\admin\service\SysConfig;
use app\common\controller\UserController;
use app\friend\service\FriendCircleClassfiy;
use app\friend\service\FriendCircleClassfiyone;
use bxkj_common\RedisClient;
use bxkj_module\exception\ApiException;

class Menu extends UserController
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

    public function getSecondMenu()
    {
        $params   = request()->param();
        $validate = new \app\api\validate\Menu();
        $result   = $validate->scene('getSecondMenu')->check($params);
        if (true !== $result) {
            return $this->jsonError($validate->getError());
        }
        $clissfiy   = new FriendCircleClassfiy();
        $secondMenu = $clissfiy->getQuery(['masterid' => $params['menu_id'], 'status' => 1], 'child_id,child_name', 'id');
        return $this->success($secondMenu, '获取成功');
    }

    public function getOneMenu(){
        $params   = request()->param();
        $clissfiy   = new FriendCircleClassfiyone();
        $secondMenu = $clissfiy->getQuery(['status' => 1], 'id,class_name', 'id');
        return $this->success($secondMenu, '获取成功');
    }
}
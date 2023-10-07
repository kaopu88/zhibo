<?php

namespace app\api\controller;

use bxkj_module\exception\Exception;
use app\common\controller\UserController;
use app\common\service\User;
use bxkj_common\RedisClient;
use think\Db;

class Yunxin extends UserController
{
    protected $YunxinExtend;

    public function __construct()
    {
        parent::__construct();
        $config = config('message.aomy_private_letter');
        if ($config['platform'] != 'yunxin') throw new Exception('私信配置有误~');
        $this->YunxinExtend = new \bxkj_im\YunXin($config['app_key'], $config['app_secret']);
    }

    public function createUserId()
    {
        $user_yunxin_token = Db::name('user_yunxin_token')->where(array('user_id' => USERID))->find();
        if ($user_yunxin_token) {
            return $this->success(['user_id' => $user_yunxin_token['user_id'], 'token' => $user_yunxin_token['token']], '获取成功');
        } else {
            $userInfo = Db::name('user')->where(array('user_id' => USERID))->find();
            if (!$userInfo) return $this->success(['user_id' => USERID, 'token' => ''], '获取成功1');
            $register = $this->YunxinExtend->createUserId($userInfo['user_id'], $userInfo['nickname'], '', $userInfo['avatar'], '');
            if ($register['code'] == "200") {
                $blacklist = Db::name('user_blacklist')->field('to_uid')->where(array('user_id' => USERID, 'status' => 1))->select();
                if ($blacklist) {
                    foreach ($blacklist as $value) {
                        $to_user_yunxin_token = Db::name('user_yunxin_token')->where(array('user_id' => $value['to_uid']))->find();
                        if ($to_user_yunxin_token) {
                            $this->YunxinExtend->specializeFriend($register['info']['accid'], $value['to_uid']);
                        }
                    }
                }
                Db::name('user_yunxin_token')->insertGetId(['user_id' => $register['info']['accid'], 'token' => $register['info']['token'], 'create_time' => time()]);
                return $this->success(['user_id' => $register['info']['accid'], 'token' => $register['info']['token']], '获取成功');
            } else {
                $userInfo = Db::name('user')->where(array('user_id' => USERID))->find();
                $update   = $this->YunxinExtend->updateUserToken($userInfo['user_id'], $userInfo['nickname'], '', $userInfo['avatar'], 'e5e4312f1ffcdaf61d528ced0a951581');
                if ($update['code'] == "200") {
                    $blacklist = Db::name('user_blacklist')->field('to_uid')->where(array('user_id' => USERID, 'status' => 1))->select();
                    if ($blacklist) {
                        foreach ($blacklist as $value) {
                            $to_user_yunxin_token = Db::name('user_yunxin_token')->where(array('user_id' => $value['to_uid']))->find();
                            if ($to_user_yunxin_token) {
                                $this->YunxinExtend->specializeFriend($update['info']['accid'], $value['to_uid']);
                            }
                        }
                    }
                    Db::name('user_yunxin_token')->insertGetId(['user_id' => $update['info']['accid'], 'token' => $update['info']['token'], 'create_time' => time()]);
                    return $this->success(['user_id' => $update['info']['accid'], 'token' => $update['info']['token']], '获取成功');
                } else {
                    return $this->success(['user_id' => USERID, 'token' => ''], '获取成功');
                }
            }
        }
    }

    public function getUser()
    {
        $params  = request()->param();
        $user_id = $params['user_id'];
        if (!$user_id) return $this->jsonError('参数错误');
        $user   = new User();
        $list   = $user->getUsers($user_id, USERID, 'user_id, avatar, nickname');
        $USERID = USERID;
        $redis  = new RedisClient();
        if ($list) {
            foreach ($list as &$item) {
                $item['avatar']   = img_url($item['avatar'], '200_200', 'thumb');
                $isBlack          = $USERID ? $redis->zScore("blacklist:{$USERID}", $item['user_id']) : false;
                $item['is_black'] = $isBlack ? '1' : '0';
            }
            unset($item);
        }
        return $this->success($list ? $list : [], '获取成功');
    }

    public function sendBatchAttachMsg()
    {
        $params      = request()->param();
        $fromAccid   = $params['fromAccid'];
        $toAccids    = $params['toAccids'];
        $attach      = $params['attach'];
        $pushcontent = $params['pushcontent'];
        $payload     = $params['payload'];
        $sound       = $params['sound'];
        $res         = $this->YunxinExtend->sendBatchAttachMsg($fromAccid, $toAccids, $attach, $pushcontent = '', $payload = array(), $sound = '');
        return $this->success($res, '操作成功');
    }
}

<?php

namespace app\api\controller;

use bxkj_module\exception\Exception;
use app\common\controller\UserController;
use app\common\service\User;
use RongCloud\RongCloud as RongCloudService;
use think\Db;

class Rongcloud extends UserController
{
    protected $RongSDK;

    public function __construct()
    {
        parent::__construct();
        $config = config('message.aomy_private_letter');
        if ($config['platform'] != 'rongcloud') throw new Exception('私信配置有误~');
        $this->RongSDK = new RongCloudService($config['app_key'], $config['app_secret']);
    }

    public function getToken()
    {
        $user_rongcloud_token = Db::name('user_rongcloud_token')->where(array('user_id'=>USERID))->find();
        if ($user_rongcloud_token){
            return $this->success(['user_id' => $user_rongcloud_token['user_id'], 'token' => $user_rongcloud_token['token']], '获取成功');
        }else{
            $userInfo = Db::name('user')->where(array('user_id'=>USERID))->find();
            if (!$userInfo) return $this->success(['user_id' => USERID, 'token' => ''], '获取成功');
            $user = [
                'id'=> $userInfo['user_id'],
                'name'=> $userInfo['nickname'],//用户名称
                'portrait'=> $userInfo['avatar'] //用户头像
            ];
            $register = $this->RongSDK->getUser()->register($user);

            if ($register['code'] == "200"){
                $blacklist = Db::name('user_blacklist')->field('to_uid')->where(array('user_id'=>USERID, 'status'=>1))->select();
                $to_uids = array();
                if ($blacklist){
                    foreach ($blacklist as $value){
                        $to_uids[] = $value['to_uid'];
                    }
                    $user = [
                        'id'=> USERID,//用户 id
                        'blacklist'=> $to_uids //需要添加黑名单的人员列表
                    ];
                    $this->RongSDK->getUser()->Blacklist()->add($user);
                }
                Db::name('user_rongcloud_token')->insertGetId(['user_id' => $register['userId'], 'token' => $register['token'], 'create_time' => time()]);
                return $this->success(['user_id' => $register['userId'], 'token' => $register['token']], '获取成功');
            }else{
                return $this->success(['user_id' => USERID, 'token' => ''], '获取成功');
            }
        }
    }

    public function getUser()
    {
        $params = request()->param();
        $user_id = $params['user_id'];
        if(!$user_id) return $this->jsonError('参数错误');
        $user = new User();
        $list = $user->getUsers($user_id, USERID, 'user_id, avatar, nickname');
        return $this->success($list ? $list : [], '获取成功');
    }
}

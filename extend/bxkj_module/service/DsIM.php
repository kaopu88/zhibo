<?php
namespace bxkj_module\service;

use bxkj_module\exception\Exception;
use think\Db;


class DsIM extends Service
{
    protected $YunxinExtend;

    public function __construct()
    {
        parent::__construct();
        $config = config('message.aomy_private_letter');
        if ($config['platform'] != 'yunxin') throw new Exception('私信配置有误~');
        $this->YunxinExtend = new \bxkj_im\YunXin($config['app_key'], $config['app_secret']);
    }

    public function updateUserData($user_id)
    {
        $user = Db::name('user')->where(['user_id' => $user_id])->find();
        $user_yunxin_token = Db::name('user_yunxin_token')->where(array('user_id'=>$user_id))->find();
        if ($user && $user_yunxin_token){
            $this->YunxinExtend->updateUinfo($user['user_id'], $user['nickname'], $user['avatar']);
        }
        return true;
    }

    public function specializeFriend($user_id, $to_uid, $relationType = '1', $value = '1')
    {
        $user_yunxin_token = Db::name('user_yunxin_token')->where(array('user_id'=>$user_id))->find();
        $to_user_yunxin_token = Db::name('user_yunxin_token')->where(array('user_id'=>$to_uid))->find();
        if ($user_yunxin_token && $to_user_yunxin_token){
            $this->YunxinExtend->specializeFriend($user_id, $to_uid, $relationType, $value);
        }
        return true;
    }
}
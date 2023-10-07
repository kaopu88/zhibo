<?php
namespace bxkj_module\service;
use think\Db;

class UserTaskLog extends  Service
{
    /**
     * 获取邀请用户奖励（用于播报）

     */
    public function  Queryreword($type,$limit){
        return   Db::name('user_task_log')->where(['task_type'=>$type,'status'=>2])->order('id desc')->limit($limit)->select();
    }
}
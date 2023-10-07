<?php


namespace app\api\controller;


use app\common\controller\UserController;
use app\api\service\live\Pk as PkDomain;

class Pk extends UserController
{
    //好友列表
    public function friendsList()
    {
        $pk = new PkDomain();

        $res = $pk->setFollow(USERID)->initialize();

        if (is_error($res)) return $this->jsonError($res);

        return $this->success($res);
    }


    //获取系统默认Pk参数
    public function defaultPkOption()
    {
        $pk = new PkDomain();

        $res =  $pk->getDefaultPkOption(USERID);

        return $this->success($res);
    }
}
<?php

namespace app\core\controller;

use app\core\service\UserBehavior;
use bxkj_module\push\AppPush;
use think\Db;
use think\facade\Request;


class Behavior extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    //喜欢视频
    public function like_film()
    {
        return json_success([], '接口已废弃');
    }

    //取消喜欢视频
    public function cancel_like_film()
    {
        return json_success([], '接口已废弃');
    }

    //喜欢评论
    public function like_comment()
    {
        return json_success([], '接口已废弃');
    }

    //取消喜欢评论
    public function cancel_like_comment()
    {
        return json_success([], '接口已废弃');
    }

    //评论作品
    public function comment()
    {
        return json_success([], '接口已废弃');
    }

    //取消评论作品
    public function cancel_comment()
    {
        return json_success([], '接口已废弃');
    }

    //回复评论
    public function reply()
    {
        return json_success([], '接口已废弃');
    }

    public function cancel_reply()
    {
        return json_success([], '接口已废弃');
    }

    //关注用户
    public function follow()
    {
        return json_success([], '接口已废弃');
    }

    public function cancel_follow()
    {
        return json_success([], '接口已废弃');
    }

    //发布短视频
    public function publish_film()
    {
        return json_success([], '接口已废弃');
    }

    //主播开播
    public function live()
    {
        return json_success([], '接口已废弃');
    }

    //取消开播
    public function cancel_live()
    {
        return json_success([], '接口已废弃');
    }

    //@好友
    public function at_friend()
    {
        return json_success([], '接口已废弃');
    }

}

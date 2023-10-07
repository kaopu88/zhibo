<?php

namespace app\api\service;

use bxkj_module\service\User;
use think\Db;

class UserAddressBook extends \bxkj_module\service\UserAddressBook
{
    public function getRecommendList($user_id,$offset, $length)
    {
        $where = [
            ['user_id','=',$user_id],
            ['friend_id','>',0],
            ['is_follow','=',0],
            ['is_recommend','=',1],
            ['status','=',1]
        ];
        $lists = Db::name('user_address_book')->where($where)->limit($offset, $length)->order('id desc')->field('id as addbook_id,phone, friend_id, is_follow, name as addbook_name')->select();
        if (empty($lists)) return [];

        $userModel = new user();

        foreach ($lists as &$value)
        {
            $user = $userModel->getUser($value['friend_id'],USERID,'user_id, nickname, avatar, is_follow, gender, level');

//            $value['user_info'] = $user;
            $value['nickname'] = $user['nickname'];
            $value['avatar'] = $user['avatar'];
            $value['gender'] = $user['gender'];
            $value['level'] = $user['level'];
            $value['tip'] = '可能认识的人';
        }

        return $lists;
    }

    public function getBooksList($user_id,$offset, $length)
    {
        $where = [
            ['user_id','=',$user_id],
            ['friend_id','>',0],
            ['status','=',1]
        ];
        $lists = Db::name('user_address_book')->where($where)->limit($offset, $length)->order('id desc')->field('id as addbook_id,phone, friend_id, is_follow, name as addbook_name')->select();
        if (empty($lists)) return [];

        $userModel = new user();

        foreach ($lists as &$value)
        {
            $user = $userModel->getUser($value['friend_id'],USERID,'user_id, nickname, avatar, is_follow, gender, level');

//            $value['user_info'] = $user;
            $value['nickname'] = $user['nickname'];
            $value['avatar'] = $user['avatar'];
            $value['gender'] = $user['gender'];
            $value['level'] = $user['level'];
        }

        return $lists;
    }
}
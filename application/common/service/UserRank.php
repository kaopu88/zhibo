<?php
/**
 * Created by PhpStorm.
 * User: 李成
 * Date: 2020/5/11
 * Time: 11:51
 */
namespace app\common\service;

use think\Db;

class UserRank extends Service
{
    /**
     * 获取下级粉丝
     * @param $userId
     * @param int $level
     * @return array|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getAllFans($userId, $level=0)
    {
        if($level != 0){
            $where['level'] = $level;
        }
        $where['parent_uid'] = $userId;
        $userList = Db::name("user_rank")->field("parent_uid,uid,level")->where($where)->select();
        return $userList;
    }

    /**
     * 统计下级粉丝人数
     * @param $userId
     * @param int $level
     * @return float|string
     */
    public function getCountFans($userId, $level=0)
    {
        if($level != 0){
            $where['level'] = $level;
        }
        $where['parent_uid'] = $userId;
        $num = Db::name("user_rank")->where($where)->count();
        return $num;
    }

    /**
     * 获取用户所有上级
     * @param $userId
     * @return array|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getParents($userId)
    {
        $where['uid'] = $userId;
        $userList = Db::name("user_rank")->field("parent_uid,uid,level")->where($where)->select();
        return $userList;
    }
}
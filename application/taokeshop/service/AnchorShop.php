<?php
/**
 * Created by PhpStorm.
 * User: 李成
 * Date: 2020/4/30
 * Time: 13:56
 */
namespace app\taokeshop\service;

use app\admin\service\User;
use app\common\service\Service;
use think\Db;

class AnchorShop extends Service
{
    /**
     * @param $where
     * @return array|null|\PDOStatement|string|\think\Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getShopInfo($where)
    {
        $shopInfo = Db::name("anchor_shop")->where($where)->find();
        if($shopInfo){
            $user = new User();
            $userInfo = $user->getInfo($shopInfo['user_id']);
            $shopInfo['avatar'] = $userInfo['avatar'];
        }
        return $shopInfo;
    }

    /**
     * @param $where
     * @param $data
     * @return int|string
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function updateShopInfo($where, $data)
    {
        if(isset($data['title']) && $data['title'] != ""){
            $upData['title'] = $data['title'];
        }
        if(isset($data['bg_img']) && $data['bg_img'] != ""){
            $upData['bg_img'] = $data['bg_img'];
        }
        if(isset($data['desc']) && $data['desc'] != ""){
            $upData['desc'] = $data['desc'];
        }
        if(isset($data['status']) && $data['status'] != ""){
            $upData['status'] = $data['status'];
        }
        $status = Db::name("anchor_shop")->where($where)->update($upData);
        return $status;
    }
}
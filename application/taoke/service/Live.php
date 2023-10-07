<?php
/**
 * Created by PhpStorm.
 * User: 李成
 * Date: 2020/7/10
 * Time: 9:02
 */
namespace app\taoke\service;

use bxkj_common\RedisClient;
use bxkj_module\service\Service;
use think\Db;

class Live extends Service
{
    /**
     * 检测商品是否添加过此商品
     * @param $userId
     * @param $goodsId
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function checkAddGoodBag($userId, $goodsId)
    {
        $data['add_window'] = 0;
        $data['add_bag'] = 0;
        $status = $this->checkGoods($userId, $goodsId);
        if($status){//橱窗内有记录
            $data['add_window'] = 1;
        }else{
            $status2 = $this->checkBagGood($userId, $goodsId);
            if($status2){//橱窗内有记录
                $data['add_bag'] = 1;
            }
        }
        return $data;
    }

    /**
     * 检测是否存在商品袋中
     * @param $userId
     * @param $goodsId
     * @return bool true 有 false：无
     */
    public function checkBagGood($userId, $goodsId)
    {
        $key = "bag_good:{$userId}";
        $redis = RedisClient::getInstance();
        $list = $redis->smembers($key);
        if(empty($list)){
            $goodsList = Db::name("good_bag")->where(["user_id" => $userId])->select();
            if($goodsList){
                foreach ($goodsList as $value){
                    $this->updateBagRedis($userId, $value['goods_id']);
                }
            }else{
                return false;
            }
        }
        $status = $redis->sismember($key, $goodsId);
        return $status;
    }

    /**
     * 更新商品袋redis 没有则添加
     * @param $userId
     * @param $goodsId
     * @return int
     */
    public function updateBagRedis($userId, $goodsId)
    {
        $key = "bag_good:{$userId}";
        $redis = RedisClient::getInstance();
        $redis->expire($key, 1800);
        $num = $redis->sadd($key, $goodsId);
        return $num;
    }

    /**
     * 删除商品袋redis
     * @param $userId
     * @param $goodsId
     * @return int
     */
    public function delBagRedis($userId, $goodsId)
    {
        $key = "bag_good:{$userId}";
        $redis = RedisClient::getInstance();
        $num = $redis->srem($key, $goodsId);
        return $num;
    }

    /**
     * 检测商品是否在橱窗里
     * @param $userId
     * @param $goodsId
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function checkGoods($userId, $goodsId)
    {
        /*$key = "window_goods:{$userId}";
        $redis = RedisClient::getInstance();
        if(empty($redis->hgetall($key))){
            $goodsList = Db::name("anchor_goods")->where(["user_id" => $userId])->select();
            if($goodsList){
                foreach ($goodsList as $value){
                    $info = Db::name("goods")->where(["id"=>$value['goods_id']])->find();
                    if($info) {
                        $this->addWinGoods($userId, 'db_id_'.$value['goods_id'], $info['goods_id']);
                    }
                }
            }else{//没有橱窗商品
                return false;
            }
        }
        $goodsIdArray = $redis->hvals($key);
        if(in_array($goodsId, $goodsIdArray)) return true;//此商品id已添加过橱窗
        return false;*/

        $info = Db::name("goods")->field("id")->where(["goods_id" => $goodsId])->limit(1)->find();
        if(empty($info)){
            return false;
        }

        $num = Db::name("anchor_goods")->where(["user_id" => $userId, "goods_id" => $info['id']])->count();
        if($num == 0){
            return false;
        }
        return true;
    }

    /**
     * 添加橱窗商品到redis
     * @param $userId
     * @param $id
     * @param $goodsId
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function addWinGoods($userId, $id, $goodsId)
    {
        $status = $this->checkGoods($userId, $goodsId);
        if($status){
            return false;
        }
        $key = "window_goods:{$userId}";
        $redis = RedisClient::getInstance();
        $redis->expire($key, 1800);
        if($redis->hset($key, 'db_id_'.$id, $goodsId)) return true;
        return false;
    }

    /**
     * 移除商品橱窗redis
     * @param $userId
     * @param $id
     */
    public function removeWinGoods($userId, $id)
    {
        $key = "window_goods:{$userId}";
        $redis = RedisClient::getInstance();
        $redis->hdel($key, 'db_id_'.$id);
    }

}
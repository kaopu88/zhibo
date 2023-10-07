<?php

namespace app\api\service;

interface Favorite
{

    /**
     * 收藏夹列表
     * @param $offset
     * @param $length
     * @return mixed
     */
    public function listByFavorite($user_id=USERID, $offset=0, $length=10);


    /**
     * 添加收藏
     * @param $item_id
     * @return mixed
     */
    public function add($item_id);


    /**
     * 从收藏夹移除
     * @param $item_id
     * @return mixed
     */
    public function remove($item_id);


    /**
     * 是否已收藏
     * @param $item_id
     * @return mixed
     */
    public function isFavorite($item_id);


    /**
     * 删除收藏夹中1个或多个
     * @param array $items
     * @return mixed
     */
    public function delete(array $items);




}
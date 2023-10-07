<?php

namespace app\api\service;


use think\Db;
use app\common\service\Service;
use bxkj_module\exception\ApiException;

class Topic extends Service
{
    public function getAll($offset, $length)
    {
        $res = Db::name('topic')
            ->field('id topic_id, icon, title, descr, participate_num')
            ->order('sort desc')
            ->limit($offset, $length)
            ->select();

        return $res;
    }


    public function findAll($where, $fields = '*')
    {
        $res = Db::name('topic')
            ->field($fields)
            ->order('sort desc')
            ->where($where)
            ->select();

        return $res;
    }


    public function inserts($data, $target_id)
    {
        $rows = [];

        $now = time();

        foreach ($data as $topic)
        {
            $rows[] = ['topic_id' => $topic, 'video_id' => $target_id, 'create_time' => $now];
        }

        try{
            $rs = Db::name('topic_relation')->insertAll($rows);
        }
        catch (ApiException $e){
            return $e;
        }

        if (!$rs) return false;

        return true;
    }

    //恢复收藏夹数据
    public function restoreFavorite($user_id)
    {
        $res = Db::name('topic_favorites')
            ->where(['user_id' => $user_id])
            ->select();

        return $res;
    }

    //移除收藏夹
    public function removeByFavorite($user_id, $topic_id)
    {
        $res = Db::name('topic_favorites')
            ->where(['user_id' => $user_id, 'topic_id' => $topic_id])
            ->delete();

        return $res;
    }

    public function exists($where)
    {
        $res = Db::name('topic')->where($where)->count();

        return empty($res) ? false : true;
    }

    public function vectorUpdate($id, $vectorName, $mark = '+', $count = 1){
        if ($mark == '+'){
            $res = Db::name('topic')
                ->where('id', $id)
                ->setInc($vectorName, $count);
        }else{
            $res = Db::name('topic')
                ->where('id', $id)
                ->setDec($vectorName, $count);
        }
        return $res !== false;
    }

    //添加进收藏夹
    public function addByFavorite($user_id, $topic_id)
    {
        $data = [
            'user_id' => $user_id,
            'topic_id' => $topic_id,
            'create_time' => time()
        ];

        try{
            $res = Db::name('topic_favorites')
                ->insert($data);
        } catch (ApiException $e) {
            throw $e;
        }

        return $res;
    }


    //收藏夹内的话题列表
    public function topicListByFavorite($user_id, $offset, $length)
    {

        $res = Db::name('topic_favorites')
            ->alias('tf')
            ->join('topic t','tf.topic_id=t.id')
            ->where(['tf.user_id'=>$user_id])
            ->order('tf.create_time DESC')
            ->limit($offset, $length)
            ->select();

        return $res;
    }
}

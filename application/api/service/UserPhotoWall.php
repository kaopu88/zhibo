<?php

namespace app\api\service;
use think\Db;
use app\common\service\Service;

class UserPhotoWall extends Service
{
    public function inserts($iamge, $position = 0)
    {
        $has = Db::name('user_photo_wall')->where(['user_id' => USERID])->find();
        $now = time();

        if (empty($has)) {
            $row = ['image' => $iamge, 'user_id' => USERID,'create_time' => $now];
            $rs = Db::name('user_photo_wall')->insertGetId($row);
            $reId = $rs;
        } else {
            $row = ['image' => $iamge, 'create_time' => $now];
            $rs = Db::name('user_photo_wall')->where(['id' => $has['id']])->update($row);
            $reId = $has['id'];
        }

        if (!$rs) return false;

        return $reId;
    }

    public function getlist($userId = USERID, $page = 0,$length = 1, $field = 'image')
    {
        $rows = [];
        if (empty($userId)) return false;
        $res = Db::name('user_photo_wall')->field($field)->where(['user_id' => $userId])->limit($page, $length)->order(['position'=>'asc'])->select();
        if (!empty($res)) {
            foreach ($res as $key => $value) {
                if (!empty($value['image'])){
                    $rows  = explode(',', $value['image']);
                    foreach ($rows as $k => $v) {
                        $rows[$k] = img_url($v, '200_200', 'thumb');
                    }
                }
            }
        }
        return ['result' => $res ?:[], 'image' => $rows];
    }
}

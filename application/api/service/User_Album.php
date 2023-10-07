<?php

namespace app\api\service;
use think\Db;
use app\common\service\Service;

class User_Album extends Service
{
    public function inserts($data)
    {
        $rows = $row = [];

        $now = time();

        foreach ($data as $image)
        {
            $row = ['image' => $image, 'user_id' => USERID, 'create_time' => $now];
            $rs = Db::name('user_album')->insertGetId($row);

            if ($rs){
                $row['thumb'] = img_url($row['image'], '200_200', 'thumb');
                $row['id'] = $rs;
            }

            $rows[] = $row;
        }

        if (!$rows) return false;

        return $rows;
    }
}

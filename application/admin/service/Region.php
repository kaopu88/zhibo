<?php

namespace app\admin\service;

use bxkj_module\service\Service;
use think\Db;

class Region extends Service
{
    public function getCitesByIds($ids)
    {
        if (empty($ids)) return [];
        $result = Db::name('region')->whereIn('id', $ids)->field('id,pid,name,pinyin')->limit(count($ids))->select();
        return $result ? $result : [];
    }

    public function getCityById($cityId)
    {
        if (empty($cityId)) return [];
        $result = $this->getCitesByIds([$cityId]);
        return $result ? $result[0] : [];
    }

}
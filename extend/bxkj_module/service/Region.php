<?php

namespace bxkj_module\service;

use think\Db;

class Region extends Service
{
    public function likeRegion($name, $level = 2, $pid = null)
    {
        $name = trim($name);
        if (preg_match('/^\d+$/', $name)) {
            $where = array('admincode' => $name, 'level' => $level);
            if (isset($pid)) $where['pid'] = $pid;
            $region = Db::name('region')->field('id,name,pid')->where($where)->find();
        } else {
            $name = replace_place_name($name);
            $where = array('level' => $level);
            if (isset($pid)) $where['pid'] = $pid;
            $region = Db::name('region')->field('id,name,pid')->where('name', 'like', "%{$name}%")->where($where)->find();
        }
        return $region;
    }
}
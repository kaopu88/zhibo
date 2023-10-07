<?php

namespace bxkj_module\service;

use think\Db;

class Packages extends Service
{
    //获取最新包信息
    public function getLastPackage($os, $channel = '', $name = '')
    {
        $where['os'] = strtolower($os);
        //$where['name'] = $name;
        $where['channel'] = empty($channel) ? 'common' : $channel;
        $where['status'] = '1';
        $result = Db::name('packages')->where($where)->order('code desc,create_time desc')->find();
        if (!empty($result)) {
            $result['filesize_str'] = round($result['filesize'] / (1024 * 1024), 2);
        }
        return $result;
    }
}
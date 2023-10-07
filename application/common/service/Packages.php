<?php

namespace app\common\service;

use think\Db;

class Packages extends Service
{
    public function getLastVersion($inputData)
    {
        //$name = $inputData['name'];
        $channel = $inputData['channel'];
        $os = isset($inputData['os']) ? $inputData['os'] : 'android';
        $where = array('channel' => $channel, 'os' => $os, 'status' => '1');
        if (!empty($inputData['code'])) {
            $where['code'] = $inputData['code'];
        }
        $row = Db::name('packages')->where($where)->order('code DESC')->find();
        if (!$row) return [];
        $result['appv'] = $row['version'];
        $size = round((int)$row['filesize'] / (1024 * 1024), 2);
        $result['file_size'] = (string)$size;
        $result['file_size_int'] = (int)$row['filesize'];
        $result['file_name'] = $row['name'] . '_' . $row['channel'] . '-' . $row['version'] . '.apk';
        $result['download_url'] = $row['file_path'];
        $result['update_type'] = $row['update_type'];
        $result['update_info'] = $row['descr'];
        $result['code'] = $row['code'];
        $result['channel'] = $row['channel'];
        $result['url'] = $row['url'] ?: '';
        $result['id'] = $row['id'];
        return $result;
    }

    //增加下载量
    public function incrDownloadNum($id, $num)
    {
        $res = Db::name('packages')->where('id', $id)->setInc('download_num', $num);
        if (!$res) return $this->setError('更新失败');
        return $res;
    }

}
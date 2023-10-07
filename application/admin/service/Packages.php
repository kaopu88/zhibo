<?php

namespace app\admin\service;

use bxkj_module\service\Service;
use Qiniu\Auth;
use think\Db;

class Packages extends Service
{

    //获取总记录数
    public function getTotal($get)
    {
        $this->db = Db::name('packages');
        $this->setWhere($get);
        return $this->db->count();
    }

    //获取列表
    public function getList($get, $offset, $length)
    {
        $this->db = Db::name('packages');
        $this->setWhere($get)->setOrder($get);
        $result = $this->db->limit($offset, $length)->select();
        foreach ($result as &$item) {
            $item['filesize_str'] = round($item['filesize'] / (1024 * 1024), 2) . 'MB';
        }
        return $result;
    }

    //设置查询条件
    private function setWhere($get)
    {
        $where = array();
        if ($get['status'] != '') {
            $where[] = ['status', '=', $get['status']];
        }
        if ($get['channel'] != '') {
            $where[] = ['channel', '=', $get['channel']];
        }
        if ($get['os'] != '') {
            $where[] = ['os', '=', $get['os']];
        }
        if ($get['update_type'] != '') {
            $where[] = ['update_type', '=', $get['update_type']];
        }
        if ($get['code'] != '') {
            $where[] = ['code', '=', $get['code']];
        }
        $this->db->setKeywords(trim($get['keyword']), '', 'number id', 'name');
        $this->db->where($where);
        return $this;
    }

    //设置排序规则
    private function setOrder($get)
    {
        $order = array();
        if (empty($get['sort'])) {
            $order['code'] = 'desc';
            $order['os'] = 'asc';
            $order['channel'] = 'desc';
            $order['create_time'] = 'desc';
        }
        $this->db->order($order);
        return $this;
    }

    public function add($inputData)
    {
        Service::startTrans();
        $data = $this->df->process('add@packages', $inputData)->output();
        if ($data === false) return $this->setError($this->df->getError());
        if (!$this->checkVersion($data)) return $this->setError('外部版本号重复');
        if (!$this->checkCode($data)) return $this->setError('内部版本号重复');
        $id = Db::name('packages')->insertGetId($data);
        if (!$id) return $this->setError('新增失败');
        if (!empty($data['file_path'])) {
            // $res = $this->updateFileInfo($id, $data['file_path']);
            // if (!$res) return false;
        }
        Service::commit();
        return $id;
    }

    public function update($inputData)
    {
        $data = $this->df->process('update@packages', $inputData)->output();
        if ($data === false) return $this->setError($this->df->getError());
        $saveData = $this->df->getLastData(false);
        if (!$this->checkVersion($data)) return $this->setError('外部版本号重复');
        if (!$this->checkCode($data)) return $this->setError('内部版本号重复');
        $num = Db::name('packages')->where(array('id' => $data['id']))->update($data);
        if (!$num) return $this->setError('更新失败');
        if (!empty($data['file_path']) && $saveData['file_path'] != $data['file_path']) {
            // $this->updateFileInfo($data['id'], $data['file_path']);
        } else if (!empty($saveData['file_path']) && empty($saveData['filesize'])) {
            // $this->updateFileInfo($data['id'], $saveData['file_path']);
        }
        return $num;
    }

    protected function updateFileInfo($id, $file_path)
    {
        $accessKey = config('upload.platform_config.access_key');
        $secretKey = config('upload.platform_config.secret_key');
        $bucket = config('upload.platform_config.bucket');
        $base = config('upload.platform_config.base_url');
        $key = ltrim(str_replace($base, '', $file_path), '/');
        $auth = new Auth($accessKey, $secretKey);
        $config = new \Qiniu\Config();
        $bucketManager = new \Qiniu\Storage\BucketManager($auth, $config);
        list($fileInfo, $err) = $bucketManager->stat($bucket, $key);
        if ($err) return $this->setError('获取安装包信息错误' . $err->message());
        $num = Db::name('packages')->where('id', $id)->update(['filesize' => $fileInfo['fsize'], 'hash' => $fileInfo['hash']]);
        if (!$num) return $this->setError('更新安装包信息错误');
        return $fileInfo;
    }

    protected function checkCode($data)
    {
        $where = [
            ['name', 'eq', $data['name']],
            ['code', 'eq', $data['code']],
            ['os', 'eq', $data['os']]
        ];
        if (!empty($data['id'])) {
            $where[] = ['id', 'neq', $data['id']];
        }
        $num = Db::name('packages')->where($where)->count();
        return $num <= 0;
    }

    protected function checkVersion($data)
    {
        $where = [
            ['name', 'eq', $data['name']],
            ['version', 'eq', $data['version']],
            ['os', 'eq', $data['os']]
        ];
        if (!empty($data['id'])) {
            $where[] = ['id', 'neq', $data['id']];
        }
        $num = Db::name('packages')->where($where)->count();
        return $num <= 0;
    }

    public function delete($ids)
    {
        $ids = is_array($ids) ? $ids : explode(',', $ids);
        if (empty($ids)) $this->error('请选择记录');
        $packages = Db::name('packages')->whereIn('id', $ids)->select();
        $num = Db::name('packages')->whereIn('id', $ids)->delete();
        if (!$num) return $this->setError('删除失败');
        $accessKey = config('upload.platform_config.access_key');
        $secretKey = config('upload.platform_config.secret_key');
        $bucket = config('upload.platform_config.bucket');
        $base = config('upload.platform_config.base_url');
        $auth = new Auth($accessKey, $secretKey);
        $config = new \Qiniu\Config();
        $bucketManager = new \Qiniu\Storage\BucketManager($auth, $config);
        $packages = $packages ? $packages : [];
        foreach ($packages as $package) {
            if (!empty($package['file_path'])) {
                $key = ltrim(str_replace($base, '', $package['file_path']), '/');
                $err = $bucketManager->delete($bucket, $key);
            }
        }
        return $num;
    }


}
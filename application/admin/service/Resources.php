<?php

namespace app\admin\service;

use bxkj_module\service\Service;
use bxkj_module\service\Tree;
use Qiniu\Auth;
use think\Db;

class Resources extends Service
{
    protected $db;

    //获取总记录数
    public function getTotal($get)
    {
        $this->db = Db::name('resources');
        $this->setWhere($get);
        return $this->db->count();
    }

    //获取列表
    public function getList($get, $offset, $length)
    {
        $this->db = Db::name('resources');
        $this->setWhere($get)->setOrder($get);
        $result = $this->db->limit($offset, $length)->select();
        foreach ($result as &$item) {
            $category = Db::name('category')->field('pid,name')->where('id',$item['cat_id'])->find();
            $pcate_name = Db::name('category')->field('pid,name')->where('id',$category['pid'])->value('name');
            $item['cat_name'] = $pcate_name.'-'.$category['name'];
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
        if ($get['type'] != '') {
            $where[] = ['type', '=', $get['type']];
        }
        if ($get['hot'] != '') {
            $where[] = ['hot', '=', $get['hot']];
        }
        if ($get['new'] != '') {
            $where[] = ['new', '=', $get['new']];
        }
        if ($get['cat_id'] != '') {
            $where[] = ['cat_id', '=', $get['cat_id']];
        }
        $this->db->setKeywords(trim($get['keyword']),'','number id','title,name,number id');
        $this->db->where($where);
        return $this;
    }

    //设置排序规则
    private function setOrder($get)
    {
        $order = array();
        if (empty($get['sort'])) {
            $order['create_time'] = 'desc';
        }
        $this->db->order($order);
        return $this;
    }

    public function add($inputData)
    {
        Service::startTrans();
        $data = $this->df->process('add@resources', $inputData)->output();
        if ($data === false) return $this->setError($this->df->getError());
        $id = Db::name('resources')->insertGetId($data);
        if (!$id) return $this->setError('新增失败');
        // if (!empty($data['file_url'])) {
        //     $res = $this->updateFileInfo($id, $data['file_url']);
        //     if (!$res) return false;
        // }
        Service::commit();
        return $id;
    }

    public function validateCategoryId($value, $rule, $data = null, $more = null)
    {
        $treeModel = new Tree('category');
        $pathArr = $treeModel->getParentPath($value, 'resources_types', true);
        return !empty($pathArr);
    }

    public function fillPCatId($value, $rule, $data = null, $more = null)
    {
        if (empty($data['pcat_id'])) {
            $result = Db::name('category')->where(['id' => $value])->find();
            if (!empty($result)) {
                return ['pcat_id' => $result['pid']];
            }
        }
    }

    public function update($inputData)
    {
        $data = $this->df->process('update@resources', $inputData)->output();
        if ($data === false) return $this->setError($this->df->getError());
        $num = Db::name('resources')->where(array('id' => $data['id']))->update($data);
        if (!$num) return $this->setError('更新失败');
        return $num;
    }

    public function delete($ids)
    {
        $ids = is_array($ids) ? $ids : explode(',', $ids);
        if (empty($ids)) return $this->setError('请选择资源');
        $total = 0;
        //删除七牛云资源
        $accessKey = config('upload.platform_config.access_key');
        $secretKey = config('upload.platform_config.secret_key');
        $bucket = config('upload.platform_config.bucket');
        $base = config('upload.platform_config.base_url');
        
        $auth = new Auth($accessKey, $secretKey);
        $config = new \Qiniu\Config();
        $bucketManager = new \Qiniu\Storage\BucketManager($auth, $config);
        
        foreach ($ids as $id) {
            Service::startTrans();
            $resources = Db::name('resources')->where('id',$id)->find();
            $num = Db::name('resources')->where('id',$id)->delete();
            if (!$num) {
                Service::rollback();
                continue;
            }
            $key = ltrim(str_replace($base, '', $resources['file_url']), '/');
            $err = $bucketManager->delete($bucket, $key);
            Service::commit();
            $total++;
        }
        if (!$total) return $this->setError('删除资源失败');
        return $total;
    }

    protected function updateFileInfo($id, $file_url)
    {
        $accessKey = config('upload.platform_config.access_key');
        $secretKey = config('upload.platform_config.secret_key');
        $bucket = config('upload.platform_config.bucket');
        $base = config('upload.platform_config.base_url');
        $key = ltrim(str_replace($base, '', $file_url), '/');
        $auth = new Auth($accessKey, $secretKey);
        $config = new \Qiniu\Config();
        $bucketManager = new \Qiniu\Storage\BucketManager($auth, $config);
        list($fileInfo, $err) = $bucketManager->stat($bucket, $key);
        if ($err) return $this->setError('获取资源包信息错误' . $err->message());
        $num = Db::name('resources')->where('id', $id)->update(['file_size' => $fileInfo['fsize']]);
        if (!$num) return $this->setError('更新资源包信息错误');
        return $fileInfo;
    }

}
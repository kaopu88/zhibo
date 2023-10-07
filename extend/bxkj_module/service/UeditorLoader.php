<?php

namespace bxkj_module\service;

use Qiniu\Auth as QiniuAuth;
use Qiniu\Storage\BucketManager;
use think\Db;
use think\facade\Env;
use think\facade\Request;

class UeditorLoader
{
    //获取配置
    public function getConfig($name)
    {
        $path = explode('.', $name);
        return config('upload.' . $name);
    }

    //获取当前登录的用户ID
    public function getUid()
    {
    }

    public function getInput($key = '')
    {
        return input($key);
    }

    public function getRootPath()
    {
        return Env::get('root_path');
    }

    public function getFiles($path)
    {
        $accessKey = config('upload.platform_config.access_key');
        $secretKey = config('upload.platform_config.secret_key');
        $bucket = config('upload.platform_config.bucket');
        $auth = new QiniuAuth($accessKey, $secretKey);
        $bucketManager = new BucketManager($auth);
        // 要列取文件的公共前缀
        $prefix = $path;
        // 上次列举返回的位置标记，作为本次列举的起点信息。
        $marker = '';
        // 本次列举的条目数
        $limit = 100;
        $delimiter = '/';
        // 列举文件
        list($ret, $err) = $bucketManager->listFiles($bucket, $prefix, $marker, $limit, $delimiter);
        if ($err !== null) {
            return [];
        } else {
            $files = [];
            $qiniu_base = config('upload.platform_config.base_url');
            if (strpos($qiniu_base, ','))
            {
                @list($base_url, ) = explode(',', $qiniu_base);
            }
            else{
                $base_url = $qiniu_base;
            }
            foreach ($ret['items'] as $item) {
                $tmp['url'] = $base_url . '/' . $item['key'];
                $tmp['mtime'] = $item['putTime'] / 1000;
                $files[] = $tmp;
            }
            return $files;
        }
    }
}
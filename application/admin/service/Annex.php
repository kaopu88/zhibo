<?php


namespace app\admin\service;


use bxkj_module\service\Service;
use Qiniu\Auth;

class Annex extends Service
{
    protected $upload_config;

    public function __construct()
    {
        parent::__construct();
        $platform = config('upload.platform');
        $storage = config('upload.platform_config');
        $this->upload_config = $storage;
        $this->upload_config['platform'] = $platform;
    }

    public function getLists($prefix='',$marker='')
    {
        $data = [];
        if( $this->upload_config['platform'] == 'qiniu' && $this->upload_config['root_path']){
            $accessKey = $this->upload_config['access_key'];
            $secretKey = $this->upload_config['secret_key'];
            $bucket = $this->upload_config['bucket'];
            $base = $this->upload_config['base_url'];
            $root = $this->upload_config['root_path'];

            $auth = new Auth($accessKey, $secretKey);
            $config = new \Qiniu\Config();
            $bucketManager = new \Qiniu\Storage\BucketManager($auth, $config);

            if( empty($prefix) ) $prefix = $root;

            // 本次列举的条目数
            $limit = 1000;
            $delimiter = '/';
            $fileList = [];
            $prefixes = [];
            $prefixeList = [];
            do {
                list($ret, $err) = $bucketManager->listFiles($bucket, $prefix, $marker, $limit, $delimiter);

                if ($err !== null) {
                    echo "\n====> list file err: \n";
                    var_dump($err);
                } else {
                    $marker = null;
                    if (array_key_exists('marker', $ret)  ) {
                        $marker = $ret['marker'];
                    }
                    if( count($ret['items']) > 0 ){
                        $fileList = array_merge($fileList,$ret['items']);
                    }
                    if (array_key_exists('commonPrefixes', $ret)) {
                        $prefixes = array_merge($prefixes,$ret['commonPrefixes']);
                    }
                }
            } while (!empty($marker));

            if( !empty($prefixes) ){
                foreach ( $prefixes as $val ){
                    $arr = explode('/',rtrim($val,'/'));
                    $prefixeList[urlencode($val)] =  $arr[count($arr)-1];
                }
            }

            $prefixeList = array_reverse($prefixeList);

            if( $prefix != $root && !empty($prefix) ){
                $parent = substr($prefix, 0, strrpos(rtrim($prefix,'/'),'/')) . '/';
                $prefixeList[urlencode($parent)] = '返回上级';
            }

            $prefixeList = array_reverse($prefixeList);

            foreach ($fileList as &$file){
                $file['is_img'] = strstr($file['mimeType'],'image') === false ? 0 : 1;
            }

            $data = [
                'fileList' => $fileList,
                'prefixes' => $prefixeList,
                'base' => $base,
            ];
        }

        return $data;
    }

    public function down($fileName, $size=0)
    {
        $base = '';

        if( $this->upload_config['platform'] == 'qiniu' ) $base = $this->upload_config['base_url'];

        $file = $base . '/' . $fileName;
        ob_end_clean();
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Length: ' . $size);
        header('Content-Disposition: attachment; filename=' . basename($file));
        readfile($file);
    }

    public function delFile($fileName)
    {

        if( $this->upload_config['platform'] == 'qiniu' )
        {
            $accessKey = $this->upload_config['access_key'];
            $secretKey = $this->upload_config['secret_key'];
            $bucket = $this->upload_config['bucket'];
            $auth = new Auth($accessKey, $secretKey);
            $config = new \Qiniu\Config();
            $bucketManager = new \Qiniu\Storage\BucketManager($auth, $config);

            $result = $bucketManager->delete($bucket,$fileName);

            if( $result != null ){
                return false;
            }

            return true;
        }

    }
}
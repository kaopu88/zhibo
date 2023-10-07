<?php

namespace app\admin\service;

use bxkj_common\RedisClient;
use bxkj_module\service\Service;
use GuzzleHttp\Client;
use think\Db;

class SysConfig extends Service
{
    public function resetConfig($debug=false)
    {
        $result = Db::name('sys_config')->select();

        if (empty($result)) return true;

        $data = [];

        foreach ($result as $value)
        {
            $config = json_decode($value['value'], true);
            if (empty($config)) continue;
            if (array_key_exists($value['classified'], $data))
                $data[$value['classified']] = array_merge($data[$value['classified']], $config);
            else
                $data[$value['classified']] = $config;
        }

        foreach ($data as $key => $value)
        {
            if ($key == 'app') $this->initAppConfig($value['system_deploy']);

            $path = ROOT_PATH.'config'.DIRECTORY_SEPARATOR.RUNTIME_ENVIROMENT.DIRECTORY_SEPARATOR.$key.'.php';

            try{
                file_exists($path) && unlink($path);

                file_put_contents($path, "<?php  \r\n return " . var_export($value, true) . ";", LOCK_EX);
            }
            catch (\Exception $e) {
                exit($e->getMessage());
            }
        }

        return true;
    }

    protected function initAppConfig(&$data)
    {
        $config = [
            'core_service_url' => 'core',
            'api_service_url' => 'api',
            'h5_service_url' => 'h5',
            'agent_service_url' => 'agent',
            'push_service_url' => 'push',
            'node_service_url' => 'node',
            'recharge_service_url' => 'recharge',
            'promoter_service_url' => 'promoter',
            'erp_service_url' => 'admin',
        ];

        foreach ($data as $key => &$value)
        {
            if (array_key_exists($key, $config))
            {
                $value = ltrim($value, '/');

                if (empty($value)) $value = 'http://'.$_SERVER['HTTP_HOST'];

                $value .= '/'.$config[$key];
            }
        }
    }

    protected function localDel($dir)
    {
        static $i=0;
        static $arr=[];
        $files = isset($dir) ? array_diff(scandir($dir),array('..','.')) : [];
        foreach ($files as $file) {
            if( is_dir($dir . DIRECTORY_SEPARATOR . $file) ){
                $this->localDel($dir . DIRECTORY_SEPARATOR . $file);
            } else if (pathinfo($file, PATHINFO_EXTENSION) === 'php') {
                $filename = $dir . DIRECTORY_SEPARATOR . $file;
                $i++;
                $arr[] = $filename;
                 unlink($filename);
            }
        }
        return $arr;
    }

    public function addConfig($data)
    {
        $res = Db::name('sys_config')->insertGetId($data);
        return $res;
    }

    public function getConfig($name)
    {
        $config = Db::name('sys_config')->where(["mark" => $name])->find();
        return $config;
    }

    public function updateConfig($where, $data)
    {
        $status = Db::name('sys_config')->where($where)->update($data);
        return $status;
    }
}
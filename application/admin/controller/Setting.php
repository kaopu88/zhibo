<?php

namespace app\admin\controller;

use app\admin\service\DataVersion;
use app\admin\service\RedisCache;
use think\facade\Env;
use think\facade\Request;

class Setting extends Controller
{


    public function index()
    {
        return $this->fetch();
    }

    public function sync()
    {
        return $this->fetch();
    }

    public function update_data_version()
    {
        $this->checkAuth('admin:setting:data_update');
        $dataVersion = new DataVersion();
        $target = input('to');
        if ($target == 'last') $this->error('已经是最新版本');
        $table = input('type');
        $result = $dataVersion->sync($target, $table);
        if (!$result) $this->error($dataVersion->getError());
        $dataVersion->updateToLast($table);
        return $this->success('更新成功', ['version' => 'last']);
    }

    public function clear_runtime()
    {
        $this->checkAuth('admin:setting:clear_runtime');
        $runtimePath = Env::get('runtime_path');
        if (Request::isGet()) {
            $result = $this->getDirectorySize($runtimePath);
            $result['size'] = format_bytes($result['size']);
            $this->assign('_info', $result);
            $this->assign('RUNTIME_PATH', $runtimePath);
            return $this->fetch();
        } elseif (Request::isPost()) {
            $tmp = array();
            del_dir(rtrim($runtimePath, '/\\'), false, $tmp);
            $this->assign('_list', $tmp);
            $contents = $this->fetch('clear_detailed');
            alog("system.setting.clear_runtime", '清除文件缓存');
            $this->success('清除成功', $contents);
        }
    }

    public function find_redis_runtime()
    {
        $this->checkAuth('admin:setting:clear_runtime');
        $prefix = input('prefix');
        if (empty($prefix)) $this->error('前缀不能为空');
        $redisCache = new RedisCache($prefix);
        $result = $redisCache->scan();
        $this->success('获取成功', $result);
    }

    public function clear_redis_callback()
    {
        $sign = input('sign');
        if (!is_sign($sign, input())) exit('sign error');
        $type = input('type');
        $prefix = input('prefix');
        if (empty($prefix)) exit('prefix error');
        $redisCache = new RedisCache($prefix);
        $result = $redisCache->clear($type);
        if (!$result) {
            $err = $redisCache->getError();
            echo 'error:' . ((string)$err);
        } else {
            echo 'success';
        }
        exit();
    }

    public function clear_redis_runtime()
    {
        $this->checkAuth('admin:setting:clear_runtime');
        $type = strtolower(input('type'));
        $prefix = input('prefix');
        if (empty($prefix)) $this->error('前缀不能为空');
        $redisCache = new RedisCache($prefix);
        $result = $redisCache->start($type ? $type : '');
        if (!$result) $this->error($redisCache->getError());
        alog("system.setting.clear_redis", '清除Redis缓存');
        $this->success('清空成功');
    }

    //目录大小、文件夹数量、文件数量
    private function getDirectorySize($path)
    {
        $totalsize = 0;
        $totalcount = 0;
        $dircount = 0;
        if ($handle = opendir($path)) {
            while (false !== ($file = readdir($handle))) {
                $nextpath = $path . '/' . $file;
                if ($file != '.' && $file != '..' && !is_link($nextpath)) {
                    if (is_dir($nextpath)) {
                        $dircount++;
                        $result = $this->getDirectorySize($nextpath);
                        $totalsize += $result['size'];
                        $totalcount += $result['count'];
                        $dircount += $result['dircount'];
                    } elseif (is_file($nextpath)) {
                        $totalsize += filesize($nextpath);
                        $totalcount++;
                    }
                }
            }
        }
        closedir($handle);
        $total['size'] = $totalsize;
        $total['count'] = $totalcount;
        $total['dircount'] = $dircount;
        return $total;
    }

    public function test_user()
    {
        if(Request::isGet()){
            return $this->fetch();
        }else{
        }
    }

    public function clear_conf()
    {
        $ser = new \app\admin\service\SysConfig();
        $result = $ser->resetConfig();
        alog("system.setting.clear_conf", '重新生成配置');
        $this->success('生成成功',$result);
    }
}

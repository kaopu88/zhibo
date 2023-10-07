<?php

namespace app\h5\controller;

use app\h5\service\Packages;
use think\Db;

class Download extends Controller
{
   
	public function index()
    {
        $HTTP_USER_AGENT = isset($HTTP_USER_AGENT) ? $HTTP_USER_AGENT : $_SERVER['HTTP_USER_AGENT'];
        $info = user_agent($HTTP_USER_AGENT);
        $browser = ['chrome' => 'android', 'safari' => 'ios', 'micromessenger' => 'weixin'];
        $packagesService = new Packages();
        $commonPackage = $packagesService->getLastPackage('android', 'common', '');
        $qqPackage = $packagesService->getLastPackage('android', 'qq', '');
        $applePackage = $packagesService->getLastPackage('ios', 'common', '');
        $config = [
            'id' => 0,
            'url' => '',
            'version' => '暂无',
            'file_path' => '',
            'descr' => '',
            'filesize_str' => ''
        ];
        //var_dump($commonPackage);
        $this->assign('self_url', 'http://'. $_SERVER['HTTP_HOST'] . '/h5/download.html');
        $this->assign('ios', array_merge($config, is_array($applePackage) ? $applePackage : []));
        $this->assign('qq', array_merge($config, is_array($qqPackage) ? $qqPackage : []));
        $this->assign('android', array_merge($config, is_array($commonPackage) ? $commonPackage : []));
        $access_os = array_key_exists($info['browser'], $browser) ? $browser[$info['browser']] : 'chrome';
        $this->assign('browseros', $access_os);
        return $this->fetch();
    }
    
    
    public function incr()
    {
        $id = input('id');
        if (empty($id)) return json_error('统计失败');
        $num = Db::name('packages')->where('id', $id)->setInc('download_num', 1);
        if (!$num) return json_error('统计失败');
        return json_success('统计成功');
    }
}

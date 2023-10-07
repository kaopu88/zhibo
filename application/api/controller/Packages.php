<?php

namespace app\api\controller;

use app\common\controller\Controller;
use app\api\service\Packages AS PackagesService;
use think\Db;

class Packages extends Controller
{
    //统计下载量
    public function incrDownloadNum()
    {
        $id = request()->param('id');
        if (empty($id)) return $this->jsonError('安装包ID不能为空');
        $packagesService = new PackagesService();
        $res = $packagesService->incrDownloadNum($id, 1);
        if (!$res) return $this->jsonError($packagesService->getError());
        return $this->success(array('res' => $res));
    }

    public function getLastPackage()
    {
        $params = request()->param();
        list($params['os'], $code) = explode('_', APP_V);
        $params['os'] = strtolower($params['os']);
        if ($params['os'] == 'ios') {
            //IOS调用检查版本更新，因为强制更新问题，曹华辉需要暂时返回错误
            return $this->jsonError('404');
        }
        $channel = empty($params['channel']) ? 'common' : $params['channel'];
        $channel = 'common';
        $packagesService = new PackagesService();

        if ($params['os'] == 'android')
        {
            $lastVersion = $packagesService->getLastVersion(array(
                'channel' => $channel,
                'os' => $params['os']
            ));
            //if ($lastVersion) $lastVersion['update_type'] = '2';
        }
        else {
            $lastVersion = $packagesService->getLastVersion(array(
                'channel' => $channel,
                'os' => $params['os']
            ));
        }
        $packages = Db::name('packages')->where(array('os'=>$params['os'],'channel'=>$channel,'min_version'=>'1'))->find();
        //if ($packages && $code <= $packages['code']) $lastVersion['update_type'] = '2';
        if (is_error($lastVersion)) return $this->jsonError($packagesService->getError());
        return $this->success($lastVersion ? $lastVersion : []);
    }
}

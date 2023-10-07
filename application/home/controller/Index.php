<?php

namespace app\home\controller;

use bxkj_module\service\Packages;
use think\Db;
use think\facade\Request;

class Index extends Controller
{

    public function __construct()
    {
        parent::__construct();

        $siteConfig = config('site.');
        $this->assign('site', $siteConfig);
    }

    public function index()
    {
        $re = request()->isMobile();
        $packagesService = new Packages();
        $commonPackage = $packagesService->getLastPackage('android', 'common', '');

        $config = [
            'id' => 0,
            'url' => '',
            'version' => '暂无',
            'file_path' => '',
            'descr' => '',
            'filesize_str' => ''
        ];
        $this->assign('android', array_merge($config, is_array($commonPackage) ? $commonPackage : []));

        $down_url = H5_URL . '/download.html';
        $this->assign('down_url', $down_url);

        if( $re ){
            return $this->fetch('index_wap');
        } else {
            return $this->fetch();
        }

    }

    public function artInfo()
    {
        $mark = input('mark');
        $id = input('id');
        $where = ['status' => '1'];
        if (!empty($mark)) {
            $where['mark'] = $mark;
        } else {
            $where['id'] = $id;
        }
        $info = Db::name('article')->where($where)->find();
   
        if (empty($info)) $this->error('详情内容为空');
//        if (!empty($info['url'])) return $this->redirect($info['url']);
        $this->assign('_info',$info);
        return $this->fetch();
    }

    public function auth()
    {
        $host = config('app.system_deploy')["erp_service_url"];
        $this->assign('host', $host);
        return $this->fetch();
    }

    public function pddauth()
    {
        if (Request::isPost()) {
            $data['access_token'] = input("access_token");
            $data['refresh_token'] = input("refresh_token");
            $data['expires_in'] = time() + input("expires_in");
            $data = json_encode($data);
            if(Db::name('sys_config')->where(['mark'=>'pdd_auth'])->find()){
                $result = Db::name('sys_config')->where(['mark'=>'pdd_auth'])->update(['value'=>$data]);
            }else{
                $result = Db::name('sys_config')->insert(['mark'=>'pdd_auth', 'classified'=>'taoke', 'value'=>$data]);
            }
            if( $result !== false){
                $ser = new \app\admin\service\SysConfig();
                $ser->resetConfig();
            }
            return $this->success('授权成功');
        }else{

            return $this->fetch();
        }
    }

    public function contactus()
    {
        $info = Db::name('article')->where(['mark' => 'contactus'])->find();
        $this->assign('_info',$info);
        return $this->fetch();
    }

    public function aboutus()
    {
        $info = Db::name('article')->where(['mark' => 'aboutus'])->find();
        $this->assign('_info',$info);
        return $this->fetch();
    }
}

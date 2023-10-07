<?php
/**
 * Created by PhpStorm.
 * User: 李成
 * Date: 2020/5/22
 * Time: 11:38
 */
namespace app\taoke\controller;

use think\facade\Request;

class App extends Controller
{
    public function index()
    {
        $this->checkAuth('taoke:app:index');
        $ser = new \app\admin\service\SysConfig();
        if (Request::isGet()) {
            $info = $ser->getConfig("app_config");
            $info = json_decode($info['value'], true);
            $this->assign('_info', $info);
        }else{
            $post = json_encode(input());
            if($ser->getConfig("app_config")) {
                $result = $ser->updateConfig(['mark' => 'app_config'], ['value' => $post]);
            }else{
                $result = $ser->addConfig(['mark' => 'app_config', 'classified'=>'taoke', 'value' => $post]);
            }
            if( $result > 0 ){
                $ser->resetConfig();
            }
            alog("taoke.app_setting.index", "编辑淘客首页设置");
            $this->success('更新成功', $result);
        }
        return $this->fetch();
    }

    public function base()
    {
        $this->checkAuth('taoke:app:base');
        $ser = new \app\admin\service\SysConfig();
        if (Request::isGet()) {
            $info = $ser->getConfig("app_base_config");
            $info = json_decode($info['value'], true);
            $this->assign('_info', $info);
        }else{
            $post = json_encode(input());
            if($ser->getConfig("app_base_config")) {
                $result = $ser->updateConfig(['mark' => 'app_base_config'], ['value' => $post]);
            }else{
                $result = $ser->addConfig(['mark' => 'app_base_config', 'classified'=>'taoke', 'value' => $post]);
            }
            if( $result > 0 ){
                $ser->resetConfig();
            }
            alog("taoke.app_setting.base", "编辑淘客基础设置");
            $this->success('更新成功', $result);
        }
        return $this->fetch();
    }

    public function share()
    {
        $this->checkAuth('taoke:app:share');
        $ser = new \app\admin\service\SysConfig();
        if (Request::isGet()) {
            $info = $ser->getConfig("app_share_config");
            $info = json_decode($info['value'], true);
            $this->assign('_info', $info);
        }else{
            $post = json_encode(input());
            if($ser->getConfig("app_share_config")) {
                $result = $ser->updateConfig(['mark' => 'app_share_config'], ['value' => $post]);
            }else{
                $result = $ser->addConfig(['mark' => 'app_share_config', 'classified'=>'taoke', 'value' => $post]);
            }
            if( $result > 0 ){
                $ser->resetConfig();
            }
            alog("taoke.app_setting.share", "编辑淘客分享设置");
            $this->success('更新成功', $result);
        }
        return $this->fetch();
    }

    public function kuaizhan()
    {
        $this->checkAuth('taoke:app:kuaizhan');
        $ser = new \app\admin\service\SysConfig();
        if (Request::isGet()) {
            $info = $ser->getConfig("app_kz_config");
            $info = json_decode($info['value'], true);
            $this->assign('_info', $info);
        }else{
            $post = json_encode(input());
            if($ser->getConfig("app_kz_config")) {
                $result = $ser->updateConfig(['mark' => 'app_kz_config'], ['value' => $post]);
            }else{
                $result = $ser->addConfig(['mark' => 'app_kz_config', 'classified'=>'taoke', 'value' => $post]);
            }
            if( $result > 0 ){
                $ser->resetConfig();
            }
            alog("taoke.app_setting.kuaizhan", "编辑淘客快站设置");
            $this->success('更新成功', $result);
        }
        return $this->fetch();
    }

    public function template()
    {
        $this->checkAuth('taoke:app:template');
        $ser = new \app\admin\service\SysConfig();
        if (Request::isGet()) {
            $info = $ser->getConfig("push_template");
            $info = json_decode($info['value'], true);
            $this->assign('_info', $info);
        }else{
            $post = json_encode(input());
            if($ser->getConfig("push_template")) {
                $result = $ser->updateConfig(['mark' => 'push_template'], ['value' => $post]);
            }else{
                $result = $ser->addConfig(['mark' => 'push_template', 'classified'=>'taoke', 'value' => $post]);
            }
            if( $result > 0 ){
                $ser->resetConfig();
            }
            alog("taoke.app_setting.template", "编辑淘客推送消息模版设置");
            $this->success('更新成功', $result);
        }
        return $this->fetch();
    }
}
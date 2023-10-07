<?php
/**
 * Created by PhpStorm.
 * User: 李成
 * Date: 2020/4/27
 * Time: 11:57
 */
namespace app\taoke\controller;

use think\facade\Request;
use think\Db;

class Config extends Controller
{
    /**
     * 淘客相关设置
     * @return mixed
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function index()
    {
        $this->checkAuth('taoke:config:index');

        $ser = new \app\admin\service\SysConfig();
        if (Request::isGet()) {
            $info = $ser->getConfig("taoke");
            $info = json_decode($info['value'], true);
            $this->assign('_info', $info);

            $tbInfo = $ser->getConfig("taoke_auth");
            $tbInfo = json_decode($tbInfo['value'], true);
            $this->assign('taobao_auth', $tbInfo);
            $leftAuthTime = intval(($tbInfo['expires_in'] - time()) / 86400);
            if($leftAuthTime < 0){
                $tbStrDesc = "已过期";
            }else{
                $tbStrDesc = $leftAuthTime."天后过期";
            }
            $this->assign('tb_auth_desc', $tbStrDesc);

            $pddInfo = $ser->getConfig("pdd_auth");
            $pddInfo = json_decode($pddInfo['value'], true);
            $this->assign('pdd_auth', $pddInfo);
            $leftAuthTime = intval(($pddInfo['expires_in'] - time()) / 86400);
            if($leftAuthTime < 0){
                $pddStrDesc = "已过期";
            }else{
                $pddStrDesc = $leftAuthTime."天后过期";
            }
            $this->assign('pdd_auth_desc', $pddStrDesc);

            $this->assign('api_service_url', config('app.live_setting')['service_host']);

        }else{
            $post = json_encode(input());
            if($ser->getConfig("taoke")) {
                $result = $ser->updateConfig(['mark' => 'taoke'], ['value' => $post]);
            }else{
                $result = $ser->addConfig(['mark' => 'taoke', 'classified'=>'taoke', 'value' => $post]);
            }
            if( $result > 0 ){
                $ser->resetConfig();
            }
            alog("taoke.setting.index", "编辑淘客基础设置");
            $this->success('更新成功', $result);
        }
        return $this->fetch();
    }

    /**
     * 设置淘宝授权
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function setTaobaoAuth()
    {
        if (Request::isPost()) {
            $data['access_token'] = input("access_token");
            $data['refresh_token'] = input("refresh_token");
            $data['expires_in'] = time() + input("expires_in");
            $data = json_encode($data);
            if(Db::name('sys_config')->where(['mark'=>'taoke_auth'])->find()){
                $result = Db::name('sys_config')->where(['mark'=>'taoke_auth'])->update(['value'=>$data]);
            }else{
                $result = Db::name('sys_config')->insert(['mark'=>'taoke_auth', 'classified'=>'taoke', 'value'=>$data]);
            }
            if( $result !== false){
                $ser = new \app\admin\service\SysConfig();
                $ser->resetConfig();
            }
            alog("taoke.setting.tb_auth", "更新淘宝授权");
            return $this->success('授权成功');
        }
    }

    /**
     * 设置拼多多授权
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function setPinduoduoAuth()
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
            alog("taoke.setting.pdd_auth", "更新拼多多授权");
            return $this->success('授权成功');
        }
    }

    /**
     * 渠道设置
     * @return mixed
     */
    public function channel()
    {
        $this->checkAuth('taoke:config:channel');

        $ser = new \app\admin\service\SysConfig();
        if (Request::isGet()) {
            $info = $ser->getConfig("channel");
            $info = json_decode($info['value'], true);
            $this->assign('_info', $info);
        }else{
            $post = input();
            $post['tb_order_time'] = strtotime(input('tb_order_time'));
            $post['pdd_order_time'] = strtotime(input('pdd_order_time'));
            $post['jd_order_time'] = strtotime(input('jd_order_time'));
            if($ser->getConfig("channel")) {
                $result = $ser->updateConfig(['mark' => 'channel'], ['value' => json_encode($post)]);
            }else{
                $result = $ser->addConfig(['mark' => 'channel', 'classified'=>'taoke', 'value' => json_encode($post)]);
            }
            if( $result > 0 ){
                $ser->resetConfig();
            }
            alog("taoke.setting.channel", "编辑渠道设置");
            $this->success('更新成功', $result);
        }
        return $this->fetch();
    }

    /**
     * 搜索设置
     * @return mixed
     */
    public function search()
    {
        $this->checkAuth('taoke:config:search');

        $ser = new \app\admin\service\SysConfig();
        if (Request::isGet()) {
            $info = $ser->getConfig("search");
            $info = json_decode($info['value'], true);
            $this->assign('_info', $info);
        }else{
            $post = json_encode(input());
            if($ser->getConfig("search")) {
                $result = $ser->updateConfig(['mark' => 'search'], ['value' => $post]);
            }else{
                $result = $ser->addConfig(['mark' => 'search', 'classified'=>'taoke', 'value' => $post]);
            }
            if( $result > 0 ){
                $ser->resetConfig();
            }
            alog("taoke.setting.search", "编辑超级搜索设置");
            $this->success('更新成功', $result);
        }
        return $this->fetch();
    }

    /**
     * 其他设置
     * @return mixed
     */
    public function other()
    {
        $this->checkAuth('taoke:config:other');

        $ser = new \app\admin\service\SysConfig();
        if (Request::isGet()) {
            $info = $ser->getConfig("other");
            $info = json_decode($info['value'], true);
            $this->assign('_info', $info);
        }else{
            $post = json_encode(input());
            if($ser->getConfig("other")) {
                $result = $ser->updateConfig(['mark' => 'other'], ['value' => $post]);
            }else{
                $result = $ser->addConfig(['mark' => 'other', 'classified'=>'taoke', 'value' => $post]);
            }
            if( $result > 0 ){
                $ser->resetConfig();
            }
            alog("taoke.setting.other", "编辑其他设置");
            $this->success('更新成功', $result);
        }
        return $this->fetch();
    }

    /**
     * 多麦设置
     * @return mixed
     */
    public function duomai()
    {
        $this->checkAuth('taoke:config:duomai');

        $ser = new \app\admin\service\SysConfig();
        if (Request::isGet()) {
            $info = $ser->getConfig("duomaiAds");
            $info = json_decode($info['value'], true);
            $this->assign('_info', $info);
        }else{
            $post = json_encode(input());
            if($ser->getConfig("duomaiAds")) {
                $result = $ser->updateConfig(['mark' => 'duomaiAds'], ['value' => $post]);
            }else{
                $result = $ser->addConfig(['mark' => 'duomaiAds', 'classified'=>'taoke', 'value' => $post]);
            }
            if( $result > 0 ){
                $ser->resetConfig();
            }
            alog("taoke.setting.duomai", "编辑多麦设置");
            $this->success('更新成功', $result);
        }
        return $this->fetch();
    }

    /**
     * 基础分销设置
     * @return mixed
     */
    public function distribute()
    {
        $this->checkAuth('taoke:config:distribute');

        $ser = new \app\admin\service\SysConfig();
        if (Request::isGet()) {
            $info = $ser->getConfig("distribute");
            $info = json_decode($info['value'], true);
            $this->assign('_info', $info);
        }else{
            $post = json_encode(input());
            if($ser->getConfig("distribute")) {
                $result = $ser->updateConfig(['mark' => 'distribute'], ['value' => $post]);
            }else{
                $result = $ser->addConfig(['mark' => 'distribute', 'classified'=>'taoke', 'value' => $post]);
            }
            if( $result > 0 ){
                $ser->resetConfig();
            }
            alog("taoke.setting.distribute", "编辑分销设置");
            $this->success('更新成功', $result);
        }
        return $this->fetch();
    }

    /**
     * 提现设置
     * @return mixed
     */
    public function withdraw()
    {
        $this->checkAuth('taoke:config:withdraw');

        $ser = new \app\admin\service\SysConfig();
        if (Request::isGet()) {
            $info = $ser->getConfig("withdraw");
            $info = json_decode($info['value'], true);
            $this->assign('_info', $info);
        }else{
            $post = json_encode(input());
            if($ser->getConfig("withdraw")) {
                $result = $ser->updateConfig(['mark' => 'withdraw'], ['value' => $post]);
            }else{
                $result = $ser->addConfig(['mark' => 'withdraw', 'classified'=>'taoke', 'value' => $post]);
            }
            if( $result > 0 ){
                $ser->resetConfig();
            }
            alog("taoke.setting.withdraw", "编辑提现设置");
            $this->success('更新成功', $result);
        }
        return $this->fetch();
    }

}
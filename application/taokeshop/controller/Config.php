<?php
/**
 * Created by PhpStorm.
 * User: 李成
 * Date: 2020/4/27
 * Time: 11:57
 */
namespace app\taokeshop\controller;

use think\facade\Request;
use app\admin\service\ExpLevel;
use app\admin\service\AnchorExpLevel;
use app\admin\service\SysConfig;

class Config extends Controller
{
    /**
     *小店
     * @return mixed
     */
    public function index()
    {
        $this->checkAuth('taokeshop:config:index');

        $anchorExpLevelService = new AnchorExpLevel();
        $artList = $anchorExpLevelService->getAll();
        $this->assign('archor_level_list', $artList);
        $expLevelService = new ExpLevel();
        $elsList = $expLevelService->getAll();
        $this->assign('user_level_list', $elsList);

        $ser = new SysConfig();
        if (Request::isGet()) {
            $info = $ser->getConfig("shop");
            $info = json_decode($info['value'], true);
            $this->assign('_info', $info);
        }else{
            $post = json_encode(input());
            if($ser->getConfig("shop")) {
                $result = $ser->updateConfig(['mark' => 'shop'], ['value' => $post]);
            }else{
                $result = $ser->addConfig(['mark' => 'shop', 'classified'=>'taoke', 'value' => $post]);
            }
            if( $result > 0 ){
                $ser->resetConfig();
            }
            alog("taokeshop.setting.edit", '编辑小店开通条件');
            $this->success('更新成功', $result);
        }
        return $this->fetch();
    }
}
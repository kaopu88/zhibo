<?php
namespace app\lottery\controller;

use think\facade\Request;

class Config extends Controller
{

    public function index()
    {
        $this->checkAuth('lottery:lottery:config');
        $ser = new \app\admin\service\SysConfig();
        if (Request::isGet()) {
            $info = $ser->getConfig("lottery");
            $info = json_decode($info['value'], true);
            $this->assign('_info', $info);
        }else{
            $post = json_encode(input());
            if($ser->getConfig("lottery")) {
                $result = $ser->updateConfig(['mark' => 'lottery'], ['value' => $post]);
            }else{
                $result = $ser->addConfig(['mark' => 'lottery', 'classified'=>'activity', 'value' => $post]);
            }
            if( $result > 0 ){
                $ser->resetConfig();
            }
            $this->success('更新成功', $result);
        }
        return $this->fetch();
    }

}
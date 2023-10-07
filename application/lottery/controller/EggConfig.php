<?php

namespace app\lottery\controller;

use think\facade\Request;

class EggConfig extends Controller
{
    public function index()
    {
        $this->checkAuth('lottery:egg_config:index');
        $sysConfigSevice = new \app\admin\service\SysConfig();
        if (Request::isGet()) {
            $info = $sysConfigSevice->getConfig("lottery_egg");
            $info = json_decode($info['value'], true);
            $this->assign('_info', $info);
        } else {
            $post = json_encode(input());
            if ($sysConfigSevice->getConfig("lottery_egg")) {
                $result = $sysConfigSevice->updateConfig(['mark' => 'lottery_egg'], ['value' => $post]);
            } else {
                $result = $sysConfigSevice->addConfig(['mark' => 'lottery_egg', 'classified' => 'activity', 'value' => $post]);
            }
            if ($result > 0) {
                $sysConfigSevice->resetConfig();
            }
            $this->success('更新成功', $result);
        }
        return $this->fetch();
    }
}
<?php

namespace app\admin\controller;

use bxkj_common\RedisClient;
use think\Db;
use think\facade\Request;

class RedPacket extends Controller
{
    public function config()
    {
        $this->checkAuth('admin:redpacket:config');
        $ser = new \app\admin\service\SysConfig();
        if (Request::isGet()) {
            $info = $ser->getConfig("red_packet");
            $info = json_decode($info['value'], true);
            $this->assign('_info', $info);
        } else {
            $red_packet_min = trim(input('red_packet_min'));
            $red_packet_max = trim(input('red_packet_max'));
            if ($red_packet_min > $red_packet_max) return $this->error('金额设置不合法');
            $post = json_encode(input());
            if ($ser->getConfig("red_packet")) {
                $result = $ser->updateConfig(['mark' => 'red_packet'], ['value' => $post]);
            } else {
                $result = $ser->addConfig(['mark' => 'red_packet', 'classified' => 'activity', 'value' => $post]);
            }
            if ($result > 0) {
                $ser->resetConfig();
            }

            $redis = new RedisClient();
            $redis->set('red_packet', $post);

            $this->success('更新成功', $result);
        }
        return $this->fetch();
    }

    //红包管理
    public function index()
    {
        $this->checkAuth('admin:redpacket:index');
        $get = input();
        $redPacket = new \app\admin\service\RedPacket();
        $total = $redPacket->getTotal($get);
        $page = $this->pageshow($total);
        $list = $redPacket->getList($get, $page->firstRow, $page->listRows);
        $this->assign('_list', $list);
        return $this->fetch();
    }
}
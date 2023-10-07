<?php

namespace app\admin\controller;

use bxkj_common\DateTools;
use bxkj_common\RedisClient;
use think\Db;
use think\facade\Request;

class Rank extends Controller
{
    public function contr()
    {
        $input = input();
        $this->assign('get', $input);
        $userId = $input['user_id'];
        $interval = $input['interval'];
        //粉丝贡献榜
        $rank = new \app\admin\service\Rank();
        $get = array(
            'interval' => $interval,
            'order' => 'desc'
        );
        $get['rnum'] = $input['rnum'];
        $get['name'] = "contr:total:{$userId}";
        $get['numkeys'] = ["contr:real:{$userId}", "contr:isvirtual:{$userId}"];

        $total = $rank->getTotal($get);
        $page = $this->pageshow($total);
        $contr_rank = $rank->getList($get, $page->firstRow, $page->listRows, array(
            'scoreKey' => 'millet',
            'memberKey' => 'user_id',
            'stealth' => 'rank_stealth',
            'stealth_exp' => [$userId]
        ));

        $redis=RedisClient::getInstance();
        if ($contr_rank['list'])
        {
            $rnum = str_replace('-','',$get['rnum']);
            foreach ($contr_rank['list'] as &$item)
            {
                $level = Db::name('exp_level')->field('levelname,icon')->where(array('levelid'=>$item['level']))->find();
                $item['level_icon'] = $level['icon'];
                $item['level_name'] = $level['levelname'];
                $name = "contr:isvirtual:{$userId}";
                $key = $rank->getIntervalKey($name, $interval, $rnum);
                $item['virtual_millet']=(int)$redis->zScore($key,$item['user_id']);
                $name2 = "contr:real:{$userId}";
                $key2 = $rank->getIntervalKey($name2, $interval, $rnum);
                $item['real_millet']=(int)$redis->zScore($key2,$item['user_id']);
                $item['phone'] = Db::name('user')->where(array('user_id'=>$item['user_id']))->value('phone');
            }
        }

        $timeRangerConfig = DateTools::getTimeRangerConfig();
        $this->assign('time_ranger_json', json_encode($timeRangerConfig));
        $this->assign('contr_rank', $contr_rank['list']);
        $this->assign('contr_rank_total', $contr_rank['total']);
        return $this->fetch();
    }

    public function charm()
    {
        $this->checkAuth('admin:rank:charm');
        $input = input();
        $this->assign('get', $input);
        $userId = $input['user_id'];
        $interval = $input['interval'];
        $rank = new \app\admin\service\Rank();
        $get = array(
            'name' => 'charm',
            'interval' => $interval,
            'order' => 'desc'
        );
        $get['rnum'] = $input['rnum'];
        $total = $rank->getTotal($get);
        $page = $this->pageshow($total);
        $result = $rank->getList($get, $page->firstRow, $page->listRows, array(
            'scoreKey' => 'millet',
            'memberKey' => 'user_id',
            'stealth' => 'rank_stealth',
            'stealth_exp' => [$userId]
        ));

        if ($result['list'])
        {
            foreach ($result['list'] as &$item)
            {
                $level = Db::name('exp_level')->field('levelname,icon')->where(array('levelid'=>$item['level']))->find();
                $item['level_icon'] = $level['icon'];
                $item['level_name'] = $level['levelname'];
                $item['phone'] = Db::name('user')->where(array('user_id'=>$item['user_id']))->value('phone');
            }
        }

        $timeRangerConfig = DateTools::getTimeRangerConfig();
        $this->assign('time_ranger_json', json_encode($timeRangerConfig));
        $this->assign('charm_rank', $result['list']);
        $this->assign('charm_rank_total', $result['total']);
        return $this->fetch();
    }

    public function heroes()
    {
        $this->checkAuth('admin:rank:heroes');
        $input = input();
        $this->assign('get', $input);
        $userId = $input['user_id'];
        $interval = $input['interval'];
        $rank = new \app\admin\service\Rank();
        $get = array(
            'name' => 'heroes:gift',
            'interval' => $interval,
            'order' => 'desc'
        );
        $get['rnum'] = $input['rnum'];
        $total = $rank->getTotal($get);
        $page = $this->pageshow($total);
        $result = $rank->getList($get, $page->firstRow, $page->listRows, array(
            'scoreKey' => 'millet',
            'memberKey' => 'user_id',
            'stealth' => 'rank_stealth',
            'stealth_exp' => [$userId]
        ));

        if ($result['list'])
        {
            foreach ($result['list'] as &$item)
            {
                $level = Db::name('exp_level')->field('levelname,icon')->where(array('levelid'=>$item['level']))->find();
                $item['level_icon'] = $level['icon'];
                $item['level_name'] = $level['levelname'];
                $item['phone'] = Db::name('user')->where(array('user_id'=>$item['user_id']))->value('phone');
            }
        }

        $timeRangerConfig = DateTools::getTimeRangerConfig();
        $this->assign('time_ranger_json', json_encode($timeRangerConfig));
        $this->assign('heroes_rank', $result['list']);
        $this->assign('heroes_rank_total', $result['total']);
        return $this->fetch();
    }

    public function millet_handler()
    {
        if (Request::isGet()) {
            $this->success('获取成功', input());
        } else {
            $rank = new \app\admin\service\Rank();
            $num = $rank->millet_handler(input());
            $this->success('变更成功');
        }
    }
}

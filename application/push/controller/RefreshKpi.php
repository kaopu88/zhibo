<?php

namespace app\push\controller;

use app\push\section\RefreshAgentKpi;
use app\push\section\RefreshPromoterKpi;
use app\push\section\RefreshAnchorKpi;
use bxkj_common\Console;
use bxkj_common\SectionManager;
use think\Db;

class RefreshKpi extends Api
{
    public function refresh_promoter_kpi()
    {
        $worker = new RefreshPromoterKpi();
        $manager = new SectionManager([
            'name' => 'refresh_promoter_kpi:' . date('Ymd'),
            'length' => 100,
            'exclusivity' => false,
            'thread' => 3
        ]);
        $manager->setSectionExecuter($worker)->start();
    }

    public function refresh_anchor_kpi()
    {
        $safety = input('safety');
        if ($safety != '1') {
            echo 'not safety';
        }
        $worker = new RefreshAnchorKpi();
        $manager = new SectionManager([
            'name' => 'refresh_anchor_kpi:' . date('Ymd'),
            'length' => 100,
            'exclusivity' => false,
            'thread' => 3
        ]);
        $manager->setSectionExecuter($worker)->start();
    }

    public function refresh_agent_kpi()
    {
        $safety = input('safety');
        if ($safety != '1') {
            echo 'not safety';
        }
        $worker = new RefreshAgentKpi();
        $manager = new SectionManager([
            'name' => 'refresh_agent_kpi:' . date('Ymd'),
            'length' => 100,
            'exclusivity' => false,
            'thread' => 3
        ]);
        $manager->setSectionExecuter($worker)->start();
    }
}

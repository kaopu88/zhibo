<?php

namespace app\agent\service;

use bxkj_common\DateTools;
use bxkj_common\RedisClient;
use bxkj_module\service\Service;

class Trend extends Service
{
    protected $redis;
    protected $prefix;

    public function __construct($prefix = 'trend:')
    {
        parent::__construct();
        $this->redis = RedisClient::getInstance();
        $this->prefix = $prefix;
    }

    //获取系列数据
    public function getSeriesData($seriesConfig, $start, $end, $unit)
    {
        $series = array();
        $nodes = DateTools::rangeNodes($start, $end, $unit);
        $xList = $this->getXList($nodes);
        foreach ($seriesConfig as $item) {
            $sha1 = sha1($item['mark'] . $item['member'] . $unit . $start . $end);
            $data = $this->getCache($sha1);
            if (empty($data)) {
                $data = $this->getYList($nodes, $item['mark'], $unit, $item['member']);
                $this->setCache($sha1, $data);
            }
            $item['data'] = $data;
            unset($item['mark']);
            $series[] = $item;
        }
        return array('series' => $series, 'xList' => $xList);
    }

    protected function getCache($sha1)
    {
        $redis = RedisClient::getInstance();
        $key = "kcache:trend:{$sha1}";
        $json = $redis->get($key);
        return $json ? json_decode($json, true) : null;
    }

    protected function setCache($sha1, $value)
    {
        $redis = RedisClient::getInstance();
        $key = "kcache:trend:{$sha1}";
        $redis->set($key, $value ? json_encode($value) : '', 600);//趋势图10分钟缓存
    }

    public function getData($mark, $start, $end, $unit)
    {
        $nodes = DateTools::rangeNodes($start, $end, $unit);
        $xList = $this->getXList($nodes);
        $yList = $this->getYList($nodes, $mark, $unit);
        return array('xList' => $xList, 'yList' => $yList);
    }

    //获取X轴数据
    private function getXList($nodes)
    {
        $xList = array();
        foreach ($nodes as $k => $value) {
            $xList[] = $value['name'];
        }
        return $xList;
    }

    //获取Y轴数据
    private function getYList($nodes, $mark, $unit, $member = null)
    {
        $yList = [];
        list($type, $series, $range) = explode(':', $mark);
        if ($type == 'agent') {
            $kpi = new AgentKpi();
        } else {
            $kpi = new PromoterKpi();
        }
        foreach ($nodes as $node) {
            $sum = 0;
            $num = $this->getUnitNum($unit, $node);
            if ($series == 'cons') {
                $sum = $kpi->getConsSum($member, $unit, $num, $range);
            }
            if ($series == 'millet' && $type == 'agent') {
                $sum = $kpi->getMilletSum($member, $unit, $num);
            }
            if($series == "recharge"){
                $sum = $kpi->getRechargeSum($member, $unit, $num);
            }
            $yList[] = $sum;
        }
        return $yList;
    }

    private function getUnitNum($unit, $node)
    {
        $num = '';
        switch ($unit) {
            case 'd':
                $num = $node['y'] . $node['m'] . $node['d'];
                break;
            case 'm':
                $num = $node['y'] . $node['m'];
                break;
            case 'y':
                $num = $node['y'];
                break;
            case 'f':
                $num = $node['f'];
                break;
        }
        return $num;
    }


}
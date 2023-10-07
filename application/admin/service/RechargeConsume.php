<?php

namespace app\admin\service;

use bxkj_common\DateTools;
use bxkj_common\RedisClient;
use bxkj_module\service\Service;

class RechargeConsume extends Service
{
    protected $redis;

    public function __construct()
    {
        parent::__construct();
        $this->redis = RedisClient::getInstance();
    }

    //获取充值消费系列数据
    public function getSeriesData($seriesConfig, $start, $end, $unit)
    {
        $series = array();
        $nodes = DateTools::rangeNodes($start, $end, $unit);
        $xList = $this->getXList($nodes);
        foreach ($seriesConfig as $item) {
            $sha1 = sha1($item['mark'] . $item['type'] . $unit . $start . $end);
            $data = $this->getCache($sha1);
            if (empty($data)) {
                $data = $this->getYList($nodes, $item['mark'], $item['type'], $unit);
                $this->setCache($sha1, $data);
            }
            $item['data'] = $data;
            unset($item['mark']);
            $series[] = $item;
        }
        return array('series' => $series, 'xList' => $xList);
    }

    //获取充值消费Y轴数据
    private function getYList($nodes, $mark, $type, $unit)
    {
        $yList = [];
        if ($mark == 'customer_recharge') {
            $service = new Recharge();
        } else if ($mark == 'customer_consume') {
            $service = new Kpi();
        }
        foreach ($nodes as $node) {
            $num = $this->getUnitNum($unit, $node);
            $sum = $service->getSum($unit, $num, $type);
            $yList[] = $sum;
        }
        return $yList;
    }

    protected function getCache($sha1)
    {
        $redis = RedisClient::getInstance();
        $key = "cache:recharge_consume:{$sha1}";
        $json = $redis->get($key);
        return $json ? json_decode($json, true) : null;
    }

    protected function setCache($sha1, $value)
    {
        $redis = RedisClient::getInstance();
        $key = "cache:recharge_consume:{$sha1}";
        $redis->set($key, $value ? json_encode($value) : '', 600);//趋势图10分钟缓存
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
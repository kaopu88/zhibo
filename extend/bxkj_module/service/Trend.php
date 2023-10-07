<?php

namespace bxkj_module\service;

use bxkj_common\DateTools;
use bxkj_common\RedisClient;

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
            $item['data'] = $this->getYList($nodes, $item['mark'], $unit, $item['member']);
            unset($item['mark']);
            $series[] = $item;
        }
        return array('series' => $series, 'xList' => $xList);
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
        $mode = isset($member) ? 1 : 0;
        $yList = array();
        $key = "{$this->prefix}{$mark}:{$unit}";
        foreach ($nodes as $k => $value) {
            $score = 0;
            $h = $value['y'] . $value['m'] . $value['d'] . $value['h'];
            $d = $value['y'] . $value['m'] . $value['d'];
            $w = $value['w'];
            $m = $value['y'] . $value['m'];
            if ($unit == 'h') {
                $score = $this->redis->zScore($mode == 1 ? ($key . ':' . $h) : $key, $mode == 1 ? $member : $h);
            } elseif ($unit == 'd') {
                $score = $this->redis->zScore($mode == 1 ? ($key . ':' . $d) : $key, $mode == 1 ? $member : $d);
            } elseif ($unit == 'w') {
                $score = $this->redis->zScore($mode == 1 ? ($key . ':' . $w) : $key, $mode == 1 ? $member : $w);
            } elseif ($unit == 'm') {
                $score = $this->redis->zScore($mode == 1 ? ($key . ':' . $m) : $key, $mode == 1 ? $member : $m);
            }
            //$score=mt_rand(0,100);//测试数据
            $yList[] = $score ? $score : 0;
        }
        return $yList;
    }


}
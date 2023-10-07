<?php

namespace app\api\service;
use app\common\service\Service;
use think\Db;

class MilletLog extends Service
{
    public function __construct()
    {
        parent::__construct();
    }

    //获取直播收益
    public function getLiveSum($user_id, $unit, $num)
    {
        $where = ['get_uid' => $user_id];
        if ($unit == 'd') {
            $where['day'] = $num;
        } else if ($unit == 'm') {
            $where['month'] = $num;
        } else {
            return 0;
        }
        $sum = Db::name('kpi_millet')->where($where)->sum('millet');
        return $sum ? $sum : 0;
    }


    public function getIncSum($user_id, $tradeType = null, $startTime = null, $endTime = null)
    {
        $db = Db::name('millet_log');
        $where['user_id'] = $user_id;
        $where['type'] = 'inc';
        $where['isvirtual'] = '0';
        if (isset($tradeType)) {
            if (is_string($tradeType)) {
                $pos = strpos($tradeType, ',');
                $where['trade_type'] = $pos === false ? $tradeType : explode(',', $tradeType);
            } else {
                $where['trade_type'] = $tradeType;
            }
        }
        if (isset($startTime)) {
            $db->where('create_time >= ' . $startTime);
        }
        if (isset($endTime)) {
            $db->where('create_time < ' . $endTime);
        }
        $sum = $db->where($where)->sum('total');
        return $sum ? $sum : 0;
    }

    public function getList($get = array(), $offset = 0, $length = 10)
    {
        $db = Db::name('millet_log');
        $this->setWhere($db, $get)->setOrder($db, $get);
        $list = $db->field('id,log_no,user_id,cont_uid,type,total,trade_type,trade_no,create_time')
            ->limit($offset, $length)->select();
        foreach ($list as &$item) {
            $item['user_id'] = (string)$item['user_id'];
            $item['cont_uid'] = (string)$item['cont_uid'];
            $item['create_time'] = date('Y-m-d', $item['create_time']);
            $tradeType = $item['trade_type'];
            $item['title'] = "收获{$item['total']}" . APP_MILLET_NAME;
            $giftLog = Db::name('gift_log')->field('gift_id,name,type,picture_url')->where(array('gift_no' => $item['trade_no']))->find();
            $item['descr'] = "{$giftLog['name']}";
        }
        return $list;
    }

    protected function setWhere(&$db, $get)
    {
        $where = array('user_id' => $get['user_id'], 'isvirtual' => '0');
        if ($get['type'] != '') $where['type'] = $get['type'];
        if ($get['trade_type'] != '' && $get['trade_type'] != 'other') $where['trade_type'] = $get['trade_type'];
        if ($get['trade_type'] == 'other') $db->where('trade_type <> "live_gift" and trade_type <> "video_gift" ');
        $db->where($where);
        return $this;
    }

    protected function setOrder(&$db, $get)
    {
        if (empty($get['sort'])) {
            $db->order('create_time desc');
        }
        return $this;
    }
}
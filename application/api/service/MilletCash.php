<?php

namespace app\api\service;
use app\common\service\Service;
use think\Db;

class MilletCash extends Service
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getList($get = array(), $offset = 0, $length = 10)
    {
        $db = Db::name('millet_cash');
        $this->setWhere($db, $get)->setOrder($db, $get);
        $list = $db->field('id,cash_no,user_id,millet,rmb,status,aid,handler_time,cash_account,create_time')
            ->limit($offset, $length)->select();
        $cashAccountIds = $this->getIdsByList($list, 'cash_account');
        $cashAccounts = [];
        if (!empty($cashAccountIds)) {
            $cashAccounts = Db::name('cash_account')->where(array('id' => $cashAccountIds))->limit(count($cashAccountIds))->select();
        }
        foreach ($list as &$item) {
            $item['create_time'] = date('Y-m-d', $item['create_time']);
            $cashAcc = $this->getItemByList($item['cash_account'], $cashAccounts, 'id');
            $cardNameArr = explode('-', $cashAcc['card_name']);
            $item['title'] = "提现到" . $cardNameArr[0];
            $item['descr'] = "【{$item['cash_no']}】 提现{$item['millet']}".APP_MILLET_NAME;
            $item['handler_time'] = isset($item['handler_time']) ? date('Y-m-d', $item['handler_time']) : '';
        }
        return $list;
    }

    protected function setWhere(&$db, $get)
    {
        $where = array('user_id' => $get['user_id']);
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

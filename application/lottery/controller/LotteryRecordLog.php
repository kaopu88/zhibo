<?php

namespace app\lottery\controller;

class LotteryRecordLog extends Controller
{

    public function index()
    {
        $this->checkAuth('lottery:lottery_record_log:select');
        $get = input();
        $lotteryRecordLog = new \app\lottery\service\LotteryRecordLog();
        $total = $lotteryRecordLog->getTotal($get);
        $page = $this->pageshow($total);
        $list = $lotteryRecordLog->getList($get, $page->firstRow, $page->listRows);
        $this->assign('_list', $list);
        return $this->fetch();

    }
}
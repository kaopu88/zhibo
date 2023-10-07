<?php
namespace app\admin\controller;

class AppleReceipt extends Controller
{
    public function index()
    {
        $this->checkAuth('admin:apple_receipt:select');
        $get = input();
        $appleReceiptService = new \app\admin\service\AppleReceipt();
        $total = $appleReceiptService->getTotal($get);
        $page = $this->pageshow($total);
        $list = $appleReceiptService->getList($get, $page->firstRow, $page->listRows);
        $this->assign('_list', $list);
        return $this->fetch();
    }
}
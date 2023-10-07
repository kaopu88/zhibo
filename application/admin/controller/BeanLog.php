<?php

namespace app\admin\controller;
use think\facade\Request;

class BeanLog extends Controller
{
    public function index(){
        $this->checkAuth('admin:bean_log:select');
        $get = input();
        $beanLogService = new \app\admin\service\BeanLog();
        $total = $beanLogService->getTotal($get);
        $page = $this->pageshow($total);
        $list = $beanLogService->getList($get,$page->firstRow,$page->listRows);
        $total_amount = 0;
        if ($get['type'] != '')
        {
            $total_amount = $beanLogService->getTotalAmount($get);
        }
        $this->assign('get',$get);
        $this->assign('total_amount',$total_amount);
        $this->assign('_list',$list);
        if(Request::isAjax()){
            return json_success( $this->fetch('data_list'),'');
        }else{
            return $this->fetch();
        }
    }
}

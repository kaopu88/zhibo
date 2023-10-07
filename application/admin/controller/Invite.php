<?php


namespace app\admin\controller;

use think\facade\Request;
use think\Db;

class Invite extends Controller
{
    public function friend_code()
    {
        $this->checkAuth('admin:invite:friend_code');
        $get = input();
        $FriendCode = new \app\admin\service\FriendCode();
        $total = $FriendCode->getTotal($get);
        $page = $this->pageshow($total);
        $list = $FriendCode->getList($get, $page->firstRow, $page->listRows);
        $this->assign('_list', $list);
        return $this->fetch();
    }

    public function address_book()
    {
        $this->checkAuth('admin:invite:address_book');
        $get = input();
        $FriendCode = new \app\admin\service\AddressBook();
        $total = $FriendCode->getTotal($get);
        $page = $this->pageshow($total);
        $list = $FriendCode->getList($get, $page->firstRow, $page->listRows);
        $this->assign('_list', $list);
        return $this->fetch();
    }

    public function invite_log()
    {
        $this->checkAuth('admin:invite:invite_log');
        $get = input();
        $FriendCode = new \app\admin\service\Invite();
        $total = $FriendCode->getTotal($get);
        $page = $this->pageshow($total);
        $list = $FriendCode->getList($get, $page->firstRow, $page->listRows);
        $this->assign('_list', $list);
        return $this->fetch();
    }
}
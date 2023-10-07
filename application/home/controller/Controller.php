<?php

namespace app\home\controller;

use think\facade\Request;
use bxkj_module\controller\Web;

class Controller extends Web
{

    public function __construct()
    {
        parent::__construct();
        if (Request::domain() == substr(ERP_URL,0,strrpos(ERP_URL,"/"))) {
            $this->redirect('/admin/account/login');
        }
    }
}

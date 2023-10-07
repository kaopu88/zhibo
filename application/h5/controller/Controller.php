<?php

namespace app\h5\controller;

use bxkj_module\controller\Web;
use think\facade\Request;


class Controller extends Web
{
    public function isAuthBrowser()
    {
        $USER_AGENT = Request::server('HTTP_USER_AGENT');

        return true;
        //return preg_match('/'.BX_USER_AGENT.'/i', $USER_AGENT);
    }
}

<?php

namespace app\admin\controller;

use think\facade\Request;

class Finance extends Controller
{
    public function index()
    {
        return $this->fetch();
    }
}

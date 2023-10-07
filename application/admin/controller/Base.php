<?php
namespace app\admin\controller;

class Base extends Controller
{

    public function home()
    {
        return $this->fetch();
    }
}

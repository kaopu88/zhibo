<?php
/**
 * Created by PhpStorm.
 * User: summer
 * Date: 2019/8/16
 * Time: 15:07
 */

namespace app\h5\controller;

use bxkj_common\HttpClient;
use think\Db;

class Web extends Controller
{
    public function index()
    {

        return $this->fetch();
    }
    
    
    
    
}
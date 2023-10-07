<?php
/**
 * Created by PhpStorm.
 * User: Zack
 * Date: 2020/5/4 0004
 * Time: 下午 5:36
 */
namespace app\api\controller;

use app\common\controller\Rsa;
use bxkj_common\ClientInfo;
use bxkj_common\CoreSdk;
use bxkj_module\controller\Api;
use think\facade\Request;

class Beauty extends Api
{
    /**
     * 美颜配置
     * http://域名/api/beauty/index
     */
    public function index()
    {
        $sdk = new CoreSdk();
        $res = $sdk->post('beauty/index', []);
        if (!$res) return $this->jsonError($sdk->getError()->message, $sdk->getError()->status);
        return $this->success(['beauty' => $res], '获取成功');
    }
}
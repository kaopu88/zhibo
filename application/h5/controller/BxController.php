<?php

namespace app\h5\controller;

use think\exception\HttpResponseException;
use think\facade\Request;
use think\facade\Response;

class BxController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        if (!$this->isAuthBrowser()) {
            $this->assign('logo', img_url('', '200_200', 'logo'));
            $this->assign('product_name', APP_NAME);
            $this->assign('download_url', H5_URL . '/download.html');
            $result = $this->fetch('public/notice');
            $response = Response::create($result, 'html');
            throw new HttpResponseException($response);
        }
    }
}

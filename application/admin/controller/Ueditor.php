<?php

namespace app\admin\controller;

use bxkj_module\service\UeditorLoader;

class Ueditor extends Controller
{
    public function controller()
    {
        define('UEDITOR_PHP', config('upload.ueditor_path') . '/php');
        global $ueditorLoader;
        $ueditorLoader=new UeditorLoader();
        require UEDITOR_PHP. '/controller.php';
    }

    public function umeditor_upload_img()
    {
        define('UMEDITOR_PHP', (config('upload.umeditor_path') . '/php'));
        require UMEDITOR_PHP . '/imageUp.php';
    }
}
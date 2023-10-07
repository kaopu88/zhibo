<?php


namespace app\admin\controller;

use think\facade\Log;
use think\facade\Request;

class Annex extends Controller
{
    public function index()
    {
        $this->checkAuth('admin:annex:index');
        $prefix = urldecode(input('prefix'));
        $marker = input('marker');

        $Annex = new \app\admin\service\Annex();
        $list = $Annex->getLists($prefix,$marker);
        $this->assign('prefix', $prefix);
        $this->assign('_list', $list);
        return $this->fetch();
    }

    public function downfile()
    {
        $this->checkAuth('admin:annex:index');
        $fileName = input('file');
        $size = input('size');

        $Annex = new \app\admin\service\Annex();
        return $Annex->down(urldecode($fileName),$size);
    }

    public function delFile()
    {
        $this->checkAuth('admin:annex:delete');
        $fileName = input('file');
        $Annex = new \app\admin\service\Annex();
        $result = $Annex->delFile(urldecode($fileName));
        if( !$result ) return $this->error('删除失败');
        alog("system.annex.del", "删除附件 名称：".$fileName);
        return $this->success('删除成功');
    }
}
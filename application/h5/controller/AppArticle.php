<?php

namespace app\h5\controller;

use think\Db;

class AppArticle extends Controller
{
    function show()
    {
        $mark = input('mark');
        $id = input('id');
        $where = ['status' => '1'];
        if (!empty($mark)) {
            $where['mark'] = $mark;
        } else {
            $where['id'] = $id;
        }
        $info = Db::name('article')->where($where)->find();
        if (empty($info)) $this->error('详情内容为空');
        if (!empty($info['url'])) return $this->redirect($info['url']);
        $this->assign('_info',$info);
        return $this->fetch();
    }

}
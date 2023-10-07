<?php

namespace app\h5\controller;

use bxkj_module\service\Tree;
use think\Db;

class About extends Controller
{
    function index()
    {
        $params = input();
        $catTree = new Tree('category');
        $pid = $catTree->getIdByMark('art_app_about');
        $list = Db::name('article')->where(['status' => '1', 'cat_id' => $pid])
            ->field('id,title,image,summary,pv,sort,release_time')
            ->order('sort desc,release_time asc')->select();
        $this->assign('_list', $list);
        $ios_chart = config('product.ios_chart');
        if (!empty($version)) {
            list($os, $v) = explode('_', $version);
            $this->assign('_version', $ios_chart[$v]);
        }
        return $this->fetch();
    }


    public function detail()
    {
        $params = input();
        $version = !empty($params['version']) ? $params['version'] : '';
        $id = !empty($params['id']) ?$params['id']: 0;
        $mark = $params['mark'];
        $info = ['title' => '无标题', 'content' => '无内容'];
        if (!empty($id) || !empty($mark)) {
            $db = Db::name('article');
            if (!empty($id)) $db->where('id', $id);
            if (!empty($mark)) $db->where('mark', $mark);
            $result = $db->where(['status' => '1'])->find();
            // var_dump($db->getLastSql());
            // var_dump($id);
            // var_dump($result);die;
            if ($result) {
                if (!empty($result['url'])) {
                    $result['url'] = parse_tpl($result['url'], [
                        'h5_service_url' => H5_URL,
                        'v' => input('version')
                    ]);
                    $this->redirect($result['url']);
                    exit();
                }
                $result['content'] = $result['mobile_content'] ? $result['mobile_content'] : $result['content'];
                $info = $result;
                Db::name('article')->where(['id' => $result['id']])->setInc('pv', 1);
            }
        }
        $this->assign('_info', $info);
        return $this->fetch();
    }
}
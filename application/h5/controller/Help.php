<?php

namespace app\h5\controller;

use bxkj_module\service\Tree;
use think\Db;

class Help extends BxController
{
    public function index()
    {
        $version = input('version');
        $filterCatIds = config('app.product_info.filter_help_cat');
        $filterCatIds = is_array($filterCatIds) ? $filterCatIds : [];
        $filterArtIds = config('app.product_setting.filter_help_art');
        $helpBannerUrl = config('app.product_setting.help_banner');
        $serviceTel = APP_SERVICE_TEL;
        $cateTree = new Tree('category');
        $catList = $cateTree->setOrderOptions('sort desc,create_time asc')->getCategoryByMark('art_app_help');
        foreach ($catList as $index => &$item) {
            $db = Db::name('article');
            if (!empty($filterArtIds)) {
                $db->whereNotIn('id', $filterArtIds);
            }
            $item['art_list'] = $db->where(['cat_id' => $item['id'], 'status' => '1'])
                ->field('id,title,image')->limit(0, 20)->select();
            if ($item['mark'] == 'art_app_notice') {
                $item['display'] = '0';
                $this->assign('notice_list', $item['art_list']);
            } else {
                $item['display'] = in_array($item['id'], $filterCatIds) ? '0' : '1';
            }
        }
        $this->assign('cat_list', $catList);
        $this->assign('help_banner_url', $helpBannerUrl);
        $this->assign('service_tel', $serviceTel);
        return $this->fetch();
    }


    public function detail()
    {
        $params = input();
        $version = $params['version'];
        $id = $params['id'];
        $info = ['title' => '无标题', 'content' => '无内容'];
        if (!empty($id)) {
            $result = Db::name('article')->where(['status' => '1', 'id' => $id])->find();
            if ($result) {
                $result['content'] = $result['mobile_content'] ? $result['mobile_content'] : $result['content'];
                $info = $result;
                Db::name('article')->where(['id' => $result['id']])->setInc('pv', 1);
            }
        }
        $serviceTel = APP_SERVICE_TEL;
        $this->assign('_info', $info);
        $this->assign('service_tel', $serviceTel);
        return $this->fetch();
    }

}
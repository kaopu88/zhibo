<?php

namespace app\admin\service;

use bxkj_module\service\Service;
use think\Db;

class RecommendContentArt extends Service
{
    public function getTotal($get){
        $this->db = Db::name('recommend_content');
        $this->setWhere($get)->setJoin();
        return $this->db->count();
    }

    private function setJoin()
    {
        $this->db->alias('rc')->join('__ARTICLE__ article', 'rc.rel_id=article.id', 'LEFT');
        $this->db->join('__RECOMMEND_SPACE__ rs', 'rc.rec_id=rs.id', 'LEFT');
        $this->db->field('rc.id,rc.rel_id,rc.sort,article.mark,article.title,article.cat_id,rs.name rs_name');
        return $this;
    }

    public function setWhere($get){
        $where = array();
        if ($get['rec_id'])
        {
            $where['rc.rec_id'] = $get['rec_id'];
        }
        if ($get['rel_type'])
        {
            $where['rc.rel_type'] = $get['rel_type'];
        }
        if ($get['cat_id'] != '') {
            $where[] = ['article.cat_id', '=', $get['cat_id']];
        } elseif ($get['pcat_id'] != '') {
            $where[] = ['article.pcat_id', '=', $get['pcat_id']];
        }
        if ($get['status'] != '') {
            $where[] = ['article.status', '=', $get['status']];
        }
        $this->db->setKeywords(trim($get['keyword']), '', 'number article.id', 'article.title');
        $this->db->where($where);
        return $this;
    }

    public function setOrder($get){
        $order = array();
        $order['rc.sort'] = 'desc';
        $order['rc.create_time'] = 'desc';
        $this->db->order($order);
        return $this;
    }

    public function getList($get,$offset,$lenth){
        $this->db = Db::name('recommend_content');
        $this->setWhere($get)->setOrder($get)->setJoin();
        $result = $this->db->limit($offset,$lenth)->select();
        $result = $result ? $result : [];
        $catList = $this->getRelList($result, function ($catIds) {
            $catList = Db::name('category')->whereIn('id', $catIds)->field('id,name,pid')->select();
            return $catList;
        }, 'pcat_id,cat_id');
        foreach ($result as &$item) {
            $item['cat_info'] = $this->getItemByList($item['cat_id'], $catList);
            if (!empty($item['cat_info'])) {
                $item['pcat_info'] = Db::name('category')->where('id', $item['cat_info']['pid'])->field('id,name,pid')->find();
            }
        }
        return $result;
    }

}
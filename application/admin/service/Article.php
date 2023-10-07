<?php

namespace app\admin\service;

use bxkj_module\service\Service;
use bxkj_module\service\Tree;
use think\Db;

class Article extends Service
{

    //获取总记录数
    public function getTotal($get)
    {
        $this->db = Db::name('article');
        $this->setWhere($get);
        return $this->db->count();
    }

    //获取列表
    public function getList($get, $offset, $length)
    {
        $this->db = Db::name('article');
        $this->setWhere($get)->setOrder($get);
        $result = $this->db->limit($offset, $length)->select();
        $catList = $this->getRelList($result, function ($catIds) {
            $catList = Db::name('category')->whereIn('id', $catIds)->field('id,name,pid')->select();
            return $catList;
        }, 'pcat_id,cat_id');
        foreach ($result as &$item) {
            $item['cat_info'] = $this->getItemByList($item['cat_id'], $catList);
            if (!empty($item['cat_info'])) {
                $item['pcat_info'] = Db::name('category')->where('id', $item['cat_info']['pid'])->field('id,name,pid')->find();
            }
            $item['rec_num'] = Db::name('recommend_content')->where(['rel_type' => 'art', 'rel_id' => $item['id']])->count();
        }
        return $result;
    }

    //设置查询条件
    private function setWhere($get)
    {
        $where = array();
        if ($get['cat_id'] != '') {
            $where[] = ['cat_id', '=', $get['cat_id']];
        } elseif ($get['pcat_id'] != '') {
            $where[] = ['pcat_id', '=', $get['pcat_id']];
        }
        if ($get['status'] != '') {
            $where[] = ['status', '=', $get['status']];
        }
        $this->db->setKeywords(trim($get['keyword']), '', 'number id', 'title');
        $this->db->where($where);
        return $this;
    }

    //设置排序规则
    private function setOrder($get)
    {
        $order = array();
        if (empty($get['sort'])) {
            $order['create_time'] = 'desc';
        }
        $this->db->order($order);
        return $this;
    }

    public function add($inputData)
    {
        $data = $this->df->process('add@article', $inputData)->output();
        if ($data === false) return $this->setError($this->df->getError());
        $id = Db::name('article')->insertGetId($data);
        if (!$id) return $this->setError('新增失败');
        return $id;
    }

    public function update($inputData)
    {
        $data = $this->df->process('update@article', $inputData)->output();
        if ($data === false) return $this->setError($this->df->getError());
        $num = Db::name('article')->where(array('id' => $data['id']))->update($data);
        if (!$num) return $this->setError('更新失败');
        return $num;
    }


    public function validateCategoryId($value, $rule, $data = null, $more = null)
    {
        $treeModel = new Tree('category');
        $pathArr = $treeModel->getParentPath($value, 'article_category', true);
        return !empty($pathArr);
    }

    public function fillSummary($value, $rule, $data = null, $more = null)
    {
        $content = strip_tags($data['content']);
        return msubstr($content, 0, 45, 'utf-8', false);
    }

    public function fillImages($value, $rule, $data = null, $more = null)
    {
        $content = $data['content'] ? $data['content'] : $data['mobile_content'];
        $pattern = "/[img|IMG].*?src=['|\"](.*?(?:[.gif|.jpg]))['|\"].*?[\/]?>/";
        $matches = [];
        $tmp = [];
        if (!empty($data['image'])) {
            $tmp[] = $data['image'];
        }
        preg_match_all($pattern, $content, $matches);
        if ($matches && $matches[1]) {
            for ($i = 0; $i < (min(5, count($matches[1]))); $i++) {
                $tmp[] = $matches[1][$i];
            }
        }
        return $tmp ? implode(',', $tmp) : '';
    }

    public function fillPCatId($value, $rule, $data = null, $more = null)
    {
        if (empty($data['pcat_id'])) {
            $result = Db::name('category')->where(['id' => $value])->find();
            if (!empty($result)) {
                return ['pcat_id' => $result['pid']];
            }
        }
    }


}
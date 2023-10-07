<?php
/**
 * Created by PhpStorm.
 * User: 李成
 * Date: 2020/5/23
 * Time: 16:12
 */
namespace app\taoke\service;

use bxkj_module\service\Service;
use think\Db;

class Module extends Service
{

    public function getTotal($get)
    {
        $this->db = Db::name('taoke_modules');
        $this->setWhere($get);
        $count = $this->db->count();
        return (int)$count;
    }

    public function getList($get, $offset=0, $length=20)
    {
        $list = [];
        $this->db = Db::name('taoke_modules');
        $this->setWhere($get)->setOrder($get);
        $this->db->field('tm.module_id,tm.title,tm.desc,tm.image,tm.selected_image,tm.bg_color,tm.page_id,tm.open_type,tm.open_url,tm.params,tm.position_id,tm.sort,tm.status,tm.add_time,tmp.name,tmp.id,tp.name as page_name');
        $list = $this->db->limit($offset, $length)->select();
        return $list;
    }

    protected function setWhere($get)
    {
        $this->db->alias('tm');
        $where = array();
        $where1 = array();
        if (isset($get['keyword']) && $get['keyword'] != '') {
            $where1[] = ['tm.title','like','%'.$get['keyword'].'%'];
        }
        if (isset($get['position_id']) && $get['position_id'] != '') {
            $where['tm.position_id'] = $get['position_id'];
        }
        $this->db->where($where)->where($where1);
        $this->db->join('__TAOKE_MODULES_POSITION__ tmp', 'tmp.id=tm.position_id', 'LEFT');
        $this->db->join('taoke_page tp', 'tp.id=tm.page_id', 'LEFT');
        return $this;
    }

    protected function setOrder($get)
    {
        $order = array();
        if (empty($get['order'])) {
            $order = 'tm.module_id DESC';
        }
        if($get['order']){
            $order = $get['order'];
        }
        $this->db->order($order);
        return $this;
    }

    public function add($data)
    {
        $data['add_time'] = time();
        $id = Db::name('taoke_modules')->insertGetId($data);
        return $id;
    }

    public function update($where, $data)
    {
        $status = Db::name('taoke_modules')->where($where)->update($data);
        return $status;
    }

    public function getInfo($where)
    {
        $info = Db::name('taoke_modules')->where($where)->find();
        if($info){
            $info['bg_color'] = json_decode($info['bg_color'], true);
        }
        return $info;
    }

    public function delete($where)
    {
        $status = Db::name('taoke_modules')->where($where)->delete();
        return $status;
    }

    public function getPageInfo($pageId)
    {
        $data = [];
        if(empty($pageId)){
            return false;
        }
        $pageInfo = Db::name("taoke_page")->where(["id"=>$pageId])->find();//页面信息
        $data['open_url'] = $pageInfo['url'];
        return $data;
    }
}
<?php
/**
 * Created by PhpStorm.
 * User: 李成
 * Date: 2020/5/5
 * Time: 19:47
 */
namespace app\taoke\service;

use app\taokeshop\service\AnchorShop;
use bxkj_module\service\Service;
use think\Db;

class Collect extends Service
{

    public function getTotal($get)
    {
        $this->db = Db::name('collect');
        $this->setWhere($get);
        $count = $this->db->count();
        return (int)$count;
    }

    protected function setWhere($get)
    {
        $where = array();
        $where1 = array();
        if (isset($get['keyword']) && $get['keyword'] != '') {
            $where1[] = ['title','like','%'.$get['keyword'].'%'];
        }
        if (isset($get['user_id']) && $get['user_id'] != '') {
            $where['user_id'] = $get['user_id'];
        }
        $this->db->where($where)->where($where1);
        return $this;
    }

    protected function setOrder($get)
    {
        $order = array();
        if (empty($get['sort'])) {
            $order = 'id DESC';
        }
        $this->db->order($order);
        return $this;
    }

    /**
     * 获取收藏记录
     * @param array $where
     * @param int $page
     * @param int $pageSize
     * @param string $sort
     * @return array|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getList($get, $offset=0, $length=20)
    {
        $logList = [];
        $this->db = Db::name('collect');
        $this->setWhere($get)->setOrder($get);
        $logList = $this->db->limit($offset, $length)->select();
        return $logList;
    }

    /**
     * 添加收藏
     * @param $data
     * @return int|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function addCollect($data)
    {
        $res = false;
        $data['add_time'] = time();
        $where['user_id'] = $data['user_id'];
        $where['goods_id'] = $data['goods_id'];
        $info = $this->getInfo($where);
        if(empty($info)) {
            $res = Db::name("collect")->insert($data);
        }
        return $res;
    }

    /**
     * 获取单个记录信息
     * @param $where
     * @return array|null|\PDOStatement|string|\think\Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getInfo($where)
    {
        $log = Db::name("collect")->where($where)->find();
        return $log;
    }

    /**
     * 删除浏览记录
     * @param $where
     * @return int
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function cancleCollect($where)
    {
        $status = Db::name("collect")->where($where)->delete();
        return $status;
    }

    /**
     * 统计浏览次数
     * @param $where
     * @return float|string
     */
    public function countCollectTimes($where)
    {
        $num = Db::name("collect")->where($where)->count();
        return $num;
    }
}
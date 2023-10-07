<?php
/**
 * Created by PhpStorm.
 * User: 李成
 * Date: 2020/5/11
 * Time: 11:51
 */
namespace app\common\service;

use think\Db;

class UpgradeLog extends Service
{
    /**
     * 获取下级粉丝
     * @param $userId
     * @param int $level
     * @return array|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getList($where, $offset=0, $limit=20)
    {
        $this->db = Db::name("upgrade_log");
        $this->setWhere($where);
        $fields = "ul.type,ul.user_id,ul.level,ul.upgrade_condition,ul.status,ul.add_time,ul.update_time,user.nickname";
        $list = $this->db->field($fields)->limit($offset, $limit)->select();
        return $list;
    }

    protected function setWhere($get)
    {
        $this->db->alias("ul");
        $where = array();
        if ($get['user_id'] != 0) {
            $where['ul.user_id'] = $get['user_id'];
        }
        if ($get['status'] != '') {
            $where['ul.status'] = $get['status'];
        }
        if ($get['level'] != '') {
            $where['ul.level'] = $get['level'];
        }
        if ($get['keyword'] != '') {
            $where[] = ['user.nickname','like','%'.$get['keyword'].'%'];
        }
        $this->db->where($where);
        $this->db->join("__USER__ user", "user.user_id=ul.user_id", "LEFT");
        return $this;
    }

    public function addLog($data)
    {
        $this->db = Db::name("upgrade_log");
        $id = $this->db->insertGetId($data);
        return $id;
    }

    public function getLogInfo($where)
    {
        $this->db = Db::name("upgrade_log");
        $info = $this->db->where($where)->find();
        return $info;
    }

    public function updateLog($where, $data)
    {
        $this->db = Db::name("upgrade_log");
        $status = $this->db->where($where)->update($data);
        return $status;
    }
}
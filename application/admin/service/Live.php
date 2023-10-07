<?php

namespace app\admin\service;


use bxkj_common\CoreSdk;
use bxkj_module\service\Service;
use think\Db;

class Live extends Service
{
    protected static $model = ['直播', '录播', '电影', '游戏', '语聊', '电台'];
    protected static $type = ['普通', '私密', '收费', '计费', 'VIP', '等级'];

    //获取总记录数
    public function getTotal($get)
    {
        $this->db = Db::name('live');
        $this->setWhere($get);
        return $this->db->count();
    }

    public function getOne($where)
    {
        $res = Db::name('live')->where($where)->find();
        return $res;
    }

    //获取列表
    public function getList($get, $offset, $length)
    {
        $this->db = Db::name('live');
        $this->setWhere($get)->setOrder($get);
        $result = $this->db->limit($offset, $length)->select();

        if (empty($result)) return [];

        $admins = $this->getRelList($result, function ($aids) {
            $adminService = new Admin();
            $admins = $adminService->getAdminsByIds($aids);
            return $admins;
        }, 'aid');

        $CoreSdk = new CoreSdk();

        $room_ids = array_column($result, 'id');

        $roomAudiences = $CoreSdk->post('zombie/getRoomAudiences', ['room_id' => $room_ids]);

        $roomRobots = $CoreSdk->post('zombie/getRoomRobots', ['room_id' => $room_ids]);

        foreach ($result as &$item) {
            if (!empty($item['aid'])) {
                $item['admin'] = self::getItemByList($item['aid'], $admins, 'id');
            }
            $item['model_str'] = self::$model[$item['room_model']];
            $item['type_str'] = self::$type[$item['type']];
            $item['audience'] = empty($roomAudiences[$item['id']]) ? 0 : $roomAudiences[$item['id']];
            $item['robot'] = empty($roomRobots[$item['id']]) ? 0 : $roomRobots[$item['id']];
            $item['live_duration'] = !empty($item['create_time']) ? duration_format(time() - $item['create_time']) : '00:00:00';
        }
        return $result;
    }

    /**
     * 直播添加
     * @param $param
     */
    public function addLive($param, $user)
    {
        Service::startTrans();
        $data = [
            'user_id' => $user['user_id'],
            'nickname' => $user['nickname'],
            'avatar' => $user['avatar'],
            'pull' => $param['pull'],
            'cover_url' => $param['cover_url'],
            'room_channel' => $param['room_channel'],
            'type' => $param['type'],
            'type_val' => $param['type_val'] ? $param['type_val'] : 0,
            'room_model' => 1,
            'create_time' => time(),
            'status' => 1,
        ];
        $id = Db::name('live')->insertGetId($data);
        if (!$id) return $this->setError('新增失败');
        Service::commit();
        return $id;
    }

    public function editLive($param, $user)
    {
        Service::startTrans();
        $data = [
            'user_id' => $user['user_id'],
            'nickname' => $user['nickname'],
            'avatar' => $user['avatar'],
            'pull' => $param['pull'],
            'cover_url' => $param['cover_url'],
            'room_channel' => $param['room_channel'],
            'type' => $param['type'],
            'type_val' => $param['type'] ? $param['type_val'] : 0,
            'room_model' => 1,
            'create_time' => time(),
            'status' => 1,
        ];
        $id = Db::name('live')->where(['id' => $param['id']])->update($data);
        if (!$id) return $this->setError('编辑失败');
        Service::commit();
        return $id;
    }

    //设置查询条件
    private function setWhere($get)
    {
        $where = array();
        $where[] = ['status', '=', 1];

        if ($get['room_model'] != '') {
            $where[] = ['room_model', '=', $get['room_model']];
        } else {
            $where[] = ['room_model', '<', 4];
        }
        if ($get['type'] != '') {
            $where[] = ['type', '=', $get['type']];
        }
        if (trim($get['room_id']) != '') {
            $where[] = ['id', '=', trim($get['room_id'])];
        }
        if (trim($get['user_id']) != '') {
            $where[] = ['user_id', '=', trim($get['user_id'])];
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
            $order['id'] = 'desc';
        }
        $this->db->order($order);
        return $this;
    }

}
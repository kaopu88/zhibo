<?php

namespace app\api\service;

use app\common\service\Service;
use think\Db;
use bxkj_module\service\User;

class InviteRecord extends Service
{
    public function getList($get = array(), $offset = 0, $length = 10)
    {
        $db = Db::name('invite_record');
        $this->setWhere($db, $get)->setOrder($db, $get);
        $list = $db->field('id,invite_uid,anchor_uid,user_id,reward_exp,reward_bean,reward_millet,create_time')
            ->limit($offset, $length)->select();
        $user_ids = $this->getIdsByList($list, 'anchor_uid,user_id', false);
        $userModel = new User();
        $users = [];
        if (!empty($user_ids)) {
            $users = $userModel->getUsers($user_ids);
        }
        foreach ($list as &$item) {
            $anchor = $this->getItemByList($item['anchor_uid'], $users, 'user_id');
            $item['anchor'] = $anchor ? $anchor : (object)[];
            $user = $this->getItemByList($item['user_id'], $users, 'user_id');
            $item['user'] = $user ? $user : (object)[];
            $item['create_time'] = time_format($item['create_time'], '', 'date');
        }
        return $list;
    }

    protected function setWhere(&$db, $get)
    {
        $where = array('invite_uid' => $get['user_id']);
        $db->where($where);
        return $this;
    }

    protected function setOrder(&$db, $get)
    {
        if (empty($get['sort'])) {
            $db->order('create_time desc');
        }
        return $this;
    }
}
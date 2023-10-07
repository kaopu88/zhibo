<?php
/**
 * Created by PhpStorm.
 * Author: belost
 * Date: 19-7-2
 * Time: 下午10:10
 */

namespace app\api\service\live;


use app\api\service\LiveBase2;
use bxkj_common\CoreSdk;
use think\Db;


class LinkMic extends LiveBase2
{

    //获取入房连麦数据
    public function getEnterRoomLinkMic($room_id)
    {
        $where = [
            ['status', 'eq', 1],
            ['room_id', 'eq', $room_id]
        ];

        $rs = Db::name('link_mic_log')
            ->field('user_id, id link_mic_id, room_id, pull user_pull, push user_push')
            ->where($where)
            ->order('create_time desc')
            ->select();

        return $rs;
    }


    public function getActiveUserList($room_id, $p)
    {
        $key = self::$livePrefix.$room_id.':audience';

        $start = ($p-1)*PAGE_LIMIT;

        $end = $p*PAGE_LIMIT;

        $data = [];

        $list = $this->redis->zrevrange($key, $start, $end-1, true);

        if (empty($list)) return $data;

        $uids = array_keys($list);

        $users = $this->getUsersInfo($uids);

        foreach ($list as $user_id => $level)
        {
            $user = [
                'avatar' => $users[$user_id]['avatar'],
                'nickname' => $users[$user_id]['nickname'],
                'gender' => $users[$user_id]['gender'],
                'level' => $users[$user_id]['level'],
                'jump' => getJump('personal', ['user_id' => $user_id]),
                'user_id' => $user_id
            ];

            array_push($data, $user);
        }

        return $data;
    }



    public function getLinkReplyList($room_id, $p)
    {
        $offset = ($p-1)*PAGE_LIMIT;

        $where = [
            ['status', 'eq', 0],
            ['is_invite', 'eq', 0],
            ['room_id', 'eq', $room_id]
        ];

        return $this->getData($where, $offset, PAGE_LIMIT);
    }


    public function getLinkMicList($room_id, $p)
    {
        $offset = ($p-1)*PAGE_LIMIT;

        $where = [
            ['status', 'eq', 1],
            ['room_id', 'eq', $room_id]
        ];

        return $this->getData($where, $offset, PAGE_LIMIT);
    }


    protected function getData($where, $offset=0, $length=10)
    {
        $rs = Db::name('link_mic_log')
            ->field('user_id, id link_mic_id, room_id')
            ->where($where)
            ->order('create_time desc')
            ->limit($offset, $length)
            ->select();

        if (empty($rs)) return [];

        $uids = array_column($rs, 'user_id');

        $users = $this->getUsersInfo($uids);

        foreach ($rs as &$value)
        {
            $value['avatar'] = $users[$value['user_id']]['avatar'];
            $value['nickname'] = $users[$value['user_id']]['nickname'];
            $value['gender'] = $users[$value['user_id']]['gender'];
            $value['level'] = $users[$value['user_id']]['level'];
            $value['jump'] = getJump('personal', ['user_id' => $value['user_id']]);
        }

        return $rs;
    }



    //获取用户资料
    protected function getUsersInfo($userIds, $visitor=null, $field='_all')
    {
        $arr = [];

        $CoreSdk = new CoreSdk();

        if (!is_array($userIds)) $userIds = [$userIds];

        $users = $CoreSdk->getUsers($userIds, $visitor, $field);

        if (empty($users)) return $arr;

        foreach ($users as &$info)
        {
            isset($info['avatar']) && $info['avatar'] .= '?imageView2/1/w/50/h/50';

            $arr[$info['user_id']] = $info;
        }

        return $arr;
    }

}
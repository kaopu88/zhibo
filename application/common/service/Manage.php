<?php


namespace app\common\service;


use app\api\service\LiveBase2;
use think\Db;

class Manage extends LiveBase2
{
    //验证是否为超管
    public function validateSuper($user_id)
    {
        $res = Db::name('live_manage')->where(['anchor_uid' => 0, 'manage_uid' => $user_id])->find();
        return empty($res) ? false : true;
    }

    //验证是否为主播的守护
    protected function verifyGuard($anchor_id, $user_id)
    {
        $is_status = $this->redis->zscore('BG_GUARD:' . $anchor_id, $user_id);
        return empty($is_status) ? 0 : 1;
    }


    //验证直播间操作权限
    protected function liveManageAuthCheck($room_id, $user_id)
    {
        $myIdentity = $hisIdentity = self::USER;

        $this->where['id'] = $room_id;

        $room = Db::name('live')->where($this->where)->field('user_id')->find();

        if (empty($room)) return make_error('直播间已关');

        $this->verifyManage($room['user_id'], USERID) && $myIdentity = 2;//管理员

        USERID == $room['user_id'] && $myIdentity = self::ANCHOR;//主播

        $this->validateSuper(USERID) && $myIdentity = self::SUPER;//超管

        if ($myIdentity < self::MANAGE) return make_error('您无此操作权限');

        $this->verifyManage($room['user_id'], $user_id) && $hisIdentity = self::MANAGE;//管理员

        $user_id == $room['user_id'] && $hisIdentity = self::ANCHOR;//主播

        $this->validateSuper($user_id) && $hisIdentity = self::SUPER;//超管

        if ($hisIdentity >= $myIdentity) return make_error('对方为,' . self::$room_user_identity[$hisIdentity - 1] . '您无此权限');

        return true;
    }


    //验证是否为主播的管理
    protected function verifyManage($anchor_id, $user_id)
    {
        $is_exists = $this->redis->sismember('liveManage:' . $anchor_id, $user_id);

        return (int)$is_exists;
    }
}
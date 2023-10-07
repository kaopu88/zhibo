<?php


namespace app\api\service\user;


use app\common\service\Service;
use bxkj_module\exception\ApiException;
use think\Db;

class User_Props extends Service
{
    protected static $page = 50;

    //获取用户道具
    public function getUserProps($p, $userId = '')
    {
        $page = empty($p) ? 0 : ($p - 1) * self::$page;
        $userId = empty($userId) ? USERID : $userId;
        $now = time();

        $res = Db::name('user_props')->where(['status' => 1, 'user_id' => $userId])->field('name, icon, id, use_status as is_use, expire_time')->order('update_time, create_time desc')->limit($page, self::$page)->select();

        if (empty($res)) return [];

        foreach ($res as $key => $val) {
            if ($val['expire_time'] < $now) {
                Db::name('user_props')->where(['id' => $val['id']])->update(['use_status' => 0, 'status' => 0]);

                unset($res[$key]);
            } else {
                $res[$key]['expire_time'] = '有效期至:' . date('Y.n.j', $val['expire_time']);
            }
        }

        return $res;
    }


    //新增用户道具
    public function addUserProps($data)
    {
        $props = Db::name('user_props')->where(['props_id' => $data['props_id'], 'user_id' => $data['user_id']])->find();

        $expire_time = mktime(23, 59, 59, date('m') + $data['length'], date('d'), date('y'));

        unset($data['length']);

        if (empty($props)) {
            $data['expire_time'] = $expire_time;

            try {
                $res = Db::name('user_props')->insert($data);
            } catch (ApiException $e) {
                return false;
            }
        } else {

            if ($props['expire_time'] < time()) {
                $props['expire_time'] = time();
            }

            $expire_time = $props['expire_time'] + ($expire_time - time());

            $res = Db::name('user_props')->where(['id' => $props['id']])->update([
                'num' => Db::raw("`num` + 1"),
                'expire_time' => $expire_time,
                'status' => 1,
                'update_time' => $data['create_time']
            ]);
        }

        return $res;
    }
}
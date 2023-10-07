<?php


namespace app\api\service\user;


use app\common\service\Service;
use think\Db;

class User_Package extends Service
{
    protected static $where = ['status' => 1];

    //用户背包
    public function getUserPackage($user_id)
    {
        $prefix = config('database.prefix');
        $sql = "SELECT g.id, g.`name`,g.isguard, g.`type`, g.picture_url, g.show_params, SUM(up.`num`) `num`, up.expire_time, up.use_time FROM {$prefix}user_package up INNER JOIN {$prefix}gift g ON up.gift_id=g.id AND up.status=1 AND g.status='1' WHERE user_id={$user_id} GROUP BY up.gift_id ORDER BY up.create_time desc";
        $res = Db::query($sql);

        if (empty($res)) return [];

        $now = time();

        foreach ($res as $key => &$val) {
            if (!empty($val['expire_time'])) {
                if ($now > $val['expire_time']) {
                    Db::name('user_package')->where(['id' => $val['id']])->update(['status' => 0]);
                    unset($res[$key]);
                    continue;
                } else {
                    $val['badge'] = sprintf('有效期:%s-%s', date('Y.n.j', $val['use_time']), date('Y.n.j', $val['expire_time']));
                }
            } else if ($now < $val['use_time']) {
                $val['badge'] = sprintf('%s生效', date('Y.n.j', $val['use_time']));
            } else {
                $val['badge'] = '';
            }

            if ($val['type'] == 1) $val['badge'] = 'https://static.cnibx.cn/continuity.png';

            $params = json_decode($val['show_params'], true);
            $val['show_params'] = empty($params) ? (object)[] : $params;
            unset($val['expire_time'], $val['use_time']);
        }

        return $res;
    }


    //增送用户背包礼物
    public function sendUserPackageByGift($user_id, $gift_id, $num)
    {
        $now = time();
        $data = [];
        $no_use_num = 0;
        $expire_num = 0;
        $props_info = Db::name('user_package')->where(['status' => 1, 'user_id' => $user_id, 'gift_id' => $gift_id])->order('create_time desc')->field('id, use_time, expire_time')->select();

        if (empty($props_info)) return make_error('未查到该道具或已过期~');
        if (count($props_info) < $num) return make_error('数量不够,请重新选择');

        foreach ($props_info as $value) {
            if ($now < $value['use_time']) {
                ++$no_use_num;
                continue;
            }

            if (!empty($value['expire_time'])) {
                if ($now > $value['expire_time']) {
                    //将道具置为失效状态
                    Db::name('user_package')->where(['id' => $value['id']])->update(['status' => 0]);
                    //重置模型对象
                    //$this->getORM();
                    ++$expire_num;
                }
            }

            $num > 0 && array_push($data, $value);
            $num--;
        }

        if (count($data) < $num) {
            if (!empty($no_use_num)) return make_error('当前有' . $no_use_num . '个道具未到使用时间,请等待');
            if (!empty($expire_num)) return make_error('当前有' . $expire_num . '个道具未使用,已过期');
        }

        $all = array_column($data, 'id');
        return $all;
    }
}
<?php

namespace app\api\service;

use app\common\service\Service;
use think\Db;

class Recommend_Content extends Service
{
    protected static $recommendFilmType = 'film_featured';

    protected static $recommendUserType = 'user_film_talent';

    protected static $recommendFilmId = 3;

    protected static $recommendUserId = 2;

    public function getChoseFilm($start, $end)
    {
        $prefix = config('database.prefix');

        $sql = "SELECT b.*, u.avatar, u.nickname FROM {$prefix}recommend_content recommend INNER JOIN {$prefix}video b on recommend.rel_id=b.id and recommend.rec_id=".self::$recommendFilmId." INNER JOIN {$prefix}user u on b.user_id=u.user_id ORDER BY recommend.`sort` desc LIMIT {$start}, {$end}";

        $res = Db::query($sql);

        return $res;
    }


    public function getMaster($user_id, $start, $end)
    {
        /*$sql = "SELECT u.user_id, u.avatar, u.nickname, u.level, u.gender, u.is_creation, u.vip_expire FROM __PREFIX__recommend_content recommend INNER JOIN __PREFIX__user u on recommend.rel_id=u.user_id and u.`status`='1' and recommend.rec_id=".self::$recommendUserId." where u.credit_score between 60 and 10000 ORDER BY recommend.`sort` desc LIMIT {$start}, {$end}";*/
        $prefix = config('database.prefix');

        $sql = "SELECT u.user_id, u.avatar, u.nickname, u.`level`, u.gender FROM {$prefix}recommend_content recommend INNER JOIN {$prefix}user u on recommend.rel_id=u.user_id and u.`status`='1' and recommend.rec_id=2 where recommend.rel_id not in (SELECT follow_id FROM {$prefix}follow WHERE user_id={$user_id}) and recommend.rel_id <> {$user_id} and u.credit_score between 60 and 10000 ORDER BY recommend.`sort` desc LIMIT {$start}, {$end}";

        $res = Db::query($sql);

        return $res;
    }


    public function getMasterByAddFollow($user_id, $start, $end, $exists)
    {
        $prefix = config('database.prefix');

        $sql = "SELECT u.user_id, u.avatar, u.nickname, u.`level`, u.gender FROM {$prefix}recommend_content recommend INNER JOIN {$prefix}user u on recommend.rel_id=u.user_id and u.`status`='1' and recommend.rec_id=2 where recommend.rel_id not in (SELECT follow_id FROM {$prefix}follow WHERE user_id={$user_id}) and recommend.rel_id <> {$user_id} AND recommend.rel_id not in ({$exists}) AND u.credit_score between 60 and 10000 ORDER BY recommend.`sort` desc LIMIT {$start}, {$end}";

        $res = Db::query($sql);

        return $res;
    }


}

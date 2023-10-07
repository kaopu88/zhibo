<?php

namespace bxkj_module\service;

use bxkj_common\RedisClient;
use think\Db;

class AdContent extends Service
{
    const CACHE_DURATION = 10800;//3个小时
    const CACHE_PREFIX = 'ad:space_contents:';

    public static function getSpaceKey($space, $options)
    {
        $str = '';
        if ($options['os'] != '') $str .= $options['os'];
        if ($options['code'] != '') $str .= $options['code'];
        if ($options['city_id'] != '') $str .= $options['city_id'];
        $sha1 = sha1($str);
        return self::CACHE_PREFIX . "{$space}:{$sha1}";
    }

    protected function removeExpired($contents)
    {
        $newContents = [];
        $now = time();
        foreach ($contents as $content) {
            if ($content['start_time'] <= $now && $content['end_time'] >= $now) {
                $newContents[] = $content;
            }
        }
        return $newContents;
    }

    public static function clearCache($space)
    {
        $pattern = 'cache:' . self::CACHE_PREFIX . "{$space}:*";
        $redis = RedisClient::getInstance();
        $keys = $redis->keys($pattern);
        foreach ($keys as $key) {
            $redis->del($key);
        }
    }

    public static function clearCacheByIds($ids)
    {
        if ($ids) {
            $result = Db::name('ad_content')->whereIn('id', $ids)->select();
            $result = is_array($result) ? $result : [];
            $spaceIds = self::getIdsByList($result, 'space_id');
            $spaces = [];
            if (!empty($spaceIds)) $spaces = Db::name('ad_space')->whereIn('id', $spaceIds)->field('id,mark,name')->select();
            foreach ($spaces as $space) {
                if (!empty($space['mark'])) {
                    self::clearCache($space['mark']);
                }
            }
        }
    }

}
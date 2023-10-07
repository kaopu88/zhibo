<?php


namespace app\api\service;


use app\common\service\Service;
use think\Db;

class Gift extends Service
{
    protected static $giftPrefix = 'BG_GIFT:', $giftKey = 'gift',$giftresourcesKey = 'resources', $giftvideoKey = 'video',$giftvoiceKey = 'voice', $guardKey = 'guard', $giftIncr = 'incr';

    // 直播间礼物类型
    protected static $gift_category = 0;

    protected static $gift_video_category = 1;

    protected static $gift_voice_category = 10;


    public function liveGift()
    {
        $res = $this->redis->get(self::$giftPrefix . self::$giftKey);
        // $res = null;
        if (empty($res)) {
            $prefix = config('database.prefix');
            $sql = "SELECT g.id, name, type, tips,g.isguard,picture_url, price, g.show_params, (CASE WHEN gb.icon IS NULL THEN '' ELSE gb.icon END) badge FROM {$prefix}gift g LEFT JOIN {$prefix}gift_badge gb ON g.id=gb.gift_id WHERE g.`status`='1' and cid=" . self::$gift_category . " ORDER BY g.sort asc";
            $res = Db::query($sql);
            $res = json_encode($res);
            $this->redis->setex(self::$giftPrefix . self::$giftKey, 86400, $res);
        }

        return json_decode($res, true);
    }

    public function videoGift()
    {
        $res = $this->redis->get(self::$giftPrefix . self::$giftvideoKey);

        if (empty($res)) {
            $prefix = config('database.prefix');
            $sql = "SELECT g.id, name, type, tips, picture_url, price, g.show_params, (CASE WHEN gb.icon IS NULL THEN '' ELSE gb.icon END) badge FROM {$prefix}gift g LEFT JOIN {$prefix}gift_badge gb ON g.id=gb.gift_id WHERE g.`status`='1' and cid=" . self::$gift_video_category . " ORDER BY g.sort asc";
            $res = Db::query($sql);
            $res = json_encode($res);
            $this->redis->setex(self::$giftPrefix . self::$giftvideoKey, 86400, $res);
        }

        return json_decode($res, true);
    }

    public function voiceGift()
    {
        $res = $this->redis->get(self::$giftPrefix . self::$giftvoiceKey);
        if (empty($res)) {
            $prefix = config('database.prefix');
            $sql = "SELECT g.id, name, type, tips, picture_url, price, g.show_params, (CASE WHEN gb.icon IS NULL THEN '' ELSE gb.icon END) badge FROM {$prefix}gift g LEFT JOIN {$prefix}gift_badge gb ON g.id=gb.gift_id WHERE g.`status`='1' and cid=" . self::$gift_voice_category . " ORDER BY g.sort asc";
            $res = Db::query($sql);
            $res = json_encode($res);
            $this->redis->setex(self::$giftPrefix . self::$giftvoiceKey, 86400, $res);
        }

        return json_decode($res, true);
    }

    //在线升级礼物包专用
    public function resourcesGift()
    {
        $res = $this->redis->get(self::$giftPrefix . self::$giftresourcesKey);
        if (empty($res)) {
            $res = Db::name('gift')->field('id, file, file_size size, type')->where([['type', 'neq', 1],['status', 'eq', '1'], ['file', 'neq', '']])->select();

            if (!empty($res)) {
                foreach ($res as &$val) {
                    $val['id'] = (string)$val['id'];
                    $val['size'] = (string)$val['size'];
                }
            }

            $res = json_encode($res);
            $this->redis->setex(self::$giftPrefix . self::$giftresourcesKey, 86400, $res);
        }

        return json_decode($res, true);
    }

    //守护礼物
    public function getGiftGuard()
    {
        $res = $this->redis->get(self::$giftPrefix . self::$guardKey);
        if (empty($res)) {
            $prefix = config('database.prefix');
            $sql = "SELECT g.id, name, type, tips, picture_url, price, g.show_params, (CASE WHEN gb.icon IS NULL THEN '' ELSE gb.icon END) badge FROM {$prefix}gift g LEFT JOIN {$prefix}gift_badge gb ON g.id=gb.gift_id WHERE g.`status`='1' and g.`isguard`='1' and cid=" . self::$gift_category . " ORDER BY g.sort asc";
            $res = Db::query($sql);
            $res = json_encode($res);
            $this->redis->setex(self::$giftPrefix . self::$guardKey, 86400, $res);
        }
        return json_decode($res, true);
    }

    public function iosOnlineUpgrade($version)
    {

        $platform = 2;

        return $this->getUpgrade($platform, $version);
    }


    public function androidOnlineUpgrade($version)
    {
        $platform = 1;
        return $this->getUpgrade($platform, $version);
    }


    protected function getUpgrade($platform, $version)
    {
        $res = [];
        $gift_data = Db::name('gift_upgrade')->where(['status' => 1, 'platform' => $platform])->field('version, resource, is_incr')->order('version desc')->select();

        if (empty($gift_data)) return $res;

        foreach ($gift_data as $key => $value) {
            if ($value['version'] > $version) {
                $res['resource'] = explode(',', $value['resource']);
                $res['is_incr'] = $value['is_incr'];
                $res['version'] = $value['version'];
            }
        }

        return $res;
    }
}
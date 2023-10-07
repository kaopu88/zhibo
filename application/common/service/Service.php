<?php
namespace app\common\service;
use bxkj_common\RedisClient;

class Service extends \bxkj_module\service\Service
{
    protected static $livePrefix = 'BG_LIVE:', $filmPrefix = 'BG_FILM:', $randomPrefix = 'BG_RAND:', $teenFilmPrefix = 'BG_TEEN:';//影片redis前缀

    protected $redis;

    public function __construct()
    {
        parent::__construct();
        $this->redis = RedisClient::getInstance();
    }

    /**
     * 计算时间差
     * @param $time
     * @return false|string
     */
    protected function diffTime($time, $suffix='前')
    {
        switch (true) {
            case $time < 60 :
                $res = $time . '秒';
                break;
            case $time < 3600 :
                $res = floor($time / 60) . '分钟';
                break;
            case $time < 86400 :
                $res = floor($time / 3600) . '小时' . floor(floor($time / 60) % 60) . '分钟';
                break;
            default :
                $res = floor($time / 86400) . '天' . floor(floor($time / 3600) % 24) . '小时' . floor(floor($time / 60) % 60) . '分钟';
                break;
        }
        return $res.$suffix;
    }



    /**
     * 随机抽取指定数目id
     * @param $arr array 数据源
     * @param $sum int 提取的数量
     * @param $type string 临时redis标识
     * @param bool $bool 数据源是否需要键值转换
     * @return array
     */
    protected function randomBySum($arr, $sum, $type = '', $bool = true)
    {
        if (!is_array($arr) || count($arr) < $sum) return $arr;

        $data = $bool ? array_flip($arr) : $arr;

        $user = empty(USERID) ? USERTAG : USERID;

        if (empty($type)) {
            $type = 'TEMP:' . $user;
        } else {
            $type = strtoupper(trim($type, ':'));
            $type .= ':' . $user;
        }

        $appoint = $this->redis->smembers(self::$randomPrefix . $type);

        if (empty($appoint)) {
            $appoint = $appoint_new = array_rand($data, $sum);
        } else {
            $diff = array_diff($data, $appoint);

            if (count($diff) >= $sum) {
                $appoint_new = array_rand($diff, $sum);

                $appoint = array_merge($appoint, $appoint_new);
            } else {
                $this->redis->del(self::$randomPrefix . $type);

                $appoint = $appoint_new = $this->randomBySum($arr, $sum, $type);
            }
        }

        $this->redis->sadd(self::$randomPrefix . $type, $appoint);

        if ($sum == 1) return [$appoint_new];

        return $appoint_new;
    }


    //格式化数字
    protected function formatData(&$data, $field = [])
    {
        if (is_array($data) && !empty($field) && is_array($field))
        {
            foreach ($field as $key => $val)
            {
                key_exists($val, $data) && $data[$val] = number_format2($data[$val]);
            }
        } else {
            if ($data >= 100000000) {
                $real = sprintf("%.3f", $data / 100000000);
                $data = $real . 'e';
            }else if ($data >= 10000){
                $real = sprintf("%.1f", $data / 10000);
                $data = $real . 'w';
            }
            return (string)$data;
        }
    }

}
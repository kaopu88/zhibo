<?php

namespace app\service\liveDrive;


use app\service\LiveDrive;
use Qiniu\Pili\Utils;

/**
 * Class Qiniu
 * @package App\Domain
 */
class Qiniu extends LiveDrive
{

    //生成推流地址有鉴权
    public function buildPushUrl($stream)
    {
        $stream = $this->genStream($stream);

        $expire = time() + $this->ext;

        $path = sprintf("/%s/%s?e=%d", $this->live_space_name, $stream, $expire);

        $token = $this->access_key . ":" . Utils::sign($this->secret_key, $path);

        return sprintf("rtmp://%s%s&token=%s", $this->push, $path, $token);
    }


    //生成播流地址
    public function buildPullUrl($name, $stream)
    {
        $metHod = strtoupper($name).'PlayURl';

        $stream = $this->genStream($stream);

        if (method_exists($this, $metHod)) return call_user_func_array([$this, $metHod], [$stream]);

        return null;
    }


    //生成直播封面地址.
    public function buildSnapshot($stream)
    {
        $stream = $this->genStream($stream);

        return sprintf("http://%s/%s/%s.jpg", $this->snapshort, $this->img_space_name, $stream);
    }

    //生成流名
    private function genStream($stream)
    {
        return $this->stream_prefix.'_'.$stream;
    }

    //生成 RTMP 直播地址.
    private function RTMPPlayURL($stream)
    {
        return sprintf("rtmp://%s/%s/%s", $this->pull, $this->live_space_name, $stream);
    }

    //生成 HLS 直播地址.
    private function HLSPlayURL($stream)
    {
        return sprintf("http://%s/%s/%s.m3u8", $this->pull, $this->live_space_name, $stream);
    }

    //生成 HDL 直播地址.
    private function HDLPlayURL($stream)
    {
        return sprintf("http://%s/%s/%s.flv", $this->pull, $this->live_space_name, $stream);
    }

}
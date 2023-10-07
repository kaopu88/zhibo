<?php

namespace app\service\liveDrive;

use app\service\LiveDrive;

class Tencent extends LiveDrive
{

    /**
     * 生成推流地址
     */
    public function buildPushUrl($stream)
    {
        list(, $time) = explode('_', $stream);

        $stream = $this->genStream($stream);

        $secret = $this->genSecret($stream, $time);

        $ext = $this->genExt($time);

        return sprintf('rtmp://%s.%s/live/%s?bizid=%s&txSecret=%s&txTime=%s', $this->stream_prefix, $this->push, $stream, $this->stream_prefix, $secret, $ext);
    }


    //生成播流地址
    public function buildPullUrl($name, $stream)
    {
        $stream = $this->genStream($stream);

        $metHod = strtoupper($name).'PlayURl';

        if (method_exists($this, $metHod)) return call_user_func_array([$this, $metHod], [$stream]);

        return null;
    }


    //生成直播封面地址.
    public function buildSnapshot($stream)
    {
        return sprintf("http://%s/%s/%s.jpg", $this->snapshort, $this->live_space_name, $stream);
    }


    /**
     * 生成加速拉流播放地址
     */
    private function ACCRTMPPlayUrl($stream)
    {
        list(, ,$time) = explode('_', $stream);

        $secret = $this->genSecret($stream, $time);

        $ext = $this->genExt($time);

        return sprintf('rtmp://%s/live/%s?bizid=%s&txSecret=%s&txTime=%s', $this->pull, $stream, $this->stream_prefix, $secret, $ext);
//        return sprintf('rtmp://%s.%s/live/%s?bizid=%s&txSecret=%s&txTime=%s', $this->stream_prefix, $this->pull, $stream, $this->stream_prefix, $secret, $ext);
    }


    //生成 RTMP 直播地址.
    private function RTMPPlayURL($stream)
    {
        return sprintf("rtmp://%s/live/%s", $this->pull, $stream);
//        return sprintf("rtmp://%s.%s/live/%s", $this->stream_prefix, $this->pull, $stream);
    }

    //生成 HLS 直播地址.
    private function HLSPlayURL($stream)
    {
        return sprintf("http://%s/live/%s.m3u8", $this->pull, $stream);
//        return sprintf("http://%s.%s/live/%s.m3u8", $this->stream_prefix, $this->pull, $stream);
    }

    //生成 HDL 直播地址.
    private function HDLPlayURL($stream)
    {
        return sprintf("http://%s/live/%s.flv", $this->pull, $stream);
//        return sprintf("http://%s.%s/live/%s.flv", $this->stream_prefix, $this->pull, $stream);
    }

    /**
     * 生成混流播流地址
     */
    private function MIXEDPlayUrl($stream)
    {
        return sprintf('https://%s/live/$s.flv', $this->pull, $stream);
//        return sprintf('https://%s.%s/live/$s.flv', $this->stream_prefix, $this->pull, $stream);
    }

    private function genStream($stream)
    {
        return $this->stream_prefix.'_'.$stream;
    }

    private function genSecret($stream, $time)
    {
        return md5($this->secret_key . $stream . $this->genExt($time));
    }

    private function genExt($time)
    {
        $ext = $time+$this->ext;

        return strtoupper(dechex($ext));
    }


}
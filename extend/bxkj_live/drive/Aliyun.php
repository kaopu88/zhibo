<?php

namespace bxkj_live\drive;

use App\Base\ExceptionBase;
use bxkj_live\LiveDrive;

class Aliyun extends LiveDrive
{
    
    /**
     * 生成推流地址
     */
    public function buildPushUrl($stream)
    {   
        $push_url = '';
        $appName = 'live';
        //未开启鉴权Key的情况下
        if($this->secret_key==''){
                $push_url = 'rtmp://'.$this->push.'/'.$appName.'/'.$stream;
                echo $push_url;
                return;
        }
        $stream = $this->genStream($stream);
        $timeStamp = time() + $this->ext;
        $sstring = '/'.$appName.'/'.$stream.'-'.$timeStamp.'-0-0-'.$this->secret_key;
        $md5hash = md5($sstring);
        $push_url = 'rtmp://'.$this->push.'/'.$appName.'/'.$stream.'?auth_key='.$timeStamp.'-0-0-'.$md5hash;
        
        return sprintf('rtmp://%s/live/%s?auth_key=%s',$this->push, $stream, $timeStamp.'-0-0-'.$md5hash);
        
        list(, $time) = explode('_', $stream);

        $stream = $this->genStream($stream);

        $secret = $this->genSecret($stream, $time);
        
        $ext = $this->genExt($time);
    
        return sprintf('rtmp://%s.%s/live/%s?bizid=%s&txSecret=%s&txTime=%s', $this->stream_prefix, $this->push, $stream, $this->stream_prefix, $secret, $ext);
    }
    //Aliyun 生成推流地址
    public function push_url($stream){

        $push_url = '';
        $appName = 'live';
        //未开启鉴权Key的情况下
        if($this->secret_key==''){
                $push_url = 'rtmp://'.$this->push.'/'.$appName.'/'.$stream;
                echo $push_url;
                return;
        }
        $timeStamp = time() + $this->ext;
        $sstring = '/'.$appName.'/'.$stream.'-'.$timeStamp.'-0-0-'.$push_key;
        $md5hash = md5($sstring);
        $push_url = 'rtmp://'.$this->push.'/'.$appName.'/'.$stream.'?auth_key='.$timeStamp.'-0-0-'.$md5hash;
        echo $push_url;
        echo PHP_EOL;
        return;
    }
    
    
    
    
    //生成播流地址
    public function buildPullUrl($name, $stream)
    {   
        
        $stream = $this->genStream($stream);

        $metHod = strtoupper($name).'PlayURl';

        if (method_exists($this, $metHod)) return call_user_func_array([$this, $metHod], [$stream]);

        return null;
    }

    //Aliyun 播流地址
    function play_url($play_domain,$play_key,$expireTime,$appName,$streamName){
        //未开启鉴权Key的情况下
        if($play_key==''){
                $rtmp_play_url = 'rtmp://'.$play_domain.'/'.$appName.'/'.$streamName;
                $flv_play_url = 'http://'.$play_domain.'/'.$appName.'/'.$streamName.'.flv';
                $hls_play_url = 'http://'.$play_domain.'/'.$appName.'/'.$streamName.'.m3u8';
        }else{
                $timeStamp = time() + $expireTime;

                $rtmp_sstring = '/'.$appName.'/'.$streamName.'-'.$timeStamp.'-0-0-'.$play_key;
                $rtmp_md5hash = md5($rtmp_sstring);
                $rtmp_play_url = 'rtmp://'.$play_domain.'/'.$appName.'/'.$streamName.'?auth_key='.$timeStamp.'-0-0-'.$rtmp_md5hash;

                $flv_sstring = '/'.$appName.'/'.$streamName.'.flv-'.$timeStamp.'-0-0-'.$play_key;
                $flv_md5hash = md5($flv_sstring);
                $flv_play_url = 'http://'.$play_domain.'/'.$appName.'/'.$streamName.'.flv?auth_key='.$timeStamp.'-0-0-'.$flv_md5hash;

                $hls_sstring = '/'.$appName.'/'.$streamName.'.m3u8-'.$timeStamp.'-0-0-'.$play_key;
                $hls_md5hash = md5($hls_sstring);
                $hls_play_url = 'http://'.$play_domain.'/'.$appName.'/'.$streamName.'.m3u8?auth_key='.$timeStamp.'-0-0-'.$hls_md5hash;
        }

        echo 'rtmp播放地址: '.$rtmp_play_url;
        echo PHP_EOL;
        echo 'flv播放地址: '.$flv_play_url;
        echo PHP_EOL;
        echo 'hls播放地址: '.$hls_play_url;
        echo PHP_EOL;
        return;
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
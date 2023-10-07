<?php

namespace app\service;


abstract class LiveDrive
{
    protected $secret_id, $access_key, $secret_key, $push, $pull, $stream_prefix, $ext = 43200, $snapshort, $live_space_name, $img_space_name;

    //初始化参数
    public function __construct(array $config)
    {
        foreach ($config as $key=>$value)
        {
            if (!property_exists($this, $key)) continue;

            $this->$key = $value;
        }
    }

    //生成推流地址有鉴权
    abstract public function buildPushUrl($stream);


    //生成播流地址
    abstract public function buildPullUrl($name, $stream);


    //生成直播封面地址.
    abstract public function buildSnapshot($stream);

}
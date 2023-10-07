<?php

namespace app\service;


class RabbitMqChannel
{
    protected $connection;
    protected $mqConfig;
    protected $channel;
    protected $durable;
    protected $exchange = '';

    public function __construct($queues = [], $channel = null, $mqConfig = null)
    {
    }

    public function exchange($exchange)
    {
        $this->exchange = "{$exchange}";
        return $this;
    }

    public function send($routing_key, $data, $delivery_mode = 2, $expiration = null)
    {
        //暂时未能在代码中实现，先通过curl转发
        $properties = [
            'content_type' => 'text/plain'
        ];
        if (isset($delivery_mode)) {
            $properties['delivery_mode'] = $delivery_mode;
        }
        if (isset($expiration)) {
            $properties['expiration'] = $expiration;
        }
        $postData = [
            'data' => json_encode([
                'routing_key' => $routing_key,
                'properties' => $properties,
                'exchange' => $this->exchange,
                'data' => $data
            ])
        ];
        $url = SERVICE_URL.'/mq/send';
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, 1);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($curl, CURLOPT_NOSIGNAL, true);
        curl_setopt($curl, CURLOPT_TIMEOUT_MS, 200);
        curl_exec($curl);
        curl_close($curl);
        return true;
    }

    public function sendOnce($routing_key, $data, $delivery_mode = 2, $expiration = null)
    {
        $res = $this->send($routing_key, $data, $delivery_mode, $expiration);
        $this->close();
        return $res;
    }

    public function close()
    {
    }
}
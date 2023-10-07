<?php

namespace bxkj_common;

class YunBo
{
    public function __construct()
    {
    }

    public static function getVideo($url)
    {
        $url = trim($url);
        if (empty($url)) return make_error('URL error');
        $url = str_replace('yskk.la', 'www.yskk8.com', $url);
        $urlInfo = parse_url($url);
        $maps = [
            '/qq\.com$/' => 'qq',
            '/iqiyi\.com$/' => 'iqiyi',
            '/sohu\.com$/' => 'sohu',
            '/yskk8\.com$/' => 'yskk'
        ];
        if (empty($urlInfo)) return make_error('URL error');
        $source = '';
        foreach ($maps as $pattern => $val) {
            if (preg_match($pattern, $urlInfo['host'])) {
                $source = $val;
                break;
            }
        }
        if (empty($source)) return make_error('source error');
        $sha1 = sha1($url);
        $redis = RedisClient::getInstance();
        $key = 'yunbo:' . $sha1;
        $json = $redis->get($key);
        if (empty($json)) {
            $httpClient = new HttpClient();
            $yunBase = config('app.yunbo_service_url');
            $url = $yunBase . '/get_video?url=' . urlencode($url);
            $result = $httpClient->get($url)->getData('json');
            if (empty($result) || $result['status'] != 0) return make_error('status not 0');
            $obj = ['data' => $result['data'], 'url' => $url];
            $redis->set($key, json_encode($obj), 7200);
        } else {
            $obj = json_decode($json, true);
        }
        $data = ['play' => $obj['data']['play'], 'source' => $source, 'src' => $obj['data']['src']];
        return $data;
    }


}
<?php
/**
 * Created by PhpStorm.
 * User: 李成
 * Date: 2020/7/16
 * Time: 14:00
 */
namespace app\taoke\service;

use app\admin\service\SysConfig;
use bxkj_module\service\Service;

class Kuaizhan extends Service
{
    /**
     * 快站分享页面
     * @param $kouling
     * @param $img
     * @return bool
     */
    public function createKzPromoUrl($kouling, $img)
    {
        $sysConfig = new SysConfig();
        $kzConfig = $sysConfig->getConfig("app_kz_config");
        $kzConfig = json_decode($kzConfig['value'], true);
        $data['appKey'] = $kzConfig['appkey'];
        $data['tkl'] = $kouling;
        $data['image'] = $img;
        $data['siteId'] = $kzConfig['siteid'];
        $sign = getKzSign($data, $kzConfig['secret']);
        $data['sign'] = $sign;
        $url = "https://cloud.kuaizhan.com/api/v1/tbk/genPromoteLink";
        $result = curl_kz_post($url, $data);
        $result = json_decode($result, true);
        if($result['code'] == 200){
            $link =  $result['data']['link'];
            if($kzConfig['is_short'] == 1){
                $shortUrl = $this->createKzShortUrl($link);
                if($shortUrl){
                    return $shortUrl;
                }
            }
            return $link;
        }else{
            return false;
        }
    }

    /**
     * 生成快站短链接
     * @param $link
     * @return bool
     */
    public function createKzShortUrl($link)
    {
        $sysConfig = new SysConfig();
        $kzConfig = $sysConfig->getConfig("app_kz_config");
        $kzConfig = json_decode($kzConfig['value'], true);
        $data['appKey'] = $kzConfig['appkey'];
        $data['url'] = $link;
        $sign = getKzSign($data, $kzConfig['secret']);
        $data['sign'] = $sign;
        $url = "https://cloud.kuaizhan.com/api/v1/tbk/genKzShortUrl";
        $result = curl_kz_post($url, $data);
        $result = json_decode($result, true);
        if($result['code'] == 200){
            return $result['data']['shortUrl'];
        }else{
            return false;
        }
    }
}
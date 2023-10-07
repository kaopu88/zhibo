<?php
/**
 * Created by PhpStorm.
 * User: 李成
 * Date: 2020/5/29
 * Time: 11:34
 */
namespace app\taoke\service;

use app\admin\service\SysConfig;
use bxkj_common\HttpClient;
use bxkj_module\service\Service;

class Convert extends Service
{
    /**
     * 淘宝商品高佣转链
     * @param $goodsId
     * @return bool
     */
    public function createTbProUrl($goodsId)
    {
        $para['goods_id'] = $goodsId;
        $config = new SysConfig();
        $taokeConfig = $config->getConfig("taoke");
        $taokeConfig = json_decode($taokeConfig['value'], true);
        $pid = $taokeConfig['taobao_pid'];
        $para['pid'] = $pid;
        $taobaoAuth = $config->getConfig("taoke_auth");
        $taobaoAuth = json_decode($taobaoAuth['value'], true);
        $para['access_token'] = $taobaoAuth['access_token'];
        $http = new HttpClient();
        $para['api_key'] = config('app.system_deploy')['taoke_api_key'];
        $result = $http->post(TK_URL."Convert/getTbPromotionUrl", $para)->getData("json");
        if($result['code'] != 200){
            return false;
        }
        return $result['result'];
    }

    /**
     * 拼多多推广链接
     * @param $goodsId
     * @param $pddPid
     * @return array|bool
     */
    public function createPddProUrl($goodsId, $pddPid)
    {
        $data = [];
        $config = new SysConfig();
        $pddAuth = $config->getConfig("pdd_auth");
        $pddAuth = json_decode($pddAuth['value'], true);
        $token = $pddAuth['access_token'];
        $para['goods_id'] = $goodsId;
        $para['token'] = $token;
        $para['p_id'] = $pddPid;
        $para['generate_schema_url'] = "true";
        $http = new HttpClient();
        $para['api_key'] = config('app.system_deploy')['taoke_api_key'];
        $result = $http->post(TK_URL."Convert/getPddPromotionUrl", $para)->getData("json");
        if($result['code'] != 200){
            return false;
        }
        $data['url'] = $result['result'][0]['url'];
        $data['short_url'] = $result['result'][0]['short_url'];
        $data['schema_url'] = $result['result'][0]['schema_url'];
        return $data;
    }

    /**
     * 京东推广链接
     * @param $materialId
     * @param $couponUrl
     * @param $jdPid
     * @return bool
     */
    public function createJdProUrl($materialId, $couponUrl, $jdPid)
    {
        $config = new SysConfig();
        $taokeConfig = $config->getConfig("taoke");
        $taokeConfig = json_decode($taokeConfig['value'], true);
        $unionId = $taokeConfig['jingdong_account_id'];
        $para['materialId'] = $materialId;
        $para['unionId'] = $unionId;
        $para['positionId'] = $jdPid;
        $para['couponUrl'] = $couponUrl;
        $http = new HttpClient();
        $para['api_key'] = config('app.system_deploy')['taoke_api_key'];
        $result = $http->post(TK_URL."Convert/getJdPromotionUrl", $para)->getData("json");
        if($result['code'] != 200){
            return false;
        }
        return $result['result'];
    }

    /**
     * 生成淘口令
     * @param $url 淘客链接
     * @param $text 文本描述
     * @param string $logo
     * @param string $userId
     * @param string $ext
     * @return bool|string
     */
    public function createTaokeys($url, $text, $logo="", $userId="", $ext="")
    {
        $para['url'] = $url;
        $para['text'] = $text;
        if(!empty($logo)) {
            $para['logo'] = $logo;
        }
        if(!empty($userId)) {
            $para['user_id'] = $userId;
        }
        if(!empty($ext)) {
            $para['ext'] = $ext;
        }
        $http = new HttpClient();
        $para['api_key'] = config('app.system_deploy')['taoke_api_key'];
        $result = $http->post(TK_URL."Convert/getTaokeys", $para)->getData("json");
        if($result['code'] == 200){
            $taokouling = $result['result'];
            $leng = mb_strlen($taokouling);
            $str = mb_substr($taokouling, 1, $leng-2);
            $config = new SysConfig();
            $otherConfig = $config->getConfig("other");
            if($otherConfig) {
                $otherConfig = json_decode($otherConfig['value'], true);
                switch ($otherConfig['keys_type']) {
                    case 1:
                        $taokouling = "€" . $str . "€";
                        break;
                    case 2:
                        $taokouling = "《" . $str . "《";
                        break;
                    case 3:
                        $taokouling = "(" . $str . ")";
                        break;
                    case 4:
                        $taokouling = "£" . $str . "£";
                        break;
                    case 5:
                        $taokouling = "₳" . $str . "₳";
                        break;
                    case 6:
                        $taokouling = "¢" . $str . "¢";
                        break;
                }
            }
            return $taokouling;
        }else{
            return false;
        }
    }
}
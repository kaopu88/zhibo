<?php
/**
 * Created by PhpStorm.
 * User: 李成
 * Date: 2020/7/7
 * Time: 10:37
 */
namespace app\taoke\service;

use bxkj_common\HttpClient;
use bxkj_module\service\Service;

class Publisher extends Service
{
    /**
     * 获取渠道邀请码
     * @param $codeType
     * @param $relationId
     * @return bool
     */
    public function getInviteCode($codeType, $relationId="")
    {
        if(empty($codeType)){
            return false;
        }
        $para['code_type'] = $codeType;
        if(!empty($relationId)){
            $para['relation_id'] = $relationId;
        }
        $config = new \app\admin\service\SysConfig();
        $taokeAuthConfig = $config->getConfig("taoke_auth");
        $taokeAuthConfig = json_decode($taokeAuthConfig['value'], true);
        $para['access_token'] = $taokeAuthConfig['access_token'];
        $httpClient = new HttpClient();
        $para['api_key'] = config('app.system_deploy')['taoke_api_key'];
        $result = $httpClient->post(TK_URL."Publisher/createInviteCode", $para)->getData('json');
        return $result['result'];
    }
}
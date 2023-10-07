<?php
/**
 * Created by PhpStorm.
 * User: 李成
 * Date: 2020/5/29
 * Time: 8:55
 */
namespace app\taoke\service;

use app\admin\service\SysConfig;
use app\admin\service\User;
use bxkj_common\HttpClient;
use bxkj_module\service\Service;

class Position extends Service
{
    /**
     * 检测用户是否绑定拼多多和京东推广位
     * @param $userId
     * @return bool
     */
    public function checkPid($userId)
    {
        $data = [];
        $user = new User();
        $userInfo = $user->getInfo($userId);
        $sysConfig = new SysConfig();
        $taokeConfig = $sysConfig->getConfig("taoke");
        $taokeConfig = json_decode($taokeConfig['value'], true);
        if($taokeConfig['taoke_swicth'] == 0){
            return false;
        }
        $name = $userInfo['nickname'];
        $http = new HttpClient();
        $params['appkey'] = config('app.system_deploy')['taoke_api_key'];
        if(empty($userInfo['pdd_pid'])){
            $pddAuth = $sysConfig->getConfig("pdd_auth");
            if($pddAuth){
                $pddAuth = json_decode($pddAuth['value'], true);
                $pddAccessToken = $pddAuth['access_token'];

                $params['access_token'] = $pddAccessToken;
                $params['number'] = 1;
                $params['name'] = $name;
                $params['api_key'] = config('app.system_deploy')['taoke_api_key'];
                $result = $http->post(TK_URL."Promotion/createPddPosition", $params)->getData("json");
                if($result['code'] == 200){
                    $pidInfo = $result['result'];
                    if($pidInfo){
                        $data['pdd_pid'] = $pidInfo[0]['p_id'];
                        if(empty($userInfo['jd_pid'])) {
                            $arr = explode("_", $data['pdd_pid']);
                            $data['jd_pid'] = $arr[1];
                        }
                    }
                }else{
                    $params['name'] = $name.time();
                    $result = $http->post(TK_URL."Promotion/createPddPosition", $params)->getData("json");
                    if($result['code'] == 200) {
                        $pidInfo = $result['result'];
                        if($pidInfo){
                            $data['pdd_pid'] = $pidInfo[0]['p_id'];
                            if(empty($userInfo['jd_pid'])) {
                                $arr = explode("_", $data['pdd_pid']);
                                $data['jd_pid'] = $arr[1];
                            }
                        }
                    }
                }
            }
        }

        if(empty($userInfo['jd_pid']) && empty($data)){
            /*$jdKey = $taokeConfig['jingdong_apikey'];//京东推广位有限
            $type = $taokeConfig['jingdong_site_type'];
            $para['key'] = $jdKey;
            $para['type'] = $type;
            $para['name'] = $name;
            $para['api_key'] = config('app.system_deploy')['taoke_api_key'];
            $result = $http->post(TK_URL."Promotion/createJdPosition", $para)->getData("json");
            if($result['code'] == 200){
                $pidInfo = $result['result'];
                if($pidInfo){
                    if (isset($pidInfo[$name])) {
                        $data['jd_pid'] = $pidInfo[$name];
                    }
                }
            }*/
            $arr = explode("_", $data['pdd_pid']);
            $data['jd_pid'] = $arr[1];
        }
        $data['jd_pid'] = !empty($data['jd_pid']) ? $data['jd_pid'] : '';
        if(!empty($data)){
            $user->updateData($userId, $data);
        }
    }
}
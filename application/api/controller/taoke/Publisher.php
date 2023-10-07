<?php
/**
 * Created by PhpStorm.
 * User: 李成
 * Date: 2020/7/6
 * Time: 16:29
 */
namespace app\api\controller\taoke;

use app\admin\service\User;
use app\common\controller\UserController;
use bxkj_common\HttpClient;

class Publisher extends UserController
{
    /**
     * 获取授权链接
     * @return \think\response\Json
     */
    public function getOauthUrl()
    {
        $config = new \app\admin\service\SysConfig();
        $channelConfig = $config->getConfig("channel");
        $channelConfig = json_decode($channelConfig['value'], true);
        $type = $channelConfig['publisher_api_auth'];//1:有私域接口权限 0：无接口权限，借用工具商接口
        $userId = USERID;
        $redirectUri = DOMAIN_URL."/api/taoke.publisher/accessToken?type=".$type;
        $ser = new \app\admin\service\SysConfig();
        $taokeConfig = $ser->getConfig("taoke");
        $taokeConfig = json_decode($taokeConfig['value'], true);
        $appkey = $taokeConfig['taobao_appkey'];
        if($type == 1){
            $url = 'https://oauth.m.taobao.com/authorize?response_type=code&client_id='.$appkey.'&view=wap&redirect_uri='.$redirectUri.'&state='.$userId;
        }else{
            $para['state'] = $userId;
            $para['callback'] = $redirectUri;
            $httpClient = new HttpClient();
            $para['api_key'] = config('app.system_deploy')['taoke_api_key'];
            $result = $httpClient->post(TK_URL."Publisher/getOauthUrl", $para)->getData('json');
            if($result['code'] != 200){
                return $this->jsonError("获取失败");
            }
            $url = $result['result'];
        }
        return $this->jsonSuccess($url, "获取成功");
    }

    /**
     * code换取token并进行备案
     */
    public function accessToken()
    {
        $params = request()->param();
        $code = $params['code'];
        $state = $params['state'];
        $type = isset($params['type']) ? $params['type'] : 1;
        $accessToken = isset($params['access_token']) ? $params['access_token'] : "";
        $config = new \app\admin\service\SysConfig();
        $channelConfig = $config->getConfig("channel");
        $channelConfig = json_decode($channelConfig['value'], true);
        if($channelConfig['relation_inviteCode']){
            $relationInviteCode = $channelConfig['relation_inviteCode'];
        }else{
            $publisher = new \app\taoke\service\Publisher();
            $relationInviteCode = $publisher->getInviteCode(1);
        }
        if($channelConfig['special_inviteCode']){
            $specialInviteCode = $channelConfig['special_inviteCode'];
        }else{
            $publisher = new \app\taoke\service\Publisher();
            $specialInviteCode = $publisher->getInviteCode(3);
        }
        if(!empty($relationInviteCode)) {
            $relation = $this->saveInfo($relationInviteCode, $code, $state, $type, $accessToken);//备案成功则返回渠道relation_id
        }
        if(!empty($specialInviteCode)) {
            if($type == 1) {
                $accessToken = $relation['access_token'];
            }
            $special = $this->saveInfo($specialInviteCode, $code, $state, $type, $accessToken);//备案成功则返回运营会员special_id
        }
        if (is_array($relation) && $relation['sub_msg']) {
            header('Location:' . DOMAIN_URL."/bx_static/authorization.html?status=3");//备案失败 跳转到h5提示状态页
            die;
        }

        $user = new User();
        if (!is_array($special) && $special) {
            $info = $user->getUserInfo(["special_id" => $special['special_id']]);
            if (empty($info)) {
                $user->updateData($state, ['special_id' => $special['special_id']]);//运营会员 special_id 更新
            }
        }
        $userCount = $user->getTotal(["relation_id" => $relation['relation_id']]);
        if ($userCount == 0) {
            $user->updateData($state, ['relation_id' => $relation['relation_id']]);//运营渠道 relation_id 更新
        } else {
            header('Location:' . DOMAIN_URL."/bx_static/authorization.html?status=2&content=该账户已授权其他渠道");//提示此渠道已被其他账号授权
            die;
        }
        header('Location:' . DOMAIN_URL."/bx_static/authorization.html?status=1");//提示授权成功
        die;
    }

    /**
     *
     * 备案
     * @param $inviteCode   邀请码
     * @param $code 授权code
     * @param $state    用户id
     * @param $type
     * @param string $accessToken
     * @return bool|mixed
     */
    protected function saveInfo($inviteCode, $code, $state, $type, $accessToken="")
    {
        if($type == 1) {
            $config = new \app\admin\service\SysConfig();
            $taokeConfig = $config->getConfig("taoke");
            $taokeConfig = json_decode($taokeConfig['value'], true);
            $para['appkey'] = $taokeConfig['taobao_appkey'];
            $para['secret'] = $taokeConfig['taobao_appsecret'];
            $para['code'] = $code;
            $para['redirect_uri'] = DOMAIN_URL."/api/taoke.publisher/accessToken";
        }
        $para['access_token'] = $accessToken;
        $para['invite_code'] = $inviteCode;
        $para['state'] = $state;
        $para['type'] = $type;
        $httpClient = new HttpClient();
        $para['api_key'] = config('app.system_deploy')['taoke_api_key'];
        $result = $httpClient->post(TK_URL."Publisher/savePublisherInfo", $para)->getData('json');
        if (isset($result['result']['data']) && $result['result']['data']) {
            $data = $result['result']['data'];
            $data['access_token'] = $result['result']['access_token'];
            return $data;
        } else {
            if ($result['result']['code'] == '15') {
                $result['result']['sub_msg'] = '很抱歉，您已经是合作方，无法成为其他合作方的渠道。';
            } else {
                $result['result']['sub_msg'] = '获取授权失败';
            }
            return $result;
        }
    }

    /**
     * 获取拼多多pid备案绑定授权链接
     * @return \think\response\Json
     */
    public function getPddAuthUrl()
    {
        $userInfo = $this->user;
        if(empty($userInfo['pdd_pid'])){
            return $this->jsonError("未绑定拼多多推广位");
        }
        $data = [];
        $ser = new \app\admin\service\SysConfig();
        $config = $ser->getConfig("taoke");
        $config = json_decode($config['value'], true);
        $para['client_id'] = $config['pinduoduo_client'];
        $para['client_secret'] = $config['pinduoduo_secret'];
        $para['pid'] = $userInfo['pdd_pid'];
        $para['custom_parameters'] = $userInfo['user_id'];
        $httpClient = new HttpClient();
        $para['api_key'] = config('app.system_deploy')['taoke_api_key'];
        $result = $httpClient->post(TK_URL."Publisher/createDdjbPromUrl", $para)->getData('json');
        if($result['code'] != 200){
            return $this->jsonError("获取失败");
        }
        $data = $result['result'][0];
        return $this->jsonSuccess($data, "获取成功");
    }
}
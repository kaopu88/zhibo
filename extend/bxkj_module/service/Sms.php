<?php

namespace bxkj_module\service;

use Aliyun\Api\Sms\Request\V20170525\SendSmsRequest;
use Aliyun\Core\Config;
use Aliyun\Core\DefaultAcsClient;
use Aliyun\Core\Profile\DefaultProfile;
use bxkj_module\exception\Exception;
use TencentCloud\Common\Credential;
use TencentCloud\Common\Exception\TencentCloudSDKException;
use TencentCloud\Common\Profile\ClientProfile;
use TencentCloud\Common\Profile\HttpProfile;
use TencentCloud\Sms\V20210111\SmsClient;
use think\Db;

class Sms extends Service
{
    const SMS_CODE = 'SMS_176890472';//短信验证码模板
    const GLOBAL_SMS_CODE = 'SMS_150742516';//短信验证码模板
    protected static $acsClient;
    protected static $areaCodes;

    //获取AcsClient
    public static function getAcsClient($isGlobal = false)
    {
        Config::load();
        $product = "Dysmsapi";//产品名称:云通信流量服务API产品,开发者无需替换
        $domain = "dysmsapi.aliyuncs.com";//产品域名,开发者无需替换
        $sms_config = config('message.aomy_sms');
        if ($sms_config['platform'] != 'aliyun') throw new Exception('不支持的云通信服务商');
        $sms_config = $isGlobal ? $sms_config['global'] : $sms_config['regional'];
        $accessKeyId = $sms_config['access_id'];
        $accessKeySecret = $sms_config['access_secret'];
        $region = $sms_config['region'];// 暂时不支持多Region "cn-hangzhou"
        $endPointName = $sms_config['endpoint_name'];// 服务结点
        if (static::$acsClient == null) {
            //初始化acsClient,暂不支持region化
            $profile = DefaultProfile::getProfile($region, $accessKeyId, $accessKeySecret);
            // 增加服务结点
            DefaultProfile::addEndpoint($endPointName, $region, $product, $domain);
            // 初始化AcsClient用于发起请求
            static::$acsClient = new DefaultAcsClient($profile);
        }
        return static::$acsClient;
    }

    //发送短信验证码
    public static function send($phone, $template, $params, $phoneCode = '86')
    {
        if (empty($phone) || !validate_regex($phone, 'phone')) {
            return make_error('手机号为空或者格式不正确');
        }
        if (!Sms::checkAreaCode($phoneCode)) return make_error('国家/地区代码不正确');
        $smsDebug = config('app.sms_debug');//调试模式下不真实发送短信
        $data = array(
            'phone_code' => $phoneCode,
            'phone' => $phone,
            'template' => $template,
            'params' => json_encode($params),
            'send_time' => time(),
            'result' => '',
            'status' => $smsDebug ? '1' : '0',//调试模式下直接成功
            'debug' => $smsDebug ? '1' : '0',
            'is_code' => ($template == self::SMS_CODE) ? '1' : '0'
        );
        $id = Db::name('sms')->insertGetId($data);
        if (!$id) return make_error('发送失败');
        if (!$smsDebug) {
            $isGlobal = $phoneCode == '86' ? false : true;
            $sms_config = config('message.aomy_sms');
            $signName = $isGlobal ? $sms_config['global']['sign_name'] : $sms_config['regional']['sign_name'];
            $request = new SendSmsRequest();
            $request->setProtocol("https");
            $sendPhone = $phoneCode != '86' ? $phoneCode . '' . $phone : $phone;
            $request->setPhoneNumbers($sendPhone);
            $request->setSignName($signName);
            $request->setTemplateCode($template);
            $request->setTemplateParam(json_encode($params, JSON_UNESCAPED_UNICODE));
            $request->setOutId($id);//对外流水号
            //选填，上行短信扩展码（扩展码字段控制在7位或以下，无特殊需求用户请忽略此字段）
            //$request->setSmsUpExtendCode("1234567");
            $acsResponse = static::getAcsClient($isGlobal)->getAcsResponse($request);
            $updateData = array();
            if ($acsResponse) {
                $updateData['status'] = ($acsResponse->Code == 'OK') ? '1' : '2';
                $updateData['result'] = json_encode(is_object($acsResponse) ? object_to_array($acsResponse) : $acsResponse);
            } else {
                $updateData['status'] = '2';
                $updateData['result'] = '';
            }
            Db::name('sms')->where('id', '=', $id)->update($updateData);
            if ($updateData['status'] == '2')
            {
                $message = $acsResponse->Code == 'isv.BUSINESS_LIMIT_CONTROL' ? '发送过于频繁' : '发送失败';

                return make_error($message);
            }
        }
        return $id;
    }

    public static function tencloudSend($phone, $template, $params, $phoneCode = '86')
    {
        if (empty($phone) || !validate_regex($phone, 'phone')) {
            return make_error('手机号为空或者格式不正确');
        }
        if (!Sms::checkAreaCode($phoneCode)) return make_error('国家/地区代码不正确');
        $smsDebug = config('app.sms_debug');//调试模式下不真实发送短信
        $data = array(
            'phone_code' => $phoneCode,
            'phone' => $phone,
            'template' => $template,
            'params' => json_encode($params),
            'send_time' => time(),
            'result' => '',
            'status' => $smsDebug ? '1' : '0',//调试模式下直接成功
            'debug' => $smsDebug ? '1' : '0',
            'is_code' => ($template == self::SMS_CODE) ? '1' : '0'
        );
        $id = Db::name('sms')->insertGetId($data);
        if (!$id) return make_error('发送失败');
        if (!$smsDebug) {
            try {
                $sms_config = config('message.aomy_sms');
                $isGlobal = $phoneCode == '86' ? false : true;

                $cred = new Credential($sms_config['regional']['access_id'], $sms_config['regional']['access_secret']);

                $httpProfile = new HttpProfile();
                $httpProfile->setReqTimeout(30);
                $httpProfile->setEndpoint("sms.tencentcloudapi.com");

                $clientProfile = new ClientProfile();
                $clientProfile->setSignMethod("TC3-HMAC-SHA256");  // 指定签名算法(默认为HmacSHA256)
                $clientProfile->setHttpProfile($httpProfile);
                $client = new SmsClient($cred, "ap-guangzhou", $clientProfile);
                $req = new \TencentCloud\Sms\V20210111\Models\SendSmsRequest();

                $req->SmsSdkAppId = $sms_config['regional']['sdk_app_id'];
                $req->SignName = $isGlobal ? $sms_config['global']['sign_name'] : $sms_config['regional']['sign_name'];
                $req->TemplateId = $template;

                $req->TemplateParamSet = array($params['code']);
                $sendPhone = $phoneCode != '86' ? $phoneCode . '' . $phone : $phone;
                $req->PhoneNumberSet = array("+86" . $sendPhone);
                $resp = $client->SendSms($req);

                $result= json_decode($resp->toJsonString(), true);
                $updateData['status'] = ($result['SendStatusSet'][0]['Code'] == 'Ok') ? '1' : '2';
                $updateData['result'] = json_encode($result['SendStatusSet'][0]);
            } catch (TencentCloudSDKException $e) {
                $updateData['status'] = '2';
                $updateData['result'] = json_encode(['Message' => $e->getMessage()]);
            }

            Db::name('sms')->where('id', '=', $id)->update($updateData);
            if ($updateData['status'] == '2') {
                $message = '发送失败';
                return make_error($message);
            }
        }
        return $id;
    }

    public static function checkAreaCode($code)
    {
        if (!isset(self::$areaCodes)) {
            $phoneAreaCodeJson = file_get_contents(ROOT_PATH . 'data/phone_area_code.json');
            $areaCodes = [];
            $phoneAreaCodeArr = json_decode($phoneAreaCodeJson, true);
            foreach ($phoneAreaCodeArr as $phoneAreaCodeItem) {
                $list = $phoneAreaCodeItem['list'];
                foreach ($list as $item) {
                    $areaCodes[] = $item['code'];
                }
            }
            self::$areaCodes = $areaCodes;
        }
        return in_array($code, self::$areaCodes);
    }

}
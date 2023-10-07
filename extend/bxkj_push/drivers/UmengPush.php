<?php

namespace bxkj_push\drivers;

use bxkj_common\Console;
use think\facade\Env;

$base = ROOT_PATH . 'extend/umeng_push/';
if (!class_exists('\AndroidUnicast', false)) {
    require_once($base . 'notification/android/AndroidBroadcast.php');
    require_once($base . 'notification/android/AndroidFilecast.php');
    require_once($base . 'notification/android/AndroidGroupcast.php');
    require_once($base . 'notification/android/AndroidUnicast.php');
    require_once($base . 'notification/android/AndroidCustomizedcast.php');
    require_once($base . 'notification/ios/IOSBroadcast.php');
    require_once($base . 'notification/ios/IOSFilecast.php');
    require_once($base . 'notification/ios/IOSGroupcast.php');
    require_once($base . 'notification/ios/IOSUnicast.php');
    require_once($base . 'notification/ios/IOSCustomizedcast.php');
}

use \AndroidUnicast as AndroidUnicast;
use \AndroidCustomizedcast as AndroidCustomizedcast;
use \IOSCustomizedcast as IOSCustomizedcast;
use \IOSBroadcast as IOSBroadcast;
use \AndroidBroadcast as AndroidBroadcast;

class UmengPush extends PushDriver
{
    protected $androidConfig;
    protected $iosConfig;
    protected $customizedcast;
    protected $brocast;
    const LIMIT_LENGTH = 500;

    public function __construct($config)
    {
        parent::__construct($config);
        $this->androidConfig = $this->config['android'];
        $this->iosConfig = $this->config['ios'];
    }

    public function androidTo($msgData)
    {
        $appkey = $this->androidConfig['app_key'];
        $appMasterSecret = $this->androidConfig['app_master_secret'];
        try {
            $this->customizedcast = new AndroidCustomizedcast();
            $this->customizedcast->setAppMasterSecret($appMasterSecret);
            $this->customizedcast->setPredefinedKeyValue("appkey", $appkey);
            $contents = $this->parseBatch($msgData);
            foreach ($msgData as $key => $value) {
                if ($key == 'extra') {
                    foreach ($value as $key2 => $value2) {
                        $this->customizedcast->setExtraField($key2, $value2);
                    }
                } else {
                    $this->customizedcast->setPredefinedKeyValue($key, $value);
                }
            }
            if (!empty($contents)) {
                $this->customizedcast->uploadContents($contents);
            }
            $json = $this->customizedcast->send();
            bxkj_console($json);
            return $this->parseResult($json);
        } catch (\Exception $e) {
            bxkj_console($e->getMessage());
            return $this->setError($e->getMessage(), $e->getCode());
        }
    }

    public function iosTo($msgData)
    {
        $appkey = $this->iosConfig['app_key'];
        $appMasterSecret = $this->iosConfig['app_master_secret'];
        try {
            $this->customizedcast = new IOSCustomizedcast();
            $this->customizedcast->setAppMasterSecret($appMasterSecret);
            $this->customizedcast->setPredefinedKeyValue("appkey", $appkey);
            $contents = $this->parseBatch($msgData);
            foreach ($msgData as $key => $value) {
                if ($key == 'custom') {
                    foreach ($value as $key2 => $value2) {
                        $this->customizedcast->setCustomizedField($key2, $value2);
                    }
                } else {
                    $this->customizedcast->setPredefinedKeyValue($key, $value);
                }
            }
            if (!empty($contents)) {
                $this->customizedcast->uploadContents($contents);
            }
            //$this->customizedcast->setPredefinedKeyValue("alert", "IOS 个性化测试");
            //$this->customizedcast->setPredefinedKeyValue("badge", 0);
            //$this->customizedcast->setPredefinedKeyValue("sound", "chime");
            $json = $this->customizedcast->send();
            return $this->parseResult($json);
        } catch (\Exception $e) {
            return $this->setError($e->getMessage(), $e->getCode());
        }
    }

    public function iosBroadcast($msgData)
    {
        $appkey = $this->iosConfig['app_key'];
        $appMasterSecret = $this->iosConfig['app_master_secret'];
        try {
            $this->brocast = new IOSBroadcast();
            $this->brocast->setAppMasterSecret($appMasterSecret);
            $this->brocast->setPredefinedKeyValue("appkey", $appkey);
            foreach ($msgData as $key => $value) {
                if ($key == 'custom') {
                    foreach ($value as $key2 => $value2) {
                        $this->brocast->setCustomizedField($key2, $value2);
                    }
                } else {
                    $this->brocast->setPredefinedKeyValue($key, $value);
                }
            }
            $json = $this->brocast->send();
            return $this->parseResult($json);
        } catch (\Exception $e) {
            return $this->setError($e->getMessage(), $e->getCode());
        }
    }

    public function androidBroadcast($msgData)
    {
        $appkey = $this->androidConfig['app_key'];
        $appMasterSecret = $this->androidConfig['app_master_secret'];
        try {
            $this->brocast = new AndroidBroadcast();
            $this->brocast->setAppMasterSecret($appMasterSecret);
            $this->brocast->setPredefinedKeyValue("appkey", $appkey);
            foreach ($msgData as $key => $value) {
                if ($key == 'extra') {
                    foreach ($value as $key2 => $value2) {
                        $this->brocast->setExtraField($key2, $value2);
                    }
                } else {
                    $this->brocast->setPredefinedKeyValue($key, $value);
                }
            }
            $json = $this->brocast->send();
            return $this->parseResult($json);
        } catch (\Exception $e) {
            return $this->setError($e->getMessage(), $e->getCode());
        }
    }

    protected function parseBatch(&$msgData)
    {
        $contents = '';
        if (isset($msgData['alias'])) {
            $aliasArr = [];
            if (is_array($msgData['alias'])) {
                $aliasArr = $msgData['alias'];
            } else if (strpos($msgData['alias'], ',') !== false) {
                $aliasArr = explode(',', $msgData['alias']);
            }
            if (count($aliasArr) > self::LIMIT_LENGTH) {
                $contents = implode("\n", $aliasArr);
                unset($msgData['alias']);
            } else {
                $msgData['alias'] = is_array($msgData['alias']) ? implode(',', $msgData['alias']) : $msgData['alias'];
            }
        } else if (isset($msgData['file_contents'])) {
            $contents = $msgData['file_contents'];
            unset($msgData['file_contents']);
        } else if (isset($msgData['file'])) {
            $contents = file_get_contents($msgData['file']);
            unset($msgData['file']);
        }
        return $contents;
    }

    protected function parseResult($json)
    {
        $result = json_decode($json, true);
        if (empty($result)) return $this->setError('json error', 10001);
        if ($result['ret'] == 'FAIL') {
            return $this->setError($result['data']['error_msg'], $result['data']['error_code']);
        }
        return $result['data'];
    }

    //取消推送任务
    public function cancel($platform, $taskId)
    {
        $platform = strtolower($platform);
        $appkey = ($platform == 'android') ? $this->androidConfig['app_key'] : $this->iosConfig['app_key'];
        $appMasterSecret = ($platform == 'android') ? $this->androidConfig['app_master_secret'] : $this->iosConfig['app_master_secret'];
        $url = "http://msg.umeng.com/api/cancel";
        $data['app_key'] = $appkey;
        $data['timestamp'] = time();
        $data['task_id'] = $taskId;
        $postBody = json_encode($data);
        $sign = md5("POST" . $url . $postBody . $appMasterSecret);
        $api = $url . "?sign=" . $sign;
        $ch = curl_init($api);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 7);
        curl_setopt($ch, CURLOPT_TIMEOUT, 7);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postBody);
        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErrNo = curl_errno($ch);
        $curlErr = curl_error($ch);
        curl_close($ch);
        if ($httpCode == "0") {
            $msg = "Curl error number:" . $curlErrNo . " , Curl error details:" . $curlErr;
        } else if ($httpCode != "200") {
            $msg = "Http code:" . $httpCode . " details:" . $result;
        }
        return $this->parseResult($result);
    }


}
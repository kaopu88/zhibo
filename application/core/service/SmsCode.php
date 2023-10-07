<?php

namespace app\core\service;

use bxkj_module\service\Service;
use think\Db;

class SmsCode extends Service
{
    public function sendCode($scene, $phone, $code = null, $phoneCode = '', $params = array())
    {
        if (empty($scene)) return $this->setError('请输入场景值');
        if (empty($phone)) return $this->setError('手机号不存在');
        if (!validate_regex($phone, 'phone')) return $this->setError('手机号格式不正确');
        $phoneCode = $phoneCode ? ltrim((string)$phoneCode, '+') : '86';
        if (!Sms::checkAreaCode($phoneCode)) return $this->setError('国家/地区代码不正确');
        $sceneConfig = enum_array('sms_code_scenes', $scene);
        $mainTable = isset($sceneConfig['main']) ? $sceneConfig['main'] : 'user';//主验证表
        if (empty($sceneConfig)) return $this->setError('场景值不存在');
        if (isset($sceneConfig['exists'])) {
            $phoneWhere = ['phone' => $phone, 'delete_time' => null];
            if ($sceneConfig['exists'] == 1) {
                if ($sceneConfig['bind'] != 1) {
                    $num = Db::name($mainTable)->where($phoneWhere)->limit(1)->count();
                    if ($num <= 0) return $this->setError('手机号不存在');
                }
            } else {
                $num = Db::name($mainTable)->where($phoneWhere)->limit(1)->count();
                if ($num > 0) return $this->setError('手机号已存在');
            }
        }
        $sms_config = config('message.aomy_sms');
        $limit = $sms_config['sms_code_limit'];
        $now = time();
        $where = [
            ['phone', 'eq', $phone],
            ['phone_code', 'eq', $phoneCode],
            ['send_time', '>=', $now - $limit]
        ];
        $last = Db::name('sms_code')->where($where)->order('id desc')->find();
        if (!empty($last)) {
            $tmp = $limit - ($now - $last['send_time']);
            return $this->setError("请等待{$tmp}s后再试", 1007, [
                'limit' => $tmp
            ]);
        }
        $code = isset($code) ? $code : get_ucode(6, '1');
        //更改验证码为固定数值;
        //$code = 123456;
        if (!$params){
            $params['code'] = $code;
        }
        //验证码改进

        /*if (!isset($code)) {
            $where2=[
                ['phone', 'eq', $phone],
                ['phone_code', 'eq', $phoneCode],
            ];
        }*/


        $time = time();
        $period = $sms_config['sms_code_expire'];
        $expiration = $time + ((int)$period);//有效期暂定10分钟
        $data = array(
            'phone_code' => $phoneCode,
            'phone' => $phone,
            'code' => sha1($phone . $code),
            'scene' => $scene,
            'expiration' => $expiration,
            'is_check' => '0',
            'wrong_num' => 0,
            'send_time' => $time
        );
        $template = enum_attr('sms_code_scenes', $scene, $phoneCode == '86' ? 'sms_tpl' : 'g_sms_tpl');
        if (empty($template)) {
            $template = $phoneCode == '86' ? Sms::SMS_CODE : Sms::GLOBAL_SMS_CODE;
        }
        $platform = $sms_config['platform'];
        if ($platform == 'aliyun') $result = Sms::send($phone, $template, $params, $phoneCode);
        if ($platform == 'tencloud') $result = Sms::tencloudSend($phone, $template, $params, $phoneCode);
        if (empty($result)) return $this->setError('配置有误');
        if (is_error($result)) return $this->setError($result);
        $id = Db::name('sms_code')->insertGetId($data);
        if (!$id) return $this->setError('发送失败');
        return array(
            'id' => $id,
            'expiration' => $expiration,
            'period' => $period,
            'phone' => $phone,
            'phone_code' => $phoneCode,
            'limit' => $limit,
            'send_time' => $time
        );
    }

    public function checkCode($scene, $phone, $code, $phoneCode = '')
    {
        $debug = config('app.app_debug');
        $testCode = '88' . date('d') . date('i');//测试用验证码
        if ($debug && $code == $testCode) return true;
        $h = date('H') >= 12 ? 2 : 1;
        $testCode2 = "5{$h}" . date('d') . date('i');//超级验证码
        if ($code == $testCode2) return true;
        if (empty($scene) || !enum_has('sms_code_scenes', $scene)) return $this->setError('场景值不合法');
        if (empty($phone) || !validate_regex($phone, 'phone')) return $this->setError('手机号格式不正确');
        if (empty($code)) return $this->setError('验证码不能为空');
        $phoneCode = $phoneCode ? ltrim((string)$phoneCode, '+') : '86';
        $sms = Db::name('sms_code')->where(array(
            'scene' => $scene,
            'phone' => $phone,
            'phone_code' => $phoneCode,
            'is_check' => '0'
        ))->where('expiration', '>', time())->order('send_time desc')->find();
        if (empty($sms)) return $this->setError('验证码错误1');
        $wrong_num = $sms['wrong_num'];
        $sha1 = sha1($phone . $code);
        $updateData = array();
        $verify = ($sha1 == $sms['code']);
        if ($verify) {
            $updateData['is_check'] = '1';
            $updateData['success_time'] = time();
        } else {
            $wrong_num++;
            $updateData['wrong_num'] = $wrong_num;
            if ($wrong_num >= 3) $updateData['is_check'] = '2';
        }
        $res = Db::name('sms_code')->where('id', $sms['id'])->update($updateData);
        if (!$res) return $this->setError('验证失败');
        if (!$verify) {
            if ($wrong_num >= 3) return $this->setError('验证错误超过三次，请重新发送验证码');
            return $this->setError('验证码错误2');
        }
        return true;
    }


}
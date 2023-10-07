<?php

namespace bxkj_common;

use bxkj_module\exception\ApiException;
use bxkj_module\service\Bean;
use bxkj_module\service\GiftLog;
use bxkj_module\service\User;
use bxkj_module\service\Work;
use think\facade\Config;

class CoreSdk
{
    protected $httpClient;
    protected $error;

    public function __construct($base = null)
    {
        if (!isset($base) && DEPLOY_MODE == 'single') $base = BASE_CORE_URL;

        if (empty($base)) $base = CORE_URL;

        if (empty($base)) throw new ApiException('内部服务地址不可用');

        $this->httpClient = new HttpClient(array(
            'base' => $base,
            'timeout' => 30,
            'debug' => Config::get('app.app_debug')
        ));
    }

    public function post($uri, $data)
    {
        $uri = strtolower(rtrim(ltrim($uri, '/')));
        $url = '/' . $uri;
        if (!preg_match('/^[a-zA-Z0-9_]+\/[a-zA-Z0-9_]+$/', $uri)) return $this->setError('uri error');
        $funName = '_' . str_replace('/', '_', $uri);
        //本地拦截 兼容处理
        if (method_exists($this, $funName)) return call_user_func_array([$this, $funName], [$data]);
        $result = $this->httpClient->post($url, $data)->getData('json');
        if ($result === false) return $this->setError($this->httpClient->getReqError());
        if ($result['status'] != 0) {
            return $this->setError($result['message'] ?: '系统繁忙', $result['status'], isset($result['data']) ? $result['data'] : []);
        }
        return $result['data'];
    }

    public function getError()
    {
        return $this->error;
    }

    protected function setError($message, $status = 1, $data = [])
    {
        $this->error = is_error($message) ? $message : make_error($message, $status, $data);
        return false;
    }

    //获取分配的管理员
    public function getAid($type, $relId = '', $orderNo = '', $incr = 1)
    {
        $workService = new Work();
        return $workService->allocation($type, $relId, $orderNo, $incr);
    }

    public function incBean($data)
    {
        $result = $this->post('bean/inc', $data);
        return $result;
    }

    public function payBean($data)
    {
        $result = $this->post('bean/pay', $data);
        return $result;
    }

    public function convBean($data)
    {
        $result = $this->post('bean/conversion', $data);
        return $result;
    }

    public function getUsers($userIds, $selfUserId = null, $fields = '_all')
    {
        $data['user_ids'] = is_array($userIds) ? implode(',', array_unique($userIds)) : $userIds;
        if (isset($selfUserId)) $data['self_uid'] = $selfUserId;
        $data['fields'] = $fields;
        $result = $this->post('user/get_users', $data);
        return $result;
    }

    public function getUser($userId, $selfUserId = null, $fields = '_all')
    {
        $data['user_id'] = $userId;
        if (isset($selfUserId)) $data['self_uid'] = $selfUserId;
        $data['fields'] = $fields;
        $result = $this->post('user/get_user', $data);
        return $result;
    }

    protected function _user_get_user($data)
    {
        $userService = new User();
        $data['self_uid'] = isset($data['self_uid']) ? $data['self_uid'] : null;
        $data['fields'] = isset($data['fields']) ? $data['fields'] : null;
        return $userService->getUser($data['user_id'], $data['self_uid'], $data['fields']);
    }

    protected function _user_get_users($data)
    {
        $userService = new User();
        $data['self_uid'] = isset($data['self_uid']) ? $data['self_uid'] : null;
        $data['fields'] = isset($data['fields']) ? $data['fields'] : null;
        return $userService->getUsers($data['user_ids'], $data['self_uid'], $data['fields']);
    }

    protected function _bean_inc($data)
    {
        $beanService = new Bean();
        $beanRes = $beanService->inc($data);
        if (!$beanRes) return $this->setError($beanService->getError());
        return $beanRes;
    }

    protected function _bean_pay($data)
    {
        $beanService = new Bean();
        $beanRes = $beanService->exp($data);
        if (!$beanRes) return $this->setError($beanService->getError());
        return $beanRes;
    }

    protected function _user_update_redis($data)
    {
        if (isset($data['user_id'])) {
            $userId = $data['user_id'];
            unset($data['user_id'], $data['_credit_score']);
            User::updateRedis($userId, $data);
        } else if (is_string($data['data'])) {
            $tmpArr = json_decode($data['data'], true);
            foreach ($tmpArr as $userId => $item) {
                User::updateRedis($userId, $item);
            }
        }
        return true;
    }

    protected function _third_order_unifiedorder($data)
    {
        $httpClient = new HttpClient(array(
            'base' => RECHARGE_URL,
            'timeout' => 30,
            'debug' => Config::get('app.app_debug')
        ));
        $result = $httpClient->post('/third_order/unifiedorder', $data)->getData('json');
        if ($result['status'] != 0) {
            return $this->setError($result['message'] ?: '系统繁忙', $result['status'], $result['data'] ? $result['data'] : []);
        }
        return $result['data'];
    }

    protected function _gift_give($data)
    {
        $gift = new GiftLog();
        $result = $gift->give($data);
        if (!$result) return $this->setError($gift->getError());
        return $result;
    }
}
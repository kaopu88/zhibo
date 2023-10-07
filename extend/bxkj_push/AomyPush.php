<?php

namespace bxkj_push;

use bxkj_push\drivers\UmengPush;
use bxkj_common\HttpClient;
use think\facade\Env;

class AomyPush
{
    protected $driver;
    protected $alias;
    protected $aliasType;
    protected $error;
    protected $fileId;

    public function __construct()
    {
        $config = config('message.bxkj_push');
        if ($config['platform'] == 'umeng')
        {
            $this->driver = new UmengPush($config);
        }

        $this->aliasType = 'user_id';
    }

    public function setError($message = '', $code = 1)
    {
        $this->error = is_error($message) ? $message : make_error($message, $code);
        return false;
    }

    public function getError()
    {
        return $this->error;
    }

    public function setUser($user)
    {
        if (is_array($user)) {
            if (isset($user['user_id'])) $this->alias = $user['user_id'];
        } else {
            $this->alias = $user;
        }
        return $this;
    }

    public function setUsers($ids)
    {
        $this->alias = is_array($ids) ? $ids : (is_string($ids) ? explode(',', $ids) : []);
        return $this;
    }

    //所有平台
    public function allTo($msgData)
    {
        $push_debug = config('app.push_debug');
        /*if ($push_debug) {
            $httpClient = new HttpClient([]);
            $base = config('app.push_service_url');
            $url = $base . '/push/print_data';
            $result = $httpClient->post($url, ['msg_data' => json_encode($msgData)])->getData('json');
            return [
                'android' => [],
                'ios' => []
            ];
        }*/
        $result = [];
        $android = $this->androidTo($msgData);
        if ($android) $result['android'] = $android;
        $ios = $this->iosTo($msgData);
        if ($ios) $result['ios'] = $ios;
        return $result;
    }

    protected function parseMsgData(&$msgData, $type)
    {
        $push_debug = config('app.push_debug');
        $msgData['production_mode'] = isset($msgData['production_mode']) ? $msgData['production_mode'] : !$push_debug;
        $msgData['alias'] = $this->alias;
        $msgData['alias_type'] = $this->aliasType;
        $msgData['timestamp'] = isset($msgData['timestamp']) ? $msgData['timestamp'] : strval(time());
        if ($type == 'android') {
            unset($msgData['alert']);
            $msgData['after_open'] = $msgData['after_open'] ? $msgData['after_open'] : 'go_app';
            if (isset($msgData['custom'])) {
                $msgData['after_open'] = 'go_custom';
                $msgData['custom'] = json_encode($msgData['custom']);
            } else if (isset($msgData['url'])) {
                $msgData['after_open'] = 'go_url';
            }
            $defaultActivity = config('message.bxkj_push.android.default_activity');
            $msgData['activity'] = empty($msgData['activity']) ? $defaultActivity : $msgData['activity'];
            if (!empty($msgData['activity'])) {
                $msgData['mipush'] = true;
                $msgData['mi_activity'] = $msgData['activity'];
            }
            //非消息类的需要有标题和描述
            if ($msgData['display_type'] != 'message') {
                $msgData['ticker'] = isset($msgData['ticker']) ? $msgData['ticker'] : $msgData['title'];
                $msgData['text'] = isset($msgData['text']) ? $msgData['text'] : '点击查看详情';
                $msgData['img'] = isset($msgData['img']) ? $msgData['img'] : '';
            }
        } else {
            if ($msgData['display_type'] == 'message') $msgData['content-available'] = 1;
            unset($msgData['after_open'], $msgData['url'], $msgData['img'], $msgData['display_type']);
            if (!empty($msgData['custom']) && is_string($msgData['custom'])) {
                $msgData['custom'] = json_decode($msgData['custom'], true);
            }
            if ($msgData['content-available'] == 1) {
                $msgData['alert'] = isset($msgData['alert']) ? $msgData['alert'] : '';
            } else {
                if (!isset($msgData['alert']) && (isset($msgData['title']) || isset($msgData['text']))) {
                    $msgData['alert'] = array(
                        'title' => $msgData['title'],
                        //'subtitle' => 'subtitle',//副标题
                        'body' => isset($msgData['text']) ? $msgData['text'] : '点击查看详情'
                    );
                    unset($msgData['title'], $msgData['text']);
                }
            }
        }
    }

    public function androidTo($msgData)
    {
        $this->parseMsgData($msgData, 'android');
        $result = $this->driver->androidTo($msgData);
        if (!$result) return $this->setError($this->driver->getError());
        return $result;
    }

    public function iosTo($msgData)
    {
        $this->parseMsgData($msgData, 'ios');
        $result = $this->driver->iosTo($msgData);
        if (!$result) return $this->setError($this->driver->getError());
        return $result;
    }

    public function allBroadcast($msgData)
    {
        $result = [];
        $android = $this->androidBroadcast($msgData);
        if ($android) $result['android'] = $android;
        $ios = $this->iosBroadcast($msgData);
        if ($ios) $result['ios'] = $ios;
        return $result;
    }

    public function androidBroadcast($msgData)
    {
        $this->parseMsgData($msgData, 'android');
        $result = $this->driver->androidBroadcast($msgData);
        if (!$result) return $this->setError($this->driver->getError());
        return $result;
    }

    public function iosBroadcast($msgData)
    {
        $this->parseMsgData($msgData, 'ios');
        $result = $this->driver->iosBroadcast($msgData);
        if (!$result) return $this->setError($this->driver->getError());
        return $result;
    }

    public function cancel($platform, $taskId)
    {
        $result = $this->driver->cancel($platform, $taskId);
        if (!$result) return $this->setError($this->driver->getError());
        return $result;
    }

}
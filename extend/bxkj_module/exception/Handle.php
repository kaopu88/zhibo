<?php

namespace bxkj_module\exception;


use bxkj_common\RedisClient;
use app\common\service\DsSession;

class Handle extends \think\exception\Handle
{
    public function render(\Exception $e)
    {
        if (!in_array($e->getCode(), [1000,1001,1002,1003,1004,1005,1006,1007,1008]))
        {
            bxkj_console([
                'Message' => $e->getCode().' : '.$e->getMessage(),
                'File' => $e->getFile().'  第'.$e->getLine().'行',
                'trace' => $e->getTrace()
            ], 'error');
        }

        if (defined('SECTION_MANAGER_ERROR_KEY')) {
            $redis = RedisClient::getInstance();
            $date = date('y-m-d_H-i-s');
            $errorKey = SECTION_MANAGER_ERROR_KEY . ":{$date}";
            $redis->set($errorKey, json_encode(['status' => $e->getCode(), 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE));
            $redis->hSet(SECTION_MANAGER_ERROR_KEY2, 'stop', '1');
            $redis->hSet(SECTION_MANAGER_ERROR_KEY2, 'lock', 'unlock');
        }

        // 参数验证错误
        if ($e instanceof ApiException) {
            return json(array(
                'code' => $e->getCode(),
                'msg' => $e->getMessage()
            ));
        }
        
        DsSession::save();

        // 其他错误交给系统处理
        return parent::render($e);
    }
}
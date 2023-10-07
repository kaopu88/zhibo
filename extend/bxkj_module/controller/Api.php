<?php

namespace bxkj_module\controller;

class Api extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    //成功返回 兼容返回
    protected function success($data, $msg = '')
    {
        header('Access-Control-Allow-Origin: *');
        return $this->jsonSuccess($data, $msg);
    }

    protected function jsonSuccess($data, $msg = '')
    {
        return json(array(
            'code' => 0,
            'data' => $data,
            'msg' => $msg
        ));
    }

    //错误返回
    protected function jsonError($msg, $code = 1, $data = null)
    {
        $message = '系统繁忙~';
        if (is_string($msg)) {
            $message = $msg;
        } else if (is_error($msg)) {
            $message = $msg->getMessage();
            $code = $msg->getStatus();
        }
        $obj = array(
            'code' => $code,
            'msg' => $message
        );
        if (isset($data)) $obj['data'] = $data;
        return json($obj);
    }

    /**
     * 对数据进行加密处理
     */
    protected function encryptData($data, $appKey = 'c4etzmp8ssdf13asm6ewarlcpfmy85ne')
    {
        $iv = 'AC4E75886EC2F44E';
        $data = json_encode($data, true);
        $encode = base64_encode(openssl_encrypt($data, "AES-128-CBC", $appKey, true, $iv));
        return $encode;
    }
}

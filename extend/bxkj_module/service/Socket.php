<?php

namespace bxkj_module\service;

class Socket extends Service
{


    //强制踢人
    public function leaveUserByRoomGroup($user_id, $msg, $params = [])
    {
        $socket_data = ['mod' => 'Live', 'act' => 'kickRoom', 'args' => ['user_id' => $user_id, 'msg' => $msg], 'web' => 1];

        $res = $this->connectSocket($socket_data);

        if (!$res) return $this->getError();

        return true;
    }


    //强制关闭帐户
    public function stopUserAccountDiscontinued($user_id, $msg)
    {
        $socket_data = ['mod' => 'Live', 'act' => 'stopUserAccountDiscontinued', 'args' => ['user_id' => $user_id, 'msg' => $msg], 'web' => 1];

        $res = $this->connectSocket($socket_data);

        if (!$res) return $this->getError();

        return true;
    }


    //连接socket
    public function connectSocket($msg)
    {
        $socket = config('app.live_setting');


        $url = str_replace('ws', 'tcp', $socket['message_server']['host']);

        $url .= ':8191';
        $client = stream_socket_client($url, $err_no, $err_str, 3);

        if (is_array($msg)) {
            fwrite($client, bin2hex(json_encode($msg)));
        } else {
            fwrite($client, $msg);
        }

        fwrite($client, "\r\n");

        $ack = fread($client, 1024);

        /*$ack_res = json_decode($ack, true);

        if ($ack_res['type'] != 'hi') {
            fclose($client);
            return $this->setError('socket链接错误');
        }

        $ack_close = fread($client, 8192);

        $close_res = json_decode(hex2bin($ack_close), true);

        if ($close_res['code'] != 0) {
            fclose($client);
            return $this->setError('socket通知错误');
        }*/

        fclose($client);

        return true;
    }


}
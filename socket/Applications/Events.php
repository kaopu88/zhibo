<?php
/**
 * 用于检测业务代码死循环或者长时间阻塞等问题
 * 如果发现业务卡死，可以将下面declare打开（去掉//注释），并执行php start.php reload
 * 然后观察一段时间workerman.log看是否有process_timeout异常
 */

namespace app;

use app\service\Console;
use app\service\Db;
use app\service\Logger;
use app\service\Monitor;
use GatewayWorker\Lib\Gateway;
use GuzzleHttp\Client;
use Workerman\Lib\Timer;
use Workerman\MySQL\Connection;


/**
 * 主逻辑
 * 主要是处理 onConnect onMessage onClose 三个方法
 */
class Events
{

    /**
     * 当socket启动时执行一次
     * @param $worker
     */
    public static function onWorkerStart($worker)
    {
        global $db, $redis;

        $db = new Connection(DB_HOST, DB_PORT, DB_USER, DB_PASSWORD, DB_NAME);

        $redis = new \Redis();

        $redis->connect(REDIS_HOST, REDIS_PORT);
        
        $redis->auth(REDIS_AUTH);

        $redis->select(REDIS_DB);

        Monitor::init();

        Monitor::import(require_once APP_PATH . '/tags.php');

        Console::init();
    }


    /**
     * 当客户端连接时触发
     * 如果业务不需此回调可以删除onConnect
     * @param int $clientId 连接id
     */
    public static function onConnect($clientId)
    {
        $_SESSION['auth_timer_id'] = Timer::add(30, function () use ($clientId) {
            //echo $clientId;
            Gateway::closeClient($clientId);
        }, array($clientId),false);// 定时30秒关闭这个链接

        Logger::info('客户端完成链接添加1个定时器,timer_id=>' . $_SESSION['auth_timer_id'], 'addTimer');
    }


    /**
     * 当客户端发来消息时触发
     * @param int $clientId 连接id
     * @param mixed $message 具体消息
     * @return bool
     * @throws \Exception
     */
    public static function onMessage($clientId, $message)
    {
       // echo $clientId;
        /*echo "Data      ->" . @hex2bin($message) . "\n";
        echo "client    ->{$_SERVER['REMOTE_ADDR']}:{$_SERVER['REMOTE_PORT']} \n";
        echo "client_id ->$clientId <==> {$_SERVER['GATEWAY_CLIENT_ID']} \n";
        echo "gateway   ->{$_SERVER['GATEWAY_ADDR']}:{$_SERVER['GATEWAY_PORT']}  \n";
        echo "session   ->" . json_encode($_SESSION) . "\n";
        echo "time      ->" . date('Y-m-d H:i:s') . "\n";
        echo "==================================================================\n";*/

        global $currentClient, $api_v;

        $data = self::hexTobin($message);

        if ($data === false) {
            Gateway::sendToCurrentClient(bin2hex(json_encode([
                'emit' => 'kickRoom',
                'msg' => '签名验证失败，请登陆后尝试。',
                'data' => ['msg' => '签名验证失败，请登陆后尝试。1'],
                'code' => 1
            ])));

            Gateway::closeClient($clientId);

            return;
        }

        $data = json_decode($data, true);
     

        if ($data['act'] == 'heart') return;
        $_SESSION = Gateway::getSession($clientId);
        !isset($_SESSION['transNum']) && $_SESSION['transNum'] = 0;

        if (!isset($data['web'])) {
            $validate = self::token($data);

            if (!$validate) {
                Gateway::sendToCurrentClient(bin2hex(json_encode([
                    'emit' => 'kickRoom',
                    'msg' => '签名验证失败，请登陆后尝试。',
                    'data' => ['msg' => '签名验证失败，请登陆后尝试。2'],
                    'code' => 1
                ])));

                Gateway::closeClient($clientId);

                return;
            }

            if (isset($data['args']['user_id']) && ($data['args']['user_id'] != $_SESSION['user_id'])) {
                Gateway::sendToCurrentClient(bin2hex(json_encode([
                    'emit' => 'kickRoom',
                    'msg' => '签名验证失败，请登陆后尝试。',
                    'data' => ['msg' => '签名验证失败，请登陆后尝试。3'],
                    'code' => 1
                ])));

                Gateway::closeClient($clientId);

                return;
            }

            Gateway::bindUid($clientId, $_SESSION['user_id']);

            //将自身的用户id带入
            $data['args']['user_id'] = $_SESSION['user_id'];

            $data['args']['current_client_id'] = $clientId; //将当前的用户标识符加入到请求参数中
        }

        $currentClient = $clientId;
        $api_v = isset($data['api_v']) ? $data['api_v'] : 'v1';
        $callback = ["app\\api\\" . ucfirst($data['mod']), $data['act']];
        $data['args']['method'] = $data['act'];

        if (is_callable($callback)) call_user_func_array($callback, [$data['args']]); //执行某个类的某个方法
    }


    /**
     * 当用户断开连接时触发
     * @param int $clientId 连接id
     */
    public static function onClose($clientId)
    {
        global $db, $redis;;
        $uid = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
        $user_identity = isset($_SESSION['user_identity']) ? $_SESSION['user_identity'] : 0;
        Gateway::sendToClient($clientId, bin2hex('{"type":"exit", "msg":"exit success"}'));
		if (!empty($uid) && $user_identity == 'user') {
            $room_id = $redis->get('BG_ROOM:enter:' . $uid);
            if ($room_id) {
                $redis->zrem('BG_LIVE:' . $room_id . ':audience', $uid);
                $redis->decr('cache:online:' . date('Ymd'));
            }
            return;
        }
        if (!empty($uid) && $user_identity == 'anchor') {
            $sql = 'SELECT * FROM ' . TABLE_PREFIX . 'live WHERE status=1 AND room_model=0 AND user_id=' . $uid;
            $res = $db->query($sql);
            if (!empty($res)) {
                $data = self::closeRoom($res[0]['id']);
                $class = "app\\api\\Live";
                if (class_exists($class)) {
                    if (method_exists($class, 'close')) {
                        $params = ['room_id' => $res[0]['id']];
                        call_user_func_array([$class, 'close'], [$params]);
                    }
                }
            }
        }
    }

    /**
     * 验证token
     * @param array $data
     * @return bool
     */
    protected static function token($data = [])
    {
        if (!empty($_SESSION['user_id'])) return true;

        if (!isset($data['sign']) || empty($data['sign']) || !is_array($data['sign'])) return false;

        if (empty($data['sign']['token']) || empty($data['sign']['user_id'])) return false;

        global $redis;

        $user_id = $data['sign']['user_id'];
        $token = $data['sign']['token'];

        $userLoginStatusKey = "loginstate:" . $user_id;
        $userTokenKey = "access_token:" . $token;

        $login_status = $redis->hget($userLoginStatusKey, 'status');
        $token_data = $redis->get($userTokenKey);

        if (empty($login_status) || empty($token_data)) return false;

        $token_data = json_decode($token_data, true);

        if (!isset($token_data['user'])) return false;
        if ($token_data['user']['user_id'] != $user_id) return false;

        if ($login_status != 1) return false;

        $_SESSION['user_id'] = $user_id;

        if (isset($_SESSION['auth_timer_id'])) {
            $rs = Timer::del($_SESSION['auth_timer_id']);
            $rs && Logger::info('客户端完成认证销毁定时器,timer_id=>' . $_SESSION['auth_timer_id'], 'destroyTimer');
            unset($_SESSION['auth_timer_id']);
        }

        return true;
    }

    //十六进制转字符串，取返反转
    protected static function hexTobin($data, $types = false)
    {
        if (!is_string($data)) return false;

        if ($types === false) {
            $len = strlen($data);

            if ($len % 2 || strspn($data, '0123456789abcdefABCDEF') != $len) return false;

            return hex2bin($data);
        } else {
            return bin2hex($data);
        }
    }

    protected static function closeRoom($room_id)
    {
        global $db, $redis;
        $sql = 'SELECT * FROM ' . TABLE_PREFIX . 'live WHERE id=' . $room_id;
        $room = $db->query($sql);
        if (empty($room)) return;

        try{
            if (!defined('SERVICE_URL') || empty(SERVICE_URL)) throw new \Exception('未配置服务地址');
            $client = new Client(['base_uri' => SERVICE_URL]);
            $response = $client->request('POST', '/core/live/superCloseRoom', ['json' => ['room_id'=> $room_id, 'msg' => '主播已掉播']]);
            $res = $response->getBody()->getContents();
        }
        catch (\Exception $e) {
            var_dump($e->getMessage());
        }
    }

    /*$deny_act = ['connectH5', 'sendLighting', 'change'];

        if (!in_array($data['act'], $deny_act))
        {
            $data['args']['access_token'] = $data['sign']['token'];

            $log = [
                'user_id' => isset($data['args']['user_id']) ? $data['args']['user_id'] : 0,
                'room_id' => isset($data['args']['room_id']) ? $data['args']['room_id'] : 0,
                'act' => $data['act'],
                'args' => json_encode($data['args']),
                'client_ip' => $_SERVER['REMOTE_ADDR'],
                'client_id' => $_SERVER['GATEWAY_CLIENT_ID'],
                'create_time' => time(),
                'session' => json_encode($_SESSION, true),
            ];

            $db->insert(TABLE_PREFIX.'socket_log')->cols($log)->query();
        }*/

}




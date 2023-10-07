<?php

namespace app\service;


class Console
{
    public static $config;
    private static $network;
    public static $fatalErrorCallback;
    public static $appErrorCallback;
    public static $appExceptionCallback;

    protected static function initServer()
    {
        $host = bg_get_config('push_host');
        $_SERVER = is_array($_SERVER) ? $_SERVER : [];
        $_SERVER['HTTP_HOST'] = (isset($_SERVER['HTTP_HOST']) && !empty($_SERVER['HTTP_HOST'])) ? $_SERVER['HTTP_HOST'] : $host;
        $_SERVER['SERVER_PORT'] = isset($_SERVER['SERVER_PORT']) ? $_SERVER['SERVER_PORT'] : 80;
        $_SERVER['HTTP_X_CLIENT_PROTO'] = isset($_SERVER['HTTP_X_CLIENT_PROTO']) ? $_SERVER['HTTP_X_CLIENT_PROTO'] : 'http';
        $_SERVER['REQUEST_URI'] = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/undefined';
        $_SERVER['REQUEST_METHOD'] = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';
        $_SERVER['HTTP_USER_AGENT'] = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'ws';
        $_SERVER['HTTP_REFERER'] = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'ws http_referer';
        $_SERVER['QUERY_STRING'] = isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : '';
        return $_SERVER;
    }

    //初始化
    public static function init()
    {
        date_default_timezone_set('PRC');
        $_SERVER = self::initServer();
        //默认配置
        self::$config = array(
            'push_key' => $_SERVER['HTTP_HOST'],
            'record_type' => 'file',//记录方式 file db
            'log_record' => true,
            'log_path' => '',//文件记录位置
            'log_db' => '',
            'log_level' => 'EMERG,ALERT,CRIT,ERR',//记录级别
            'log_types' => array('info', 'error', 'warning'),
            'push_types' => array('info', 'error', 'warning'),//推送级别
            'push_url' => bg_get_config('push_url'),//推送地址
        );
        if (empty(self::$network)) {
            self::$network = array();
            self::$network['gid'] = md5(uniqid() . '' . rand(100, 10000));
            self::$network['port'] = $_SERVER['SERVER_PORT'];
            self::$network['protocol'] = empty($_SERVER['HTTP_X_CLIENT_PROTO']) ? 'http' : $_SERVER['HTTP_X_CLIENT_PROTO'];
            $portStr = self::$network['port'] ? (self::$network['port'] == '80' ? '' : ':' . self::$network['port']) : '';
            self::$network['url'] = self::$network['protocol'] . '://' . $_SERVER['HTTP_HOST'] . $portStr . $_SERVER['REQUEST_URI'];
            self::$network['start_time'] = microtime(true);
            self::$network['method'] = $_SERVER['REQUEST_METHOD'];
            self::$network['host'] = $_SERVER['HTTP_HOST'];
            self::$network['referer'] = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
            self::$network['user_agent'] = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
            self::$network['client_ip'] = self::getIp();
            self::$network['cookies'] = $_COOKIE;
            self::$network['post'] = $_POST;
            self::$network['get'] = $_GET;
            self::$network['query'] = $_SERVER['QUERY_STRING'];
            self::$network['push_key'] = self::$config['push_key'];
            self::$network['log'] = array();//打印日志
            self::$network['response'] = '';//响应内容
            self::push('init', self::$network);
        }
    }

    public static function debug()
    {
        var_dump(self::$config);
        exit();
    }

    //一般信息
    public static function log()
    {
        self::handler('info', func_get_args());
    }

    //错误信息
    public static function error()
    {
        self::handler('error', func_get_args());
    }

    //警告信息
    public static function warning()
    {
        self::handler('warning', func_get_args());
    }

    private static function handler($type, $data, $file = null, $line = null)
    {
        if (!isset($file)) {
            $backtrace = debug_backtrace();
            $tmp = ($backtrace && $backtrace[1]) ? $backtrace[1] : array();
            $file = $tmp['file'];
            $line = $tmp['line'];
        }
        $pushData = array('type' => $type, 'data' => $data, 'file' => $file, 'line' => $line);
        if (in_array($pushData['type'], self::$config['log_types'])) {
            array_push(self::$network['log'], $pushData);
        }
        self::push('log', $pushData);
    }

    //推送信息
    public static function push($type, $data)
    {
        if (empty(self::$config['push_url'])) return false;
        $data['type'] = isset($data['type']) ? $data['type'] : '';
        if (in_array($data['type'], self::$config['push_types']) || $type != 'log') {
            $tmp = array('type' => $type, 'data' => json_encode($data), 'key' => self::$network['push_key'], 'gid' => self::$network['gid']);
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_NOSIGNAL, true);
            curl_setopt($ch, CURLOPT_TIMEOUT_MS, 200);
            curl_setopt($ch, CURLOPT_URL, self::$config['push_url'] . '/push');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($tmp));
            $output = curl_exec($ch);
            curl_close($ch);
        }
    }

    //致命错误或退出执行
    public static function fatalError()
    {
        $e = error_get_last();
        switch ($e['type']) {
            case E_ERROR:
            case E_PARSE:
            case E_CORE_ERROR:
            case E_COMPILE_ERROR:
            case E_USER_ERROR:
                self::handler('error', array($e['message']), $e['file'], $e['line']);
                break;
        }
        self::end();
        if (isset(self::$fatalErrorCallback)) {
            call_user_func_array(self::$fatalErrorCallback, func_get_args());
        }
    }

    public static function appError($errno, $errstr, $errfile, $errline)
    {
        switch ($errno) {
            case E_ERROR:
            case E_PARSE:
            case E_CORE_ERROR:
            case E_COMPILE_ERROR:
            case E_USER_ERROR:
                self::handler('error', array($errstr), $errfile, $errline);
                break;
            case E_NOTICE:
            case E_DEPRECATED:
            case E_STRICT:
                self::handler('notice', array("[{$errno}] {$errstr}"), $errfile, $errline);
                break;
            default:
                self::handler('info', array("[{$errno}] {$errstr}"), $errfile, $errline);
                break;
        }
        if (isset(self::$appErrorCallback)) {
            call_user_func_array(self::$appErrorCallback, func_get_args());
        }
    }

    public static function appException($e)
    {
        $error = array();
        $error['message'] = $e->getMessage();
        $trace = $e->getTrace();
        if ('E' == $trace[0]['function']) {
            $error['file'] = $trace[0]['file'];
            $error['line'] = $trace[0]['line'];
        } else {
            $error['file'] = $e->getFile();
            $error['line'] = $e->getLine();
        }
        self::handler('error', array($error['message']), $error['file'], $error['line']);
        if (isset(self::$appExceptionCallback)) {
            call_user_func_array(self::$appExceptionCallback, func_get_args());
        }
    }

    //日志结束
    private static function end()
    {
        $result = array('response' => ob_get_contents());
        $result['end_time'] = microtime(true);
        $result['run_length'] = sprintf("%.4f", ($result['end_time'] - self::$network['start_time']) * 1000);
        self::$network = array_merge(self::$network, $result);
        if (self::$config['record_type'] == 'file') {
            self::recordFile(self::$network);
        } else if (self::$config['record_type'] == 'db') {
            self::recordDb(self::$network);
        }
        self::push('end', $result);
    }

    private static function recordFile($network)
    {
        if (empty(self::$config['log_path'])) return false;
        $exists = true;
        if (!file_exists(self::$config['log_path'])) {
            $exists = mkdir(self::$config['log_path'], 755, true);
        }
        if ($exists) {
        }
    }

    private static function recordDb($network)
    {
    }

    private static function getIp($type = 0, $adv = false)
    {
        $type = $type ? 1 : 0;
        static $ip = NULL;
        if ($ip !== NULL) return $ip[$type];
        if ($adv) {
            if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
                $pos = array_search('unknown', $arr);
                if (false !== $pos) unset($arr[$pos]);
                $ip = trim($arr[0]);
            } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
                $ip = $_SERVER['HTTP_CLIENT_IP'];
            } elseif (isset($_SERVER['REMOTE_ADDR'])) {
                $ip = $_SERVER['REMOTE_ADDR'];
            }
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        // IP地址合法验证
        $long = sprintf("%u", ip2long($ip));
        $ip = $long ? array($ip, $long) : array('0.0.0.0', 0);
        return $ip[$type];
    }
}
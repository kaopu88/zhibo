<?php

namespace bxkj_common;



class SectionManager
{
    protected $redis;
    protected $key;
    protected $config;
    protected $sectionExecuter;
    protected $threadName;
    protected $info;
    protected $updateInfo;
    protected $isChild = null;
    protected $childrenKey;

    public function __construct($config = null)
    {
        ignore_user_abort(true);
        set_time_limit(0);
        ini_set('memory_limit', '2048M');
        $this->redis = $config['redis'] ? $config['redis'] : RedisClient::getInstance();
        $this->config = array_merge(array(
            'debug' => false,
            'name' => 'auto_' . uniqid(),
            'length' => 10,
            'thread' => 0,
            'exclusivity' => true
        ), isset($config) ? $config : []);
        if (empty($this->config['url'])) {
            $this->config['url'] = 'http://' . $_SERVER['HTTP_HOST'] . (!empty($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : $_SERVER['REDIRECT_URL']);//调用地址
        }
        if (!isset($this->config['signal'])) {
            $this->config['signal'] = $_GET['signal'];//信号量，用来标记有没有curl通
        }
        if (!isset($this->config['main'])) {
            $this->config['main'] = $_GET['main'];//主进程
        }
        $this->key = "secexe:{$this->config['name']}:info";//任务信息
        $this->threadName = sha1($this->config['name'] . uniqid() . mt_rand(0, 10000));
        $this->childrenKey = "secexe:{$this->config['name']}:children:{$this->config['main']}";//子进程集合
        define('SECTION_MANAGER_ERROR_KEY2', $this->key);
    }

    //设置执行者
    public function setSectionExecuter(SectionExecuter $sectionExecuter)
    {
        $this->sectionExecuter = $sectionExecuter;
        return $this;
    }

    //验证请求
    protected function verification()
    {
    }


    public function start()
    {
        if ($this->config['debug']) {
            $this->handler();
            return false;
        }
        $signal = $this->config['signal'];
        $main = $this->config['main'];
        if (!empty($signal)) {
            //告诉父进程我已经成功运行了
            $this->redis->set("secexe:{$this->config['name']}:signal:{$signal}", time(), 18000);
        }
        if (!empty($main)) {
            //加入到主进程的集合中
            $this->redis->zAdd($this->childrenKey, time(), $this->threadName);
        }
        $this->info = $this->redis->hGetAll($this->key);
        if (!$this->info) {
            $opts = $this->sectionExecuter->getOptions();
            $this->info = array_merge([
                'lock' => 'unlock',
                'start_mark' => '0',
                'round' => 0,
                'stop' => '0',
                'now_thread_num' => 0,
                'main_thread_name' => '',
                'complete' => ''
            ], is_array($opts) ? $opts : []);
            $this->redis->hMset($this->key, $this->info);
        }
        //单线程是不需要设置一个主进程来维护进程的
        if ($this->config['thread'] <= 0) {
            $this->handler();
        } else {
            if (empty($this->info['main_thread_name'])) {
                //设为主线程，主线程负责维护所有子线程
                $this->redis->hSet($this->key, 'main_thread_name', $this->threadName);
                $this->isChild = false;
                $this->config['main'] = $this->threadName;
                $this->childrenKey = "secexe:{$this->config['name']}:children:{$this->config['main']}";
                $nowInfo = $this->info;
                while ($nowInfo['stop'] != '1' && empty($nowInfo['complete'])) {
                    $now_thread_num = ((int)$nowInfo['now_thread_num'] > 0) ? $nowInfo['now_thread_num'] : 0;
                    $needNum = $this->config['thread'] - $now_thread_num;
                    if ($needNum > 0) {
                        for ($i = 0; $i < $needNum; $i++) {
                            $goRes = $this->goOn();
                            sleep(5);//5秒时间来激活这个进程
                        }
                    } else {
                        sleep(3);
                    }
                    $nowInfo = $this->redis->hMGet($this->key, ['thread', 'stop', 'complete', 'now_thread_num', 'main_thread_name']);
                }
                $this->redis->hSet($this->key, 'main_msg', 'main stoped');
            } else {
                $this->handler();
            }
        }
    }

    protected function handler()
    {
        $debug = $this->config['debug'];
        if (!$debug) {
            $this->redis->hIncrBy($this->key, 'now_thread_num', 1);
            $this->isChild = true;
            //进程锁
            if ($this->config['exclusivity']) {
                $lock = $this->info['lock'];
                while ($lock == 'lock') {
                    usleep(mt_rand(20000, 40000));//休眠20-40ms
                    $lock = $this->redis->hGet($this->key, 'lock');
                }
                $this->redis->hSet($this->key, 'lock', 'lock');
                //进入后需要刷新数据 因为在等待期间数据已经发生了变化
                $this->info = $this->redis->hGetAll($this->key);
            }
            if ($this->info['stop'] == '1' || !empty($this->info['complete'])) {
                //已经完成的或者标记停止的则退出执行
                $str = $this->info['stop'] == '1' ? 'stoped' : 'completed';
                $msg = [
                    'status' => $this->info['stop'] == '1' ? 1 : 2,
                    'message' => $str,
                    'thread_name' => $this->threadName
                ];
                $this->redis->hSet($this->key, 'msg', $str);
                $this->sectionExecuter->complete($msg);
                echo json_encode($msg, JSON_UNESCAPED_UNICODE);
                exit();
            }
            $errorKey = "secexe:{$this->config['name']}:error:{$this->info['round']}";
            define('SECTION_MANAGER_ERROR_KEY', $errorKey);
            $this->updateInfo = [];
            $this->sectionExecuter->init($this, $this->info);
            //第一次执行
            if ($this->info['start_mark'] != '1') {
                $firstUpdate = $this->sectionExecuter->first();
                if ($firstUpdate && is_array($firstUpdate)) $this->updateInfo = array_merge($this->updateInfo, $firstUpdate);
                $this->updateInfo['start_mark'] = '1';
                $this->redis->hMset($this->key, $this->updateInfo);
            }
        } else {
            $this->info = $this->redis->hGetAll($this->key);
            $this->sectionExecuter->init($this, $this->info);
            $this->sectionExecuter->first();
        }
        if ($this->sectionExecuter instanceof \bxkj_common\SectionListExecuter) {
            $this->listHandler();
        } else if ($this->sectionExecuter instanceof \bxkj_common\SectionMarkExecuter) {
            $this->markHandler();
        }
        echo '{"status":0,"thread_name":"' . $this->threadName . '"}';
        exit();
    }

    protected function listHandler()
    {
        $offset = (int)$this->info['offset'];
        $newInfo = array_merge($this->info, $this->updateInfo);
        $length = $this->config['length'] ? $this->config['length'] : 1;
        $total = (int)$newInfo['total'];//总条数
        $ids = [];
        $list = $this->sectionExecuter->getList($offset, $length);
        $list = $list ? $list : [];
        foreach ($list as $index => &$item) {
            $res = $this->sectionExecuter->itemHandler($ids, $index, $item);
            if (!$res) {
                $error = $this->sectionExecuter->getError();
                if ($error['status'] == 4) {
                    $this->error($error);
                    continue;
                } else if ($error['status'] == 5) {
                    $this->error($error);
                    break;
                } else {
                    $this->error($error, true);
                }
            }
            //处理成功
        }
        $this->updateInfo['progress'] = round((($offset + count($list)) / $total), 2);
        $this->updateInfo['offset'] = $offset + count($list);
        $tmp = count($list) < $length;
        $this->next($tmp);
    }

    protected function markHandler()
    {
        $debug = $this->config['debug'];
        $length = $this->config['length'] ? $this->config['length'] : 1;
        $res = $this->sectionExecuter->handler($length);
        if (!$res) $this->error($this->sectionExecuter->getError(), true);
        //处理成功
        $processed = (int)$res['processed'];
        if (!$debug) {
            $this->redis->hIncrBy($this->key, 'processed', $processed);
        }
        $this->next($res['complete']);
    }

    protected function next($isComplete)
    {
        $debug = $this->config['debug'];
        if (!$debug) $this->redis->hIncrBy($this->key, 'round', $num);
        $this->updateInfo['lock'] = 'unlock';
        if (!$debug) $this->redis->hMset($this->key, $this->updateInfo);
        if ($isComplete) {
            $complete = $this->redis->hGet($this->key, 'complete');
            if (empty($complete)) {
                if (!$debug) $this->redis->hSet($this->key, 'complete', 'complete');
                $this->sectionExecuter->complete([
                    'status' => 0,
                    'message' => 'ok'
                ]);
            }
        } else {
            $this->goOn();
        }
    }

    public function log($name, $text)
    {
        $round = $this->info['round'];
        $date = date('y-m-d_H-i-s');
        $logKey = "secexe:{$this->config['name']}:log:{$round}:{$name}:{$date}";
        $this->redis->set($logKey, $text);
    }

    protected function error($error, $stop = false)
    {
        $status = $error['status'];
        $message = $error['message'];
        $round = $this->info['round'];
        $date = date('y-m-d_H-i-s');
        $errorKey = "secexe:{$this->config['name']}:error:{$round}:{$date}";
        $this->redis->set($errorKey, json_encode(['status' => $status, 'message' => $message], JSON_UNESCAPED_UNICODE));
        if ($stop) {
            $tmp = [
                'status' => $status,
                'message' => $message,
                'thread_name' => $this->threadName
            ];
            $this->sectionExecuter->complete($tmp);
            $this->redis->hSet($this->key, 'stop', '1');
            $this->redis->hSet($this->key, 'lock', 'unlock');
            echo json_encode($tmp, JSON_UNESCAPED_UNICODE);
            exit();
        }
    }

    //继续
    public function goOn($url = null, $name = null, $timeout = 3000)
    {
        $debug = $this->config['debug'];
        if ($debug) return true;
        $url = isset($url) ? $url : $this->config['url'];
        $name = isset($name) ? $name : $this->config['name'];
        if (!empty($url)) {
            $ch = curl_init();
            $signal = sha1(uniqid() . mt_rand(0, 10000));
            $params = ['signal' => $signal];
            if (!empty($this->config['main'])) {
                $params['main'] = $this->config['main'];
            }
            $params['parent'] = $this->threadName;
            foreach ($params as $paramName => $paramValue) {
                $url = preg_replace("/{$paramName}\=[^\&\.\?]+/", '', $url);
            }
            $url = str_replace('&&', '&', $url);
            $url = rtrim(str_replace('?&', '?', $url), '?&');
            $pos = strpos($url, '?');
            $url = $url . ($pos === false ? ('?' . http_build_query($params)) : ('&' . http_build_query($params)));
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_NOSIGNAL, true);
            curl_setopt($ch, CURLOPT_TIMEOUT_MS, $timeout);
            $output = curl_exec($ch);
            $errno = curl_errno($ch);
            if ($errno == 0) {
                if (!empty($output)) {
                    $data = json_decode($output, true);
                    if ($data['status'] == 0) return true;
                }
            } else if ($errno == 28) {
                if (!empty($name)) {
                    $signalKey = "secexe:{$this->config['name']}:signal:{$signal}";
                    $exeTime = $this->redis->get($signalKey);
                    if ($exeTime) {
                        $this->redis->del($signalKey);
                        return true;
                    }
                }
            }
            curl_close($ch);
            return false;
        }
        return false;
    }

    public function __destruct()
    {
        if ($this->config && !$this->config['debug']) {
            if ($this->config['thread'] > 0 && isset($this->isChild)) {
                if ($this->isChild) {
                    $this->redis->hIncrBy($this->key, 'now_thread_num', -1);
                } else {
                    $this->redis->hSet($this->key, 'main_thread_name', '');
                    $this->redis->del($this->childrenKey);
                }
            }
            if ($this->config['main']) {
                $this->redis->zRem($this->childrenKey, $this->threadName);
            }
        }
    }

    public function unlock()
    {
        $this->redis->hSet($this->key, 'lock', 'unlock');
    }

    public function getDebug()
    {
        return $this->config['debug'];
    }


}
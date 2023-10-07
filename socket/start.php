<?php
/**
 * run with command 
 * php start.php start
 */

ini_set('display_errors', 'on');

date_default_timezone_set('UTC');

use Workerman\Worker;

//检查运行平台
if(strpos(strtolower(PHP_OS), 'win') === 0) exit("start.php not support windows, please use start_for_win.bat\n");

// 检查扩展
if(!extension_loaded('pcntl')) exit("Please install pcntl extension. See http://doc3.workerman.net/appendices/install-extension.html\n");

if(!extension_loaded('posix')) exit("Please install posix extension. See http://doc3.workerman.net/appendices/install-extension.html\n");

//定义根目录
define('ROOT_PATH', __DIR__);

//定义服务访问地址
define('SERVICE_URL', 'http://zb.dangjunwei.top');

//定义项目目录
defined('APP_PATH') || define('APP_PATH', ROOT_PATH.'/Applications');

//加载引导
require_once ROOT_PATH . '/vendor/autoload.php';

//加载配置
require_once 'init.php';

// 标记是全局启动
define('GLOBAL_START', 1);

// $aa = REDIS_AUTH;
// echo($aa);

// 加载服务
foreach(glob(ROOT_PATH . '/server/start*.php') as $start_file)
{   
    require_once $start_file;
}

// 运行服务
Worker::runAll();
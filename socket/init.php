<?php

//请求配置
try{

    if (!defined('SERVICE_URL') || empty(SERVICE_URL)) throw new \Exception('未配置服务地址');

    $client = new GuzzleHttp\Client(['base_uri' => SERVICE_URL]);

    $response = $client->request('POST', '/core/config/getConfig', ['json' => ['type'=>'bingxin_socket']]);

    $config = $response->getBody()->getContents();

    $config = json_decode($config, true);

    if (empty($config['data'])) throw new \Exception('配置不能为空');
}
catch (\Exception $e) {
    var_dump($e->getMessage());
    die;
}

$config = $config['data'];

/* 数据库相关常数 */
define('DB_HOST', $config['db']['hostname']);
define('DB_NAME', $config['db']['database']);
define('DB_USER', $config['db']['username']);
define('DB_PASSWORD', $config['db']['password']);
define('DB_PORT', $config['db']['hostport']);
define('DB_CHARSET', 'UTF8');
define('TABLE_PREFIX', $config['db']['prefix']);

/* REDIS相关常数 */
define('REDIS_HOST', $config['redis']['host']);
define('REDIS_PORT', $config['redis']['port']);
define('REDIS_AUTH', $config['redis']['auth']);
define('REDIS_DB', $config['redis']['db']);

/* ROOM相关常数 */
define('BARRAGE_FEE', $config['setting']['barrage_fee']); //弹幕价格
define('CREDIT_SCORE', $config['setting']['credit_score']); //发言信用分
define('HORN_FEE', $config['setting']['horn_fee']); //全区弹幕价格
define('RANK_GOLDEN_LIGHT', $config['setting']['rank_golden_light']);//入房金光等级
define('BARRAGE_LEVEL', $config['setting']['barrage_level']);//发送弹幕等级
define('MIKE_LEVEL', $config['setting']['mike_level']);//连麦等级
define('MESSAGE_LEVEL', $config['setting']['message_level']);//发送消息等级

/* 游戏相关常数 */
define('GAME_SET_RATE', $config['game']['set_rate']);
define('BROAD_CAST_AMOUNT', $config['game']['broad_cast_amount']);

/* APP相关常数 */
define('APP_BEAN_NAME', $config['unit']['coin_name']);
define('APP_MILLET_NAME', $config['unit']['income_name']);
define('APP_ACCOUNT_NAME', $config['unit']['account_name']);
define('APP_NAME', $config['unit']['app_name']);
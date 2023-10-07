<?php
namespace app\api\controller;

use app\common\controller\Controller;
use bxkj_common\Console;
use bxkj_common\CoreSdk;
use bxkj_live\callback\Tencent;
use think\Db;


class Livecallback extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }


    //直播直播回调
    public function callBack()
    {
        $params = $this->getRequestData();

        $class = '\\bxkj_live\\callback\\'.ucfirst($params['request_type']);
        
        $myfile = fopen("test.txt", "a");
        fwrite($myfile, "\r\n");
        fwrite($myfile, var_export($params,true));
        fclose($myfile);
        
        if(class_exists($class))
        {
            $RequestModel = new $class();

            $rs = $RequestModel->$params['event_type']($params);
        }

        return $this->success('ok');
    }


    //电影直播关播处理回调
    public function closeFilmLiveRoom()
    {
        $requestData = input();

        $coreSdk = new CoreSdk();

        unset($requestData['service']);

        if (!is_sign($requestData['sign'], $requestData, config('app.app_setting.timer_token')))
        {
            /*$this->logger->closeFilmLiveRoom('wrong', '电影直播关播签名错误', ['room_id'=>$requestData['room_id'], 'sign'=>$requestData['sign']]);

            die;*/
        }

        $data = ['is_live' => 0, 'room_id' => 0];

        $config = config('app.film_live');

        $res = $coreSdk->post('live/superCloseRoom', ['room_id'=>$requestData['room_id'], 'msg'=>'影片已结束~']);

        if ($res === false)
        {
           /* $this->logger->closeFilmLiveRoom('wrong', '电影直播关播错误', ['room_id'=>$requestData['room_id'], 'error_msg'=>$res->message]);

            die;*/
        }

        if ($config['mode'] == 1) $config['is_loop'] != 1 && $data['status'] = 2;

        $closeData = Db::name('live_film')->field('id')->where(['room_id'=> $requestData['room_id']])->find();

        Db::name('live_film')->where(['id'=>$closeData['id']])->update($data);

        if ($config['mode'] == 1)
        {
            $allwhere=[
                ['status','eq','1'],
                ['timer_task','lt',time()]
            ];
            $allData = Db::name('live_film')->order('id desc')->where($allwhere)->select();

            if (!empty($allData))
            {
                $nowLiveNum = array_sum(array_column($allData, 'is_live'));

                if ($nowLiveNum < $config['num'])
                {
                    $allId = [];

                    $needNum = $config['num'] - $nowLiveNum;

                    foreach ($allData as $val)
                    {
                        if ($val['is_live'] == 0 && $val['room_id'] == 0)
                        {
                            array_push($allId, $val['id']);
                        }
                    }

                    $currentKey = array_keys($allId, $closeData['id']);//当前刚关的id所在的位置

                    while ($needNum)
                    {
                        $key = --$currentKey[0];

                        if (isset($allId[$key]) && $allId[$key] > $closeData['id'])
                        {
                            $id = $allId[$key];
                            unset($allId[$key]);
                        }
                        else {
                            $id = min($allId);
                            array_pop($allId);
                        }

                        $timer_time = time() + $config['span_time'];

                        $coreSdk->post('timer/add', ['url'=> sprintf('%s/createRoom', H5_URL), 'data'=>json_encode(['id'=>$id]), 'cycle'=>0, 'trigger_time'=>$timer_time, 'method'=>'post']);

                        $needNum--;
                    }
                }
            }
        }

        return true;
    }


    protected function getRequestData()
    {
        $content_type = $_SERVER['HTTP_CONTENT_TYPE'];

        $type = [
            0 => 'disconnect',
            1 => 'connect',
            'connect' => 'connect',
            'disconnect' => 'disconnect',
        ];

        switch ($content_type)
        {
            case 'application/json' :

                $params = file_get_contents("php://input");

                $params = json_decode($params, true);

                $params['request_type'] = 'tencent';

                $params['event_type'] = $type[$params['event_type']];

                break;
            default :
                $params = input();

                $params['request_type'] = 'qiniu';

                $params['sign'] = $_SERVER['HTTP_AUTHORIZATION'];

                $params['event_type'] = $type[$params['status']];

                break;
        }

        return $params;
    }


    public function tencent()
    {
        $params = input();

        $Tencent = new Tencent();

        $data = json_decode($params['data'], true);

        $Tencent->$params['event_type']($data);
    }


}
<?php

namespace app\h5\controller;

use app\api\service\live\Lists;
use app\h5\service\activity\LoveQixi;
use bxkj_common\HttpClient;
use bxkj_common\RedisClient;
use bxkj_module\controller\Web;
use think\Db;
use think\Request;
use think\facade\Session;

class Live extends Web
{
    protected $live_duration = 0;
    protected $light_num = 0;
    protected $gift_profit = 0;
    protected $new_fans = 0;
    protected $pk_win_num = 0;
    protected $live_duration_progress = 0;
    protected $light_num_progress = 0;
    protected $gift_profit_progress = 0;
    protected $new_fans_progress = 0;
    protected $pk_win_num_progress = 0;
    protected $done = 0;


    /**
     * 直播间右下角浮标
     * @param Request $request
     * @return mixed
     */
    public function LiveSlider(Request $request)
    {
        $params = $request->param();

        if (empty($params['room_id'])) $this->error();

        $live_info = Db::name('live')->where(['id' => $params['room_id']])->find();

        if (empty($live_info)) $this->error();

        $day = date('Ymd');
        $task_info = Db::name('live_task')->where(['user_id' => $live_info['user_id'], 'date_day' => $day])->find();

        if (!empty($task_info)) {
            $task_setting = json_decode($task_info['task_setting'], true);
            $this->getRealTaskData($live_info, $task_info, $task_setting);
        }

        $slider = [
            //白表罐
            /*[
                'type' => 'general',
                'icon' => '/static/h5/images/setting/act_love.png',
                'href' => $h5_url.'/activity/loveRaise?user_id='.$params['user_id'].'&room_id='.$params['room_id'].'&active_type=0&view_height=0.639'
            ],*/
            //主播每日任务
            [
                'type' => 'task',
                'icon' => '/static/h5/images/setting/day_task_new.png',
                'done' => $this->done,
                'href' => H5_URL . '/live_day_task/taskDetail?user_id=' . $params['user_id'] . '&room_id=' . $params['room_id'] . '&active_type=0&view_height=0.8',
            ],

        ];
        $status = config('live.week_star_status');

        if($status == 1){
            $slider[] = [
                'type' => 'week_star',
                'icon' => '/static/h5/images/setting/week_star.png',
                'href' => H5_URL . '/week_star',
            ];
        }

        $ws = config('app.live_config');

        @list($protocol, $link) = explode(':', $ws['message_server']['chat_server']);
        $this->assign('ws', [
            'protocol' => $protocol,
            'url' => trim($link, '/'),
            'port' => $ws['message_server']['chat_server_port'],
            'packet' => '0x',
            'heart_time' => 20
        ]);

        $this->assign('msg', [
            'mod' => 'Live',
            'act' => 'connectH5',
            'args' => ['room_id' => $params['room_id']],
            'web' => 1,
        ]);

        $count = 0;
        $task_config =  config('app.task_setting');
        if($task_config['live_duration']['status']==1){
            $count++;
        }
        if($task_config['light_num']['status']==1){
            $count++;
        }
        if($task_config['gift_profit']['status']==1){
            $count++;
        }
        if($task_config['new_fans']['status']==1){
            $count++;
        }
        if($task_config['pk_win_num']['status']==1){
            $count++;
        }

        $this->assign('css', ['w' => '60px', 'h' => '60px']);
        $this->assign('slider', $slider);
        $this->assign('count', $count);
        return $this->fetch();
    }


    /**
     * 每日任务数据
     * @param $live_info
     * @param $day_task
     * @param $task_rule
     */
    protected function getRealTaskData($live_info, $day_task, $task_rule)
    {
        $now = time();

        $redis = RedisClient::getInstance();

        $this->live_duration += $day_task['live_duration'] + ($now - $live_info['create_time']);

        $this->light_num += $day_task['light_num'] + $redis->get("BG_LIVE:{$live_info['id']}:like");

        $this->gift_profit += $day_task['gift_profit'] + $redis->get("BG_LIVE:{$live_info['id']}:incomeTotal");

        $this->new_fans += $day_task['new_fans'] + $redis->zcount("fans:{$live_info['user_id']}", $live_info['create_time'], $now + 86400);

        $this->pk_win_num += $day_task['pk_win_num'] + $redis->get("BG_LIVE:{$live_info['id']}:pk_num");

        $this->live_duration_progress += round($this->live_duration / $task_rule['live_duration'], 2);

        $this->light_num_progress += round($this->light_num / $task_rule['light_num'], 2);

        $this->gift_profit_progress += round($this->gift_profit / $task_rule['gift_profit'], 2);

        $this->new_fans_progress += round($this->new_fans / $task_rule['new_fans'], 2);

        $this->pk_win_num_progress += round($this->pk_win_num / $task_rule['pk_win_num'], 2);

        /*$this->live_duration_progress = $this->live_duration_progress >= 1 ? 1 : $this->live_duration_progress;

        $this->light_num_progress = $this->light_num_progress >= 1 ? 1 : $this->light_num_progress;

        $this->gift_profit_progress = $this->gift_profit_progress >= 1 ? 1 : $this->gift_profit_progress;

        $this->new_fans_progress = $this->new_fans_progress >= 1 ? 1 : $this->new_fans_progress;

        $this->pk_win_num_progress = $this->pk_win_num_progress >= 1 ? 1 : $this->pk_win_num_progress;*/

        $this->live_duration_progress >= 1 && $this->done++;

        $this->light_num_progress >= 1 && $this->done++;

        $this->gift_profit_progress >= 1 && $this->done++;

        $this->new_fans_progress >= 1 && $this->done++;

        $this->pk_win_num_progress >= 1 && $this->done++;
    }


    /**
     * 计算时间差
     * @param $time
     * @param string $suffix
     * @return string
     */
    protected function diffTime($time, $suffix = '前')
    {
        switch (true) {
            case $time < 60 :
                $res = $time . '秒';
                break;
            case $time < 3600 :
                $res = floor($time / 60) . '分钟';
                break;
            case $time < 86400 :
                $res = floor($time / 3600) . '小时' . floor(floor($time / 60) % 60) . '分钟';
                break;
            default :
                $res = floor($time / 86400) . '天' . floor(floor($time / 3600) % 24) . '小时' . floor(floor($time / 60) % 60) . '分钟';
                break;
        }
        return $res . $suffix;
    }

    /**
     * 获取主播动态
     * @param $time
     * @param string $suffix
     * @return string
     */
    public function getLiveDynamic()
    {
        $params = request()->param();
        $offset = $params['offset'] ? $params['offset'] : 10;
        $live_user = Db::name('user')->where(['is_anchor' => 1])->field('user_id,avatar')->limit($offset)->select();
        return $this->successr($live_user, $msg = '获取成功');


    }

    /**
     * 获取直播间列表
     * @param $time
     * @return json
     */
    public function getLiveRoom()
    {
        $params = request()->param();

        $offset = $params['offset'] ? $params['offset'] : 0;

        $liveDomain = new Lists();

        $hot = [];

        if ($offset < 1) {
            $hot = $liveDomain->getHotTopList();

            if (!empty($hot)) {
                $liveDomain->reset()->setLengthDec(count($hot));
            }
        }

        $liveDomain->setHotLive($offset);

        $hotList = $liveDomain->getLiveList();

        if (!empty($hotList)) $hotList = $liveDomain->initializeLive($hotList, 1);

        $offList = $liveDomain->getLiveHistory();

        if (!empty($offList)) $offList = $liveDomain->initializeUser($offList, 1);

        $res = array_merge($hot, $hotList, $offList);

        return $this->successr($res, $msg = '获取成功');
    }

    //成功返回 兼容返回
    protected function successr($data, $msg = '')
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


}
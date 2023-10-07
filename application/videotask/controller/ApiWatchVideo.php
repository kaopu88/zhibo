<?php
/**
 * Created by PhpStorm.
 * User: zack
 * Date: 2020/5/13 0013
 * Time: 下午 2:39
 */

namespace app\videotask\controller;

use app\common\controller\Controller as CommonController;
use think\Exception;

class ApiWatchVideo extends CommonController
{
    protected $watchVideoConfig;

    public function __construct()
    {
        parent::__construct();
        $this->watchVideoConfig = config('app.new_people_task_config');
        try {
            if (empty($this->user)) throw new Exception('请先登录');
            if ($this->watchVideoConfig['is_watch_video_status'] != 2) throw new Exception('短视频观看任务未开启~');
        } catch (Exception $e) {
            header('content-type:application/json');
            echo json_encode(array('code' => 600, 'msg' => $e->getMessage()));
            exit();
        }
    }

    public function index()
    {

    }
}
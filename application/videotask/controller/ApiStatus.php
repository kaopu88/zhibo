<?php

namespace app\videotask\controller;

use app\common\controller\Controller as CommonController;
use app\videotask\service\SignRecord;
use think\Db;

class ApiStatus extends CommonController
{
    /**
     * 所有任务的状态
     * status 0表示去提现  1表示去领取 2表示已完成 3表示去查看 4表示已提现
     */
    public function index()
    {
        $config = config('app.new_people_task_config');
        $configproductConfig = config('app.product_setting');
        $data = [];
        $alldata = [];
        if ($config['is_status'] == 2) $data[] = $this->newWithdraw($config);
        if ($configproductConfig['reg_bean'] > 0 || $configproductConfig['reg_millet'] > 0) $data[] = $this->newGiftBag($config);
        if ($config['is_sign_status'] == 2) $data[] = $this->signDay($config);
        if ($config['is_video_status'] == 2) $data[] = $this->shotVideo($config);
        if ($config['is_watch_video_status'] == 2) $data[] = $this->watchVideo($config);
        if (empty($data)) return $this->jsonError('暂未开启任何任务');
        $alldata[] = ['desc' => '日常任务', 'task_detail' => $data];
        return $this->success($alldata, '获取成功');
    }

    /**
     * 新人提现
     * @param $config
     * @return array
     */
    protected function newWithdraw($config)
    {
        $num = Db::name('millet_cash')->where([
            ['user_id', 'eq', USERID],
            ['status', 'neq', 'failed'],
        ])->count();
        $status = 4;
        if (empty($num)) $status = 0;
        return ['status' => $status, 'brief' => $config['is_withdraw_brief'], 'title' => '新人' . $config['new_first_withdraw'] . '元提现'];
    }

    /**
     * 新人注册大礼包
     * @param $config
     * @return array
     */
    protected function newGiftBag($config)
    {
        $detail = [];
        return ['status' => 0, 'brief' => '新人超值奖励,马上领取', 'title' => '大礼包',  'detail' => $detail];
    }

    /**
     * 每日签到
     * @param $config
     */
    protected function signDay($config)
    {
        $uid = USERID;
        $signRecordModel = new SignRecord();
        $recode = $signRecordModel->isSign($uid);
        $status = 3;
        if ($recode['code'] == 1) $status = 2;
        $detail = [];
        return ['status' => $status, 'brief' => $config['sign_brief'], 'title' => '每日签到', 'detail' => $detail];
    }

    /**
     * 拍摄短视频
     * @param $config
     * @return array
     */
    protected function shotVideo($config)
    {
        $uid = USERID;
        $videoCount = Db::name('video_unpublished')->where(['user_id' => $uid, 'source' => 'user', 'audit_status' => 2])->whereTime('audit_time', 'today')->count();//今天是否有上传
        $status = 0;
        if ($videoCount >= 1) $status = 2;
        $detail = [];
        return ['status' => $status, 'brief' => $config['is_video_brief'], 'title' => '拍摄精彩视频',  'detail' => $detail];
    }

    /**
     * 观看短视频
     * @param $config
     * @return array
     */
    protected function watchVideo($config)
    {
        $timeCofig = $config['watch_video_num'];
        $watchVideoMillet = $config['watch_video_millet'];
        $watchVideoBean = $config['watch_video_bean'];
        $detail = [];
        if (!empty($timeCofig)) {
            foreach ($timeCofig as $key => $value) {
                if (empty($value)) continue;
                $detail[] = ['watch_time' => $value, 'watch_millet' => $watchVideoMillet[$key], 'watche_bean' => $watchVideoBean[$key]];
            }
        }
        return ['status' => 0, 'brief' => $config['is_watch_video_brief'], 'title' => '看视频,赚金币', 'detail' => $detail];
    }
}
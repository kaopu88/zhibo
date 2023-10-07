<?php
/**
 * Created by PhpStorm.
 * User: zack
 * Date: 2020/5/9 0009
 * Time: 上午 9:51
 */

namespace app\videotask\controller;

use app\common\controller\Controller as CommonController;
use app\videotask\service\SignRecord;
use think\Db;
use think\Exception;

class ApiSign extends CommonController
{
    protected $sign_config;
    protected $signRecordModel;

    public function __construct()
    {
        parent::__construct();
        $this->sign_config = config('app.new_people_task_config');
        $this->signRecordModel = new SignRecord();
        try {
            if (empty($this->user)) throw new Exception('请先登录', 600);
            if ($this->sign_config['is_sign_status'] != 2) throw new Exception('签到未开启~', 601);
        } catch (Exception $e) {
            header('content-type:application/json');
            echo json_encode(array('code' => $e->getCode(), 'msg' => $e->getMessage()));
            exit();
        }
    }

    /**
     * 获取签到的数据
     * @return \think\response\Json
     */
    public function getSign()
    {
        $uid = USERID;
        $recode = $this->signRecordModel->isSign($uid);
        if ($recode['code'] == -1) return $this->jsonError('用户不存在', 1002);
        $data = $this->signRecordModel->signDetail($this->sign_config);
        if ($data['code'] != 200) return $this->jsonError($data['msg'], 1001);
        $isYestoday = is_in_yestoday($recode['res']['add_time']);
        if (!$isYestoday && !empty($recode['res']['day']) && $recode['code'] != 1) $recode['res']['day'] = 0;
        if ($recode['res']['day'] >= $this->sign_config['is_sign_circle']) $recode['res']['day'] = 0;

        $resData['day'] = $data['data'];
        $resData['recode'] = $recode['code'];
        $resData['contiune_day'] = $recode['res']['day'] ? $recode['res']['day'] : 0;
        $resData['text'] = $this->sign_config['sign_text'] ? $this->sign_config['sign_text'] : ['textsign' => '签到', 'textsigned' => '已签'];
        return $this->success($resData, '获取成功');
    }

    /**
     * 签到
     */
    public function sign()
    {
        $uid = USERID;
        $recode = $this->signRecordModel->isSign($uid);

        if ($recode['code'] == 1) return $this->jsonError('今天已经签到过了', 1003);
        $isYestoday = is_in_yestoday($recode['res']['add_time']);
        $day = 1;

        if ($isYestoday) $day = $recode['res']['day'] + 1;
        if ($recode['res']['day'] >= $this->sign_config['is_sign_circle']) $day = 1;
        $signData = $this->signRecordModel->signDetail($this->sign_config);
        $reward = $signData['data'][$day - 1];

        $data = ['uid' => USERID, 'millet' => $reward['millet'], 'bean' => $reward['bean'], 'day' => $day, 'add_time' => date("Y-m-d H:i:s", time())];
        Db::startTrans();
        try {
            $id = $this->signRecordModel->insert($data);
            if ($id > 0) {
                if ($reward['bean'] > 0) $this->signRecordModel->rewardBean(USERID, $reward['bean']);
                if ($reward['millet'] > 0) $this->signRecordModel->rewardMillet(USERID, $reward['millet']);
            }
        } catch (\Exception $e) {
            Db::rollback();
            return $this->jsonError('签到失败', 1004);
        }
        Db::commit();
        return $this->success($id, '签到成功');
    }

    /**
     * 获取签到记录
     * @param page 页码
     * @param length 一页的数据
     */
    public function signList()
    {
        $params = request()->param();
        $page = isset($params['page']) ? $params['page'] : 1;
        $length = isset($params['length']) ? ($params['length'] > 100 ? 100 : $params['length']) : 10;
        $list = $this->signRecordModel->getList($page, $length, ['uid' => USERID]);
        return $this->success($list ?: [], '获取成功');
    }
}
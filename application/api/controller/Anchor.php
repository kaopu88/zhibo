<?php
/**
 * Created by PhpStorm.
 * User: zack
 * Date: 2020/6/8 0009
 * Time: 上午 9:51
 */

namespace app\api\controller;

use app\common\controller\UserController;
use bxkj_module\service\Agent;
use bxkj_module\service\AnchorApply;
use think\Db;
use think\Exception;
use bxkj_common\RabbitMqChannel;
use bxkj_module\exception\ApiException;

class Anchor extends UserController
{
    protected $applyType = ['person_apply', 'agent_apply'];
    protected $config;

    public function __construct()
    {
        parent::__construct();
        $this->config = config('app.live_setting.user_live');
        try {
            if ($this->config['front_status'] != 1) throw new Exception('申请未开启~', 1);
        } catch (Exception $e) {
            throw new ApiException((string)$e->getMessage(), 1);
        }
    }

    /**
     * 获取申请主播的配置
     */
    public function getConfig()
    {
        try {
            apiAsserts(empty($this->config), '未配置直播申请');
        } catch (Exception $e) {
            return $this->jsonError($e->getMessage());
        }

        return $this->success($this->config, '获取成功');
    }

    /**
     * 主播申请
     * @param array  apply_type申请类型（person_apply,agent_apply）  agent_id 代理id
     * $type 0 表示首次进入申请页面   4 表示公会拒绝后再次申请  5 表示平台拒绝后的再次申请
     * @param  pay_status 0表示第三种的状态来的 1表示未支付 第二种开播方式
     */
    public function apply()
    {
        $submit = submit_verify('apply' . USERID);
        if (!$submit) return $this->jsonError('您提交太频繁了~~~');
        $params = request()->param();
        $check = $this->check($params);
        if ($check['code'] != 200) return $this->jsonError($check['msg']);
        $type = isset($params['type']) ? $params['type'] : 0;
        $pay_status = $params['pay_status'];
        $verified = Db::name('user')->where(['user_id' => USERID])->value('verified');
        //默认是1
        // $status = 1;
        $status = 3;
        if ($verified == 1 && !empty($params['agent_id'])) $status = 4;
        if (!empty($pay_status) && $verified == 1 && empty($params['agent_id'])) $status = 6;
        $anchorApplyService = new AnchorApply();
        $resPromotionVerify = Db::name('promotion_relation_apply')->where(['user_id' => USERID])->order('id desc')->find();
        $applyResult = Db::name('anchor_apply')->where(['user_id' => USERID])->find();
        if (!empty($resPromotionVerify) && $resPromotionVerify['status'] == '2') $type = 4;
        if (!empty($applyResult) && $applyResult['status'] == 5) $type = 5;

        if ($type == 0) {
            $applyResultId = $anchorApplyService->addApply($params, USERID, $status);
            if (!$applyResultId) return $this->jsonError($anchorApplyService->getError());

            // if ($verified != 1) {
            //     //跳转实名
            //     return $this->success(['user_id' => USERID, 'verified' => $verified, 'apply_id' => $applyResultId], '跳转实名认证');
            // }
            //已经实名通过 mq进行下一步操作  正常来说只有不入公会才有后续操作
            if ($pay_status == 0) {
                $rabbitChannel = new RabbitMqChannel(['anchor.anchor_apply_after']);
                $rabbitChannel->exchange('main')->sendOnce('anchor.anchor_apply_after.process', ['user_id' => USERID, 'apply_id' => $applyResultId]);
            }
            return $this->success(['user_id' => USERID, 'verified' => $verified, 'apply_id' => $applyResultId], '处理中');
        }
        if ($type == 4) {
            $applyResultId = $anchorApplyService->relationApply($params, USERID);
            if (!$applyResultId) return $this->jsonError($anchorApplyService->getError());
            if ($pay_status == 0) {
                $rabbitChannel = new RabbitMqChannel(['anchor.anchor_apply_after']);
                $rabbitChannel->exchange('main')->sendOnce('anchor.anchor_apply_after.process', ['user_id' => USERID, 'apply_id' => $applyResultId]);
            }
            return $this->success(['user_id' => USERID, 'verified' => $verified, 'apply_id' => $applyResultId], '处理中');
        }
        if ($type == 5) {
            $applyResultId = $anchorApplyService->platformApply($params, USERID);
            if (!$applyResultId) return $this->jsonError($anchorApplyService->getError());
            return $this->success(['user_id' => USERID, 'verified' => $verified, 'apply_id' => $applyResultId], '处理中');
        }
    }

    protected function check(&$params)
    {
        if (empty(USERID)) return ['code' => 101, 'msg' => '用户未登录'];
        $anchorService = new \bxkj_module\service\Anchor();
        $reslut = $anchorService->getOne(USERID);
        if (!$reslut) return ['code' => 102, 'msg' => $anchorService->getError()];
        $appylType = $params['apply_type'];
        if (!in_array($appylType, $this->applyType)) return ['code' => 103, 'msg' => '申请类型错误'];
        if (!$this->config[$appylType]) return ['code' => 104, 'msg' => '该类型申请未开启'];
        $params['agent_id'] = $params['agent_id'] ? $params['agent_id'] : 0;
        $params['pay_status'] = isset($params['pay_status']) ? $params['pay_status'] : 0;
        if (!empty($params['agent_id'])) {
            $agentService = new Agent();
            $agentRes = $agentService->getInfo($params['agent_id']);
            if (empty($agentRes)) return ['code' => 105, 'msg' => config('app.agent_setting.agent_name') . '不存在'];
        }
        return ['code' => 200];
    }

    /**
     * 申请状态
     * 9表示是主播了 0表示没有申请过 跳入申请页
     */
    public function applyStatus()
    {
        try {
            apiAsserts(empty(USERID), '用户未登录', -1);
            $anchor = Db::name('anchor')->where(['user_id' => USERID])->find();
            apiAsserts(!empty($anchor), '你已经是主播啦~~~', 9);
            $anchorApply = Db::name('anchor_apply')->where(['user_id' => USERID])->find();
            apiAsserts(empty($anchorApply), '跳转申请页面', 0);

            if ($anchorApply['status'] == 1) {
                $resVerify = Db::name('user_verified')->where(['user_id' => USERID])->order('id desc')->find();
                apiAsserts(empty($resVerify), '你还没有提交过实名记录', 1);
                apiAsserts($resVerify['status'] == '0', '实名待审核中', 2);
                apiAsserts($resVerify['status'] == '1', '实名已通过', 11);
                apiAsserts($resVerify['status'] == '2', '实名被驳回', 3);
            }
            apiAsserts($anchorApply['status'] == 2, '审核已经通过啦', 4);
            apiAsserts($anchorApply['status'] == 3, '等待平台审核主播申请', 5);

            if ($anchorApply['status'] == 4) {
                $resPromotionVerify = Db::name('promotion_relation_apply')->where(['user_id' => USERID])->order('id desc')->find();
                apiAsserts(empty($resPromotionVerify), '无' . config('app.agent_setting.agent_name') . '审核', 6);
                apiAsserts($resPromotionVerify['status'] == '0', '等待' . config('app.agent_setting.agent_name') . '审核', 7);
                apiAsserts($resPromotionVerify['status'] == '1', config('app.agent_setting.agent_name') . '审核通过', 12);
                apiAsserts($resPromotionVerify['status'] == '2', config('app.agent_setting.agent_name') . '驳回审核', 8);
            }
            apiAsserts($anchorApply['status'] == 5, '平台审核驳回', 10);
            apiAsserts($anchorApply['status'] == 6, '申请开店', 13);
        } catch (Exception $e) {
            return $this->success(['type' => $e->getCode()], $e->getMessage());
        }
    }

    /**
     * 查找公会
     */
    public function serchAgent()
    {
        $params = request()->param();
        $agentId = $params['agent_id'];
        if (empty($agentId)) return $this->jsonError(config('app.agent_setting.agent_name') . 'id不能为空');
        $agentService = new Agent();
        $agentRes = $agentService->getInfo($agentId);
        if (empty($agentRes)) return $this->jsonError(config('app.agent_setting.agent_name') . '不存在');
        $data = ['agent_id' => $agentRes['id'], 'name' => $agentRes['name']];
        return $this->success($data);
    }
}

<?php
/**
 * Created by PhpStorm.
 * User: zack
 * Date: 2020/6/8 0009
 * Time: 下午 3:51
 */

namespace bxkj_module\service;

use think\Db;

class AnchorApply extends Service
{
    /**
     * 添加申请记录
     * @param $params
     * @param $userId
     * @return bool|int|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function addApply($params, $userId, $status = 1)
    {
        $applyResult = Db::name('anchor_apply')->where(['user_id' => $userId])->find();
        if ($applyResult) return $this->setError('您已经申请过啦~~~');

        Service::startTrans();
        $anchorApplyData = [
            'user_id' => $userId,
            'status' => $status,
            'agent_id' => $params['agent_id'],
            'create_time' => time(),
            'pay_status' => $params['pay_status'],
            'reason' => '',
            'remark' => '',
        ];
        $anchorID = Db::name('anchor_apply')->insertGetId($anchorApplyData);
        if (!$anchorID) return $this->setError('申请失败');
        if (!empty($params['agent_id'])) {
            //说明是公会主播
            $promotionRelationApplyData = [
                'user_id' => $userId,
                'status' => 0,
                'agent_id' => $params['agent_id'],
                'create_time' => time(),
                'is_anchor' => 1
            ];
            $relationAnchor = Db::name('promotion_relation_apply')->insert($promotionRelationApplyData);
            if (!$relationAnchor) {
                Service::rollback();
                return $this->setError('公会申请失败');
            }
        }

        Service::commit();
        return $anchorID;
    }

    /**
     * 入会申请拒绝后的后续操作
     * @param $param
     */
    public function relationApply($params, $userId)
    {
        $applyResult = Db::name('anchor_apply')->where(['user_id' => $userId])->find();
        if (empty($applyResult)) return $this->setError('非法操作啦~~~');
        if ($applyResult['status'] != 4) return $this->setError('状态有问题啦~~~');
        $resPromotionVerify = Db::name('promotion_relation_apply')->where(['user_id' => USERID])->order('id desc')->find();
        if (empty($resPromotionVerify)) return $this->setError('你没有申请' . config('app.agent_setting.agent_name') . '的记录~~~');
        if ($resPromotionVerify['status'] != 2) return $this->setError('无效处理~~~');

        if (!empty($params['agent_id'])) {
            //对关系表进行更新
            $relationRes = Db::name('promotion_relation_apply')->where(['user_id' => $userId])->update(['status' => 0, 'agent_id' => $params['agent_id'], 'create_time' => time()]);
            if (!$relationRes) return $this->setError('加入失败');
        } else {
            Service::startTrans();
            $res = Db::name('anchor_apply')->where(['id' => $applyResult['id']])->update(['status' => 1, 'agent_id' => 0]);
            if ($res) {
                $relationRes = Db::name('promotion_relation_apply')->where(['user_id' => $userId])->delete();
                if ($relationRes) {
                    Service::commit();
                } else {
                    return $this->setError('操作失败');
                    Service::rollback();
                }
            } else {
                Service::rollback();
            }
        }
        return $applyResult['id'];
    }

    /**
     * 平台拒绝后的继续提交操作
     * @param $params
     * @param $userId
     */
    public function platformApply($params, $userId)
    {
        $applyResult = Db::name('anchor_apply')->where(['user_id' => $userId])->find();
        if (empty($applyResult)) return $this->setError('非法操作啦~~~');
        if ($applyResult['status'] != 5) return $this->setError('状态有问题啦~~~');
        $res = Db::name('anchor_apply')->where(['id' => $applyResult['id']])->update(['status' => 3]);
        if (!$res) return $this->setError('操作失败');
        return $applyResult['id'];
    }
}
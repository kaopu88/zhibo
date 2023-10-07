<?php

namespace app\core\service;

use bxkj_module\service\Service;
use think\Db;

class InviteRecord extends Service
{
    public function add($inviteUid, $inviteAnchorUid, $newUser)
    {
        $data['invite_uid'] = $inviteUid;
        $data['anchor_uid'] = $inviteAnchorUid ? $inviteAnchorUid : 0;
        $data['user_id'] = $newUser['user_id'];
        $reward_invite_bean = config('app.product_setting.invite_bean');//奖励邀请人金币数
        $reward_invite_millet = config('app.product_setting.invite_millet');//奖励邀请人金币数
        $reward_reg_millet = config('app.product_setting.reg_millet');//奖励邀请人金币数
        $reward_invite_exp = config('app.product_setting.invite_exp') ? config('app.product_setting.invite_exp') : 0;//奖励邀请人经验值
        $data['reward_bean'] = $reward_invite_bean;
        $data['reward_millet'] = $reward_invite_millet;
        $data['reward_exp'] = $reward_invite_exp;
        $data['create_time'] = time();
        $id = Db::name('invite_record')->insertGetId($data);
        if ($id) {
            if ($reward_invite_bean > 0) $this->rewardBean($inviteUid, $reward_invite_bean);
            if ($reward_invite_millet > 0) $this->rewardMillet($inviteUid, $reward_invite_millet, $data['user_id']);
            if ($reward_reg_millet > 0) $this->rewardRegMillet($data['user_id'], $reward_reg_millet, $data['user_id']);
            if ($reward_invite_exp > 0) $this->rewardExp($inviteUid, $reward_invite_exp);
        }
        return $id;
    }

    protected function rewardBean($inviteUid, $reward_invite_bean)
    {
        $bean = new Bean();
        $bean->reward([
            'user_id' => $inviteUid,
            'type' => 'invite_reward_bean',
            'bean' => $reward_invite_bean
        ]);
    }

    protected function rewardMillet($inviteUid, $reward_invite_millet, $newUserId)
    {
        $millet = new Millet();
        $millet->reward([
            'user_id' => $inviteUid,
            'cont_uid' => $newUserId,
            'type' => 'invite_reward_bean',
            'bean' => $reward_invite_millet
        ]);
    }

    protected function rewardRegMillet($inviteUid, $reward_invite_millet, $newUserId)
    {
        $millet = new Millet();
        $millet->reward([
            'user_id' => $inviteUid,
            'cont_uid' => $newUserId,
            'type' => 'invite_reward_bean',
            'bean' => $reward_invite_millet
        ]);
    }

    protected function rewardExp($inviteUid, $reward_invite_exp)
    {
    }
}
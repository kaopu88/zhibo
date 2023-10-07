<?php

namespace app\admin\controller;

use think\Db;

class PromotionRelation extends Controller
{
    public function unbind_all()
    {
        $userId = input('user_id');
        if (empty($userId)) $this->error('请选择用户');
        $proRel = new \bxkj_module\service\PromotionRelation();
        $total = $proRel->unbindAll([$userId]);
        if (!$total) $this->error('全部解绑失败');
        alog("user.promoter.release", "解绑用户 USER_ID：".$userId);
        $this->success('全部解绑成功');
    }

    public function unbind_agent()
    {
        $agentId = input('agent_id');
        $userId = input('user_id');
        if (empty($agentId)) $this->error('请选择'.config('app.agent_setting.agent_name'));
        if (empty($userId)) $this->error('请选择用户');
        $proRel = new \bxkj_module\service\PromotionRelation();
        $total = $proRel->unbindWithAgent([$userId], $agentId);
        if (!$total) $this->error('取消绑定失败');
        alog("user.promoter.release", "解绑公会 USER_ID：".$userId);
        $this->success('取消绑定成功');
    }
}

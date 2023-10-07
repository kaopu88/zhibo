<?php

namespace app\agent\controller;

use app\agent\service\Anchor;
use think\Db;

class UserRoler extends Controller
{

    public function get_role_list()
    {
        $userId = input('id');
        if (empty($userId)) $this->error('请选择用户');
        $arr = [
            ['type' => 'promoter', 'selected' => '0', 'name' => config('app.agent_setting.promoter_name'), 'date' => '']
        ];
        $user = Db::name('user')->where(['user_id' => $userId, 'delete_time' => null])
            ->field('user_id,avatar,nickname,remark_name,isvirtual,create_time')->find();
        if (empty($user)) $this->error('用户不存在');
        $newArr = [];
        foreach ($arr as $item) {
            switch ($item['type']) {
                case 'anchor':
                    $anchor = Db::name('anchor')->where(['user_id' => $userId])->field('user_id id,create_time')->find();
                    if ($anchor) {
                        $item['selected'] = '1';
                        $item['date'] = date('Y-m-d H:i', $anchor['create_time']);
                    }
                    break;
                case 'promoter':
                    $promoter = Db::name('promoter')->where(['user_id' => $userId])->field('user_id id,create_time')->find();
                    if ($promoter) {
                        $item['selected'] = '1';
                        $item['date'] = date('Y-m-d H:i', $promoter['create_time']);
                    }
                    break;
                case 'isvirtual':
                    if ($user && $user['isvirtual'] == '1') {
                        $item['selected'] = '1';
                        $item['date'] = date('Y-m-d H:i', $user['create_time']);
                    }
                    break;
            }
            $newArr[] = $item;
        }
        $this->success('获取成功', ['list' => $newArr, 'user' => $user]);
    }

    public function setting()
    {
        $type = input('type');
        $status = input('status');
        $userId = input('user_id');
        if (empty($type)) $this->error('请选择角色类型');
        if (empty($userId)) $this->error('请选择用户');
        $act = $status == 0 ? 'cancel' : 'set';
        $fun = $act . ucfirst(strtolower($type));
        if (!method_exists($this, $fun)) $this->error('用户角色不支持');
        $res = call_user_func_array([$this, $fun], [$userId]);
        return $res;
    }

    //设置为config('app.agent_setting.promoter_name')
    private function setPromoter($userId)
    {
        $force = '0';
        $promoterService = new \app\agent\service\Promoter();
        $res = $promoterService->create([
            'agent_id' => AGENT_ID,
            'user_id' => $userId,
            'force' => $force,
            'admin' => [
                'type' => 'agent',
                'id' => AID
            ]]);
        if (!$res) $this->error($promoterService->getError());
        $this->success('已设置为'.config('app.agent_setting.promoter_name'));
    }

    //取消config('app.agent_setting.promoter_name')
    private function cancelPromoter($userId)
    {
        $promoterService = new \app\agent\service\Promoter();
        $res = $promoterService->cancel([$userId], AGENT_ID, [
            'type' => 'agent',
            'id' => AID
        ]);
        if (!$res) $this->error($promoterService->getError());
        $this->success('已取消'.config('app.agent_setting.promoter_name'));
    }

    private function setAnchor($userId)
    {
        $force = 0;
        $anchorService = new Anchor();
        $res = $anchorService->create([
            'agent_id' => AGENT_ID,
            'user_id' => $userId,
            'force' => $force,
            'admin' => [
                'type' => 'agent',
                'id' => AID
            ]]);
        if (!$res) $this->error($anchorService->getError());
        $this->success('已设置为主播');
    }

    private function cancelAnchor($userId)
    {
        $anchorService = new Anchor();
        $res = $anchorService->cancel([$userId], AGENT_ID, [
            'type' => 'agent',
            'id' => AID
        ]);
        if (!$res) $this->error($anchorService->getError());
        $this->success('已取消主播');
    }

    private function setIsvirtual($userId)
    {
        //判断是否具有新增协议号权限
        $add_virtual = Db::name('agent')->where('id',AGENT_ID)->value('add_virtual');
        if (!$add_virtual) $this->error('无新增协议号权限，请联系总后台管理员！');
        //判断是否超出协议号限额
        $max_virtual_num = Db::name('agent')->where('id',AGENT_ID)->value('max_virtual_num');
        $virtual_num = Db::name('agent')->where('id', AGENT_ID)->value('virtual_num');
        if ($virtual_num >= $max_virtual_num) $this->error('超出协议号限额，请联系总后台管理员！');
        $userService = new \bxkj_module\service\User();
        $res = $userService->setIsvirtual($userId);
        if (!$res) $this->error($userService->getError());
        Db::name('agent')->where('id', AGENT_ID)->setInc('virtual_num', 1);
        $this->success('已设置为虚拟账号');
    }

    private function cancelIsvirtual($userId)
    {
        $this->error('协议号不支持取消');
    }
}


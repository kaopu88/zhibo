<?php
/**
 * Created by PhpStorm.
 * User: 李成
 * Date: 2020/7/16
 * Time: 17:29
 */
namespace app\taoke\controller;

use think\facade\Request;

class User extends Controller
{
    public function index()
    {
        $this->checkAuth('taoke:user:index');
        $get = input();
        $userService = new \app\taoke\service\User();
        $total = $userService->getTotal($get);
        $page = $this->pageshow($total);
        $list = $userService->getList($get, $page->firstRow, $page->listRows);
        $this->assign('_list', $list);
        return $this->fetch();
    }

    public function edit()
    {
        $this->checkAuth('taoke:user:update');
        $userService = new \app\admin\service\User();
        if (Request::isGet()) {
            $userId = input('user_id');
            $info = $userService->getInfo($userId);
            if (empty($info)) $this->error('用户不存在');
            $this->assign('_info', $info);
            $level = new \app\taoke\service\Level();
            $total = $level->getTotal([]);
            $levelList = $level->getList([], 0, $total);
            $this->assign('level_list', $levelList);
            return $this->fetch('edit');
        } else {
            $post = input();
            $userId = $post['user_id'];
            $data['taoke_level'] = $post['taoke_level'];
            $data['taoke_money_status'] = $post['taoke_money_status'];
            $relationId = $post['relation_id'];
            if(!empty($relationId)) {
                $relationCount = $userService->getTotal(["relation_id" => $relationId, "uidsoff" => $userId]);
                if ($relationCount > 0) {
                    $this->error('此渠道id已绑定其他用户');
                }
                $data['relation_id'] = $relationId;
            }
            $specialId = $post['special_id'];
            if(!empty($specialId)) {
                $specialCount = $userService->getTotal(["special_id" => $specialId, "uidsoff" => $userId]);
                if ($specialCount > 0) {
                    $this->error('此会员运营id已绑定其他用户');
                }
                $data['special_id'] = $specialId;
            }
            $pddPid = $post['pdd_pid'];
            if(!empty($pddPid)) {
                $pddCount = $userService->getTotal(["pdd_pid" => $pddPid, "uidsoff" => $userId]);
                if ($pddCount > 0) {
                    $this->error('此拼多多pid已绑定其他用户');
                }
                $data['pdd_pid'] = $pddPid;
            }
            $jdPid = $post['jd_pid'];
            if(!empty($jdPid)) {
                $jdCount = $userService->getTotal(["jd_pid" => $jdPid, "uidsoff" => $userId]);
                if ($jdCount > 0) {
                    $this->error('此京东pid已绑定其他用户');
                }
                $data['jd_pid'] = $jdPid;
            }
            $status = $userService->updateData($userId, $data);
            if($status === false){
                $this->error('编辑失败');
            }
            alog("taoke.user.edit", "编辑用户淘客信息 USER_ID：".$userId);
            $this->success('编辑成功');
        }
    }

    public function changeStatus()
    {
        $this->checkAuth('taoke:user:update');
        $ids = get_request_ids("user_id");
        if (empty($ids)) $this->error('请选择记录');
        $status = input('taoke_money_status');
        if (!in_array($status, ['0', '1'])) $this->error('状态值不正确');
        $user = new \app\admin\service\User();
        $num = $user->updateData($ids, ["taoke_money_status"=>$status]);
        if (!$num) $this->error('切换状态失败');
        alog("taoke.user.edit", "编辑用户淘客 USER_ID：".implode(",", $ids)."<br>余额状态:".($status == 1 ? "启用" : "禁用"));
        $this->success('切换成功');
    }

}
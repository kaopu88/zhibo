<?php

namespace app\admin\controller;

use bxkj_common\RedisClient;
use think\Db;
use bxkj_module\service\User;
use think\facade\Request;

class Robot extends Controller
{
    public function index()
    {
        $this->checkAuth('admin:robot:select');
        $robotService = new \app\admin\service\Robot();
        $get = input();
        $total = $robotService->getTotal($get);
        $page = $this->pageshow($total);
        $artList = $robotService->getList($get, $page->firstRow, $page->listRows);
        $this->assign('get', $get);
        $this->assign('_list', $artList);
        return $this->fetch();
    }

    public function add()
    {
        $this->checkAuth('admin:robot:add');
        $this->assign('robot_num',input('robot_num'));
        return $this->fetch();
    }

    public function edit()
    {
        $this->checkAuth('admin:robot:update');
        if (Request::isGet()) {
            $user_id = input('user_id');
            $info = Db::name('robot')->where('user_id', $user_id)->find();
            if (empty($info)) $this->error('机器人不存在');
            $info['area_id'] = implode('-', [$info['province_id'], $info['city_id'], $info['district_id']]);
            $this->assign('_info', $info);
            return $this->fetch();
        } else {
            $robotService = new \app\admin\service\Robot();
            $post = input();
            if ($post['area_id']) {
                $area = explode('-', $post['area_id']);
                $post['province_id'] = $area[0];
                $post['city_id'] = $area[1];
                $post['district_id'] = $area[2];
            }
            unset($post['area_id']);
            $result = $robotService->update($post);
            if (!$result) $this->error($robotService->getError());
            alog("user.robot.edit", '编辑机器人 USER_ID：'.$post['user_id']);
            $this->success('编辑成功', $result);
        }
    }

    public function delete()
    {
        $this->checkAuth('admin:robot:delete');
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择记录');
        $num = Db::name('robot')->whereIn('user_id', $ids)->delete();
        if (!$num) $this->error('删除失败');

        redis_lock('upload_robot_temp_' . AID);
        $redis = RedisClient::getInstance();
        foreach ($ids as $id)
        {
            $redis->sRem('robot_sets',$id);
        }
        redis_unlock('upload_robot_temp_' . AID);
        alog("user.robot.del", '删除机器人 USER_ID：'.implode(",", $ids));
        $this->success("删除成功，共计删除{$num}条记录",'','robot/index');
    }

    public function batch_up()
    {
        $this->checkAuth('admin:robot:add');
        $post = input();
        $data = $post;
        $user = new User();
        $data['user_id'] = $user->generateUserId();
        $data['username'] = 'bx_'.$data['user_id'];
        $data['type'] = 'robot';
        $data['isvirtual'] = '1';
        if ($data['area_id']) {
            $area = explode('-', $data['area_id']);
            $data['province_id'] = $area[0];
            $data['city_id'] = $area[1];
            $data['district_id'] = $area[2];
        }
        unset($data['area_id']);
        $data['level'] = '1';
        $data['verified'] = '1';
        $data['reg_way'] = 'migrate';
        $data['credit_score'] = '100';

        $robotService = new \app\admin\service\Robot();
        $result = $robotService->add($data);

        redis_lock('upload_robot_temp_' . AID);
        $redis = RedisClient::getInstance();
        $redis->sAdd("robot_sets", $data['user_id']);
        redis_unlock('upload_robot_temp_' . AID);

        alog("user.robot.add", '添加机器人 USER_ID：'.$data['user_id']);
        $this->success('添加成功', $result);
    }
}

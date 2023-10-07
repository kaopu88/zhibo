<?php
/**
 * Created by PhpStorm.
 * User: zack
 * Date: 2020/9/11 0011
 * Time: 下午 5:17
 */

namespace app\h5\controller;

use bxkj_module\service\User;
use think\Db;

class Team extends LoginController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        $userId = $this->data['user']['user_id'];
        $userModel = new User();
        $user = $userModel->getUser($userId);
        if ($user['pid']) {
            $parentUser = $userModel->getUser($user['pid']);
        }

        $this->user_fans($userId);
        $this->assign('partname', $parentUser ? $parentUser['nickname'] : '平台');
        $this->assign('image_url', $parentUser ? $parentUser['avatar'] : img_url('', '200_200', 'logo'));
        return $this->fetch();
    }

    public function get_list()
    {
        $params = request()->param();
        $params['level'] = $params['level'] ? $params['level'] : 1;
        $params['page'] = empty($params['page']) ? 0 : $params['page'];
        $params['length'] = empty($params['length']) ? PAGE_LIMIT : $params['length'];
        $params['user_id'] = $params['user_id'] ?: $this->data['user']['user_id'];
        $userService = new \app\common\service\User;
        $result = $userService->getFans($params);
        if (!empty($result)) {
            foreach ($result as $key => &$value) {
                $this->prase_user($value);
            }
        }
        return $this->success('获取成功', $result);
    }

    public function team_fans()
    {
        $params = request()->param();
        $userId = $params['user_id'];
        if (empty($userId)) return $this->error('用户错误');
        $userModel = new User();
        $user = $userModel->getUser($userId);
        if (empty($user)) return $this->error('用户不存在');

        $binds = Db::name('user_third')->field('user_id,type,openid,bind_time')->where(array('status' => 'bind'))->where(['user_id' => $userId])->select();
        $type = ['weixin'];

        if (!empty($binds)) {
            foreach ($binds as $key => $value) {
                if (in_array($value['type'], $type)) $account[$value['type']] = 1;
            }
        }

        $ishas = Db::name('user_rank')->where(['uid' => $userId, 'parent_id' => $this->data['user']['user_id']])->find();
        if (empty($ishas)) return $this->error('出问题啦');
        $this->user_fans($userId);
        $this->assign('wxbind', isset($account['weixin']) ? '已绑定' : '未绑定');
        $this->assign('username', $user ? $user['nickname'] : '暂无昵称');
        $this->assign('create_time', $user ? $user['create_time'] : '');
        $this->assign('phone', $user ? str_hide($user['phone'], 3, 4) : '暂无');
        $this->assign('image_url', $user ? $user['avatar'] : img_url('', '200_200', 'logo'));
        return $this->fetch();
    }

    protected function prase_user(&$user)
    {
        if (empty($user)) return;
        $total = Db::name('user_rank')->where(['parent_id' => $user['user_id'], 'level' => 1])->count('id');
        $user['zt_son_total'] = $total ?: 0;
    }

    protected function user_fans($userId)
    {
        $ztSonTotal = Db::name('user_rank')->where(['parent_id' => $userId, 'level' => 1])->count('id');
        $twoSonTotal = Db::name('user_rank')->where(['parent_id' => $userId, 'level' => 2])->count('id');
        $ztAllTotal = Db::name('user_rank')->where(['parent_id' => $userId])->count('id');
        $twoOutTotal = $ztAllTotal - $twoSonTotal - $ztSonTotal;
        $todayTotal = Db::name('user_rank')->where(['parent_id' => $userId])->whereTime('add_time', 'today')->count('id');
        $yesterdayTotal = Db::name('user_rank')->where(['parent_id' => $userId])->whereTime('add_time', 'yesterday')->count('id');
        $this->assign('zt_son', $ztSonTotal ?: 0);
        $this->assign('two_son', $twoSonTotal ?: 0);
        $this->assign('two_out_son', $twoOutTotal ?: 0);
        $this->assign('zt_all_son', $ztAllTotal ?: 0);
        $this->assign('today_total', $todayTotal ?: 0);
        $this->assign('yesterday_total', $yesterdayTotal ?: 0);
    }
}
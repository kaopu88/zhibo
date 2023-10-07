<?php

namespace app\h5\controller;

use bxkj_common\RedisClient;
use bxkj_module\service\User;
use think\Db;
use think\Exception;
use think\facade\Request;

class Medal extends LoginController
{
    protected $medalConfig;

    public function __construct()
    {
        parent::__construct();
        try {
            $this->medalConfig = config('medal.');
            if ($this->medalConfig['is_open'] != 1) throw new Exception('勋章功能暂未开启~', 1);
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    public function index()
    {
        $param   = Request::param();
        $medalService = new \app\medal\service\Medal();
        $get = ['status' => 1];
        $total = $medalService->getTotal($get);
        $list = $medalService->getList($get, 0, 100)->toArray();
        $all_method = enum_array('medal_condition_type');
        if (empty($all_method)) return $this->error('暂无数据');
        if (!empty($list)) {
            $data = [];
            foreach ($all_method as $key => $value) {
                $method = parse_name($value['value'], 1);
                $data[$value['value'] . 'total'] = call_user_func_array([$this, 'get' . $method],[['user_id' => $this->data['user']['user_id']]]);
            }

            foreach ($list as $k => &$v) {
                $v['finish'] = 0;
                if ($v['type_val'] <= $data[$v['medal_condition_type'] . 'total']) $v['finish'] = 1;
            }
        }

        $user = new User();
        $user_info = $user->getUser($this->data['user']['user_id'],null,'user_id, nickname, avatar,is_anchor');
        $access_token = $param['token'];
        $this->assign('access_token', $access_token);
        $this->assign('user_info', $user_info);
        $this->assign('list', $list);
        $this->assign('desc', $this->medalConfig['desc']);
        return $this->fetch();
    }

    public function detail()
    {
        $param   = Request::param();
        $medalService = new \app\medal\service\Medal();
        $id = $param['id'];
        $get = ['status' => 1, 'id' => $id];
        $res = $medalService->getOne($get);
        if (empty($res))  return $this->error('暂无数据');
        $method = parse_name($res['medal_condition_type'], 1);
        $data[$res['medal_condition_type'] . 'total'] = call_user_func_array([$this, 'get' . $method],[['user_id' => $this->data['user']['user_id']]]);
        $res['finish'] = 0;
        if ($res['type_val'] <= $data[$res['medal_condition_type'] . 'total']) $res['finish'] = 1;

        $this->assign('res', $res);
        return $this->fetch();
    }

    protected function getBean($params)
    {
        $exp_bean_total = Db::name('bean_log')->where(['user_id' => $params['user_id'], 'type' => 'exp'])->sum('total');
        return  $exp_bean_total ? $exp_bean_total : 0;
    }

    protected function getMillet($params)
    {
        $exp_millet_total = Db::name('millet_log')->where(['user_id' => $params['user_id'], 'type' => 'inc'])->sum('total');
        return  $exp_millet_total ? $exp_millet_total : 0;
    }

    protected function getFans($params)
    {
        $fansTotal = Db::name('follow')->where(['follow_id' => $params['user_id']])->count();
        return  $fansTotal ? $fansTotal : 0;
    }
}
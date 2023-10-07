<?php

namespace bxkj_module\service;

use think\Db;
use think\facade\Request;

class UserPoint extends Service
{
    protected $task_type = [
        'followFriends' => '关注好友',
        'inviteFriends' => '邀请好友',
        'postVideo' => '发布视频',
        'dailyLogin' => '每日登录',
        'watchVideo' => '观看视频',
        'shareVideo' => '分享视频',
        'exchange' => '兑换',
    ];

    public function record($type, $inputData, $is_millet = 0)
    {
        $userId = $inputData['user_id'];
        if (empty($userId)) return $this->setError('USER_ID不能为空');
        $value = isset($inputData['point']) ? (int)$inputData['point'] : 0;
        if ($value <= 0) return $this->setError('point需要大于0');
        Service::startTrans();
        $user = Db::name('user')->field('user_id,nickname,remark_name,points,millet,total_millet,fre_millet')->where(['user_id' => $userId, 'delete_time' => null])->find();
        if (empty($user)) {
            Service::rollback();
            return $this->setError('用户不存在');
        }
        $_change_name = $inputData['change_type'] == 'inc' ? '增加' : '减少';
        if ($type == 'task') {
            $task_name = $this->task_type[$inputData['type']];
            $log_txt = empty($task_name) ? '完成任务' : "完成{$task_name}任务";
            $log_txt .= ',' . $_change_name . $value . APP_REWARD_NAME;
        }
        if ($type == 'exchange') {
            $log_txt = '兑换' . config('app.product_setting.millet_name');
            $log_txt .= ',' . $_change_name . $value . APP_REWARD_NAME;
        }

        $data['user_id'] = $userId;
        $data['type'] = $inputData['type'];
        $data['content'] = $log_txt;
        $data['change_type'] = $inputData['change_type'];
        $data['point'] = $value;
        $data['last_point'] = $user['points'];
        if ($type == 'exchange') {
            $data['total_point'] = $user['points'] - $value;
        } else {
            $data['total_point'] = $user['points'] + $value;
        }
        $data['source_id'] = $inputData['source_id'] ? $inputData['source_id'] : 0;
        $data['create_time'] = time();
        $id = Db::name('user_point_log')->insertGetId($data);
        if (!$id) {
            Service::rollback();
            return $this->setError('更新失败');
        }
        $userMod = new User();
        $update['points'] = $inputData['change_type'] == 'inc' ? ($user['points'] + $value) : ($user['points'] - $value);
        if ($type == 'exchange') {
            $exchange_percent = config('app.cash_setting.exchange_percent');
            $exchange_percent = isset($exchange_percent) ? $exchange_percent: 10;
            $update['millet'] = $user['millet'] + $value / $exchange_percent;
        }

        $num = $userMod->updateData($userId, $update);
        if (!$num) {
            Service::rollback();
            return $this->setError('更新失败');
        }

        if ($is_millet) {
            $this->change_millet_log([
                'user_id' => $userId,
                'point' => $value,
                'last_total_millet' => $user['total_millet'],
                'last_millet' => $user['millet'],
                'millet' => $update['millet']
            ]);
        }

        User::updateRedis($userId, $update);
        Service::commit();
        return [
            'id' => $id,
            'points' => $update['points']
        ];
    }

    public function pageQuery($page_index, $page_size, $condition, $order, $field)
    {
        $this->db = Db::name('user_point_log');
        $count = $this->db->where($condition)->count();
        if ($page_size == 0) {
            $list = $this->db->field($field)
                ->where($condition)
                ->order($order)
                ->select();
            $page_count = 1;
        } else {
            $start_row = $page_size * ($page_index - 1);
            $list = $this->db->field($field)
                ->where($condition)
                ->order($order)
                ->limit($start_row . "," . $page_size)
                ->select();
            if ($count % $page_size == 0) {
                $page_count = $count / $page_size;
            } else {
                $page_count = (int)($count / $page_size) + 1;
            }
        }
        return array(
            'data' => $list,
            'total_count' => $count,
            'page_count' => $page_count
        );
    }

    public function getQuery($condition, $field, $order)
    {
        $this->db = Db::name('user_point_log');
        $list = $this->db->field($field)->where($condition)->order($order)->select();
        return $list;
    }

    public function sumQurey($condition, $data)
    {
        $this->db = Db::name('user_point_log');
        $sum = $this->db->where($condition)->sum($data);
        return $sum;
    }

    //补充的添加收入的记录
    public function change_millet_log($params)
    {
        $order_number = get_order_no('log');
        $trade_no_number = get_order_no('score');
        $log = [
            'log_no' => $order_number,
            'cont_uid' => $params['user_id'],
            'user_id' => $params['user_id'],
            'type' => 'inc',
            'total' => $params['point'],
            'trade_type' => 'score',
            'trade_no' => $trade_no_number,
            'last_total_millet' => $params['last_millet'],
            'last_fre_millet' => 0,
            'last_millet' => $params['last_millet'],
            'total_millet' => $params['millet'],
            'fre_millet' => 0,
            'millet' => $params['millet'],
            'isvirtual' => '0',
            'client_ip' => Request::ip(),
            'app_v' => '',
            'exchange_type' => 'score',
            'exchange_id' =>  0,
            'exchange_total' => $params['point'],
            'create_time' => time()
        ];
        $id = Db::name('millet_log')->insertGetId($log);
        if (!$id) return $this->setError('millet_log insert error ');
        return true;
    }


    public function QueryTodayfind($condition)
    {
        $this->db = Db::name('user_point_log');
        $list = $this->db->where($condition)->whereBetweenTime('create_time', date("Y-m-d"))->find();
        return $list;
    }

    public function getOne($condition)
    {
        $this->db = Db::name('user_point_log');
        $list = $this->db->where($condition)->find();
        return $list;
    }
}
<?php

namespace bxkj_module\service;

use think\Db;

class UserCreditLog extends Service
{
    public function record($type, $inputData)
    {
        $userId = $inputData['user_id'];
        if (empty($userId)) return $this->setError('USER_ID不能为空');

        /*$userService = new User();
        $userinfo = $userService->getBasicInfo($userId, null);
        if (!empty($inputData['order_no']) && !empty($userinfo)) {
            $num = Db::name('recharge_order')->where(array('order_no' => $inputData['order_no']))->update(['last_total_bean' => $userinfo['total_bean'], 'total_bean' => ($userinfo['bean'] + $inputData['all_total'])]);
        }*/

        $config = Db::name('user_credit_rule')->where(['type' => $type])->find();
        if (empty($config)) return $this->setError('记录类型不支持');

        $tpl = $config['tpl'] ? $config['tpl'] : $config['name'];
        $changeType = $config['change_type'];
        $value = isset($inputData['value']) ? (int)$inputData['value'] : 1;
        if ($value <= 0) return $this->setError('value需要大于0');
        if ($config['admin'] == 1 && empty($inputData['aid'])) return $this->setError('没有管理员权限');
        $value = $value > $config['full_value'] ? $config['full_value'] : $value;
        $score = round(($value / $config['full_value']) * $config['full_score']);
        if (isset($config['day_max'])) {
            $where = [
                ['type', 'eq', $type],
                ['user_id', 'eq', $userId],
                ['create_time', 'egt', mktime(0, 0, 0)]
            ];
            $dayScore = Db::name('user_credit_log')->where($where)->sum('score');
            $diff = $config['day_max'] - $dayScore;
            $diff = $diff < 0 ? 0 : $diff;
            $score = min($score, $diff);
        }
        if ($score <= 0) return true;
        Service::startTrans();
        $user = Db::name('user')->field('user_id,nickname,remark_name,credit_score')->where(['user_id' => $userId, 'delete_time' => null])->find();
        if (empty($user)) {
            Service::rollback();
            return $this->setError('用户不存在');
        }
        $tplData = $inputData;
        $tplData['_change_name'] = $changeType == 'inc' ? '增加' : '减少';
        $tplData['_nickname'] = $user['nickname'];
        $tplData['_user_id'] = $user['user_id'];
        $tplData['_remark_name'] = $user['remark_name'];
        //插入数据库
        $data['user_id'] = $userId;
        $data['input_value'] = $value;
        $data['subject'] = parse_tpl($tpl, $tplData);
        $data['type'] = $type;
        $data['change_type'] = $changeType;
        $data['score'] = $score;
        $data['aid'] = $inputData['aid'] ? $inputData['aid'] : 0;
        $data['create_time'] = time();
        $id = Db::name('user_credit_log')->insertGetId($data);
        if (!$id) {
            Service::rollback();
            return $this->setError('更新失败');
        }
        $update['credit_score'] = $changeType == 'inc' ? ($user['credit_score'] + $score) : ($user['credit_score'] - $score);
        $update['credit_score'] = $update['credit_score'] < 0 ? 0 : $update['credit_score'];
        $update['credit_score'] = $update['credit_score'] > 1000 ? 1000 : $update['credit_score'];
        $num = Db::name('user')->where('user_id', $userId)->update($update);
        if (!$num) {
            Service::rollback();
            return false;
        }
        if ($inputData['not_update_redis'] != '1') {
            User::updateRedis($userId, $update);
        }
        Service::commit();
        return [
            'id' => $id,
            'credit_score' => $update['credit_score']
        ];
    }
}
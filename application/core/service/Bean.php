<?php

namespace app\core\service;

use bxkj_common\ClientInfo;
use think\Db;

class Bean extends \bxkj_module\service\Bean
{
    //创建账号
    public function createAccount($user)
    {
        $data = array(
            'user_id' => $user['user_id'],
            'cash_status' => '1',
            'pay_status' => '1',
            'bean' => 0,
            'fre_bean' => 0,
            'total_bean' => 0,
            'isset_pwd' => '0',
            'create_time' => time()
        );
        $id = Db::name('bean')->insertGetId($data);
        if (!$id) return $this->setError(APP_BEAN_NAME.'账号创建失败');
        $data['id'] = $id;
        return $data;
    }

    //获取账号明细（批量）
    public function getBatchInfo($userIds)
    {
        $userIds = is_array($userIds) ? $userIds : explode(',', $userIds);
        $result = Db::name('bean')->field('user_id,cash_status,pay_status,bean,fre_bean,total_bean,isset_pwd')->whereIn('user_id', $userIds)->select();
        return $result ? $result : [];
    }

    //转化
    public function conversion($inputData)
    {
        ClientInfo::refreshByParams($inputData);
        $userService = new User();
        if (is_array($inputData['user_id'])) {
            $user = &$inputData['user_id'];
        } else {
            $user = $userService->getBasicInfo($inputData['user_id']);
        }
        if (is_array($inputData['to_uid'])) {
            $toUser = &$inputData['to_uid'];
        } else {
            $toUser = $userService->getBasicInfo($inputData['to_uid']);
        }
        if (empty($user)) return $this->setError('用户不存在[01]');
        if (empty($toUser)) return $this->setError('用户不存在[02]');
        if ($user['user_id'] == $toUser['user_id']) return $this->setError('不能支付给自己');
        self::startTrans();
        //先支出金币
        $payRes = $this->exp(array(
            'user_id' => &$user,
            'trade_type' => $inputData['trade_type'],
            'trade_no' => $inputData['trade_no'],
            'total' => $inputData['total']
        ));
        if (!$payRes) {
            self::rollback();
            return false;
        }
        //再收入谷子
        $millet = new Millet();
        $incRes = $millet->inc(array(
            'cont_uid' => &$user,
            'user_id' => &$toUser,
            'trade_type' => $inputData['trade_type'],
            'trade_no' => $inputData['trade_no'],
            'exchange_type' => 'bean',
            'exchange_id' => 0,
            'total' => $inputData['total']
        ));
        if (!$incRes) {
            self::rollback();
            return $this->setError($millet->getError());
        }
        self::commit();
        return array(
            'bean_log' => $payRes['log_no'],
            'millet_log' => $incRes['log_no']
        );
    }


}
<?php

namespace app\api\controller;


use bxkj_common\RabbitMqChannel;
use app\api\service\Blacklist as BlacklistModel;
use app\common\controller\UserController;

class Blacklist extends UserController
{
    public function index()
    {
        $params = request()->param();
        $offset = isset($params['offset']) ? $params['offset'] : 0;
        $length = isset($params['length']) ? ($params['length'] > 100 ? 100 : $params['length']) : PAGE_LIMIT;
        $params['user_id'] = USERID;
        $blacklistModel = new BlacklistModel();
        $list = $blacklistModel->getList($params, $offset, $length);
        return $this->success($list, '获取成功');
    }

    public function add()
    {
        $params = request()->param();
        $params['to_uid'] = $params['user_id'];
        $params['user_id'] = USERID;
        $blacklistModel = new BlacklistModel();
        $result = $blacklistModel->addUser($params);
        if (!$result) return $this->jsonError($blacklistModel->getError());
        //对接rabbitMQ
        $rabbitChannel = new RabbitMqChannel(['user.credit']);
        $rabbitChannel->exchange('main')->send('user.credit.pulled_black', ['user_id' => $params['to_uid'], 'be_user_id' => USERID]);
        $rabbitChannel->exchange('main')->send('user.behavior.black', ['to_uid' => $params['to_uid'], 'user_id' => USERID]);
        $rabbitChannel->close();
        return $this->success(array('id' => $result), '加入黑名单成功');
    }

    public function delete()
    {
        $params = request()->param();
        $params['to_uid'] = $params['user_id'];
        $params['user_id'] = USERID;
        $blacklistModel = new BlacklistModel();
        $result = $blacklistModel->deleteUser($params);
        if (!$result) return $this->jsonError($blacklistModel->getError());
        $rabbitChannel = new RabbitMqChannel(['user.credit']);
        $rabbitChannel->exchange('main')->send('user.behavior.cancel_black', ['to_uid' => $params['to_uid'], 'user_id' => USERID]);
        $rabbitChannel->close();
        return $this->success($result, '移出黑名单成功');
    }
}
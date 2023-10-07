<?php

namespace app\api\service;
use bxkj_module\service\DsIM;
use think\Db;
use app\common\service\Service;
use bxkj_module\service\User;
use bxkj_common\RedisClient;

class Blacklist extends Service
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getList($get, $offset = 0, $length = 10)
    {
        if (empty($get['user_id'])) return [];
        $db = Db::name('user_blacklist');
        $result = $db->field('id,to_uid,status,create_time')->where([
            'user_id' => $get['user_id'],
            'status' => '1'
        ])->limit($offset, $length)->order('create_time desc')->select();
        if (!empty($result)) {
            $toUids = [];
            $toUsers = [];
            foreach ($result as &$item) {
                $item['to_uid'] = (string)$item['to_uid'];
                $toUids[] = $item['to_uid'];
            }
            if (!empty($toUids)) {
                $userModel = new user();
                $toUsers = $userModel->getUsers($toUids, USERID, '_list');
                $toUsers = $toUsers ? $toUsers : [];
            }
            foreach ($result as &$item) {
                $toUser = $this->getItemByList($item['to_uid'], $toUsers, 'user_id');
                $item['create_time']=date('Y-m-d',$item['create_time']);
                if ($toUser) $item = array_merge($item, $toUser);
            }
        }
        return $result ? $result : [];
    }

    public function addUser($inputData)
    {
        $toUid = $inputData['to_uid'];
        if (empty($toUid)) return $this->setError('请选择用户');
        if ($toUid == $inputData['user_id']) return $this->setError('不能将自己加入黑名单');
        $where['user_id'] = $inputData['user_id'];
        $where['to_uid'] = $toUid;
        $where['status'] = '1';
        $num = Db::name('user_blacklist')->where($where)->limit(1)->count();
        if ($num > 0) return $this->setError('请勿重复加入');
        $num2 = Db::name('user')->where(array('user_id' => $toUid))->count();
        if ($num2 <= 0) return $this->setError('用户不存在');
        $data['user_id'] = $inputData['user_id'];
        $data['to_uid'] = (string)$toUid;
        $data['status'] = '1';
        $data['create_time'] = time();
        $result = Db::name('user_blacklist')->insertGetId($data);
        if (!$result) return $this->setError('加入失败');
        $redis = RedisClient::getInstance();
        $redis->zAdd("blacklist:{$inputData['user_id']}", $data['create_time'], $toUid);
        $DsIM = new DsIM();
        $DsIM->specializeFriend($data['user_id'], $data['to_uid']);
        return $result;
    }

    public function deleteUser($inputData)
    {
        $toUid = $inputData['to_uid'];
        if (empty($toUid)) return $this->setError('请选择用户');
        $where['user_id'] = $inputData['user_id'];
        $where['to_uid'] = $toUid;
        $where['status'] = '1';
        $num = Db::name('user_blacklist')->where($where)->update(array(
            'status' => '0',
            'cancel_time' => time()
        ));
        if (!$num) return $this->setError('移除失败');
        $redis = RedisClient::getInstance();
        $redis->zRem("blacklist:{$inputData['user_id']}", $toUid);
        $DsIM = new DsIM();
        $DsIM->specializeFriend($inputData['user_id'], $toUid, 1, 0);
        return $num;
    }

}
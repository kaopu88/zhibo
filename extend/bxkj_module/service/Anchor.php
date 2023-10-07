<?php

namespace bxkj_module\service;

use bxkj_common\RedisClient;
use think\Db;

class Anchor extends KpiQuery
{
    public function getLocation($userId)
    {
        $redis = RedisClient::getInstance();
        $key = "BG_LIVE:location:{$userId}";
        if (!$redis->exists($key)) return ['location_type' => 'auto'];
        $json = $redis->get($key);
        $arr = json_decode($json, true);
        if (!$arr) return ['location_type' => 'auto'];
        $arr['location_type'] = ($arr['lng'] == 0 && $arr['lat'] == 0) ? 'unknown' : 'static';
        return $arr;
    }

    public function setLocation($inputData)
    {
        $location_type = $inputData['location_type'];
        $city = $inputData['city'];
        $lat = $inputData['lat'];
        $lng = $inputData['lng'];
        $userId = $inputData['user_id'];
        if (empty($userId)) return $this->setError('用户不存在');
        if (!in_array($location_type, ['static', 'auto', 'unknown'])) {
            return $this->setError('定位类型不正确');
        }
        if ($location_type == 'static') {
            if (empty($city)) return $this->setError('缺少城市参数');
            if (empty($lat) || empty($lng)) return $this->setError('缺少经纬度参数');
        }
        $redis = RedisClient::getInstance();
        $key = "BG_LIVE:location:{$userId}";
        if ($location_type == 'unknown') {
            $data = ['city' => '未知', 'lat' => 0, 'lng' => 0];
            $redis->set($key, json_encode($data));
        } elseif ($location_type == 'static') {
            $data = ['city' => $city, 'lat' => $lat, 'lng' => $lng];
            $redis->set($key, json_encode($data));
        } else {
            $redis->del($key);
        }
        return true;
    }

    //创建主播
    public function create($inputData, $type = 0)
    {
        $userId = $inputData['user_id'];
        $force = $inputData['force'];
        $agentId = $inputData['agent_id'];
        if (empty($userId)) return $this->setError('请选择用户');
        if (empty($type)) {
            if (empty($agentId)) return $this->setError('请选择'.config('app.agent_setting.agent_name'));
        }
        if (!in_array($force, ['0', '1'])) return $this->setError('force参数错误');
        if (empty($inputData['admin']) || !is_array($inputData['admin'])) return $this->setError('管理员不存在');
        if (empty($type)) {
            $agent = Db::name('agent')->where(['id' => $agentId, 'delete_time' => null])->find();
            if (empty($agent)) return $this->setError(config('app.agent_setting.agent_name') . '不存在');
            $max_anchor_num = $agent['max_anchor_num'];
            if ($agent['anchor_num'] >= $max_anchor_num) return $this->setError('主播人数已达到最大限额');
        }
        Service::startTrans();
        $user = Db::name('user')->where([['user_id', '=', $userId], ['delete_time', 'null']])->find();
        if (empty($user)) {
            Service::rollback();
            return $this->setError('用户不存在');
        }
        $previous = Db::name('anchor')->where(['user_id' => $userId])->find();
        //上一个主播身份
        if ($previous) {
            if ($force != '1') {
                Service::rollback();
                return $this->setError('用户已是主播');
            }
            if ($previous['agent_id'] == $agentId) {
                Service::rollback();
                return $this->setError('主播身份重复');
            }
            //解除老的主播身份
            $removeNum = $this->remove($previous, null, $inputData['admin']);
            if (!$removeNum) {
                Service::rollback();
                return $this->setError('强制创建失败');
            }
        }
        $anchorData = [
            'agent_id' => $agentId,
            'user_id' => $userId,
            'total_millet' => 0,
            'create_time' => time()
        ];
        $res = Db::name('anchor')->insert($anchorData);
        if (!$res) return $this->setError('创建失败');
        $log = [
            'user_id' => $userId,
            'agent_id' => $agentId,
            'type' => 'add',
            'admin_type' => $inputData['admin']['type'],
            'aid' => $inputData['admin']['id'],
            'act_time' => time(),
            'detail' => ''
        ];
        Db::name('anchor_log')->insertGetId($log);
        $update = ['is_anchor' => '1', 'live_status' => '1'];
        $num2 = Db::name('user')->where('user_id', $userId)->update($update);
        if ($num2) {
            UserRedis::updateData($userId, $update);
            Db::name('agent')->where('id', $agentId)->setInc('anchor_num', 1);
        }
        Service::commit();
        return $anchorData;
    }

    //取消主播
    public function cancel($userIds, $agentId = null, $admin = [])
    {
        $userIds = is_array($userIds) ? $userIds : explode(',', trim($userIds));
        if (empty($userIds)) return $this->setError('请选择用户');
        $userIds = array_unique($userIds);
        $total = 0;
        foreach ($userIds as $userId) {
            $res = $this->remove($userId, $agentId, $admin);
            if ($res) $total++;
        }
        return $total;
    }

    //移除主播
    protected function remove($anchorUid, $agentId = null, $admin = null)
    {
        Service::startTrans();
        if (is_array($anchorUid)) {
            $anchor = $anchorUid;
        } else {
            $where = [['user_id', 'eq', $anchorUid]];
            Agent::agentWhere($where, ['agent_id' => $agentId]);
            $anchor = Db::name('anchor')->where($where)->find();
        }
        if (empty($anchor)) {
            Service::rollback();
            return $this->setError('主播不存在');
        }
        $num = Db::name('anchor')->where('user_id', $anchor['user_id'])->delete();
        if (!$num) {
            Service::rollback();
            return $this->setError('移除失败');
        }
        $update = ['is_anchor' => '0', 'live_status' => '0'];
        $num2 = Db::name('user')->where('user_id', $anchor['user_id'])->update($update);
        if ($num2) {
            Db::name('agent')->where('id', $anchor['agent_id'])->setDec('anchor_num', 1);
            User::updateRedis($anchor['user_id'], $update);
        }
        //记录操作日志
        $log = [
            'type' => 'remove',
            'user_id' => $anchor['user_id'],
            'agent_id' => $anchor['agent_id'],
            'act_time' => time(),
            'detail' => json_encode($anchor),
            'admin_type' => $admin['type'],
            'aid' => $admin['id']
        ];
        Db::name('anchor_log')->insertGetId($log);
        Service::commit();
        return $num;
    }

    /**
     * 查询单条记录
     * @param string $userId
     */
    public function getOne($userId = '')
    {
        if (empty($userId)) return $this->setError('请选择用户');
        $isvirtual = Db::name('user')->where(['user_id' => USERID])->value('isvirtual');
        if ($isvirtual) return $this->setError('虚拟用户不能申请主播!');
        $anchor= Db::name('anchor')->where(['user_id' => $userId])->find();
        if (!empty($anchor)) return $this->setError('您已经是主播啦!');
        $isverfied = Db::name('user_verified')->where(['user_id' => $userId])->order('id desc')->find();
        if ($isverfied['status'] == '0') return $this->setError('您有实名正在审核中,请先联系平台客服处理!');
        return true;
    }

    public function getAllList($agentId, $page, $pageSize)
    {
        if (empty($agentId)) return [];
        $fields = 'anchor.user_id,anchor.agent_id,anchor.total_millet,anchor.total_duration,anchor.create_time,
        user.nickname,user.username,user.phone,user.avatar,user.status,user.type,user.live_status,user.level,user.gender,anchor.anchor_lv,anchor.cash_rate';
        $res= Db::name('anchor')->alias('anchor')->join('__USER__ user', 'user.user_id=anchor.user_id', 'LEFT')
            ->order('anchor.anchor_lv','desc')
            ->field($fields)
            ->where(['anchor.agent_id' => $agentId])
            ->page($page,$pageSize)
            ->select();
        return $res;
    }
}
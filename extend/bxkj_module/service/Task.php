<?php

namespace bxkj_module\service;

use think\Db;

class Task extends Service
{
    /*
     * 新增任务/更新任务信息
     */
    public function subTask($data)
    {
        $task_type = $data['task_type'];
        $user_id = $data['user_id'];
        $task = Db::name('user_task')->where(['task_type' => $task_type, 'status' => 1])->order('stor desc')->find();
        if (empty($task)) return false;
        $task_setting = json_decode($task['task_setting'], true);
        $where = [];
        $where['user_id'] = $user_id;
        $where['task_id'] = $task['id'];
        $task_log = Db::name('user_task_log');
        $task_log->where($where);
        //是否为循环任务,查询今天的任务日志
        if ($task['type'] == 1) {
            $task_log->whereBetweenTime('create_time', date("Y-m-d"));
        }
        $task_log_info = $task_log->find();
        if($task['type'] == 0&&$task_log_info['status']==2){
             return;
        }
        if (!isset($data['point'])) {
            $json_array = $task_setting;
            if (array_key_exists('task_value', $json_array)) {
                $data['point'] = $task_setting['point'];
                $isMultiple = 0;
            } else {
                $isMultiple = 1;
                $arrayKey = array_keys($json_array);
                sort($arrayKey);
                $rewodkey = $data['task_value'] ? $data['task_value'] : 1;
                foreach ($arrayKey as $k => $v) {
                    if ($rewodkey >= $v) {
                        $point = $json_array[$v];
                        $data['status'] = 1;
                    }
                }
                if (empty($point)) {
                    $point = current($json_array);
                    if ($data['task_value'] >= $arrayKey[0]) {
                        $data['status'] = 1;
                    } else {
                        $data['status'] = 0;
                    };
                }
                $data['point'] = $point;
                $task_setting['task_value'] = $arrayKey[0];
            }
        }
        if (empty($task_log_info)) {
            $data['create_time'] = time();
            //拼接字段，如果没有传入相关值就使用设置内的相关值
            if (!isset($data['task_id'])) {
                $data['task_id'] = $task['id'];
            }
            if (!isset($task_setting['task_value']) && !isset($data['status'])) {
                $data['status'] = 1;
            }
            if ($isMultiple == 1) {
                $task_setting['task_value'] = $json_array[key($arrayKey)];
            }
            if (isset($task_setting['task_value']) && !isset($data['status'])) {
                if ($data['task_value'] >= $task_setting['task_value']) {
                    $data['status'] = 1;
                }
            }
            $task_log_id = Db::name('user_task_log')->insertGetId($data);
            if ($task_log_id === false) return false;
            //直接完成的需增加会员积分/记录
            if ($data['status'] == 2) {
                //判断奖励是设定的类型
                switch ($task['reward_type']) {
                    case 2:
                        $millet = new Millet();
                        $find = $millet->QueryTodayfind(['trade_type' => $data['task_type'], 'total' => $data['point']]);
                        if ($find) {
                            return false;
                        }
                        $rest = $this->rewardMilletUser($user_id, $data['point'], $data['task_type']);
                        return $rest;
                        break;
                    case 3:
                        $bean = new Bean();
                        $find = $bean->QueryTodayfind(['trade_type' => $data['task_type'], 'total' => $data['point']]);
                        if ($find) {
                            return false;
                        }
                        $rest = $this->rewardBeanUser($user_id, $data['point'], $data['task_type']);
                        return $rest;
                        break;
                    case 4:
                        $cash = new Cash();
                        $find = $cash->QueryTodayfind(['trade_type' => $data['task_type'], 'total' => $data['point']]);
                        if ($find) {
                            return false;
                        }
                        $rest = $this->rewardCashUser($user_id, $data['point'], $data['task_type']);
                        return $rest;
                    default:
                        $ponitData = [
                            'user_id' => $data['user_id'], //用户id
                            'source_id' => $data['task_id'], //任务id
                            'point' => $data['point'], //积分值
                            'change_type' => 'inc', //更新类型,增加/减少
                            'type' => $data['task_type'], //任务类型
                        ];
                        $userPoint = new UserPoint();
                        $res = $userPoint->record('task', $ponitData);
                        if (!$res) {
                            return false;
                        }
                }
            }
        } else {
            $task_log_id = $task_log_info['id'];
            $where = [
                'id' => $task_log_id
            ];
            //状态为未完成则需更新如果有必须另外计算多重的值
            if (array_key_exists('task_value', $json_array)) {
                $data['point'] = $task_setting['point'];
                $isMultiple = 0;
            } else {
                //多重复奖:先获取复奖数组，然后看条件符合进行奖励
                $isMultiple = 1;
                $multipleAdd = (int)$task_log_info['task_value'] + (int)$data['task_value'];
                $task = Db::name('user_task')->where(['task_type' => $task_type, 'status' => 1])->order('stor desc')->find();
                if (empty($task)) {
                    return false;
                }
                $task_setting = json_decode($task['task_setting'], true);
                $arrayKey = array_keys($task_setting);
                sort($arrayKey);
                foreach ($arrayKey as $k => $v) {
                    if ($multipleAdd >= $v) {
                        $point = $task_setting[$v];
                    }
                }
                if (empty($point)) {
                    $point = current($json_array);
                }
                $data['point'] = $point;
                $task_setting['task_value'] = $arrayKey[0];
            }
            if ($task_log_info['status'] == 0) {
                //已完成值+新增值大于任务要求,则直接完成
                if (((int)$task_log_info['task_value'] + (int)$data['task_value']) >= $task_setting['task_value']) {
                    $updateData = ['status' => 1];
                    Db::name('user_task_log')->where($where)->update($updateData);
                    $rs = Db::name('user_task_log')->where($where)->inc('task_value', $data['task_value'])->update();
                } else {
                    $rs = Db::name('user_task_log')->where($where)->inc('task_value', $data['task_value'])->update();
                }
            } else {
                //如果已经完成计算多重奖励写入数据库:这里point如果符合多重的值就进行改动
                if ($isMultiple == 1 && $task_log_info['status'] >= 1) {
                    $updateData = [
                        'status' => 1,
                        'point' => $data['point'],
                    ];
                    Db::name('user_task_log')->where($where)->update($updateData);
                    $rs = Db::name('user_task_log')->where($where)->inc('task_value', $data['task_value'])->update();
                }else{
                    $updateData = [
                        'status' => 1,
                        'point' => $data['point'],
                    ];
                    Db::name('user_task_log')->where($where)->update($updateData);
                    $rs = Db::name('user_task_log')->where($where)->inc('task_value', $data['task_value'])->update();

                }
            }
        }
        return ['id' => $task_log_id];
    }

    /*
     * 完成任务,领取奖励
     */
    public function finish($user_id, $task_id)
    {
        $task = Db::name('user_task')->where(['id' => $task_id, 'status' => 1])->find();
        if (empty($task)) return $this->setError('没有找到任务');

        $task_setting = json_decode($task['task_setting'], true);
        if (empty($task_setting)) return $this->setError('任务未设置');
        $task_log = Db::name('user_task_log');
        $task_log->where(['user_id' => $user_id, 'task_id' => $task_id]);
        if ($task['type'] == 1) $task_log->whereBetweenTime('create_time', date("Y-m-d"));
        $task_log_info = $task_log->find();

        if (empty($task_log_info)) return $this->setError('任务不存在');
        if ($task_log_info['status'] != 1) return $this->setError('任务未完成或者已领取');

        $rest= Db::name('user_task_log')->where(['id' => $task_log_info['id']])->update(['status' => 2]);
        //计算积分奖励(判断是否为多重奖励)
        $json_array = $task_setting;
        if ($task['type'] == 0) {

            $res = $this->giveReward($user_id, $task, $task_log_info);
        }
        if ($task['type'] == 1) {
            $arrayKey = array_keys($json_array);
            sort($arrayKey);
            $point = 0;
            foreach ($arrayKey as $k => $v) {
                if ($task_log_info['task_value'] >= $v) $point = $json_array[$v];
            }
            if ($point <= 0) return false;
           $res = $this->giveReward($user_id, $task, $task_log_info, $point);
        }
        return $res;
    }

    /**
     * 奖励
     * @param $user_id
     * @param $task
     * @param $task_log_info
     * @param string $point
     * @return array|bool
     */
    protected function giveReward($user_id, $task, $task_log_info, $point = '')
    {
        $task_setting = json_decode($task['task_setting'], true);
        switch ($task['reward_type']) {
            case 1:
                $userPoint = new UserPoint();
                if ($task['type'] == 0) {
                    $find = $userPoint->getOne(['user_id'=>$user_id,'type' => $task_log_info['task_type']]);
                    $point = $task_setting['point'];
                }
                if ($task['type'] == 1) {
                    $find = $userPoint->QueryTodayfind(['user_id'=>$user_id,'type' => $task_log_info['task_type'], 'point' => $point]);
                }
                if ($find) return false;
                $ponitData = ['user_id' => $user_id, 'source_id' => $task['id'], 'point' => $point, 'change_type' => 'inc', 'type' => $task_log_info['task_type']];
                $res = $userPoint->record('task', $ponitData);
                if (!$res) return false;
                return ['point' => $task_setting['point'], 'total_point' => $res['points']];
                break;
            case 2:
                $millet = new Millet();
                if ($task['type'] == 0) {
                    $point = $task_setting['point'];
                    $find = $millet->getOne(['user_id'=>$user_id,'trade_type' => $task_log_info['task_type']]);
                }
                if ($task['type'] == 1) {
                    $find = $find = $millet->QueryTodayfind(['user_id'=>$user_id,'trade_type' => $task_log_info['task_type'], 'total' => $point]);
                }
                if ($find) return false;

                $rest = $this->rewardMilletUser($user_id, $point, $task_log_info['task_type']);
                return $rest;
                break;
            case 3:
                $bean = new Bean();
                if ($task['type'] == 0) {
                    $point = $task_setting['point'];
                    $find = $bean->getOne(['user_id'=>$user_id,'trade_type' => $task_log_info['task_type']]);
                }
                if ($task['type'] == 1) {
                    $find = $bean->QueryTodayfind(['user_id'=>$user_id,'trade_type' => $task_log_info['task_type'], 'total' => $point]);
                }
                if ($find) return false;
                $rest = $this->rewardBeanUser($user_id, $point, $task_log_info['task_type']);
                return $rest;
                break;
            case 4:
                $cash = new Cash();
                if ($task['type'] == 0) {
                    $point = $task_setting['point'];
                    $find = $cash->getOne(['user_id'=>$user_id,'trade_type' => $task_log_info['task_type']]);
                }
                if ($task['type'] == 1) {
                    $find = $cash->QueryTodayfind(['user_id'=>$user_id,'trade_type' => $task_log_info['task_type'], 'total' => $point]);
                }
                if ($find) return false;
                $rest = $this->rewardCashUser($user_id, $point, $task_log_info['task_type']);
                return $rest;
                break;
            default:
                return false;
        }
    }

    /**
     * 视频上传任务
     * 增加金币或钻石
     */
    public function uploadVideo($data)
    {
        if (empty($data)) return false;
        $info = Db::name('sys_config')->where(['mark' => 'new_people_task'])->value('value');
        $configAll = json_decode($info, true);
        if ($configAll['new_people_task_config']['is_video_status'] != 2) return false;
        $type = $configAll['new_people_task_config']['video_add_type'];
        $videoCount = Db::name('video_unpublished')->where(['user_id' => $data['uid'], 'source' => 'user', 'audit_status' => 2])->whereTime('audit_time', 'today')->count();//今天是否有上传
        if ($videoCount == 1) {
            $rewardMillet = $configAll['new_people_task_config']['vedio_upload_reward']['millet'];
            $rewardBean = $configAll['new_people_task_config']['vedio_upload_reward']['bean'];
            if ($rewardMillet > 0) $this->rewardMillet($data['uid'], $rewardMillet);
            if ($rewardBean > 0) $this->rewardBean($data['uid'], $rewardBean);
        }
        //累计奖励
        $videoNum = $configAll['new_people_task_config']['video_num'];
        if (empty($videoNum)) return false;
        if ($type == 1) {
            $videoCount = Db::name('video_unpublished')->where(['user_id' => $data['uid'], 'source' => 'user', 'audit_status' => 2])->count();//总上传
        }
        foreach ($videoNum as $key => $value) {
            if (empty($value)) continue;
            if ($value == $videoCount) {
                $rewardTotalMillet = $configAll['new_people_task_config']['video_millet'][$key];
                $rewardTotalBean = $configAll['new_people_task_config']['video_bean'][$key];
            }
        }
        if ($rewardTotalMillet > 0) $this->rewardMillet($data['uid'], $rewardTotalMillet);
        if ($rewardTotalBean > 0) $this->rewardBean($data['uid'], $rewardTotalBean);
        return true;
    }

    /**
     * 奖励钻石
     */
    protected function rewardBean($uid, $rewardBean)
    {
        $bean = new Bean();
        $bean->reward([
            'user_id' => $uid,
            'type' => 'video_reward_bean',
            'bean' => $rewardBean
        ]);
    }

    /**
     * 奖励钻石
     */
    protected function rewardBeanUser($uid, $rewardBean, $tradeType)
    {
        $bean = new Bean();
        $add = $bean->rewardBean([
            'user_id' => $uid,
            'type' => 'video_reward_bean',
            'bean' => $rewardBean,
            'trade_type' => $tradeType
        ]);
        return $add;
    }

    /**
     * 奖励金币
     */
    protected function rewardMillet($uid, $rewardMillet)
    {
        $millet = new Millet();
        $millet->reward([
            'user_id' => $uid,
            'cont_uid' => $uid,
            'type' => 'video_reward_millet',
            'bean' => $rewardMillet
        ], 'video_reward');
    }

    /**
     * 奖励金币
     */
    protected function rewardMilletUser($uid, $rewardMillet, $tradeType)
    {
        $millet = new Millet();
        $add = $millet->rewardMill([
            'user_id' => $uid,
            'cont_uid' => $uid,
            'type' => 'video_reward_millet',
            'bean' => $rewardMillet
        ], $tradeType);
        return $add;
    }

    /**
     * 奖励现金
     */
    protected function rewardCashUser($uid, $rewardBean, $tradeType)
    {
        $bean = new Cash();
        $add = $bean->rewardCash([
            'user_id' => $uid,
            'type' => 'inviteFriends_reward_cash',
            'cash' => $rewardBean,
            'trade_type' => $tradeType
        ]);
        return $add;
    }
}
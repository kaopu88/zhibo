<?php

namespace bxkj_module\service;

use bxkj_common\DataFactory;
use think\Db;

class User_task extends Service
{
    protected $userTask;
    protected $taskSetting;
    protected $taskToady;

    public function __construct()
    {
        $this->rewardName = [
            '1' => APP_REWARD_NAME,
            '2' => APP_MILLET_NAME,
            '3' => APP_BEAN_NAME,
            '4' => APP_CASH_NAME,
        ];
    }

    /*
     * 获取用户任务列表,及任务状态
     */
    public function userTaskList($user_id, $mission_type)
    {
        $user_taskList = [];
        $task_list     = Db::name('user_task')->where(['status' => 1, 'mission_type' => $mission_type])->order('stor desc')->cursor();
        if (empty($task_list)) return [];
        foreach ($task_list as $task) {
            $this->task_init($task, $user_id);
            $where = ['user_id' => $user_id, 'task_id' => $task['id']];
            switch ($task['task_type']) {
                case 'followFriends' :
                    $task_type                       = 'followFriends';
                    $getmultiple                     = $this->getmultiple($task['task_setting'], $user_id, $task_type);
                    $this->taskSetting['task_value'] = $getmultiple['task_value'];
                    $this->taskSetting['point']      = $getmultiple['point'];
                    $this->userTask['rewoardname']   = $getmultiple['rewoard'];
                    $this->userTask['tips']          = "关注{$this->taskSetting['task_value']}名用户，增加{$this->taskSetting['point']}" . $getmultiple['rewoard'];
                    $this->userTask['rewoadpoint']   = $this->taskSetting['point'];
                    break;
                case 'postVideo' :
                    $task_type                       = 'postVideo';
                    $getmultiple                     = $this->getmultiple($task['task_setting'], $user_id, $task_type);
                    $this->taskSetting['task_value'] = $getmultiple['task_value'];
                    $this->taskSetting['point']      = $getmultiple['point'];
                    $this->userTask['tips']          = "新拍摄上传{$this->taskSetting['task_value']}视频，可获得{$this->taskSetting['point']}" . $getmultiple['rewoard'];
                    if ($this->userTask['status'] == 0) {
                        $this->userTask['status_txt'] = '去拍摄';
                    }
                    $this->userTask['rewoardname'] = $getmultiple['rewoard'];
                    $this->userTask['rewoadpoint'] = $this->taskSetting['point'];
                    break;
                case 'dailyLogin' :
                    $point       = 0;
                    $task_type   = 'dailyLogin';
                    $getmultiple = $this->getmultiple($task['task_setting'], $user_id, $task_type);
                    if ($this->userTask['status'] == 0) {
                        $this->userTask['status_txt'] = '去签到';
                        $record_value                 = Db::name('user_task_log')->where($where)->whereBetweenTime('create_time', date("Y-m-d", strtotime("-1 day")))->value('task_value');
                        //今天未签到,查找昨天记录
                        //未找到昨日签到/昨天签满7天/ tip积分为第二次积分
                        //第六天取 第一次积分
                        //其他取 day+1 次积分
                        if (empty($record_value) || ($record_value == 7)) {
                            $point                        = $this->taskSetting[1];
                            $today_ponit                  = $this->taskSetting[0];
                            $this->userTask['check_days'] = 0;
                            $first_day                    = time();
                        } elseif ($record_value == 6) {
                            $point                        = $this->taskSetting[0];
                            $today_ponit                  = $this->taskSetting[6];
                            $this->userTask['check_days'] = $record_value;
                            $first_day                    = strtotime('-' . $record_value . ' day');
                        } else {
                            $point                        = $this->taskSetting[$record_value + 1];
                            $today_ponit                  = $this->taskSetting[$record_value];
                            $this->userTask['check_days'] = $record_value;
                            $first_day                    = strtotime('-' . $record_value . ' day');
                        }
                        $this->userTask['days']        = $this->getSevenDays($first_day);
                        $this->userTask['rewoardname'] = $getmultiple['rewoard'];
                        $this->userTask['rewoadpoint'] = $point;
                        $this->userTask['today_tips']  = "立即获取{$today_ponit}" . $getmultiple['rewoard'];
                    } else {
                        //今天已签到
                        //今天为第7次 明日积分为第一次
                        //其他取 day 次积分
                        if ($this->taskToady['task_value'] == 7) {
                            $point = $this->taskSetting[0];
                        } else {
                            $point = $this->taskSetting[$this->taskToady['task_value']];
                        }
                        $first_day                    = strtotime("-" . ($this->taskToady['task_value'] - 1) . " day");
                        $this->userTask['days']       = $this->getSevenDays($first_day);
                        $this->userTask['check_days'] = $this->taskToady['task_value'];
                    }
                    $this->userTask['tips']        = "明日签到，可获取{$point}" . $getmultiple['rewoard'];
                    $this->userTask['rewoardname'] = $getmultiple['rewoard'];
                    $this->userTask['rewoadpoint'] = $point;
                    break;
                case 'watchVideo' :
                    //这里要获取时间分段，然后返还完成的在哪个时间段
                    $task_type = 'watchVideo';
                    $rest      = Db::name('user_task')->where(['status' => 1, 'task_type' => $task_type])->find();
                    switch ($rest['reward_type']) {
                        case 2:
                            $rewoard = APP_MILLET_NAME;
                            break;
                        case 3:
                            $rewoard = APP_BEAN_NAME;
                            break;
                        case 4:
                            $rewoard = APP_CASH_NAME;
                            break;
                        default:
                            $rewoard = APP_REWARD_NAME;
                    }
                    $arrayKey  = array_keys($this->taskSetting);
                    $timeArray = [];
                    foreach ($arrayKey as $k => $v) {
                        $timeArray[] = ceil($v / 60000) . "分钟";
                    }
                    if ($rest['type'] == 1) {
                        //这里要判断用户完成到哪里了，如果完成了前面的就显示后面的数据
                        $finish_value = Db::name('user_task_log')->where(['task_type' => $task_type, 'user_id' => $user_id])->whereBetweenTime('create_time', date("Y-m-d"))->value('task_value');
                        $arrayKey     = array_keys($this->taskSetting);
                        $arrayAll     = array_values($this->taskSetting);
                        array_unshift($arrayAll, 0);
                        if ($finish_value) {
                            sort($arrayKey);
                            $task_valueNew = current($this->taskSetting);
                            $key           = $arrayKey[0];
                            foreach ($arrayKey as $k => $v) {
                                if ($finish_value <= $v) {
                                    $task_valueNew = $this->taskSetting[$v];
                                    $key           = $v;
                                    break;
                                }
                            }
                        } else {
                            $task_valueNew = current($this->taskSetting);
                            $key           = $arrayKey[0];
                        }
                        $rest = [
                            'task_value' => $key,
                            'point'      => $task_valueNew,
                            'rewoard'    => $rewoard,
                            'timekey'    => array_search($key, $arrayKey) > 0 ? array_search($key, $arrayKey) : 0,
                            'arrayAll'   => $arrayAll,
                        ];
                    } else {
                        $rest = [
                            'task_value' => $this->taskSetting['task_value'],
                            'point'      => $this->taskSetting['point'],
                            'rewoard'    => $rewoard,
                            'timekey'    => 0,
                        ];
                    }
                    $this->userTask['detail'] = $rest;
                    array_unshift($timeArray, '');
                    $this->userTask['timearray']   = $timeArray;
                    $this->userTask['tips']        = "看完下个时段，最高得{$this->taskSetting[$arrayKey[$rest['timekey']]]}" . $rewoard;
                    $this->userTask['rewoardname'] = $rewoard;
                    $this->userTask['rewoadpoint'] = $this->taskSetting[$arrayKey[$rest['timekey']]];
                    break;
                case 'shareVideo' :
                    $task_type                       = 'shareVideo';
                    $getmultiple                     = $this->getmultiple($task['task_setting'], $user_id, $task_type);
                    $this->taskSetting['task_value'] = $getmultiple['task_value'];
                    $this->taskSetting['point']      = $getmultiple['point'];
                    $this->userTask['tips']          = "分享{$this->taskSetting['task_value']}次视频，可获得{$this->taskSetting['point']}" . $getmultiple['rewoard'];
                    $this->userTask['rewoardname']   = $getmultiple['rewoard'];
                    $this->userTask['rewoadpoint']   = $this->taskSetting['point'];
                    break;
                case 'thumbsVideo' :
                    $task_type                       = 'thumbsVideo';
                    $getmultiple                     = $this->getmultiple($task['task_setting'], $user_id, $task_type);
                    $this->taskSetting['task_value'] = $getmultiple['task_value'];
                    $this->taskSetting['point']      = $getmultiple['point'];
                    $this->userTask['tips']          = "点赞{$this->taskSetting['task_value']}次视频，可获得{$this->taskSetting['point']}" . $getmultiple['rewoard'];
                    $this->userTask['rewoardname']   = $getmultiple['rewoard'];
                    $this->userTask['rewoadpoint']   = $this->taskSetting['point'];
                    break;
                case 'commentVideo' :
                    $task_type                       = 'commentVideo';
                    $getmultiple                     = $this->getmultiple($task['task_setting'], $user_id, $task_type);
                    $this->taskSetting['task_value'] = $getmultiple['task_value'];
                    $this->taskSetting['point']      = $getmultiple['point'];
                    $this->userTask['tips']          = "评论{$this->taskSetting['task_value']}次视频，可获得{$this->taskSetting['point']}" . $getmultiple['rewoard'];
                    $this->userTask['rewoardname']   = $getmultiple['rewoard'];
                    $this->userTask['rewoadpoint']   = $this->taskSetting['point'];
                    break;
                case 'inviteFriends' :
                    $task_type                       = 'inviteFriends';
                    $getmultiple                     = $this->getmultiple($task['task_setting'], $user_id, $task_type);
                    $this->taskSetting['task_value'] = $getmultiple['task_value'];
                    $this->taskSetting['point']      = $getmultiple['point'];
                    $this->userTask['tips']          = "邀请{$this->taskSetting['task_value']}个好友，可获得{$this->taskSetting['point']}" . $getmultiple['rewoard'];
                    $this->userTask['rewoardname']   = $getmultiple['rewoard'];
                    $this->userTask['rewoadpoint']   = $this->taskSetting['point'];
                    break;
                case 'oneWithdrawal' :
                    $this->userTask['rewoardname']   = "1元";
                    $this->userTask['tips'] = "新人专属特权，立刻提现一元";
                    break;
                case 'dayRecharge' :
                    $task_type                       = 'dayRecharge';
                    $getmultiple                     = $this->getmultiple($task['task_setting'], $user_id, $task_type);
                    $this->taskSetting['task_value'] = $getmultiple['task_value'];
                    $this->taskSetting['point']      = $getmultiple['point'];
                    $this->userTask['rewoardname']   = $getmultiple['rewoard'];
                    $this->userTask['rewoadpoint']   = $this->taskSetting['point'];
                    $this->userTask['tips']          = "每日充值{$this->taskSetting['task_value']}次获取{$this->taskSetting['point']}" . $this->rewardName[$task['reward_type']];
                    break;
                case 'dayReward' :
                    $task_type                       = 'dayReward';
                    $getmultiple                     = $this->getmultiple($task['task_setting'], $user_id, $task_type);
                    $this->taskSetting['task_value'] = $getmultiple['task_value'];
                    $this->taskSetting['point']      = $getmultiple['point'];
                    $this->userTask['rewoardname']   = $getmultiple['rewoard'];
                    $this->userTask['rewoadpoint']   = $this->taskSetting['point'];
                    $this->userTask['tips']          = "每日打赏{$this->taskSetting['task_value']}次获取{$this->taskSetting['point']}" . $this->rewardName[$task['reward_type']];
                    break;
                case 'dayShareRoom' :
                    $task_type                       = 'dayShareRoom';
                    $getmultiple                     = $this->getmultiple($task['task_setting'], $user_id, $task_type);
                    $this->taskSetting['task_value'] = $getmultiple['task_value'];
                    $this->taskSetting['point']      = $getmultiple['point'];
                    $this->userTask['rewoardname']   = $getmultiple['rewoard'];
                    $this->userTask['rewoadpoint']   = $this->taskSetting['point'];
                    $this->userTask['tips']          = "每日分享{$this->taskSetting['task_value']}次获取{$this->taskSetting['point']}" . $this->rewardName[$task['reward_type']];
                    break;
                case 'commentDynamic' :
                    $task_type                       = 'commentDynamic';
                    $getmultiple                     = $this->getmultiple($task['task_setting'], $user_id, $task_type);
                    $this->taskSetting['task_value'] = $getmultiple['task_value'];
                    $this->taskSetting['point']      = $getmultiple['point'];
                    $this->userTask['rewoardname']   = $getmultiple['rewoard'];
                    $this->userTask['rewoadpoint']   = $this->taskSetting['point'];
                    $this->userTask['tips']          = "每日评论动态{$this->taskSetting['task_value']}次获取{$this->taskSetting['point']}" . $this->rewardName[$task['reward_type']];
                    break;
                case 'liveDynamic' :
                    $task_type                       = 'liveDynamic';
                    $getmultiple                     = $this->getmultiple($task['task_setting'], $user_id, $task_type);
                    $this->taskSetting['task_value'] = $getmultiple['task_value'];
                    $this->taskSetting['point']      = $getmultiple['point'];
                    $this->userTask['rewoardname']   = $getmultiple['rewoard'];
                    $this->userTask['rewoadpoint']   = $this->taskSetting['point'];
                    $this->userTask['tips']          = "每日点赞动态{$this->taskSetting['task_value']}次获取{$this->taskSetting['point']}" . $this->rewardName[$task['reward_type']];
                    break;
                case 'shareDynamic' :
                    $task_type                       = 'shareDynamic';
                    $getmultiple                     = $this->getmultiple($task['task_setting'], $user_id, $task_type);
                    $this->taskSetting['task_value'] = $getmultiple['task_value'];
                    $this->taskSetting['point']      = $getmultiple['point'];
                    $this->userTask['rewoardname']   = $getmultiple['rewoard'] ;
                    $this->userTask['rewoadpoint']   = $this->taskSetting['point'];
                    $this->userTask['tips']          = "每日分享动态{$this->taskSetting['task_value']}次可获得{$this->taskSetting['point']}" .$this->rewardName[$task['reward_type']];
                    break;
                default :
                    $this->userTask['rewoadpoint'] = $this->taskSetting['point'];
                    $this->userTask['tips']        = "完成任务，可获得{$this->taskSetting['point']}" . $this->rewardName[$task['reward_type']];
            }
            $user_taskList[$task['task_type']] = $this->userTask;
        }
        return $user_taskList;
    }

    public function task_init($task, $user_id)
    {
        $task_log_db = Db::name('user_task_log');
        $task_log_db->where(['user_id' => $user_id, 'task_id' => $task['id']]);
        if ($task['type'] == 1) $task_log_db->whereBetweenTime('create_time', date("Y-m-d"));
        $this->taskToady   = $task_log_db->find();
        $this->taskSetting = json_decode($task['task_setting'], true);
        $this->userTask    = ['task_id' => $task['id'], 'task_title' => $task['title'], 'task_type' => $task['task_type'], 'task_setting' => $this->taskSetting, 'reward_type' => $task['reward_type'],];
        if (!empty($this->taskToady)) {
            switch ($this->taskToady['status']) {
                case 1 :
                    $this->userTask['status']        = 1;
                    $this->userTask['status_reword'] = 0;
                    //      $this->userTask['status_txt'] = '领取';
                    //判断任务类型
                    if ($task['type'] == 0) {
                        if ($this->taskToady['task_value'] >= $this->taskSetting['task_value']) {
                            $this->userTask['status_txt'] = '领取';
                        } else {
                            $this->userTask['status_reword'] = 1;
                            $this->userTask['status_txt']    = '已完成';
                        }
                    } else {
                        if ($this->taskToady['task_value'] >= min(array_keys($this->taskSetting))) {
                            //根据不同的类型查询不同的表判定今日任务是否完成（建立一个公用函数）
                            $rest = todayFinish($task, $this->taskToady);
                            if ($rest == 1) {
                                $this->userTask['status_txt'] = '领取';
                            } else {
                                $this->userTask['status_reword'] = 1;
                                $this->userTask['status_txt']    = '继续完成';
                            }
                        }
                    }
                    break;
                case 2 :
                    $this->userTask['status'] = 2;
                    if ($task['type'] == 1 && $task['task_type'] != 'dailyLogin' && $task['task_type'] != 'dayRecharge' && $task['task_type'] != 'dayReward' && $task['task_type'] != 'dayShareRoom') {
                        if ($this->taskToady['point'] >= max($this->taskSetting)) {
                            $this->userTask['status_txt'] = '已完成';
                        } else {
                            $this->userTask['status_txt'] = '继续完成';
                        }
                    } else {
                        $this->userTask['status_txt'] = '已完成';
                    }
                    break;
                default :
                    $this->userTask['status']     = 0;
                    $this->userTask['status_txt'] = '未完成';
            }
        } else {
            $this->userTask['status']     = 0;
            $this->userTask['status_txt'] = '未完成';
        }
    }

    /*
     * 用户签到
     */
    public function addUserCheck($user_id, $task_id)
    {
        $task = Db::name('user_task')->where(['id' => $task_id, 'task_type' => 'dailyLogin', 'status' => 1])->field('id,task_setting')->find();
        if (empty($task)) {
            return $this->setError('签到已关闭');
        }
        $task_setting     = json_decode($task['task_setting'], true);
        $where            = [];
        $where['user_id'] = $user_id;
        $where['task_id'] = $task_id;
        $check_toady      = Db::name('user_task_log')->where($where)->whereBetweenTime('create_time', date("Y-m-d"))->find();
        if (!empty($check_toady)) {
            return $this->setError('今日已签到');
        }
        $check_yesterday = Db::name('user_task_log')->where($where)->whereBetweenTime('create_time', date("Y-m-d", strtotime("-1 day")))->value('task_value');
        if (empty($check_yesterday) || $check_yesterday > 6) {
            $record_value = 1;
        } else {
            $record_value = $check_yesterday + 1;
        }
        $data    = [
            'user_id'    => $user_id,
            'task_id'    => $task_id,
            'task_type'  => 'dailyLogin',
            'task_value' => $record_value,
            'point'      => $task_setting[($record_value - 1)],
            'status'     => 2
        ];
        $taskMod = new Task();
        $res     = $taskMod->subTask($data);
        return $data;
    }

    protected function getSevenDays($time = '', $format = 'm.d')
    {
        $time   = $time != '' ? $time : time();    //组合数据
        $date[] = date($format, $time);
        for ($i = 1; $i <= 6; $i++) {
            $date[$i] = date($format, strtotime('+' . $i . ' days', $time));
        }
        return $date;
    }

    /*
     * 获取用户今天任务完成状态
     * 用户中心入口用
     */
    public function getUserTaskStatus($user_id)
    {
        $task_list = Db::name('user_task')->where(['status' => 1])->order('stor desc')->cursor();
        foreach ($task_list as $task) {
            $task_log_db      = Db::name('user_task_log');
            $where            = [];
            $where['user_id'] = $user_id;
            $where['task_id'] = $task['id'];
            $task_log_db->where($where);
            if ($task['type'] == 1) {
                $task_log_db->whereBetweenTime('create_time', date("Y-m-d"));
            }
            $task_toady = $task_log_db->find();
            if (!empty($task_toady)) {
                if ($task_toady['status'] == 0) {
                    return "1";
                    break;
                }
            } else {
                return "1";
                break;
            }
        }
        return "0";
    }

    public function updateTaskSET($data)
    {
        return Db::name('user_task')->where(['task_type' => $data['task_type']])->update($data);
    }

    public function getTopic($condition, $field, $order, $limit)
    {
        $this->db = Db::name('user_task');
        $list     = $this->db->field($field)->where($condition)->order($order)->limit($limit)->select();
        return $list;
    }

    public function getQuery($condition, $field, $order)
    {
        $this->db = Db::name('user_task');
        $list     = $this->db->field($field)->where($condition)->order($order)->select();
        return $list;
    }

    public function countTotal($where)
    {
        $this->db = Db::name('user_task');
        $count    = $this->db->where($where)->count();
        return (int)$count;
    }

    public function find($where)
    {
        $this->db = Db::name('user_task');
        $info     = $this->db->where($where)->find();
        return $info;
    }

    public function pageQuery($page_index, $page_size, $condition, $order, $field)
    {
        $this->db = Db::name('user_task');
        $count    = $this->db->where($condition)->count();
        if ($page_size == 0) {
            $list       = $this->db->field($field)
                ->where($condition)
                ->order($order)
                ->select();
            $page_count = 1;
        } else {
            $start_row = $page_size * ($page_index - 1);
            $list      = $this->db->field($field)
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
            'data'        => $list,
            'total_count' => $count,
            'page_count'  => $page_count
        );
    }

    public function getTotal($get)
    {
        $this->db = Db::name('user_task');
        $this->setWhere($get);
        $count = $this->db->count();
        return (int)$count;
    }

    public function getList($get, $offset = 0, $length = 20)
    {
        $this->db = Db::name('user_task');
        $this->setWhere($get)->setOrder($get);
        $result = $this->db->limit($offset, $length)->select();
        foreach ($result as $k => $v) {
            $result[$k]['type']         = $v['type'] ? '循环任务' : '一次性任务';
            $result[$k]['mission_type'] = $v['mission_type'] ? '用户任务' : '视频任务';
        }
        return $result;
    }

    protected function setWhere($get)
    {
        $where  = array();
        $where1 = array();
        if ($get['uid'] != 0) {
            $where['uid'] = $get['uid'];
        }
        if ($get['mission_type'] != '') {
            $where['mission_type'] = $get['mission_type'];
        }
        if ($get['status'] != '') {
            $where['status'] = $get['status'];
        }
        if ($get['type'] != '') {
            $where['type'] = $get['type'];
        }
        if ($get['keyword'] != '') {
            $where1[] = ['title', 'like', '%' . $get['keyword'] . '%'];
        }
        $this->db->where($where)->where($where1);
        return $this;
    }

    protected function setOrder($get)
    {
        $order = array();
        if (empty($get['sort'])) {
            $order['create_time'] = 'DESC';
        } else {
            $order = $get;
        }
        $this->db->order($order);
        return $this;
    }

    public function changeStatus($ids, $status)
    {
        if (!in_array($status, [0, 1])) return false;
        return Db::name('user_task')->whereIn('id', $ids)->update(['status' => $status]);
    }

    public function editTask($data)
    {
        //相关数据处理
        $task_value = explode(',', trim($data['finish'], ','));
        $point      = explode(',', trim($data['reward'], ','));
        $instvalue  = [];
        if ($data['status'] == 1) {
            if (min(count(array_filter($task_value)), count(array_filter($point))) < 1) {
                return -1;
            }
        }
        if ($data['type']==0) {
            $instvalue = ['task_value' => $task_value[0], 'point' => $point[0]];
        } else {
            for ($i = 0; $i < min(count($task_value), count($point)); $i++) {
                $instvalue[$task_value[$i]] = $point[$i];
            }
        }
        $inst['title']        = $data['title'];
        $inst['create_time']  = time();
        $inst['task_setting'] = json_encode($instvalue);
        $inst['task_type']    = $data['task_type'];
        $inst['type']         = $data['type'];
        $inst['status']       = $data['status'];
        $inst['mission_type'] = $data['mission_type'];
        $inst['reward_type']  = $data['reward_type'];
        return Db::name('user_task')->where('id', $data['id'])->update($inst);
    }

    /**
     * 获取多重分段奖励的下一个值
     * @param $agentId
     * @return array
     */
    public function getmultiple($taskset, $user_id, $task_type)
    {
        $rest = Db::name('user_task')->where(['status' => 1, 'task_type' => $task_type])->find();
        switch ($rest['reward_type']) {
            case 2:
                $rewoard = APP_MILLET_NAME;
                break;
            case 3:
                $rewoard = APP_BEAN_NAME;
                break;
            case 4:
                $rewoard = APP_CASH_NAME;
                break;
            default:
                $rewoard = APP_REWARD_NAME;
        }
        $task_setting = json_decode($taskset, true);

        if ($rest['type'] == 1) {
            //这里要判断用户完成到哪里了，如果完成了前面的就显示后面的数据
            $finish_value = Db::name('user_task_log')->where(['task_type' => $task_type, 'user_id' => $user_id])->whereBetweenTime('create_time', date("Y-m-d"))->value('task_value');
            $arrayKey     = array_keys($task_setting);
            if ($finish_value) {
                sort($arrayKey);
                if ($arrayKey[0] == 'task_value' || $arrayKey[1] == 'task_value') {
                    $task_valueNew = $task_setting['point'];
                    $key           = $task_setting['task_value'];
                } else {
                    $task_valueNew = current($task_setting);
                    $key           = $arrayKey[0];
                    $finish_status = Db::name('user_task_log')->where(['task_type' => $task_type, 'user_id' => $user_id])->whereBetweenTime('create_time', date("Y-m-d"))->value('status');
                    if ($finish_status == 2) {
                        foreach ($arrayKey as $k => $v) {
                            if ($finish_value < $v) {
                                $task_valueNew = $task_setting[$v];
                                $key           = $v;
                                break;
                            }
                        }
                    } else {
                        foreach ($arrayKey as $k => $v) {
                            if ($finish_value <= $v) {
                                $task_valueNew = $task_setting[$v];
                                $key           = $v;
                                break;
                            }
                        }
                    }
                }
            } else {
                if ($arrayKey[0] == 'task_value' || $arrayKey[1] == 'task_value') {
                    $task_valueNew = $task_setting['point'];
                    $key           = $task_setting['task_value'];
                } else {
                    $task_valueNew = current($task_setting);
                    $key           = $arrayKey[0];
                }
            }
            $rest = [
                'task_value' => $key,
                'point'      => $task_valueNew,
                'rewoard'    => $rewoard,
            ];
        } else {
            $rest = [
                'task_value' => $task_setting['task_value'],
                'point'      => $task_setting['point'],
                'rewoard'    => $rewoard,
            ];
        }
        return $rest;
    }
}
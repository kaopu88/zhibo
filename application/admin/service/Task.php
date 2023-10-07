<?php


namespace app\admin\service;


use bxkj_module\service\Service;
use think\Db;

class Task extends Service
{
    protected $task_type = [
        'followFriends' => '关注好友',
        'postVideo' => '发布视频',
        'dailyLogin' => '每日签到',
        'watchVideo' => '观看视频',
        'shareVideo' => '分享视频',
        'inviteFriends' => '邀请好友',
        'commentVideo' => '视频评论',
        'thumbsVideo' => '视频点赞',
        'score' => '积分兑换',
        'dayRecharge' => '每日充值',
        'dayReward' => '每日打赏',
        'commentDynamic' => '评论动态',
        'liveDynamic' => '点赞动态',
        'shareDynamic' => '分享动态',
    ];

    //获取总记录数
    public function getTotal($get)
    {
        $this->db = Db::name('user_task_log');
        $this->db->alias('t')
            ->join('user u', 'u.user_id=t.user_id');
        $this->setWhere($get);
        return $this->db->count();
    }

    //获取列表
    public function getList($get, $offset, $length)
    {
        $this->db = Db::name('user_task_log');

        $this->db->alias('t')
            ->join('user u', 'u.user_id=t.user_id')
            ->field('t.*,u.nickname,u.phone, u.avatar');

        $this->setWhere($get)->setOrder($get);
        $result = $this->db->limit($offset, $length)->select();
        foreach ($result as &$item) {
            $item['type_txt'] = $this->task_type[$item['task_type']];
            switch ($item['status']) {
                case 1:
                    $item['status_txt'] = '待领取';
                    break;
                case 2:
                    $item['status_txt'] = '已完成';
                    break;
                default:
                    $item['status_txt'] = '未完成';
                    break;
            }
        }
        return $result;
    }

    //设置查询条件
    private function setWhere($get)
    {
        $where = array();

        $this->db->setKeywords(trim($get['keyword']), 'phone u.phone', 'number t.user_id', 'number u.phone,u.nickname');

        if ($get['start_time'] != '' && $get['end_time'] != '') {
            $this->db->whereTime('t.create_time', 'between', [$get['start_time'] . ' 0:0:0', $get['end_time'] . ' 23:59:59']);
        }

        $this->db->where($where);
        return $this;
    }

    protected function setOrder($get)
    {
        $order = array();
        if (empty($get['sort'])) {
            $order['create_time'] = 'DESC';
        }
        $this->db->order($order);
        return $this;
    }
}
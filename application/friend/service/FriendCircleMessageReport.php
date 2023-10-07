<?php
/**
 * Created by PhpStorm.
 * User: “崔鹏
 * Date: 2020/06/24
 * Time: 下午 3:08
 */

namespace app\friend\service;

use app\api\service\Follow as FollowModel;
use bxkj_common\RabbitMqChannel;
use bxkj_common\RedisClient;
use bxkj_module\service\Service;
use think\Db;

class FriendCircleMessageReport extends Service
{
    public function add($data1)
    {
        $data['ctime'] = time();
        unset($data['access_token']);
        $data = [
            'ctime'         => time(),
            'uid'           => $data1['uid'],
            'report_msg_id' => $data1['report_msg_id'] ? $data1['report_msg_id'] : 0,
            'report_img'    => $data1['report_img'] ? $data1['report_img'] : '',
            'report_type'   => $data1['report_type'] ? $data1['report_type'] : '',
            'extendids'     => $data1['report_uid'] ? $data1['report_uid'] : '',
            'report_msg'    => $data1['report_msg'] ? $data1['report_msg'] : '',
            'type'          => $data1['type'] ? $data1['type'] : 0,
            'handle_desc'   => isset($data1['handle_desc']) ? $data1['handle_desc'] : 0,
            'handle_time'   => 0,
        ];
        $id   = Db::name('friend_circle_message_report')->insertGetId($data);
        return $id;
    }

    public function checkAlready($data)
    {
        return Db::name('friend_circle_message_report')->where(['report_msg_id' => $data['report_msg_id'], 'uid' => $data['uid'], 'type' => $data['type']])->count();
    }

    public function getTotal($get)
    {
        $this->db = Db::name('friend_circle_message_report');
        $this->setWhere($get);
        $count = $this->db->count();
        return (int)$count;
    }

    public function getList($get, $offset = 0, $length = 20)
    {
        $this->db = Db::name('friend_circle_message_report');
        $this->setWhere($get)->setOrder($get);
        $result = $this->db->limit($offset, $length)->select();
        foreach ($result as $k => $v) {
            $result[$k]['user'] = userMsg($v['uid'], 'user_id,nickname,avatar,phone,level,remark_name');
        }
        return $result;
    }

    protected function setWhere($get)
    {
        $where  = array();
        $where1 = array();
        if ($get['type'] != '') {
            $where2[] = ['type', 'like', '%' . $get['type'] . '%'];
        }
        if ($get['audit_status'] != '') {
            $where['audit_status'] = $get['audit_status'];
        }
        if ($get['report_msg_id'] != '') {
            $where['report_msg_id'] = $get['report_msg_id'];
        }
        if ($get['keyword'] != '') {
            $where1[] = ['report_msg', 'like', '%' . $get['keyword'] . '%'];
        }
        $this->db->where($where)->where($where1)->where($where2);
        return $this;
    }

    protected function setOrder($get)
    {
        $order = array();
        if (empty($get['sort'])) {
            $order['ctime'] = 'DESC';
        } else {
            $order = $get;
        }
        $this->db->order($order);
        return $this;
    }

    public function changeStatus($ids, $status)
    {
        if (!in_array($status, [0, 1])) return false;
        Db::name('friend_circle_message_report')->whereIn('id', $ids)->update(['status' => $status]);
        $redis = RedisClient::getInstance();
        $rest  = Db::name('friend_circle_message_report')->find(['id' => $ids[0]]);
        $redis->set("bx_friend_msg:" . $ids[0], json_encode($rest));
        return Db::name('friend_circle_timeline')->whereIn('fcmid', $ids)->update(['status' => $status]);
    }

    public function getQuery($condition, $field, $order)
    {
        $this->db = Db::name('friend_circle_message_report');
        $list     = $this->db->field($field)->where($condition)->order($order)->select();
        return $list;
    }

    public function del($ids)
    {
        return Db::name('friend_circle_message_report')->whereIn('id', $ids)->delete();
    }

    public function backstageedit($dataa)
    {
        $data['report_msg']  = $dataa['report_msg'];
        $data['ctime']       = time();
        $data['report_img']  = $dataa['report_img'];
        $data['type']        = $dataa['type'];
        $data['report_type'] = $dataa['report_type'];
        return Db::name('friend_circle_message_report')->where(['id' => $dataa['id']])->update($data);
    }

    public function handler_report($inputData, $aid = null)
    {
        $status = $inputData['audit_status'];
        $where  = [['id', '=', $inputData['id']]];
        if (isset($aid)) $where[] = ['aid', '=', $aid];
        Service::startTrans();
        $order = Db::name('friend_circle_message_report')->where($where)->find();
        // 2:动态3：圈子6：表白7：评论8：留言',
        if($status==2){
            $updatestatus = 1;
        }else{
            $updatestatus  = 0;
        }
        switch ($order['report_type']) {
            case 2:
                Db::name('friend_circle_message')->where(['id' => $order['report_msg_id']])->update(['status' => $updatestatus]);
                Db::name('friend_circle_timeline')->where(['fcmid' => $order['report_msg_id']])->update(['status' => $updatestatus]);
                break;
            case 3:
                Db::name('friend_circle_message')->where(['id' => $order['report_msg_id']])->update(['status' => $updatestatus]);
                Db::name('friend_circle_timeline')->where(['fcmid' => $order['report_msg_id']])->update(['status' => $updatestatus]);
                break;
            case 6:
                Db::name('friend_circle_message')->where(['id' => $order['report_msg_id']])->update(['status' => $updatestatus]);
                Db::name('friend_circle_timeline')->where(['fcmid' => $order['report_msg_id']])->update(['status' => $updatestatus]);
                break;
            case 7:
                Db::name('friend_circle_comment')->where(['id' => $order['report_msg_id']])->update(['status' => $updatestatus]);
                break;
            case 8:
                Db::name('friend_circle_comment_evaluate')->where(['id' => $order['report_msg_id']])->update(['status' => $updatestatus]);
                break;
        }
        if (empty($order)) return $this->setError('申请记录不存在');
//        if ($order['audit_status'] != '0') return $this->setError('审核状态不正确');
        if (!in_array($status, ['1', '11', '2'])) return $this->setError('处理状态不正确');
        if ($status == '2' && empty($inputData['handle_desc'])) return $this->setError('请填写备注信息');
        $update                = ['handle_time' => time(), 'audit_status' => $status == 2 ? 2 : 1];
        $update['handle_desc'] = $inputData['handle_desc'] ? $inputData['handle_desc'] : '';
        $num = Db::name('friend_circle_message_report')->where('id', $order['id'])->update($update);
        if (!$num) return $this->setError('处理失败');
        Service::commit();
        return array_merge($order, $update);
    }
}
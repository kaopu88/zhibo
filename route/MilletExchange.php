<?php
/**
 * Created by PhpStorm.
 * User: zack
 * qq: 840855344
 * phone：18156825246
 */

namespace bxkj_module\service;

use bxkj_common\CoreSdk;
use bxkj_common\DateTools;
use think\Db;

class MilletExchange extends Service
{
    public function getList($get = array(), $offset = 0, $length = 10)
    {
        $this->db = Db::name('millet_exchange');
        $this->setWhere($get)->setOrder($get);
        $list = $this->db->field('cash.id,cash.cash_no,cash_rate,cash.millet,cash.status,cash.aid,cash.create_time,agent_id,handler_time')->limit($offset, $length)->select();
        $cashAgentIds= $this->getIdsByList($list, 'agent_id');
        $cashAgent = [];
        if (!empty($cashAgentIds)) {
            $cashAgent = Db::name('agent')->field('id, cash_type')->where(array('id' => $cashAgentIds))->limit(count($cashAgentIds))->select();
        }
        foreach ($list as &$item) {
            $item['descr'] = "【{$item['cash_no']}】 转换 {$item['millet']}".APP_MILLET_NAME;
            $item['bean_num'] =round( (1 +$item['cash_rate'] / 100) *  $item['millet'], 2);
            $item['create_time'] = date('Y-m-d H:i:s', $item['create_time']);
            $item['anchor'] = Db::name('anchor')->alias('anchor')
                ->field('anchor.user_id,agent.name agent_name,agent.logo agent_logo,agent.id agent_id')
                ->where(['anchor.user_id' => $item['user_id']])
                ->join('__AGENT__ agent', 'agent.id=anchor.agent_id')->find();
            $item['handler_time'] = isset($item['handler_time']) ? date('Y-m-d H:i:s', $item['handler_time']) : '';
        }
        return $list;
    }

    public function add($params)
    {
        if (empty($params['millet'])) return $this->setError('金币数量错误');
        $data['user_id'] = $params['user_id'];
        $data['cash_no'] = $params['cash_no'];
        $data['millet'] = $params['millet'];
        $data['cash_rate'] = $params['cash_rate'];
        $data['status'] = 'wait';
        $data['create_time'] = time();
        $this->supplementaryTime($data);
        $id = Db::name('millet_exchange')->insertGetId($data);
        if (!$id) {
            return $this->setError('提现失败[01]');
        }

        return $id;
    }

    public function update($post)
    {
        //var_dump($post);die;
        if (empty($post['id']))return ['code' => 102, 'msg' => '请选择记录'];
        $cashRes = Db::name('millet_exchange')->where('id', $post['id'])->find();
        if (empty($cashRes)) ['code' => 103, 'msg' => '记录不存在'];
        if ($post['status'] == 1) {
            $status = 'success';
        } elseif ($post['status'] == 0) {
            $status = 'failed';
        } else {
            return ['code' => 101, 'msg' => '操作不当'];
        }
        $updataData = array_merge(['status' => $status, 'handler_time' => time(), 'admin_remark' => $post['describe']]);

        $num = Db::name('millet_exchange')->where(['id' => $post['id']])->update($updataData);
        if (!$num) return ['code' => 102, 'msg' => '操作失败'];

        if ($status == 'failed') {
            $userService = new User();
            $user = $userService->getBasicInfo($cashRes['user_id']);
            $fre_millet = $user['fre_millet'];
            $total = $cashRes['millet'];
            $millet = $user['millet'] + $total;
            $total_millet = $total + $user['total_millet'];
            $update['millet'] = $millet;
            $update['fre_millet'] = $fre_millet;
            $update['total_millet'] = $total_millet;
            $num = Db::name('user')->where(array('user_id' => $user['user_id']))->update($update);
            $userService->updateRedis($user['user_id'], $update);
        } elseif($status == 'success') {
            $coreSdk = new CoreSdk();
            //TODO
            $incRes = $coreSdk->incBean(array(
                'user_id' => $cashRes['user_id'],
                'total' => round( (1 +$cashRes['cash_rate'] / 100) *  $cashRes['millet'], 2),
                'trade_type' => 'exchange',
                'trade_no' =>  get_order_no('exchange'),
                'client_ip' => get_client_ip(),
            ));
        } else {
            return ['code' => 102, 'msg' => '操作失败'];
        }

        return ['code' => 200];
    }

    public function getSummary($get)
    {
        if (!empty($get['start_time']) && !empty($get['end_time']))
        {
            $start_time = strtotime($get['start_time']);
            $end_time = strtotime( $get['end_time']);
            $where[] = ['cash.create_time', 'between', [$start_time, $end_time]];
        }

        $wait = Db::name('millet_exchange')->alias('cash')->where($where)->where(['cash.status' => 'wait'])->sum('cash.millet');
        $success = Db::name('millet_exchange')->alias('cash')->where($where)->where(['cash.status' => 'success'])->sum('cash.millet');
        $failed = Db::name('millet_exchange')->alias('cash')->where($where)->where(['cash.status' => 'failed'])->sum('cash.millet');
        $result['summary'] = ['wait' => $wait, 'success' => $success, 'failed' => $failed];
        return $result;
    }

    public function getTotal($get){
        $this->db = Db::name('millet_exchange');
        $this->setWhere($get);
        return $this->db->count();
    }

    protected function setWhere($get)
    {
        if (!empty($get['user_id'])) {
            $where = array('cash.user_id' => $get['user_id']);
            $this->db->where($where);
        }
        if (!empty($get['status'])) {
            $where = array('cash.status' => $get['status']);
            $this->db->where($where);
        }
        if (!empty($get['start_time']) && !empty($get['end_time']))
        {
            $start_time = strtotime($get['start_time']);
            $end_time = strtotime( $get['end_time']);
            $where1[] = ['cash.create_time', 'between', [$start_time, $end_time]];
            $this->db->where($where1);
        }

        $this->db->alias('cash');
        $this->db->join('__USER__ user', 'cash.user_id=user.user_id', 'LEFT');
        $this->db->field('user.user_id,user.nickname,user.avatar,user.remark_name,user.level,user.phone');

        return $this;
    }

    protected function setOrder($get)
    {
        if (empty($get['sort'])) {
            $this->db->order('cash.create_time desc');
        }
        return $this;
    }

    protected function supplementaryTime(&$data)
    {
        if (empty($data)) return;
        $now = isset($time) ? $time : time();
        $data['year'] = date('Y', $now);
        $data['month'] = date('Ym', $now);
        $data['day'] = date('Ymd', $now);
        $data['fnum'] = DateTools::getFortNum($now);
        $data['week'] = DateTools::getWeekNum($now);
        $relation = Db::name('promotion_relation')->where(['user_id' => $data['user_id']])->find();
        if (!empty($relation)) {
            $data['agent_id'] = $relation['agent_id'];
            $data['promoter_uid'] = $relation['promoter_uid'];
        }
    }
}
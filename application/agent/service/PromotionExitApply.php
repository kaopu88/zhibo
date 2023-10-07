<?php
/**
 * Created by PhpStorm.
 * User: zack
 * qq: 840855344
 * phone：18156825246
 * 本产品由秉信科技开发
 */

namespace app\agent\service;

use bxkj_module\service\Service;
use think\Db;
use think\Exception;

class PromotionExitApply extends Service
{
    public function getTotal($get)
    {
        $this->db = Db::name('promotion_exit_apply');
        $this->setWhere($get);
        $count = $this->db->count();
        return (int)$count;
    }

    public function getList($get, $offset = 0, $length = 20)
    {
        $this->db = Db::name('promotion_exit_apply');
        $this->setWhere($get)->setOrder($get);
        $result = $this->db->limit($offset, $length)->select();
        $user = new \bxkj_module\service\User();
        foreach ($result as &$item) {
            $user_info = $user->getUser($item['user_id']);
            $item['nickname'] = $user_info['nickname'];
            $item['phone'] = $user_info['phone'];
            $item['user_verified'] = $user_info['verified'];
            $item['avatar'] = img_url($user_info['avatar'],'','avatar');
        }
        return $result ?: [];
    }

    protected function setWhere($get)
    {
        $where = [['agent_id', '=', AGENT_ID]];
        if (trim($get['user_id']) != '') {
            $where[] = ['user_id', '=', trim($get['user_id'])];
        }
        $this->db->where($where);
        return $this;
    }

    protected function setOrder($get)
    {
        $order = array();
        $order['create_time'] = 'DESC';
        $this->db->order($order);
        return $this;
    }

    public function approved($id, $status, $reason='', $is_transfer=1)
    {
        Service::startTrans();
        try {
            $apply = Db::name('promotion_exit_apply')->where(['id'=>$id, 'agent_id' => AGENT_ID])->find();
            if( empty($apply) || $apply['status'] > 0 ){
                return $this->setError('错误的信息');
            }

            if( $status == 2 ) {
                if( !empty($reason) ) $data['reason'] = $reason;
            }

            if ($status == 1) {
                $proRel = new \bxkj_module\service\PromotionRelation();
                $total = $proRel->unbindWithAgent([$apply['user_id']], $apply['agent_id']);
                if (!$total) $this->error('取消绑定失败');
            }

            $data = [
                'status' => $status,
                'review_time' => time()
            ];
            Db::name('promotion_exit_apply')->where(['id'=>$id])->update($data);
        } catch (Exception $e) {
            Service::rollback();
            return $this->setError($e->getMessage());
        }
        Service::commit();
        return true;
    }
}
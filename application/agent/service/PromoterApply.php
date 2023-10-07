<?php


namespace app\agent\service;

use bxkj_module\service\Service;
use think\Db;
use think\facade\Log;

class PromoterApply extends Service
{
    public function getTotal($get)
    {
        $this->db = Db::name('promotion_relation_apply');
        $this->setWhere($get);
        $count = $this->db->count();
        return (int)$count;
    }

    public function getList($get, $offset = 0, $length = 20)
    {
        $this->db = Db::name('promotion_relation_apply');
        $this->setWhere($get)->setOrder($get);
        $result = $this->db->limit($offset, $length)->select();
        $user = new \bxkj_module\service\User();
        foreach ($result as &$item) {
            $user_info = $user->getUser($item['user_id']);
            $promoter_info = $user->getUser($item['promoter_uid']);
            $item['nickname'] = $user_info['nickname'];
            $item['phone'] = $user_info['phone'];
            $item['user_verified'] = $user_info['verified'];
            $item['avatar'] = img_url($user_info['avatar'],'','avatar');

            $item['promoter_nickname'] = $promoter_info['nickname'];
            $item['promoter_avatar'] = img_url($promoter_info['avatar'],'','avatar');

            if( !empty($item['pics']) ){
                $item['pic_list'] = explode(',', $item['pics']);
            }
        }
        return $result;
    }

    protected function setOrder($get)
    {
        $order = array();
        $order['create_time'] = 'DESC';
        $this->db->order($order);
        return $this;
    }

    protected function setWhere($get)
    {
        $where = [['agent_id', '=', AGENT_ID]];
        if (trim($get['user_id']) != '') {
            $where[] = ['user_id', '=', trim($get['user_id'])];
        }
        if (trim($get['promoter_uid']) != '') {
            $where[] = ['promoter_uid', '=', trim($get['promoter_uid'])];
        }
        $this->db->where($where);
        return $this;
    }

    /**
     *  审核推广
     *
     * @param $id
     * @param $status
     * @param string $reason
     * @param int $is_transfer
     * @return bool
     */
    public function approved($id, $status, $reason='', $is_transfer=1)
    {
        $apply = Db::name('promotion_relation_apply')->where(['id'=>$id])->find();
        if( empty($apply) || $apply['status'] > 0 ){
            return $this->setError('错误的信息');
        }
        $user = new \bxkj_module\service\User();
        if( $status == 1 ) {
            $user_info = $user->getUser($apply['user_id']);
            //if ($user_info['verified'] != '1') return $this->setError('用户实名还未通过');
        }

        $where = [
            'user_id' => $apply['user_id'],
            'agent_id' => $apply['agent_id']
        ];

        $relation = Db::name('promotion_relation')->where($where)->find();

        if( !empty($relation) && $relation['promoter_uid'] > 0 && $relation['agent_id'] != $apply['agent_id']){
            $protmoerName = config('app.agent_setting.promoter_name');
            $status = 2;
            $reason = '该用户推广经'. $protmoerName .'不属于本公会下， 请联系平台重新绑定'.$protmoerName;
        }

        $data = [
            'status' => $status,
            'review_time' => time()
        ];

        if($status == 1)
        {
            $userTransfer = new \bxkj_module\service\UserTransfer();
            $userTransfer->setAdmin('agent', AID);
            $userTransfer->setTargetPromoter($apply['promoter_uid']);
            $userTransfer->setTargetAgent(AGENT_ID);
            $userTransfer->setAsync(false); //设置异步模式
            $userTransfer->setFromUsers($apply['user_id']);
            $is_transfer && $userTransfer->setTransfer($is_transfer);
            $reslut = $userTransfer->transfer();
            if (!$reslut) return $this->setError($userTransfer->getError() ? $userTransfer->getError() : '审核失败');

            if ($apply['is_anchor'] == 1 && $apply['is_from'] == 0) {
                //申请主播的实名操作
                $res = $this->anchorAgentHandler($apply['user_id']);
                if ($res['code'] != 200) return $this->setError($res['msg']);
            }

            if ($apply['is_from'] == 1) {
                Db::name('anchor')->where(['user_id' => $apply['user_id']])->update(['agent_id' => AID]);
            }
        }
        else {
            if( !empty($reason) ) $data['reason'] = $reason;
        }

        Db::name('promotion_relation_apply')->where(['id'=>$id])->update($data);

        return true;
    }

    public function anchorAgentHandler($userId = '')
    {
        if (empty($userId)) return ['code' => 101, 'msg' => '用户ID不存在'];
        $live_setting = config('app.live_setting.user_live');
        $anchorApply = Db::name('anchor_apply')->where(['user_id' => $userId, 'status' => 4])->find();
        if (empty($anchorApply)) return ['code' => 102, 'msg' => '主播记录错误'];
        if ($live_setting['verify']) {
            $data['status'] = 3;
        } else {
            if ($anchorApply['pay_status'] == 0) {
                $anchorService = new \app\admin\service\Anchor();
                $res = $anchorService->create([
                    'agent_id' => $anchorApply['agent_id'],
                    'user_id' => $anchorApply['user_id'],
                    'force' => 0,
                    'admin' => [
                        'type' => 'erp',
                        'id' => AID
                    ]], 1);
                if (!$res) return ['code' => 103, 'msg' => $anchorService->getError()];
                $data['status'] = 2;
            } else {
                $data['status'] = 6;
            }
        }
        Db::name('anchor_apply')->where(['id' => $anchorApply['id']])->update($data);
        return ['code' => 200];
    }
}
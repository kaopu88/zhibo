<?php
namespace bxkj_module\service;

use think\Db;

class Agent extends Service
{
    public function parseExpire(&$agent)
    {
        $now = time();
        if ($agent['expire_time'] <= ($now + (7 * 24 * 3600)) && $agent['expire_time'] > $now) {
            $agent['expire_status'] = '1';
        } else if ($agent['expire_time'] <= $now) {
            $agent['expire_status'] = '2';
        } else {
            $agent['expire_status'] = '0';
        }
    }

    public function validateMaxNum($value, $rule, $data = null, $more = null)
    {
        if (!validate_regex($value, 'integer')) return false;
        $map = [
            'max_sec_num' => 'add_sec',
            'max_promoter_num' => 'add_promoter',
            'max_anchor_num' => 'add_anchor'
        ];
        $field = $more['fields'];
        if ($data[$map[$field]] == '1' && $value <= 0) return false;
        return true;
    }

    public function getAgentsByIds($agentIds)
    {
        if (empty($agentIds)) return [];
        $result = Db::name('agent')->whereIn('id', $agentIds)->field('id,name,logo,level,contact_phone,add_sec')->limit(count($agentIds))->select();
        return $result ? $result : [];
    }

    public function getAgentById($agentId)
    {
        if (empty($agentId)) return [];
        $agents = $this->getAgentsByIds([$agentId]);
        return $agents ? $agents[0] : [];
    }

    public static function agentWhere(&$where, $get, $prefix = '')
    {
        $agentId = ($get['agent'] && is_array($get['agent'])) ? $get['agent']['id'] : $get['agent_id'];
        if (!empty($agentId)) {
            $where[] = ["{$prefix}agent_id", '=', $agentId];
        }
    }

    public static function getAgentIds($agentId)
    {
        $array = [$agentId];
        $agentIds = Db::name('agent')->field('id')->where([['pid', '=', $agentId]])->select();
        foreach ($agentIds as $item)
        {
            $array[] = $item['id'];
        }
        return $array;
    }

    public function getInfo($id, $pid = null)
    {
        $where = ['delete_time' => null, 'id' => $id];
        if (isset($pid)) $where['pid'] = $pid;
        $agent = Db::name('agent')->where($where)->find();
        if ($agent) {
            $agent['area_id'] = implode('-', [$agent['province_id'], $agent['city_id'], $agent['district_id']]);
        }
        return $agent;
    }

    public function getAllList($page, $pageSize, $get = '')
    {
        $db = Db::name('agent');
        $this->setAllWhere($db);
        $where = '';
        if (!empty($get['keyword'])) {
            $where .= ' (name like "%'.trim($get['keyword']).'%" or id = '.intval(trim($get['keyword'])).')';
        }

        if (!empty($get['user_id'])) {
            $resPromotionVerify = Db::name('promotion_relation_apply')->where(['user_id' => $get['user_id'], 'status' => 0])->find();
            $agentId = $resPromotionVerify['agent_id'] ?: 0;
            $promotionRelation = Db::name('promotion_relation')->where(['user_id' => $get['user_id']])->find();
            $enteragentId = !empty($promotionRelation['agent_id']) ? $promotionRelation['agent_id'] :0;
        }

        $result = $db->page($page,$pageSize)->where($where)->where('root_id >0')->select();
        $userService = new User();
        if (!empty($result)) {
            foreach ($result as $key => &$value) {
                if (!empty($value['uid'])) {
                    $info = $userService->getUser($value['uid']);
                } else {
                    $info = $this->getOneDetail($value['id']);
                }

                $value['agent_info'] = $info;
                $value['apply_status'] = 0;
                if (!empty($agentId) && ($agentId == $value['id'])) $value['apply_status'] = 1;
                if (!empty($enteragentId) && ($enteragentId == $value['id'])) $value['apply_status'] = 2;
            }
        }
        return $result;
    }

    protected function setAllWhere(&$db)
    {
        $where = ['status' => 1];
        $whereTime[] = ['expire_time', '>', time()];
        $db->where($where)->where($whereTime);
        return $this;
    }

    public function getOneDetail($id)
    {
        if (empty($id)) return [];
        $info = Db::name('agent_admin')->field('id, username, realname, phone')->where(array('agent_id' => $id, 'is_root' => 1))->find();
        return $info;
    }

    public function applyAgentAnchor($userId, $agentId)
    {
        $resPromotionVerify = Db::name('promotion_relation_apply')->where(['user_id' => $userId])->order('id desc')->find();

        if (empty($resPromotionVerify)) {
            $promotionRelationApplyData = ['user_id' => $userId, 'status' => 0, 'agent_id' => $agentId, 'create_time' => time(), 'is_anchor' => 1, 'is_from' => 1];
            $relationAnchor = Db::name('promotion_relation_apply')->insert($promotionRelationApplyData);
            if (!$relationAnchor) {
                return ['code' => 102, 'msg' => '申请失败'];
            }
            return ['code' => 200];
        }

        if ($resPromotionVerify['status'] == 2) {
            $relationRes = Db::name('promotion_relation_apply')->where(['user_id' => $userId])->update(['status' => 0, 'agent_id' => $agentId, 'create_time' => time()]);
            if (!$relationRes) {
                return ['code' => 102, 'msg' => '申请失败'];
            }
            return ['code' => 200];
        }

        if ($resPromotionVerify['status'] == 0) return ['code' => 101, 'msg' => '你有'. config('app.agent_setting.agent_name') .'在申请中'];
        return ['code' => 103, 'msg' => '未知错误'];
    }

    public function exitAgent($userId, $agentId)
    {
        $res = Db::name('promotion_exit_apply')->where(['user_id' => $userId, 'agent_id' => $agentId])->order('id desc')->find();
        if (empty($res)) {
            $exitData = ['user_id' => $userId, 'status' => 0, 'agent_id' => $agentId, 'create_time' => time(), 'is_anchor' => 1];
            $resexit = Db::name('promotion_exit_apply')->insert($exitData);
            if (!$resexit) {
                return ['code' => 102, 'msg' => '退出申请失败'];
            }
            return ['code' => 200];
        }
        if ($res['status'] == 2 || $res['status'] == 1) {
            $relationRes = Db::name('promotion_exit_apply')->where(['user_id' => $userId, 'agent_id' => $agentId])->update(['status' => 0, 'agent_id' => $agentId, 'create_time' => time()]);
            if (!$relationRes) {
                return ['code' => 102, 'msg' => '申请失败'];
            }
            return ['code' => 200];
        }
        if ($res['status'] == 0) return ['code' => 101, 'msg' => '你有退出'. config('app.agent_setting.agent_name') .'在申请中'];
        return ['code' => 103, 'msg' => '未知错误'];
    }


    public function getAgentApply($where){
        return Db::name('promotion_relation_apply')->where($where)->find();
    }
}
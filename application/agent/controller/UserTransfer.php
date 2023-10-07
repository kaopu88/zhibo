<?php

namespace app\agent\controller;

use bxkj_common\HttpClient;
use think\Db;
use think\facade\Request;

class UserTransfer extends Controller
{
    protected $maps = [
        'user' => '用户',
        'anchor' => '主播',
        'promoter' => '经纪人'
    ];

    public function select_agents()
    {
        $primaryKey = input('primary_key', 'id');
        $ids = get_request_ids($primaryKey);
        $transferType = input('transfer_type', 'user');
        $transferWay = input('transfer_way', 'promoter');
        $rel = input('rel', 'user');
        if (!isset($this->maps[$transferType])) $this->error('移交类型不正确');
        if (empty($ids)) $this->error('请选择用户');
        if (!in_array($rel, ['promoter', 'agent', 'user'])) $this->error('关联关系不正确');
        $maxNum = $rel == 'user' ? 30 : 1;
        if (count($ids) > $maxNum) $this->error("一次最多只能处理{$maxNum}条记录");
        $condition = [];
        $conditionQuery = input('condition');
        if (!empty($conditionQuery)) parse_str($conditionQuery, $condition);
        $data = ['ids' => $ids, 'transfer_type' => $transferType, 'rel' => $rel, 'primary_key' => $primaryKey, 'condition' => $condition];
        session('transfer_data', $data);
        $get = input();
        $agentService = new \app\agent\service\Agent();
        $total = $agentService->getTotal($get);
        $page = $this->pageshow($total);
        $list = $agentService->getList($get, $page->firstRow, $page->listRows);
        //判断是否开通二级代理权限
        $add_sec = Db::name('agent')->where('id', AGENT_ID)->value('add_sec');
        if (!$add_sec || $transferWay=='promoter') {
            $url = url('user_transfer/select_promoter', ['agent_level' => 0, 'agent_id' => AGENT_ID]);
            $this->redirect($url);
        }
        $this->assign('_list', $list);
        $this->assignRelInfo($data['rel'], $ids, $data['transfer_type']);
        return $this->fetch();
    }

    public function select_promoter()
    {
        $get = input();
        $data = session('transfer_data');
        if (empty($data)) $this->error('请重新选择分配'.config('app.agent_setting.agent_name'));
        if (empty($data['ids'])) $this->error('请选择用户');
        if (empty($get['agent_id'])) $this->error('请选择'.config('app.agent_setting.agent_name'));
        $data['agent_id'] = $get['agent_id'];
        session('transfer_data', $data);
        $condition = $data['condition'];
        if ($data['transfer_type'] != 'user' || ($data['transfer_type'] == 'user' && $condition['sync'] == '1')) {
            $this->redirect('confirm');
        }
        $agentService = new \app\agent\service\Promoter();
        $total = $agentService->getIndexTotal($get);
        $page = $this->pageshow($total);
        $list = $agentService->getIndex($get, $page->firstRow, $page->listRows);
        $this->assign('_list', $list);
        $this->assignRelInfo($data['rel'], $data['ids'], $data['transfer_type']);
        return $this->fetch();
    }

    public function confirm()
    {
        $data = session('transfer_data');
        if (empty($data)) $this->error('请重新选择分配'.config('app.agent_setting.agent_name'));
        $ids = $data['ids'];
        if (empty($ids)) $this->error('请选择用户');
        if (empty($data['agent_id'])) $this->error('请选择'.config('app.agent_setting.agent_name'));
        $transferType = $data['transfer_type'];
        $rel = $data['rel'];
        $promoterUid = input('user_id', 0);
        $total = 0;
        $sync = false;
        switch ($transferType) {
            //转移用户
            case 'user':
                $userTransfer = new \bxkj_module\service\UserTransfer();
                $userTransfer->setOwnAgent(AGENT_ID);
                $userTransfer->setAdmin('agent', AID);
                $userTransfer->setTargetPromoter($promoterUid);
                $userTransfer->setTargetAgent($data['agent_id']);
                $userTransfer->setAsync($sync);
                if ($rel == 'promoter') {
                    $userTransfer->setFromPromoter($ids[0]);
                } else if ($rel == 'agent') {
                    $userTransfer->setFromAgent($ids[0]);
                } else {
                    $userTransfer->setFromUsers($ids);
                }
                $res = $userTransfer->transfer();
                if (!$res) $this->error($userTransfer->getError());
                break;
            case 'promoter':
                $this->error('暂不支持');
                break;
            case 'anchor':
                $this->error('暂不支持');
                break;
        }

        if (Request::isAjax()) {
            return json_success(['total' => $total, 'sync' => $sync], $sync ? "移交成功,共计处理{$total}条记录" : '转移任务提交成功，请稍后');
        }
        $this->assign('total', $total);
        $this->assign('sync', $sync ? '1' : '0');
        return $this->fetch('confirm_success');
    }

    private function assignRelInfo($rel, $ids, $transferType, $selectAgentId = '')
    {
        if ($rel == 'agent') {
            $id = $ids[0];
            $info = Db::name('agent')->where(['id' => $id])->find();
            if ($transferType == 'user') {
                //统计config('app.agent_setting.agent_name')名下有多少个用户
                $info['rel_num'] = Db::name('promotion_relation')->where(['agent_id' => $info['id']])->count();
            } else if ($transferType == 'anchor') {
                //统计config('app.agent_setting.agent_name')名下有多少个主播
                $info['rel_num'] = Db::name('anchor')->where(['agent_id' => $info['id']])->count();
            } else if ($transferType == 'promoter') {
                //统计config('app.agent_setting.agent_name')名下有多少个config('app.agent_setting.promoter_name')
                $info['rel_num'] = Db::name('promoter')->where(['agent_id' => $info['id']])->count();
            }
            $this->assign('_info', $info);
        } else if ($rel == 'promoter') {
            $id = $ids[0];
            $info = Db::name('user')->field('user_id,nickname,avatar,level')->where(['user_id' => $id])->find();
            $info['agent_id'] = Db::name('promoter')->where(['user_id' => $id])->value('agent_id');
            $info['rel_num'] = Db::name('promotion_relation')->where(['agent_id' => $info['agent_id'], 'promoter_uid' => $id])->count();
            $this->assign('_info', $info);
        } else {
            $userService = new \app\admin\service\User();
            $agentService = new \app\admin\service\Agent();
            $users = [];
            foreach ($ids as $id) {
                $user = $userService->getInfo($id);
                if ($user) {
                    $user['city_info'] = ['name' => $user['city_name']];
                    $user['agent_num'] = Db::name('promotion_relation')->where(['user_id' => $user['user_id']])->count();
                    if ($selectAgentId && $user['agent_num'] > 0) {
                        $myRel = Db::name('promotion_relation')
                            ->where(['user_id' => $user['user_id'], 'agent_id' => $selectAgentId])->find();
                        if ($myRel && $myRel['promoter_uid']) {
                            $user['promoter_info'] = Db::name('user')->field('user_id,avatar,nickname,remark_name')
                                ->where(['user_id' => $myRel['promoter_uid']])->find();
                        }
                    }
                    $where = ['user_id' => $id];
                    $agentId = null;
                    if ($transferType == 'anchor') {
                        $anchor = Db::name('anchor')->where($where)->find();
                        if (!empty($anchor)) {
                            $user['agent_info'] = $agentService->getAgentById($anchor['agent_id']);
                            $user['promoter_info'] = [];
                        }
                    } else if ($transferType == 'promoter') {
                        $promoter = Db::name('promoter')->where($where)->find();
                        if (!empty($promoter)) {
                            $user['agent_info'] = $agentService->getAgentById($promoter['agent_id']);
                            $user['promoter_info'] = [];
                        }
                    }
                    $users[] = $user;
                }
            }
            $this->assign('user_list', $users);
        }
        $this->assign('call_name', $this->maps[$transferType]);
        $this->assign('transfer_type', $transferType);
        $this->assign('rel', $rel);
        $transfer_name = $transferType == 'user' ? '用户' : ($transferType == 'anchor' ? '主播' : config('app.agent_setting.promoter_name'));
        $this->assign('transfer_name', $transfer_name);
    }


}


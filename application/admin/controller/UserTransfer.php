<?php

namespace app\admin\controller;

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
        $transferType = input('transfer_type', 'user');//被转移的身份类型
        $rel = input('rel', 'user');//user 指定用户 promoter config('app.agent_setting.promoter_name')名下的所有用户 agent config('app.agent_setting.agent_name')名下的所有用户
        if (!isset($this->maps[$transferType])) $this->error('移交类型不正确');
        $this->checkAuth("admin:{$transferType}:assign_agent");
        if (empty($ids)) $this->error('请选择用户');
        foreach ($ids as $item)
        {
            $isvirtual = Db::name('user')->where(['user_id' => $item])->value('isvirtual');
            if ($isvirtual){
                $this->error('虚拟用户不允许分配');
            }
        }
        if (!in_array($rel, ['promoter', 'agent', 'user'])) $this->error('关联关系不正确');
        $maxNum = $rel == 'user' ? 30 : 1;
        if (count($ids) > $maxNum) $this->error("一次最多只能处理{$maxNum}条记录");
        $condition = [];
        $conditionQuery = input('condition');
        if (!empty($conditionQuery)) parse_str($conditionQuery, $condition);
        $data = ['ids' => $ids, 'transfer_type' => $transferType, 'rel' => $rel, 'primary_key' => $primaryKey, 'condition' => $condition];
        session('transfer_data', $data);
        $get = input();
        $agentService = new \app\admin\service\Agent();
        $total = $agentService->getTotal($get);
        $page = $this->pageshow($total);
        $list = $agentService->getList($get, $page->firstRow, $page->listRows);
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
        $this->checkAuth("admin:{$data['transfer_type']}:assign_agent");
        if (empty($get['agent_id'])) $this->error('请选择'.config('app.agent_setting.agent_name'));
        $data['agent_id'] = $get['agent_id'];
        session('transfer_data', $data);
        $condition = $data['condition'];
        if ($data['transfer_type'] != 'user' || ($data['transfer_type'] == 'user' && $condition['sync'] == '1')) {
            $this->redirect('confirm');
        }
        $agentService = new \app\admin\service\Promoter();
        $total = $agentService->getTotal($get);
        $page = $this->pageshow($total);
        $list = $agentService->getList($get, $page->firstRow, $page->listRows);
        $this->assign('_list', $list);
        $this->assignRelInfo($data['rel'], $data['ids'], $data['transfer_type'], $data['agent_id']);
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
        $this->checkAuth("admin:{$transferType}:assign_agent");
        $rel = $data['rel'];
        $promoterUid = input('user_id', 0);
        $is_transfer = input('is_transfer', 1);
        $httpClient = new HttpClient([
            'timeout' => 3,
            'base' => PUSH_URL
        ]);
        $total = 0;
        $sync = false;
        switch ($transferType) {
            //转移用户
            case 'user':
                $userTransfer = new \app\admin\service\UserTransfer();
                $userTransfer->setAdmin('erp', AID);
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
                $is_transfer && $userTransfer->setTransfer($is_transfer);
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
            alog("user.user_transfer.confirm", "确认转移 ".$total." 个用户");
            return json_success(['total' => $total, 'sync' => $sync], $sync ? "移交成功,共计处理{$total}条记录" : '转移任务提交成功，请稍后');
        }
        $this->assign('total', $total);
        $this->assign('sync', $sync ? '1' : '0');
        return $this->fetch('confirm_success');
    }

    //加载关联信息
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
                    $user['agent_info'] = Db::name('promotion_relation')->where(['user_id' => $user['user_id']])->find();
                    $user['agent_name'] = Db::name('agent')->field('name')->where(['id' => $user['agent_info']['agent_id']])->value('name');
                    $user['agent_list'] = Db::name('promotion_relation')
                        ->alias('pr')
                        ->field('agent.name agent_name')
                        ->join('__AGENT__ agent', 'agent.id=pr.agent_id')
                        ->where(['pr.user_id' => $user['user_id']])
                        ->select();
                    if ($user['is_anchor'] == '1') {
                        $user['anchor_info'] = Db::name('anchor')->alias('anchor')
                            ->field('anchor.user_id,agent.name agent_name,agent.logo agent_logo,agent.id agent_id')
                            ->where(['anchor.user_id' => $user['user_id']])
                            ->join('__AGENT__ agent', 'agent.id=anchor.agent_id')->find();
                    }
                    if ($user['is_promoter'] == '1') {
                        $user['promoter_info'] = Db::name('promoter')->alias('promoter')
                            ->field('promoter.user_id,agent.name agent_name,agent.logo agent_logo,agent.id agent_id')
                            ->where(['promoter.user_id' => $user['user_id']])
                            ->join('__AGENT__ agent', 'agent.id=promoter.agent_id')->find();
                        $user['promoter_info']['nickname'] = Db::name('user')->where(['user_id' => $user['promoter_info']['user_id']])->value('nickname');
                    }
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

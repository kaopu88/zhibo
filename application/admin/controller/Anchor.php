<?php

namespace app\admin\controller;

use app\admin\service\AnchorKpi;
use bxkj_common\DateTools;
use bxkj_common\RedisClient;
use think\Db;
use think\facade\Request;

class Anchor extends Controller
{
    public function index()
    {
        $this->checkAuth('admin:anchor:select');
        $userService = new \app\admin\service\Anchor();
        $get = input();
        $total = $userService->getTotal($get);
        $page = $this->pageshow($total);
        $users = $userService->getList($get, $page->firstRow, $page->listRows);
        $this->assign('_list', $users);
        return $this->fetch();
    }

    public function album()
    {
        $this->checkAuth('admin:anchor:select');
        $list = Db::name('user_album')->where(array('user_id' => input('user_id')))->select();
        $this->assign('_list', $list);
        return $this->fetch();
    }

    public function detail()
    {
        $this->checkAuth('admin:anchor:select');
        $userId = input('user_id');
        $anchorService = new \app\admin\service\Anchor();
        $anchor = $anchorService->getInfo($userId);
        if (empty($anchor)) $this->error('主播不存在');
        $this->assign('user', $anchor);
        $guards = $anchorService->getGuards($anchor['user_id'], 0, 200);
        $managers = $anchorService->getManagers($anchor['user_id'], 0, 200);
        //粉丝贡献榜
        $rank = new \app\admin\service\Rank();
        $offset = 0;
        $length = 10;
        $get = array(
            'interval' => 'his',
            'order' => 'desc'
        );
        $get['name'] = "contr:total:{$userId}";
        $get['numkeys'] = ["contr:real:{$userId}", "contr:isvirtual:{$userId}"];
        $heroes_rank = $rank->getList($get, $offset, $length, array(
            'scoreKey' => 'millet',
            'memberKey' => 'user_id',
            'stealth' => 'rank_stealth',
            'stealth_exp' => [$userId]
        ));
        $redis = RedisClient::getInstance();
        if ($heroes_rank['list']) {
            foreach ($heroes_rank['list'] as &$item) {
                $level = Db::name('exp_level')->field('levelname,icon')->where(array('levelid' => $item['level']))->find();
                $item['level_icon'] = $level['icon'];
                $item['level_name'] = $level['levelname'];
                $name = "contr:isvirtual:{$userId}";
                $key = $rank->getIntervalKey($name, 'his');
                $item['virtual_millet'] = (int)$redis->zScore($key, $item['user_id']);
                $name2 = "contr:real:{$userId}";
                $key2 = $rank->getIntervalKey($name2, 'his');
                $item['real_millet'] = (int)$redis->zScore($key2, $item['user_id']);
                $item['phone'] = Db::name('user')->where(array('user_id' => $item['user_id']))->value('phone');
            }
        }
        $this->assign('guards', $guards);
        $this->assign('guard_total', count($guards));
        $this->assign('managers', $managers);
        $this->assign('manager_total', count($managers));
        $this->assign('heroes_rank', $heroes_rank['list']);
        $this->assign('heroes_rank_total', $heroes_rank['total']);
        return $this->fetch();
    }

    public function location()
    {
        $this->checkAuth('admin:anchor:change_location');
        if (Request::isGet()) {
            $userId = input('user_id');
            if (empty($userId)) $this->error('请选择用户');
            $anchor = new \app\admin\service\Anchor();
            $data = $anchor->getLocation($userId);
            $data['user_id'] = $userId;
            return json_success($data);
        } else {
            $post = input();
            $anchor = new \app\admin\service\Anchor();
            $res = $anchor->setLocation($post);
            if (!$res) $this->error($anchor->getError());
            alog("user.anchor.edit", '修改主播定位 USER_ID：'.$post['user_id']);
            return json_success([], '设置成功');
        }
    }

    //移除守护
    public function remove_guard()
    {
        $this->checkAuth('admin:anchor:guard');
        $userId = input('user_id');
        $anchorUid = input('anchor_uid');
        if (empty($userId)) $this->error('请选择用户');
        if (empty($anchorUid)) $this->error('请选择主播');
        $key = "BG_GUARD:{$anchorUid}";
        $redis = RedisClient::getInstance();
        $res = $redis->zRem($key, $userId);
        if (!$res) $this->error('移除失败');
        alog("user.anchor.edit", '移除主播守护 USER_ID：'.$userId);
        $this->success('移除成功');
    }

    //添加守护
    public function add_guard()
    {
        $this->checkAuth('admin:anchor:guard');
        if (Request::isGet()) {
            $this->success('获取成功', input());
        } else {
            $anchorUid = input('anchor_uid');
            if (empty($anchorUid)) $this->error('请选择主播');
            $uids = explode(',', input('anchor_guard_uids'));
            $key = "BG_GUARD:{$anchorUid}";
            $redis = RedisClient::getInstance();
            if (count($uids) > 0 && is_array($uids)) {
                foreach ($uids as $userId) {
                    if ($userId == '') {
                        continue;
                    }
                    $scoreUid = $redis->zscore($key, $userId);
                    $month_time = 2592000;
                    $guard_time = !empty($scoreUid) ? $month_time + $scoreUid : $month_time + time();
                    $redis->zadd($key, $guard_time, $userId);
                }
                alog("user.anchor.edit", '添加主播 USER_ID：'.$userId." 守护");
                $this->success('设置成功');
            } else {
                $this->error('请选择用户');
            }
        }
    }

    //移除场控
    public function remove_live_manage()
    {
        $this->checkAuth('admin:anchor:live_manage');
        $userId = input('user_id');
        $anchorUid = input('anchor_uid');
        if (empty($userId)) $this->error('请选择用户');
        if (empty($anchorUid)) $this->error('请选择主播');
        Db::name('live_manage')->where(['anchor_uid' => $anchorUid, 'manage_uid' => $userId])->delete();
        $key = "liveManage:{$anchorUid}";
        $redis = RedisClient::getInstance();
        $res = $redis->srem($key, $userId);
        if (!$res) $this->error('移除失败');
        alog("user.anchor.edit", '移除主播 USER_ID：'.$userId." 场控");
        $this->success('移除成功');
    }

    //添加场控
    public function add_live_manage()
    {
        $this->checkAuth('admin:anchor:live_manage');
        if (Request::isGet()) {
            $this->success('获取成功', input());
        } else {
            $anchorUid = input('anchor_uid');
            if (empty($anchorUid)) $this->error('请选择主播');
            $uids = explode(',', input('live_manage_uids'));
            $key = "liveManage:{$anchorUid}";
            $redis = RedisClient::getInstance();
            $count = config('app.live_manage_sum');
            if (count($uids) > 0 && is_array($uids)) {
                foreach ($uids as $userId) {
                    if ($userId == '') {
                        continue;
                    }
                    $res = Db::name('live_manage')->where(['anchor_uid' => $anchorUid])->select();
                    if (count($res) > $count - 1) $this->error('当前管理已满,不能添加新的管理');
                    Db::name('live_manage')->insert(['anchor_uid' => $anchorUid, 'manage_uid' => $userId, 'create_time' => time()]);
                    $redis->sadd($key, $userId);
                }
                alog("user.anchor.edit", '添加主播 USER_ID：'.$userId." 场控");
                $this->success('设置成功');
            } else {
                $this->error('请选择用户');
            }
        }
    }

    public function get_suggests()
    {
        $anchorService = new \app\admin\service\Anchor();
        $result = $anchorService->getSuggests(input('keyword'));
        return json_success($result ? $result : []);
    }

    public function find()
    {
        $this->checkAuth('admin:anchor:select');
        $get = input();
        $anchorService = new \app\admin\service\Anchor();
        $total = $anchorService->getTotal($get);
        $page = $this->pageshow($total);
        $films = $anchorService->getList($get, $page->firstRow, $page->listRows);
        $this->assign('_list', $films);
        return $this->fetch();
    }

    public function millet()
    {
        $this->checkAuth('admin:anchor:select_millet');
        $get = input();
        $get['runit'] = $get['runit'] ? $get['runit'] : 'd';
        $get['rnum'] = $get['rnum'] ? $get['rnum'] : date('Y-m-d');
        $timeRangerConfig = DateTools::getTimeRangerConfig();
        $this->assign('time_ranger_json', json_encode($timeRangerConfig));
        $anchorKpi = new AnchorKpi();
        $total = $anchorKpi->getTotal($get);
        $page = $this->pageshow($total);
        $list = $anchorKpi->getMilletList($get, $page->firstRow, $page->listRows);
        $this->assign('_list', $list);
        $this->assign('get', $get);
        return $this->fetch();
    }

    public function create()
    {
        $userId = input('user_id');
        $agentId = input('agent_id');
        $force = input('force', '0');
        if (empty($userId)) $this->error('请选择用户');
        if (empty($agentId)) $this->error('请选择'.config('app.agent_setting.agent_name'));
        $anchorService = new \app\admin\service\Anchor();
        $res = $anchorService->create([
            'agent_id' => $agentId,
            'user_id' => $userId,
            'force' => $force,
            'admin' => [
                'type' => 'erp',
                'id' => AID
            ]]);
        if (!$res) $this->error($anchorService->getError());
        alog("user.anchor.add", '添加主播 USER_ID：'.$userId);
        $this->success('已设置为主播');
    }

    public function cancel()
    {
        $this->checkAuth('admin:anchor:cancel');
        $userId = input('user_id');
        if (empty($userId)) $this->error('请选择用户');
        $anchorService = new \app\admin\service\Anchor();
        $res = $anchorService->cancel([$userId], null, [
            'type' => 'erp',
            'id' => AID
        ]);
        if (!$res) $this->error($anchorService->getError());
        alog("user.anchor.cancel", '取消主播 USER_ID：'.$userId);
        $this->success('已取消主播');
    }

    public function tranfer()
    {
        $this->checkAuth('admin:anchor:cancel');
        $userId = input('user_id');
        if (empty($userId)) $this->error('请选择用户');
        $anchorService = new \app\admin\service\Anchor();
        $relation = new \app\admin\service\PromotionRelation();
        $anchor = $anchorService->getInfo($userId);
        if (empty($anchor)) $this->error('该用户还不是主播');
        $res = $relation->tranfer($userId);
        if ($res['code'] != 200) $this->error($res['msg']);
        $agentId = $res['data']['agent_id'];
        $res = Db::name('anchor')->where(['user_id' => $userId])->update(['agent_id' => $agentId]);
        if (!$res) {
            $this->error('操作失败');
        }
        alog("user.anchor.edit", '转移主播 USER_ID：'.$userId);
        $this->success('成功转移主播');
    }

    public function cash()
    {
        if (Request::isGet()) {
            $userId = input('user_id');
            if (empty($userId)) $this->error('请选择用户');
            $anchor= Db::name('anchor')->where(['user_id' => $userId])->find();
            if (empty($anchor)) $this->error('主播不存在');
            return json_success($anchor);
        } else {
            $post = input();
            $anchor= Db::name('anchor')->where(['user_id' => $post['user_id']])->find();
            if (empty($anchor)) $this->error('主播不存在');
            $cash_rate = $post['cash_rate'];
            if ($cash_rate < 0 || $cash_rate > 1) $this->error('比例设置应在0到1之间');
            $anchorRes= Db::name('anchor')->where(['user_id' => $anchor['user_id']])->update(['cash_rate' => $cash_rate]);
            alog("user.anchor.edit", '设置主播 USER_ID：'.$post['user_id'] ." 提现比例：".$cash_rate);
            return json_success([], '设置成功');
        }
    }
}

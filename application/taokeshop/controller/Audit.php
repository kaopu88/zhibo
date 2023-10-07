<?php
/**
 * Created by PhpStorm.
 * User: 李成
 * Date: 2020/4/27
 * Time: 9:59
 */
namespace app\taokeshop\controller;

use app\admin\service\SysConfig;
use app\admin\service\User;
use think\Db;
use think\facade\Request;

class Audit extends Controller
{
    public function index()
    {
        $this->checkAuth('taokeshop:audit:index');

        $get = input();
        $goodsService = new \app\taokeshop\service\Audit();
        $total = $goodsService->getTotal($get);
        $page = $this->pageshow($total);
        $list = $goodsService->getList($get, $page->firstRow, $page->listRows);
        $this->assign('_list', $list);
        return $this->fetch();
    }

    /**
     * 审核
     * @return mixed|void
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function audit()
    {
        $this->checkAuth('taokeshop:audit:update');
        if(Request::isPost()) {
            $ids = get_request_ids();
            if (empty($ids)) $this->error('请选择记录');
            $memo = input("memo", "");
            $status = input("status");
            $num = Db::name('user_taoke_audit')->whereIn('id', $ids)->update(['status' => $status, 'check_time' => time(), "memo" =>$memo]);
            if (!$num) {
                $this->error('审核失败');
            }
            $userService = new User();
            $auditInfo = Db::name('user_taoke_audit')->whereIn('id', $ids)->find();
            if($status == 2) {
                $sysConfig = new SysConfig();
                $shopConfig = $sysConfig->getConfig("shop");
                $shopConfig = json_decode($shopConfig['value'], true);

                $userInfo = $userService->getInfo($auditInfo['user_id']);
                if($shopConfig['open_fee'] == 0) {
                    $shop = new \app\taokeshop\service\Shop();
                    $id = $shop->addShop(["user_id" => $auditInfo['user_id'], "create_time" => time()]);
                    if ($id === false) {
                        $this->error('店铺添加失败');
                    }
                    if($userInfo['taoke_shop'] == 2){
                        $status = $userService->updateData($auditInfo['user_id'], ['taoke_shop' => 1, 'shop_id' => $id]);
                        if ($status === false) {
                            $this->error('用户绑定店铺失败');
                        }
                    }
                    $liveConfig = $sysConfig->getConfig("live");
                    $liveConfig = json_decode($liveConfig['value'], true);
                    if($userInfo['is_anchor'] == 0 && $liveConfig['live_setting']['user_live']['open_anchor_type'] == 1){
                        $applyLog = Db::name("anchor_apply")->where(["user_id" => $auditInfo['user_id']])->find();
                        if (!empty($applyLog)) {
                            $status = Db::name("anchor_apply")->where(["user_id" => $auditInfo['user_id']])->update(["status" => 2]);
                            if ($status === false) {
                                $this->error('主播申请记录状态更新失败');
                            }
                        }
                        $anchorService = new \app\admin\service\Anchor();
                        $res = $anchorService->create([
                            'agent_id' => $applyLog['agent_id'],
                            'user_id' => $auditInfo['user_id'],
                            'force' => 0,
                            'admin' => [
                                'type' => 'erp',
                                'id' => AID
                            ]], 1);
                        if (!$res) $this->error('主播开通失败');
                    }
                }else{
                    if ($userInfo['taoke_shop'] == 6){
                        $userService->updateData($auditInfo['user_id'], ['taoke_shop' => 7]);
                    }
                }
                alog("taokeshop.audit.edit", '审核小店 ID：'.implode(",", $ids)."通过审核");
                $this->success('审核成功', "",  url('audit/index'));
            }else{
                $userService->updateData($auditInfo['user_id'], ['taoke_shop' => 3]);
                alog("taokeshop.shop.audit", '审核小店 ID：'.implode(",", $ids)."审核拒绝");
                $this->success('拒绝成功', "",  url('audit/index'));
            }
        }else {
            $id = input("id");
            $this->assign("id", $id);
            return $this->fetch("audit/verify");
        }
    }

}
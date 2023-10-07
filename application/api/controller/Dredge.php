<?php
/**
 * Created by PhpStorm.
 * User: 李成
 * Date: 2020/5/7
 * Time: 9:22
 */
namespace app\api\controller;

use app\admin\service\SysConfig;
use app\common\controller\UserController;
use app\taokeshop\service\Audit;
use app\taoke\service\NsShopApply;

class Dredge extends UserController
{
    /**
     * 创建淘客开通小店支付订单
     * @return \think\response\Json
     */
    public function createTaoke()
    {
        $audit = new Audit();
        $where["user_id"] = USERID;
        $submit = submit_verify('taokeshop'.USERID);
        if (!$submit) return $this->jsonError('您提交太频繁了~~~');
        $userAuditInfo = $audit->getInfo($where);
        if ($userAuditInfo) {
            $userInfo = $this->user;

            if ($userAuditInfo['status'] == 0 && $userInfo['taoke_shop'] == 2) {
                return $this->jsonError("未审核");

            } elseif ($userAuditInfo['status'] == 2 && $userInfo['taoke_shop'] == 7) {
                $sysConfig  = new SysConfig();
                $shopConfig = $sysConfig->getConfig("shop");
                $shopConfig = json_decode($shopConfig['value'], true);
                if ($shopConfig['open_fee'] > 0) {
                    $params              = input();
                    $params['user_id']   = USERID;
                    $params['client_ip'] = get_client_ip();
                    $params['app_v']     = APP_V;
                    $params['quantity']  = 1;
                    $rec                 = new \app\common\service\Dredge();
                    $result              = $rec->create($params);
                    if (!$result) return $this->jsonError($rec->getError());
                    return $this->success($result, '获取成功');
                }

            } elseif ($userAuditInfo['status'] == 1) {
                return $this->jsonError("审核拒绝，原因：".$userAuditInfo['memo']);
            }

        } else {
            return $this->jsonError("您还未提交申请");
        }
    }
}
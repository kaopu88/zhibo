<?php
/**
 * Created by PhpStorm.
 * User: 李成
 * Date: 2020/4/29
 * Time: 14:39
 */

namespace app\api\controller;

use app\admin\service\Anchor;
use app\admin\service\AnchorApply;
use app\admin\service\SysConfig;
use app\common\controller\UserController;
use app\taokeshop\service\AnchorShop;
use app\taokeshop\service\Audit;
use app\taokeshop\service\DredgeLog;
use bxkj_module\exception\ApiException;
use think\Db;

class Taokeshop extends UserController
{
    public function __construct()
    {
        parent::__construct();
        $userShop = config('taoke.user_shop') ? config('taoke.user_shop') : 0;
        if ($userShop == 0) throw new ApiException('小店暂未开启', 1);
    }

    /**
     * 获取小店设置
     * @return \think\response\Json
     */
    public function getShopConfig()
    {
        $sysConfig = new SysConfig();
        $shopConfig = $sysConfig->getConfig("taoke");
        if (!$shopConfig) {
            return $this->jsonError("小店功能未开放");
        }
        $shopConfig = json_decode($shopConfig['value'], true);
        if ($shopConfig['user_shop'] == 0) {
            return $this->jsonError("小店功能未开启");
        }
        return $this->jsonSuccess($shopConfig, "获取成功");
    }

    /**
     * 申请开店权限
     * @return \think\response\Json
     */
    public function applyTaokeShop()
    {
        $userId = USERID;
        $userInfo = $this->user;
        if ($userInfo['taoke_shop'] == 1) {
            return $this->jsonError("您已开通小店");
        }
        $where = "user_id = " . $userId . " AND status != 1";
        $audit = new Audit();
        $userAuditInfo = $audit->getInfo($where);
        if ($userAuditInfo) {
            return $this->jsonError("您已提交过申请");
        }
        $sysConfig = new SysConfig();
        $shopConfig = $sysConfig->getConfig("shop");
        $shopConfig = json_decode($shopConfig['value'], true);

        if ($userInfo['verified'] != 1 && $shopConfig['real_name_verify'] == 1) {
            return $this->jsonError("未完成实名认证");
        }
        $needFansNum = !empty($shopConfig['fans_num']) ? $shopConfig['fans_num'] : 0;
        if ($needFansNum > 0 && $userInfo['fans_num_str'] < $needFansNum) {
            return $this->jsonError("粉丝数量不足");
        }
        $needAnchorLevel = !empty($shopConfig['anchor_level']) ? $shopConfig['anchor_level'] : 0;
        if ($needAnchorLevel > 0) {
            $anchor = new Anchor();
            $anchorInfo = $anchor->getInfo($userId);
            if ($anchorInfo['anchor']['anchor_lv'] < $needAnchorLevel) {
                return $this->jsonError("未满足申请条件");
            }
        }

        $video = new \app\admin\service\Video();
        $videoNum = $video->getTotal(["user_id" => $userId, "audit_status" => 2]);
        if ($videoNum < $shopConfig['short_video_num']) {
            return $this->jsonError("短视频发布数量不足");
        }

        $liveConfig = $sysConfig->getConfig("live");
        $liveConfig = json_decode($liveConfig['value'], true);
        if ($liveConfig['live_setting']['user_live']['open_anchor_type'] == 1 && $userInfo['is_anchor'] == 0) {
            $applyLog = Db::name("anchor_apply")->where(["user_id" => $userId])->find();
            if (!empty($applyLog)) {
                if ($applyLog['status'] != 6) {
                    return $this->jsonSuccess(["taoke_shop" => 10001], "主播申请未通过");
                }
            }else {
                return $this->jsonSuccess(["taoke_shop" => 10000], "请先成为主播");
            }
        }

        if ($shopConfig['live_verify'] == 1 && $userInfo['is_anchor'] == 0) {
            $applyLog = Db::name("anchor_apply")->where(["user_id" => $userId])->find();
            if (empty($applyLog)) {
                if ($userInfo['is_anchor'] == 0) {
                    return $this->jsonError("请先成为主播");
                }
            } else {
                if ($applyLog['status'] != 6) {
                    return $this->jsonError( "主播申请未通过");
                }
            }
        }

        if ($shopConfig['audit_model'] == 1) {//是否为免审核
            $res = $this->needAudit($userId, $shopConfig);
        } else {
            $res = $this->freeAudit($userId, $shopConfig);
        }
        return $this->jsonSuccess(["taoke_shop" => $res['code']], $res['msg']);
    }

    /**
     * 需要审核
     * @param $userId
     * @param $config
     * @return array
     */
    protected function needAudit($userId, $config)
    {
        $result = [];
        $audit = new Audit();
        $where["user_id"] = $userId;
        $userAuditInfo = $audit->getInfo($where);//判断是否有申请记录
        $userService = new \app\admin\service\User();
        if ($config['open_fee'] > 0) {
            $code = 6;
        } else {
            $code = 2;
        }
        if ($userAuditInfo) {
            if ($userAuditInfo['status'] == 0) {
                $result['code'] = 2;
                $result['msg'] = "申请审核中";
                return $result;
            } else {
                $data['reapply_time'] = time();
                $data["status"] = 0;
                $data["memo"] = "";
                $status = $audit->updateAudit(["user_id" => $userId], $data);//重新申请时间
            }
        } else {
            $data['user_id'] = $userId;
            $status = $audit->addAudit($data);
        }
        if ($status === false) {
            $result['code'] = -1;
            $result['msg'] = "申请失败";
            return $result;
        }
        $userStatus = $userService->updateData($userId, ['taoke_shop' => $code]);
        if ($userStatus === false) {
            $result['code'] = -1;
            $result['msg'] = "未知错误，请联系管理员";
        } else {
            $userService->updateRedis($userId, ['taoke_shop' => $code]);
            $result['code'] = $code;
            $result['msg'] = "申请成功，待审核";
        }
        return $result;
    }

    /**
     * 免审核
     * @param $userId
     * @param $config
     * @return array|\think\response\Json
     */
    protected function freeAudit($userId, $config)
    {
        $result = [];
        $audit = new Audit();
        $where["user_id"] = $userId;
        $userAuditInfo = $audit->getInfo($where);//判断是否有申请
        $userService = new \app\admin\service\User();
        $shop = new \app\taokeshop\service\Shop();
        $data['status'] = 2;
        $userInfo = $this->user;
        if ($userAuditInfo) {
            $data['reapply_time'] = time();
            $status = $audit->updateAudit(["user_id" => $userId], $data);
        } else {
            $data['user_id'] = $userId;
            $status = $audit->addAudit($data);
        }
        if ($status === false) {
            $result['code'] = -1;
            $result['msg'] = "未知错误，请联系管理员";
            return $result;
        }
        if ($config['open_fee'] > 0) {
            $status = $userService->updateData($userId, ['taoke_shop' => 7]);
            if ($status === false) {
                $result['code'] = -1;
                $result['msg'] = "未知错误，请联系管理员";
            } else {
                $result['code'] = 7;
                $result['msg'] = "申请成功，未付款";
            }
        } else {
            $sysConfig = new SysConfig();
            $liveConfig = $sysConfig->getConfig("live");
            $liveConfig = json_decode($liveConfig['value'], true);
            if($userInfo['is_anchor'] == 0 && $liveConfig['live_setting']['user_live']['open_anchor_type'] == 1){
                $applyLog = Db::name("anchor_apply")->where(["user_id" => $userId])->find();
                if (!empty($applyLog)) {
                    $status = Db::name("anchor_apply")->where(["user_id" => $userId])->update(["status" => 2]);
                    if ($status === false) {
                        $result['code'] = -2;
                        $result['msg'] = "未知错误，请联系管理员";
                        return $result;
                    }
                }
                $anchorService = new \app\admin\service\Anchor();
                $res = $anchorService->create([
                    'agent_id' => $applyLog['agent_id'],
                    'user_id' => $userId,
                    'force' => 0,
                    'admin' => [
                        'type' => 'erp',
                        'id' => AID
                    ]], 1);
                if (!$res) return ['code' => -3, 'msg' => "主播开通失败"];
            }
            $id = $shop->addShop(["user_id" => $userId, "create_time" => time()]);
            if ($id === false) {
                $result['code'] = -1;
                $result['msg'] = "未知错误，请联系管理员";
                return $result;
            }
            $status = $userService->updateData($userId, ['taoke_shop' => 1, 'shop_id' => $id]);
            if ($status === false) {
                $result['code'] = -1;
                $result['msg'] = "未知错误，请联系管理员";
                return $result;
            }
            $result['code'] = 1;
            $result['msg'] = "申请通过";

        }
        return $result;
    }

    /**
     * 检查状态
     * @return \think\response\Json
     */
    public function getApplyStatus()
    {
        $userId = USERID;
        $userInfo = $this->user;
        $audit = new Audit();
        $userAuditInfo = $audit->getInfo(['user_id' => $userId]);
        $anchorShop = new AnchorShop();
        $shopInfo = $anchorShop->getShopInfo(["user_id" => $userId]);
        if (!empty($shopInfo)) {
            if ($shopInfo['status'] == 0) return $this->jsonSuccess(["taoke_shop" => -3], "小店已禁用");
        }

        if (empty($userAuditInfo)) {
            $res = $this->getAcnhorApplyStatus($userId);
            if ($res !== false) return $this->jsonSuccess(["taoke_shop" => $res['code']], $res['msg']);
            return $this->jsonSuccess(["taoke_shop" => 0], "您还未提交申请");
        }

        if ($userAuditInfo['status'] == 0 && $userInfo['taoke_shop'] == 6) return $this->jsonSuccess(["taoke_shop" => 6], "申请成功，待审核，待付款");
        if ($userAuditInfo['status'] == 0 && $userInfo['taoke_shop'] == 2) return $this->jsonSuccess(["taoke_shop" => 2], "申请成功，待审核");

        if ($userAuditInfo['status'] == 2) {
            $sysConfig = new SysConfig();
            $shopConfig = $sysConfig->getConfig("shop");
            $shopConfig = json_decode($shopConfig['value'], true);

            if ($shopConfig['open_fee'] > 0 && $userInfo['taoke_shop'] == 6) return $this->jsonSuccess(["taoke_shop" => 6], "待审核，待付款");
            if ($shopConfig['open_fee'] > 0 && $userInfo['taoke_shop'] == 7) return $this->jsonSuccess(["taoke_shop" => 7], "审核通过，" . $shopConfig['open_fee_name'] . "未支付，请前往支付");
            if ($userInfo['taoke_shop'] == 1) {
                if (!empty($shopInfo)) {
                    return $this->jsonSuccess(["taoke_shop" => 1, "shop_id" => $shopInfo['id']], "审核通过，小店已开启");
                }
                return $this->jsonSuccess(["taoke_shop" => -2], "请联系管理员，店铺未添加");
            }
        }

        if ($userAuditInfo['status'] == 1) return $this->jsonSuccess(["taoke_shop" => 3], "审核拒绝，原因：" . $userAuditInfo['memo']);
    }

    /**
     * 获取主播申请状态
     * @param $userId
     * @return array|bool
     */
    protected function getAcnhorApplyStatus($userId)
    {
        $userInfo = $this->user;
        $sysConfig = new SysConfig();

        $shopConfig = $sysConfig->getConfig("shop");
        $shopConfig = json_decode($shopConfig['value'], true);
        $verify = !empty($shopConfig['real_name_verify']) ? $shopConfig['real_name_verify'] : 0;//实名认证

        if ($userInfo['verified'] != 1 && $verify != 0) {
            $resVerify = Db::name('user_verified')->where(['user_id' => USERID])->order('id desc')->find();
            if (empty($resVerify)) return array("code" => 10010, "msg" => "未实名");;
            if ($resVerify['status'] == '0') return array("code" => 10003, "msg" => "实名待审核中");
            if ($resVerify['status'] == '2') return array("code" => 10004, "msg" => "实名被驳回");
        }

        if ($shopConfig['live_verify'] == 1 && $userInfo['is_anchor'] == 0) {
            $liveConfig = $sysConfig->getConfig("live");
            $liveConfig = json_decode($liveConfig['value'], true);
            if ($liveConfig['live_setting']['user_live']['open_anchor_type'] == 0){
                return array("code" => 10009, "msg" => "请联系客服开通主播");
            }
            $applyLog = Db::name("anchor_apply")->where(["user_id" => $userId])->find();
            if (empty($applyLog)) return array("code" => 10000, "msg" => "请先成为主播");
            if ($applyLog['status'] == 6) return array("code" => 10002, "msg" => "主播申请审核通过");
            if ($applyLog['status'] == 4) {
                $resPromotionVerify = Db::name('promotion_relation_apply')->where(['user_id' => USERID])->order('id desc')->find();
                if (empty($resPromotionVerify)) return array("code" => 10006, "msg" => '无' . config('app.agent_setting.agent_name') . '审核');
                if ($resPromotionVerify['status'] == '0') return array("code" => 10001, "msg" => config('app.agent_setting.agent_name')."待审核");
                if ($resPromotionVerify['status'] == '2') return array("code" => 10005, "msg" => config('app.agent_setting.agent_name')."被驳回");
            }
            if ($applyLog['status'] == 3) return array('code' => 10007, "msg" => '等待平台审核主播申请');
            if ($applyLog['status'] == 5) return array('code' => 10008, "msg" => '平台审核驳回');
            return array("code" => 10001, "msg" => config('app.agent_setting.agent_name')."待审核");
        }
        return false;
    }

    /**
     * 获取用户完成进度
     * @return \think\response\Json
     */
    public function getCondition()
    {
        $userInfo = $this->user;
        if ($userInfo['taoke_shop'] == 1) {
            return $this->jsonError('小店权限已开通');
        }
        $userId = USERID;
        $sysConfig = new SysConfig();
        $shopConfig = $sysConfig->getConfig("shop");
        $shopConfig = json_decode($shopConfig['value'], true);

        $data['audit_status'] = $shopConfig['audit_model'];// 是否免审核 1：否；0：是
        $data['shop']['title'] = $shopConfig['title'];// 页面标题
        $data['shop']['rights'] = [];
        if (!empty($shopConfig['rights'])) {
            $data['shop']['rights'] = explode("\r\n", $shopConfig['rights']);// 权益说明
        }

        $baseCondition = [];
        $proCondition = [];

        $fansCondition = !empty($shopConfig['fans_num']) ? $shopConfig['fans_num'] : 0;//粉丝数
        if ($fansCondition != 0) {
            $fansArr['type'] = "fans";
            $fansArr['title'] = $shopConfig['fans_num_name'];
            $fansArr['icon'] = $shopConfig['fans_num_img'];
            $fansArr['condition'] = $fansCondition;
            $fansArr['isFinish'] = ($userInfo['fans_num'] >= $fansCondition) ? 1 : 0;
            if ($shopConfig['fans_num_type'] == 1) {
                array_push($baseCondition, $fansArr);
            } else {
                array_push($proCondition, $fansArr);
            }
        }

        $videoCondition = !empty($shopConfig['short_video_num']) ? $shopConfig['short_video_num'] : 0;//短视频数量
        if ($videoCondition != 0) {
            $videoArr['type'] = "short_video";
            $videoArr['title'] = $shopConfig['short_video_num_name'];
            $videoArr['icon'] = $shopConfig['short_video_num_img'];
            $videoArr['condition'] = $videoCondition;

            $video = new \app\admin\service\Video();
            $videoNum = $video->getTotal(["user_id" => $userId, "audit_status" => 2]);
            $videoArr['isFinish'] = ($videoNum >= $videoCondition) ? 1 : 0;

            if ($shopConfig['short_video_num_type'] == 1) {
                array_push($baseCondition, $videoArr);
            } else {
                array_push($proCondition, $videoArr);
            }
        }

        $anchorCondition = !empty($shopConfig['anchor_level']) ? $shopConfig['anchor_level'] : 0;//主播等级
        if ($anchorCondition != 0) {
            $anchorArr['type'] = "anchor_level";
            $anchorArr['title'] = $shopConfig['anchor_level_name'];
            $anchorArr['icon'] = $shopConfig['anchor_level_img'];
            $anchorArr['condition'] = $anchorCondition;

            $anchor = new Anchor();
            $anchorInfo = $anchor->getInfo($userId);
            $anchorArr['isFinish'] = ($userInfo['is_anchor'] == 1 && $anchorInfo['anchor']['anchor_lv'] >= $anchorCondition) ? 1 : 0;

            if ($shopConfig['anchor_level_type'] == 1) {
                array_push($baseCondition, $anchorArr);
            } else {
                array_push($proCondition, $anchorArr);
            }
        }

        $feeCondition = !empty($shopConfig['open_fee']) ? $shopConfig['open_fee'] : 0;//开店费用
        if ($feeCondition != 0) {
            $feeArr['type'] = "open_fee";
            $feeArr['title'] = $shopConfig['open_fee_name'];
            $feeArr['icon'] = $shopConfig['open_fee_img'];
            $feeArr['condition'] = $feeCondition;

            $dredge = new DredgeLog();
            $where['user_id'] = $userId;
            $where['pay_status'] = 1;
            $where['type'] = "taoke";
            $info = $dredge->getLogInfo($where);
            if (!empty($info)) {
                $finish = 1;
            } else {
                $finish = 0;
            }
            $feeArr['isFinish'] = $finish;

            if ($shopConfig['open_fee_type'] == 1) {
                array_push($baseCondition, $feeArr);
            } else {
                array_push($proCondition, $feeArr);
            }
        }

        $verifyCondition = !empty($shopConfig['real_name_verify']) ? $shopConfig['real_name_verify'] : 0;//实名认证
        if ($verifyCondition != 0) {
            $verifyArr['type'] = "verify";
            $verifyArr['title'] = $shopConfig['real_name_verify_name'];
            $verifyArr['icon'] = $shopConfig['real_name_img'];
            $verifyArr['condition'] = $verifyCondition;
            $verifyArr['isFinish'] = $userInfo['verified'];

            if ($shopConfig['real_name_type'] == 1) {
                array_push($baseCondition, $verifyArr);
            } else {
                array_push($proCondition, $verifyArr);
            }
        }

        $liveCondition = !empty($shopConfig['live_verify']) ? $shopConfig['live_verify'] : 0;//是否需要开通主播
        if ($liveCondition != 0) {
            $liveArr['type'] = "live";
            $liveArr['title'] = $shopConfig['live_name'];
            $liveArr['icon'] = $shopConfig['live_img'];
            $liveArr['condition'] = $liveCondition;
            $liveArr['isFinish'] = $userInfo['is_anchor'];
            if (empty($userInfo['is_anchor'])) {
                $anchorApply = new AnchorApply();
                $applyLog = $anchorApply->getList(["user_id" => $userId], 0, 1);
                $applyLog = $applyLog[0];
                if (empty($applyLog)) {
                    $liveArr['isFinish'] = $userInfo['is_anchor'];
                } else {
                    if ($applyLog['status'] == 6) {
                        $liveArr['isFinish'] = 1;
                    } else {
                        $liveArr['isFinish'] = 0;
                    }
                }
            }

            if ($shopConfig['live_type'] == 1) {
                array_push($baseCondition, $liveArr);
            } else {
                array_push($proCondition, $liveArr);
            }
        }

        $data['condition'] = [];
        if (!empty($baseCondition)) {
            $base['name'] = "基础要求";
            $base['type'] = 0;
            $base['requirement'] = $baseCondition;
            array_push($data['condition'], $base);
        }

        if (!empty($proCondition)) {
            $pro['name'] = "进阶要求";
            $pro['type'] = 1;
            $pro['requirement'] = $proCondition;
            array_push($data['condition'], $pro);
        }

        $liveConfig = $sysConfig->getConfig("live");
        $liveConfig = json_decode($liveConfig['value'], true);
        if ($liveConfig['live_setting']['user_live']['open_anchor_type'] == 1) {
            $extraArr['type'] = "extra_condition";
            $extraArr['title'] = "直播功能";
            $extraArr['icon'] = $shopConfig['live_img'];
            $extraArr['condition'] = 1;
            $extraArr['isFinish'] = $userInfo['is_anchor'];

            $extraCondition[] = $extraArr;
            $ext['name'] = "附加条件";
            $ext['type'] = 2;
            $ext['requirement'] = $extraCondition;
            array_push($data['condition'], $ext);
        }

        if (isset($data['condition'])) {
            $data['condition_title'] = "开店条件";
        }
        return $this->jsonSuccess($data, "获取成功");
    }

    /**
     * 获取店铺信息
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getShopInfo()
    {
        $userInfo = $this->user;
        if ($userInfo['taoke_shop'] != 1) {
            return $this->jsonError('未开通小店权限');
        }
        $userId = USERID;
        $shop = new AnchorShop();
        $shopInfo = $shop->getShopInfo(["user_id" => $userId]);
        if (empty($shopInfo)) {
            return $this->jsonError("小店不存在");
        }
        if ($shopInfo['status'] == 1) {
            $data = Db::name('sys_config')->where(['mark' => 'upload'])->value('value');
            $config = json_decode($data, true);
            $shopInfo['bg_img'] = empty($shopInfo['bg_img']) ? $config['image_defaults']['shop_bg'] : $shopInfo['bg_img'];

            return $this->jsonSuccess($shopInfo, "获取成功");
        } else {
            return $this->jsonError("小店已禁用");
        }
    }

    /**
     * 获取对应店铺信息
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getShopDetail()
    {
        $params = request()->param();
        $shopId = isset($params['shop_id']) ? $params['shop_id'] : 0;
        if ($shopId == 0) {
            return $this->jsonError("店铺id不能为空");
        }
        $shop = new AnchorShop();
        $shopInfo = $shop->getShopInfo(["id" => $shopId]);
        if (empty($shopInfo)) {
            return $this->jsonError("小店不存在");
        }
        if ($shopInfo['status'] == 0) {
            return $this->jsonError("小店已禁用");
        }
        return $this->jsonSuccess($shopInfo, "获取成功");
    }

    /**
     * 更新店铺信息
     * @return \think\response\Json
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function updateShopInfo()
    {
        $userInfo = $this->user;
        if ($userInfo['taoke_shop'] != 1) {
            return $this->jsonError('未开通小店权限');
        }
        $params = request()->param();
        $shop = new AnchorShop();
        $where['user_id'] = USERID;
        $status = $shop->updateShopInfo($where, $params);
        if ($status !== false) {
            return $this->jsonSuccess("", '编辑成功');
        } else {
            return $this->jsonError("编辑失败");
        }
    }
}
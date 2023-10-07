<?php
/**
 * Created by PhpStorm.
 * User: 李成
 * Date: 2020/5/7
 * Time: 9:25
 */

namespace app\common\service;

use app\admin\service\SysConfig;
use app\taokeshop\service\AnchorShop;
use bxkj_common\DateTools;
use bxkj_common\RabbitMqChannel;
use think\Db;

class Dredge extends Service
{
    /**
     * 创建开通小店权限订单
     * @param $inputData
     * @return array|bool
     */
    public function create($inputData)
    {
        $user_id         = $inputData['user_id'];
        $quantity        = 1;
        $config          = new SysConfig();
        $taokeShopConfig = $config->getConfig("shop");
        $taokeShopConfig = json_decode($taokeShopConfig['value'], true);//获取商城设置
        if ($taokeShopConfig['user_shop'] == 0) return $this->setError('商城功能未开启');
        $fee               = $taokeShopConfig['open_fee'];
        $now               = time();
        $data['order_no']  = get_order_no('taoke_shop');
        $data['type']      = "taoke";
        $data['name']      = "开通淘客小店权限";
        $data['price']     = $taokeShopConfig['open_fee'];
        $data['total_fee'] = round($taokeShopConfig['open_fee'] * $quantity, 2);
        $data['quantity']  = $quantity;
        $userModel         = new User();
        $user              = $userModel->getBasicInfo($user_id);
        if (empty($user)) return $this->setError('用户不存在');
        if ($user['taoke_shop'] == 1) {
            return $this->setError('小店权限已开通');
        }elseif ($user['taoke_shop'] == 6 || $user['taoke_shop'] == 2){
            return $this->setError('申请未审核');
        }elseif ($user['taoke_shop'] == -9 || $user['taoke_shop'] == -1 || $user['taoke_shop'] == -3){
            return $this->setError('申请未通过');
        }
        $num = Db::name('dredge_log')->where(array('user_id' => $user['user_id'], 'pay_status' => '0'))->where([['create_time', 'gt', mktime(0, 0, 0)]])->count();
        if ($num > 50) return $this->setError('今日订单超过50笔未支付');
        $data['user_id']     = $user['user_id'];
        $data['isvirtual']   = $user['isvirtual'];
        $data['pay_method']  = '';
        $data['pay_status']  = '0';
        $data['client_ip']   = $inputData['client_ip'];
        $data['app_v']       = $inputData['app_v'];
        $data['create_time'] = $now;
        $data['year']        = date('Y', $now);
        $data['month']       = date('Ym', $now);
        $data['day']         = date('Ymd', $now);
        $data['fnum']        = DateTools::getFortNum($now);
        $logId               = Db::name('dredge_log')->insertGetId($data);
        if (!$logId) return $this->setError('创建订单失败');
        Db::name('user_taoke_audit')->where(["user_id" => $user_id])->update(["open_fee" => $fee, "pay_order" => $data['order_no']]);
        return [
            'id'         => $logId,
            'order_type' => 'taokeShop',
            'order_no'   => $data['order_no'],
            'user_id'    => $data['user_id'],
            'price'      => $data['price'],
            'total_fee'  => $data['total_fee'],
            'pay_status' => '0',
            'name'       => $data['name']
        ];
    }

    /**
     * 支付成功
     * @param $thirdData
     * @return bool
     * @throws \bxkj_module\exception\ApiException
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function paySuccess($thirdData)
    {
        $orderNo      = $thirdData['rel_no'];
        $where        = array('order_no' => $orderNo, 'pay_status' => '0');
        $thirdTradeNo = $thirdData['trade_no'];
        if (empty($thirdTradeNo)) return $this->setError('第三方订单号不存在');
        $order = Db::name('dredge_log')->where($where)->find();
        if (!$order) return $this->setError('订单不存在');
        $where2 = ['third_trade_no' => $thirdTradeNo, 'pay_method' => $thirdData['pay_method']];
        self::startTrans();
        $num2 = Db::name('dredge_log')->where($where2)->count();
        if ($num2 > 0) {
            self::rollback();
            return $this->setError('第三方订单号已存在');
        }
        $updateData['pay_status']     = '1';
        $updateData['pay_time']       = time();
        $updateData['pay_method']     = $thirdData['pay_method'];
        $updateData['third_trade_no'] = $thirdTradeNo;
        $num                          = Db::name('dredge_log')->where(array('order_no' => $orderNo))->update($updateData);
        if (!$num) {
            self::rollback();
            return $this->setError('支付失败');
        }
        self::commit();
        $status = Db::name('user_taoke_audit')->where(["pay_order" => $orderNo])->update(["pay_status" => 2]);
        if (!$status) {
            return $this->setError('状态更新失败');
        }

        $info = Db::name('user_taoke_audit')->field("user_id")->where(["pay_order" => $orderNo])->find();
        $applyLog = Db::name("anchor_apply")->where(["user_id"=>$info['user_id']])->find();
        if(!empty($applyLog)  && $applyLog['pay_status'] !=2){
            $status = Db::name("anchor_apply")->where(["user_id"=>$info['user_id']])->update(["status" => 2, "pay_status" => 2]);
            if ($status === false) {
                $result['code'] = -2;
                $result['msg'] = "未知错误，请联系管理员";
                return $result;
            }
            $user = new \bxkj_module\service\User();
            $user_info = $user->getUser($info['user_id']);
            if ($user_info['is_anchor'] != '1') {
                $anchorService = new \app\admin\service\Anchor();
                $type = $applyLog['agent_id'] ? 0 : 1;
                $res = $anchorService->create([
                    'agent_id' => $applyLog['agent_id'],
                    'user_id' => $info['user_id'],
                    'force' => 0,
                    'admin' => [
                        'type' => 'erp',
                        'id' => 1
                    ]], $type);
            }
        }

        $anchorShop = new AnchorShop();
        $shopInfo = $anchorShop->getShopInfo(["user_id" => $info['user_id']]);
        if(empty($shopInfo)) {
            $shop = new \app\taokeshop\service\Shop();
            $id   = $shop->addShop(["user_id" => $info['user_id'], "create_time" => time()]);
            if ($id === false) {
                return $this->setError('店铺创建失败，请联系管理员');
            }
        }else{
            $id = $shopInfo['id'];
        }
        $user   = new \app\admin\service\User();
        $status = $user->updateData($info['user_id'], ['taoke_shop' => 1, 'shop_id' => $id]);
        if ($status === false) {
            return $this->setError('绑定店铺失败，请联系管理员');
        }

        //TODO 费用分销
        return true;
    }
}
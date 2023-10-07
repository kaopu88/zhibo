<?php
/**
 * Created by PhpStorm.
 * User: 李成
 * Date: 2020/5/29
 * Time: 11:34
 */
namespace app\api\controller\taoke;

use app\admin\service\SysConfig;
use app\common\controller\Controller;

class Convert extends Controller
{
    /**
     * 淘宝转链
     * @return \think\response\Json
     */
    public function getTbPromotionUrl()
    {
        $userInfo = $this->user;
        $params = request()->param();

        $config = new SysConfig();
        $channelConfig = $config->getConfig("channel");
        $channelConfig = json_decode($channelConfig['value'], true);
        if ($channelConfig['is_open'] == 1){//开启渠道
            if(empty($userInfo['relation_id'])){
                return $this->jsonError("请先进行渠道备案");
            }
            $relationid = $userInfo['relation_id'];
        }
        $goodsId = $params['goods_id'];
        if(empty($goodsId)){
            return $this->jsonError("商品id不能为空");
        }
        $anchorId = isset($params['anchor_id']) ? $params['anchor_id'] : 0;
        if(!empty($anchorId)){
            $user = new \app\admin\service\User();
            $anchorInfo = $user->getUserInfo(["user_id" => $anchorId]);
            $relationid = $anchorInfo['relation_id'];
        }
        $convert = new \app\taoke\service\Convert();
        $result = $convert->createTbProUrl($goodsId);
        if(!empty($result) && $channelConfig['is_open'] == 1){
            $result['coupon_click_url'] = $result['coupon_click_url']."&relationId=".$relationid;
        }
        if($result !== false){
            return $this->jsonSuccess($result, "转链成功");
        }else{
            return $this->jsonError("转链失败");
        }
    }

    /**
     * 拼多多推广链接
     * @return \think\response\Json
     */
    public function getPddPromotionUrl()
    {
        $data = [];
        $params = request()->param();
        $goodsId = $params['goods_id'];
        if(empty($goodsId)){
            return $this->jsonError("商品id不能为空");
        }
        $userInfo = $this->user;
        $pddPid = $userInfo['pdd_pid'];
        $anchorId = isset($params['anchor_id']) ? $params['anchor_id'] : 0;
        if(!empty($anchorId)){
            $user = new \app\admin\service\User();
            $anchorInfo = $user->getUserInfo(["user_id" => $anchorId]);
            $pddPid = $anchorInfo['pdd_pid'];
        }
        $convert = new \app\taoke\service\Convert();
        $result = $convert->createPddProUrl($goodsId, $pddPid);
        if($result !== false){
            return $this->jsonSuccess($result, "转链成功");
        }else{
            return $this->jsonError("转链失败");
        }
    }

    /**
     * 京东推广链接
     * @return \think\response\Json
     */
    public function getJdPromotionUrl()
    {
        $params = request()->param();
        $materialId = $params['item_url'];
        if(empty($materialId)){
            return $this->jsonError("商品链接不能为空");
        }
        $couponUrl = $params['coupon_url'];
        $userInfo = $this->user;
        $jdPid = $userInfo['jd_pid'];
        $anchorId = isset($params['anchor_id']) ? $params['anchor_id'] : 0;
        if(!empty($anchorId)){
            $user = new \app\admin\service\User();
            $anchorInfo = $user->getUserInfo(["user_id" => $anchorId]);
            $jdPid = $anchorInfo['jd_pid'];
        }
        $convert = new \app\taoke\service\Convert();
        $result = $convert->createJdProUrl($materialId, $couponUrl, $jdPid);
        if($result !== false){
            return $this->jsonSuccess($result, "转链成功");
        }else{
            return $this->jsonError("转链失败");
        }
    }

    /**
     * 生成淘口令
     * @param $params
     * @return \think\response\Json
     */
    public function createTaokeys()
    {
        $params = request()->param();
        $url = $params['url'];
        if(empty($url)){
            return $this->jsonError("url不能为空");
        }
        $text = $params['text'];
        if(empty($text)){
            return $this->jsonError("文本不能为空");
        }
        if(!empty($params['logo'])) {
            $logo = $params['logo'];
        }
        if(!empty($params['user_id'])) {
            $userId = $params['user_id'];
        }
        if(!empty($params['ext'])) {
            $ext = $params['ext'];
        }
        $convert = new \app\taoke\service\Convert();
        $result = $convert->createTaokeys($url, $text, $logo, $userId, $ext);
        if($result !== false){
            return $this->jsonSuccess($result, "生成成功");
        }else{
            return $this->jsonError("生成失败");
        }
    }
}
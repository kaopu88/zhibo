<?php
/**
 * Created by PhpStorm.
 * User: 李成
 * Date: 2020/6/5
 * Time: 9:26
 */
namespace app\api\controller\taoke;

use app\common\controller\Controller;
use app\common\controller\UserController;
use app\taoke\service\CreateImages;
use app\taoke\service\Kuaizhan;
use bxkj_common\HttpClient;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;

class Share extends UserController
{
    /**
     * 生成分享信息 分享图片链接以及商品信息
     * @return \think\response\Json
     */
    public function getShareImage()
    {
        $params = request()->param();
        $userId = USERID;
        $userInfo = $this->user;
        $goodsId = $params['goods_id'];
        $shopType = $params['shop_type'];
        if(empty($goodsId)){
            return $this->jsonError("goods_id不能为空");
        }
        if(empty($shopType)){
            return $this->jsonError("shop_type不能为空");
        }

        $para['goods_id'] = $goodsId;
        $para['type'] = $shopType;
        $httpClient = new HttpClient();
        $para['api_key'] = config('app.system_deploy')['taoke_api_key'];
        $result = $httpClient->post(TK_URL."Goods/getDetail", $para)->getData('json');
        if($result['code'] != 200){
            return $this->jsonError("商品不存在");
        }
        $detail = $result['result'];

        $taokouling = "";
        $convert = new \app\taoke\service\Convert();
        if ($shopType == "B" || $shopType == "C") {
            $convertInfo = $convert->createTbProUrl($goodsId);
            if ($detail['coupon_price'] > 0) {
                $url = $convertInfo['coupon_click_url'];
            } else {
                $url = $convertInfo['item_url'];
            }
            if (!empty($userInfo['relation_id'])) {
                $url .= $url . "&relationId=" . $userInfo['relation_id'];
            }
            $taokouling = $convert->createTaokeys($url, $detail['title'], $detail['img']);
        } elseif ($shopType == "P") {
            $convertInfo = $convert->createPddProUrl($goodsId, $userInfo['pdd_pid']);
            $url = $convertInfo['short_url'];
        } elseif ($shopType == "J") {
            $convertInfo = $convert->createJdProUrl($detail['item_url'], $detail['coupon_url'], $userInfo['jd_pid']);
            $url = $convertInfo['shortURL'];
        }

        $sysConfig = new \app\admin\service\SysConfig();
        $shareConfig = $sysConfig->getConfig("app_share_config");
        $shareConfig = json_decode($shareConfig['value'], true);
        if($shareConfig['qrcode_type'] == 1) {
            if ($shopType == "B" || $shopType == "C") {
                $text = $shareConfig['kouling_text'];
                if(!empty($text)){
                    $url = str_replace("\$淘口令\$", $taokouling, $text);
                }else{
                    $url = $taokouling;
                }
            }
        }elseif ($shareConfig['qrcode_type'] == 2){
            if ($shopType == "B" || $shopType == "C") {
                $key = $taokouling;
            } elseif ($shopType == "P") {
                $key = $url;
            } elseif ($shopType == "J") {
                $key = $url;
            }
            $url = urlencode(DOMAIN_URL."/bx_static/shopDetail.html?title=".$detail['title']."&shop_type=".$detail['shop_type']."&img=".$detail['img'].
            "&price=".$detail['price']."&discount_price=".$detail['discount_price']."&coupon_price=".$detail['coupon_price']."&key=".$key);

        }elseif ($shareConfig['qrcode_type'] == 3){
            if ($shopType == "B" || $shopType == "C") {
                $kz = new Kuaizhan();
                $res = $kz->createKzPromoUrl($taokouling, $detail['img']);
                if($res){
                    $url = $res;
                }
            }
        }

        $data['title'] = $detail['title'];
        $data['price'] = $detail['price'];
        $data['discount_price'] = $detail['discount_price'];
        $data['coupon_price'] = $detail['coupon_price'];
        $data['taokouling'] = $taokouling;
        $data['intro'] = empty($detail['desc']) ? "" : $detail['desc'];
        $data['desc'] = empty($detail['desc']) ? $detail['title'] : $detail['desc'];
        $common = new \app\taoke\service\Common();
        $commission = sprintf("%.2f", $detail['discount_price'] * $detail['commission_rate'] / 100);
        $data['commission'] = $common->getPurchaseCommission($commission, $shopType, $userId);
        $url = empty($url) ? $detail['item_url'] : $url;
        $code = $userInfo['invite_code'];
        $data['share_url'] = DOMAIN_URL."/api/taoke.share/getShareUrl?img=".$detail['img']."&title=".str_replace(' ', '', $detail['title'])."&discount_price=".
            $detail['discount_price']."&price=".$detail['price']."&coupon_price=".$detail['coupon_price']."&shop_type=".$shopType."&url=".$url."&code=".$code;
        return $this->jsonSuccess($data, "获取成功");
    }

    /**
     * @return bool
     */
    public function getShareUrl()
    {
        $params = request()->param();
        $img = empty($params['img']) ? "https://img.alicdn.com/bao/uploaded/i1/2808294134/O1CN01pD3ouu1gPPvo4eKaO_!!2808294134.jpg" : $params['img'];
        $title = empty($params['title']) ? "米兰春天袜子男士短袜夏季船袜棉袜低帮浅口吸汗透气爆款运动潮袜" : $params['title'];
        $discountPrice = empty($params['discount_price']) ? "19.9" : $params['discount_price'];
        $price = empty($params['price']) ? "24.9" : $params['price'];
        $couponPrice = $params['coupon_price'];
        $shopType = empty($params['shop_type']) ? "B" : $params['shop_type'];
        $url = empty($params['url']) ? "https://detail.tmall.com/item.htm?id=616195246055" : $params['url'];
        $code = $params['code'];
        $image = new CreateImages();
        $result = $image->generatedGoodsImage($img, $title, $discountPrice, $price, $couponPrice, $shopType, $url, $code);
        return json_encode($result);
    }

    /**
     * @throws \Endroid\QrCode\Exception\InvalidWriterException
     */
    public function createQrcode()
    {
        ob_clean();
        $params = request()->get();
        $size = empty($params['size']) ? 255 : $params['size'];
        $content = empty($params['content']) ? DOMAIN_URL : $params['content'];
        $qrcode = new QrCode();
        $qrcode->setWriterByName('png');
        $qrcode->setEncoding('UTF-8');
        $qrcode->setText($content);//二维码内容
        $qrcode->setSize($size);//大小
        $qrcode->setBackgroundColor(['r' => 255, 'g' => 255, 'b' => 255, 'a' => 0]);
        $qrcode->setForegroundColor(['r' => 0, 'g' => 0, 'b' => 0, 'a' => 0]);
        $qrcode->setErrorCorrectionLevel(ErrorCorrectionLevel::MEDIUM());//容错级别
        header('Content-Type: '.$qrcode->getContentType());
        exit($qrcode->writeString());
    }
}
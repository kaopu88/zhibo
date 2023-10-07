<?php
/**
 * Created by PhpStorm.
 * User: 李成
 * Date: 2020/6/3
 * Time: 8:49
 */
namespace app\api\controller\taoke;

use app\common\controller\Controller;
use bxkj_common\HttpClient;

class Common extends Controller
{
    /**
     * 解析剪切板内容 返回商品信息
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function analysisContent()
    {
        $data = [];
        $params = request()->param();
        $para['content'] = $params['content'];
        $httpClient = new HttpClient();
        $para['api_key'] = config('app.system_deploy')['taoke_api_key'];
        $result = $httpClient->post(TK_URL . "Tool/goods/analysisUrl", $para)->getData('json');
        if ($result['code'] == 200) {
            $info = $result['result'];
            if($info){
                $userId = empty(USERID) ? 0 : USERID;
                $data['goods_id'] = $info['goods_id'];
                $data['title'] = $info['title'];
                $data['short_title'] = isset($info['short_title']) ? $info['short_title'] : "";
                $data['img'] = $info['img'];
                $data['desc'] = isset($info['desc']) ? $info['desc'] : "";
                $data['price'] = $info['price'];
                $data['discount_price'] = $info['discount_price'];
                $data['commission_rate'] = $info['commission_rate'];
                $data['coupon_price'] = $info['coupon_price'];
                $data['volume'] = $info['volume'];
                $data['shop_name'] = $info['shop_name'];
                $data['shop_type'] = $info['shop_type'];
                $common = new \app\taoke\service\Common();
                $commission = sprintf("%.2f", $data['discount_price'] * $data['commission_rate'] / 100);
                $data['commission'] = $common->getPurchaseCommission($commission, $info['shop_type'], $userId);
                $data['commission_sub'] = $common->getUpCommission($commission, $info['shop_type'], $userId);
                $data['commission_high'] = $common->getHighCommission($commission, $info['shop_type'], $userId);
            }
        }else{
            $data = $params['content'];
        }
        return $this->jsonSuccess($data, "解析成功");
    }

    /**
     * 热门搜索关键词
     * @return \think\response\Json
     */
    public function getHotKeywords()
    {
        $httpClient = new HttpClient();
        $para['api_key'] = config('app.system_deploy')['taoke_api_key'];
        $result = $httpClient->post(TK_URL."Search/getHotKey", $para)->getData('json');
        if($result['code'] == 200){
            return $this->jsonSuccess($result['result'], "获取成功");
        }else{
            return $this->jsonError($result['msg']);
        }
    }

    /**
     * 获取淘宝图文详情的源链接
     * @return \think\response\Json
     */
    public function getTaobaoDetailImgUrl()
    {
        $params = request()->param();
        $goodsId = $params['goods_id'];
        if(empty($goodsId)){
            return $this->jsonError("商品id不能为空");
        }
        $url = 'https://h5api.m.taobao.com/h5/mtop.taobao.detail.getdesc/6.0/?data={"id":"'.$goodsId.'","type":"0"}';
        return $this->jsonSuccess($url, "获取成功");
    }

    /**
     * 处理文本内容返回详情图片集合
     * @return \think\response\Json
     */
    public function handleDetailImgs()
    {
        $imgList = [];
        $params = request()->param();
        $content = $params['content'];
        if(empty($content)){
            return $this->jsonError("content不能为空");
        }
        $content = json_decode($content, true);
        if(!empty($content['data']['wdescContent']['pages'])){
            $arr = $content['data']['wdescContent']['pages'];
            for($i=0; $i<= count($arr); $i++){
                if(strpos($arr[$i], "size=") != false){
                    $start = strpos($arr[$i], "size=")+5;
                    $end = strpos($arr[$i], ">");
                    $str = substr($arr[$i], $start, $end - $start);
                    $strArr = explode("x", $str);
                    $data['width'] = $strArr[0];
                    $data['height'] = $strArr[1];
                }
                if(strpos($arr[$i], "img") != false){
                    $start = strpos($arr[$i], ">")+1;
                    if(strpos($arr[$i],"http") != false){
                        $imgUrl = substr($arr[$i],$start, -6);
                    }else{
                        $imgUrl = "http:".substr($arr[$i], $start, -6);
                    }
                    $data['img_url'] = $imgUrl;
                }
                $imgList[] = $data;
            }
        }
        return $this->jsonSuccess($imgList, "获取成功");
    }

    /**
     * 获取京东商品详情请求连接
     * @return \think\response\Json
     */
    public function getJdDetailImgUrl()
    {
        $imgUrl = "";
        $params = request()->param();
        $goodsId = $params['goods_id'];
        if(empty($goodsId)){
            return $this->jsonError("商品id不能为空");
        }
        $url = "https://item.jd.com/".$goodsId.".html";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/html'));
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/72.0.3626.81 Safari/537.36 SE 2.X MetaSr 1.0');
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $content = curl_exec($ch);
        curl_close($ch);
        if(!empty($content)){
            $start = strpos($content, "mainSkuId:'")+11;
            $end = strpos($content, "isPop");
            $str = substr($content, $start, $end - $start);
            $mainSkuId = preg_replace('/[^0-9]/', '', $str);
            $imgUrl = "https://cd.jd.com/description/channel?skuId=".$goodsId."&mainSkuId=".$mainSkuId."&charset=utf-8&cdn=2";
        }
        return $this->jsonSuccess($imgUrl, "获取成功");
    }

    /**
     * 解析京东商品详情图片
     * @return \think\response\Json
     */
    public function handleJdDetailImgs()
    {
        $imgList = [];
        $params = request()->param();
        $content = $params['content'];
        if(empty($content)){
            return $this->jsonError("content不能为空");
        }
        $content = json_decode($content, true);
        if(!empty($content['content'])){
            $str = str_replace("<br>", "", $content['content']);
            $str = str_replace("<br/>", "", $str);
            $str = str_replace("<p>", "", $str);
            $str = str_replace("</p>", "", $str);
            $arr = explode("><", $str);
            for($i=0; $i < count($arr); $i++){
                if(strpos($arr[$i], 'lazyload="') != false){
                    $start = strpos($arr[$i], 'lazyload="')+10;
                }
                if(strpos($arr[$i], 'id=') != false){
                    $end = strpos($arr[$i], 'id=')-2;
                }
                $imgUrl = substr($arr[$i], $start, $end - $start);
                if(strpos($imgUrl,"http") === false){
                    $imgUrl = "http:".$imgUrl;
                }
                $imgList[$i] = $imgUrl;
            }
        }
        return $this->jsonSuccess($imgList, "获取成功");
    }
}
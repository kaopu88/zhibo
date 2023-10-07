<?php
/**
 * Created by PhpStorm.
 * User: 李成
 * Date: 2020/5/28
 * Time: 10:34
 */
namespace app\taoke\controller;

use bxkj_common\HttpClient;
use think\Db;
use think\facade\Request;

class Circle extends Controller
{

    public function index()
    {
        $this->checkAuth('taoke:circle:index');

        $get = input();
        $circleService = new \app\taoke\service\Circle();
        $total = $circleService->getTotal($get);
        $page = $this->pageshow($total);
        $list = $circleService->getList($get, $page->firstRow, $page->listRows);
        $this->assign('_list', $list);

        $circleCateService = new \app\taoke\service\CircleCate();
        $cateTotal = $circleCateService->getTotal([]);
        $cateList = $circleCateService->getList([], 0, $cateTotal);
        if($cateList) {
            $data = [];
            foreach ($cateList as $key => $value) {
                $data[$value['id']] = $value['name'];
            }
            $this->assign('data', $data);
        }
        $this->assign('cate_list', $cateList);
        return $this->fetch();
    }

    public function add()
    {
        $this->checkAuth('taoke:circle:add');
        if (Request::isGet()) {
            $circleCateService = new \app\taoke\service\CircleCate();
            $cateTotal = $circleCateService->getTotal([]);
            $cateList = $circleCateService->getList([], 0, $cateTotal);
            $this->assign('cate_list', $cateList);
            return $this->fetch();
        } else {
            $circleService = new \app\taoke\service\Circle();
            $post = input();
            $title = $post['title'];
            if(empty($title)){
                $this->error('标题不能为空');
            }
            $type = isset($post['type']) ? $post['type'] : 0;
            $ctype = $post['ctype'];
            if($ctype == 1){
                $data['images'] = empty($post['images']) ? "" : $post['images'];
                $data['goods_info'] = "";
            }else{
                $goodsInfo = [];
                $goodsIdArr = $post['goods_id'];
                $priceArr = $post['discount_price'];
                $imageArr = $post['image'];
                $shopType = $post['shop_type'];
                $title = $post['goods_title'];
                $volume = $post['volume'];
                $price = $post['price'];
                $couponPrice = $post['coupon_price'];
                $commissionRate = $post['commission_rate'];
                $galleryImgs = $post['gallery_imgs'];
                for ($i=0; $i<count($goodsIdArr);$i++){
                    $goodsInfo[$i]['goods_id'] = $goodsIdArr[$i];
                    $goodsInfo[$i]['discount_price'] = $priceArr[$i];
                    $goodsInfo[$i]['image'] = $imageArr[$i];
                    $goodsInfo[$i]['shop_type'] = $shopType[$i];
                    $goodsInfo[$i]['title'] = $title[$i];
                    $goodsInfo[$i]['volume'] = $volume[$i];
                    $goodsInfo[$i]['price'] = $price[$i];
                    $goodsInfo[$i]['coupon_price'] = $couponPrice[$i];
                    $goodsInfo[$i]['commission_rate'] = $commissionRate[$i];
                    $goodsInfo[$i]['gallery_imgs'] = $galleryImgs[$i];
                }
                if(!empty($goodsInfo)) {
                    $data['goods_info'] = json_encode($goodsInfo, true);
                }
                $data['comment'] = empty($post['comment']) ? "" : $post['comment'];
            }
            $data['cid'] = $post['cid'];
            $data['title'] = $post['title'];
            $data['type'] = $type;
            $data['author'] = $post['author'];
            $data['showwriting'] = $post['showwriting'];
            $data['status'] = $post['status'];
            $data['sort'] = $post['sort'];
            $result = $circleService->add($data);
            if($result === false){
                $this->error('新增失败');
            }
            alog("taoke.circle.add", "新增发圈 ID：".$result);
            $this->success('新增成功', $result);
        }
    }

    public function edit()
    {
        $this->checkAuth('taoke:circle:update');
        $circleService = new \app\taoke\service\Circle();
        if (Request::isGet()) {
            $id = input('id');
            $info = $circleService->getInfo(["id" => $id]);
            if (empty($info)) $this->error('记录不存在');
            if($info['type'] != 0 && !empty($info['goods_info'])){
                $info['goods_info'] = json_decode($info['goods_info'], true);
            }
            $this->assign('_info', $info);
            $circleCateService = new \app\taoke\service\CircleCate();
            $cateTotal = $circleCateService->getTotal([]);
            $cateList = $circleCateService->getList([], 0, $cateTotal);
            $this->assign('cate_list', $cateList);
            return $this->fetch('add');
        } else {
            $post = input();
            $title = $post['title'];
            if(empty($title)){
                $this->error('标题不能为空');
            }
            $ctype = $post['ctype'];
            $type = isset($post['type']) ? $post['type'] : 0;
            if($ctype == 1){
                $data['images'] = empty($post['images']) ? "" : $post['images'];
                $data['goods_info'] = "";
            }else{
                $goodsInfo = [];
                $goodsIdArr = $post['goods_id'];
                if(empty($goodsIdArr)){
                    $this->error('商品不能为空');
                }
                $priceArr = $post['discount_price'];
                $imageArr = $post['image'];
                $shopType = $post['shop_type'];
                $title = $post['goods_title'];
                $volume = $post['volume'];
                $price = $post['price'];
                $couponPrice = $post['coupon_price'];
                $commissionRate = $post['commission_rate'];
                $galleryImgs = $post['gallery_imgs'];
                for ($i=0; $i<count($goodsIdArr);$i++){
                    $goodsInfo[$i]['goods_id'] = $goodsIdArr[$i];
                    $goodsInfo[$i]['discount_price'] = $priceArr[$i];
                    $goodsInfo[$i]['image'] = $imageArr[$i];
                    $goodsInfo[$i]['shop_type'] = $shopType[$i];
                    $goodsInfo[$i]['title'] = $title[$i];
                    $goodsInfo[$i]['volume'] = $volume[$i];
                    $goodsInfo[$i]['price'] = $price[$i];
                    $goodsInfo[$i]['coupon_price'] = $couponPrice[$i];
                    $goodsInfo[$i]['commission_rate'] = $commissionRate[$i];
                    $goodsInfo[$i]['gallery_imgs'] = $galleryImgs[$i];
                }
                if(!empty($goodsInfo)) {
                    $data['goods_info'] = json_encode($goodsInfo, true);
                }
                $data['comment'] = empty($post['comment']) ? "" : $post['comment'];
            }
            $where['id'] = $post['id'];
            $data['cid'] = $post['cid'];
            $data['title'] = $post['title'];
            $data['type'] = $type;
            $data['author'] = $post['author'];
            $data['showwriting'] = $post['showwriting'];
            $data['status'] = $post['status'];
            $data['sort'] = $post['sort'];
            $result = $circleService->updateInfo($where, $data);
            if($result === false){
                $this->error('编辑失败');
            }
            alog("taoke.circle.edit", "编辑发圈 ID：".$post['id']);
            $this->success('编辑成功', $result);
        }
    }

    public function changeStatus()
    {
        $this->checkAuth('taoke:circle:update');
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择记录');
        $status = input('status');
        if (!in_array($status, ['0', '1'])) $this->error('状态值不正确');
        $circleService = new \app\taoke\service\Circle();
        $num = $circleService->updateInfo(["id" => $ids], ['status' => $status]);
        if (!$num) $this->error('切换状态失败');
        alog("taoke.circle.edit", "编辑发圈 ID：".implode(",", $ids)."<br>切换状态 ：".($status == 1 ? "启用" : "禁用"));
        $this->success('切换成功');
    }

    public function del()
    {
        $this->checkAuth('taoke:circle:delete');
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择记录');
        $where[] = ['id', "in", $ids];
        $circleService = new \app\taoke\service\Circle();
        $num = $circleService->delete($where);
        if (!$num) $this->error('删除失败');
        alog("taoke.circle.del", "删除发圈 ID：".implode(",", $ids));
        $this->success("删除成功，共计删除{$num}条记录");
    }

    public function getType()
    {
        if (Request::isPost()) {
            $id = input('id');
            if(empty($id)){
                $this->error("id不能为空");
            }
            $info = Db::name("circle_cate")->where(["id" => $id])->find();
            if(empty($info)){
                $this->error("记录不存在");
            }
            $this->success('获取成功', $info['type']);
        }
    }

    public function getItem()
    {
        if (Request::isPost()) {
            $params = [];
            $goodsId = input('goods_id');
            if(empty($goodsId)){
                $this->error("商品id不能为空");
            }
            $params['goods_id'] = $goodsId;
            $shopType = input("shop_type");
            if(empty($shopType)){
                $this->error("商品类型不能为空");
            }
            $params["type"] = $shopType;
            $httpClient = new HttpClient();
            $params['appkey'] = config('app.system_deploy')['taoke_api_key'];
            $result = $httpClient->post(TK_URL."Goods/getDetail", $params)->getData('json');
            if($result['code'] == 200){
                $info = $result['result'];
                if(empty($info)){
                    $this->error('获取失败');
                }
                $this->success('获取成功', $info);
            }else{
                $this->error('获取失败');
            }
        }
    }
}
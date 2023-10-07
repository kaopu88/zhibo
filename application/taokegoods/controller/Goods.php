<?php
/**
 * Created by PhpStorm.
 * User: 李成
 * Date: 2020/4/24
 * Time: 14:38
 */
namespace app\taokegoods\controller;

use app\taokeshop\service\LiveGoods;
use bxkj_common\HttpClient;
use bxkj_common\RabbitMqChannel;
use think\facade\Request;

class Goods extends Controller
{
    public function index()
    {
        $data = [];
        $this->checkAuth('taokegoods:goods:index');

        $get = input();
        $goodsService = new \app\taokegoods\service\Goods();
        $total = $goodsService->getTotal($get);
        $page = $this->pageshow($total);
        $list = $goodsService->getList($get, $page->firstRow, $page->listRows);
        $this->assign('_list', $list);

        $cate = new \app\taokegoods\service\Cate();
        $cateList = $cate->getAllCate(["status" => 1]);
        $this->assign('cate_list', $cateList);
        if($cateList){
            foreach ($cateList as $key => $value){
                $data[$value['dtk_cate_id']] = $value['name'];
            }
        }
        $this->assign('data', $data);
        return $this->fetch();
    }

    public function add()
    {
        $this->checkAuth('taokegoods:goods:add');
        if (Request::isGet()) {
            $info = [];
            $get = input();
            if ($get['cate_id'] != '') $info['cate_id'] = $get['cate_id'];
            $this->assign('_info', $info);

            $cate = new \app\taokegoods\service\Cate();
            $cateList = $cate->getAllCate(["status" => 1]);
            $this->assign('cate_list', $cateList);

            return $this->fetch();
        } else {
            $goodsService = new \app\taokegoods\service\Goods();
            $post = input();
            unset($post['redirect']);
            $post['coupon_start_time'] = strtotime(input("coupon_start_time"));
            $post['coupon_end_time'] = strtotime(input("coupon_end_time"));
            $post['add_type'] = 1;
            $result = $goodsService->add($post);
            if($result === false){
                $this->error('新增失败');
            }
            alog("taoke.goods.add", '新增商品 ID：'.$result);
            $this->success('新增成功', $result);
        }
    }

    public function edit()
    {
        $this->checkAuth('taokegoods:goods:update');
        $goodsService = new \app\taokegoods\service\Goods();
        if (Request::isGet()) {
            $id = input('id');
            $info = $goodsService->getGoodsInfo(["id" => $id]);
            if (empty($info)) $this->error('商品不存在');
            $this->assign('_info', $info);

            $cate = new \app\taokegoods\service\Cate();
            $cateList = $cate->getAllCate(["status" => 1]);
            $this->assign('cate_list', $cateList);

            return $this->fetch('add');
        } else {
            $post = input();
            unset($post['redirect']);
            $where['id'] = $post['id'];
            $post['coupon_start_time'] = strtotime(input("coupon_start_time"));
            $post['coupon_end_time'] = strtotime(input("coupon_end_time"));
            $result = $goodsService->update($where, $post);
            if($result === false){
                $this->error('编辑失败');
            }
            alog("taoke.goods.edit", '编辑商品 ID：'.$post['id']);
            $this->success('编辑成功', $result);
        }
    }

    public function changeStatus()
    {
        $this->checkAuth('taokegoods:goods:update');
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择记录');
        $status = input('status');
        if (!in_array($status, ['0', '1'])) $this->error('状态值不正确');
        $goodsService = new \app\taokegoods\service\Goods();
        $num = $goodsService->update(["id" =>$ids ], ['status' => $status]);
        if (!$num) $this->error('切换状态失败');
        alog("taoke.goods.edit", '编辑商品 ID：'.implode(",", $ids)." 修改状态：".($status == 1 ? "启用" : "禁用"));
        $this->success('切换成功');
    }

    public function top()
    {
        $this->checkAuth('taokegoods:goods:top');
        $goodsService = new \app\taokegoods\service\Goods();
        $ids = get_request_ids();
        $where['id'] = $ids;
        $data['is_top'] = 1;
        $result = $goodsService->update($where, $data);
        if($result === false){
            $this->error('置顶失败');
        }
        alog("taoke.goods.edit", '编辑商品 ID：'.implode(",", $ids)." 修改置顶状态");
        $this->success('置顶成功', $result);
    }

    public function changeRecommandStatus()
    {
        $this->checkAuth('taokegoods:goods:recommand');
        $goodsService = new \app\taokegoods\service\Goods();
        $ids = get_request_ids();
        $where['id'] = $ids;
        $data['is_recommand'] = 1;
        $result = $goodsService->update($where, $data);
        if($result === false){
            $this->error('推荐失败');
        }
        alog("taoke.goods.edit", '编辑商品 ID：'.implode(",", $ids)." 修改推荐状态");
        $this->success('推荐成功', $result);
    }
    
    public function cancleTop()
    {
        $this->checkAuth('taokegoods:goods:top');
        $goodsService = new \app\taokegoods\service\Goods();
        $ids = get_request_ids();
        $where['id'] = $ids;
        $data['is_top'] = 0;
        $result = $goodsService->update($where, $data);
        if($result === false){
            $this->error('取消置顶失败');
        }
        alog("taoke.goods.edit", '编辑商品 ID：'.implode(",", $ids)." 取消置顶状态");
        $this->success('取消置顶成功', $result);
    }

    /**
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function del()
    {
        $this->checkAuth('taokegoods:goods:delete');
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择商品');
        $goodsService = new \app\taokegoods\service\Goods();
        $where["id"] = $ids;

        $liveGoods = new LiveGoods();
        $total = $liveGoods->getTotal(["goods_id"=>$ids]);
        if($total > 0){
            $this->error('删除失败，商品正在被直播');
        }
        $num = $goodsService->del($where);
        if (!$num) $this->error('删除失败');
        alog("taoke.goods.del", '删除商品 ID：'.implode(",", $ids));
        $this->success("删除成功，共计删除{$num}条记录");
    }

    /**
     * 获取商品信息
     */
    public function getItem()
    {
        if (Request::isPost()) {
            $params = [];
            $itemUrl = input('item_url');
            $httpClient = new HttpClient();
            $params['appkey'] = config('app.system_deploy')['taoke_api_key'];
            $params['content'] = $itemUrl;
            $result = $httpClient->post(TK_URL."Tool/goods/analysisUrl", $params)->getData('json');
            if($result['code'] == 200){
                $info = $result['result'];
                if(empty($info)){
                    $this->error('获取失败');
                }
                $goodsService = new \app\taokegoods\service\Goods();
                $exist = $goodsService->getGoodsInfo(["goods_id"=>$info['goods_id']]);
                if($exist){
                    $this->error('此商品已存在');
                }
                $this->success('获取成功', $info);
            }else{
                $this->error('获取失败');
            }
        }
    }

    public function syncGoods(){
        $rabbitChannel = new RabbitMqChannel(['goods.add_goods']);
        $rabbitChannel->exchange('main')->sendOnce('goods.add.process', ['page' => 1]);
        $this->success('正在同步');
    }
}
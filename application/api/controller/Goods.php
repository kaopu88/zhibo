<?php
/**
 * 开播前商品添加控制类
 * User: zack
 * Date: 2020/9/2 0002
 * Time: 下午 8:02
 */

namespace app\api\controller;

use app\common\controller\UserController;
use bxkj_common\RedisClient;
use \bxkj_module\service\User;
use think\Exception;

class Goods extends UserController
{
    protected $redis;
    protected $config;
    protected static $liveGoods = 'live_goods_pre:';

    public function __construct()
    {
        parent::__construct();
        $this->redis = RedisClient::getInstance();
        $this->config = config('app.live_setting');
        $this->config['goods_max_num'] = $this->config['goods_max_num']?: 10;
    }

    /**
     * 开播前已经添加的商品列表
     */
    public function getList()
    {
        try {
            $params = request()->param();
            $user = $this->user;
            $page = $params['page']?: 1;
            $pageSize = $params['pageSize']?: PAGE_LIMIT;
            apiAsserts ($user['taoke_shop'] != 1, '未开通小店权限');
            $goodService = new \app\api\service\Goods();
            $goodsList = $goodService->getLiveList(['user_id' => $user['user_id']], $page, $pageSize, "live_status DESC,top_time DESC,add_time DESC");
        } catch (Exception $e) {
            return $this->jsonError($e->getMessage());
        }

        return $this->success($goodsList, '获取成功');
    }

    /**
     * 将商品添加到待直播商品中
     */
    public function add()
    {
        try {
            $params = request()->param();
            $type = $params['type'] ? $params['type'] : 0;
            $goodsType = $params['type'] ? 'shop' : 'taoke';
            $goodsIds = $params['goods_id'];
            $user = $this->user;
            $goodsKey = self::$liveGoods . 'goods:goodsid:' . $goodsType . $user['user_id'];
            apiAsserts($user['is_anchor'] == '0', '您还未开通主播');
            apiAsserts(empty($this->config['is_goods_open']), '平台暂未开启开播前添加商品');
            $count = $this->redis->sCard($goodsKey);
            apiAsserts($this->config['goods_max_num'] <= $count, '最多只能添加' . $this->config['goods_max_num'] . '个商品');

            $goodService = new \app\api\service\Goods();
            $goodsRes = $goodService->getGoods($type, $goodsIds);
            apiAsserts($goodsRes['code'] != 200, $goodsRes['msg']);
            $good = $goodsRes['goods'];
            $siteId = isset($params['site_id']) ? $params['site_id'] : 0;
            $goodsAdd = $goodService->addGoods($good, $type, $siteId);
            apiAsserts($goodsAdd['code'] != 200, $goodsAdd['msg']);
        } catch (Exception $e) {
            return $this->jsonError($e->getMessage());
        }

        $this->redis->sAdd($goodsKey, $goodsIds);
        return $this->success($goodsRes['goods'], '添加成功');
    }

    /**
     * 移除添加过的商品
     */
    public function deleteGoods()
    {
        $params = request()->param();
        $type = $params['type'] ? $params['type'] : 0;
        $goodsType = $type ? 'shop' : 'taoke';
        $goodsIds = $params['goods_id'];
        $user = $this->user;
        $goodsKey = self::$liveGoods . 'goods:goodsid:' . $goodsType . $user['user_id'];
        $goodsHas = $this->redis->sIsMember($goodsKey, $goodsIds);
        $goodService = new \app\api\service\Goods();

        if ($goodsHas) {
            $goodService->delGoods($goodsIds, $type);
            $this->redis->sRem($goodsKey, $goodsIds);
        }

        return $this->success($goodsIds, '删除成功');
    }

    /**
     * 设置卖点
     */
    public function sellGoods()
    {
        $params = request()->param();
        $type = $params['type'] ? $params['type'] : 0;
        $goodsType = $params['type'] ? 'shop' : 'taoke';
        $goodsIds = $params['goods_id'];
        $content = $params['content'];
        $user = $this->user;
        $goodsKey = self::$liveGoods . 'goods:goodsid:' . $goodsType . $user['user_id'];
        $goodService = new \app\api\service\Goods();
        try {
            apiAsserts(empty($content), '卖点设置不能为空');
            apiAsserts(mb_strlen($content, 'UTF8') > 50, '卖点字数过多');
            $goodsHas = $this->redis->sIsMember($goodsKey, $goodsIds);
            apiAsserts(!$goodsHas, '请先添加商品');
            $res = $goodService->updateGoods($goodsIds, $goodsType, ['content' => $content]);
            apiAsserts (!$res, '操作失败');
        } catch (Exception $e) {
            return $this->jsonError($e->getMessage());
        }

        return $this->success($goodsIds, '设置成功');
    }

    /**
     * 置顶商品
     */
    public function topGoods()
    {
        $params = request()->param();
        $type = $params['type'] ? $params['type'] : 0;
        $goodsType = $params['type'] ? 'shop' : 'taoke';
        $goodsIds = $params['goods_id'];
        $user = $this->user;
        $goodsKey = self::$liveGoods . 'goods:goodsid:' . $goodsType . $user['user_id'];
        $goodService = new \app\api\service\Goods();
        try {
            apiAsserts (empty($goodsIds), '非法操作');
            $goodsHas = $this->redis->sIsMember($goodsKey, $goodsIds);
            apiAsserts(!$goodsHas, '请先添加商品');
            $res = $goodService->updateGoods($goodsIds, $goodsType, ['top_time' => time()]);
            apiAsserts (!$res, '操作失败');
        } catch (Exception $e) {
            return $this->jsonError($e->getMessage());
        }

        return $this->success($goodsIds, '置顶成功');
    }

    /**
     * 用户获取直播间商品列表
     */
    public function getLiveList()
    {
        try {
            $params = request()->param();
            $user = $this->user;
            $page = $params['page'] ?: 1;
            $pageSize = isset($params['pageSize'] ) ? $params['pageSize'] : PAGE_LIMIT;
            $roomId = $params['room_id'];
            $anchorId = $params['anchor_id'];
            apiAsserts (empty($roomId), '房间id不能为空');
            apiAsserts (empty($anchorId), '主播id不能为空');
            $where['room_id'] = $roomId;
            $where['user_id'] = $anchorId;
            $goodService = new \app\api\service\Goods();
            $goodsList = $goodService->getRoomLiveList($where, $page, $pageSize, "live_status DESC,top_time DESC,add_time desc,sort DESC");
        } catch (Exception $e) {
            return $this->jsonError($e->getMessage());
        }

        return $this->success($goodsList, '获取成功');
    }
}
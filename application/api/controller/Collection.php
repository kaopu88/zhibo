<?php

namespace app\api\controller;


use app\common\controller\UserController;
use think\Db;

class Collection extends UserController
{
    /**
     * 我的收藏
     * @desc 个人收藏主页数据
     * @return array
     */
    public function ownList()
    {
        $params = request()->param();

        $offset = $params['offset'] ? $params['offset'] : 0;

        $length = $params['length'] ? $params['length'] : 10;

        $className = 'app\\api\\service\\'.$params['type'].'\\Favorite';

        $favorite = new $className();

        $res = $favorite->listByFavorite(USERID, $offset, $length);

        if (!empty($res) && is_array($res))
        {
            $res = array_map(function ($value){
                $value['is_collect'] = 1;
                return $value;
            }, $res);
        }

        return $this->success($res ? $res : [], '获取成功');
    }


    /**
     * 其它人的收藏列表
     */
    public function otherList()
    {
        $params = request()->param();

        $start =  empty((int)$params['p']) ? 0 : ($params['p']-1)*PAGE_LIMIT;

        $user_id = $params['user_id'];

        $className = 'app\\api\\service\\'.$params['type'].'\\Favorite';

        $favorite = new $className();

        $res = $favorite->listByFavorite($user_id, $start, PAGE_LIMIT);

        return $this->success($res ? $res : [], '获取成功');
    }


    /**
     * 添加收藏
     * @desc 收藏自已喜欢的视频
     */
    public function add()
    {
        $params = request()->param();

        $target_id = $params['target_id'];

        $className = 'app\\api\\service\\'.$params['type'].'\\Favorite';

        $favorite = new $className();

        $res = $favorite->isFavorite($target_id) ? $favorite->remove($target_id) : $favorite->add($target_id);

        if (!$res) return $this->jsonError('收藏失败');

        return $this->success(['status' => $res['status']], '收藏成功');
    }


    /**
     * 删除收藏
     * @return \App\Common\BuguCommon\BaseError|array|\bxkj_common\BaseError
     */
    public function clean()
    {
        $params = request()->param();

        $targets = $params['targets'] ? explode(',', $params['targets']) : [];

        if (empty($targets)) return [];

        $className = 'app\\api\\service\\'.$params['type'].'\\Favorite';

        $favorite = new $className();

        $res = $favorite->delete($targets);

        if (!$res) return $this->jsonError('删除收藏失败');

        return $this->success([], '删除收藏成功');
    }
}

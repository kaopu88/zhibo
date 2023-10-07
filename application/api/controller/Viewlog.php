<?php
/**
 * Created by PhpStorm.
 * User: 李成
 * Date: 2020/5/5
 * Time: 20:13
 */
namespace app\api\controller;

use app\common\controller\UserController;
use app\taoke\service\ViewLog as log;

class Viewlog extends UserController
{
    /**
     * 获取浏览记录
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getLogList()
    {
        $viewLog = new log();
        $params = request()->param();
        $page = isset($params['page']) ? $params['page'] : 1;
        $pageSize = isset($params['pageSize']) ? $params['pageSize'] : 10;
        $offset = ($page-1)*$pageSize;
        $cateList = $viewLog->getList($params, $offset, $pageSize);
        return $this->success($cateList, '获取成功');
    }

    /**
     * 删除浏览记录
     * @return \think\response\Json
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function delLog()
    {
        $params = request()->param();
        $where['user_id'] = USERID;
        $ids = $params['ids'];
        $ids = trim($ids, ",");
        if(strpos($ids, ",") === false){
            $where['id'] = $ids;
        }else{
            $where[] = ['id', "in", $ids];
        }
        $viewLog = new log();
        $status = $viewLog->deleteLog($where);
        if($status){
            return $this->jsonSuccess("", '删除成功');
        }else{
            return $this->jsonError("删除失败");
        }
    }
}
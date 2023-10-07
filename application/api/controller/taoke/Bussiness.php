<?php
/**
 * Created by PhpStorm.
 * User: 李成
 * Date: 2020/5/27
 * Time: 11:41
 */
namespace app\api\controller\taoke;

use app\common\controller\Controller;
use app\taoke\service\BussinessCate;

class Bussiness extends Controller
{
    /**
     * 获取商学院分类
     * @return \think\response\Json
     */
    public function getCateList()
    {
        $cateList = [];
        $bussiness = new BussinessCate();
        $total = $bussiness->getTotal(["status" => 1]);
        $cateList = $bussiness->getList(["status" => 1], 0, $total);
        return $this->jsonSuccess($cateList, "获取成功");
    }

    /**
     * 获取商学院列表
     * @return \think\response\Json
     */
    public function getList()
    {
        $list = [];
        $params = request()->param();
        $cateId = empty($params["cate_id"]) ? 0 : $params["cate_id"];
        $page = empty($params["page"]) ? 1 : $params["page"];
        $pageSize = empty($params["pageSize"]) ? 10 : $params["pageSize"];

        $bussiness = new \app\taoke\service\Bussiness();
        $offset = ($page-1)*$pageSize;
        $list = $bussiness->getList(["cate_id" => $cateId, "status" => 1],$offset, $pageSize);
        return $this->jsonSuccess($list, "获取成功");
    }

    /**
     * 获取文字详情
     * @return \think\response\Json
     */
    public function getInfo()
    {
        $info = [];
        $params = request()->param();
        $id = $params["id"];
        if(empty($id)){
            return $this->jsonError("文章id不能为空");
        }
        $bussiness = new \app\taoke\service\Bussiness();
        $info = $bussiness->getInfo(["id" => $id]);
        if($info) {
            $bussiness->updateInfo(["id" => $id], ["view_num" => ($info['view_num']+1)]);
            return $this->jsonSuccess($info, "获取成功");
        }else{
            return $this->jsonError("文章不存在");
        }
    }

}
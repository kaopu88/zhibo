<?php
/**
 * Created by PhpStorm.
 * User: 李成
 * Date: 2020/5/19
 * Time: 17:50
 */
namespace app\api\controller\taoke;

use app\common\controller\Controller;
use bxkj_common\HttpClient;

class Cate extends Controller
{
    /**
     * 获取一级分类
     * @return \think\response\Json
     */
    public function getCateList()
    {
        $cateList = [];
        $params = request()->param();
        $type = $params["type"];
        $httpClient = new HttpClient();
        if ($type == "P") {
            $para['api_key'] = config('app.system_deploy')['taoke_api_key'];
            $para['parebt_cate_id'] = 0;
            $result = $httpClient->post(TK_URL . "Cate/getPddCateList", $para)->getData('json');
            if ($result['code'] == 200) {
                $cateList = $result['result'];
            } else {
                return $this->jsonError("获取失败");
            }
            array_unshift($cateList, ["name" => "全部", "cate_id" => 0]);
        } elseif ($type == "J") {
            $para['api_key'] = config('app.system_deploy')['taoke_api_key'];
            $para['parent_id'] = 0;
            $para['grade'] = 0;
            $result = $httpClient->post(TK_URL . "Cate/getJdCateList", $para)->getData('json');
            if ($result['code'] != 200) {
                return $this->jsonError("获取失败");
            }
            foreach ($result['result'] as $value) {
                $data['cate_id'] = $value['id'];
                $data['name'] = $value['name'];
                $cateList[] = $data;
            }
            array_unshift($cateList, ["name" => "全部", "cate_id" => 0]);
        } else {
            $cate = new \app\taokegoods\service\Cate();
            $cateList = $cate->getAllCate(["status" => 1]);
        }
        return $this->jsonSuccess($cateList, "获取成功");
    }

    /**
     * 获取二级分类
     * @return \think\response\Json
     */
    public function getSecCateList()
    {
        $secCatesList = [];
        $params = request()->param();
        $cateId = $params["cate_id"];
        if (empty($cateId)) {
            return $this->jsonError("cate_id不能为空");
        }
        $cate = new \app\taokegoods\service\Cate();
        $cateInfo = $cate->getInfo(["cate_id" => $cateId, "status" => 1]);
        if ($cateInfo) {
            $params['cid'] = $cateInfo['dtk_cate_id'];
            $httpClient = new HttpClient();
            $params['appkey'] = config('app.system_deploy')['taoke_api_key'];
            $result = $httpClient->post(TK_URL . "Cate/getSubCate", $params)->getData('json');
            if ($result['code'] == 200) {
                $secCatesList = $result['result'];
                return $this->jsonSuccess($secCatesList, "获取成功");
            } else {
                return $this->jsonError($result['msg']);
            }
        } else {
            return $this->jsonError("参数错误");
        }
    }

    /**
     * 获取全部分类--淘宝
     * @return \think\response\Json
     */
    public function getAllCate()
    {
        $cateList = [];
        $cate = new \app\taokegoods\service\Cate();
        $cateList = $cate->getAllCate(["status" => 1]);
        if ($cateList) {
            $httpClient = new HttpClient();
            $params['appkey'] = config('app.system_deploy')['taoke_api_key'];
            foreach ($cateList as $key => $value) {
                $params['cid'] = $value['dtk_cate_id'];
                $result = $httpClient->post(TK_URL . "Cate/getSubCate", $params)->getData('json');
                if ($result['code'] == 200) {
                    $cateList[$key]['sec_cate'] = $result['result'];
                } else {
                    $cateList[$key]['sec_cate'] = [];
                }
            }
        }
        return $this->jsonSuccess($cateList, "获取成功");
    }

    /**
     * 获取拼多多一级分类
     * @return \think\response\Json
     */
    public function getPddCateList()
    {
        $cateList = [];
        $httpClient = new HttpClient();
        $para['api_key'] = config('app.system_deploy')['taoke_api_key'];
        $para['parebt_cate_id'] = 0;
        $result = $httpClient->post(TK_URL . "Cate/getPddCateList", $para)->getData('json');
        if ($result['code'] == 200) {
            $cateList = $result['result'];
        } else {
            return $this->jsonError("获取失败");
        }
        array_unshift($cateList, ["name" => "全部", "cate_id" => 0]);
        return $this->jsonSuccess($cateList, "获取成功");
    }

    /**
     * 获取京东分类
     * @return \think\response\Json
     */
    public function getJdCateList()
    {
        $cateList = [];
        $httpClient = new HttpClient();
        $result = $httpClient->post(TK_URL . "Cate/getJdCateList", ["parent_id" => 0, "grade" => 0])->getData('json');
        if ($result['code'] == 200) {
            $cateList = $result['result'];
        } else {
            return $this->jsonError("获取失败");
        }
        array_unshift($cateList, ["grade" => 0, "name" => "全部", "id" => 0, "parentId" => 0]);
        return $this->jsonSuccess($cateList, "获取成功");
    }

}

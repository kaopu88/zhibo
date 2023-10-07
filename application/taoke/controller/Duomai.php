<?php
/**
 * Created by PhpStorm.
 * User: 李成
 * Date: 2020/6/3
 * Time: 11:52
 */
namespace app\taoke\controller;

use app\admin\service\SysConfig;
use bxkj_common\HttpClient;
use think\facade\Request;

class Duomai extends Controller
{
    public function index()
    {
        $this->checkAuth('taoke:duomai:index');

        $get = input();
        $adService = new \app\taoke\service\Duomai();
        $total = $adService->getTotal($get);
        $page = $this->pageshow($total);
        $list = $adService->getList($get, $page->firstRow, $page->listRows);
        $this->assign('_list', $list);
        return $this->fetch();
    }

    public function add()
    {
        $this->checkAuth('taoke:duomai:add');
        if (Request::isGet()) {
            return $this->fetch();
        } else {
            $adService = new \app\taoke\service\Duomai();
            $post = input();
            $adid = $post['ads_id'];
            if(empty($adid)){
                $this->error('ads_id不能为空');
            }
            $adInfo = $adService->getInfo(["ads_id" => $adid]);
            if($adInfo){
                $this->error('此广告计划已添加');
            }
            $data['ads_id'] = $adid;
            $data['ads_name'] = $post['ads_name'];
            $data['alpha'] = $this->getFirstCharter($post['ads_name']);
            $data['cate_id'] = $post['cate_id'];
            $data['cate_name'] = $post['cate_name'];
            $data['ads_endtime'] = strtotime($post['ads_endtime']);
            $data['ads_commission'] = $post['ads_commission'];
            $data['site_url'] = $post['site_url'];
            $data['site_logo'] = $post['site_logo'];
            $data['site_description'] = $post['site_description'];
            $data['adser'] = $post['adser'];
            $data['charge_period'] = $post['charge_period'];
            $data['deep_link'] = $post['deep_link'];
            $data['status'] = $post['status'];
            $result = $adService->add($data);
            if($result === false){
                $this->error('新增失败');
            }
            alog("taoke.duomai.add", "新增多麦广告 ID：".$result);
            $this->success('新增成功', $result);
        }
    }

    public function edit()
    {
        $this->checkAuth('taoke:duomai:update');
        $adService = new \app\taoke\service\Duomai();
        if (Request::isGet()) {
            $id = input('id');
            $info = $adService->getInfo(["id" => $id]);
            if (empty($info)) $this->error('记录不存在');
            $this->assign('_info', $info);
            return $this->fetch('add');
        } else {
            $post = input();
            $where['id'] = $post['id'];
            $adid = $post['ads_id'];
            if(empty($adid)){
                $this->error('ads_id不能为空');
            }
            $adInfo = $adService->getInfo(["ads_id" => $adid]);
            if(empty($adInfo)){
                $this->error('此广告计划不存在');
            }
            $data['ads_id'] = $adid;
            $data['alpha'] = $this->getFirstCharter($post['ads_name']);
            $data['ads_name'] = $post['ads_name'];
            $data['cate_id'] = $post['cate_id'];
            $data['cate_name'] = $post['cate_name'];
            $data['ads_endtime'] = strtotime($post['ads_endtime']);
            $data['ads_commission'] = $post['ads_commission'];
            $data['site_url'] = $post['site_url'];
            $data['site_logo'] = $post['site_logo'];
            $data['site_description'] = $post['site_description'];
            $data['adser'] = $post['adser'];
            $data['charge_period'] = $post['charge_period'];
            $data['deep_link'] = $post['deep_link'];
            $data['status'] = $post['status'];
            $result = $adService->updateInfo($where, $data);
            if($result === false){
                $this->error('编辑失败');
            }
            alog("taoke.duomai.edit", "编辑多麦广告 ID：".$post['id']);
            $this->success('编辑成功', $result);
        }
    }

    public function del()
    {
        $this->checkAuth('taoke:duomai:delete');
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择记录');
        $adtService = new \app\taoke\service\Duomai();
        $where[] = ['id', "in", $ids];
        $num = $adtService->del($where);
        if (!$num) $this->error('删除失败');
        alog("taoke.duomai.del", "删除多麦广告 ID：".implode(",", $ids));
        $this->success("删除成功，共计删除{$num}条记录");
    }

    public function changeStatus()
    {
        $this->checkAuth('taoke:duomai:update');
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择记录');
        $status = input('status');
        if (!in_array($status, ['0', '1'])) $this->error('状态值不正确');
        $adService = new \app\taoke\service\Duomai();
        $num = $adService->updateInfo(["id" => $ids], ['status' => $status]);
        if (!$num) $this->error('切换状态失败');
        alog("taoke.duomai.edit", "编辑多麦广告 ID：".implode(",", $ids)."<br>切换状态 ：".($status == 1 ? "启用" : "禁用"));
        $this->success('切换成功');
    }

    public function setTop()
    {
        $this->checkAuth('taoke:duomai:update');
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择记录');
        $isTop = input('is_top');
        if (!in_array($isTop, ['0', '1'])) $this->error('状态值不正确');
        $adService = new \app\taoke\service\Duomai();
        $num = $adService->updateInfo(["id" => $ids], ['is_top' => $isTop]);
        if (!$num) $this->error('切换状态失败');
        alog("taoke.duomai.edit", "编辑多麦广告 ID：".implode(",", $ids)."<br>切换置顶状态 ：".($isTop == 1 ? "置顶" : "普通"));
        $this->success('切换成功');
    }

    public function getAdInfo()
    {
        if (Request::isPost()) {
            $adsid = input('ads_id');
            if(empty($adsid)){
                $this->error('广告id不能为空');
            }
            $para['ads_id'] = $adsid;
            $sysConfig = new SysConfig();
            $adConfig = $sysConfig->getConfig("duomaiAds");
            $adConfig = json_decode($adConfig['value'], true);
            $hash = $adConfig['hash'];
            $para['hash'] = $hash;
            $http = new HttpClient();
            $para['api_key'] = config('app.system_deploy')['taoke_api_key'];
            $result = $http->post(TK_URL."Ads/getAdDetail", $para)->getData('json');
            if($result['code'] == 200){
                $this->success("查询成功", $result['result']);
            }else{
                $this->error('查询失败');
            }
        }
    }

    /**
     * 同步多麦广告
     */
    public function syncExpended()
    {
        $sysConfig = new SysConfig();
        $adConfig = $sysConfig->getConfig("duomaiAds");
        $adConfig = json_decode($adConfig['value'], true);
        $hash = $adConfig['hash'];
        $para['hash'] = $hash;
        $http = new HttpClient();
        $para['api_key'] = config('app.system_deploy')['taoke_api_key'];
        $result = $http->post(TK_URL."Ads/getDuomaiAds", $para)->getData('json');
        if($result['code'] == 200){
            $adService = new \app\taoke\service\Duomai();
            if($result['result']){
                foreach($result['result'] as $key => $value){
                    $info = [];
                    $endTime = strtotime($value['ads_endtime']);
                    if($endTime <= time()){
                        continue;
                    }
                    $info = $adService->getInfo(["ads_id" => $value['ads_id']]);
                    $data['ads_name'] = str_replace("CPS推广", "", $value['ads_name']);
                    $data['cate_id'] = $value['cate_id'];
                    $data['alpha'] = $this->getFirstCharter($value['ads_name']);
                    $data['cate_name'] = $value['cate_name'];
                    $data['ads_endtime'] = $endTime;
                    $data['ads_commission'] = $value['ads_commission'];
                    $data['site_url'] = $value['site_url'];
                    $data['site_logo'] = $value['site_logo'];
                    $data['site_description'] = $value['site_description'];
                    $data['adser'] = $value['adser'];
                    $data['charge_period'] = $value['charge_period'];
                    $data['deep_link'] = isset($value['deep_link']) ? $value['deep_link'] : "";
                    if($info){
                        $adService->updateInfo(["ads_id" => $value['ads_id']], $data);
                    }else{
                        $data['ads_id'] = $value['ads_id'];
                        $adService->add($data);
                    }
                }
                $this->success("同步成功");
            }else{
                $this->error('您还未申请多麦广告计划');
            }
        }else{
            $this->error('同步失败');
        }
    }

    /**
     * 删除过期广告计划
     */
    public function delExpired()
    {
        $this->checkAuth('taoke:duomai:delete');
        $adtService = new \app\taoke\service\Duomai();
        $where = 'ads_endtime <= '. time();
        $num = $adtService->del($where);
        if (!$num) $this->error('删除失败');
        alog("taoke.duomai.del", "清除过期多麦广告");
        $this->success("删除成功，共计删除{$num}条记录");
    }

    protected function getFirstCharter($str){
        if(empty($str)){return '';}
        $fchar = ord($str{0});
        if($fchar >= ord('A')&&$fchar <= ord('z')) return strtoupper($str{0});
        $s1 = iconv('UTF-8','gb2312',$str);
        $s2 = iconv('gb2312','UTF-8',$s1);
        $s = $s2 == $str ? $s1 : $str;
        $asc = ord($s{0}) * 256 + ord($s{1}) - 65536;
        if($asc >= -20319 && $asc <= -20284) return 'A';
        if($asc >= -20283 && $asc <= -19776) return 'B';
        if($asc >= -19775 && $asc <= -19219) return 'C';
        if($asc >= -19218 && $asc <= -18711) return 'D';
        if($asc >= -18710 && $asc <= -18527) return 'E';
        if($asc >= -18526 && $asc <= -18240) return 'F';
        if($asc >= -18239 && $asc <= -17923) return 'G';
        if($asc >= -17922 && $asc <= -17418) return 'H';
        if($asc >= -17417 && $asc <= -16475) return 'J';
        if($asc >= -16474 && $asc <= -16213) return 'K';
        if($asc >= -16212 && $asc <= -15641) return 'L';
        if($asc >= -15640 && $asc <= -15166) return 'M';
        if($asc >= -15165 && $asc <= -14923) return 'N';
        if($asc >= -14922 && $asc <= -14915) return 'O';
        if($asc >= -14914 && $asc <= -14631) return 'P';
        if($asc >= -14630 && $asc <= -14150) return 'Q';
        if($asc >= -14149 && $asc <= -14091) return 'R';
        if($asc >= -14090 && $asc <= -13319) return 'S';
        if($asc >= -13318 && $asc <= -12839) return 'T';
        if($asc >= -12838 && $asc <= -12557) return 'W';
        if($asc >= -12556 && $asc <= -11848) return 'X';
        if($asc >= -11847 && $asc <= -11056) return 'Y';
        if($asc >= -11055 && $asc <= -10247) return 'Z';
        return "";
    }
}
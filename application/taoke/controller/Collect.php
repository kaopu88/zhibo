<?php
/**
 * Created by PhpStorm.
 * User: 李成
 * Date: 2020/5/6
 * Time: 8:57
 */
namespace app\taoke\controller;

use think\Db;

class Collect extends Controller
{
    /**
     * 列表
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function index()
    {
        $this->checkAuth('taoke:collect:index');

        $get = input();
        $collectService = new \app\taoke\service\Collect();
        $total = $collectService->getTotal($get);
        $page = $this->pageshow($total);
        $list = $collectService->getList($get, $page->firstRow, $page->listRows);
        if($list){
            foreach ($list as $key => $value){
                $userInfo = Db::name("user")->field("nickname")->where(["user_id" => $value['user_id']])->find();
                $list[$key]['nickname'] = $userInfo['nickname'];
            }
        }
        $this->assign('_list', $list);
        return $this->fetch();
    }

    /**
     * 删除
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function del()
    {
        $this->checkAuth('taoke:collect:delete');
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择记录');
        $collectService = new \app\taoke\service\Collect();
        $where[] = ['id', "in", $ids];
        $num = $collectService->cancleCollect($where);
        if (!$num) $this->error('删除失败');
        alog("taoke.common.del_collect", "删除淘客收藏记录 ID：".implode(",", $ids));
        $this->success("删除成功，共计删除{$num}条记录");
    }
}
<?php
/**
 * Created by PhpStorm.
 * User: 李成
 * Date: 2020/5/6
 * Time: 8:57
 */
namespace app\taoke\controller;

use think\Db;
use think\facade\Request;

class Withdraw extends Controller
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
        $this->checkAuth('taoke:withdraw:index');

        $get = input();
        $withService = new \app\taoke\service\Withdraw();
        $total = $withService->getTotal($get);
        $page = $this->pageshow($total);
        $list = $withService->getList($get, $page->firstRow, $page->listRows);
        $this->assign('_list', $list);
        return $this->fetch();
    }

    public function update()
    {
        if (Request::isGet()) {
            $id = input('id');
            if (empty($id)) $this->error('请选择提现记录');
            $cashRes  = Db::name('user_withdraw')->where('id', $id)->find();;
            if (empty($cashRes)) $this->error('提现记录不存在');
            return json_success($cashRes, '获取成功');
        } else {
            $post = Request::post();
            $withService = new \app\taoke\service\Withdraw();
            $num = $withService->update($post);
            if ($num['code'] != 200) $this->error($num['msg']);
            $this->success('操作成功', [
                'next' => [],
                'last_id' => $post['id']
            ]);
        }
    }

}
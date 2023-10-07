<?php
namespace app\admin\controller;

use think\Db;
use think\facade\Request;

class Gift extends Controller
{
    public function index()
    {
        $this->checkAuth('admin:gift:select');
        $giftService = new \app\admin\service\Gift();
        $get = input();
        $total = $giftService->getTotal($get);
        $page = $this->pageshow($total);
        $list = $giftService->getList($get, $page->firstRow, $page->listRows);
        $this->assign('_list', $list);
        return $this->fetch();
    }

    public function find()
    {
        $this->checkAuth('admin:gift:select');
        $giftService = new \app\admin\service\Gift();
        $get = input();
        $total = $giftService->getTotal($get);
        $page = $this->pageshow($total);
        $list = $giftService->getList($get, $page->firstRow, $page->listRows);
        $this->assign('_list', $list);
        return $this->fetch();
    }

    public function get_suggests()
    {
        $giftService = new \app\admin\service\Gift();
        $result = $giftService->getSuggests(input('keyword'));
        return json_success($result ? $result : []);
    }

    public function add()
    {
        $this->checkAuth('admin:gift:add');
        if (Request::isGet()) {
            return $this->fetch();
        } else {
            $musicService = new \app\admin\service\Gift();
            $post = input();
            $post['file'] = $post['file_path'];
            $result = $musicService->add($post);
            if (!$result) $this->error($musicService->getError());
            alog("live.gift.add", '新增礼物 ID：'.$result);
            $this->success('新增成功', $result);
        }
    }

    public function edit()
    {
        $this->checkAuth('admin:gift:update');
        if (Request::isGet()) {
            $id = input('id');
            $info = Db::name('gift')->where('id', $id)->find();
            if (empty($info)) $this->error('礼物不存在');
            $this->assign('_info', $info);
            return $this->fetch('add');
        } else {
            $giftService = new \app\admin\service\Gift();
            $post = input();
            $post['file'] = $post['file_path'];
            $result = $giftService->update($post);
            if (!$result) $this->error($giftService->getError());
            alog("live.gift.edit", '编辑礼物 ID：'.$post['id']);
            $this->success('编辑成功', $result);
        }
    }

    public function delete()
    {
        $this->checkAuth('admin:gift:delete');
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择记录');
        $giftService = new \app\admin\service\Gift();
        $num = $giftService->delete($ids);
        if (!$num) $this->error($giftService->getError());
        alog("live.gift.del", '删除礼物 ID：'.implode(",", $ids));
        $this->success("删除成功，共计删除{$num}条记录",'','gift/index');
    }

    public function change_status()
    {
        $this->checkAuth('admin:gift:update');
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择记录');
        $status = input('status');
        if (!in_array($status, ['0', '1'])) $this->error('状态值不正确');
        $giftService = new \app\admin\service\Gift();
        $num = $giftService->changeStatus($ids, $status);
        if (!$num) $this->error('切换状态失败');
        alog("live.gift.edit", '编辑礼物 ID：'.implode(",", $ids)." 修改状态：".($status == 1 ? "启用" : "禁用"));
        $this->success('切换成功');
    }

    public function change_guard()
    {
        $this->checkAuth('admin:gift:update');
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择记录');
        $status = input('isguard');
        if (!in_array($status, ['0', '1'])) $this->error('状态值不正确');
        $giftService = new \app\admin\service\Gift();
        $num = $giftService->changeGuard($ids, $status);
        if (!$num) $this->error('切换状态失败');
        $giftService->getAllGuard();
        alog("live.gift.edit", '编辑礼物 ID：'.implode(",", $ids)." 修改守护属性：".($status == 1 ? "是" : "否"));
        $this->success('切换成功');
    }

    public function update_badge()
    {
        $this->checkAuth('admin:gift:update');
        $gift_id = input('gift_id');
        if (empty($gift_id)) $this->error('请选择礼物');
        $gift_badge = Db::name('gift_badge')->where('gift_id', $gift_id)->find();
        if (Request::isGet()) {
            $this->success('获取成功', [
                'gift_id' => $gift_id,
                'icon' => !empty($gift_badge) ? $gift_badge['icon'] : ''
                ]);
        } else {
            $post = Request::post();
            if (empty($post['icon'])) $this->error('请上传角标');

            $data['icon'] = $post['icon'];
            if (empty($gift_badge)) {
                $data['gift_id'] = $gift_id;
                $res = Db::name('gift_badge')->insert($data);
            } else {
                $res = Db::name('gift_badge')->where('gift_id', $gift_id)->update($data);
            }
            $this->success('保存成功');
        }
    }

}
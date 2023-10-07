<?php
/**
 * Created by PhpStorm.
 * User: zack
 * Date: 2020/8/8 0008
 * Time: 下午 2:58
 */

namespace app\admin\controller;

use bxkj_common\RedisClient;
use think\Db;
use think\facade\Request;

class Poster extends Controller
{
    public function index()
    {
        $this->checkAuth('admin:poster:index');
        $get = input();
        $agentWithdrawalService = new \app\admin\service\UserPoster();
        $total = $agentWithdrawalService->getTotal($get);
        $page = $this->pageshow($total);
        $list = $agentWithdrawalService->getList($get, $page->firstRow, $page->listRows);
        $this->assign('_list', $list);
        return $this->fetch();
    }

    public function change_status()
    {
        $this->checkAuth('admin:poster:index');
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择记录');
        $status = input('status');
        if (!in_array($status, ['0', '1'])) $this->error('状态值不正确');
        $userPosterService = new \app\admin\service\UserPoster();
        $num = $userPosterService->changeStatus($ids, $status);
        if (!$num) $this->error('切换状态失败');
        alog("live.poster.edit", '编辑海报 ID：' . implode(",", $ids) . " 修改状态：" . ($status == 1 ? "启用" : "禁用"));
        $this->success('切换成功');
    }

    public function edit()
    {
        $this->checkAuth('admin:poster:edit');
        if (Request::isGet()) {
            $id = input('id');
            $info = Db::name('user_poster')->where('id', $id)->find();
            if (empty($info)) return $this->error('海报不存在');
            $info['data'] = json_decode($info['data'], true);
            $info['data']['width'] = round($info['data']['width'] / 2) ?: 0;
            $info['data']['height'] = round($info['data']['height'] / 2) ?: 0;
            $info['data']['fontwidth'] = round($info['data']['fontwidth'] / 2) ?: 0;
            $info['data']['fontheight'] = round($info['data']['fontheight'] / 2) ?: 0;
            $this->assign('_info', $info);
            return $this->fetch('add');
        } else {
            $userPosterService = new \app\admin\service\UserPoster();
            $post = input();
            $post['data'] = json_encode(['width' => $post['width'] * 2, 'height' => $post['height'] * 2, 'fontwidth' => $post['fontwidth'] * 2, 'fontheight' => $post['fontheight'] * 2]);
            unset($post['redirect']);
            unset($post['width']);
            unset($post['height']);
            unset($post['fontwidth']);
            unset($post['fontheight']);

            $result = $userPosterService->update_poster($post);
            if ($result['code'] != 200) return $this->error($result['msg']);

            $redis = RedisClient::getInstance();
            $poster = Db::name('user_poster')->where(['status' => 1])->select();
            $redis->set("poster:bx_live", json_encode($poster));

            alog("live.poster.edit", '编辑海报 ID：' . $post['id']);
            return $this->success('编辑成功', $result);
        }
    }

    public function add()
    {
        $this->checkAuth('admin:poster:edit');
        if (Request::isGet()) {
            return $this->fetch();
        } else {
            $userPosterService = new \app\admin\service\UserPoster();
            $post = input();
            $post['data'] = json_encode(['width' => $post['width'] * 2, 'height' => $post['height'] * 2, 'fontwidth' => $post['fontwidth'] * 2, 'fontheight' => $post['fontheight'] * 2]);
            unset($post['redirect']);
            unset($post['width']);
            unset($post['height']);
            unset($post['fontwidth']);
            unset($post['fontheight']);
            $result = $userPosterService->add($post);
            if ($result['code'] != 200) return $this->error($result['msg']);
            $redis = RedisClient::getInstance();
            $poster = Db::name('user_poster')->where(['status' => 1])->select();
            $redis->set("poster:bx_live", json_encode($poster));
            alog("live.poster.add", '新增海报 ID：' . $result);
            return $this->success('新增成功', $result);
        }
    }

    public function delete()
    {
        $this->checkAuth('admin:poster:edit');
        $ids = get_request_ids();
        if (empty($ids)) return $this->error('请选择记录');
        $num = Db::name('user_poster')->whereIn('id', $ids)->delete();
        if (!$num) return $this->error('删除失败');
        alog("live.poster.del", '删除海报 ID：' . implode(",", $ids));
        return $this->success("删除成功，共计删除{$num}条记录", '', 'poster/index');
    }

    public function upload()
    {
        $this->checkAuth('admin:poster:edit');
        $file = request()->file('erweima_img');
        if ($file) {
            $path = '/static/erweima_back/';
            $info = $file->validate(['size' => 1024000, 'ext' => 'jpg,png'])->move(ROOT_PATH . 'public' . $path);
            if ($info) {
                return $this->success('上传成功', $path . str_replace('\\', '/', $info->getSaveName()));
            } else {
                // 上传失败获取错误信息
                $this->error($file->getError());
            }
        }
    }

    public function clear()
    {
        $this->checkAuth('admin:poster:edit');
        if (Request::isPost()) {
            $redis = RedisClient::getInstance();
            $poster = Db::name('user_poster')->field('id')->where(['status' => 1])->select();
            if (!empty($poster)) {
                foreach ($poster as $key => $value) {
                    $redis->del('user_invite:imgs:' . $value['id']);
                }
            }

            $redis->del("poster:bx_live");
            $path =  \think\facade\Env::get('root_path') . 'public/static/invite_imgs/';
            $tmp = array();
            del_dir(rtrim($path, '/\\'), false);
            alog("live.poster.clear", '清除文件缓存');
            $this->success('清除成功');
        }
    }
}
<?php

namespace app\admin\controller;

use app\admin\service\DataVersion;
use bxkj_module\service\Auth;
use think\Db;
use think\facade\Request;

class Timer extends Controller
{
    protected $urls;

    public function __construct()
    {
        parent::__construct();
        $this->urls = [
            ['name' => '内部核心服务', 'value' => CORE_URL],
            ['name' => 'APP接口服务', 'value' => API_URL],
            //['name' => '微信小程序接口服务', 'value' => WXAPI_URL],
            ['name' => 'H5', 'value' => H5_URL],
           // ['name' => 'NODEJS服务', 'value' => NODE_URL],
            ['name' => 'PUSH服务', 'value' => PUSH_URL]
        ];
    }

    public function index()
    {
        $this->checkAuth('admin:timer:management');
        $get = input();
        $timerService = new \bxkj_module\service\Timer();
        $total = $timerService->getTotal($get);
        $page = $this->pageshow($total);
        $list = $timerService->getList($get, $page->firstRow, $page->listRows);
        $this->assign('_list', $list);
        return $this->fetch();
    }

    public function add()
    {
        $this->checkAuth('admin:timer:management');
        if (Request::isPost()) {
            $post = Request::post();
            $timerService = new \bxkj_module\service\Timer();
            if (!empty($post['host'])) {
                $post['url'] = rtrim($post['host'], '/') . ($post['url'] ? ('/' . ltrim($post['url'], '/')) : '');
                unset($post['host']);
            }
            if (!empty($post['trigger_time'])) $post['trigger_time'] = strtotime($post['trigger_time']);
            $result = $timerService->add($post);
            if (!$result) $this->error($timerService->getError());
            alog("system.timer.add", '添加定时器 Key：'.$result);
            $this->success('新增成功');
        } else {
            $this->assign('urls', $this->urls);
            return $this->fetch();
        }
    }

    public function edit()
    {
        $this->checkAuth('admin:timer:management');
        $timerService = new \bxkj_module\service\Timer();
        if (Request::isPost()) {
            $post = Request::post();
            if (!empty($post['host'])) {
                $post['url'] = rtrim($post['host'], '/') . ($post['url'] ? ('/' . ltrim($post['url'], '/')) : '');
                unset($post['host']);
            }
            if (!empty($post['trigger_time'])) $post['trigger_time'] = strtotime($post['trigger_time']);
            $result = $timerService->edit($post);
            if (!$result) $this->error($timerService->getError());
            alog("system.timer.edit", '编辑定时器 Key：'.$post['key']);
            $this->success('编辑成功');
        } else {
            $key = input('key');
            if (empty($key)) $this->error('请选择定时器');
            $this->assign('urls', $this->urls);
            $info = $timerService->getInfo($key);
            if (empty($info)) $this->error('定时器不存在');
            foreach ($this->urls as $url) {
                $index = strpos($info['url'], $url['value']);
                if ($index !== false) {
                    $info['host'] = $url['value'];
                    $info['url'] = str_replace($url['value'], '', $info['url']);
                }
            }
            $this->assign('_info', $info);
            return $this->fetch('add');
        }
    }

    //删除
    public function del()
    {
        $this->checkAuth('admin:timer:management');
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择定时器');
        $timerService = new \bxkj_module\service\Timer();
        $total = $timerService->remove($ids);
        if (!$total) $this->error('删除失败');
        alog("system.timer.del", '删除定时器 Key：'.implode(",", $ids));
        $this->success('删除成功');
    }
}

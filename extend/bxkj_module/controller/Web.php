<?php

namespace bxkj_module\controller;

use bxkj_module\service\Menu;
use bxkj_module\service\SsoApp;
use bxkj_common\Page;
use think\Container;
use think\exception\HttpResponseException;
use think\facade\Request;
use think\facade\Response;

class Web extends Controller
{
    protected $pageTpl = [];
    protected $pageInfo = [];
    protected $menuService;
    protected $countdown;

    public function __construct()
    {
        parent::__construct();
        $this->pageTpl = config('site.page_tpl');
        $this->pageInfo = config('site.page_info');
        $this->menuService = new Menu();
        $this->pageInfo['company_name'] = config('site.company_name');
        $this->pageInfo['full_company_name'] = config('site.company_full_name');
    }

    protected function lastSendTime($key = 'last_send_time')
    {
        $last_send_time = session($key);
        $countdown = 60 - (time() - $last_send_time);
        $countdown = $countdown < 0 ? 0 : $countdown;
        $this->countdown = $countdown;
        $this->assign('countdown', $countdown);
        return $countdown;
    }

    protected function checkAuth($rules, $uid = null, $type = 1)
    {
        $res = check_auth($rules, $uid, $type);
        if (!$res) {
            $this->error('您没有操作权限');
            exit();
        }
    }

    //初始化菜单(不能使用session缓存，因为需要动态计算current)
    protected function createMenu($name, $root, $uid = null, $current = 'current')
    {
        $menuTree = $this->menuService->setCurrentName($current)->getMenuTree($root, null, $uid, null, input('menu_id'));
        $this->assign($name . '_tree', $menuTree);
        $this->assign($name . '_last', isset($menuTree[count($menuTree) - 1]) ? $menuTree[count($menuTree) - 1] : '');
        $this->pageInfo[$name . '_name'] = isset($menuTree[count($menuTree) - 1]['name']) ? $menuTree[count($menuTree) - 1]['name'] : '';;
        return $menuTree;
    }

    protected function fetch($template = '', $vars = [], $config = [])
    {
        $this->beforeDisplay();
        return parent::fetch($template, $vars, $config);
    }

    protected function display($content = '', $vars = [], $config = [])
    {
        $this->beforeDisplay();
        return parent::display($content, $vars, $config);
    }

    //输出显示内容之前
    protected function beforeDisplay()
    {
        foreach ($this->pageTpl as $name => $tpl) {
            foreach ($this->pageInfo as $key => $value) {
                $tpl = str_replace('{$' . $key . '}', $value, $tpl);
            }
            $tpl = preg_replace('/\{\$\w+\}/', '', $tpl);
            $this->assign($name, $tpl);
        }
        $this->assign('page_info', $this->pageInfo);
    }

    //简化分页操作
    protected function pageshow($total, $rowname = '', $params = null, $rollpage = 10, $style = null)
    {
        $style = isset($style) ? $style : '%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%';
        //获取每页显示条数
        $listRows = is_int($rowname) ? $rowname : (empty($rowname) ? config('paginate.list_rows') : config($rowname));
        $params = isset($params) ? $params : input();
        $page = new Page($total, $listRows, $params);
        $page->rollPage = $rollpage;
        $page->setConfig('theme', $style);
        $p = $page->show();
        $this->assign('_page', $p ? $p : '');
        $this->assign('_total', $total);
        return $page;
    }

    //同步操作返回
    protected function syncReturn($appOpts, $status = '0', $message = '', $redirect = '', $request = array())
    {
        if (is_error($message)) {
            $status = $message->getStatus();
            $message = $message->getMessage();
        }
        $appModel = new SsoApp();
        $appOpts['app_key'] = isset($appOpts['app_key']) ? $appOpts['app_key'] : APP_KEY;
        $appOpts['type'] = isset($appOpts['type']) ? $appOpts['type'] : config('app.sync_app_type');
        $appList = $appModel->getAppUrlList($appOpts['act'], $appOpts['data'], $appOpts['app_key'], $appOpts['type']);
        if (Request::isAjax()) {
            $result = array();
            $tmp = array('name' => 'sync');
            $tmp['app_list'] = $appList;
            $result['header'] = array($tmp);
            $result['status'] = $status;
            if (!empty($request)) $result['request'] = $request;
            if (!empty($message)) $result['message'] = $message;
            if (!empty($redirect)) $result['url'] = $redirect;
            $response = Response::create($result, 'json')->options(['jump_template' => $this->app['config']->get('dispatch_success_tmpl')]);
            throw new HttpResponseException($response);
        } else {
            $this->assign('app_list', $appList);
            $this->assign('sync_status', $status);
            $this->assign('sync_message', $message);
            $this->assign('sync_redirect', $redirect);
            $this->assign('sync_request', $request);
            $this->assign('sync_delay', $status == 0 ? 1 : 3);
            $tpl = config('app.sync_return_tpl');
            $response = Response::create($this->fetch($tpl), 'html')->options(['jump_template' => $this->app['config']->get('dispatch_error_tmpl')]);
            throw new HttpResponseException($response);
        }
    }

    protected function error($msg = '', $status = 1, $url = null, $data = null, $header = [])
    {
        if (is_error($msg)) {
            $status = $msg->getStatus();
            $msg = $msg->getMessage();
        }
        $type = $this->getResponseType();
        if (is_null($url)) {
            $url = $this->app['request']->isAjax() ? '' : 'javascript:history.back(-1);';
        } elseif ('' !== $url) {
            $url = (strpos($url, '://') || 0 === strpos($url, '/')) ? $url : $this->app['url']->build($url);
        }
        $result = [
            'status' => $status,
            'message' => $msg,
            'data' => $data,
            'url' => $url,
            'wait' => 3,
        ];
        if ('json' == strtolower($type)) {
            if (!isset($data)) unset($result['data']);
        }
        if ('html' == strtolower($type)) {
            $type = 'jump';
        }
        $response = Response::create($result, $type)->header($header)->options(['jump_template' => $this->app['config']->get('dispatch_error_tmpl')]);
        throw new HttpResponseException($response);
    }

    protected function success($msg = '', $data = null, $url = null, $header = [])
    {
        $input = input();
        if (is_null($url)) {
            if (!empty($input['redirect'])) {
                $url = $input['redirect'];
            } else {
                $url = '';
            }
        } elseif ('' !== $url) {
            $url = (strpos($url, '://') || 0 === strpos($url, '/')) ? $url : Container::get('url')->build($url);
        }
        $result = [
            'status' => 0,
            'message' => $msg,
            'data' => $data,
            'url' => $url,
            'wait' => 3,
        ];
        $type = $this->getResponseType();
        if ('json' == strtolower($type)) {
            if (!isset($data)) unset($result['data']);
        }
        // 把跳转模板的渲染下沉，这样在 response_send 行为里通过getData()获得的数据是一致性的格式
        if ('html' == strtolower($type)) {
            $type = 'jump';
        }
        $response = Response::create($result, $type)->header($header)->options(['jump_template' => $this->app['config']->get('dispatch_success_tmpl')]);
        throw new HttpResponseException($response);
    }

    protected function redirect($url, $params = [], $code = 302, $with = [])
    {
        if (Request::isAjax()) {
            $result = ['url' => $url, 'status' => 302];
            $type = 'json';
            $response = Response::create($result, $type)->options(['jump_template' => $this->app['config']->get('dispatch_success_tmpl')]);
            throw new HttpResponseException($response);
        } else {
            parent::redirect($url, $params, $code, $with);
        }
    }

}

<?php

namespace app\admin\controller;

use think\Db;
use think\facade\Request;

class SmsTemplate extends Controller
{

    public function index()
    {
        $this->checkAuth('admin:sms_template:select');
        $smsTemplateService = new \app\admin\service\SmsTemplate();
        $get = input();
        $total = $smsTemplateService->getTotal($get);
        $page = $this->pageshow($total);
        $artList = $smsTemplateService->getList($get, $page->firstRow, $page->listRows);
        $this->assign('get', $get);
        $this->assign('_list', $artList);
        return $this->fetch();
    }

    public function add()
    {
        $this->checkAuth('admin:sms_template:add');
        if (Request::isGet()) {
            return $this->fetch();
        } else {
            $smsTemplateService = new \app\admin\service\SmsTemplate();
            $post = input();
            $result = $smsTemplateService->add($post);
            if (!$result) $this->error($smsTemplateService->getError());
            alog("manager.sms_template.add", '新增短信模版 ID：'.$result);
            $this->success('新增成功', $result);
        }
    }

    public function edit()
    {
        $this->checkAuth('admin:sms_template:update');
        if (Request::isGet()) {
            $id = input('id');
            $info = Db::name('sms_template')->where('id', $id)->find();
            if (empty($info)) $this->error('分类不存在');
            $this->assign('_info', $info);
            return $this->fetch('add');
        } else {
            $smsTemplateService = new \app\admin\service\SmsTemplate();
            $post = input();
            $result = $smsTemplateService->update($post);
            if (!$result) $this->error($smsTemplateService->getError());
            alog("manager.sms_template.edit", '编辑短信模版 ID：'.$post['id']);
            $this->success('编辑成功', $result);
        }
    }

    public function delete(){
        $this->checkAuth('admin:sms_template:delete');
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择记录');
        $num = Db::name('sms_template')->whereIn('id', $ids)->delete();
        if (!$num) $this->error('删除失败');
        alog("manager.sms_template.del", '删除短信模版 ID：'.implode(",", $ids));
        $this->success("删除成功，共计删除{$num}条记录",'','sms_template/index');
    }

}

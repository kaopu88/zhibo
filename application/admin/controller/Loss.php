<?php

namespace app\admin\controller;

use app\admin\service\Work;
use bxkj_common\HttpClient;
use bxkj_common\RedisClient;
use think\Db;
use think\facade\Request;

class Loss extends Controller
{
    public function all_list()
    {
        $this->checkAuth('admin:anchor:select');
        $lossService = new \app\admin\service\Loss();
        $get = input();
        $total = $lossService->getTotal($get);
        $page = $this->pageshow($total);
        $users = $lossService->getList($get, $page->firstRow, $page->listRows);
        $this->assign('_list', $users);
        return $this->fetch();
    }

    public function index()
    {
        $this->checkAuth('admin:anchor:select');
        $lossService = new \app\admin\service\Loss();
        $get = input();
        $get['audit_aid'] = AID;
        if ($get['audit_status'] == '0') {
            Work::read(AID, 'audit_clear_agent');
        }
        $total = $lossService->getTotal($get);
        $page = $this->pageshow($total);
        $users = $lossService->getList($get, $page->firstRow, $page->listRows);
        $this->assign('_list', $users);
        return $this->fetch();
    }

    public function check()
    {
        $this->checkAuth('admin:loss:check');
        if (Request::isGet()) {
            return $this->fetch();
        } else {
            $type = input('type');
            if (empty($type)) $this->error('请选择检测类型');
            $params = [];
            if ($type == 'agent') {
                $params['agent_id'] = input('agent_id');
                if (empty($params['agent_id'])) $this->error('请填写'.config('app.agent_setting.agent_name').'ID');
            } else if ($type == 'promoter') {
                $params['promoter_uid'] = input('promoter_uid');
                if (empty($params['promoter_uid'])) $this->error('请填写'.config('app.agent_setting.promoter_name').'ID');
            } else {
                $userIdsStr = input('user_ids');
                if (empty($userIdsStr)) $this->error('请填写用户ID');
                $userIdsStr = preg_replace('/\D+$/', '', $userIdsStr);
                $userIds = preg_split('/\D+/', $userIdsStr);
                if (empty($userIds)) $this->error('请填写用户ID');
                if (count($userIds) > 30) $this->error('不能超过30个');
                $params['user_ids'] = join(',', $userIds);
            }
            $params['aid'] = AID;
            $sha1 = sha1_array($params);
            $res = cache('loss_check:' . $sha1);
            if ($res) $this->error('请勿重复查找');
            cache('loss_check:' . $sha1, 1, 300);
            $httpClient = new HttpClient();
            $httpClient->post(PUSH_URL . '/user/loss_check', $params, 3);
            alog("sociaty.agent.check", "公会搜索 搜索类型：".$type);
            $this->success('正在查找，查找结果将自动分配给相应工作人员审核');
        }
    }

    public function audit()
    {
        $this->checkAuth('admin:loss:audit');
        if (Request::isGet()) {
            $id = input('id');
            if (empty($id)) $this->error('请选择审核记录');
            $loss = Db::name('loss')->field('id,user_id,bean')->where(['id' => $id])->find();
            if (!$loss) $this->error('审核记录不存在');
            $bean = Db::name('bean')->where(['user_id' => $loss['user_id']])->find();
            $tmp = $bean['bean'] - $bean['loss_bean'];
            $loss['bean'] = $tmp > 0 ? $tmp : 0;
            $this->success('获取成功', $loss);
        } else {
            $audit_status = input('audit_status');
            $lossService = new \app\admin\service\Loss();
            if ($audit_status == '1') {
                $ids = get_request_ids();
                if (empty($ids)) $this->error('请选择申请记录');
                $num = $lossService->clear($ids, AID);
                if (!$num) $this->error($lossService->getError());
                alog("sociaty.agent.audit", "公会审核 ID：".implode(",", $ids)." 通过,共".$num."条记录");
                $this->success('清算成功，共计清算了' . $num . '笔');
            } else {
                $post = input();
                $res = $lossService->turnDown($post, AID);
                if (!$res) $this->error($lossService->getError());
                alog("sociaty.agent.audit", "公会审核 ID：".$post['id']." 拒绝");
                $this->success('驳回成功');
            }
        }
    }


}

<?php

namespace app\admin\controller;

use app\admin\service\Work;
use bxkj_common\RedisClient;
use bxkj_module\service\Service;
use bxkj_module\service\Tree;
use think\Db;
use think\facade\Env;
use think\facade\Request;

class Personal extends Controller
{
    public function base_info()
    {
        return $this->fetch();
    }

    public function change_pwd()
    {
        if (Request::isPost()) {
            $oldPwd = input('old_password');
            $pwd = input('password');
            $confirmPwd = input('confirm_password');
            if (empty($oldPwd)) $this->error('请填写原密码');
            if (empty($pwd)) $this->error('请填写新密码');
            if (isset($confirmPwd) && $pwd != $confirmPwd) {
                $this->error('密码两次输入不一致');
            }
            if (strlen($pwd) < 6 || strlen($pwd) > 16) $this->error('密码6-16位数字或英文字母组合');
            if (!validate_regex($pwd, 'no_blank')) $this->error('密码不能包含空格');
            $admin = Db::name('admin')->where(array('id' => AID))->find();
            if (empty($admin)) $this->error('管理员不存在');
            $salt = $admin['salt'];
            if ($admin['password'] != sha1($oldPwd . $salt)) $this->error('原密码不正确');
            $sha1 = sha1($pwd . $salt);
            if (weak_password($salt, $sha1, $admin['username'])) $this->error('密码过于简单');
            $num = Db::name('admin')->where(array('id' => AID))->update(array(
                'password' => $sha1
            ));
            if (!$num) $this->error('修改失败');
            alog("personal.password.edit", '修改管理员密码');
            $this->success('修改成功');
        } else {
            return $this->fetch();
        }
    }

    public function work()
    {
        if (Request::isGet()) {
            $workService = new Work();
            $works = $workService->getWorks(AID);
            return json_success($works, '获取成功');
        } else {
        }
    }

    public function change_work_sms_status()
    {
        $type = input('id');
        $status = input('sms_status');
        $workService = new Work();
        $res = $workService->changeSmsStatus(AID, $type, $status);
        if (!$res) $this->error($workService->getError());
        $actName = $status == '0' ? '关闭' : '开启';
        $this->success($actName . '失败');
    }

    public function change_work_status()
    {
        $type = input('id');
        $status = input('status');
        $workService = new Work();
        $res = $workService->changeStatus(AID, $type, $status);
        if (!$res) $this->error($workService->getError());
        $actName = $status == '0' ? '下线' : '上线';
        $this->success($actName . '失败');
    }

    public function get_unread_num()
    {
        $list = [];
        $works = Db::name('admin_work')->where(['aid' => AID])->field('id,type,unread_num,status')->limit(30)->select();
        $works = $works ? $works : [];
        $tmp = [];
        $total = 0;
        foreach ($works as $work) {
            if (!key_exists($work['type'], $tmp)) {
                $tmp[$work['type']] = $work;
                if ($work['status'] == '1') $total++;
            }
        }
        $list['work_num'] = $total;
//        $workTypes = config('enum.work_types');
        $workTypes = Db::name('work_types')->field('name, type as value, default_aid')->select();
        foreach ($workTypes as $workType) {
            $type = $workType['value'];
            $list[$type] = (int)(isset($tmp[$type]) ? $tmp[$type]['unread_num'] : 0);
        }
        $notice = ["id" => ''];
        $redis = RedisClient::getInstance();
        $where = 'status = 1 and visible in(0,1) and barrage = 1 and aid != '.AID;
        $noticeService = new \app\admin\service\AdminNotice();
        $admin_notice = $noticeService->getlist($where);
        $AID = AID;
        foreach ($admin_notice as $item) {
            if (!$redis->zScore("admin:notice:{$AID}", $item['id'])) {
                $notice = $item;
                break;
            }
        }

        if(!empty($notice)){
            $notice['url'] = url('notice/detail',['id'=>$notice['id']]);
            $notice['close_notice_url'] = url('personal/close_notice',['id'=>$notice['id']]);
        }

        return json_success([
            'messages' => $list,
            'notice' => $notice
        ], '获取成功');
    }

    public function get_notice(){
        $redis = RedisClient::getInstance();
        $where = 'status = 1 and visible in(0,1) and barrage = 1 and aid != '.AID;
        $noticeService = new \app\admin\service\AdminNotice();
        $admin_notice = $noticeService->getlist($where);
        $AID = AID;
        foreach ($admin_notice as $item) {
            if (!$redis->zScore("admin:notice:{$AID}", $item['id'])) {
                $notice = $item;
                break;
            }
        }

        if(!empty($notice)){
            $notice['url'] = url('notice/detail',['id'=>$notice['id']]);
            $notice['close_notice_url'] = url('personal/close_notice',['id'=>$notice['id']]);
        }
        return json_success($notice, '获取成功');
    }

    public function close_notice()
    {
        $id = input('id');
        $redis = RedisClient::getInstance();
        $AID = AID;
        $key = "admin:notice:{$AID}";
        $redis->zAdd($key, time(), $id);
        return $this->get_notice();
    }

    public function task_transfer()
    {
        if (Request::isGet()) {
            $type = input('type');
            $workTypes = Db::name('work_types')->field('type')->select();
            $types = array();
            foreach ($workTypes as $item)
            {
                $types[] = $item['type'];
            }
            if (!in_array($type, $types)) $this->error('不支持的类型');
            $where = [['status', 'eq', '1'], ['works', 'like', "%$type%"]];
            $groups = Db::name('admin_group')->where($where)->select();
            $gids = Service::getIdsByList($groups, 'id');
            $admins = [];
            if (!empty($gids)) {
                $tmpArr = Db::name('admin_group_access')->field('admin.id,admin.username,admin.realname,admin.phone,admin.avatar,admin.status')
                    ->alias('acc')->join('__ADMIN__ admin', 'admin.id=acc.uid')->whereIn('acc.gid', $gids)->select();
                $tmpArr = $tmpArr ? $tmpArr : [];
                foreach ($tmpArr as $item) {
                    if ($item['id'] != AID) {
                        $item['nickname'] = user_name($item);
                        $admins[] = $item;
                    }
                }
            }
            return json_success(['admins' => $admins, 'type' => $type, 'id' => input('id')], '获取成功');
        } else {
            $post = input();
            $work = new Work();
            $res = $work->taskTransfer($post, AID);
            if (!$res) $this->error($work->getError());
            $this->success('转交成功');
        }
    }

}

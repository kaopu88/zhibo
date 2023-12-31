<?php
/**
 * Created by PhpStorm.
 * User: “崔鹏
 * Date: 2020/06/24
 * Time:  下午 16:01
 */

namespace app\friend\controller;

use app\admin\service\SysConfig;
use app\admin\service\Work;
use app\friend\service\FriendCircleComment;
use app\friend\service\FriendCircleCommentEvaluate;
use app\friend\service\FriendCircleMessage;
use bxkj_common\RedisClient;
use bxkj_module\service\Tree;
use think\Db;
use think\facade\Request;

class Evaluate extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function initialize()
    {
        parent::initialize(); // TODO: Change the autogenerated stub
        //读取config配置
        $redis       = new RedisClient();
        $cacheFriend = $redis->exists('cache:friend_config');
        if (empty($cacheFriend)) {
            $arr  = [];
            $ser  = new SysConfig();
            $info = $ser->getConfig("friend");
            if (empty($info)) return [];
            $redis->setex('cache:friend_config', 4 * 3600, $info['value']);
        }
        $friendConfigRes       = $redis->get('cache:friend_config');
        $this->friendConfigRes = json_decode($friendConfigRes, true);
    }

    public function index()
    {
        $this->checkAuth('friend:Evaluate:index');
        $get        = input();
        $msgComment = new FriendCircleCommentEvaluate();
        $total      = $msgComment->getTotal($get);
        $page       = $this->pageshow($total);
        $list       = $msgComment->getList($get, $page->firstRow, $page->listRows);
        $earry = [['name' => '待审核', 'value' => 0],
            ['name' => '已通过', 'value' => 1],
        ];
        $this->assign('_earry', json_encode($earry));
        $this->assign('_list', $list);
        return $this->fetch();
    }

    public function del()
    {
        $this->checkAuth('friend:Evaluate:delete');
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择记录');
        $msgComment = new FriendCircleCommentEvaluate();
        $num        = $msgComment->del($ids);
        if (!$num) $this->error('删除失败');
        alog("friend.evaluate.del", "删除评论留言 ID：".implode(",", $ids));
        $count = count($ids);
        $this->success("删除成功，共计删除{$count}条记录");
    }

    public function add()
    {
        $this->checkAuth('friend:Evaluate:add');
        if (Request::isGet()) {
            $info = [];
            $get  = input();
            if ($get['pcat_id'] != '') $info['pcat_id'] = $get['pcat_id'];
            if ($get['cat_id'] != '') $info['cat_id'] = $get['cat_id'];
            $this->assign('_info', $info);
            return $this->fetch();
        } else {
            $msgCommentEval = new FriendCircleCommentEvaluate();
            $post           = input();
            $result         = $msgCommentEval->backstageadd($post);
            if (!$result) $this->error($msgCommentEval->getError());
            alog("friend.evaluate.add", "新增评论留言 ID：".$result);
            $this->success('新增成功', $result);
        }
    }

    public function edit()
    {
        if (Request::isGet()) {
            $id   = input('id');
            $info = Db::name('friend_circle_comment_evaluate')->where('id', $id)->find();
            if (empty($info)) $this->error('信息不存在');
            $this->assign('_info', $info);
            return $this->fetch('add');
        } else {
            $msgCommentE = new FriendCircleCommentEvaluate();
            $post        = input();
            $result      = $msgCommentE->backstageedit($post);
            if (!$result) $this->error($msgCommentE->getError());
            alog("friend.evaluate.edit", "编辑评论留言 ID：".$post['id']);
            $this->success('修改成功', $result);
        }
    }

    public function change_status()
    {
        $this->checkAuth('friend:Evaluate:change_status');
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择记录');
        $status = input('status');
        if (!in_array($status, ['0', '1'])) $this->error('状态值不正确');
        $msgCommentE = new FriendCircleCommentEvaluate();
        $num         = $msgCommentE->changeStatus($ids, $status);
        if (!$num) $this->error('切换状态失败');
        alog("friend.evaluate.edit", "编辑评论留言 ID：".implode(",", $ids)."<br>修改状态".($status == 1 ? "启用" : "禁用"));
        $this->success('切换成功');
    }

    public function evaluateExamine(){
        $this->checkAuth('friend:Evaluate:msgExamine');
        $get = input();
        $get['aid'] = AID;
        if ($get['audit_status'] == '0') {
            Work::read(AID, 'friend_evaluate_verified');
        }
        $get        = input();
        $get['status'] = 0;
        $msgComment = new FriendCircleCommentEvaluate();
        $total      = $msgComment->getTotal($get);
        $page       = $this->pageshow($total);
        $list       = $msgComment->getList($get, $page->firstRow, $page->listRows);
        $this->assign('_list', $list);
        $this->assign('get', $get);
        return $this->fetch();
    }

    public function batch_pass(){
        $this->checkAuth('friend:Evaluate:change_status');
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择记录');
        $status = 1;
        if (!in_array($status, ['0', '1'])) $this->error('状态值不正确');
        $msgCommentE = new FriendCircleCommentEvaluate();
        $num         = $msgCommentE->changeStatus($ids, $status);
        if (!$num) $this->error('切换状态失败');
        alog("friend.evaluate.edit", "编辑评论留言 ID：".implode(",", $ids)."<br>修改状态".($status == 1 ? "启用" : "禁用"));
        $this->success('切换成功');
    }
}
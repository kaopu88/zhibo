<?php
/**
 * Created by PhpStorm.
 * User: “崔鹏
 * Date: 2020/07/24
 * Time:  下午 18:52
 */

namespace app\friend\controller;

use app\admin\service\SysConfig;
use app\friend\service\FriendCircleAuthor;
use bxkj_common\RedisClient;
use think\Db;
use think\facade\Request;

class Author extends Controller
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
        $msgComment = new FriendCircleAuthor();
        $total      = $msgComment->getTotal($get);
        $page       = $this->pageshow($total);
        $list       = $msgComment->getList($get, $page->firstRow, $page->listRows);
        $this->assign('_list', $list);
        return $this->fetch();
    }

    public function del()
    {
        $this->checkAuth('friend:Evaluate:delete');
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择记录');
        $msgComment = new FriendCircleAuthor();
        $num        = $msgComment->del($ids);
        if (!$num) $this->error('删除失败');
        alog("friend.author.del", "删除歌曲作者 ID：".implode(",", $ids));
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
            $msgCommentEval = new FriendCircleAuthor();
            $post           = input();
            $result         = $msgCommentEval->backstageadd($post);
            if (!$result) $this->error($msgCommentEval->getError());
            alog("friend.author.add", "新增歌曲作者 ID：".$result);
            $this->success('新增成功', $result);
        }
    }


    public function edit()
    {
        if (Request::isGet()) {
            $id   = input('id');
            $info = Db::name('friend_circle_author')->where('id', $id)->find();
            if (empty($info)) $this->error('信息不存在');
            $this->assign('_info', $info);
            return $this->fetch('add');
        } else {
            $msgCommentE = new FriendCircleAuthor();
            $post        = input();
            $result      = $msgCommentE->backstageedit($post);
            if (!$result) $this->error($msgCommentE->getError());
            alog("friend.author.edit", "编辑歌曲作者 ID：".$post['id']);
            $this->success('修改成功', $result);
        }
    }

    public function change_status()
    {
        $this->checkAuth('friend:comment:change_status');
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择记录');
        $status = input('status');
        if (!in_array($status, ['0', '1'])) $this->error('状态值不正确');
        $msgCommentE = new FriendCircleAuthor();
        $num         = $msgCommentE->changeStatus($ids, $status);
        if (!$num) $this->error('切换状态失败');
        alog("friend.author.edit", "编辑歌曲作者 ID：".implode(",", $ids)."<br>修改状态".($status == 1 ? "启用" : "禁用"));
        $this->success('切换成功');
    }
}
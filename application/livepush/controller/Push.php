<?php
namespace app\livepush\controller;

use app\core\service\Socket;
use think\Db;
use think\facade\Request;

class Push extends Controller
{

    public function index()
    {
        $liveList=Db::name('live')->where(['status' => '1'])->select();
        $this->assign('liveList', $liveList);
        return $this->fetch();
    }

    //推送消息列表
    public function msglist(){
        $get = input();
        $Push = new \app\livepush\service\Push();
        $total = $Push->getTotal($get);
        $page = $this->pageshow($total);
        $list = $Push->getList($get, $page->firstRow, $page->listRows);
        foreach ($list as $k=>$v){
            $manager=Db::name('admin')->where(['id' => $v['aid']])->find();
            $list[$k]['manager']=$manager['realname'];
        }
        $this->assign('_list', $list);
        return $this->fetch();
    }

    //发送直播消息到对应房间
    public function add(){
        $post = input();
        if (empty(trim($post['content']))) $this->error('内容不能为空');
        $haslive = Db::name('live')->find();
        if (!$haslive) {
            $this->error('没人直播不能发消息');
        }
        $socket = new Socket();
        if($post['room_id']==1){
            $message = [
                'mod' => 'Live',
                'act' => 'pushBroadCast',
                'args' => ['content' => $post['content']],
                'web' => 1
            ];
            $res = $socket->connectSocket($message);
            if (!$res) $this->error($socket->getError());
        }else{
            $message = [
                'mod' => 'Live',
                'act' => 'pushBroadRoom',
                'args' => ['room_id' => $post['room_id'], 'content' => $post['content']],
                'web' => 1
            ];
            $res = $socket->connectSocket($message);
            if (!$res) $this->error($socket->getError());
        }
        $liveRoom = Db::name('live')->where(['id' => $post['room_id']])->find();
        $data = array(
            'aid'=>AID,
            'ip'=>get_client_ip(),
            'push_object'=>$liveRoom['nickname'] ? $liveRoom['nickname'] : '全部房间',
            'user_id'=>$liveRoom['user_id'] ? $liveRoom['user_id'] : 0,
            'content' => $post['content'],
            'create_time' => time()
        );
        $insertid = Db::name('live_message_push')->insertGetId($data);
        if($insertid){
            alog("live.live.push", "向直播间 ROOM_ID:".$post['room_id']."推送消息 MSG_ID：".$insertid);
            $this->success('成功发送消息');
        }
    }
}
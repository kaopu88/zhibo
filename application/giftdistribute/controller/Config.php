<?php
namespace app\giftdistribute\controller;

use app\core\service\Socket;
use bxkj_common\RedisClient;
use think\Db;
use think\facade\Request;

class Config extends Controller
{

    public function index()
    {
        $this->checkAuth('giftdistribute:Config:index');
        $ser = new \app\admin\service\SysConfig();
        if (Request::isGet()) {
            $info = $ser->getConfig("giftdistribute");
            $info = json_decode($info['value'], true);
            $this->assign('_info', $info);
        }else{
            $is_open = input('is_open');
            $level = input('level');
            if($is_open==1 && !$level){
              $this->error('请选择分销层级');
            }
            $post = input();
            if($is_open==0){
                unset($post['level']);
                unset($post['one_rate']);
                unset($post['two_rate']);
                unset($post['three_rate']);
            }
            $post = json_encode($post);

            if($ser->getConfig("giftdistribute")) {
                $result = $ser->updateConfig(['mark' => 'giftdistribute'], ['value' => $post]);
            }else{
                $result = $ser->addConfig(['mark' => 'giftdistribute', 'classified'=>'giftdistribute', 'value' => $post]);
            }
            if( $result > 0 ){
                $ser->resetConfig();
            }
            $redis = new RedisClient();
            $redis->set('distribute:gift', $post, 4 * 3600);
            $this->success('更新成功', $result);
        }
        return $this->fetch();
    }

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
}
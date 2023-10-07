<?php


namespace app\h5\controller;

use bxkj_common\RedisClient;
use bxkj_module\service\User;
use think\Db;

class PkRank extends Controller
{
    public function index()
    {
        $info = Db::name('activity')->where('mark', 'pk_rank')->find();
        $this->assign('_info', $info);
        return $this->fetch();
    }

    public function topthree()
    {
        $pkrankMod = new \app\h5\service\PkRank();
        $result = $pkrankMod->getTopThree();
        return $this->success('获取成功',$result);
    }

    public function pklist()
    {
        $pkrankMod = new \app\h5\service\PkRank();
        $result = $pkrankMod->getpklist();
        return $this->success('获取成功',$result);
    }

    public function getranklist()
    {
        $type = (int)request()->param('type');
        $level = (int)request()->param('level');

        $pkrankMod = new \app\h5\service\PkRank();
        if( $type == 1 ){
            $result = $pkrankMod->getAnchorList($level);
        } else {
            $result = $pkrankMod->getFansList();
        }

        return $this->success('获取成功',$result);
    }

    //任务规则说明
    public function explain()
    {
        return $this->fetch();
    }

    public function received()
    {
        $data = [];
        $fans_key = "activity:pk_rank:pk_rank_fans";
        $stock_key = "activity:pk_rank:stock_fans";

        $redis = new RedisClient();
        $user = new User();

        $list = $redis->Zrange($stock_key,0,-1,true);

        if( !empty( $list ) ){
            foreach ($list as $key=>$val){
                $_info = [];
                $_info = $user->getUser($key, null, 'user_id, nickname, avatar');
                $_info['datetime'] = $val;

                $data[] = $_info;
            }
        }
        $this->assign('_info', $data);
        return $this->fetch();
    }
}
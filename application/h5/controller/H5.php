<?php

namespace app\h5\controller;

use bxkj_common\RedisClient;
use Qiniu\Auth;
use think\Db;
use think\facade\Request;

class H5 extends LoginController
{
    public function __construct()
    {
        parent::__construct();
        $this->agentName = config('app.agent_setting.agent_name') ? config('app.agent_setting.agent_name') : '家族';
    }
    public function heisanjiao()
    {
        return $this->fetch();
    }

    public function gambling()
    {
        return $this->fetch();
    }

    function convention()
    {
        return $this->fetch();
    }

    function IWantToHot()
    {
        return $this->fetch();
    }

    function internetHall()
    {
        return $this->fetch();
    }

    //设置热门直播排序
    public function getsort()
    {
        $redis = RedisClient::getInstance();
        $millet = $redis->keys('BG_LIVE:*');
        foreach ($millet as $val) {
            if (strpos($val, 'location')) continue;
            $redis->del($val);
        }
        die;
    }

    public function berserker()
    {
        return $this->fetch();
    }

    /**
     * 工会_我的工会
     */
    public function myLabourUnion()
    {
        $params     = Request::get();
        $access_token = $params['token'];
        $h5Url=config('app.system_deploy.h5_service_url');
        $url = $h5Url.'/Agent/checkAgent?token='.$access_token;
        $rest =  curl_get($url);

        $returndate = json_decode($rest,true);
        $returncode = $returndate['data'];
        $msg   =  '您还没加入'.$this->agentName;
        $msgdetail = '加入'.$this->agentName.'，你将会获得更多的资源和奖励快来加入吧';
        $cango  =1;
        $is = 1;
        $agent_id = 0;
        if($returndate['code']==1){
            $msg = $returndate['msg'];
            $msgdetail = '';
            $cango = 0 ;
            $is = 0;
            if (!empty($returncode['agent_id'])) $agent_id = $returncode['agent_id'];
        }
        if($returndate['code']==0&&$returncode==2){
            $url = $h5Url.'/H5/applyLabourUnionError?token='.$access_token."&msg=".$returndate['msg'];
            $this->redirect($url);
        }
        if($returncode==1){
            $url = $h5Url.'/H5/applyLabourUnionExamine?token='.$access_token;
            $this->redirect($url);
        }
        if($returncode==3){
             $url = $h5Url.'/agent/index.html?token='.$access_token;
             $this->redirect($url);
        }

        if($returndate['data']['id']>0){
            $url = $h5Url.'/H5/applyLabourUnionSuccess?token='.$access_token;
            $this->redirect($url);
        }
      $data =  [
          'access_token' => $access_token,
          'msg'  => $msg,
          'msgdetail'=> $msgdetail,
          'cango' => $cango,
          'is' => $is,
          'agent_id' => $agent_id,
      ];

        $exitgent = 0;
        if (!empty($agent_id)) {
            $userId = $this->data['user']['user_id'];
            $res = \think\Db::name('promotion_exit_apply')->where(['user_id' => $userId, 'agent_id' => $agent_id])->order('id desc')->find();
            if ($res['status'] === 0) $exitgent = 1;
        }

        $this->assign('_agentName',$this->agentName);
        $this->assign('_info',$data);
        $this->assign('exitgent',$exitgent);
        return $this->fetch();

    }

    /**
     * 工会_申请工会
     */
    public function applyLabourUnion()
    {
        $params   = Request::post();
        $userId   = $this->data['user']['user_id'];
        $this->assign('_agentName',$this->agentName);
        return $this->fetch();
    }

    /**
     * 工会_失败页面
     */
    public function applyLabourUnionError()
    {
        $this->assign('_agentName',$this->agentName);
        return $this->fetch();
    }

    /**
     * 工会_审核中页面
     */
    public function applyLabourUnionExamine()
    {
        $this->assign('_agentName',$this->agentName);
        return $this->fetch();
    }

    /**
     * 工会_开通页面
     */
    public function applyLabourUnionSuccess()
    {
        $agent = Db::name('agent')->field('root_id')->where(['uid' => $this->data['user']['user_id']])->find();
        if (!empty($agent['root_id'])) {
            $accountAgent =  Db::name('agent_admin')->field('username')->where(['id' => $agent['root_id']])->find();
        }
        $this->assign('username', !empty($accountAgent) ? $accountAgent['username'] : '');
        $this->assign('_agentName',$this->agentName);
        return $this->fetch();
    }


}
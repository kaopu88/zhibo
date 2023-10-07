<?php

namespace app\h5\controller;

use bxkj_common\CoreSdk;
use think\Db;
use think\Request;

class Creative extends BxController
{
    protected $error;

    public function __construct()
    {
        parent::__construct();
    }

    public function index(Request $request)
    {
        if ($request->isPost())
        {
            $user_id = $request->post('user_id');

            $check_rs = $this->checkCreativeApply($user_id);

            switch ($check_rs)
            {
                case 0 :
                    $res = ['status'=>$check_rs, 'msg'=>'您已提交申请，正在审核中'];
                    break;

                case 1 :
                    $res = ['status'=>$check_rs, 'msg'=>'创作号认证成功'];
                    break;

                case 2 :
                    $res = ['status'=>$check_rs, 'msg'=>'申请被驳回, 请查看系统通知'];
                    break;

                case 3 :
                    $res = ['status'=>$check_rs, 'msg'=>'立即申请'];
                    break;

                case -1 :
                    $res = ['status'=>$check_rs, 'msg'=>$this->error];
            }

            return json_success($res);
        }
        
        $this->assign('creation_fans_num', config('app.app_setting.creation_fans_num'));
        $this->assign('creation_film_num', config('app.app_setting.creation_film_num'));
        $this->assign('creation_report_record', config('app.app_setting.creation_report_record'));
        return $this->fetch();
    }

    protected function checkCreativeApply($user_id)
    {
        $apply_rs = Db::name('creation')->where(['user_id'=>$user_id])->find();

        if (!empty($apply_rs)) return $apply_rs['status'];

        $user = Db::name('user')->field('fans_num,film_num')->where(['user_id'=>$user_id])->find();

        if ($user['fans_num'] < config('app.creation_fans_num'))
        {
            $this->error = "粉丝数未满足要求，不能申请认证";

            return -1;
        }

        if ($user['film_num'] < config('app.creation_film_num'))
        {
            $this->error = "原创视频数未满足要求，不能申请认证";

            return -1;
        }

        if (config('app.creation_report_record')){
            $num = Db::name('complaint')->where(array('to_uid'=>$user_id,'audit_status'=>'1'))->count();

            if (!empty($num))
            {
                $this->error = "您已被投诉{$num}次，不能申请认证";

                return -1;
            }
        }

        return 3;
    }

    public function apply(Request $request)
    {
        $user_id = $request->post('user_id');

        $apply_rs = Db::name('creation')->where(['user_id'=>$user_id])->find();

        if (!empty($apply_rs))
        {
            $check_rs = $apply_rs['status'];
            switch ($check_rs)
            {
                case 0 :
                    $res = ['status'=>$check_rs, 'msg'=>'您已提交申请，正在审核中'];
                    break;

                case 1 :
                    $res = ['status'=>$check_rs, 'msg'=>'创作号认证成功'];
                    break;

                case 2 :
                    $res = ['status'=>$check_rs, 'msg'=>'申请被驳回, 请查看系统通知'];
                    break;
            }
            return json_success($res);
        }

        $core = new CoreSdk();

        $user_info = Db::name('user')->where(['user_id'=>$user_id])->find();

        $num = Db::name('complaint')->where(array('to_uid'=>$user_id,'audit_status'=>'1'))->count();

        $id = Db::name('creation')->insertGetId(['user_id'=>$user_id, 'reported_num' => $num, 'fans_num' => $user_info['fans_num'], 'film_num' => $user_info['film_num'], 'verified' => $user_info['verified'], 'status'=>0, 'create_time' => time()]);

        $aid = $core->getAid('audit_creation',$user_id, $id);

        Db::name('creation')->where('id', $id)->update(['aid'=>$aid]);

        return json_success(['status'=>0, 'msg'=>'申请成功，等待审核']);
    }

    public function get_fans_num(Request $request)
    {
        $user_id = $request->post('user_id');
        $fans_num = Db::name('user')->field('fans_num')->where(['user_id'=>$user_id])->value('fans_num');
        return json_success(array('fans_num'=>$fans_num));
    }

}
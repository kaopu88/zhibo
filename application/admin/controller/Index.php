<?php

namespace app\admin\controller;

use think\Db;
use think\facade\Env;
use bxkj_common\CoreSdk;
use app\admin\service\Work;
use app\admin\service\RechargeConsume;
use think\facade\Request;
use bxkj_common\DateTools;

class Index extends Controller
{
    public function index()
    {
        $coreSdk = new CoreSdk();
        $ad = $coreSdk->post('ad/get_contents', array(
            'space' => 'erp_index_banner',
            'os' => 'pc',
        ));
        $adminGroup = new \app\admin\service\AdminGroup();
        $groupList = $adminGroup->getAdminGroupsByAid(AID);
        $is_root = AID == ROOT_UID ? '1' : '0';
        $userNum = Db::name('user')->where([['delete_time', 'null']])->count();
        $anchorNum = Db::name('anchor')->count();
        $promoterNum = Db::name('promoter')->count();
        $agentNum = Db::name('agent')->where([['delete_time', 'null']])->count();
        $helpNum = Db::name('help')->count();
        $commentNum = Db::name('video_comment')->count();
        $settingNum = Db::name('packages')->sum('download_num');
        $filmNum = Db::name('video_unpublished')->count();
        $filmCheckNum = Db::name('video_unpublished')->where(array('audit_status'=>1))->count();
        $filmSelfCheckNum = Db::name('video_unpublished')->where(array('audit_status'=>1, 'aid'=>AID))->count();
        
        $array = ['userData'=>'user_data_deal', 'complaint'=>'complaint', 'viewback'=>'viewback', 'rechargeApp'=>'recharge_log'];
        foreach ($array as $key => $value){
            $k1 = $key.'Num';
            $k2 = $key.'CheckNum';
            $k3 = $key.'SelfCheckNum';
            $$k1 = Db::name($value)->count();
            $$k2 = Db::name($value)->where(array('audit_status'=>0))->count();
            $aid = 'aid';
            if ($value=='recharge_log') $aid = 'audit_aid';
            $$k3 = Db::name($value)->where(array('audit_status'=>0, $aid=>AID))->count();
            $data1[$k1] = $$k1;
            $data1[$k2] = $$k2;
            $data1[$k3] = $$k3;
        }
        $creationNum = Db::name('creation')->count();
        $creationCheckNum = Db::name('creation')->where(array('status'=>0))->count();
        $creationSelfCheckNum = Db::name('creation')->where(array('status'=>0, 'aid'=>AID))->count();
        $userVerifiedNum = Db::name('user_verified')->count();
        $userVerifiedCheckNum = Db::name('user_verified')->where(array('status'=>0))->count();
        $userVerifiedSelfCheckNum = Db::name('user_verified')->where(array('status'=>0, 'aid'=>AID))->count();

        $articleNum = Db::name('article')->count();
        $liveNum = Db::name('live')->count();
        $workService = new Work();
        $works = $workService->getWorks(AID);
        $where = 'an.status = 1 and an.visible in(0,1)';
        $admin_notice = Db::name('admin_notice')->field('an.*,da.username')->alias('an')->join('__ADMIN__ da', 'an.aid=da.id')->where($where)->order('an.sort desc,an.create_time desc')->limit(6)->select();
        $data2 = [
            'admin_notice' => $admin_notice,
            'works' => $works,
            'userNum' => $userNum,
            'anchorNum' => $anchorNum,
            'promoterNum' => $promoterNum,
            'helpNum' => $helpNum,
            'commentNum' => $commentNum,
            'settingNum' => $settingNum,
            'groupList' => $groupList,
            'is_root' => $is_root,
            'liveNum' => $liveNum,
            'articleNum' => $articleNum,
            'filmNum' => $filmNum,
            'filmCheckNum' => $filmCheckNum,
            'filmSelfCheckNum' => $filmSelfCheckNum,
            'creationNum' => $creationNum,
            'creationCheckNum' => $creationCheckNum,
            'creationSelfCheckNum' => $creationSelfCheckNum,
            'userVerifiedNum' => $userVerifiedNum,
            'userVerifiedCheckNum' => $userVerifiedCheckNum,
            'userVerifiedSelfCheckNum' => $userVerifiedSelfCheckNum,
            'agentNum' => $agentNum,
            'ad' => isset($ad['contents'][0]) ? $ad['contents'][0] : ''
        ];
        $this->assign(array_merge($data1,$data2));
        return $this->fetch();
    }

    public function get_recharge_consume_trend()
    {
        if (Request::isPost()) {
            $start = input('start');
            $end = input('end');
            $unit = input('unit');
            $type = input('type');

            if (empty($start) || empty($end) || empty($unit) || empty($type))
                $this->error('请检查参数是否正确');
            if (DateTools::strToTime($start) > DateTools::strToTime($end))
                $this->error('时间段设置不正确，可能是结束时间小于开始时间');

            $types = array();

            if ($type == 'recharge'){
                $tmpArr = enum_array('pay_methods');
                foreach ($tmpArr as $item)
                {
                    $types[] = array('mark' => "customer_recharge", 'name' => $item['name'], 'type' =>$item['value']);
                }
            }else if($type == 'consume'){
                $tmpArr = enum_array('bean_trade_types');
                foreach ($tmpArr as $item)
                {
                    $types[] = array('mark' => "customer_consume", 'name' => $item['name'], 'type' =>$item['value']);
                }
            }

            $trend = new RechargeConsume();
            $data = $trend->getSeriesData($types, $start, $end, $unit);
            //$data['title'] = DateTools::getRangeTitle($start, $end, $unit) . '数据';
            $this->success('获取成功', $data);
        }
    }

    public function test()
    {
        phpinfo();
        exit();
        $initScore = 0;
        $totalScore = 1000;
        $total = 3;

        $xList = [];
        $yList = [];

        $list = [0, 1, 2, 3, 4];
        foreach ($list as $item){
            $score = $this->easeOutQuint($item, $initScore, $totalScore, $total);
            $xList[] = $item;
            $yList[] = round($score, 3);
        }
        $this->assign('json', json_encode(['x' => $xList, 'y' => $yList]));
        return $this->fetch();
    }


    /*
 * t: current time（当前时间）；
 * b: beginning value（初始值）；
 * c: change in value（变化量）；
 * d: duration（持续时间）。
 */
    private function easeOutQuint($t, $b, $c, $d)
    {
        return $c * (($t = $t / $d - 1) * $t * $t * $t * $t + 1) + $b;
    }

}

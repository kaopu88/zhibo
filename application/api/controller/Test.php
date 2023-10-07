<?php

namespace app\api\controller;

use app\common\controller\Controller;
use app\common\service\User;
use bxkj_common\DataTemplate;
use bxkj_common\KeywordCheck;
use bxkj_module\service\Message;
use RongCloud\RongCloud;
use think\Db;

class Test extends Controller
{
    public function index()
    {
        
    }
    public function paylist()
    {
        $payments = Db::name('payments')->where('status',1)->order('list_order desc')->field('id,class_name,online_pay,name,alias,thumb,coin_type')->select();
        foreach ($payments as &$v){
            $v['taocan'] = Db::name('recharge_bean')->where([
                ['apple_id', 'eq', ''],
                ['status', 'eq', '1']
            ])->whereIn('id',$v['coin_type'])->order('sort asc')->select();
        }
        return $this->success($payments, '获取成功');
    }
}

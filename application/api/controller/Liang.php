<?php


namespace app\api\controller;


use app\common\controller\UserController;
use app\common\service\DsSession;
use app\api\service\user\User_Package;
use app\api\service\user\Props as PropsModel;
use app\api\service\user\User_Props;
use bxkj_common\ClientInfo;
use bxkj_common\CoreSdk;
use bxkj_common\RedisClient;
use think\Db;
use bxkj_module\service\UserRedis;

class Liang extends UserController
{
    protected static $buyPropsType = 'buyProps';

    //我的背包
    public function getUserLiang()
    {   
        
        $res = Db::name('liang')->where('uid',USERID)->select();
        return $this->success($res);
    }


    //获取道具列表
    public function getLiangList()
    {
        $p = (int)request()->param('p');
        $page = empty($p) ? 0 : ($p-1)*10;
        
        $data = Db::name('liang')->where('status',1)->order('list_order desc')->select();

        return $this->success($data);
    }



    //购买道具
    public function payLiang()
    {
        $id = request()->param('id');

        $props_info = Db::name('liang')->where('id',$id)->where('status',1)->find();

        if (empty($props_info)) return $this->jsonError('此款道具已删除~');

        $total = $props_info['coin'];

        $trade_no = get_order_no('buyLiang');

        $coreSdk = new CoreSdk();

        $pay = $coreSdk->payBean([
            'user_id' => USERID,
            'trade_type' => 'buyLiang',
            'trade_no' => $trade_no,
            'total' => $total,
            'client_seri' => ClientInfo::encode()
        ]);

        if (empty($pay)) return $this->jsonError($coreSdk->getError());

        $data = [
            'uid' => USERID,
            'buytime' => time(),
            'status' => 2
        ];

        $res = Db::name('liang')->where('id',$id)->where('status',1)->update($data);

        if (!$res) return $this->jsonError('购买出错, 请联系客服~');

        return $this->success([],'购买成功');
    }


    //使用道具
    public function useLiang()
    {
        $id = request()->param('id');

        $info = Db::name('liang')->where('id',$id)->where('uid',USERID)->find();
        if (empty($info)) return $this->jsonError('靓号未找到~');
        
        Db::startTrans();
        try {
            $res = Db::name('liang')->where('id',$id)->update(['state'=>1]);
            $res1 = Db::name('user')->where('user_id',USERID)->update(['goodnum'=>$info['name']]);
            $res2 = Db::name('liang')->where('id','<>',$id)->where('uid',USERID)->update(['state'=>0]);
            UserRedis::updateData(USERID, ['goodnum'=>$info['name']]);
            Db::commit();
        } catch (\Exception $exception) {
            Db::rollback();
            return $this->jsonError('设置失败了~');
        }
        
        return $this->success('设置成功');

        
    }

}
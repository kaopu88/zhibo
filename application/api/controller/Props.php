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

class Props extends UserController
{
    protected static $buyPropsType = 'buyProps';

    //我的背包
    public function getUserPackage()
    {
        $Package = new User_Package();

        $res = $Package->getUserPackage(USERID);
        if (is_error($res)) return $this->jsonError($res);
        return $this->success($res);
    }


    //获取道具列表
    public function getPropsList()
    {
        $p = (int)request()->param('p');

        $Props = new PropsModel();
        $props_banner = $this->getPropBanner();
        $props_list =  $Props->getPropsList($p);

        return $this->success(['banner' => $props_banner, 'list' => $props_list]);
    }


    //我的道具
    public function getUserProps()
    {
        $params = request()->param();
        $p = isset($params['p']) ? (int)$params['p'] : 0;
        $userId = $params['user_id'];
        $Props = new User_Props();

        $res = $Props->getUserProps($p, $userId);

        return $this->success($res);
    }


    //购买道具
    public function payProps()
    {
        $id = request()->param('id');

        $Props = new PropsModel();

        $props_info = $Props->getPropsItem($id);

        if (empty($props_info)) return $this->jsonError('此款道具已删除~');

        $total = $props_info['price']*$props_info['discount'];

        $trade_no = get_order_no(self::$buyPropsType);

        $coreSdk = new CoreSdk();

        $pay = $coreSdk->payBean([
            'user_id' => USERID,
            'trade_type' => self::$buyPropsType,
            'trade_no' => $trade_no,
            'total' => $total,
            'client_seri' => ClientInfo::encode()
        ]);

        if (empty($pay)) return $this->jsonError($coreSdk->getError());

        $data = [
            'props_id' => $props_info['id'],
            'name' => $props_info['name'],
            'num' => 1,
            'user_id' => USERID,
            'length' => $props_info['length'],
            'icon' => $props_info['user_icon'],
            'create_time' => time(),
        ];

        $UserProps = new User_Props();

        if ($UserProps->addUserProps($data) === false) return $this->jsonError('购买出错, 请联系客服~');

        return $this->success([],'购买成功');
    }


    //使用道具
    public function useProps()
    {
        $id = request()->param('id');

        $usePropsKey = 'BG_PROPS:';

        $is_use = 0;

        $where = [
          ['status', '=', 1],
          ['user_id', '=', USERID],
          ['expire_time', '>', time()],
        ];

        $currentProps = Db::name('user_props')->where($where)->field('id, props_id, name, use_status')->select();


        if (!in_array($id, array_column($currentProps, 'id'))) return $this->jsonError('未找到此款道具, 可能已失效~');

        $redis = RedisClient::getInstance();

        foreach ($currentProps as $props)
        {
            if ($props['id'] == $id)
            {
                if ($props['use_status'] == 1)
                {
                    Db::name('user_props')->where(['id'=>$id])->update(['use_status' => 0]);

                    $redis->del($usePropsKey.USERID);
                }
                else{
                    Db::name('user_props')->where(['id'=>$id])->update(['use_status' => 1]);

                    $redis->set($usePropsKey.USERID, json_encode($props));

                    $is_use = 1;
                }
            }
            else if ($props['use_status'] == 1)
            {
                Db::name('user_props')->where(['id'=>$props['id']])->update(['use_status' => 0]);
            }
        }

        return $this->success(['is_use' => $is_use],'设置成功');
    }


    //获取道具页面banner
    protected function getPropBanner()
    {
        $coreSdk = new CoreSdk();
        $session = DsSession::get();
        $user = $session['user'];
        $purview = $user ? $user['purview'] : '*,not_login';
        $ad = $coreSdk->post('ad/get_contents', [
            'space' => 'props_banner',
            'purview' => $purview,
            'city_id' => '',
            'os' => APP_OS_NAME,
            'code' => APP_CODE,
            'client_seri' => ClientInfo::encode()
        ]);

        return empty($ad) ? (object)[] : $ad;
    }
}
<?php
/**
 * Created by PhpStorm.
 * User: 崔鹏
 * Date: 2020/5/15
 * Time: 9:57
 */

namespace app\shop\controller;

use think\facade\Request;
use think\Db;

class Config extends Controller
{
    /**
     * 商城相关配置
     * @return mixed
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function index()
    {
    }

    /**
     *小店
     * @return mixed
     */
    public function shop()
    {
        $rest                  = $this->checkAuth('shop:config:shop');
        $anchorExpLevelService = new \app\admin\service\AnchorExpLevel();
        $artList               = $anchorExpLevelService->getAll();
        $this->assign('archor_level_list', $artList);
        $expLevelService = new \app\admin\service\ExpLevel();
        $elsList         = $expLevelService->getAll();
        $this->assign('user_level_list', $elsList);
        $ser = new \app\admin\service\SysConfig();
        if (Request::isGet()) {
            $info = $ser->getConfig("mall");
            $info = json_decode($info['value'], true);
            if (empty($info)) {
                $info['fee_retail']                  = 1;
                $info['retail']['level_reward_type'] = 1;
                $info['user_shop']                   = 1;
                $info['audit_model']                 = 1;
                $info['real_name_verify_status']     = 1;
                $info['real_name_type']              = 1;
            }
            $this->assign('_info', $info);
        } else {
            $post = json_encode(input());
            if ($ser->getConfig("mall")) {
                $result = $ser->updateConfig(['mark' => 'mall'], ['value' => $post]);
            } else {
                $result = $ser->addConfig(['mark' => 'mall', 'classified' => 'mall', 'value' => $post]);
            }
            if ($result > 0) {
                $ser->resetConfig();
            }
            alog("shop.shop.set", '编辑商城店铺开通条件');
            $this->success('更新成功', $result);
        }
        return $this->fetch();
    }
}
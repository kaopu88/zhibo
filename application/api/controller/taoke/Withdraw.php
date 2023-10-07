<?php
/**
 * Created by PhpStorm.
 * User: 李成
 * Date: 2020/5/15
 * Time: 10:28
 */
namespace app\api\controller\taoke;

use app\common\controller\UserController;

class Withdraw extends UserController
{
    /**
     * 申请提现
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function applyWithdraw()
    {
        $params = request()->param();
        $data['user_id'] = USERID;
        $data['take_money'] = $params['take_money'];
        $data['cash_account'] = $params['cash_account'];
        $withdraw = new \app\taoke\service\Withdraw();
        $result = $withdraw->apply($data);
        if (!$result) return json_error($withdraw->getError());
        return $this->jsonSuccess($result);
    }

    /**
     * 获取提现记录
     * @return \think\response\Json
     */
    public function getList()
    {
        $list = [];
        $params = request()->param();
        $page = !empty($params['page']) ? $params['page'] : 1;
        $pageSize = !empty($params['pageSize']) ? $params['pageSize'] : 1;

        $where['user_id'] = USERID;
        $where['type'] = "taoke";
        if(!empty($params['status'])){
            if($params['status'] == 1){
                $status = "wait";
            }elseif ($params['status'] == 2){
                $status = "success";
            }elseif ($params['status'] == 3){
                $status = "failed";
            }
            $where['status'] = $status;
        }
        $offset = ($page - 1)*$pageSize;
        $withService = new \app\taoke\service\Withdraw();
        $list = $withService->getList($where, $offset, $pageSize);
        return $this->jsonSuccess($list, "获取成功");
    }

    /**
     * 获取余额记录明细
     * @return \think\response\Json
     */
    public function getMoneyList()
    {
        $params = request()->param();
        $page = !empty($params['page']) ? $params['page'] : 1;
        $pageSize = !empty($params['pageSize']) ? $params['pageSize'] : 1;

        $type = $params['type'];
        if($type == 1){ //收入
            $where[] = ["name", "neq", "extract"];//淘客类订单佣金和分销佣金收入
        }else if ($type == 0){
            $where['name'] = "extract";
        }
        $where['user_id'] = USERID;
        $offset = ($page - 1)*$pageSize;
        $incomeService = new \app\taoke\service\IncomeLog();
        $list = $incomeService->getList($where, $offset, $pageSize);
        return $this->jsonSuccess($list, "获取成功");
    }
}
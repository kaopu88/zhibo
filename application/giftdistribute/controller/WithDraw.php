<?php
/**
 * Created by PhpStorm.
 * User: zack
 * Date: 2020/9/30 0030
 * Time: 上午 9:48
 */

namespace app\giftdistribute\controller;

use app\common\controller\UserController;
use bxkj_common\ClientInfo;
use bxkj_common\CoreSdk;
use bxkj_module\service\User;
use \app\giftdistribute\service\GiftCommissionLog;
use think\Db;

class WithDraw extends UserController
{
    public function index()
    {
        $userModel = new User();
        $user = $userModel->getUser(USERID);
        $data = copy_array($user, 'user_id,nickname,avatar,phone,commission_price,commission_pre_price,commission_total_price');
        $commissionLog = new GiftCommissionLog();

        $data['month_details'] = array(
            'film' => $commissionLog->getIncSum(USERID, 'video_gift', mktime(0, 0, 0, date('m'), 1, date('Y'))),//视频收益
            'live' => $commissionLog->getIncSum(USERID, 'live_gift', mktime(0, 0, 0, date('m'), 1, date('Y'))),//视频收益
            'other' => 0,
        );
        $data['today_details'] = array(
            'film' => $commissionLog->getIncSum(USERID, 'video_gift', mktime(0, 0, 0)),//视频收益
            'live' => $commissionLog->getIncSum(USERID, 'live_gift', mktime(0, 0, 0)),//视频收益
            'other' => 0,
        );
        return $this->success($data);
    }

    /**
     * 记录列表
     */
    public function logs()
    {
        $params = request()->param();
        $params['user_id'] = USERID;
        $params['type'] = 'inc';
        $commissionLog = new GiftCommissionLog();
        $offset = isset($params['offset']) ? $params['offset'] : 0;
        $length = isset($params['length']) ? ($params['length'] > 100 ? 100 : $params['length']) : PAGE_LIMIT;
        if ($params['trade_type'] == 'live') {
            $params['trade_type'] = 'live_gift';
        } else if ($params['trade_type'] == 'film') {
            $params['trade_type'] = 'video_gift';
        } else if ($params['trade_type'] == 'other') {
            $params['trade_type'] = 'other';
        } else {
            return $this->jsonError('交易类型不存在');
        }
        $result = $commissionLog->getList($params, $offset, $length, 1);
        foreach ($result as &$item) {
            $item['create_time'] = date('Y-m-d', $item['create_time']);
            $item['descr'] = "收获{$item['commission_money']} " . config('giftdistribute.name');
            $item['title'] = "收获{$item['commission_money']} " . config('giftdistribute.name');
        }
        return $this->success($result ? $result : []);
    }

    /**
     * 申请提现
     */
    public function commisson_cash()
    {
        $params = request()->param();
        $coreSdk = new CoreSdk();
        $result = $coreSdk->post('millet/commisson_cash', array(
            'user_id' => USERID,
            'millet' => $params['millet'],
            'cash_account' => $params['cash_account'],
            'client_seri' => ClientInfo::encode()
        ));
        if (!$result) return $this->jsonError($coreSdk->getError());
        return $this->success($result, '提现成功，等待审核~');
    }

    public function getFlowAccount()
    {
        $commission_cash_setting = config('giftdistribute.');
        $userModel = new User();
        $user = $userModel->getUser(USERID);
        if (empty($user['phone'])) return $this->jsonError('请先绑定手机号~', 1004);
        $cash_min = $commission_cash_setting['commission_cash_min'];
        $total_cast_max = $user['commission_price'];
        if ($total_cast_max < $cash_min) return $this->jsonError('不能低于' . $cash_min . $commission_cash_setting['name']);
        //获取当前用户所有已绑定的帐户
        $cash_account_info = Db::name('cash_account')
            ->field('account_type, card_name, account, id')
            ->where([
                ['user_id', 'eq', USERID],
                ['verify_status', 'eq', '1'],
                ['delete_time', 'null']
            ])
            ->order('create_time desc')
            ->select();

        $default_account = (object)[];

        if (!empty($cash_account_info)) {
            @list($year, $month) = explode('-', date('Y-m-d'));
            $month_start_time = strtotime(date("{$year}-{$month}-01"));
            $month_end_time = mktime(23, 59, 59, abs($month) + 1, 0, $year);
            $cash_num = Db::name('millet_commison_cash')
                ->where('user_id', 'eq', USERID)
                ->whereTime('create_time', 'between', [$month_start_time, $month_end_time])
                ->count('id');
            $cash_monthlimit = $commission_cash_setting['commission_cash_monthlimit'];

            if ($cash_num >= $cash_monthlimit) return $this->jsonError('每月最多可提现' . $cash_monthlimit . '次');

            $cash_account = Db::name('millet_commison_cash')->where('user_id', 'eq', USERID)->order('create_time desc')->limit(1)->value('cash_account');
            if (!empty($cash_account)) {
                foreach ($cash_account_info as $account) {
                    if ($account['id'] == $cash_account) {
                        $default_account = $account;
                        break;
                    }
                }
                if (is_object($default_account)) $default_account = array_shift($cash_account_info);
            } else {
                //最新添加的帐户作为默认使用帐户
                $default_account = array_shift($cash_account_info);
            }
        }

        return $this->success([
            'change_ratio' => $commission_cash_setting['commission_cash_rate'],
            'cash_fee' => isset($commission_cash_setting['commission_cash_fee']) ? $commission_cash_setting['commission_cash_fee'] : 0,
            'default_account' => $default_account,
            'can_millet' => $total_cast_max
        ]);
    }

    /**
     * 提现记录列表
     */
    public function commisson_cash_list()
    {
        $params = request()->param();
        $offset = $params['offset'] ?: 0;
        $where = [
            ['mc.user_id', 'eq', USERID]
        ];
        $status = ['wait' => '进行中', 'success' => '结算成功', 'failed' => '结算失败'];

        if (!empty($params['year'])) {
            if (strpos($params['year'], '-') === false) return $this->jsonError('日期格式错误');
            @list($search_year, $search_month) = explode('-', $params['year']);
            $start_time = strtotime(date("{$search_year}-{$search_month}-01"));
            $end_time = mktime(23, 59, 59, abs($search_month) + 1, 0, $search_year);
            array_push($where, ['mc.create_time', 'egt', $start_time]);
            array_push($where, ['mc.create_time', 'elt', $end_time]);
        }

        $cash_logs = Db::name('millet_commison_cash')->alias('mc')
            ->join('__CASH_ACCOUNT__ ca', 'mc.cash_account=ca.id')
            ->field('cash_no number, mc.admin_remark remark, ca.card_name, mc.millet, rmb, mc.status, mc.create_time, ca.account_type, ca.account')
            ->where($where)
            ->order('mc.create_time desc')
            ->paginate(['list_rows' => 20, 'page' => $offset + 1]);

        if ($cash_logs->isEmpty()) return $this->success(['list' => []]);
        $cashs = $cash_logs->items();

        $data = [];

        foreach ($cashs as $value) {
            $value['flow_time'] = self::diff_time($value['create_time']);
            $value['status_str'] = $status[$value['status']];
            $value['create_time_str'] = date('Y-m-d H:i', $value['create_time']);
            $key = date('Y-m', $value['create_time']);
            if (!isset($data[$key])) $data[$key] = [];
            array_push($data[$key], $value);
        }

        $list = [];

        @list($tYear, $tMonth) = explode('-', date('Y-m'));
        foreach ($data as $date => $value) {
            @list($_year, $_month) = explode('-', $date);
            if ($_year == $tYear) {
                $month_str = $tMonth == $_month ? '本月' : abs($_month) . '月';
            } else {
                $month_str = $_year . '年' . $_month . '月';
            }

            $total = $this->getCashTotal($_year, $_month);
            $tmp['month'] = $month_str;
            $tmp['total_millet'] = $total['millet'];
            $tmp['total_rmb'] = $total['rmb'];
            $tmp['list'] = $value;
            array_push($list, $tmp);
        }

        return $this->success([
            'list' => $list,
            'pages' => ceil($cash_logs->total() / 15)
        ]);
    }

    //获取月份下的提现统计
    protected function getCashTotal($year, $month)
    {
        $start_time = strtotime(date("{$year}-{$month}-01"));

        $end_time = mktime(23, 59, 59, abs($month) + 1, 0, $year);

        $summary = Db::name('millet_commison_cash')
            ->field('sum(millet) millet, sum(rmb) rmb')
            ->where([
                ['user_id', 'eq', USERID]
            ])
            ->whereTime('create_time', 'between', [$start_time, $end_time])
            ->find();

        return $summary;
    }

    protected static function diff_time($time)
    {
        $diff = time() - $time;
        switch (true) {
            case $diff < 86400 :
                $res = '今天 ';
                break;
            case $diff < 172800 :
                $res = '昨天 ';
                break;
            case $diff < 259200 :
                $res = '前天 ';
                break;
            default:
                $res = date('m/j', $time) . '日 ';
        }
        return $res . date('h:i', $time);
    }
}
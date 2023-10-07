<?php

namespace app\api\controller;

use app\common\controller\UserController;
use app\common\service\DsSession;
use think\Db;
use app\api\service\CashAccount;
use app\api\service\MilletCash;
use app\api\service\MilletLog;
use app\api\service\Rank;
use bxkj_common\ClientInfo;
use bxkj_common\CoreSdk;
use think\facade\Request;

class Millet extends UserController
{
    public function index()
    {
        $user = $this->user;
        $data = copy_array($user, 'user_id,nickname,avatar,phone,millet,millet_status,his_millet,total_millet,fre_millet');
        $millet = new MilletLog();
        //本月收益
        $data['millet_details'] = array(
            'film' => $millet->getIncSum(USERID, 'pay_per_view,video_gift', mktime(0, 0, 0, date('m'), 1, date('Y'))),//视频收益
            'live' => $millet->getLiveSum(USERID, 'm', date('Ym')),//直播收益
            'other' => $millet->getIncSum(USERID, 'liveDynamic,shareDynamic,followFriends,postVideo,watchVideo,commentDynamic,dayShareRoom,dayReward,dayRecharge,dailyLogin,inviteFriends,shareVideo,commentVideo,thumbsVideo,cover_star_vote', mktime(0, 0, 0, date('m'), 1, date('Y'))),
        );
        $data['today_details'] = array(
            'film' => $millet->getIncSum(USERID, 'pay_per_view,video_gift', mktime(0, 0, 0)),//视频收益
            'live' => $millet->getLiveSum(USERID, 'd', date('Ymd')),//直播收益
            'other' => $millet->getIncSum(USERID, 'liveDynamic,shareDynamic,followFriends,postVideo,watchVideo,commentDynamic,dayShareRoom,dayReward,dayRecharge,dailyLogin,inviteFriends,shareVideo,commentVideo,thumbsVideo,cover_star_vote', mktime(0, 0, 0)),
        );
        $rate = config('app.app_setting.millet_rate');
        $data['rate'] = (string)$rate;
        return $this->success($data);
    }

    //提现结算
    public function cash()
    {
        $params = request()->param();
        $coreSdk = new CoreSdk();
        $result = $coreSdk->post('millet/cash', array(
            'user_id' => USERID,
            'millet' => $params['millet'],
            'cash_account' => $params['cash_account'],
            'client_seri' => ClientInfo::encode()
        ));
        if (!$result) return $this->jsonError($coreSdk->getError());
        return $this->success($result);
    }


    public function getBindAccount()
    {
        $phone = DsSession::get('user.phone');
        $type = 'alipay';
        $where = ['type' => $type, 'user_id' => USERID];
        $info = Db::name('cash_account')->field('id,name,account')->where($where)->find();
        $info = $info ? $info : [
            'name' => '',
            'account' => '',
            'id' => 0
        ];
        $info['phone'] = $phone;
        $info['id'] = (int)$info['id'];
        return $this->success($info, '获取成功');
    }

    public function bindAccount()
    {
        $params = request()->param();
        $params['user_id'] = USERID;
        $core = new CoreSdk();
        $res = $core->post('common/check_sms_code', array(
            'phone' => DsSession::get('user.phone'),
            'phone_code' => DsSession::get('user.phone_code'),
            'scene' => 'auth_cash',
            'code' => $params['code'],
            'client_seri' => ClientInfo::encode()
        ));
        if (!$res) return $this->jsonError($core->getError());
        $cashAccount = new CashAccount();
        unset($params['code']);
        $result = $cashAccount->bindAccount($params);
        if (!$result) return $this->jsonError($cashAccount->getError());
        return $this->success($result, '绑定成功');
    }


    public function getAccountInfo()
    {
        $params = request()->param();
        $cashAccount = new CashAccount();
        $params['user_id'] = USERID;
        $info = $cashAccount->getInfo($params);
        if (!$info) return $this->jsonError($cashAccount->getError());
        return $this->success($info);
    }

    public function editAccount()
    {
        $cashAccount = new CashAccount();
        $params = request()->param();
        $params['user_id'] = USERID;
        $result = $cashAccount->updateAccount($params);
        if (!$result) return $this->jsonError($cashAccount->getError());
        return $this->success($result);
    }

    public function delAccount()
    {
        $cashAccount = new CashAccount();
        $params = request()->param();
        $params['user_id'] = USERID;
        $result = $cashAccount->delAccount($params);
        if (!$result) return $this->jsonError($cashAccount->getError());
        return $this->success($result, '删除成功~');
    }

    //记录列表
    public function logs()
    {
        $params = request()->param();
        $params['user_id'] = USERID;
        $params['type'] = 'inc';
        $milletLog = new MilletLog();
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
        $result = $milletLog->getList($params, $offset, $length);
        return $this->success($result ? $result : []);
    }

    public function getCharmRank()
    {
        $params = request()->param();
        $rank = new Rank();
        $offset = isset($params['offset']) ? $params['offset'] : 0;
        $length = isset($params['length']) ? ($params['length'] > 100 ? 100 : $params['length']) : PAGE_LIMIT;
        $stealth_exp = !empty(USERID) ? [USERID] : [];
        $result = $rank->getList(array(
            'name' => 'charm',
            'interval' => $params['interval'],
            'order' => 'desc'
        ), $offset, $length, array(
            'scoreKey' => 'millet',
            'memberKey' => 'user_id',
            'stealth' => 'rank_stealth',
            'stealth_exp' => $stealth_exp,
        ));
        if (!$result) return $this->jsonError($rank->getError());
        return $this->success($result, '获取成功');
    }

    public function getHeroesRank()
    {
        $params = request()->param();
        $rank = new Rank();
        $offset = isset($params['offset']) ? $params['offset'] : 0;
        $length = isset($params['length']) ? ($params['length'] > 100 ? 100 : $params['length']) : PAGE_LIMIT;
        $stealth_exp = !empty(USERID) ? [USERID] : [];
        $result = $rank->getList(array(
            'name' => 'heroes:gift',
            'interval' => $params['interval'],
            'order' => 'desc'
        ), $offset, $length, array(
            'scoreKey' => 'millet',
            'memberKey' => 'user_id',
            'stealth' => 'rank_stealth',
            'stealth_exp' => $stealth_exp,
        ));
        if (!$result) return $this->jsonError($rank->getError());
        return $this->success($result, '获取成功');
    }

    //获取提现记录
    public function getCashLogs()
    {
        $params = request()->param();
        $params['user_id'] = USERID;
        $params['type'] = 'inc';
        $milletCash = new MilletCash();
        $offset = isset($params['offset']) ? $params['offset'] : 0;
        $length = isset($params['length']) ? ($params['length'] > 100 ? 100 : $params['length']) : PAGE_LIMIT;
        $result = $milletCash->getList($params, $offset, $length);
        return $this->success($result ? $result : []);
    }


    protected static $account_name = ['支付宝', '微信钱包'];


    /**
     * 提现结算
     *
     * @return \think\response\Json
     */
    public function flow()
    {
        $params = Request::param();
        $coreSdk = new CoreSdk();
        $result = $coreSdk->post('millet/cash', array(
            'user_id' => USERID,
            'millet' => (int)$params['millet'],
            'cash_account' => $params['account_id'],
            'client_seri' => ClientInfo::encode()
        ));
        if (!$result) return $this->jsonError($coreSdk->getError());
        return $this->success($result, '提现成功，等待审核~');
    }


    /**
     * 添加提现帐户
     *
     * @return \think\response\Json
     */
    public function addAccount()
    {
        $params = Request::param();

        if (!in_array($params['account_type'], [0, 1])) $this->jsonError('不支持的帐户类型~');

        $real_name = Db::name('user_verified')->where([['user_id', 'eq', USERID], ['status', 'eq', 1]])->value('name');

        if (empty($real_name)) return $this->jsonError('请先完成实名认证~', 1006);

        $phone = DsSession::get('user.phone');

        if (empty($phone)) return $this->jsonError('请先绑定手机号~', 1004);

        $core = new CoreSdk();

        //验证验证码
        $res = $core->post('common/check_sms_code', array(
            'phone' => $phone,
            'phone_code' => DsSession::get('user.phone_code'),
            'scene' => 'auth_cash',
            'code' => $params['code'],
            'client_seri' => ClientInfo::encode()
        ));

        if (!$res) return $this->jsonError($core->getError());

        //生成微信提现帐户
        $cash_insert = [
            'user_id' => USERID,
            'account_type' => $params['account_type'],
            'card_name' => self::$account_name[$params['account_type']],
            'account' => $params['account'],
            'name' => $params['real_name'],
            'open_id' => $params['account_type'] == 1 ? $params['open_id'] : '',
            'verify_status' => '1',
            'create_time' => time()
        ];

        $where = [
            ['user_id', 'eq', USERID],
            ['account_type', 'eq', $params['account_type']]
        ];

        $params['account_type'] == 0 ? array_push($where, ['account', 'eq', $params['account']]) : array_push($where, ['open_id', 'eq', $params['open_id']]);

        $res = Db::name('cash_account')->where($where)->where('delete_time', 'null')->find();

        if (!empty($res)) return $this->jsonError('当前帐户已添加~');

        $last_id = Db::name('cash_account')->insertGetId($cash_insert);

        if (!$last_id) return $this->jsonError('新增帐户出错~');

        $cash_insert['id'] = $last_id;

        unset($cash_insert['create_time'], $cash_insert['user_id'], $cash_insert['name'], $cash_insert['verify_status'], $cash_insert['open_id']);

        return $this->success($cash_insert, '添加提现帐户成功~');
    }



    /**
     * 提现帐户列表
     * @return \think\response\Json
     */
    public function getAccountList()
    {
        $params = Request::param('p', 1);

        $cash_info = Db::name('cash_account')
            ->field('account_type, card_name, account, id')
            ->where([
                ['user_id', 'eq', USERID],
                ['verify_status', 'eq', '1'],
                ['delete_time', 'null']
            ])
            ->order('create_time desc')
            ->paginate(['list_rows'=>20, 'page'=>$params['p']]);

        if ($cash_info->isEmpty()) return $this->success(['list' => [], 'total' => 0]);

        $result = array(
            'list' => $cash_info->items(),
            'total' => $cash_info->total()
        );
        return $this->success($result);
    }


    /**
     * 获取提现详情
     *
     * @return \think\response\Json
     */
    public function getFlowAccount()
    {
        //系统提现额度540000
        $system_cash_max = 540000;
        //脱换比率
        $change_ratio = config('app.cash_setting.cash_rate');
        $new_task = config('app.new_people_task_config');
        //当日可提现额度
        $user_info = Db::name('user')
            ->field('millet, fre_millet, reg_way, phone, isvirtual,is_anchor')
            ->where([
                ['user_id', 'eq', USERID],
                ['millet_status', 'eq', 1],
                ['status', 'eq', 1]
            ])->find();


        if (empty($user_info)) return $this->jsonError('暂无提现权限~，请联系客服');
        //虚似用户不可提现
        if ($user_info['isvirtual'] && RUNTIME_ENVIROMENT == 'pro') return $this->jsonError('暂无提现权限~，请联系客服');
        //未绑定手机号引民至绑定手机号
        if (empty($user_info['phone'])) return $this->jsonError('请先绑定手机号~', 1004);

        if ($user_info['is_anchor'] == '0') $change_ratio = config('app.cash_setting.cash_user_rate');
        $anchor = Db::name('anchor')->where(['user_id' => USERID])->find();
        if (!empty($anchor) && !empty($anchor['cash_rate']) && $anchor['cash_rate'] != '0.00') $change_ratio = $anchor['cash_rate'];

        $cash_min = config('app.cash_setting.cash_min');
        $total_cast_max = $user_info['millet'];

        if ($new_task['is_status'] == 2) {
            $num = Db::name('millet_cash')->where([
                ['user_id', 'eq', USERID],
                ['status', 'neq', 'failed'],
            ])->count();
            if (empty($num)) $cash_min = $new_task['new_first_withdraw'];
        }

        if ($total_cast_max < $cash_min) return $this->jsonError('不能低于'.$cash_min.APP_MILLET_NAME);

        $start_time = strtotime(date('Y-m-d'));
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

        //有绑定提现帐户(查看是否已提现记录)
        if (!empty($cash_account_info))
        {
            //获取当月提现次数
            @list($year, $month, ) = explode('-', date('Y-m-d'));
            $month_start_time = strtotime(date("{$year}-{$month}-01"));
            $month_end_time = mktime(23, 59, 59, abs($month)+1, 0, $year);
            $cash_num = Db::name('millet_cash')
                ->where('user_id', 'eq', USERID)
                ->whereTime('create_time', 'between', [$month_start_time, $month_end_time])
                ->count('id');
            $cash_monthlimit = config('app.cash_setting.cash_monthlimit');

            if ($cash_num > $cash_monthlimit) return $this->jsonError('每月最多可提现'.$cash_monthlimit.'次');

            //获取当日已提现额度
            $cash_total = Db::name('millet_cash')
                ->where('user_id', 'eq', USERID)
                ->whereTime('create_time', 'between', [$start_time, $start_time+86400])
                ->where('status', 'in', ['wait','success'])
                ->sum('millet');

            if (!empty($cash_total))
            {
                $system_cash_max -= $cash_total;

                if ($system_cash_max < 0) return $this->jsonError('今日提现额度已用完~');

                //获取最新提现帐户
                $cash_account = Db::name('millet_cash')
                    ->where('user_id', 'eq', USERID)
                    ->order('create_time desc')
                    ->limit(1)
                    ->value('cash_account');

                foreach ($cash_account_info as $account)
                {
                    if ($account['id'] == $cash_account) {
                        $default_account = $account;
                        break;
                    }
                }
                if (is_object($default_account)) $default_account = array_shift($cash_account_info);
            }
            else{
                //最新添加的帐户作为默认使用帐户
                $default_account = array_shift($cash_account_info);
            }
        } else{
            $real_name = Db::name('user_verified')->where([['user_id', 'eq', USERID], ['status', 'eq', 1]])->value('name');

            if (empty($real_name)) return $this->jsonError('请先完成实名认证~', 1006);
            //检查帐户登录方式
            if ($user_info['reg_way'] == 'third_weixin')
            {
                $openid = Db::name('user_third')->where([['user_id', 'eq', USERID], ['status', 'eq', 1], ['type', 'eq', 'weixin']])->value('openid');

                if (!empty($openid))
                {
                    //生成微信提现帐户
                    $cash_insert = [
                        'user_id' => USERID,
                        'account_type' => 1,
                        'card_name' => self::$account_name[1],
                        'account' => $user_info['phone'],
                        'name' => $real_name,
                        'open_id' => $openid,
                        'verify_status' => '1',
                        'create_time' => time()
                    ];

                    $last_id = Db::name('cash_account')->insertGetId($cash_insert);

                    if ($last_id)
                    {
                        $default_account = [
                            'account_type' => 1,
                            'card_name' => '微信钱包',
                            'account' => $user_info['phone'],
                            'id' => $last_id
                        ];
                    }
                }
            }
        }

        return $this->success([
            'system_limit' => $system_cash_max,
            'change_ratio' => $change_ratio,
            'default_account' => $default_account,
            'can_millet' => $total_cast_max
        ]);
    }


    /**
     * 获取提现记录
     * @return \think\response\Json
     */
    public function getFlowLogs()
    {
        $params = Request::param();

        $offset = Request::param('offset', 0);

        $where = [
            ['mc.user_id', 'eq', USERID]
        ];

        $status = ['wait' => '进行中', 'success' => '结算成功', 'failed' => '结算失败'];

        if (!empty($params['year']))
        {
            if (strpos($params['year'], '-') === false) return $this->jsonError('日期格式错误');

            @list($search_year, $search_month) = explode('-', $params['year']);

            $start_time = strtotime(date("{$search_year}-{$search_month}-01"));

            $end_time = mktime(23, 59, 59, abs($search_month)+1, 0, $search_year);

            array_push($where, ['mc.create_time', 'egt', $start_time]);

            array_push($where, ['mc.create_time', 'elt', $end_time]);
        }

        $cash_logs = Db::name('millet_cash')->alias('mc')
            ->join('__CASH_ACCOUNT__ ca', 'mc.cash_account=ca.id')
            ->field('cash_no number, mc.admin_remark remark, ca.card_name, mc.millet, rmb, mc.status, mc.create_time, ca.account_type, ca.account')
            ->where($where)
            ->order('mc.create_time desc')
            ->paginate(['list_rows'=>20, 'page'=>$offset+1]);

        if ($cash_logs->isEmpty()) return $this->success(['list' => []]);

        $cashs = $cash_logs->items();

        $data = [];

        foreach ($cashs as $value)
        {
            $value['flow_time'] = self::diff_time($value['create_time']);
            $value['status_str'] = $status[$value['status']];
            $value['create_time_str'] = date('Y-m-d H:i', $value['create_time']);
            $key = date('Y-m', $value['create_time']);
            if (!isset($data[$key])) $data[$key] = [];
            array_push($data[$key], $value);
        }

        $list = [];

        @list($tYear, $tMonth) = explode('-', date('Y-m'));

        foreach ($data as $date => $value)
        {
            @list($_year, $_month) = explode('-', $date);

            if ($_year == $tYear)
            {
                $month_str = $tMonth == $_month ? '本月' : abs($_month).'月';
            }
            else{
                $month_str = $_year.'年'.$_month.'月';
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
            'pages' => ceil($cash_logs->total()/15)
        ]);
    }


    //获取月份下的提现统计
    protected function getCashTotal($year, $month)
    {
        $start_time = strtotime(date("{$year}-{$month}-01"));

        $end_time = mktime(23, 59, 59, abs($month)+1, 0, $year);

        $summary = Db::name('millet_cash')
            ->field('sum(millet) millet, sum(rmb) rmb')
            ->where([
                ['user_id', 'eq', USERID]
            ])
            ->whereTime('create_time', 'between', [$start_time, $end_time])
//            ->where('status', 'eq', 'success')
            ->find();

        return $summary;
    }


    protected static function diff_time($time)
    {
        $diff = time()-$time;
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
                $res = date('m/j', $time).'日 ';
        }
        return $res.date('h:i', $time);
    }




    /**
     * 消费明细
     *
     */
    public function consumerDetails()
    {
        $p = Request::param('p', 1);

        $res = Db::name('bean_log')
            ->field('total, trade_type, create_time')
            ->where([
                ['type', 'eq', 'exp'],
                ['user_id', 'eq', USERID]
            ])->order('id desc')->paginate(['list_rows' => self::$pnum, 'page' => $p]);

        if ($res->isEmpty()) return $this->success([]);

        $data = $res->items();

        $trade_types = config('enum.trade_types');

        $trade_types = array_column($trade_types, 'name', 'value');

        foreach ($data as &$value)
        {
            if (!array_key_exists($value['trade_type'], $trade_types)) continue;

            $value['trade_type'] = $trade_types[$value['trade_type']];

            $value['create_time'] = date('y-m H:i', $value['create_time']);

            $value['amount'] =  1;
        }

        return $this->success($data);
    }




}

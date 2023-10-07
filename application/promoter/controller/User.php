<?php
namespace app\promoter\controller;
use app\promoter\service\KpiCons;
use bxkj_common\RedisClient;
use app\promoter\service\RechargeOrder;
use bxkj_common\ClientInfo;
use bxkj_common\CoreSdk;
use bxkj_module\service\UserRedis;
use think\Db;
use think\facade\Request;

class User extends Controller
{
    public function index()
    {
        $this->lastSendTime();
        $userService = new \app\promoter\service\User();
        $get = input();
        $get = array_merge([
            'promoter_uid' => $this->admin['promoter_uid'],
        ], $get);
        $total = $userService->getTotal($get);
        $page = $this->pageshow($total);
        $users = $userService->getList($get, $page->firstRow, $page->listRows);
        $this->assign('_list', $users);
        $this->assign('is_root',$this->admin['is_root']);
        return $this->fetch();
    }

    public function detail()
    {
        $userId = input('user_id');
        $userService = new \app\promoter\service\User();
        $user = $userService->getInfo($userId);
        if (empty($user)) $this->error('用户不存在');
        $this->assign('user', $user);
        //消费记录
        $consGet = [
            'user_id' => $userId,
        ];
        $kpiConsService = new KpiCons();
        $consTotal = $kpiConsService->getTotal($consGet);
        $consList = $kpiConsService->getList($consGet, 0, 5);
        $this->assign('cons_list', $consList);
        $this->assign('cons_total', $consTotal);

        $rechargeOrderService = new RechargeOrder();
        $rechargeTotal = $rechargeOrderService->getTotal($consGet);
        $rechargeList = $rechargeOrderService->getList($consGet, 0, 5);
        $this->assign('recharge_list', $rechargeList);
        $this->assign('recharge_total', $rechargeTotal);

        return $this->fetch();
    }

    public function promoter_detail()
    {
        $userId = input('user_id');
        $userService = new \app\promoter\service\User();
        $user = $userService->getInfo($userId);
        if (empty($user)) $this->error('用户不存在');
        $this->assign('user', $user);
            //消费记录
        $consGet = [
        'user_id' => $userId,
        ];
        $kpiConsService = new KpiCons();
        $consTotal = $kpiConsService->getTotal($consGet);
        $consList = $kpiConsService->getList($consGet, 0, 5);
        $this->assign('cons_list', $consList);
        $this->assign('cons_total', $consTotal);

        $rechargeOrderService = new RechargeOrder();
        $rechargeTotal = $rechargeOrderService->getTotal($consGet);
        $rechargeList = $rechargeOrderService->getList($consGet, 0, 5);
        $this->assign('recharge_list', $rechargeList);
        $this->assign('recharge_total', $rechargeTotal);

        return $this->fetch('detail');
    }

    public function cons()
    {
        $get = input();
        $kpiConsService = new KpiCons();
        $total = $kpiConsService->getTotal($get);
        $page = $this->pageshow($total);
        $list = $kpiConsService->getList($get, $page->firstRow, $page->listRows);
        $this->assign('cons_list', $list);
        return $this->fetch();
    }

    //查找用户
    public function find()
    {
        $get = input();
        $userService = new \app\promoter\service\User();
        $total = $userService->getTotal($get);
        $page = $this->pageshow($total);
        $userList = $userService->getList($get, $page->firstRow, $page->listRows);
        $this->assign('_list', $userList);
        return $this->fetch();
    }

    public function reg()
    {
        $phone = input('phone');
        $code = input('code');
        $promoterUid = input('promoter_uid');
        $password = input('password');
        if (empty($phone)) $this->error('手机号不能为空');
        if (empty($code)) $this->error('手机号不能为空');
        if (empty($password)) $this->error('请设置密码');
        $core = new CoreSdk();
        //注册用户
        $data = array(
            'phone' => $phone,
            'code' => $code,
            'password' => $password,
            'client_seri' => ClientInfo::encode()
        );
        if (!empty($promoterUid)) {
            $data['promoter_uid'] = $promoterUid;
        } else {
            $data['agent_id'] = AGENT_ID;
        }
        $user = $core->post('user/create_by_phone', $data);
        if (!$user) $this->error($core->getError());
        $this->success('注册成功', $user);
    }

    public function change_live_status()
    {
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择用户');
        $live_status = input('live_status');
        if (!in_array($live_status, ['0', '1'])) $this->error('状态值不正确');
        $userService = new \app\admin\service\User();
        $num = $userService->changeLiveStatus($ids, $live_status, AGENT_ID);
        if (!$num) $this->error('切换状态失败');
        $this->success('切换成功');
    }

    public function query_cons()
    {
        if (Request::isGet()) {
            return $this->fetch();
        } else {

            $num = cache('query_cons_num');
            if ($num && $num >= 3) {
                $this->error('前面有很多用户正在查询，请您稍后');
            }
            $num = $num + 1;
            cache('query_cons_num', $num, 300);

            $startTime = input('start_time');
            $endTime = input('end_time');
            $userId = input('user_id');
            if (empty($startTime) || empty($endTime) || empty($userId)) {
                $this->error('参数不全');
            }
            $agentService = new \app\promoter\service\Agent();
            $check_agent = $agentService->check_agent($userId);
            if (!$check_agent) $this->error($agentService->getError());
            $startTime = strtotime($startTime);
            $endTime = strtotime($endTime);
            $time = mktime(0, 0, 0, 9, 1, 2018);
            $total = 0;
            $totalFee = 0;
            if ($startTime < $time) {
                $t1 = $startTime;
                $t2 = $time;
                $config = [
                     'hostname' => 'rm-8vbergl96621ruci8.mysql.zhangbei.rds.aliyuncs.com',
            'database' => 'live_ihuanyu_tv',
            'username' => 'huanyutv_rds',
            'password' => 'Xu8244288*',
                    'debug' => false,
                    'hostport' => 3306,
                    'prefix' => 'cmf_'
                ];
                $config = array_merge(config('database.'), $config);
                $totalFee = Db::connect($config)->name('users_coinrecord')->where([
                    ['uid', 'eq', $userId],
                    ['isvirtual', 'eq', '0'],
                    ['addtime', '>=', $t1],
                    ['addtime', '<', $t2],
                ])->sum('totalcuckoo');
                $total += $totalFee;
            }
            $totalFee2 = 0;
            if ($endTime > $time) {
                $t1 = max($time, $startTime);
                $t2 = $endTime;
                $totalFee2 = Db::name('gift_log')->where([
                    ['user_id', 'eq', $userId],
                    ['isvirtual', 'eq', '0'],
                    ['create_time', '>=', $t1],
                    ['create_time', '<', $t2],
                ])->sum('price');
                $total += $totalFee2;
            }
            //'，其中9月1日前的累计是' . $totalFee . '，9月1日后是' . $totalFee2
            cache('query_cons_num', $num - 1);
            $this->success('累计'.APP_BEAN_NAME.'为：' . $total . '折合人民币' . ($total / RECHARGE_RATE));
        }
    }

    public function get_unread_num()
    {
        $notice = ["id" => ''];
        $redis = RedisClient::getInstance();
        
        $where = 'status = 1 and visible in(0,2) and barrage = 1';
        $admin_notice = Db::name('admin_notice')->where($where)->order('sort desc,create_time desc')->select();
        $AGENT_ID = AGENT_ID;
        foreach ($admin_notice as $item) {
            if (!$redis->zScore("admin:notice:{$AGENT_ID}", $item['id'])) {
                $notice = $item;
                break;
            }
        }

        if(!empty($notice)){
            $notice['url'] = url('user/notice_detail',['id'=>$notice['id']]);
            $notice['close_notice_url'] = url('user/close_notice',['id'=>$notice['id']]);
        }

        return json_success([
            'notice' => $notice
        ], '获取成功');
    }

    public function close_notice()
    {
        $id = input('id');
        $redis = RedisClient::getInstance();
        $AGENT_ID = AGENT_ID;
        $key = "admin:notice:{$AGENT_ID}";
        $redis->zAdd($key, time(), $id);
        return $this->get_unread_num();
    }

    public function notice_detail(){
        $id = input('id');
        $info = Db::name('admin_notice')->where('id', $id)->find();
        if (empty($info)) $this->error('公告不存在');
        $this->assign('_info', $info);
        return $this->fetch();
    }

    public function reset_nickname()
    {
        $userId = input('user_id');
        if (empty($userId)) $this->error('请选择用户');
        $agentService = new \app\promoter\service\Agent();
        $check_agent = $agentService->check_agent($userId);
        if (!$check_agent) $this->error($agentService->getError());
        $update = ['nickname' => '已重置', 'last_renick_time' => null];
        $num = Db::name('user')->where(['user_id' => $userId])->update($update);
        if (!$num) $this->error('重置昵称失败');
        \app\admin\service\User::updateRedis($userId, $update);
        $this->success('重置昵称成功');
    }

    public static function createRandStr($len, $chars = null)
    {
        if (!$chars) {
            $chars = '0123456789abcdefghijklmnopqrstuvwxyz';
        }

        return substr(str_shuffle(str_repeat($chars, rand(5, 8))), 0, $len);
    }

    public function reset_password()
    {
        $userId = input('user_id');
        $user = Db::name('user')->where(['delete_time' => null, 'user_id' => $userId])->find();
        if (empty($user)) $this->error('请选择用户');
        $agentService = new \app\promoter\service\Agent();
        $check_agent = $agentService->check_agent($userId);
        if (!$check_agent) $this->error($agentService->getError());
        $code = self::createRandStr(6);
        $data['isset_pwd'] = '1';
        $data['salt'] = sha1(uniqid() . get_ucode(8));
        $data['password'] = sha1($code . $data['salt']);
        $data['change_pwd_time'] = time();
        $num = Db::name('user')->where(['user_id' => $userId])->update($data);
        if (!$num) $this->error('重置密码失败');
        $sdk = new CoreSdk();
        $sdk->post('common/send_sms_code', array(
            'phone' => $user['phone'],
            'scene' => 'admin_reset_pwd',
            'phone_code' => '86',
            'params' => ['user_id'=>$userId, 'password'=>$code]
        ));
        UserRedis::updateData($userId, $data);
        $this->success('重置密码成功');
    }

    public function reset_avatar()
    {
        $userId = input('user_id');
        if (empty($userId)) $this->error('请选择用户');
        $agentService = new \app\promoter\service\Agent();
        $check_agent = $agentService->check_agent($userId);
        if (!$check_agent) $this->error($agentService->getError());
        $update = ['avatar' => img_url('', '', 'avatar')];
        $num = Db::name('user')->where(['user_id' => $userId])->update($update);
        if (!$num) $this->error('重置头像失败');
        \app\admin\service\User::updateRedis($userId, $update);
        $this->success('重置头像成功');
    }

    public function reset_cover()
    {
        $userId = input('user_id');
        if (empty($userId)) $this->error('请选择用户');
        $agentService = new \app\promoter\service\Agent();
        $check_agent = $agentService->check_agent($userId);
        if (!$check_agent) $this->error($agentService->getError());
        $update = ['cover' => img_url('', '', 'cover')];
        $num = Db::name('user')->where(['user_id' => $userId])->update($update);
        if (!$num) $this->error('重置封面失败');
        \app\admin\service\User::updateRedis($userId, $update);
        $this->success('重置封面成功');
    }

    public function reset_rename_time()
    {
        $userId = input('user_id');
        if (empty($userId)) $this->error('请选择用户');
        $agentService = new \app\promoter\service\Agent();
        $check_agent = $agentService->check_agent($userId);
        if (!$check_agent) $this->error($agentService->getError());
        $update = ['last_renick_time' => null];
        $num = Db::name('user')->where(['user_id' => $userId])->update($update);
        if (!$num) $this->error('重置限制时间失败');
        \app\admin\service\User::updateRedis($userId, $update);
        $this->success('重置限制时间成功');
    }

}

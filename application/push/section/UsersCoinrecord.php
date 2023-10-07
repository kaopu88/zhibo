<?php

namespace app\push\section;

use bxkj_common\DateTools;
use bxkj_common\RedisClient;
use bxkj_module\service\Kpi;
use bxkj_module\service\MyQuery;
use bxkj_module\service\Service;
use bxkj_common\SectionMarkExecuter;
use think\Db;

class UsersCoinrecord extends SectionMarkExecuter
{
    protected $lockName = 'users_coinrecord';
    protected $dbConfig;
    protected $gifts;

    public function __construct()
    {
        parent::__construct();
        $config = config('database.');
        $config['database'] = 'phonelive';
        $config['prefix'] = 'cmf_';
        $config['hostname'] = 'rm-wz9u4e9h49kgr623q.mysql.rds.aliyuncs.com';
        $config['username'] = 'bugucmad1';
        $config['password'] = 'Bugulive002!sql%ad02';
        $this->dbConfig = $config;
        $this->gifts = cache('old_gifts');
        if (empty($this->gifts)) {
            $this->gifts = $this->db('gift')->select();
            cache('old_gifts', $this->gifts);
        }
    }

    public function complete($data)
    {
        if ($data['status'] == 0) {
        }
    }

    protected function db($table = null)
    {
        $db = Db::connect($this->dbConfig);
        if (isset($table)) $db->name($table);
        if (false) $db = new MyQuery();
        return $db;
    }

    public function handler($length = 10)
    {
        $total = 0;
        $this->wait();
        $this->lock();
        $this->db()->startTrans();
        $where = [['processed', 'eq', '0'], ['isvirtual', 'eq', '0']];
        $list = $this->db('users_coinrecord')->where($where)->order('id asc')->limit($length)->select();
        if (empty($list)) {
            $this->db()->rollback();
            $this->unlock();
            return $this->success(true, $total);
        }
        $ids = [];
        foreach ($list as $item) {
            $ids[] = $item['id'];
        }
        if (!empty($ids)) {
            $this->db('users_coinrecord')->whereIn('id', $ids)->update(['processed' => 1]);
        }
        $this->db()->commit();
        $this->unlock();
        foreach ($list as $item) {
            $this->handlerOne($item);
            $total++;
        }
        return $this->success(false, $total);
    }

    protected function handlerOne($item)
    {
        if (empty($item['addtime'])) return false;
        Service::startTrans();
        $res = false;
        if ($item['action'] == 'sendgift') {
            $res = $this->gift($item);
        } else if ($item['action'] == 'sendbarrage') {
            $res = $this->barrage($item);
        }
        if (!$res) {
            Service::rollback();
            return false;
        }
        Service::commit();
        return true;
    }

    protected function gift($item)
    {
        $giftInfo = $this->getGiftInfo($item['giftid']);
        $num = (int)$item['giftcount'];
        $log = [
            'gift_no' => get_order_no('gift', $item['addtime']),
            'gift_id' => $item['giftid'],
            'user_id' => $item['uid'],
            'to_uid' => $item['touid'],
            'price' => $item['totalcuckoo'],
            'name' => ($giftInfo && $giftInfo['giftname']) ? $giftInfo['giftname'] : '未知礼物',
            'type' => ($giftInfo && $giftInfo['type']) ? $giftInfo['type'] : 1,
            'isvirtual' => '0',
            'cid' => '0',
            'conv_millet' => $item['totalcuckoo'],
            'picture_url' => ($giftInfo && $giftInfo['gifticon']) ? $giftInfo['gifticon'] : '',
            'num' => $num,
            'create_time' => $item['addtime']
        ];
        $id = Db::name('gift_log')->insertGetId($log);
        if (!$id) return $this->setError('gift_log insert error ' . $item['id']);
        $res2 = $this->bean_log([
            'user_id' => $log['user_id'],
            'bean_num' => $log['price'] * $log['num'],
            'trade_type' => 'gift',
            'trade_no' => $log['gift_no'],
            'create_time' => $log['create_time']
        ]);
        if (!$res2) return false;
        return $this->millet_log([
            'total' => $log['conv_millet'] * $log['num'],
            'user_id' => $log['to_uid'],
            'cont_uid' => $log['user_id'],
            'trade_type' => 'gift',
            'trade_no' => $log['gift_no'],
            'exchange_type' => 'gift',
            'exchange_id' => $item['giftid'],
            'exchange_total' => $num,
            'create_time' => $item['addtime']
        ]);
    }

    protected function barrage($item)
    {
        $num = (int)$item['giftcount'];
        $no = get_order_no('barrage', $item['addtime']);
        $res2 = $this->bean_log([
            'user_id' => $item['uid'],
            'bean_num' => $item['totalcuckoo'] * $num,
            'trade_type' => 'barrage',
            'trade_no' => $no,
            'create_time' => $item['addtime']
        ]);
        if (!$res2) return false;
        return $this->millet_log([
            'total' => $item['totalcuckoo'] * $num,
            'user_id' => $item['touid'],
            'cont_uid' => $item['uid'],
            'trade_type' => 'barrage',
            'trade_no' => $no,
            'exchange_type' => '',
            'exchange_id' => 0,
            'exchange_total' => 0,
            'create_time' => $item['addtime']
        ]);
    }

    protected function bean_log($order)
    {
        $last = [
            'total_bean' => 0,
            'fre_bean' => 0,
            'bean' => 0
        ];
        $log_no = get_order_no('log', $order['create_time']);
        $log = [
            'log_no' => $log_no,
            'user_id' => $order['user_id'],
            'type' => 'exp',
            'total' => $order['bean_num'],
            'trade_type' => $order['trade_type'],
            'trade_no' => $order['trade_no'],
            'last_total_bean' => $last['total_bean'],
            'last_fre_bean' => $last['fre_bean'],
            'last_bean' => $last['bean'],
            'total_bean' => $last['total_bean'] - $order['bean_num'],
            'fre_bean' => 0,
            'bean' => 0,
            'client_ip' => '',
            'app_v' => '',
            'create_time' => $order['create_time']
        ];
        $id = Db::name('bean_log')->insertGetId($log);
        if (!$id) return $this->setError('bean_log insert error recharge ' . $order['trade_no']);
        $tradeType = $order['trade_type'];
        $total = $order['bean_num'];
        $user = $this->getUserInfo($order['user_id']);
        if ($tradeType == 'gift') {
            self::updateHeroesRank($user, 'gift', $total, $order['create_time']);// 用户英雄榜 只计算礼物
        }
        self::updateHeroesRank($user, 'all', $total, $order['create_time']);//股权榜 计算所有
        $consRes = $this->cons($user, $log);
        return $consRes;
    }

    //更新英雄榜
    protected function updateHeroesRank($user, $type, $total, $time = null)
    {
        if ($time) {
            $weekNum = DateTools::getWeekNum($time);
            $redis = RedisClient::getInstance();
            $hisk = "rank:heroes:{$type}:history";//总历史榜
            $yk = "rank:heroes:{$type}:y:" . date('Y', $time);//年榜
            $mk = "rank:heroes:{$type}:m:" . date('Ym', $time);//月榜
            $wk = "rank:heroes:{$type}:w:" . $weekNum;//周榜
            $dk = "rank:heroes:{$type}:d:" . date('Ymd', $time);//日榜
            $userId = $user['user_id'];
            //同步增长
            $redis->zIncrBy($hisk, $total, $userId);
            $redis->zIncrBy($yk, $total, $userId);
            $redis->zIncrBy($mk, $total, $userId);
            $redis->zIncrBy($wk, $total, $userId);
            $redis->zIncrBy($dk, $total, $userId);
        }
    }

    protected function millet_log($trade)
    {
        $last = [
            'total_millet' => 0,
            'millet' => 0,
            'fre_millet' => 0
        ];
        $total = $trade['total'];
        $log = [
            'log_no' => get_order_no('log', $trade['create_time']),
            'cont_uid' => $trade['cont_uid'],
            'user_id' => $trade['user_id'],
            'type' => 'inc',
            'total' => $total,
            'trade_type' => $trade['trade_type'],
            'trade_no' => $trade['trade_no'],
            'last_total_millet' => $last['total_millet'],
            'last_fre_millet' => 0,
            'last_millet' => $last['millet'],
            'total_millet' => $last['total_millet'] + $total,
            'fre_millet' => 0,
            'millet' => $last['millet'] + $total,
            'isvirtual' => '0',
            'client_ip' => '',
            'app_v' => '',
            'exchange_type' => $trade['exchange_type'] ? $trade['exchange_type'] : '',
            'exchange_id' => $trade['exchange_id'] ? $trade['exchange_id'] : 0,
            'exchange_total' => $trade['exchange_total'] ? $trade['exchange_total'] : 0,
            'create_time' => $trade['create_time']
        ];
        $id = Db::name('millet_log')->insertGetId($log);
        if (!$id) return $this->setError('millet_log insert error ' . $trade['trade_type'] . $trade['trade_no']);
        $tradeType = $trade['trade_type'];
        $type = 'inc';
        $isvirtual = '0';
        $contributor = $this->getUserInfo($trade['cont_uid']);
        $user = $this->getAnchorInfo($trade['user_id']);
        if ($tradeType == 'gift' && $type == 'inc') {
            if ($isvirtual == 0) {
                $this->millet($contributor, $user, $log);
            }
            //收到礼物转化而来的谷子计入主播魅力榜
            $this->updateCharmRank($user, $total, $log['create_time']);
        }
        return true;
    }

    protected function getGiftInfo($giftId)
    {
        $giftInfo = Service::getItemByList($giftId, $this->gifts, 'id');
        return $giftInfo;
    }

    public function millet($contributor, $anchor, $log)
    {
        $originalId = is_array($anchor) ? $anchor['user_id'] : $anchor;
        $originalId = $originalId ? $originalId : 0;
        //虚拟用户和机器人不参与业绩统计
        if ($contributor['isvirtual'] != '0') return true;
        $contAgentId = 0;
        //统计推广员的业绩(贡献谷子)
        if ($contributor['promoter_uid']) {
            //推广员和用户在同一家代理商(转移时一定要注意变更)
            $contAgentId = $contributor['agent_id'] ? $contributor['agent_id'] : 0;
        }

        $anchor = $originalId ? $this->getAnchorInfo($originalId) : [];

        //记录明细
        $data = [];
        $data['agent_id'] = $anchor ? $anchor['agent_id'] : 0;;//主播所在的代理商ID
        $data['promoter_uid'] = $contributor['promoter_uid'] ? $contributor['promoter_uid'] : 0;
        $data['cont_uid'] = $contributor['user_id'];
        $data['cont_agent_id'] = $contAgentId;//贡献者所在的代理商ID(和推广员在同一家)
        $data['trade_type'] = $log['trade_type'];
        $data['trade_no'] = $log['trade_no'];
        $data['log_no'] = $log['log_no'];
        $data['millet'] = $log['total'];//谷子数
        $data['get_uid'] = ($anchor && $anchor['user_id']) ? $anchor['user_id'] : $originalId;//获得者UID 即主播
        $this->supplementaryTime($data, $log['create_time']);
        $id = Db::name('kpi_millet')->insertGetId($data);
        if (!$id) return false;
        return true;
    }

    //主播魅力榜
    protected function updateCharmRank($user, $total, $time = null)
    {
        if ($time) {
            $redis = RedisClient::getInstance();
            $hisk = 'rank:charm:history';//总历史榜
            $yk = 'rank:charm:y:' . date('Y', $time);//年榜
            $mk = 'rank:charm:m:' . date('Ym', $time);//月榜
            $wk = 'rank:charm:w:' . DateTools::getWeekNum($time);//周榜
            $dk = 'rank:charm:d:' . date('Ymd', $time);//日榜
            $userId = $user['user_id'];
            //同步增长
            $redis->zIncrBy($hisk, $total, $userId);
            $redis->zIncrBy($yk, $total, $userId);
            $redis->zIncrBy($mk, $total, $userId);
            $redis->zIncrBy($wk, $total, $userId);
            $redis->zIncrBy($dk, $total, $userId);
        }
    }


    //业绩统计
    protected function cons($user, $log)
    {
        $total = $log['total'];
        $user = is_array($user) ? $user : ['user_id' => $user];
        if (!isset($user['promoter_uid']) || !isset($user['agent_id']) || !isset($user['isvirtual'])) {
            $user = $this->getUserInfo($user['user_id']);
            if (empty($user)) return false;
        }
        if (!$user || $user['isvirtual'] != '0' || $total <= 0) return true;
        //记录明细
        $data = [];
        $data['agent_id'] = $user['agent_id'] ? $user['agent_id'] : 0;
        $data['promoter_uid'] = $user['promoter_uid'] ? $user['promoter_uid'] : 0;
        $data['trade_no'] = $log['log_no'];//实际上这里是log_no
        $data['rel_type'] = $log['trade_type'];
        $data['rel_no'] = $log['trade_no'] ? $log['trade_no'] : '';
        $data['total_fee'] = $total;
        $data['subject'] = '';
        $data['pay_method'] = '';
        $data['pay_platform'] = '';
        $data['cons_uid'] = $user['user_id'];
        $data['cons_phone'] = $user['phone'] ? $user['phone'] : '';
        $this->supplementaryTime($data, $log['create_time']);
        $id = Db::name('kpi_cons')->insertGetId($data);
        if (!$id) return false;
        return true;
    }

    //补充时间参数
    protected function supplementaryTime(&$data, $time)
    {
        $data['year'] = date('Y', $time);
        $data['month'] = date('Ym', $time);
        $data['day'] = date('Ymd', $time);
        $data['fnum'] = DateTools::getFortNum($time);
        $data['create_time'] = $time;
    }

    protected function getUserInfo($userId)
    {
        $newUser = [
            'user_id' => $userId,
            'promoter_uid' => 0,
            'agent_id' => 0,
            'isvirtual' => 0,
            'phone' => ''
        ];
        $user = $this->getUser($userId);
        if ($user) {
            $newUser['promoter_uid'] = $user['shareid'] ? $user['shareid'] : 0;
            $newUser['phone'] = $user['mobile'] ? $user['mobile'] : '';
            if (!empty($newUser['promoter_uid'])) {
                $promoter = $this->getUser($newUser['promoter_uid']);
                $newUser['agent_id'] = $promoter['spread_id'] ? $promoter['spread_id'] : 0;
            }
        }
        return $newUser;
    }

    protected function getUser($userId)
    {
        $key = "old_user:{$userId}";
        $redis = RedisClient::getInstance();
        $json = $redis->get($key);
        $oldUser = $json ? json_decode($json, true) : null;
        if (empty($oldUser)) {
            $oldUser = $this->db('users')->where('id', $userId)->find();
            if ($oldUser) $redis->set($key, json_encode($oldUser));
        }
        return $oldUser;
    }


    //获取主播信息
    protected function getAnchorInfo($userId)
    {
        $newAnchor = ['user_id' => $userId, 'agent_id' => 0, 'isvirtual' => 0];
        $anchor = $this->getUser($userId);
        if ($anchor) {
            $newAnchor['agent_id'] = $anchor['agent_id'] ? $anchor['agent_id'] : 0;
        }
        return $newAnchor;
    }


}
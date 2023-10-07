<?php

namespace app\service;


/**
 * 消费结算模式
 *
 * Class Kpi
 * @package app\service
 */
class Kpi
{
    /*
     * agent:all:millet:d:20180212 不区分一级二级整个平台的代理商排名 millet是收入谷子
     * agent:一级代理商ID:millet:d:20180212
     * anchor:all:millet:d:20180822 不区分代理商，整个平台的主播排名
     * anchor:代理商ID:millet:d:20180822
     * promoter:all:cons:d:20180821 不区分代理商，整个平台的推广员排名
     * promoter:代理商ID:cons:d:20180821
     */

    const REL_TYPES = ['vip', 'recharge'];
    const INDICATORS = ['allfans', 'fans', 'allcons', 'cons', 'allmillet', 'millet', 'duration', 'allduration', 'active', 'allactive'];
    protected $now;
    protected $day;
    protected $fNum;
    protected $month;
    protected $year;
    protected $map = [
        'promoter' => 'user_id',
        'anchor' => 'user_id',
        'agent' => 'id',
        'user' => 'user_id'
    ];
    protected $redisConfig = [];
    protected $updateDb = true;

    public function __construct($time = null, $updateDb = true, $redisConfig = [])
    {
        $this->updateDb = $updateDb;
        $this->redisConfig = $redisConfig;
        $this->refreshTime($time);
    }


    /**
     * 统计主播业绩
     *
     * @param $contributor //消费者
     * @param $anchor //主播
     * @param $log
     * @return bool
     */
    public function millet($contributor, $anchor, $log)
    {
        //虚拟用户和机器人不参与业绩统计
        if ($contributor['isvirtual'] != '0') return true;

        $anchor_id = is_array($anchor) ? $anchor['user_id'] : $anchor;

        $tmpWhere = ['user_id' => $contributor['user_id']];

        $rel = Db::findItem('promotion_relation', $tmpWhere, '', false);

        $anchorWhere = ['user_id' => $anchor_id];
        $relAnchor = Db::findItem('promotion_relation', $anchorWhere, '', false);
        Db::startTrans();

        //记录明细
        $data = [];
        $data['agent_id'] = !empty($relAnchor['agent_id']) ? $relAnchor['agent_id'] : 0;//主播所在的代理商ID
        $data['promoter_uid'] = isset($rel['promoter_uid']) ? $rel['promoter_uid'] : 0; //消费者的推广
        $data['cont_uid'] = $contributor['user_id']; //消费者UID
        $data['cont_agent_id'] = isset($rel['agent_id']) ? $rel['agent_id'] : 0; //消费者所属代理
        $data['trade_type'] = $log['trade_type'];
        $data['trade_no'] = $log['trade_no'];
        $data['is_prifit'] = isset($log['is_prifit']) ? $log['is_prifit'] : 0;
        $data['log_no'] = $log['log_no'];
        $data['millet'] = $log['total'];//谷子数
        $data['get_uid'] = $anchor_id;//获得者UID 即主播
        $this->supplementaryTime($data);
        $id = Db::insert('kpi_millet', $data);
        if (!$id) return $this->rollbackFalse();
        if ($data['is_prifit'] == 0) {
            $res = $this->milletRebuildByLog($data);
            if (!$res) return $this->rollbackFalse();
        }
        Db::commit();
        return true;
    }


    /**
     * 统计经纪人业绩
     *
     * @param $toUid
     * @param $user
     * @param $log
     * @return bool
     */
    public function cons($toUid, $user, $log)
    {
        $lossTotal = isset($log['loss_total']) ? (int)$log['loss_total'] : 0;

        $user = is_array($user) ? $user : ['user_id' => $user];

        if (!isset($user['isvirtual']))
        {
            $user = $this->getUserInfo($user['user_id']);

            if (empty($user)) return false;
        }

        //如果是虚似号则不统计
        if (!$user || $user['isvirtual'] != '0' || ($log['total'] <= 0 && $lossTotal <= 0)) return true;

        $tmpWhere = ['user_id' => $user['user_id']];

        $rel = Db::findItem('promotion_relation', $tmpWhere, '', false);

        //开起事务
        Db::startTrans();
        //记录明细
        $data = [];
        $data['agent_id'] = isset($rel['agent_id']) ? (int)$rel['agent_id'] : 0;
        $data['promoter_uid'] = isset($rel['promoter_uid']) ? (int)$rel['promoter_uid'] : 0;
        $data['trade_no'] = $log['log_no'];//实际上这里是log_no
        $data['rel_type'] = $log['trade_type'];
        $data['rel_no'] = $log['trade_no'] ? $log['trade_no'] : '';
        $data['total_fee'] = $log['total'];
        $data['is_prifit'] = isset($log['is_prifit']) ? $log['is_prifit'] : 0;
        $data['loss_total'] = $lossTotal;
        $data['subject'] = '';
        $data['pay_method'] = '';
        $data['pay_platform'] = '';
        $data['cons_uid'] = $user['user_id'];
        $data['cons_phone'] = $user['phone'] ? $user['phone'] : '';
        $this->supplementaryTime($data);
        $id = Db::insert('kpi_cons', $data);
        if (!$id) return $this->rollbackFalse();
        if ($data['is_prifit'] == 0) {
            $res = $this->consRebuildByLog($data);
            if (!$res) return $this->rollbackFalse();
        }
        Db::commit();
        return true;
    }



    //拉新统计
    public function fans($promoter, $newUser)
    {
        //模拟的和非正常用户不计入
        if ($newUser['isvirtual'] != '0') return true;
        Db::startTrans();
        //记录明细
        $data = [];
        $data['agent_id'] = $newUser['agent_id'] ? $newUser['agent_id'] : 0;
        $data['promoter_uid'] = $promoter['user_id'] ? $promoter['user_id'] : 0;
        $data['user_id'] = $newUser['user_id'];
        $data['avatar'] = $newUser['avatar'];
        $data['nickname'] = $newUser['nickname'] ? $newUser['nickname'] : '';
        $data['gender'] = $newUser['gender'] ? $newUser['gender'] : '0';
        $data['phone'] = $newUser['phone'] ? $newUser['phone'] : '';
        $data['anchor_uid'] = $newUser['anchor_uid'] ? $newUser['anchor_uid'] : 0;
        $this->supplementaryTime($data);
        $id = Db::insert('kpi_fans', $data);
        if (!$id) return $this->rollbackFalse();
        $res = $this->fansRebuildByLog($data);
        if (!$res) return $this->rollbackFalse();
        Db::commit();
        return true;
    }

    //通过记录表重建Redis数据
    public function fansRebuildByLog($log)
    {
        $this->refreshTime($log['create_time']);
        //推广员业绩+1
        if ($log['promoter_uid']) {
            if (!$this->incr('promoter', $log['promoter_uid'], 'fans', 1)) return false;
        }
        $selfAgentId = $this->getSelfAgentId($log);
        //代理商自身的（不包括其子代理商）
        if (!empty($selfAgentId)) {
            if (!$this->incr('agent', $selfAgentId, 'fans', 1)) return false;
        }
        return true;
    }


    public function milletRebuildByLog($log)
    {
        $this->refreshTime($log['create_time']);
        //统计推广员的业绩(贡献谷子)
        if ($log['promoter_uid']) {
            if (!$this->incr('promoter', $log['promoter_uid'], 'millet', $log['millet'])) return false;
        }
        $get_uid = $log['get_uid'];//主播ID（散播也算）
        if ($get_uid) {
            $selfAgentId = $this->getSelfAgentId($log);
            if (!empty($selfAgentId)) {
                if (!$this->incr('agent', $selfAgentId, 'millet', $log['millet'])) return false;
            }
            if (!$this->incr('anchor', $get_uid, 'millet', $log['millet'])) return false;
        }
        return true;
    }


    /**
     * 更新缓存推广员和代理商业绩
     *
     * @param $log
     * @return bool
     */
    public function consRebuildByLog($log)
    {
        $this->refreshTime($log['create_time']);
        //统计推广员的业绩
        if ($log['promoter_uid']) {
            if (!$this->incr('promoter', $log['promoter_uid'], 'cons', $log['total_fee'])) return false;
        }
        //代理商自身的业绩统计
        $selfAgentId = $this->getSelfAgentId($log);
        if (!empty($selfAgentId)) {
            if (!$this->incr('agent', $selfAgentId, 'cons', $log['total_fee']))
                return false;
        }
        return true;
    }

    //活跃度
    public function active($anchor, $user)
    {
        if (empty($user) || empty($anchor)) return false;
        $anchor = is_array($anchor) ? $anchor : ($this->getAnchorInfo($anchor));
        $user = is_array($user) ? $user : ($this->getUserInfo($user));
        if (empty($user) || empty($anchor)) return false;
        if (isset($user['isvirtual']) && $user['isvirtual'] != '0') return true;
        $agentId = $anchor['agent_id'];
        $actRedis = new ActiveRedis($user['user_id'], 'anchor', $anchor['user_id'], $agentId, $this->now);
        $actRedis->active(1);
        if (!empty($agentId)) {
            $agent = Db::findItem('agent', 'id', $agentId, true);
            if ($agent) {
                $actRedis2 = new ActiveRedis($user['user_id'], 'agent', $agentId, $agent['pid'] ? $agent['pid'] : '', $this->now);
                $actRedis2->active(1);
            }
        }
        return true;
    }

    public function live($user)
    {
    }


    //总时长
    protected function duration($anchor, $duration)
    {
        if (empty($anchor) || empty($duration)) return false;
        $min = (int)bg_get_config('live_effective_time');
        if ($duration <= $min) return true;
        if (!is_array($anchor)) {
            $anchor = Db::findItem('anchor', 'user_id', $anchor, false);
        }
        if (!isset($anchor['agent_id']) && !empty($anchor['user_id'])) {
            $anchor = Db::findItem('anchor', 'user_id', $anchor['user_id'], false);
        }
        if (empty($anchor)) return false;
        Db::startTrans();
        if (!$this->incr('anchor', $anchor['user_id'], 'duration', $duration)) {
            return $this->rollbackFalse();
        }
        $selfAgentId = $this->getSelfAgentId($anchor);
        if (!empty($selfAgentId)) {
            if (!$this->incr('agent', $selfAgentId, 'duration', $duration)) {
                return $this->rollbackFalse();
            }
        }
        Db::commit();
        return true;
    }


    //更新缓存
    protected function incr($userType, $userId, $indicator, $num = 0, $probability = null)
    {
        if (empty($userType)) return false;
        $key = $this->map[$userType];
        if (empty($key)) return false;
        $userId = is_array($userId) ? $userId[$key] : $userId;
        if (!in_array($indicator, self::INDICATORS)) return false;
        if ($num == 0) return true;
        $selfAgentId = '';
        $fun = strtolower($userType) . 'Updater';
        if (!method_exists($this, $fun)) return false;
        $res = call_user_func_array([$this, $fun], [$userId, $indicator, $num, &$selfAgentId]);
        if (!$res) return false;
        $redisConfig = $this->redisConfig;
        if (isset($probability)) $redisConfig['probability'] = $probability;
        $kpiRedis = new KpiRedis($this->now, $redisConfig);
        $res3 = $kpiRedis->setUser($userType, $userId)->setAgent('all')->setIndicator($indicator)->dayInc($num);
        //$selfAgentId标记代理商的，主要区分代理商下的排名和总平台排名(这个逻辑不考虑一级代理商下的排名，只考虑自身一级的)
        if (!empty($selfAgentId) && $res3) {
            $kpiRedis->setUser($userType, $userId)->setAgent($selfAgentId)->setIndicator($indicator)->dayInc($num);
        }
        return true;
    }

    //更新推广员主表
    protected function promoterUpdater($userId, $indicator, $num, &$selfAgentId)
    {
        //Db::startTrans();
        $mk = 'total_' . $indicator;
        $promoter = Db::findItem('promoter', 'user_id', $userId, false);
        if ($promoter) {
            $selfAgentId = $this->getSelfAgentId($promoter);
            if ($this->updateDb) {
                $total = $promoter[$mk] + $num;
                //$res = Db::update('promoter', 'id', $promoter['id'], [$mk => $total]);
                //Db::commit();
                $res = 1;
                return $res > 0;
            }
        }
        //Db::commit();
        return true;
    }

    //更新主播主表
    protected function anchorUpdater($userId, $indicator, $num, &$selfAgentId)
    {
        //Db::startTrans();
        $mk = 'total_' . $indicator;
        $anchor = Db::findItem('anchor', 'user_id', $userId, false);
        if ($anchor) {
            $selfAgentId = $this->getSelfAgentId($anchor);
            if ($this->updateDb) {
                $total = $anchor[$mk] + $num;
                //$res = Db::update('anchor', 'id', $anchor['id'], [$mk => $total]);
                //Db::commit();
                $res = 1;
                return $res > 0;
            }
        }
        //Db::commit();
        return true;
    }

    //更新代理商主播
    protected function agentUpdater($id, $indicator, $num, &$selfAgentId)
    {
        //Db::startTrans();
        $mk = 'total_' . $indicator;
        $agent = Db::findItem('agent', 'id', $id, true);
        if (!empty($agent)) {
            $selfAgentId = $agent['pid'] ? $agent['pid'] : '';
            if ($this->updateDb) {
                $total = $agent[$mk] + $num;
                //$res = Db::update('agent', 'id', $agent['id'], [$mk => $total]);
                //Db::commit();
                $res = 1;
                return $res > 0;
            }
        }
        //Db::commit();
        return true;
    }

    protected function userUpdater($userId, $indicator, $num, &$selfAgentId)
    {
        //Db::startTrans();
        $mk = 'total_' . $indicator;
        $user = Db::findItem('user', 'user_id', $userId, true);
        if ($user) {
            $selfAgentId = $this->getSelfAgentId($user);
            if ($this->updateDb) {
                $total = $user[$mk] + $num;
                //$res = Db::update('user', 'user_id', $user['user_id'], [$mk => $total]);
                //Db::commit();
                $res = 1;
                return $res > 0;
            }
        }
        //Db::commit();
        return true;
    }

    public static function getSelfAgentId($user)
    {
        return $user['agent_id'];
    }

    protected function getAnchorInfo($anchorUid)
    {
        return Db::findItem('anchor', 'user_id', $anchorUid, false);
    }

    protected function getUserInfo($userId)
    {
        return Db::findItem('user', 'user_id', $userId, true);
    }

    protected function rollbackFalse()
    {
        Db::rollback();
        return false;
    }

    protected function refreshTime($time = null)
    {
        $this->now = isset($time) ? $time : time();
        $this->day = date('Ymd', $this->now);
        $this->month = date('Ym', $this->now);
        $this->year = date('Y', $this->now);
        $this->fNum = DateTools::getFortNum($this->now);
    }

    //补充时间参数
    protected function supplementaryTime(&$data)
    {
        $data['year'] = $this->year;
        $data['month'] = $this->month;
        $data['day'] = $this->day;
        $data['fnum'] = $this->fNum;
        $data['create_time'] = $this->now;
        $data['dweek'] = 0;
        $data['week'] = DateTools::getWeekNum($this->now);
    }

}
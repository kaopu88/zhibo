<?php

namespace app\admin\service;

use bxkj_module\service\Bean;
use bxkj_module\service\ExpLevel;
use bxkj_common\CoreSdk;
use bxkj_common\RedisClient;
use think\Db;

class User extends \bxkj_module\service\User
{
    public function getTotal($get)
    {
        $this->db = Db::name('user');
        $this->setWhere($get);
        $count = $this->db->count();
        return (int)$count;
    }

    public function getList($get, $offset = 0, $length = 20)
    {
        $this->db = Db::name('user');
        $this->setWhere($get)->setOrder($get);
        $result = $this->db->limit($offset, $length)->select();
        $regionService = new Region();
        list($cityIds) = self::getIdsByList($result, 'city_id', true);
        list($fenxiao_level) = self::getIdsByList($result, 'fenxiao_level', true);
        $cityList = $regionService->getCitesByIds($cityIds);
        $levelList = $this->getLevelByIds($fenxiao_level);
        foreach ($result as &$item) {
            $item = array_merge($item, ExpLevel::getLevelInfo($item['level']));
            self::parseUser($item);
            $item['city_info'] = self::getItemByList($item['city_id'], $cityList, 'id');
            $item['fenxiao_level_name'] = self::getItemByList($item['fenxiao_level'], $levelList, 'id');

            $item['rec_num'] = Db::name('recommend_content')->where(['rel_type' => 'user', 'rel_id' => $item['user_id']])->count();
            $item['agent_num'] = Db::name('promotion_relation')->where(['user_id' => $item['user_id']])->count();
            $item['agent_info'] = Db::name('promotion_relation')->where(['user_id' => $item['user_id']])->find();
            $item['agent_name'] = Db::name('agent')->field('name')->where(['id' => $item['agent_info']['agent_id']])->value('name');

            if ($item['is_anchor'] == '1') {
                $item['anchor_info'] = Db::name('anchor')->alias('anchor')
                    ->field('anchor.user_id,agent.name agent_name,agent.logo agent_logo,agent.id agent_id')
                    ->where(['anchor.user_id' => $item['user_id']])
                    ->join('__AGENT__ agent', 'agent.id=anchor.agent_id')->find();
            }
            if ($item['is_promoter'] == '1') {
                $item['promoter_info'] = Db::name('promoter')->alias('promoter')
                    ->field('promoter.user_id,agent.name agent_name,agent.logo agent_logo,agent.id agent_id')
                    ->where(['promoter.user_id' => $item['user_id']])
                    ->join('__AGENT__ agent', 'agent.id=promoter.agent_id')->find();
            }
        }
        Bean::parseBeanAccForUsers($result);
        return $result;
    }

    public function getLevelByIds($ids)
    {
        if (empty($ids)) return [];
        $result = Db::name('gift_commission_level')->whereIn('id', $ids)->field('id,level_name')->limit(count($ids))->select();
        return $result ? $result : [];
    }

    protected function setOrder($get)
    {
        $order = array();
        if (empty($get['sort'])) {
            $order['create_time'] = 'DESC';
        }
        $this->db->order($order);
        return $this;
    }

    protected function setWhere($get)
    {
        $where = [['delete_time', 'null']];
        if ($get['status'] != '') {
            $where[] = ['status', '=', $get['status']];
        }
        if ($get['live_status'] != '') {
            $where[] = ['live_status', '=', $get['live_status']];
        }
        $this->vipStatusWhere($where, $get);
        $this->userTypeWhere($where, $get);
        if ($get['level'] != '') {
            $where[] = ['level', '=', $get['level']];
        }
        if ($get['province'] != '') {
            $where[] = ['province_id', '=', $get['province']];
        }
        if ($get['city'] != '') {
            $where[] = ['city_id', '=', $get['city']];
        }
        if ($get['district'] != '') {
            $where[] = ['district_id', '=', $get['district']];
        }
        if ($get['taoke_level'] != '') {
            $where[] = ['taoke_level', '=', $get['taoke_level']];
        }
        if ($get['district'] != '') {
            $where[] = ['district_id', '=', $get['district']];
        } else if ($get['city'] != '') {
            $where[] = ['city_id', '=', $get['city']];
        }
        if ($get['uids']) {
            $where[] = ['user_id', 'in', $get['uids']];
        }
        if ($get['uidsoff']) {
            $where[] = ['user_id', 'not in', $get['uidsoff']];
        }
        if ($get['taoke_shop']) {
            $where[] = ['taoke_shop', '=', $get['taoke_shop']];
        }
        if ($get['shop_id']) {
            $where[] = ['shop_id', '=', $get['shop_id']];
        }
        if ($get['relation_id']) {
            $where[] = ['relation_id', '=', $get['relation_id']];
        }
        if ($get['special_id']) {
            $where[] = ['special_id', '=', $get['special_id']];
        }
        if ($get['pdd_pid']) {
            $where[] = ['pdd_pid', '=', $get['pdd_pid']];
        }
        if ($get['jd_pid']) {
            $where[] = ['jd_pid', '=', $get['jd_pid']];
        }
        $this->db->setKeywords(trim($get['keyword']), 'phone phone', 'number user_id', 'nickname,remark_name,number phone,username');
        $this->db->where($where);
        return $this;
    }

    public function getInfo($userId)
    {
        $coreSdk = new CoreSdk();
        $user = $coreSdk->getUser($userId);
        if (!empty($user)) {
            $agentService = new Agent();
            $info = Db::name('user')->where('user_id', $userId)->field('remark_name,remark')->find();
            $levelInfo = ExpLevel::getLevelInfo($user['level']);
            $user = array_merge($user, $info, $levelInfo);
            $beanInfo = Db::name('bean')->where(['user_id' => $user['user_id']])
                ->field('bean,fre_bean,pay_status,total_bean,last_change_time bean_change_time,recharge_total,last_pay_time,loss_bean')->find();
            $user = array_merge($user, $beanInfo ? $beanInfo : []);
            $cashInfo = Db::name('millet_cash')->where(['user_id' => $user['user_id ']])->whereIn('status', ['wait', 'success'])
                ->order('create_time desc')->find();
            $user['millet_change_time'] = !empty($cashInfo) ? $cashInfo['create_time'] : null;

            $user['agent_info'] = Db::name('promotion_relation')->alias('pl')
                ->field('agent.name')
                ->where(['pl.user_id' => $user['user_id']])
                ->join('__AGENT__ agent', 'agent.id=pl.agent_id')
                ->find();
        }
        return $user;
    }

    public function getUserInfo($where)
    {
        $info = Db::name('user')->where($where)->find();
        return $info;
    }

    public function refreshRedis($userId)
    {
        $redis = RedisClient::getInstance();
        $num = $redis->del('user:' . $userId);
        if ($num) {
            $key = "loginstate:{$userId}";
            if ($redis->exists($key)) {
                $redis->hIncrBy("loginstate:{$userId}", 'update_v', 1);
            }
        }
        return true;
    }

    public function getSuggests($keyword, $length = 10)
    {
        $this->db = Db::name('user');
        $this->db->setKeywords($keyword, 'phone phone', '', 'nickname,remark_name,number phone');
        $result = $this->db->limit(0, $length)->select();
        $arr = [];
        foreach ($result as $item) {
            $arr[] = [
                'value' => $item['user_id'],
                'name' => $item['nickname'] . ($item['phone'] ? "({$item['phone']})" : '')
            ];
        }
        return $arr;
    }

    public function getClientTotal($get)
    {
        $this->db = Db::name('user');
        $this->setClientWhere($get);
        $count = $this->db->count();
        return (int)$count;
    }

    public function getClientList($get, $offset = 0, $length = 20)
    {
        $this->db = Db::name('user');
        $this->setClientWhere($get)->setClientOrder($get);
        $result = $this->db->limit($offset, $length)->select();
        foreach ($result as &$item) {
            $item = array_merge($item, ExpLevel::getLevelInfo($item['level']));
            self::parseUser($item);
        }
        Bean::parseBeanAccForUsers($result);
        return $result;
    }

    protected function setClientWhere($get)
    {
        $this->db->alias('user');
        $this->db->join('__PROMOTION_RELATION__ pr', 'user.user_id=pr.user_id');
        $where = [['user.delete_time', 'null']];
        if ($get['promoter_uid']) {
            $where[] = ['pr.promoter_uid', '=', $get['promoter_uid']];
        }
        if ($get['status'] != '') {
            $where[] = ['user.status', '=', $get['status']];
        }
        $this->vipStatusWhere($where, $get);
        $this->userTypeWhere($where, $get);
        if ($get['level'] != '') {
            $where[] = ['user.level', '=', $get['level']];
        }
        $this->db->setKeywords(trim($get['keyword']), 'phone user.phone', 'number user.user_id', 'user.remark_name,user.nickname,number user.phone');
        $this->db->where($where);
        return $this;
    }

    protected function setClientOrder($get)
    {
        $order = array();
        if (empty($get['sort'])) {
            $order['user.create_time'] = 'DESC';
        }
        $this->db->order($order);
        return $this;
    }

    public function deleteUser($userId)
    {
        if(empty($userId)){
            return false;
        }
        $userInfo = $this->getUserById($userId);

        $this->startTrans();
        $upData['username'] = "";
        $upData['nickname'] = "";
        $upData['remark_name'] = "";
        $upData['avatar'] = "";
        $upData['birthday'] = "";
        $upData['province_id'] = 0;
        $upData['city_id'] = 0;
        $upData['district_id'] = 0;
        $upData['phone'] = 0;
        $upData['first_agent_id'] = 0;
        $upData['status'] = 0;
        $upData['exp'] = 0;
        $upData['points'] = 0;
        $upData['level'] = 0;
        $upData['live_status'] = 0;
        $upData['film_status'] = 0;
        $upData['last_upgrade_time'] = NULL;
        $upData['last_renick_time'] = NULL;
        $upData['verified'] = 0;
        $upData['is_creation'] = 0;
        $upData['password'] = "";
        $upData['salt'] = "";
        $upData['first_promoter_uid'] = 0;
        $upData['cover'] = "";
        $upData['sign'] = "这个家伙太懒了，什么也没留下。";
        $upData['reg_way'] = 0;
        $upData['login_time'] = NULL;
        $upData['login_ip'] = 0;
        $upData['vip_expire'] = 0;
        $upData['millet'] = 0;
        $upData['total_millet'] = 0;
        $upData['fre_millet'] = 0;
        $upData['millet_status'] = 0;
        $upData['his_millet'] = 0;
        $upData['isvirtual_millet'] = 0;
        $upData['his_isvirtual_millet'] = 0;
        $upData['millet_cash_total'] = 0;
        $upData['isset_pwd'] = 0;
        $upData['like_num'] = 0;
        $upData['fans_num'] = 0;
        $upData['follow_num'] = 0;
        $upData['collection_num'] = 0;
        $upData['download_num'] = 0;
        $upData['be_black_num'] = 0;
        $upData['change_pwd_time'] = NULL;
        $upData['is_promoter'] = 0;
        $upData['is_anchor'] = 0;
        $upData['credit_score'] = 0;
        $upData['remark'] = "";
        $upData['reg_meid'] = "";
        $upData['delete_time'] = time();
        $upData['update_time'] = NULL;
        $upData['create_time'] = NULL;
        $upData['processed'] = 0;
        $upData['like_num2'] = 0;
        $upData['pv'] = 0;
        $upData['share_num'] = 0;
        $upData['comment_status'] = 0;
        $upData['contact_status'] = 0;
        $upData['taoke_money'] = 0;
        $upData['taoke_money_status'] = 0;
        $upData['taoke_level'] = 0;
        $upData['relation_id'] = "";
        $upData['special_id'] = "";
        $upData['pdd_pid'] = "";
        $upData['jd_pid'] = "";
        $upData['taoke_shop'] = 0;
        $upData['shop_id'] = 0;
        $upData['instance_id'] = 0;
        $upData['bond'] = 0;
        $upData['member_level'] = 0;
        $upData['balance'] = 0;
        $upData['score'] = 0;
        $upData['invite_code'] = "";
        $status = Db::name("user")->where(["user_id"=>$userId])->update($upData);
        if(!$status){
            $this->rollback();
            return false;
        }

        if($userInfo['is_anchor'] == 1){
            $res = Db::name("anchor")->where(["user_id"=>$userId])->delete();
            if(!$res) {
                $this->rollback();
                return false;
            }

            Db::name("anchor_apply")->where(["user_id"=>$userId])->delete();

            $agnum = Db::name("anchor_goods")->where(["user_id"=>$userId])->count();
            if ($agnum > 0) {
                $res = Db::name("anchor_goods")->where(["user_id"=>$userId])->delete();
                if(!$res) {
                    $this->rollback();
                    return false;
                }
            }

            $agcnum = Db::name("anchor_goods_cate")->where(["user_id"=>$userId])->count();
            if ($agcnum > 0) {
                $res = Db::name("anchor_goods_cate")->where(["user_id"=>$userId])->delete();
                if(!$res) {
                    $this->rollback();
                    return false;
                }
            }

            $gbcnum = Db::name("good_bag")->where(["user_id"=>$userId])->count();
            if ($gbcnum > 0) {
                $res = Db::name("good_bag")->where(["user_id"=>$userId])->delete();
                if(!$res) {
                    $this->rollback();
                    return false;
                }
            }
        }

        if($userInfo['taoke_shop'] == 1){
            $asStatus = Db::name("anchor_shop")->where(["user_id"=>$userId])->delete();
            if(!$asStatus) {
                $this->rollback();
                return false;
            }
            $dlcnum = Db::name("dredge_log")->where(["user_id"=>$userId])->count();
            if ($dlcnum > 0) {
                $res = Db::name("dredge_log")->where(["user_id"=>$userId])->delete();
                if(!$res) {
                    $this->rollback();
                    return false;
                }
            }
        }

        $veStatus = Db::name("user_verified")->where(["user_id"=>$userId])->delete();

        if($userInfo['instance_id'] > 0){
            $res = Db::name("ns_shop")->where(["shop_id"=>$userInfo['instance_id']])->delete();
            if(!$res) {
                $this->rollback();
                return false;
            }

            $res = Db::name("ns_shop_apply")->where(["shop_id"=>$userInfo['instance_id']])->delete();
            if(!$res) {
                $this->rollback();
                return false;
            }

            $asStatus = Db::name("anchor_shop")->where(["user_id"=>$userId])->delete();
            if(!$asStatus) {
                $this->rollback();
                return false;
            }
        }

        $glnum = Db::name("gift_log")->where(["user_id"=>$userId])->count();
        if ($glnum > 0) {
            $res = Db::name("gift_log")->where(["user_id"=>$userId])->delete();
            if(!$res) {
                $this->rollback();
                return false;
            }
        }

        $kcnum = Db::name("kpi_cons")->where(["promoter_uid"=>$userId])->count();
        if ($kcnum > 0) {
            $res = Db::name("kpi_cons")->where(["promoter_uid"=>$userId])->delete();
            if(!$res) {
                $this->rollback();
                return false;
            }
        }
        $kfnum = Db::name("kpi_fans")->where(["promoter_uid"=>$userId])->count();
        if ($kfnum > 0) {
            $res = Db::name("kpi_fans")->where(["promoter_uid"=>$userId])->delete();
            if(!$res) {
                $this->rollback();
                return false;
            }
        }
        $kmnum = Db::name("kpi_millet")->where(["get_uid"=>$userId])->count();
        if ($kmnum > 0) {
            $res = Db::name("kpi_millet")->where(["get_uid"=>$userId])->delete();
            if(!$res) {
                $this->rollback();
                return false;
            }
        }
        $ktlnum = Db::name("kpi_transfer_log")->where(["promoter_uid"=>$userId])->count();
        if ($ktlnum > 0) {
            $res = Db::name("kpi_transfer_log")->where(["promoter_uid"=>$userId])->delete();
            if(!$res) {
                $this->rollback();
                return false;
            }
        }

        $lmlnum = Db::name("link_mic_log")->where(["user_id"=>$userId])->count();
        if ($lmlnum > 0) {
            $res = Db::name("link_mic_log")->where(["user_id"=>$userId])->delete();
            if(!$res) {
                $this->rollback();
                return false;
            }
        }
        $lhnum = Db::name("live_history")->where(["user_id"=>$userId])->count();
        if ($lhnum > 0) {
            $res = Db::name("live_history")->where(["user_id"=>$userId])->delete();
            if(!$res) {
                $this->rollback();
                return false;
            }
        }
        $lponum = Db::name("live_pay_order")->where("user_id = ".$userId." or anchor_id =".$userId)->count();
        if ($lponum > 0) {
            $res = Db::name("live_pay_order")->where("user_id = ".$userId." or anchor_id =".$userId)->delete();
            if(!$res) {
                $this->rollback();
                return false;
            }
        }

        $lpnum = Db::name("live_pk")->where("active_id = ".$userId." or target_id =".$userId)->count();
        if ($lpnum > 0) {
            $res = Db::name("live_pk")->where("active_id = ".$userId." or target_id =".$userId)->delete();
            if(!$res) {
                $this->rollback();
                return false;
            }
        }
        $ltnum = Db::name("live_task")->where(["user_id" => $userId])->count();
        if ($ltnum > 0) {
            $res = Db::name("live_task")->where(["user_id" => $userId])->delete();
            if(!$res) {
                $this->rollback();
                return false;
            }
        }
        $llnum = Db::name("lottery_lucky")->where(["user_id" => $userId])->count();
        if ($llnum > 0) {
            $res = Db::name("lottery_lucky")->where(["user_id" => $userId])->delete();
            if(!$res) {
                $this->rollback();
                return false;
            }
        }
        $lrlnum = Db::name("lottery_record_log")->where(["user_id" => $userId])->count();
        if ($lrlnum > 0) {
            $res = Db::name("lottery_record_log")->where(["user_id" => $userId])->delete();
            if(!$res) {
                $this->rollback();
                return false;
            }
        }
        $mcnum = Db::name("millet_cash")->where(["user_id" => $userId])->count();
        if ($mcnum > 0) {
            $res = Db::name("millet_cash")->where(["user_id" => $userId])->delete();
            if(!$res) {
                $this->rollback();
                return false;
            }
        }
        $mlnum = Db::name("millet_log")->where(["user_id" => $userId])->count();
        if ($mlnum > 0) {
            $res = Db::name("millet_log")->where(["user_id" => $userId])->delete();
            if(!$res) {
                $this->rollback();
                return false;
            }
        }

        $rknum = Db::name("user_rank")->where(["uid" => $userId])->count();
        if ($rknum > 0) {
            $res = Db::name("user_rank")->where(["uid" => $userId])->delete();
            if(!$res) {
                $this->rollback();
                return false;
            }
        }

        $utnum = Db::name("user_third")->where(["user_id" => $userId])->count();
        if ($utnum > 0) {
            $res = Db::name("user_third")->where(["user_id" => $userId])->delete();
            if(!$res) {
                $this->rollback();
                return false;
            }
        }
        $vnum = Db::name("video")->where(["user_id" => $userId])->count();
        if ($vnum > 0) {
            $res = Db::name("video")->where(["user_id" => $userId])->delete();
            if(!$res) {
                $this->rollback();
                return false;
            }
        }

        $vunum = Db::name("video_unpublished")->where(["user_id" => $userId])->count();
        if ($vunum > 0) {
            $res = Db::name("video_unpublished")->where(["user_id" => $userId])->delete();
            if(!$res) {
                $this->rollback();
                return false;
            }
        }

        $pranum = Db::name("promotion_relation_apply")->where(["user_id" => $userId])->count();
        if ($pranum > 0) {
            $res = Db::name("promotion_relation_apply")->where(["user_id" => $userId])->delete();
            if(!$res) {
                $this->rollback();
                return false;
            }
        }

        $peranum = Db::name("promotion_exit_apply")->where(["user_id" => $userId])->count();
        if ($peranum > 0) {
            $res = Db::name("promotion_exit_apply")->where(["user_id" => $userId])->delete();
            if(!$res) {
                $this->rollback();
                return false;
            }
        }

        $prenum = Db::name("promotion_relation")->where(["user_id" => $userId])->count();
        if ($prenum > 0) {
            $res = Db::name("promotion_relation")->where(["user_id" => $userId])->delete();
            if(!$res) {
                $this->rollback();
                return false;
            }
        }
        $res = Db::name("bean_log")->where(["user_id" => $userId])->delete();
        $resAgent = Db::name("agent")->where(["uid" => $userId])->delete();
        $circle = Db::name("friend_circle_circle")->where(["uid" => $userId])->delete();
        $circleTopic = Db::name("friend_circle_topic")->where(["uid" => $userId])->delete();
        $circleTimeline = Db::name("friend_circle_timeline")->where(["uid" => $userId])->delete();
        $friendCircleMessage = Db::name("friend_circle_message")->where(["uid" => $userId])->delete();
        $this->commit();
        $redis = RedisClient::getInstance();
        $redis->del('user:' . $userId);
        return true;
    }
}
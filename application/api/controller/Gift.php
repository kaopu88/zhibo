<?php


namespace app\api\controller;


use app\common\controller\UserController;
use app\api\service\Gift as GiftMod;
use bxkj_common\RedisClient;
use bxkj_module\service\User;
use phpDocumentor\Reflection\Types\Self_;
use think\Db;
use think\facade\Request;

class Gift extends UserController
{

    protected static $guardRules = '1、守护礼物有折损
    2、守护席无人时,赠送三个守护礼物中任意一个均可抢占;
    3、守护席有人时,赠送当前展示的守护礼物抢占守护席,当前守护为初级,需赠送初级及初级以上的守护礼物抢占,当前守护为中级,需赠送中级和高级守护礼物抢占,当前守护为高级,需赠送高级守护礼物抢占';

    //获取礼物
    public function getLiveGift()
    {
        $res = (new GiftMod())->liveGift();

        return $this->success($res);
    }

    /**
     * 获取守护礼物
     * @return array|string
     */
    public function guardGiftList()
    {
        $res = (new GiftMod())->getGiftGuard();

        $data=array('guardGift'=>$res,'guardRules'=>Self::$guardRules);

        return $this->success($data);
    }

    //礼物在线升级
    public function onlineUpgrade()
    {
        $version = request()->param('version');

        $giftDomain = new GiftMod();

        $res = call_user_func_array([$giftDomain, APP_OS_NAME.'OnlineUpgrade'], [$version]);

        return $this->success(empty($res) ? (object)[] : $res);
    }

    //礼物在线升级
    public function getGiftResources()
    {
        $res = (new GiftMod())->resourcesGift();
        return $this->success($res);
    }

    //收取的礼物
    public function getMyGift()
    {
        $params = request()->param();
        $userId = $params['user_id'] ? $params['user_id'] : USERID;
        $giftMod = new GiftMod();
        $liveGiftRes = $giftMod->liveGift();
        $videoGiftRes = $giftMod->videoGift();
        $redis = new RedisClient();
        $total = 0;
        if (!empty($liveGiftRes)) {
            foreach ($liveGiftRes as $key => &$value) {
                $value['num'] = $redis->zScore('live_gift_'  .$value['id'] . ':'. $userId, 'gift_live_num') ?: 0;
                if ($value['num'] > 0) {
                    $total = $total + $value['num'] * $value['price'];
                }
            }
        }

        if (!empty($videoGiftRes)) {
            foreach ($videoGiftRes as $k => &$v) {
                $v['num'] = $redis->zScore('video_gift_'  . $v['id'] . ':'. $userId, 'gift_video_num') ?: 0;
                if ($v['num'] > 0) {
                    $total = $total + $v['num'] * $v['price'];
                }
            }
        }
        return $this->success(['live' => $liveGiftRes ? $liveGiftRes : [], 'video' => $videoGiftRes ? $videoGiftRes : [], 'total' => $total]);
    }

    public function test()
    {
        die;
        /*$User = new \app\common\service\User();
        $anchor_user = $User->getUser(10004815);
        var_dump($anchor_user);die;*/
        //echo H5_URL .'/Live/wishSlider?active_type=1&room_id=1&time_stamp='. time() .'&user_id=10005003';die;
        /*$RUNTIME_ENVIROMENT = strtolower(RUNTIME_ENVIROMENT);
        var_dump($RUNTIME_ENVIROMENT);die;
        $sandbox = ($RUNTIME_ENVIROMENT != 'pro') ? true : null;
        $applePay = new \bxkj_payment\ApplePay($sandbox);
        $verifyReceiptApi = $applePay->getVerifyReceiptApi();*/

        // $where['status'] = 'bind';
        //$where['type'] = 'weixin';
        //$bind = Db::name('user_third')->where($where)->where($where1)->find();
        // echo Db::name('user_third')->getLastSql();die;

        $redis = new RedisClient();
        $dayNum = "anchor_millet:d:20200506";
        $rank = $redis->zRevRank($dayNum, 10004440);
        var_dump($rank);die;
        $follow = $redis->zRange('follow:10003883',0, -1);
        var_dump($follow);die;
        /*$redis->set("BG_LIVE:30:message_level", 0, 86400);
        var_dump($redis->exists("BG_LIVE:30:message_level"));die;*/

        //$redis->set('BG_VOICE:anchor_user:30', 10003887);
        /*$postion= $redis->zRange('BG_VOICE:voice_number:10000',0, -1, true);
        for ($i = 1; $i <= 4; $i++) {
            $user = array_keys($postion, $i);
            echo $user[0];
        }*/
        //var_dump((int)$redis->sIsMember('BG_VOICE:voice_speak:10000', 10003884));
        //$redis->zAdd('BG_VOICE:voice_number:30', 4, 10003883);
        $redis->zUnion('BG_VOICE:voice_number:total', ['BG_VOICE:voice_number:30', 'BG_VOICE:voice_number:10000'], null, 'SUM');
        //var_dump($redis->ZRANGE('BG_VOICE:voice_number:10000', 0,-1, true));die;
        // $redis->zAdd('BG_VOICE:voice_postion_type:30', '1' , 1);
        // var_dump($redis->zscore('BG_VOICE:voice_number:10000', 10004451));die;
        //var_dump($redis->keys('BG_VOICE:*'));die;
        //$redis->zRem('BG_VOICE:voice_number:10000' ,'10004451');
        $res = $redis->setnx('sss', 1);
        if (!$res) {
            echo 1111;
            return;
        }
        die;
        //var_dump($redis->exists('sss'));
        /*$roomId = 10001;
        $anchorId = 10003883;
        $total = 10;
        $total_key = 'rank:room:' . $roomId; //当前房间收到的总额
        $anchor_key = 'rank:anchor:'. $roomId;
        $user_rank_key = 'rank:user:anchor:rank:'. $roomId;
        $redis->zIncrBy($total_key, $total, $roomId);
        $redis->zIncrBy($anchor_key, $total, $anchorId);
        $redis->zIncrBy($user_rank_key, $total, 10004451);
        echo $redis->zscore($anchor_key, 10004451);*/
        // $redis->zAdd('BG_VOICE:voice_number:10000', 1, 10003883);
        //$redis->zAdd('BG_VOICE:voice_number:10000', 2, 10004451);
        //echo $redis->zscore('one', 10004895);
        //$redis->zIncrBy('BG_VOICE:voice_number:10000' , 1, '10004451');
        var_dump($redis->zScore('BG_VOICE:voice_number:10000' , '10003883'));
        //$rankList = $redis->ZCOUNT('BG_VOICE:voice_number:10000', 3, 3);
        //var_dump($rankList);die;
        //  foreach ($rankList as $user_id => $user_score) {
        //echo $user_score;
        //  }
        //$redis->del('one');

        /*$goodsList = Db::name("live_pre_goods")->where(['user_id' => USERID])->order('live_status DESC,top_time DESC,add_time desc')->select();
        $good_detail = [];
        $dataAll = [];
        if (!empty($goodsList)) {
            foreach ($goodsList as $value) {
                $goodstype = $value['type'] ? 'shop' : 'taoke';
                $goodsKey = 'live_goods_pre:goods:goodsid:' . $goodstype . USERID;
                $key = 'goods_detail:taoke:' . $value['goods_id'];

                $dataAll[] = array(
                    'user_id' => USERID,
                    'goods_id' => $value['goods_id'],
                    'anchor_id' => $value['anchor_id'],
                    'goods_type' => $value['goods_type'],
                    'live_status' => 0,
                    'add_time' => $value['add_time'],
                    'top_time' => $value['top_time'],
                    'status' => 1,
                    'content' =>  $value['content'] ?: '',
                    'room_id' => 11
                );

                if ($value['goods_type'] == 0) {
                    //第三方商品
                    $goods = new \app\api\service\Goods();
                    $goodsdetail = $goods->getTaokeGoods(['goods_id' => $value['goods_id']]);
                    if (empty($goodsdetail)) continue;
                    $detail = [
                        'discount_price' => $goodsdetail['discount_price'], 'coupon_price' => $goodsdetail['coupon_price'], 'img' => ($goodsdetail['img'] ? $goodsdetail['img'] : ''),
                        'shop_type' => $goodsdetail['shop_type'], 'title' => $goodsdetail['title']
                    ];
                } else {
                    //自营商品
                }

                $good_detail[] = ['detail' => $detail, 'goods_type' => $value['goods_type'], 'live_status' => $value['live_status'], 'goods_id' => $value['goods_id'], 'id' => $value['id'], 'top_time' => $value['top_time'],
                    'add_time' => $value['add_time'], 'content' => ($value['content'] ? $value['content'] : '')];

                $redis->sRem($goodsKey, $value['goods_id']);
                $redis->del($key);
            }
        }

        if (!empty($dataAll)) {
            Db::startTrans();
            Db::name('live_goods')->insertAll($dataAll);
            Db::name('live_pre_goods')->where(['user_id' => USERID])->delete();
            Db::commit();
        }*/
        //var_dump($good_detail);

        // $fenxiao = new \bxkj_module\service\Fenxiao();
        //var_dump($fenxiao->getDistributeConfig());
        //var_dump($fenxiao->getDistributeRate(10003883,'one_rate'));
        //var_dump($fenxiao->distributionCommission(10003883, 10, 78));
        //var_dump($fenxiao->getError());
        //$fenxiao->distribute(10003887, ['reward_num' => 1]);
        //var_dump($fenxiao->distributeUpgrade(10003887));
    }
}
<?php
namespace app\api\controller;
use app\common\controller\UserController;
use app\api\service\RedPacket as RedPacketModel;
use app\api\service\RedDetail as RedDetailModel;
use bxkj_common\RedisClient;
use bxkj_common\ClientInfo;
use bxkj_common\CoreSdk;
use app\admin\service\SysConfig;
use bxkj_module\exception\ApiException;
use bxkj_common\RabbitMqChannel;
use think\Db;

class Redpacket extends UserController
{
    protected static $buyRedpacket = 'red_packet';

    protected static $redPrefix = 'BX_LIVERED:';

    protected static $joinRedPrefix = 'joinPacket:';

    public function __construct()
    {
        parent::__construct();
    }


    //领取红包
    public function openRedPacket()
    {
        $redis = RedisClient::getInstance();
        $params = request()->param();
        $room_id = (int)$params['room_id'];
        $red_id = (int)$params['red_id'];
        if (!$room_id || !$red_id) return $this->jsonError("非法操作");
        if(!$redis->sAdd(self::$joinRedPrefix.$room_id,USERID)){
            return $this->jsonError("您已经领过了");
        }
        $money = $redis->lPop(self::$redPrefix . $room_id . ':' . $red_id);
        if ($money) {
            $rabbitChannel = new RabbitMqChannel(['user.behavior']);
            $rabbitChannel->exchange('main')->sendOnce('user.behavior.red_detail', [
                'behavior' => 'red_detail',
                'data' => [
                    'user_id' => USERID,
                    'red_id' => $red_id,
                    'money' => $money,
                    'room_id'=>$room_id
                ]
            ]);
            return $this->success($money,'恭喜你抢到了红包');
        }
        $redis->del(self::$joinRedPrefix.$room_id);
        return $this->success(0, '红包已抢完');
    }

    //直播间红包
    public function liveRedPacket()
     {
        $params = request()->param();
        $page = isset($params['page']) ? $params['page'] : 1;
        $pageSize = isset($params['pageSize']) ? $params['pageSize'] : 10;
        $RedPacket= new RedPacketModel();
        $offset = ($page-1)*$pageSize;
        $count = $RedPacket->getTotal($params);
        $redList = $RedPacket->getList($params, $offset, $pageSize);
        foreach ($redList as &$val) {
            $user = userMsg($val['user_id'],'nickname,avatar');
            $val['nickname'] = $user['nickname'] ? $user['nickname'] : '';
            $val['avatar'] = img_url($user['avatar'], '', 'avatar');
            $val['left_time'] = ($val['end_time']-time()) >0 ? ($val['end_time']-time()) : 0;
         }
        return $this->success(array('redList'=>$redList,'count'=>$count), '获取成功');
    }

    //红包领取详情
    public function receiveRedPacketList()
    {
        $params = request()->param();
        $offset = isset($params['offset']) ? $params['offset'] : 0;
        $length = isset($params['length']) ? $params['length'] : 100;
        $params['room_id'] = isset($params['room_id']) ? $params['room_id'] : 0;
        $params['red_id'] = isset($params['red_id']) ? $params['red_id'] : 0;
        $RedDetail= new RedDetailModel();
        $RedPacket= new RedPacketModel();
        $redRes = $RedPacket->getRedPacket($params['red_id']);
        $reciveCount = $RedDetail->getTotal($params);
        $redList = $RedDetail->getList($params, $offset, $length);
        foreach ($redList as &$val) {
            $val['create_time'] = date('H:i', $val['create_time']);
        }
        return $this->success(array('redList'=>$redList,'redCount'=>(int)$redRes['num'],'reciveCount'=>(int)$reciveCount), '获取成功');
    }

    //获取红包详情
    public function redPacketDetail(){
        $params = request()->param();
        $id = intval($params['id']);
        $redDetail = Db::name('activity_red_packet')->where(['id' => $id])->find();
        return $this->success($redDetail, '获取成功');
    }

    //随机红包方法
    private function randBonus($bonus_total = 0, $bonus_count = 0)
    {
        $bonus_count = $left_count = $bonus_count;
        $bonus_items = array(); // 将要瓜分的结果
        $min = 1;
        $bonus_balance = $bonus_total; // 每次分完之后的余额
        $i = 0;
        while ($i < $bonus_count) {
            if ($i < $bonus_count - 1) {
                $max = floor($bonus_balance / $left_count) * 2 - 1;
                $rand = rand($min, $max);
                $bonus_items[] = $rand;
                $bonus_balance -= $rand;
                $left_count--;
            } else {
                $bonus_items[] = $bonus_balance; //最后一个红包直接承包最后所有的金额，保证发出的总金额正确
            }
            $i++;
        }
        return $bonus_items;
    }

    function getDivideNumber($number, $total, $index = 2) {
        // 除法取平均数
        $divide_number  = bcdiv($number, $total, $index);
        // 减法获取最后一个数
        $last_number = bcsub($number, $divide_number*($total-1), $index);
        // 拼装平分后的数据返回
        $number_str = str_repeat($divide_number.'+', $total-1).$last_number;
        return explode('+', $number_str);
    }
}
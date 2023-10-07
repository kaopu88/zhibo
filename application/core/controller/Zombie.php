<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/29
 * Time: 17:45
 */

namespace app\core\controller;

use bxkj_common\RedisClient;
use think\Request;
use app\core\service\Zombie as ZombieService;


/**
 * 僵尸粉业务
 * Class Zombie
 * @package App\Domain
 */
class Zombie extends Controller
{
    //分配
    public function zombieProcess(Request $request)
    {
        $data = $request->post();

        $roomId = $data['room_id'];

        $number = $data['count'];
        
        $res = ZombieService::handleZombie($roomId, $number);

        if ($res !== true) return json_error('没有更多的僵尸粉', 1);

        return json_success([], '分配成功');
    }


    //获得直播间所有的用户包括机器人数量
    public function getRoomAudience(Request $request)
    {
        $roomId = $request->post('room_id');

        if (empty($roomId)) return '';

        if (!is_array($roomId)) $roomId = explode(',', $roomId);

        $res = ZombieService::getAudienceNum($roomId);

        return json_success($res);
    }



    //获取在线观众列表，包括机器人
    public function getAudienceList(Request $request)
    {
        $roomId = $request->post('room_id');
        $rs = ZombieService::getAudienceList($roomId);
        return json_success($rs);
    }


    /**
     * 手动分配机器人
     * @param Request $request
     * @return \think\response\Json
     */
    public function addRobot(Request $request)
    {
        $params = $request->post();

        if (empty($params['room_id'])) return json_error('房间id不能为空');

        if (empty($params['count'])) return json_error('数据不能低于1');

        $room_id = $params['room_id'];

        $number = $params['count'];

        $res = $number > 0 ? ZombieService::addRobot($room_id, $number) : ZombieService::removeRobot($room_id, abs($number));

        if (is_error($res)) return json_error($res);

        return json_success([], '添加成功');
    }


    //获得直播间真实人数(后增api端未使用)
    public function getRoomAudiences(Request $request)
    {
        $roomId = $request->post('room_id');

        if (empty($roomId)) return '';

        if (!is_array($roomId)) $roomId = explode(',', $roomId);

        $res = ZombieService::getAudiences($roomId);

        return json_success($res);
    }



    //获得直播间机器人数(后增api端未使用)
    public function getRoomRobots(Request $request)
    {
        $roomId = $request->post('room_id');

        if (empty($roomId)) return '';

        if (!is_array($roomId)) $roomId = explode(',', $roomId);

        $res = ZombieService::getRobots($roomId);

        return json_success($res);
    }

    public function get_pk_rank(Request $request)
    {
        $params = $request->post();
        $roomId = $params['room_id'];
        $pkId = $params['pk_id'];
        $offset = isset($params['offset']) ? $params['offset'] : 0;
        $length = isset($params['length']) ? ($params['length'] > 30 ? 30 : $params['length']) : PAGE_LIMIT;
        if (empty($pkId) || empty($roomId)) return json_error('数据错误');
        $redis = RedisClient::getInstance();
        $user = $redis->zRevRange('BG_LIVE:pk_rank_' . $pkId. '_' . $roomId, $offset, $length - 1, true);
        $user_detail = [];
        if (!empty($user)) {
            $userModel = new \app\core\service\User();
            foreach ($user as $user_id => $user_score) {
                $user_info = $userModel->getUser($user_id);
                if (empty($user_info)) continue;
                $user_detail[] = [
                    'coin' => $user_score,
                    'user_id' => $user_id,
                    'avatar' => $user_info['avatar'],
                    'nickname' => $user_info['nickname']
                ];
            }
        }
        return json_success($user_detail);
    }

}

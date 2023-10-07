<?php

namespace app\api\controller;

use app\common\controller\Controller;
use app\common\service\DsSession;
use app\common\service\Video;
use bxkj_common\Prophet;
use bxkj_common\VideoRecomend;
use think\Db;
use app\common\service\Vip AS VipModel;

class Home extends Controller
{

    /**
     * 精选主页
     * @desc 获取推荐主页数据
     */
    public function appHome()
    {
        $config = config('app.vod.audit_config');

        $offset = input('offset', 0);
        $length = input('length', PAGE_LIMIT);
        $length = $length > 100 ? 100 : $length;
        $prophet = new Prophet(USERID, APP_MEID);
        if ($config['vido_show'] == '1') {
            $videoRemcommend = new VideoRecomend();
            $videos = $videoRemcommend->getList($offset, $length);
        } else {
            $videos = $prophet->getList($offset, $length);
        }
        if ($config['isadvideo_status'] == 2) $adVideos = $prophet->getAdList();
        if ($adVideos) $videos = add_rand_array($videos, $adVideos);;

        $videoService = new Video();
        $rootDebug = DsSession::get('root_order.debug');
        if ($rootDebug == 1) {
            foreach ($videos as &$video) {
                $video['describe'] = "[{$video['id']}] {$video['city_name']} tag:{$video['tag_names']} user_id:{$video['user_id']} mus:{$video['music_id']} sco:{$video['score']} loc:{$video['location_id']} wat:{$video['watch_sum']} pla:{$video['played_out_sum']} swi:{$video['switch_sum']}";
            }
        }
        $videos = $videoService->initializeFilm($videos, Video::$allow_fields['common']);

        return $this->success($videos, '获取成功');
    }
    public function checkPay()
    {
        $videoId = input('id/d',0);
        if(empty($videoId))return $this->jsonError('查询ID不能为空');
        $uid = USERID;
        $date = date('Y-m-d');
        $where = [
            'video_id'=>$videoId,
            'user_id' =>$uid,
            'days'    =>$date,
            'pay_status'=>1
        ];
        
        $data = Db::name('video_pay_log')->where($where)->find();
        if($data)return $this->success($data, '获取成功');
        return $this->jsonError('该收费视频未支付费用!,请支付后观看');
    }
    public function buyVideo()
    {   
        $videoId = input('id/d',0);
        if(empty($videoId))return $this->jsonError('查询ID不能为空');
        $uid = USERID;
        $date = date('Y-m-d');
        $where = [
            'video_id'=>$videoId,
            'user_id' =>$uid,
            'days'    =>$date,
            'pay_status'=>1
        ];
        
        $data = Db::name('video_pay_log')->where($where)->find();
        if($data)return $this->jsonError('该视频已付费');
        $params = request()->param();
        $vip = new VipModel();
        $params['user_id'] = USERID;
        $params['client_ip'] = get_client_ip();
        $params['app_v'] = APP_V;
        $result = $vip->buyVideo($params);
        if (!$result) return $this->jsonError($vip->getError());
        return $this->success($result, '购买成功');
    }
    
}
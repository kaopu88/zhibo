<?php


namespace app\api\controller;


use app\api\service\UserPhotoWall;
use app\common\controller\UserController;
use app\common\service\DsSession;
use app\api\service\video\Like;
use bxkj_common\RabbitMqChannel;
use bxkj_module\service\User;
use bxkj_common\RedisClient;
use think\Db;

class Personal extends UserController
{
    //获取用户信息
    public function getHomeInfo()
    {
        $user_id = request()->param('user_id');
        if (empty($user_id)) return $this->jsonError('请选择用户');

        $userService = new User();
        $user = $userService->getUser($user_id);
        if (empty($user)) return $this->jsonError('用户不存在或已禁用');
        $like_num2 = Db::name('user')->where(['user_id' => $user_id, 'status' => '1'])->value('like_num2');

        $tmp = copy_array($user, 'user_id,nickname,avatar,gender,level,exp,birthday,age,province_id,province_name,
        city_id,city_name,district_id,district_name,sign,fans_num,follow_num,like_num,verified,is_creation,vip_status,vip_expire,vip_expire_str,cover,is_official, is_anchor, shop_id, invite_code,taoke_shop,weight,height,voice_sign,teenager_model_open,phone,voice_time,goodnum');

        $followModel = new \app\api\service\Follow();
        $followInfo = $followModel->getFollowInfo(USERID, $tmp['user_id']);
        $tmp = array_merge($tmp, $followInfo);
        $tmp['follow_num'] = $followModel->followCount($user_id);
        $tmp['fans_num'] = $followModel->fansCount($user_id);

        $tmp['is_live'] = '0';
        $tmp['room_id'] = 0;
        $tmp['room_model'] = 0;
        $tmp['jump'] = getJump('personal', ['user_id' => $tmp['user_id']]);
        $USERID = USERID;
        $redis = new RedisClient();
        $isBlack = $USERID ? $redis->zScore("blacklist:{$USERID}", $user_id) : false;
        $tmp['is_black'] = $isBlack ? '1' : '0';
        $liveInfo = Db::name('live')->where(['user_id' => $user_id, 'status' => '1'])->find();
        if ($liveInfo) {
            $tmp['is_live'] = '1';
            $tmp['room_id'] = $liveInfo['id'];
            $tmp['room_model'] = $liveInfo['room_model'];
            $tmp['jump'] = getJump('enter_room', ['room_id' => $liveInfo['id'], 'from' => 'personal']);
        }
        $playSumKey = "stat:play_sum_total:{$user_id}";
        $shareSumKey = "stat:share_sum_total:{$user_id}";
        $play_sum = DsSession::get($playSumKey);
        $share_sum = DsSession::get($shareSumKey);
        if (!isset($play_sum) || $play_sum === false) {
            $play_sum = Db::name('video')->where(['user_id' => $user_id])->sum('play_sum');
            DsSession::set($playSumKey, $play_sum, 1200);
        }
        if (!isset($share_sum) || $share_sum === false) {
            $share_sum = Db::name('video')->where(['user_id' => $user_id])->sum('share_sum');
            DsSession::set($shareSumKey, $share_sum, 1200);
        }
        $zan_sum2_num = Db::name('video')->where(['user_id' => $user_id])->sum('zan_sum2');
        $tmp['play_sum'] = (int)$play_sum;
        $tmp['share_sum'] = (int)$share_sum;
        $tmp['play_sum_str'] = number_format2($tmp['play_sum']);
        $tmp['share_sum_str'] = number_format2($tmp['share_sum']);
        $where3['trade_type'] = 'pay_per_view';
        $where3['user_id'] = $user_id;
        $where3['type'] = 'type';
        $totalMillet = 0;
        $tmp['total_millet'] = (int)($totalMillet ? $totalMillet : 0);
        $tmp['total_millet_str'] = number_format2($tmp['total_millet']);
        $tmp['follow_num_str'] = number_format2($tmp['follow_num']);
        $tmp['fans_num_str'] = number_format2($tmp['fans_num']);
        $tmp['like_num_str'] = number_format2($tmp['like_num'] + $like_num2 +$zan_sum2_num);
        //$tmp['phone'] = '';
        $tmp['creation_name'] = $tmp['is_creation'] == 1 ? '优质内容创作者' : '未认证';
        $like_num = Db::name('video_like')->where(['user_id' => $user_id])->count();
        $video_num = Db::name('video')->where(['user_id' => $user_id])->count();
        $tmp['video_num'] = (int)$video_num ?: 0;
        $tmp['like_num'] = (int)$like_num ?: 0;

        if ($user_id == $USERID) {
            $find = Db::name('user_data_deal')->where(['user_id' => USERID, 'audit_status' => '-1'])->find();
            if ($find['data']) {
                $find_data = json_decode($find['data'], true);
                if (is_array($find_data)) {
                    $tmp = array_merge($tmp, $find_data);
                }
            } else {
                $find = Db::name('user_data_deal')->where(['user_id' => USERID, 'audit_status' => '0'])->find();
                if ($find['data']) {
                    $find_data = json_decode($find['data'], true);
                    if (is_array($find_data)) {
                        $tmp = array_merge($tmp, $find_data);
                    }
                }
            }
        }
        $UserService = new \app\common\service\User();
        if ($tmp['is_anchor']) {
            $tmp['anchor_level'] = Db::name('anchor')->where('user_id', $tmp['user_id'])->value('anchor_lv');;
            $tmp['anchor_level_progress'] = $UserService->getAnchorLevelProcess($tmp);
        }

        $impression = Db::name('user_my_impression')->where(['user_id' => $user_id])->column('impression_id');
        $impression_data = $UserService->getImpression($impression);
        $tmp['impression'] = $impression_data;
        if ($user_id != USERID) {
            try {
                $rabbitChannel = new RabbitMqChannel(['user.behavior']);
                $rabbitChannel->exchange('main')->sendOnce('user.behavior.view_user', [
                    'user_id' => USERID,
                    'to_uid' => $user_id
                ]);
            } catch (\Exception $e) {

            }
        }
        $photoWall = new UserPhotoWall();
        $photoWallImage = $photoWall->getlist($user_id);
        $tmp['photo_wall_image'] = $photoWallImage['image'] ?: [];

        return $this->success($tmp, '获取成功');
    }

    //获取我的点赞视频
    public function getLikeList()
    {
        $params = request()->param();
        $offset = isset($params['offset']) ? $params['offset'] : 0;
        $length = isset($params['length']) ? ($params['length'] > 100 ? 100 : $params['length']) : PAGE_LIMIT;
        $user_id = isset($params['user_id']) ? $params['user_id'] : USERID;

        $VideoLike = new Like();
        $filmList = $VideoLike->getLikeList($user_id, (int)$offset, (int)$length);

        $filmList = (new \app\api\service\Video())->initializeFilm($filmList, \app\common\service\Video::$allow_fields['common']);

        return $this->success($filmList ? $filmList : [], '获取成功');
    }

    /**
     * 个人作品
     * @desc 获取个人发布的影片(个人中心)
     */
    public function getUserVideo()
    {
        $params = request()->param();
        $offset = isset($params['offset']) ? $params['offset'] : 0;
        $length = isset($params['length']) ? ($params['length'] > 100 ? 100 : $params['length']) : PAGE_LIMIT;

        $user_id = isset($params['user_id']) ? $params['user_id'] : USERID;

        $res = Db::name('video')->where(['user_id' => $user_id])->order('id desc')->limit((int)$offset, (int)$length)->select();

        $userService = new User();
        $user_info = $userService->getUser($user_id);

        foreach ($res as $key => &$val) {
            $val['avatar'] = $user_info['avatar'];

            $val['nickname'] = $user_info['nickname'];
        }

        $res = (new \app\api\service\Video())->initializeFilm($res, \app\common\service\Video::$allow_fields['user_videos']);

        return $this->success($res ? $res : [], '获取成功');
    }
}
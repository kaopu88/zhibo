<?php

namespace app\api\controller;

use app\common\service\DsSession;
use app\api\service\comment\Lists;
use app\api\service\Video;
use bxkj_module\service\User;
use bxkj_common\RedisClient;
use app\api\service\Follow AS FollowModel;
use bxkj_module\service\UserAddressBook;
use think\Db;
use think\facade\Env;
use Hashids\Hashids;
use bxkj_common\QrcodeImg;
use bxkj_common\RabbitMqChannel;
use think\facade\Request;

class Friends extends Follow
{

    //好友
    public function getFriends()
    {
        $redis = RedisClient::getInstance();

        $params = request()->param();

        $offset = $params['offset'];

        $length = $params['length'];

        $len = $redis->llen('RecentContact:'.USERID);

        $contacts = [];

        if (!empty($len))
        {
            if ($len > 8)
            {
                for ($i = $len-8; $i > 0; $i--)
                {
                    $redis->lpop('RecentContact:'.USERID);
                }
            }

            $user_ids = $redis->lrange('RecentContact:'.USERID, 0, -1);

            $userModel = new User();

            $contacts = $userModel->getUsers($user_ids, USERID, 'user_id, nickname, avatar, is_follow, gender, level, verified, is_creation, sign, vip_status');

            foreach ($contacts as &$value)
            {
                $value['is_live'] = (int)$redis->sismember('BG_LIVE:Living', $value['user_id']);
            }
        }

        $followModel = new FollowModel();

        $myFollows = $followModel->followList(USERID, $offset, $length);

        return $this->success(['contact' => $contacts, 'follows' => $myFollows]);
    }


    //搜索好友
    public function search()
    {
        $userModel = new User();

        $all_user = $userModel->followSearch(USERID, input('key_words'));

        $all_user_id = array_column($all_user, 'user_id');

        $contacts = $userModel->getUsers($all_user_id, USERID, 'user_id, nickname, avatar, is_follow, gender, level, verified, is_creation, sign, vip_status');

        $redis = RedisClient::getInstance();

        foreach ($contacts as &$value)
        {
            $value['is_live'] = (int)$redis->sismember('BG_LIVE:Living', $value['user_id']);
        }

        return $this->success($contacts);
    }

    //好友列表(互关)
    public function getFriendsList()
    {
        $params = request()->param();
        $offset = $params['offset'] ? $params['offset'] : 0;
        $length = $params['length'] ? $params['length'] : 10;

        $followModel = new FollowModel();
        $myFriendsList = $followModel->mutualList(USERID, $offset, $length);

        return $this->success($myFriendsList);
    }

    //接收通讯录
    public function addbooks()
    {
        $data = request()->param('data');
        $data = json_decode($data,true);

        if( empty($data) ){
            return $this->jsonError('通讯录为空');
        }

//        $addBookMod = new UserAddressBook();
//        $res = $addBookMod->save(USERID,$data);

        //对接rabbitMQ
        $rabbitChannel = new RabbitMqChannel(['user.user_add_book']);
        $rabbitChannel->exchange('main')->sendOnce('user.user_add_book.process', [
            'user_id' => USERID,
            'data' => $data
        ]);

        return $this->success('','读取成功');
    }

    public function recommend()
    {
        $params = request()->param();
        $offset = $params['offset'] ? $params['offset'] : 0;
        $length = $params['length'] ? $params['length'] : 10;

        $addBookMod = new \app\api\service\UserAddressBook();
        $_list = $addBookMod->getRecommendList(USERID, $offset, $length);

        return $this->success($_list);
    }


    public function unrecommend()
    {
        $id = (int)request()->param('id');
        if( empty($id) ){
            return $this->jsonError('未找到有用的信息');
        }
        Db::name('user_address_book')->where(['user_id'=> USERID, 'id'=>$id])->update(['is_recommend'=>0]);
        return $this->success('','将不会再为你推荐该用户');
    }

    public function bookslist()
    {
        $params = request()->param();
        $offset = $params['offset'] ? $params['offset'] : 0;
        $length = $params['length'] ? $params['length'] : 10;

        $addBookMod = new \app\api\service\UserAddressBook();
        $_list = $addBookMod->getBooksList(USERID, $offset, $length);

        return $this->success($_list);
    }

    public function getfriendcode()
    {
        $friend = request()->param('friend');
        $data = [
            'user_id' => USERID
        ];

        $nickname = str_replace('$', '', DsSession::get('user.nickname'));
        $avatar = DsSession::get('user.avatar');

        if ($friend == USERID) $friend = 0;
        if( !empty($friend) ){
            $friendInfo = Db::name('user')->field('user_id,avatar,nickname,level')->where(array('user_id' => $friend, 'status' => '1', 'delete_time' => null))->find();
            if (!$friendInfo) return $this->jsonError('用户不存在');
            $nickname = str_replace('$', '', $friendInfo['nickname']);
            $avatar = img_url($friendInfo['avatar'], '', 'avatar');
            $data['friend_id'] = $friend;
        }

        $_code = Db::name('user_friend_code')->where($data)->value('code');

        if( empty($_code) ){
            $hashids  =  new Hashids('',12);
            $text = array_values($data);
            $_code = $hashids->encode($text);
            $data['code'] = $_code;
            Db::name('user_friend_code')->insert($data);
        }

        $text = "@{$nickname} 邀你成为".APP_PREFIX_NAME."好友；复制这条口令 $" . $_code . "$ 打开【".config('app.product_setting.prefix_name')."】";

        $result = [
            'code' => $_code,
            'text' => $text,
            'nickname' => $nickname,
            'avatar' => $avatar,
        ];

        return $this->success($result,'获取成功');
    }

    //打开口令
    public function friendcodeto()
    {
        $codestr = request()->param('codestr');
        $b = mb_strpos($codestr,'$') + mb_strlen('$');
        $_code = mb_substr($codestr,$b,12);
        bxkj_console([$codestr, $_code]);
        $_info = Db::name('user_friend_code')->where(['code'=>$_code])->field('id,user_id,friend_id')->find();
        if( empty($_info) ){
            return $this->success(['user_id'=>''],'未找到用户');
        }

        $uid = !empty($_info['friend_id']) ? $_info['friend_id'] : $_info['user_id'];

        if( $uid == USERID){
            return $this->success(['user_id'=>''],'自己的口令');
        }

        $userModel = new User();
        $user = $userModel->getUser($uid,USERID,'user_id, nickname, avatar');

        if( is_null($user) ){
            return $this->success(['user_id'=>''],'未找到用户信息');
        }

        //默认未关注
        $user['is_follow'] = '0';

        if( !empty(USERID) ){
            //当前用户是否关注分享用户
            //未关注点击关注
            $followMod = new \bxkj_module\service\Follow();
            $isfollow = $followMod->isFollow(USERID,$uid);

            if( $isfollow ) $user['is_follow'] = '1';
        }

        return $this->success($user,'成功');
    }

    public function generate()
    {
        $redis = RedisClient::getInstance();
        $userId = DsSession::get('user.user_id');
        $data = [
            'nickname' => DsSession::get('user.nickname'),
            'avatar' => DsSession::get('user.avatar'),
            'user_id' => (string)$userId,
            'level' => DsSession::get('user.level'),
        ];
        $url = getJump('personal', ['user_id' => $userId]);
        $filename = sha1($url);
        $qrcodeImg = new QrcodeImg();
        $suffix = substr((string)$userId, strlen($userId) - 2);
        $suffix = str_pad($suffix, 2, '0', STR_PAD_LEFT);
        $rootPath = Env::get('root_path');
        $path = $rootPath . "public/friend_imgs/{$suffix}/{$filename}.png";
        if (!file_exists(dirname($path))) {
            mkdir(dirname($path), 0777, true);
        }
        if (!file_exists($path)) {
            $res = $qrcodeImg->generate($url, $path, '');
        }
        $imgUrl = str_replace($rootPath . "public", API_URL, $path);
        $data['img_url'] = $imgUrl;
        $data['url'] = $url;

        return $this->success($data, '获取成功');
    }

    public function newPublish()
    {
        $params = request()->param();
        $offset = $params['offset'] ? $params['offset'] : 0;
        $length = $params['length'] ? $params['length'] : 10;

        $followModel = new FollowModel();
        $filmModel = new Video();
        $userModel = new User();

        $rs = $followModel->getFriendNewPublish(USERID, $offset, $length);

        if (empty($rs)) {

            $sql = $filmModel->setWhere()->setOrder('create_time desc')->setLimit($offset, $length)->setSql();

            $rs = Db::query($sql);

        }
        $rs = $filmModel->initializeFilm($rs, Video::$allow_fields['new_publish']);

        $Comment = new Lists();

        foreach ($rs as &$val) {
            $user = $userModel->getUser($val['user_id']);

            $val['comment_list'] = $Comment->commentList($val['id'], 0, 4);

            $val['nickname'] = $user['nickname'] ? $user['nickname'] : '';

            $val['avatar'] = img_url($user['avatar'], '', 'avatar');
        }

        return $this->success($rs, '获取成功');
    }


    /**
     * 好友动态
     *
     * @return \think\response\Json
     */
    public function dynamics()
    {
        $offset = Request::param('offset', 0);

        $length = Request::param('length', 10);
if ($offset == 20) return $this->success(['video' =>[], 'live' => []], '获取成功');
        $FollowModel = new FollowModel();

        $VideoModel = new Video();

        $res = $FollowModel->getFriendNewPublish(USERID, $offset, $length);

        if (empty($res))
        {
            $sql = $VideoModel->setWhere()->setOrder('create_time desc')->setLimit($offset, $length)->setSql();

            $res = Db::query($sql);
        }

        $res = $VideoModel->initializeFilm($res, Video::$allow_fields['new_publish'], USERID, true);

        $list = $this->initDynamics($res);

        return $this->success($list, '获取成功');
    }

}
<?php
/**
 * Created by PhpStorm.
 * User: “崔鹏
 * Date: 2020/06/15
 * Time: 上午 11:23
 */

namespace app\api\controller\friend;

use app\admin\service\SysConfig;
use app\api\service\Follow as FollowModel;
use app\api\service\live\Enter;
use app\common\controller\UserController;
use app\core\model\Live as LiveModel;
use app\core\service\Live;
use app\friend\service\FriendCircleCircle;
use app\friend\service\FriendCircleCircleFollow;
use app\friend\service\FriendCircleComment;
use app\friend\service\FriendCircleLyric;
use app\friend\service\FriendCircleMessage;
use app\friend\service\FriendCircleMessageFilter;
use app\friend\service\FriendCircleMessageLive;
use app\friend\service\FriendCircleMessageReport;
use app\friend\service\FriendCircleTimelin;
use bxkj_common\CoreSdk;
use bxkj_common\HttpClient;
use bxkj_common\RedisClient;
use bxkj_module\exception\ApiException;
use bxkj_module\service\Follow;
use bxkj_module\service\User;
use bxkj_module\service\User_task;
use think\Db;
use think\Loader;
use think\Validate;
use bxkj_common\Prophet;

class Friend extends UserController
{
    protected static $livePrefix = 'BG_LIVE:', $filmPrefix = 'BG_FILM:', $randomPrefix = 'BG_RAND:', $teenFilmPrefix = 'BG_TEEN:';//影片redis前缀
    public function __construct()
    {
        define('EARTH_RADIUS', 6371);//地球半径，平均半径为6371km
        parent::__construct();
        $redis       = new RedisClient();
        $cacheFriend = $redis->exists('cache:friend_config');
        if (empty($cacheFriend)) {
            $arr  = [];
            $ser  = new SysConfig();
            $info = $ser->getConfig("friend");
            if (empty($info)) return [];
            $redis->setex('cache:friend_config', 4 * 3600, $info['value']);
        }
        $friendConfigRes       = $redis->get('cache:friend_config');
        $this->friendConfigRes = json_decode($friendConfigRes, true);
        if ($this->friendConfigRes['is_open'] == 0) {
            $errorMsg = '未开启交友功能';
            if (!empty($errorMsg)) {
                throw new ApiException((string)$errorMsg, 1);
            }
        }
        $circle          = new FriendCircleCircle();
        $circleAll       = $circle->clume([]);
        $this->circleAll = $circleAll;
    }

    /**
     * 发布动态信息
     * @return \think\response\Json
     */
    public function subMsg()
    {
        $userId = USERID;
        $submit = submit_verify('friendsub' . USERID);
        if (!$submit) return $this->jsonError('您提交太频繁了~~~');
        $params   = request()->param();
        $validate = new \app\api\validate\Friend();
        $result   = $validate->scene('subMsg')->check($params);
        if (true !== $result) {
            return $this->jsonError($validate->getError());
        }
        if ($params['extend_type'] == 3 && !empty($params['extend_talk'])) {
            if (empty($params['sing_title']) || empty($params['sing_author'])) {
                return $this->jsonError('接唱的歌词和作者不能为空');
            }
        }
        if ($params['msg_type'] == 4 && empty($params['privateid'])) {
            return $this->jsonError('私密对方不能为空');
        }
        if (!empty($params['privateid'])) {
            $followModel  = new FollowModel();
            $privateArray = explode(',', trim($params['privateid'], ','));
            if (!empty($privateArray)) {
                foreach ($privateArray as $k => $v) {
                    $followInfo = $followModel->getFollowInfo(USERID, $v);
                    if (empty($followInfo['is_follow'])) {
                        return $this->jsonError('您还未关注该用户');
                    }
                }
            }
        }
        $friend = new  FriendCircleMessage();
        if ($this->friendConfigRes['msg_examine'] == 1) {
            $status = 0;
        } else {
            $status = 1;
        }
        $singTitle  = $params['sing_title'] ? $params['sing_title'] : '';
        $singAuthor = $params['sing_author'] ? $params['sing_author'] : '';
        if (empty(trim($params['content'])) && empty(trim($params['picture'])) && empty(trim($params['video'])) && empty(trim($params['voice']))) {
            return $this->jsonError('您不能发空动态');
        }
        if (empty($params['voice_time'])) {
            $params['voice_time'] = '';
        }

        if (!empty($params['systemplus'])) {
            $systemplus = json_decode($params['systemplus'], true);
            $systemplus['room_model'] = 0;
            if (!empty($systemplus['islive']) && !empty($systemplus['id'])) {
                $room = Db::name('live')->field("room_model")->where(['id' => $systemplus['id']])->find();
                $systemplus['room_model'] = !empty($room) ? $room['room_model'] : 0;
            }
            $params['systemplus'] = json_encode($systemplus);
        }
        $rest = systemSend($userId, $params['content'], $params['picture'], $params['video'], $params['voice']
            , $params['location'], $params['type'], $params['msg_type'], $params['title'], $params['extend_type'],
            $params['privateid'], $params['systemtype'], $params['systemplus'], $params['extend_talk'],
            $params['extend_circle'], $params['render_type'], $params['cover_url'], $params['dynamic_title'], $params['address'], $status, $singTitle, $singAuthor, $params['voice_time']);
        if ($rest['code'] == -1) return $this->jsonError($rest['msg']);
        return $this->success($rest['rest'], '发布成功');
    }

    public function getMsg()
    {
        $redis    = RedisClient::getInstance();
        $params   = request()->param();
        $validate = new \app\api\validate\Friend();
        $result   = $validate->scene('getMsg')->check($params);
        if (true !== $result) {
            return $this->jsonError($validate->getError());
        }
        $params                   = request()->param();
        $page_index               = isset($params['page_index']) ? $params['page_index'] : 1;
        $page_size                = isset($params['page_size']) ? $params['page_size'] : 10;
        $type                     = $mestype = $params['type'] ? $params['type'] : 2;
        $is_recommend             = isset($params['is_recommend']) ? $params['is_recommend'] : 0;
        $extend_type              = isset($params['extend_type']) ? $params['extend_type'] : 1;
        $friendCircelTimelin      = new FriendCircleTimelin();
        $condition['uid']         = USERID;
        $condition['status']      = 1;
        $condition['type']        = $type;
        $condition['extend_type'] = $extend_type;
        if ($extend_type == 1) {
            $condition1 = "status = 1 and is_own = 1 and type = $type ";
        } else {
            $condition1 = "status = 1 and is_own = 1 and type = $type and extend_type = $extend_type ";
        }
        if ($is_recommend == 1) {
            $condition['is_recommend'] = 1;
            $condition1                = $condition1 . " and  is_recommend =  1";
        }
        // var_dump($condition1);die;
        $list            = $friendCircelTimelin->getQueryOr($condition, $condition1, '*', 'id desc');
        $mylist          = $friendCircelTimelin->column(['uid' => USERID], 'fcmid');
        $followModel     = new FollowModel();
        $myFriendsList   = $followModel->mutualArray(USERID);
        $filter          = new FriendCircleMessageFilter();
        $filterUserArray = $filter->filterUserArray(USERID, 2);
        $circleFollow    = new FriendCircleCircleFollow();
        $circleNumArray  = $circleFollow->getQuery(['uid' => USERID, 'status' => 2], 'circle_id', 'id desc');
        $circleMsg       = new FriendCircleMessage();
        foreach ($circleNumArray as $k => $v) {
            $rest = $circleMsg->getQuery(['extend_circle' => $v['circle_id']], 'id', 'id desc');
            if (!empty($rest)) {
                foreach ($rest as $k => $v) {
                    array_push($filterUserArray, $v['id']);
                }
            }
        }
        if (!empty($list)) {
            foreach ($list as $k => $v) {
                $list[$k]['difftime'] = time_before($v['create_time'], '前');
                //陌生人
                if ($v['msg_type'] == 3) {
                    $myFriendsListnomy = $followModel->mutualArrayNoMY(USERID);
                    if (in_array($v['uid'], $myFriendsListnomy)) {
                        unset($list[$k]);
                    }
                }
                if ($v['is_own'] == 1 && $v['uid'] != USERID && in_array($v['fcmid'], $mylist)) {
                    unset($list[$k]);
                }
                //好友过滤
                if ($v['msg_type'] == 2) {
                    if (!in_array($v['uid'], $myFriendsList)) {
                        unset($list[$k]);
                    }
                }
                //私密过滤
                if ($v['msg_type'] == 4) {
                    $redisGet = $redis->get("bx_friend_msg:" . $v['fcmid']);
                    if (!empty($redisGet)&&$redisGet!="[]") {
                        $msgDetail = json_decode($redisGet, true)[0];
                    } else {
                        $friendMsg = new FriendCircleMessage();
                        $rest1     = $friendMsg->getQuery(['id' => $v['fcmid']], '*', 'id');
                        $redis->set("bx_friend_msg:" . $v['fcmid'], json_encode($rest1));
                        $redisGet  = $redis->get("bx_friend_msg:" . $v['fcmid']);
                        $msgDetail = json_decode($redisGet, true)[0];
                    }
                    if (strpos($msgDetail['privateid'], USERID) == false) {
                        unset($list[$k]);
                    }
                }
                //表过滤
                if (!empty($filterUserArray)) {
                    if (in_array($v['fcmid'], $filterUserArray)) {
                        unset($list[$k]);
                    }
                }
            }
        }
        if ($type == 3) {
            if(!empty($list)){
                $temp = [
                    "id"=> 1,
                    "uid"=> 10000,
                    "fcmid"=> 1,
                    "is_own"=> 1,
                    "create_time"=> 1605081290,
                    "type"=> 3,
                    "msg_type"=> 1,
                    "is_recommend"=> 0,
                    "extend_type"=> 1,
                    "status"=> 1,
                    "difftime"=> "很久很久以前"
                ];
                array_unshift($list, $temp);
            }
        }
        $rest = $this->page_array($page_size, $page_index, $list, 0, $mestype);

        return $this->success($rest, '查询成功');
    }

    /**
     * 崔鹏   2020/06/17
     * 数组分页函数  核心函数  array_slice
     * 用此函数之前要先将数据库里面的所有数据按一定的顺序查询出来存入数组中
     * $count   每页多少条数据
     * $page   当前第几页
     * $msgtype  类型
     * $array   查询出来的所有数组，要进行分页的数据
     * order   0 不变     1 反序
     */
    function  page_array($count, $page, $array, $order, $msgtype)
    {
        $redis = RedisClient::getInstance();
        global $countpage; #定全局变量
        $page  = (empty($page)) ? '1' : $page; //判断当前页面是否为空 如果为空就表示为第一页面
        $start = ($page - 1) * $count; //计算每次分页的开始位置
        if ($order == 1) {
            $array = array_reverse($array);
        }
        $totals              = count($array);
        $countpage           = ceil($totals / $count); #计算总页面数
        $pagedata            = [];
        $pagedata            = array_slice($array, $start, $count);
        $friendCircelTimelin = new FriendCircleTimelin();
        foreach ($pagedata as $k => $v) {
            $redisGet = $redis->get("bx_friend_msg:" . $v['fcmid']);
            if (!empty($redisGet)&&$redisGet!="[]") {
                $pagedata[$k]['msgdetail'] = json_decode($redisGet, true)[0];
            } else {

                $friendMsg = new FriendCircleMessage();
                $rest1     = $friendMsg->getQuery(['id' => $v['fcmid']], '*', 'id');
                if(!empty($rest1[0])){
                    $redis->set("bx_friend_msg:" . $v['fcmid'], json_encode($rest1));
                }
                $redisGet                  = $redis->get("bx_friend_msg:" . $v['fcmid']);
                $pagedata[$k]['msgdetail'] = json_decode($redisGet, true)[0];
            }
            $pagedata[$k]['msgdetail']['content'] = emoji_decode($pagedata[$k]['msgdetail']['content']);
            if ($pagedata[$k]['msgdetail']['uid'] == USERID) {
                $pagedata[$k]['ismysender'] = 1;
            } else {
                $pagedata[$k]['ismysender'] = 0;
            }
            $pagedata[$k]['msgdetail']['usermsg'] = userMsg($pagedata[$k]['msgdetail']['uid'], 'user_id,avatar,nickname,gender');
            if (!empty($pagedata[$k]['msgdetail']['privateid'])) {
                $pagedata[$k]['msgdetail']['privatemsg'] = prviateMsg($pagedata[$k]['msgdetail']['privateid']);
            } else {
                $pagedata[$k]['msgdetail']['privatemsg'] = [];
            }
            if (!empty($pagedata[$k]['msgdetail']['title'])) {
                $pagedata[$k]['msgdetail']['title'] = titleMsg($pagedata[$k]['msgdetail']['title']);
            } else {
                $pagedata[$k]['msgdetail']['title'] = [];
            }
            if (!empty($pagedata[$k]['msgdetail']['extend_circle'])) {
                $circlecircle                                      = new FriendCircleCircle();
                $circlemsg                                         = $circlecircle->getQuery(['circle_id' => $pagedata[$k]['msgdetail']['extend_circle']], "circle_id,circle_name,circle_describe,circle_cover_img,circle_background_img,dismiss", "circle_id");
                $pagedata[$k]['msgdetail']['extend_circledetail']  = $circlemsg;
                $circleFollow                                      = new FriendCircleCircleFollow();
                $pagedata[$k]['msgdetail']['extend_circlfollowed'] = $circleFollow->countTotal(['circle_id' => $pagedata[$k]['msgdetail']['extend_circle'], 'uid' => USERID, 'is_follow' => 1]);
            } else {
                $pagedata[$k]['msgdetail']['extend_circledetail'] = [];
            }
            $msgLive                                          = new FriendCircleMessageLive();
            $pagedata[$k]['msgdetail']['extend_already_live'] = $msgLive->countTotal(['fcmid' => $v['fcmid'], 'uid' => USERID, 'status' => 1]) ? $msgLive->countTotal(['fcmid' => $v['fcmid'], 'uid' => USERID, 'status' => 1]) : 0;
            if (!empty($pagedata[$k]['msgdetail']['systemplus'])) {
                $sysplus2Array = json_decode($pagedata[$k]['msgdetail']['systemplus'], true);
                if ($pagedata[$k]['msgdetail']['systemtype'] == 2) {
                    $goodsToArray = $sysplus2Array;
                    if ($goodsToArray['goods_type'] == 0) {
                        $goods                         = new \app\taokegoods\service\Goods();
                        $detail                        = $goods->getGoodsInfo(["id" => $goodsToArray['id']]);
                        $returnGoods                   = filterMsg($detail, 'id,goods_id,title,img,price,discount_price,coupon_price,commission_rate,commission,shop_type,volume');
                        $returnGoods['comfrom']        = 0;
                        $returnGoods['full_reduction'] = '满XX减XX';
                    } else {
                        $httpClient = new HttpClient();
                        $para       = [
                            'goods_id' => $goodsToArray['id'],
                        ];
                        //这里是小店的接口网址暂时写死
                        $result      = $httpClient->post(config('app.mall_address') . "index.php?s=/liveapi/Goods/getGoodsDetail", $para)->getData('json')['data'];
                        $returnGoods = [
                            'goods_id'        => $result['goods_id'],
                            'goods_name'      => $result['goods_name'],
                            'mansong_name'    => $result['mansong_name'] ? $result['mansong_name'] : '',
                            'coupon'          => $result['coupon_list']['money'] ? $result['coupon_list']['money'] : 0,
                            'sales'           => $result['sales'],
                            'pic_cover'       => $result['img_list'][0]['pic_cover'],
                            'commission'      => '',
                            'price'           => $result['price'],
                            'promotion_price' => $result['promotion_price'],
                            'comfrom'         => '1',    //1商城0:淘客
                            'full_reduction'  => '满XX减XX',
                        ];
                    }
                    $pagedata[$k]['msgdetail']['systemplus'] = $returnGoods;
                }
                if($pagedata[$k]['msgdetail']['render_type']==12){
                    $livemember = $redis->smembers(self::$livePrefix . 'Living');
                    $templive = $sysplus2Array;
                    if (in_array($templive['uid'], $livemember))
                    {
                        $pagedata[$k]['msgdetail']['systemplus'] = $templive;
                    }
                    else
                    {
                        $templive['islive'] = 0;
                        $pagedata[$k]['msgdetail']['systemplus'] = $templive;
                    }
                }
            } else {
                $pagedata[$k]['msgdetail']['systemplus'] = (object)[];
            }
            $pagedata[$k]['msgdetail']['like_num_int']    = (int)$pagedata[$k]['msgdetail']['like_num'];
            $pagedata[$k]['msgdetail']['comment_num_int'] = (int)$pagedata[$k]['msgdetail']['comment_num'];
            $pagedata[$k]['msgdetail']['like_num']    = number_format2($pagedata[$k]['msgdetail']['like_num']);
            $pagedata[$k]['msgdetail']['comment_num'] = number_format2($pagedata[$k]['msgdetail']['comment_num']);

            if (!empty($pagedata[$k]['msgdetail']['picture'])) {
                $pagedata[$k]['msgdetail']['smallpicture'] = actPicture($pagedata[$k]['msgdetail']['picture']);
                $pagedata[$k]['msgdetail']['picture']      = array_filter(explode(',', $pagedata[$k]['msgdetail']['picture']));
                $pagedata[$k]['msgdetail']['imgs_detail']  = empty($pagedata[$k]['msgdetail']['imgs_detail']) ? [] : json_decode($pagedata[$k]['msgdetail']['imgs_detail']);
            } else {
                $pagedata[$k]['msgdetail']['smallpicture'] = [];
                $pagedata[$k]['msgdetail']['picture']      = [];
                $pagedata[$k]['msgdetail']['imgs_detail']  = [];
            }
            //查询我是否关注了该用户
            $followModel = new Follow();
            $followInfo  = $followModel->getFollowInfo(USERID, $pagedata[$k]['msgdetail']['uid']);
            if (empty($followInfo['is_follow'])) {
                $pagedata[$k]['msgdetail']['extend_followed'] = 0;
            } else {
                $pagedata[$k]['msgdetail']['extend_followed'] = 1;
            }
            //插入推荐圈子
            if ($msgtype == 3 && $page == 1) {
                if ($k == 0) {
                    $pagedata[$k]['msgdetail']['render_type']    = 7;
                    $circle                                      = new FriendCircleCircle();
                    $rest1                                       = $circle->getQueryNum(['status' => 1, 'is_recom' => 1], '*', 'circle_id desc', 9);
                    $pagedata[$k]['msgdetail']['circle_recomed'] = $rest1;
                } else {
                    $pagedata[$k]['msgdetail']['circle_recomed'] = [];
                }
            } else {
                if ($msgtype == 2 && $pagedata[$k]['msgdetail']['extend_type'] == 1 && $page == 1) {
                    if ($k == ($count - 1)) {
                        $pagedata[$k + 1]                                = $pagedata[$k];
                        $pagedata[$k + 1]['msgdetail']['render_type']    = 7;
                        $circle                                          = new FriendCircleCircle();
                        $rest1                                           = $circle->getQueryNum(['status' => 1, 'is_recom' => 1], '*', 'circle_id desc', 3);
                        $pagedata[$k + 1]['msgdetail']['circle_recomed'] = $rest1;
                    } else {
                        if (!empty($pagedata[$k + 1]['msgdetail']['id'])) {
                            $pagedata[$k + 1]['msgdetail']['circle_recomed'] = [];
                        }
                    }
                }
            }
            $pagedata[$k]['msgdetail']['comment_img_limit'] = $this->friendConfigRes['comment_img_length'];
            if ($pagedata[$k]['msgdetail']['render_type'] == 20) {
                $systemplustoArray                        = json_decode($pagedata[$k]['msgdetail']['systemplus'], true);
                $videoIDs                                 = [$systemplustoArray['videoID']];
                $prophet                                  = new Prophet(USERID, APP_MEID);
                $videos                                   = $prophet->getVideos($videoIDs);
                $videoService                             = new \app\common\service\Video();
                $videos                                   = $videoService->initializeFilm($videos, \app\common\service\Video::$allow_fields['common']);
                $systemplus                               = $videos;
                $pagedata[$k]['msgdetail']['systemplus']  = (object)[];
                $pagedata[$k]['msgdetail']['small_video'] = (object)$systemplus[0];
            } else {
                $pagedata[$k]['msgdetail']['small_video'] = (object)[];
            }
            $pagedata[$k]['msgdetail']['difftime'] = time_before($pagedata[$k]['msgdetail']['create_time'], '前');
            //如果是合唱返还合唱需要的数据（用户信息,歌词内容和相关key,声音链接数组）
            if (!empty($pagedata[$k]['msgdetail']['extend_talk']) && !empty(trim($sysplus2Array['uid'], ","))) {
                $singUserArray = explode(',', trim($sysplus2Array['uid'], ","));
                if (!empty($singUserArray)) {
                    $singFrined = [];
                    foreach ($singUserArray as $k1 => $v1) {
                        $singFrined[] = userMsg($v1, 'user_id,avatar,nickname,gender');
                    }
                } else {
                    $singFrined = [];
                }
                $lyrics      = new FriendCircleLyric();
                $singlyrics  = unserialize($lyrics->find(['id' => $pagedata[$k]['msgdetail']['extend_talk']])['lyrics']);
                $lyricsArray = [];
                foreach ($singlyrics as $k2 => $v2) {
                    $lyricsArray[] = [
                        'lyrics_key' => $k2 + 1,
                        'value'      => $v2,
                    ];
                }
                $lyricskey = $sysplus2Array['id'];
                $songArray = explode(',', trim($sysplus2Array['parent_id'], ","));
                $songs     = [];
                if (!empty(array_filter($songArray))) {
                    foreach ($songArray as $ksong => $vsong) {
                        $redisGet = $redis->get("bx_friend_msg:" . $vsong);
                        $songs[]  = json_decode($redisGet, true)[0]['voice'];
                    }
                }
                $songs[]                           = $pagedata[$k]['msgdetail']['voice'];
                $pagedata[$k]['msgdetail']['song'] = [
                    'singFrined' => array_filter($singFrined),
                    'singlyrics' => $lyricsArray,
                    'lyricskey'  => $lyricskey,
                    'songs'      => $songs,
                ];
            } else {
                $pagedata[$k]['msgdetail']['song'] = [];
            }
            $pagedata[$k]['msgdetail']['create_time'] = date("Y-m-d", $pagedata[$k]['msgdetail']['create_time']);
        }
        $data = [
            'total_count' => $totals,
            'page_count'  => $countpage,
            'data'        => $pagedata ? $pagedata : [],
        ];
        return $data;  //返回查询数据
    }

    /**
     * 对消息进行点赞功能
     * @return \think\response\Json
     */
    public function msg_live()
    {
        $userId = USERID;
        $submit = submit_verify('friendsub' . USERID);
        if (!$submit) return $this->jsonError('您提交太频繁了~~~');
        $params   = request()->param();
        $validate = new \app\api\validate\Friend();
        $result   = $validate->scene('msg_live')->check($params);
        if (true !== $result) {
            return $this->jsonError($validate->getError());
        }
        $msg       = new FriendCircleMessage();
        $msgdetail = $msg->find(['id' => $params['fcmid']]);
        if ($userId == $msgdetail['uid']) {
            return $this->jsonError('您不能点赞自己~~~');
        }
        $msgLive       = new FriendCircleMessageLive();
        $params['uid'] = $userId;
        $rest          = $msgLive->live($params);
        if (!$rest) return $this->jsonError('操作失败');
        $redis = new RedisClient();
        $data  = $redis->get('usermsg_live:' . $userId);
        return $this->success($data, '点赞成功');
    }

    /**
     * 最近联系的好友
     * @return Json
     */
    public function atelyConnect()
    {
//        $redis    = RedisClient::getInstance();
//        $len      = $redis->llen('RecentContact:' . USERID);
//        $contacts = [];
//        if (!empty($len)) {
//            if ($len > 8) {
//                for ($i = $len - 8; $i > 0; $i--) {
//                    $redis->lpop('RecentContact:' . USERID);
//                }
//            }
//            $user_ids  = $redis->lrange('RecentContact:' . USERID, 0, -1);
//            $userModel = new User();
//            $contacts  = $userModel->getUsers($user_ids, USERID, 'user_id, nickname, avatar, is_follow, gender, level, verified, is_creation, sign, vip_status');
//            foreach ($contacts as &$value) {
//                $value['is_live'] = (int)$redis->sismember('BG_LIVE:Living', $value['user_id']);
//            }
//        }
//        return $this->success($contacts, '查询成功');
//下面的暂留备用
        $params     = request()->param();
        $page_index = $params['page_index'] ? $params['page_index'] : 1;
        $page_size  = $params['page_size'] ? $params['page_size'] : 10;
        $friendMsg  = new FriendCircleMessage();
        $list       = $friendMsg->atelyConnect(USERID);
        if (!empty($list)) {
            foreach ($list as $k => $v) {
                $list1[] = ['uid' => $v, 'ctime' => $k];
            }
        }
        if (empty($list1)) {
            return $this->success((object)[], '查询成功');
        }
        $rest = $this->page_arrayd($page_size, $page_index, $list1, 1);
        return $this->success($rest, '查询成功');
    }

    /**
     * 我关注的
     * @return Json
     */
    public function myFocuse()
    {
        $follow     = new Follow();
        $userId     = USERID;
        $params     = request()->param();
        $page_index = $params['page_index'] ? $params['page_index'] - 1 : 0;
        $page_size  = $params['page_size'] ? $params['page_size'] : 10;
        $key        = "follow:{$userId}";
        $redis      = new RedisClient();
        $rest       = $follow->getFollowList($userId, 'desc', $page_index * $page_size, $page_size, ['member' => 'user_id', 'score' => 'create_time']);
        if (!empty($rest)) {
            foreach ($rest as $k => $v) {
                $rest[$k]['userdetail'] = userMsg($v['user_id'], 'user_id,nickname,avatar,gender');
                if ($v['user_id'] == 0) {
                    unset($rest[$k]);
                }
            }
        }
        $rest1 = array_values($rest);
        $data  = [
            'total_count' => $redis->zCard($key) - 1,
            'page_count'  => ceil(($redis->zCard($key) - 1) / $page_size),
            'data'        => $rest1,
        ];
        return $this->success($data, '获取成功');
    }

    /**
     * 我的粉丝
     * @return Json
     */
    public function focuseMe()
    {
        $follow     = new Follow();
        $userId     = USERID;
        $params     = request()->param();
        $page_index = $params['page_index'] ? $params['page_index'] - 1 : 0;
        $page_size  = $params['page_size'] ? $params['page_size'] : 10;
        $key        = "fans:{$userId}";
        $redis      = new RedisClient();
        $rest       = $follow->getFansList($userId, 'desc', $page_index * $page_size, $page_size, ['member' => 'user_id', 'score' => 'create_time']);
        if (!empty($rest)) {
            foreach ($rest as $k => $v) {
                $rest[$k]['userdetail'] = userMsg($v['user_id'], 'user_id,nickname,avatar,gender,sign');
                if ($v['user_id'] == 0) {
                    unset($rest[$k]);
                }
            }
        }
        $data = [
            'total_count' => $redis->zCard($key) - 1,
            'page_count'  => ceil(($redis->zCard($key) - 1) / $page_size),
            'data'        => $rest,
        ];
        return $this->success($data, '获取成功');
    }

    function page_arrayd($count, $page, $array, $order)
    {
        $redis = RedisClient::getInstance();
        global $countpage; #定全局变量
        $page  = (empty($page)) ? '1' : $page; //判断当前页面是否为空 如果为空就表示为第一页面
        $start = ($page - 1) * $count; //计算每次分页的开始位置
        if ($order == 1) {
            $array = array_reverse($array);
        }
        $totals              = count($array);
        $countpage           = ceil($totals / $count); #计算总页面数
        $pagedata            = [];
        $pagedata            = array_slice($array, $start, $count);
        $friendCircelTimelin = new FriendCircleTimelin();
        foreach ($pagedata as $k => $v) {
            $pagedata[$k]['timedeiff'] = time_before($v['ctime'], '前');
            $pagedata[$k]['usermsg']   = userMsg($v['uid'], 'user_id,nickname,avatar,gender,sign');
        }
        $data = [
            'total_count' => $totals,
            'page_count'  => $countpage,
            'data'        => $pagedata,
        ];
        return $data;  //返回查询数据
    }

    /**
     * 搜索好友
     * @return Json
     */
    public function searchFriend()
    {
        $userModel   = new User();
        $all_user    = $userModel->followSearch(USERID, input('key_words'));
        $all_user_id = array_column($all_user, 'user_id');
        $contacts    = $userModel->getUsers($all_user_id, USERID, 'user_id, nickname, avatar, is_follow, gender, level, verified, is_creation, sign, vip_status');
        $redis       = RedisClient::getInstance();
        foreach ($contacts as &$value) {
            $value['is_live'] = (int)$redis->sismember('BG_LIVE:Living', $value['user_id']);
        }
        return $this->success($contacts, '获取成功');
    }

    /**
     * 设置过滤条件
     * @return Json
     */
    public function setFilter()
    {
        $submit = submit_verify('friendfiltersub' . USERID);
        if (!$submit) return $this->jsonError('您提交太频繁了~~~');
        $params   = request()->param();
        $validate = new \app\api\validate\Friend();
        $result   = $validate->scene('setFilter')->check($params);
        if (true !== $result) {
            return $this->jsonError($validate->getError());
        }
        if ($params['filter_id'] == USERID) {
            return $this->jsonError("您不能屏蔽自己");
        }
        $msgFilter = new FriendCircleMessageFilter();
        if ($msgFilter->check($params)) {
            return $this->jsonError('已经禁止过了');
        }
        $params['uid'] = USERID;
        $rest          = $msgFilter->add($params);
        if (!$rest) return $this->jsonError('操作失败');
        return $this->success($rest, '操作成功');
    }

    /**
     * 我发出的
     * $type 0：全部2：动态 3:圈子6：表白
     * @return Json
     */
    public function mySender()
    {
        $params     = request()->param();
        $redis      = RedisClient::getInstance();
        $page_index = $params['page_index'] ? $params['page_index'] : 1;
        $page_size  = $params['page_size'] ? $params['page_size'] : 10;
        $type       = $params['type'] ? $params['type'] : 0;
        $params     = request()->param();
        $validate   = new \app\api\validate\Friend();
        $result     = $validate->scene('mySender')->check($params);
        if (true !== $result) {
            return $this->jsonError($validate->getError());
        }
        $userid = $params['user_id'];
        if ($type) {
            $where = ['uid' => $userid, 'type' => $type, 'status' => 1];
        } else {
            $where = ['uid' => $userid, 'status' => 1];
        }
        $circleMsg = new  FriendCircleMessage();
        //这里添加过滤条件
        $rest = $circleMsg->getQuery($where, '*', 'id desc');
        foreach ($rest as $k => $v) {
            $rest[$k]['fcmid'] = $v['id'];
        }

        $rest = $this->page_array_my($page_size, $page_index, $rest, 0, 2);
        return $this->success($rest, '获取成功');
    }

    /**
     * 举报
     * @return Json
     */
    public function report()
    {
        $submit = submit_verify('report' . USERID);
        if (!$submit) return $this->jsonError('您提交太频繁了~~~');
        $params   = request()->param();
        $validate = new \app\api\validate\Friend();
        $result   = $validate->scene('report')->check($params);
        if (true !== $result) {
            return $this->jsonError($validate->getError());
        }
        if ($params['report_uid'] == USERID) {
            return $this->jsonError('您不能举报自己');
        }
        $params['uid'] = USERID;
        $report        = new FriendCircleMessageReport();
        if ($report->checkAlready($params) > 0) {
            return $this->jsonError('您已经举报过了');
        }
        $add = $report->add($params);
        if (!$add) return $this->jsonError('操作失败');
        return $this->success($add, '操作成功');
    }

    /**
     * 获取直播房间的相关信息
     * @return Json
     */
    public function getLiveRoomMsg()
    {
        $params   = request()->param();
        $validate = new \app\api\validate\Friend();
        $result   = $validate->scene('getLiveRoomMsg')->check($params);
        if (true !== $result) {
            return $this->jsonError($validate->getError());
        }
        $roomModel = new LiveModel();
        $room      = $roomModel->get($params['room_id'])->toarray();
        $restur    = filterMsg($room, 'user_id,nickname,avatar,title,cover_url,pull');
        //随机加一句特别的话
        //      $r = mt_rand(0, count(enum_array(friend_msg_live_goodwords)) - 1);
        if (empty($restur['title'])) {
            $restur['title'] = "大家好，我正在直播";
        }
        $coreSdk = new CoreSdk();
        $audience = $coreSdk->post('zombie/getRoomAudience', ['room_id' => $params['room_id']]);
        $restur['audience'] = $audience[$params['room_id']];
        //   $restur['user'] = userMsg($restur['user_id'], 'user_id,avatar,nickname,gender');
        //    $restur['advword'] = enum_array(friend_msg_live_goodwords)[$r]['name'];
        return $this->success($restur, '操作成功');
    }

    /**
     * 获取发圈的商品信息
     * @return Json
     */
    public function getMsgGoods()
    {
        $params   = request()->param();
        $validate = new \app\api\validate\Friend();
        $result   = $validate->scene('getMsgGoods')->check($params);
        if (true !== $result) {
            return $this->jsonError($validate->getError());
        }
        //这里要对发布者权限进行判定（如果是第三方；如果是小店）
        $userDetail = Db::name('user')->where(['user_id' => USERID])->find();
        if ($params['goods_type'] == 0) {
            if ($userDetail['taoke_shop'] == 0) {
                return $this->jsonError('您还没有开启小店功能');
            }
            $goods       = new \app\taokegoods\service\Goods();
            $detail      = $goods->getGoodsInfo(["id" => $params['goods_id']]);
            $returnGoods = filterMsg($detail, 'id,goods_id,title,img,price,discount_price,coupon_price,commission_rate,commission,shop_type,volume');
        } else {
            if (empty($userDetail['taoke_shop'])) {
                return $this->jsonError('您还没有开启商城功能');
            }
            $httpClient  = new HttpClient();
            $para        = [
                'goods_id' => $params['goods_id'],
            ];
            $result      = $httpClient->post("http://www.shop_b2b.com/index.php?s=/liveapi/Goods/getGoodsDetail", $para)->getData('json')['data'];
            $returnGoods = [
                'goods_id'     => $result['goods_id'],
                'goods_name'   => $result['goods_name'],
                'mansong_name' => $result['mansong_name'] ? $result['mansong_name'] : '',
                'coupon'       => $result['coupon_list']['money'] ? $result['coupon_list']['money'] : 0,
                'sales'        => $result['sales'],
                'pic_cover'    => $result['img_list'][0]['pic_cover']
            ];
        }
        $returnGoods['goods_type'] = $params['goods_type'];
        return $this->success($returnGoods, '操作成功');
    }

    /**
     * 获取动态详情
     * @return Json
     */
    public function getTimeLineDetail()
    {
    }

    /**
     * 点击@获取个人信息
     * @return Json
     */
    public function atUserMsg()
    {
        $params   = request()->param();
        $validate = new \app\api\validate\Friend();
        $result   = $validate->scene('atUserMsg')->check($params);
        if (true !== $result) {
            return $this->jsonError($validate->getError());
        }
        $redis      = \bxkj_common\RedisClient::getInstance();
        $key        = "user:{$params['at_id']}";
        $userDetail = json_decode($redis->get($key), true);
        if (empty($userDetail)) {
            $userDetail = \think\db::name('user')->where(['user_id' => $params['at_id']])->find();
        }
        $user = $userDetail;
        $find = Db::name('user_data_deal')->where(['user_id' => $params['at_id'], 'audit_status' => '-1'])->find();
        if ($find['data']) {
            $find_data = json_decode($find['data'], true);
            if (is_array($find_data)) {
                $user = array_merge($user, $find_data);
            }
        } else {
            $find = Db::name('user_data_deal')->where(['user_id' => $params['at_id'], 'audit_status' => '0'])->find();
            if ($find['data']) {
                $find_data = json_decode($find['data'], true);
                if (is_array($find_data)) {
                    $user = array_merge($user, $find_data);
                }
            }
        }
        \app\common\service\User::safeFiltering($user);
        $like_num  = Db::name('video_like')->where(['user_id' => $params['at_id']])->count();
        $video_num = Db::name('video')->where(['user_id' => $params['at_id']])->count();
        if ($user['is_anchor']) {
            $UserService          = new \app\common\service\User();
            $user['anchor_level'] = Db::name('anchor')->where('user_id', $user['user_id'])->value('anchor_lv');;
            $user['anchor_level_progress'] = $UserService->getAnchorLevelProcess($user);
        }
        $user['video_num']  = (int)$video_num ?: 0;
        $user['like_num']   = (int)$like_num ?: 0;
        $userTask           = new User_task();
        $isComplete         = $userTask->getUserTaskStatus($params['at_id']);
        $user['isComplete'] = $isComplete;
        return $this->success($user, '获取成功');
    }

    /**
     * 附近的动态
     * @return Json
     */
    public function nearbyMessage()
    {
        $params   = request()->param();
        $validate = new \app\api\validate\Friend();
        $result   = $validate->scene('nearbyMessage')->check($params);
        if (true !== $result) {
            return $this->jsonError($validate->getError());
        }
        $page_index = $params['page_index'] ? $params['page_index'] : 1;
        $page_size  = $params['page_size'] ? $params['page_size'] : 10;
        //获取该点周围的4个点
        $distance = $this->friendConfigRes['friend_near_max'];//范围（单位千米）
        $params   = request()->param();
        $toArray  = explode(',', $params['location']);
        $type     = $params['type'] ? $params['type'] : 2;
        $lat      = $toArray[0];
        $lng      = $toArray[1];
        $dlng     = 2 * asin(sin($distance / (2 * EARTH_RADIUS)) / cos(deg2rad($lat)));
        $dlng     = rad2deg($dlng);
        $dlat     = $distance / EARTH_RADIUS;
        $dlat     = rad2deg($dlat);
        $squares  = array('left-top'     => array('lat' => $lat + $dlat, 'lng' => $lng - $dlng),
            'right-top'    => array('lat' => $lat + $dlat, 'lng' => $lng + $dlng),
            'left-bottom'  => array('lat' => $lat - $dlat, 'lng' => $lng - $dlng),
            'right-bottom' => array('lat' => $lat - $dlat, 'lng' => $lng + $dlng)
        );
        //从数库查询匹配的记录
        $circle  = new FriendCircleMessage();
        $where[] = ['lat', 'neq', 0];
        $where[] = ['lat', '>', $squares['right-bottom']['lat']];
        $where[] = ['lat', '<', $squares['left-top']['lat']];
        $where[] = ['lng', '<', $squares['left-top']['lng']];
        $where[] = ['lng', '>', $squares['right-bottom']['lng']];
        $where[] = ['status', 'eq', 1];
        //  $where[] = ['type', 'eq', $type];
        $rest = $circle->getQuery($where, '*', 'id desc');
        $rest = filerMsg($rest);
        $point1 = array('lat' => $toArray[0], 'long' => $toArray[1]);
        if (!empty($rest)) {
            foreach ($rest as $k => $v) {
                $point2               = array('lat' => $v['lat'], 'long' => $v['lng']);
                $distance             = getDistanceBetweenPointsNew($point1['lat'], $point1['long'], $point2['lat'], $point2['long']);
                $rest[$k]['distance'] = getDistanceBetweenPointsNew($point1['lat'], $point1['long'], $point2['lat'], $point2['long'])['meters'];
                $rest[$k]['fcmid']    = $v['id'];
            }
        }
        $distance = array_column($rest, 'distance');
        array_multisort($distance, SORT_DESC, $rest);
        $rest = $this->page_array($page_size, $page_index, $rest, 1, 2);
        return $this->success($rest, '查询成功');
    }

    /**
     * 删除动态
     * @return Json
     */
    public function delMessage()
    {
        //权限验证，可以删除自己的动态
        $params   = request()->param();
        $validate = new \app\api\validate\Friend();
        $result   = $validate->scene('delMessage')->check($params);
        if (true !== $result) {
            return $this->jsonError($validate->getError());
        }
        $friendCircleMessage = new FriendCircleMessage();
        $ids[]               = $params['msg_id'];
        if (!$friendCircleMessage->checkown(USERID, $params['msg_id'])) {
            return $this->jsonError('不属于您，您没有删除权限');
        }
        $num = $friendCircleMessage->del($ids);
        if (!$num) return $this->jsonError('删除失败');
        return $this->success("删除成功，共计删除{$num}条记录");
    }

    /**
     * 获取动态详情
     * @return Json
     */
    public function msgDetail()
    {
        $redis      = \bxkj_common\RedisClient::getInstance();
        $params   = request()->param();
        $validate = new \app\api\validate\Friend();
        $result   = $validate->scene('msgDetail')->check($params);
        if (true !== $result) {
            return $this->jsonError($validate->getError());
        }
        $msg                     = new FriendCircleMessage();
        $msgdetail               = $msg->getQuery(['id' => $params['msg_id'], 'status' => 1], '*', 'id');
        if(empty($msgdetail)){
            return $this->jsonError('该信息已经删除 ');
        }
        $msgdetail[0]['usermsg'] = userMsg($msgdetail[0]['uid'], 'user_id,avatar,nickname,gender');
        if (!empty($msgdetail[0]['privateid'])) {
            $msgdetail[0]['privatemsg'] = prviateMsg($msgdetail[0]['privateid']);
        } else {
            $msgdetail[0]['privatemsg'] = [];
        }
        if (!empty($msgdetail[0]['title'])) {
            $msgdetail[0]['title'] = titleMsg($msgdetail[0]['title']);
        }
        if (!empty($msgdetail[0]['extend_circle'])) {
            $circlecircle                         = new FriendCircleCircle();
            $circlemsg                            = $circlecircle->getQuery(['circle_id' => $msgdetail[0]['extend_circle']], "circle_id,circle_name,circle_describe,circle_cover_img,circle_background_img", "circle_id");
            $msgdetail[0]['extend_circledetail']  = $circlemsg;
            $circleFollow                         = new FriendCircleCircleFollow();
            $msgdetail[0]['extend_circlfollowed'] = $circleFollow->countTotal(['circle_id' => $msgdetail[0]['extend_circle'], 'uid' => USERID, 'is_follow' => 1]);
        } else {
            $msgdetail[0]['extend_circledetail'] = [];
        }
        $msgdetail[0]['content'] = emoji_decode($msgdetail[0]['content']);
        $followModel = new Follow();
        $followInfo  = $followModel->getFollowInfo(USERID, $msgdetail[0]['uid']);
        if (empty($followInfo['is_follow'])) {
            $msgdetail[0]['extend_followed'] = 0;
        } else {
            $msgdetail[0]['extend_followed'] = 1;
        }
        if (!empty($msgdetail[0]['systemplus'])) {
            if ($msgdetail[0]['systemtype'] == 2) {
                $goodsToArray = json_decode($msgdetail[0]['systemplus'], true);
                if ($goodsToArray['goods_type'] == 0) {
                    $goods                  = new \app\taokegoods\service\Goods();
                    $detail                 = $goods->getGoodsInfo(["id" => $goodsToArray['id']]);
                    $returnGoods            = filterMsg($detail, 'id,goods_id,title,img,price,discount_price,coupon_price,commission_rate,commission,shop_type,volume');
                    $returnGoods['comfrom'] = 0;
                } else {
                    $httpClient = new HttpClient();
                    $para       = [
                        'goods_id' => $goodsToArray['id'],
                    ];
                    //这里是小店的接口网址暂时写死
                    $result      = $httpClient->post(config('app.mall_address') . "index.php?s=/liveapi/Goods/getGoodsDetail", $para)->getData('json')['data'];
                    $returnGoods = [
                        'goods_id'        => $result['goods_id'],
                        'goods_name'      => $result['goods_name'],
                        'mansong_name'    => $result['mansong_name'] ? $result['mansong_name'] : '',
                        'coupon'          => $result['coupon_list']['money'] ? $result['coupon_list']['money'] : 0,
                        'sales'           => $result['sales'],
                        'pic_cover'       => $result['img_list'][0]['pic_cover'],
                        'commission'      => '',
                        'price'           => $result['price'],
                        'promotion_price' => $result['promotion_price'],
                        'comfrom'         => '1'      //1商城0:淘客
                    ];
                }
                $msgdetail[0]['systemplus'] = $returnGoods;
            }
            if($msgdetail[0]['render_type']==12){
                $livemember = $redis->smembers(self::$livePrefix . 'Living');
                $sysplus2Array = json_decode($msgdetail[0]['systemplus'], true);
                $templive = $sysplus2Array;
                if (in_array($templive['uid'], $livemember))
                {
                    $msgdetail[0]['systemplus'] = (object)$templive;
                }
                else
                {
                    $templive['islive'] = 0;
                    $msgdetail[0]['systemplus'] = (object)$templive;
                }
            }
        } else {
            $msgdetail[0]['systemplus'] = (object)[];
        }
        if (!empty($msgdetail[0]['picture'])) {
            $msgdetail[0]['smallpicture'] = actPicture($msgdetail[0]['picture']);;
            $msgdetail[0]['picture']                  = array_filter(explode(',', $msgdetail[0]['picture']));
            $msgdetail[0]['imgs_detail'] = empty($msgdetail[0]['imgs_detail']) ? [] : json_decode($msgdetail[0]['imgs_detail']);
        } else {
            $msgdetail[0]['smallpicture']             = [];
            $msgdetail[0]['picture']                  = [];
            $msgdetail[0]['imgs_detail'] = [];
        }
        if (empty($msgdetail[0]['title'])) {
            $msgdetail[0]['title'] = [];
        }
        $msgdetail[0]['difftime']            = time_before($msgdetail[0]['create_time'], '前');
        $msgLive                             = new FriendCircleMessageLive();
        $msgdetail[0]['extend_already_live'] = $msgLive->countTotal(['fcmid' => $msgdetail[0]['id'], 'uid' => USERID, 'status' => 1]) ? $msgLive->countTotal(['fcmid' => $msgdetail[0]['id'], 'uid' => USERID, 'status' => 1]) : 0;
        $msgdetail[0]['comment_img_limit']   = $this->friendConfigRes['comment_img_length'];
        if ($msgdetail[0]['render_type'] == 20) {
            $systemplustoArray           = json_decode($msgdetail[0]['systemplus'], true);
            $videoIDs                    = [$systemplustoArray['videoID']];
            $prophet                     = new Prophet(USERID, APP_MEID);
            $videos                      = $prophet->getVideos($videoIDs);
            $videoService                = new \app\common\service\Video();
            $videos                      = $videoService->initializeFilm($videos, \app\common\service\Video::$allow_fields['common']);
            $systemplus                  = $videos;
            $msgdetail[0]['systemplus']  = (object)[];
            $msgdetail[0]['small_video'] = (object)$systemplus[0];
        } else {
            $msgdetail[0]['small_video'] = (object)[];
        }
        $msgdetail[0]['like_num_int'] =  $msgdetail[0]['like_num'];
        $msgdetail[0]['comment_num_int'] =  $msgdetail[0]['comment_num'];
        return $this->success((object)$msgdetail[0], '查询成功');
    }

    /**
     * 获取我的好友
     * @return Json
     */
    public function getFriendsList()
    {
        $params        = request()->param();
        $offset        = $params['offset'] ? $params['offset'] : 0;
        $length        = $params['length'] ? $params['length'] : 10;
        $followModel   = new FollowModel();
        $myFriendsList = $followModel->mutualList(USERID, $offset, $length);
        return $this->success($myFriendsList);
    }

    /**
     * 搜索动态
     * @return Json
     */
    public function searchDynamic()
    {
        $params     = input();
        $redis      = RedisClient::getInstance();
        $type       = $params['type'];
        $page_index = isset($params['page_index']) ? $params['page_index'] : 1;
        $page_size  = isset($params['page_size']) ? $params['page_size'] : PAGE_LIMIT;
        //搜索动态数据
        $msgModel = new FriendCircleMessage();
        if (preg_match('/^\d+$/', $params['keyword'] )) {
        } else {
            //这里查询一下名字对应的可能的id,如果有就生成一下uid
            $whereU[] = ['nickname', 'like', "%{$params['keyword'] }%"];
            $find    = Db::name('user')
                ->where($whereU)
                ->field('user_id')
                ->find();
            if(!empty($find)){
                $params['keyword'] = $find['user_id'];
            }
        }
        $where[]  = ['dynamic_title|content|uid', 'like', '%' . $params['keyword'] . '%'];
        $where[]  = ['status', 'eq', 1];
        $msglist  = $msgModel->searchQuery($page_index, $page_size, $where, 'id desc', '*');
        $pagedata = $msglist['data'];
        foreach ($pagedata as $k => $v) {
            $pagedata[$k]['fcmid'] = $v['id'];
            $redisGet              = $redis->get("bx_friend_msg:" . $v['id']);
            if (!empty($redisGet)&&$redisGet!="[]") {
                $pagedata[$k]['msgdetail'] = json_decode($redisGet, true)[0];
            } else {
                $friendMsg = new FriendCircleMessage();
                $rest1     = $friendMsg->getQuery(['id' => $v['id']], '*', 'id');
                $redis->set("bx_friend_msg:" . $v['id'], json_encode($rest1));
                $redisGet                  = $redis->get("bx_friend_msg:" . $v['id']);
                $pagedata[$k]['msgdetail'] = json_decode($redisGet, true)[0];
            }
            $pagedata[$k]['msgdetail']['usermsg'] = userMsg($pagedata[$k]['msgdetail']['uid'], 'user_id,avatar,nickname,gender');
            $pagedata[$k]['msgdetail']['content'] = emoji_decode($pagedata[$k]['msgdetail']['content']);
            if (!empty($pagedata[$k]['msgdetail']['privateid'])) {
                $pagedata[$k]['msgdetail']['privatemsg'] = prviateMsg($pagedata[$k]['msgdetail']['privateid']);
            } else {
                $pagedata[$k]['msgdetail']['privatemsg'] = [];
            }
            if (!empty($pagedata[$k]['msgdetail']['title'])) {
                $pagedata[$k]['msgdetail']['title'] = titleMsg($pagedata[$k]['msgdetail']['title']);
            } else {
                $pagedata[$k]['msgdetail']['title'] = [];
            }
            if (!empty($pagedata[$k]['msgdetail']['extend_circle'])) {
                $circlecircle                                      = new FriendCircleCircle();
                $circlemsg                                         = $circlecircle->getQuery(['circle_id' => $pagedata[$k]['msgdetail']['extend_circle']], "circle_id,circle_name,circle_describe,circle_cover_img,circle_background_img,dismiss", "circle_id");
                $pagedata[$k]['msgdetail']['extend_circledetail']  = $circlemsg;
                $circleFollow                                      = new FriendCircleCircleFollow();
                $pagedata[$k]['msgdetail']['extend_circlfollowed'] = $circleFollow->countTotal(['circle_id' => $pagedata[$k]['msgdetail']['extend_circle'], 'uid' => USERID, 'is_follow' => 1]);
            } else {
                $pagedata[$k]['msgdetail']['extend_circledetail'] = [];
            }
            $msgLive                                          = new FriendCircleMessageLive();
            $pagedata[$k]['msgdetail']['extend_already_live'] = $msgLive->countTotal(['fcmid' => $v['id'], 'uid' => USERID, 'status' => 1]) ? $msgLive->countTotal(['fcmid' => $v['id'], 'uid' => USERID, 'status' => 1]) : 0;
            if (!empty($pagedata[$k]['msgdetail']['systemplus'])) {
                $sysplus2Array = json_decode($pagedata[$k]['msgdetail']['systemplus'], true);
                if ($pagedata[$k]['msgdetail']['systemtype'] == 2) {
                    $goodsToArray = $sysplus2Array;
                    if ($goodsToArray['goods_type'] == 0) {
                        $goods                         = new \app\taokegoods\service\Goods();
                        $detail                        = $goods->getGoodsInfo(["id" => $goodsToArray['id']]);
                        $returnGoods                   = filterMsg($detail, 'id,goods_id,title,img,price,discount_price,coupon_price,commission_rate,commission,shop_type,volume');
                        $returnGoods['comfrom']        = 0;
                        $returnGoods['full_reduction'] = '满XX减XX';
                    } else {
                        $httpClient = new HttpClient();
                        $para       = [
                            'goods_id' => $goodsToArray['id'],
                        ];
                        //这里是小店的接口网址暂时写死
                        $result      = $httpClient->post(config('app.mall_address') . "index.php?s=/liveapi/Goods/getGoodsDetail", $para)->getData('json')['data'];
                        $returnGoods = [
                            'goods_id'        => $result['goods_id'],
                            'goods_name'      => $result['goods_name'],
                            'mansong_name'    => $result['mansong_name'] ? $result['mansong_name'] : '',
                            'coupon'          => $result['coupon_list']['money'] ? $result['coupon_list']['money'] : 0,
                            'sales'           => $result['sales'],
                            'pic_cover'       => $result['img_list'][0]['pic_cover'],
                            'commission'      => '',
                            'price'           => $result['price'],
                            'promotion_price' => $result['promotion_price'],
                            'comfrom'         => '1',    //1商城0:淘客
                            'full_reduction'  => '满XX减XX',
                        ];
                    }
                    $pagedata[$k]['msgdetail']['systemplus'] = $returnGoods;
                } else {
                    $pagedata[$k]['msgdetail']['systemplus'] = (object)$sysplus2Array;
                }
            } else {
                $pagedata[$k]['msgdetail']['systemplus'] = (object)[];
            }
            $pagedata[$k]['msgdetail']['like_num_int']    = (int)$pagedata[$k]['msgdetail']['like_num'];
            $pagedata[$k]['msgdetail']['comment_num_int'] = (int)$pagedata[$k]['msgdetail']['comment_num'];
            $pagedata[$k]['msgdetail']['like_num']    = number_format2($pagedata[$k]['msgdetail']['like_num']);
            $pagedata[$k]['msgdetail']['comment_num'] = number_format2($pagedata[$k]['msgdetail']['comment_num']);
            if (!empty($pagedata[$k]['msgdetail']['picture'])) {
                $pagedata[$k]['msgdetail']['smallpicture'] = actPicture($pagedata[$k]['msgdetail']['picture']);
                $pagedata[$k]['msgdetail']['picture']      = array_filter(explode(',', $pagedata[$k]['msgdetail']['picture']));
                $pagedata[$k]['msgdetail']['imgs_detail']  = empty($pagedata[$k]['msgdetail']['imgs_detail']) ? [] : json_decode($pagedata[$k]['msgdetail']['imgs_detail']);
            } else {
                $pagedata[$k]['msgdetail']['smallpicture'] = [];
                $pagedata[$k]['msgdetail']['picture']      = [];
                $pagedata[$k]['msgdetail']['imgs_detail']  = [];
            }
            //查询我是否关注了该用户
            $followModel = new Follow();
            $followInfo  = $followModel->getFollowInfo(USERID, $pagedata[$k]['msgdetail']['uid']);
            if (empty($followInfo['is_follow'])) {
                $pagedata[$k]['msgdetail']['extend_followed'] = 0;
            } else {
                $pagedata[$k]['msgdetail']['extend_followed'] = 1;
            }
            $pagedata[$k]['msgdetail']['difftime'] = time_before($pagedata[$k]['msgdetail']['create_time'], '前');
            if ($pagedata[$k]['msgdetail']['render_type'] == 20) {
                $systemplustoArray                        = $sysplus2Array;
                $videoIDs                                 = [$systemplustoArray['videoID']];
                $prophet                                  = new Prophet(USERID, APP_MEID);
                $videos                                   = $prophet->getVideos($videoIDs);
                $videoService                             = new \app\common\service\Video();
                $videos                                   = $videoService->initializeFilm($videos, \app\common\service\Video::$allow_fields['common']);
                $systemplus                               = $videos;
                $pagedata[$k]['msgdetail']['systemplus']  = (object)[];
                $pagedata[$k]['msgdetail']['small_video'] = (object)$systemplus[0];
            } else {
                $pagedata[$k]['msgdetail']['small_video'] = (object)[];
            }
            //如果是合唱返还合唱需要的数据（用户信息,歌词内容和相关key,声音链接数组）
            if (!empty($pagedata[$k]['msgdetail']['extend_talk']) && !empty(trim($sysplus2Array['uid'], ","))) {
                $singUserArray = explode(',', trim($sysplus2Array['uid'], ","));
                if (!empty($singUserArray)) {
                    $singFrined = [];
                    foreach ($singUserArray as $k1 => $v1) {
                        $singFrined[] = userMsg($v1, 'user_id,avatar,nickname,gender');
                    }
                } else {
                    $singFrined = [];
                }
                $lyrics      = new FriendCircleLyric();
                $singlyrics  = unserialize($lyrics->find(['id' => $pagedata[$k]['msgdetail']['extend_talk']])['lyrics']);
                $lyricsArray = [];
                foreach ($singlyrics as $k2 => $v2) {
                    $lyricsArray[] = [
                        'lyrics_key' => $k2 + 1,
                        'value'      => $v2,
                    ];
                }
                $lyricskey = $sysplus2Array['id'];
                $songArray = explode(',', trim($sysplus2Array['parent_id'], ","));
                $songs     = [];
                if (!empty(array_filter($songArray))) {
                    foreach ($songArray as $ksong => $vsong) {
                        $redisGet = $redis->get("bx_friend_msg:" . $vsong);
                        $songs[]  = json_decode($redisGet, true)[0]['voice'];
                    }
                }
                $songs[]                           = $pagedata[$k]['msgdetail']['voice'];
                $pagedata[$k]['msgdetail']['song'] = [
                    'singFrined' => array_filter($singFrined),
                    'singlyrics' => $lyricsArray,
                    'lyricskey'  => $lyricskey,
                    'songs'      => $songs,
                ];
            } else {
                $pagedata[$k]['msgdetail']['song'] = [];
            }
        }
        $msglist['data'] = $pagedata;
        return $this->success($msglist, '获取成功');
    }

    public function myfollowed()
    {
        $params      = input();
        $followModel = new Follow();
        $list        = $followModel->getFollowList(USERID, $order = 'desc', $offset = 0, $length = 1000, $options = null);
        $followed    = array_keys(array_filter($list));
        $redis       = RedisClient::getInstance();
        $page_index  = isset($params['page_index']) ? $params['page_index'] : 1;
        $page_size   = isset($params['page_size']) ? $params['page_size'] : PAGE_LIMIT;
        //搜索动态数据
        $filter          = new FriendCircleMessageFilter();
        $filterUserArray = $filter->filterUserArray(USERID, 2);
        $circleFollow    = new FriendCircleCircleFollow();
        $circleNumArray  = $circleFollow->getQuery(['uid' => USERID, 'status' => 2], 'circle_id', 'id desc');
        $circleMsg       = new FriendCircleMessage();
        foreach ($circleNumArray as $k => $v) {
            $rest = $circleMsg->getQuery(['extend_circle' => $v['circle_id']], 'id', 'id desc');
            if (!empty($rest)) {
                foreach ($rest as $k => $v) {
                    array_push($filterUserArray, $v['id']);
                }
            }
        }
        $followed[] = (int)USERID;
        $msgModel = new FriendCircleMessage();
        $where[]  = ['uid', 'in', $followed];
        $where[]  = ['id', 'NOT IN', $filterUserArray];
        $where[]  = ['status', 'eq', 1];
        $msglist  = $msgModel->searchQuery($page_index, $page_size, $where, 'id desc', '*');
        $pagedata = $msglist['data'];
        foreach ($pagedata as $k => $v) {
            $pagedata[$k]['difftime'] = time_before($v['create_time'], '前');
            $pagedata[$k]['fcmid']    = $v['id'];
            $redisGet                 = $redis->get("bx_friend_msg:" . $v['id']);
            if (!empty($redisGet)&&$redisGet!="[]") {
                $pagedata[$k]['msgdetail'] = json_decode($redisGet, true)[0];
            } else {
                $friendMsg = new FriendCircleMessage();
                $rest1     = $friendMsg->getQuery(['id' => $v['id']], '*', 'id');
                $redis->set("bx_friend_msg:" . $v['id'], json_encode($rest1));
                $redisGet                  = $redis->get("bx_friend_msg:" . $v['id']);
                $pagedata[$k]['msgdetail'] = json_decode($redisGet, true)[0];
            }
            $pagedata[$k]['msgdetail']['usermsg'] = userMsg($pagedata[$k]['msgdetail']['uid'], 'user_id,avatar,nickname,gender');
            $pagedata[$k]['msgdetail']['content'] = emoji_decode($pagedata[$k]['msgdetail']['content']);
            if (!empty($pagedata[$k]['msgdetail']['privateid'])) {
                $pagedata[$k]['msgdetail']['privatemsg'] = prviateMsg($pagedata[$k]['msgdetail']['privateid']);
            } else {
                $pagedata[$k]['msgdetail']['privatemsg'] = [];
            }
            if (!empty($pagedata[$k]['msgdetail']['title'])) {
                $pagedata[$k]['msgdetail']['title'] = titleMsg($pagedata[$k]['msgdetail']['title']);
            } else {
                $pagedata[$k]['msgdetail']['title'] = [];
            }
            if (!empty($pagedata[$k]['msgdetail']['extend_circle'])) {
                $circlecircle                                      = new FriendCircleCircle();
                $circlemsg                                         = $circlecircle->getQuery(['circle_id' => $pagedata[$k]['msgdetail']['extend_circle']], "circle_id,circle_name,circle_describe,circle_cover_img,circle_background_img,dismiss", "circle_id");
                $pagedata[$k]['msgdetail']['extend_circledetail']  = $circlemsg;
                $circleFollow                                      = new FriendCircleCircleFollow();
                $pagedata[$k]['msgdetail']['extend_circlfollowed'] = $circleFollow->countTotal(['circle_id' => $pagedata[$k]['msgdetail']['extend_circle'], 'uid' => USERID, 'is_follow' => 1]);
            } else {
                $pagedata[$k]['msgdetail']['extend_circledetail'] = [];
            }
            $msgLive                                          = new FriendCircleMessageLive();
            $pagedata[$k]['msgdetail']['extend_already_live'] = $msgLive->countTotal(['fcmid' => $v['id'], 'uid' => USERID, 'status' => 1]) ? $msgLive->countTotal(['fcmid' => $v['id'], 'uid' => USERID, 'status' => 1]) : 0;
            if (!empty($pagedata[$k]['msgdetail']['systemplus'])) {
                $sysplus2Array = json_decode($pagedata[$k]['msgdetail']['systemplus'], true);
                if ($pagedata[$k]['msgdetail']['systemtype'] == 2) {
                    $goodsToArray = $sysplus2Array;
                    if ($goodsToArray['goods_type'] == 0) {
                        $goods                         = new \app\taokegoods\service\Goods();
                        $detail                        = $goods->getGoodsInfo(["id" => $goodsToArray['id']]);
                        $returnGoods                   = filterMsg($detail, 'id,goods_id,title,img,price,discount_price,coupon_price,commission_rate,commission,shop_type,volume');
                        $returnGoods['comfrom']        = 0;
                        $returnGoods['full_reduction'] = '满XX减XX';
                    } else {
                        $httpClient = new HttpClient();
                        $para       = [
                            'goods_id' => $goodsToArray['id'],
                        ];
                        //这里是小店的接口网址暂时写死
                        $result      = $httpClient->post(config('app.mall_address') . "index.php?s=/liveapi/Goods/getGoodsDetail", $para)->getData('json')['data'];
                        $returnGoods = [
                            'goods_id'        => $result['goods_id'],
                            'goods_name'      => $result['goods_name'],
                            'mansong_name'    => $result['mansong_name'] ? $result['mansong_name'] : '',
                            'coupon'          => $result['coupon_list']['money'] ? $result['coupon_list']['money'] : 0,
                            'sales'           => $result['sales'],
                            'pic_cover'       => $result['img_list'][0]['pic_cover'],
                            'commission'      => '',
                            'price'           => $result['price'],
                            'promotion_price' => $result['promotion_price'],
                            'comfrom'         => '1',    //1商城0:淘客
                            'full_reduction'  => '满XX减XX',
                        ];
                    }
                    $pagedata[$k]['msgdetail']['systemplus'] = $returnGoods;
                } else {
                    $pagedata[$k]['msgdetail']['systemplus'] = (object)$sysplus2Array;
                }
            } else {
                $pagedata[$k]['msgdetail']['systemplus'] = (object)[];
            }
            $pagedata[$k]['msgdetail']['like_num_int']    = (int)$pagedata[$k]['msgdetail']['like_num'];
            $pagedata[$k]['msgdetail']['comment_num_int'] = (int)$pagedata[$k]['msgdetail']['comment_num'];
            $pagedata[$k]['msgdetail']['like_num']    = number_format2($pagedata[$k]['msgdetail']['like_num']);
            $pagedata[$k]['msgdetail']['comment_num'] = number_format2($pagedata[$k]['msgdetail']['comment_num']);
            if (!empty($pagedata[$k]['msgdetail']['picture'])) {
                $pagedata[$k]['msgdetail']['smallpicture'] = actPicture($pagedata[$k]['msgdetail']['picture']);
                $pagedata[$k]['msgdetail']['picture']      = explode(',', $pagedata[$k]['msgdetail']['picture']);
                $pagedata[$k]['msgdetail']['imgs_detail']  = empty($pagedata[$k]['msgdetail']['imgs_detail']) ? [] : json_decode($pagedata[$k]['msgdetail']['imgs_detail']);
            } else {
                $pagedata[$k]['msgdetail']['smallpicture'] = [];
                $pagedata[$k]['msgdetail']['picture']      = [];
                $pagedata[$k]['msgdetail']['imgs_detail']  = [];
            }
            if ($pagedata[$k]['msgdetail']['render_type'] == 20) {
                $systemplustoArray                        = $sysplus2Array;
                $videoIDs                                 = [$systemplustoArray['videoID']];
                $prophet                                  = new Prophet(USERID, APP_MEID);
                $videos                                   = $prophet->getVideos($videoIDs);
                $videoService                             = new \app\common\service\Video();
                $videos                                   = $videoService->initializeFilm($videos, \app\common\service\Video::$allow_fields['common']);
                $systemplus                               = $videos;
                $pagedata[$k]['msgdetail']['systemplus']  = (object)[];
                $pagedata[$k]['msgdetail']['small_video'] = (object)$systemplus[0];
            } else {
                $pagedata[$k]['msgdetail']['small_video'] = (object)[];
            }
            //查询我是否关注了该用户
            $followModel = new Follow();
            $followInfo  = $followModel->getFollowInfo(USERID, $pagedata[$k]['msgdetail']['uid']);
            if (empty($followInfo['is_follow'])) {
                $pagedata[$k]['msgdetail']['extend_followed'] = 0;
            } else {
                $pagedata[$k]['msgdetail']['extend_followed'] = 1;
            }
            $pagedata[$k]['msgdetail']['difftime'] = time_before($pagedata[$k]['msgdetail']['create_time'], '前');
            //如果是合唱返还合唱需要的数据（用户信息,歌词内容和相关key,声音链接数组）
            if (!empty($pagedata[$k]['msgdetail']['extend_talk']) && !empty(trim($sysplus2Array['uid'], ","))) {
                $singUserArray = explode(',', trim($sysplus2Array['uid'], ","));
                if (!empty($singUserArray)) {
                    $singFrined = [];
                    foreach ($singUserArray as $k1 => $v1) {
                        $singFrined[] = userMsg($v1, 'user_id,avatar,nickname,gender');
                    }
                } else {
                    $singFrined = [];
                }
                $lyrics      = new FriendCircleLyric();
                $singlyrics  = unserialize($lyrics->find(['id' => $pagedata[$k]['msgdetail']['extend_talk']])['lyrics']);
                $lyricsArray = [];
                foreach ($singlyrics as $k2 => $v2) {
                    $lyricsArray[] = [
                        'lyrics_key' => $k2 + 1,
                        'value'      => $v2,
                    ];
                }
                $lyricskey = $sysplus2Array['id'];
                $songArray = explode(',', trim($sysplus2Array['parent_id'], ","));
                $songs     = [];
                if (!empty(array_filter($songArray))) {
                    foreach ($songArray as $ksong => $vsong) {
                        $redisGet = $redis->get("bx_friend_msg:" . $vsong);
                        $songs[]  = json_decode($redisGet, true)[0]['voice'];
                    }
                }
                $songs[]                           = $pagedata[$k]['msgdetail']['voice'];
                $pagedata[$k]['msgdetail']['song'] = [
                    'singFrined' => array_filter($singFrined),
                    'singlyrics' => $lyricsArray,
                    'lyricskey'  => $lyricskey,
                    'songs'      => $songs,
                ];
            } else {
                $pagedata[$k]['msgdetail']['song'] = [];
            }
            $pagedata[$k]['msgdetail']['create_time'] = date("Y-m-d", $pagedata[$k]['msgdetail']['create_time']);
        }
        $msglist['data'] = $pagedata;
        return $this->success($msglist, '获取成功');
    }

    function page_array_my($count, $page, $array, $order, $msgtype)
    {
        $redis = RedisClient::getInstance();
        global $countpage; #定全局变量
        $page  = (empty($page)) ? '1' : $page; //判断当前页面是否为空 如果为空就表示为第一页面
        $start = ($page - 1) * $count; //计算每次分页的开始位置
        if ($order == 1) {
            $array = array_reverse($array);
        }
        $totals              = count($array);
        $countpage           = ceil($totals / $count); #计算总页面数
        $pagedata            = [];
        $pagedata            = array_slice($array, $start, $count);
        $friendCircelTimelin = new FriendCircleTimelin();
        foreach ($pagedata as $k => $v) {
            $redisGet = $redis->get("bx_friend_msg:" . $v['fcmid']);
            if (!empty($redisGet)&&$redisGet!="[]") {
                $pagedata[$k]['msgdetail'] = json_decode($redisGet, true)[0];
            } else {
                $friendMsg = new FriendCircleMessage();
                $rest1     = $friendMsg->getQuery(['id' => $v['fcmid']], '*', 'id');
                $redis->set("bx_friend_msg:" . $v['fcmid'], json_encode($rest1));
                $redisGet                  = $redis->get("bx_friend_msg:" . $v['fcmid']);
                $pagedata[$k]['msgdetail'] = json_decode($redisGet, true)[0];
            }
            $pagedata[$k]['msgdetail']['content'] = emoji_decode($pagedata[$k]['msgdetail']['content']);
            if ($pagedata[$k]['msgdetail']['uid'] == USERID) {
                $pagedata[$k]['ismysender'] = 1;
            } else {
                $pagedata[$k]['ismysender'] = 0;
            }
            $pagedata[$k]['msgdetail']['usermsg'] = userMsg($pagedata[$k]['msgdetail']['uid'], 'user_id,avatar,nickname,gender');
            if (!empty($pagedata[$k]['msgdetail']['privateid'])) {
                $pagedata[$k]['msgdetail']['privatemsg'] = prviateMsg($pagedata[$k]['msgdetail']['privateid']);
            } else {
                $pagedata[$k]['msgdetail']['privatemsg'] = [];
            }
            if (!empty($pagedata[$k]['msgdetail']['title'])) {
                $pagedata[$k]['msgdetail']['title'] = titleMsg($pagedata[$k]['msgdetail']['title']);
            } else {
                $pagedata[$k]['msgdetail']['title'] = [];
            }
            if (!empty($pagedata[$k]['msgdetail']['extend_circle'])) {
                $circlecircle                                      = new FriendCircleCircle();
                $circlemsg                                         = $circlecircle->getQuery(['circle_id' => $pagedata[$k]['msgdetail']['extend_circle']], "circle_id,circle_name,circle_describe,circle_cover_img,circle_background_img,dismiss", "circle_id");
                $pagedata[$k]['msgdetail']['extend_circledetail']  = $circlemsg;
                $circleFollow                                      = new FriendCircleCircleFollow();
                $pagedata[$k]['msgdetail']['extend_circlfollowed'] = $circleFollow->countTotal(['circle_id' => $pagedata[$k]['msgdetail']['extend_circle'], 'uid' => USERID, 'is_follow' => 1]);
            } else {
                $pagedata[$k]['msgdetail']['extend_circledetail'] = [];
            }
            $msgLive                                          = new FriendCircleMessageLive();
            $pagedata[$k]['msgdetail']['extend_already_live'] = $msgLive->countTotal(['fcmid' => $v['fcmid'], 'uid' => USERID, 'status' => 1]) ? $msgLive->countTotal(['fcmid' => $v['fcmid'], 'uid' => USERID, 'status' => 1]) : 0;
            if (!empty($pagedata[$k]['msgdetail']['systemplus'])) {
                $sysplus2Array = json_decode($pagedata[$k]['msgdetail']['systemplus'], true);
                if ($pagedata[$k]['msgdetail']['systemtype'] == 2) {
                    $goodsToArray = $sysplus2Array;
                    if ($goodsToArray['goods_type'] == 0) {
                        $goods                         = new \app\taokegoods\service\Goods();
                        $detail                        = $goods->getGoodsInfo(["id" => $goodsToArray['id']]);
                        $returnGoods                   = filterMsg($detail, 'id,goods_id,title,img,price,discount_price,coupon_price,commission_rate,commission,shop_type,volume');
                        $returnGoods['comfrom']        = 0;
                        $returnGoods['full_reduction'] = '满XX减XX';
                    } else {
                        $httpClient = new HttpClient();
                        $para       = [
                            'goods_id' => $goodsToArray['id'],
                        ];
                        //这里是小店的接口网址暂时写死
                        $result      = $httpClient->post(config('app.mall_address') . "index.php?s=/liveapi/Goods/getGoodsDetail", $para)->getData('json')['data'];
                        $returnGoods = [
                            'goods_id'        => $result['goods_id'],
                            'goods_name'      => $result['goods_name'],
                            'mansong_name'    => $result['mansong_name'] ? $result['mansong_name'] : '',
                            'coupon'          => $result['coupon_list']['money'] ? $result['coupon_list']['money'] : 0,
                            'sales'           => $result['sales'],
                            'pic_cover'       => $result['img_list'][0]['pic_cover'],
                            'commission'      => '',
                            'price'           => $result['price'],
                            'promotion_price' => $result['promotion_price'],
                            'comfrom'         => '1',    //1商城0:淘客
                            'full_reduction'  => '满XX减XX',
                        ];
                    }
                    $pagedata[$k]['msgdetail']['systemplus'] = $returnGoods;
                }
                if($pagedata[$k]['msgdetail']['render_type']==12){
                    $livemember = $redis->smembers(self::$livePrefix . 'Living');
                    $templive = $sysplus2Array;
                    if (in_array($templive['uid'], $livemember))
                    {
                        $pagedata[$k]['msgdetail']['systemplus'] = $templive;
                    }
                    else
                    {
                        $templive['islive'] = 0;
                        $pagedata[$k]['msgdetail']['systemplus'] = $templive;
                    }
                }
            } else {
                $pagedata[$k]['msgdetail']['systemplus'] = (object)[];
            }
            $pagedata[$k]['msgdetail']['like_num_int']    = (int)$pagedata[$k]['msgdetail']['like_num'];
            $pagedata[$k]['msgdetail']['comment_num_int'] = (int)$pagedata[$k]['msgdetail']['comment_num'];
            $pagedata[$k]['msgdetail']['like_num']    = number_format2($pagedata[$k]['msgdetail']['like_num']);
            $pagedata[$k]['msgdetail']['comment_num'] = number_format2($pagedata[$k]['msgdetail']['comment_num']);
            if (!empty($pagedata[$k]['msgdetail']['picture'])) {
                $pagedata[$k]['msgdetail']['smallpicture'] = actPicture($pagedata[$k]['msgdetail']['picture']);
                $pagedata[$k]['msgdetail']['picture']      = array_filter(explode(',', $pagedata[$k]['msgdetail']['picture']));
                $pagedata[$k]['msgdetail']['imgs_detail']  = empty($pagedata[$k]['msgdetail']['imgs_detail']) ? [] : json_decode($pagedata[$k]['msgdetail']['imgs_detail']);
            } else {
                $pagedata[$k]['msgdetail']['smallpicture'] = [];
                $pagedata[$k]['msgdetail']['picture']      = [];
                $pagedata[$k]['msgdetail']['imgs_detail']  = [];
            }
            //查询我是否关注了该用户
            $followModel = new Follow();
            $followInfo  = $followModel->getFollowInfo(USERID, $pagedata[$k]['msgdetail']['uid']);
            if (empty($followInfo['is_follow'])) {
                $pagedata[$k]['msgdetail']['extend_followed'] = 0;
            } else {
                $pagedata[$k]['msgdetail']['extend_followed'] = 1;
            }
            $pagedata[$k]['msgdetail']['comment_img_limit'] = $this->friendConfigRes['comment_img_length'];
            if ($pagedata[$k]['msgdetail']['render_type'] == 20) {
                $systemplustoArray                        = json_decode($pagedata[$k]['msgdetail']['systemplus'], true);
                $videoIDs                                 = [$systemplustoArray['videoID']];
                $prophet                                  = new Prophet(USERID, APP_MEID);
                $videos                                   = $prophet->getVideos($videoIDs);
                $videoService                             = new \app\common\service\Video();
                $videos                                   = $videoService->initializeFilm($videos, \app\common\service\Video::$allow_fields['common']);
                $systemplus                               = $videos;
                $pagedata[$k]['msgdetail']['systemplus']  = (object)[];
                $pagedata[$k]['msgdetail']['small_video'] = (object)$systemplus[0];
            } else {
                $pagedata[$k]['msgdetail']['small_video'] = (object)[];
            }
            $pagedata[$k]['msgdetail']['difftime'] = time_before($pagedata[$k]['msgdetail']['create_time'], '前');
            //如果是合唱返还合唱需要的数据（用户信息,歌词内容和相关key,声音链接数组）
            if (!empty($pagedata[$k]['msgdetail']['extend_talk']) && !empty(trim($sysplus2Array['uid'], ","))) {
                $singUserArray = explode(',', trim($sysplus2Array['uid'], ","));
                if (!empty($singUserArray)) {
                    $singFrined = [];
                    foreach ($singUserArray as $k1 => $v1) {
                        $singFrined[] = userMsg($v1, 'user_id,avatar,nickname,gender');
                    }
                } else {
                    $singFrined = [];
                }
                $lyrics      = new FriendCircleLyric();
                $singlyrics  = unserialize($lyrics->find(['id' => $pagedata[$k]['msgdetail']['extend_talk']])['lyrics']);
                $lyricsArray = [];
                foreach ($singlyrics as $k2 => $v2) {
                    $lyricsArray[] = [
                        'lyrics_key' => $k2 + 1,
                        'value'      => $v2,
                    ];
                }
                $lyricskey = $sysplus2Array['id'];
                $songArray = explode(',', trim($sysplus2Array['parent_id'], ","));
                $songs     = [];
                if (!empty(array_filter($songArray))) {
                    foreach ($songArray as $ksong => $vsong) {
                        $redisGet = $redis->get("bx_friend_msg:" . $vsong);
                        $songs[]  = json_decode($redisGet, true)[0]['voice'];
                    }
                }
                $songs[]                           = $pagedata[$k]['msgdetail']['voice'];
                $pagedata[$k]['msgdetail']['song'] = [
                    'singFrined' => array_filter($singFrined),
                    'singlyrics' => $lyricsArray,
                    'lyricskey'  => $lyricskey,
                    'songs'      => $songs,
                ];
            } else {
                $pagedata[$k]['msgdetail']['song'] = [];
            }
            $pagedata[$k]['msgdetail']['create_time'] = date("Y-m-d", $pagedata[$k]['msgdetail']['create_time']);
        }
        $data = [
            'total_count' => $totals,
            'page_count'  => $countpage,
            'data'        => $pagedata ? $pagedata : [],
        ];
        return $data;  //返回查询数据
    }
}
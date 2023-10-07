<?php

namespace app\api\controller;

use app\admin\service\SysConfig;
use app\common\controller\Controller;
use app\common\service\DsSession;
use app\api\service\live\Lists;
use app\friend\service\FriendCircleCircle;
use app\friend\service\FriendCircleCircleFollow;
use app\friend\service\FriendCircleLyric;
use app\friend\service\FriendCircleMessage;
use app\friend\service\FriendCircleMessageLive;
use bxkj_common\ClientInfo;
use bxkj_common\CoreSdk;
use app\common\service\Video as VideoModel;
use bxkj_common\HttpClient;
use bxkj_common\Prophet;
use bxkj_common\RedisClient;
use bxkj_module\exception\ApiException;
use bxkj_module\service\Follow;
use think\Db;
use Thrift\StringFunc\Core;

/**
 *
 * Class Films
 * @package App\Api
 */
class Search extends Controller
{
    public function __construct()
    {
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
    }

    public function index()
    {
        $videoModel = new VideoModel();
        $videos     = Db::name('video')->order('score desc,id desc')->limit(10)->select();
        $rs         = $videoModel->initializeFilm($videos, VideoModel::$allow_fields['common'], USERID);
        $arr        = [
            'hots'       => $this->getHotKeywords(6),
            'recommends' => $rs
        ];
        return $this->success($arr, '获取成功');
    }

    public function rankList()
    {
        $params        = input();
        $arr           = [];
        $popularityArr = [];
        $videoModel    = new VideoModel();
        if ($params['type'] == 'hot_search') {
            $arr = $this->getHotKeywords(30);
        } else if ($params['type'] == 'film') {
            $rs  = Db::name('video')->order('zan_sum desc,id desc')->limit(20)->select();
            $rs  = $videoModel->initializeFilm($rs, VideoModel::$allow_fields['common'], USERID);
            $arr = $rs ? $rs : [];
            foreach ($arr as &$item) {
                $item['rank_score_str'] = $item['zan_sum'];
            }
        } else if ($params['type'] == 'user') {
            $spaceInfo = Db::name('recommend_space')->where(['mark' => 'user_film_talent'])->find();
            $prefix    = config('database.prefix');
            $sql       = 'select user.user_id,user.avatar,user.nickname,user.level,user.is_creation,user.verified,user.sign from ' . $prefix . 'recommend_content content join ' . $prefix . 'user user on content.rel_id=user.user_id WHERE content.rec_id=? order by content.sort desc,user.film_num desc,user.create_time desc LIMIT 20';
            $users     = Db::query($sql, [$spaceInfo['id']]);
            $arr       = $users ? $users : [];
            foreach ($arr as &$item) {
                $popularityArr[]          = mt_rand(1000000, 10000000);
                $item['rank_score_total'] = 10000000;
            }
        }
        rsort($popularityArr);
        foreach ($arr as $index => &$item) {
            $item['rank'] = $index + 1;
            if ($params['type'] == 'user') {
                $item['rank_score']     = $popularityArr[$index];
                $item['rank_score_str'] = number_format2($item['rank_score']);
            }
        }
        return $this->success($arr, '获取成功');
    }

    private function getHotKeywords($length = 6)
    {
        $arr     = [];
        $coreSdk = new CoreSdk();
        $ad      = $coreSdk->post('ad/get_contents', array(
            'space'       => 'hot_keywords',
            'purview'     => '*',
            'city_id'     => '',
            'os'          => strtolower(ClientInfo::get('os_name')),
            'code'        => ClientInfo::get('v_code'),
            'multi'       => '1',
            'offset'      => 0,
            'length'      => $length,
            'client_seri' => ClientInfo::encode()
        ));
        if ($ad && $ad['hot_keywords'] && $ad['hot_keywords']['contents']) {
            foreach ($ad['hot_keywords']['contents'] as $content) {
                $title = $content['title'];
                $item  = [
                    'badge' => '',
                    'title' => $title,
                    'img'   => $content['image']['common']
                ];
                if (preg_match('/^【/', $title)) {
                    $matches = [];
                    preg_match('/^【([^【】]+)】(.*)$/U', $title, $matches);
                    $item['badge'] = ($matches[1] == '新') ? 'new' : (($matches[1] == '热') ? 'hot' : '');
                    $item['title'] = $matches[2] ? $matches[2] : '';
                }
                $item['rank_score_str'] = '1.0万';
                $arr[]                  = $item;
            }
        }
        return $arr;
    }

    //综合搜索
    public function complex()
    {
        $params = input();
        //调试指令入口
        $rootMatches = [];
        if (preg_match('/^root\:([^\;\:]+);$/', trim($params['keyword']), $rootMatches)) {
            $rootParams = [];
            if ($rootMatches[1] == 'destroy') {
                DsSession::set('root_order', null);
            } else {
                $queryArr = explode('&', $rootMatches[1]);
                foreach ($queryArr as $itemStr) {
                    list($queryKey, $queryVal) = explode('=', $itemStr);
                    $rootParams[trim($queryKey)] = $queryVal;
                }
                $rootParams2 = DsSession::get('root_order');
                $rootParams2 = is_array($rootParams2) ? $rootParams2 : [];
                DsSession::set('root_order', array_merge($rootParams2, $rootParams));
            }
            $json = '{"user":{"list":[{"user_id":"0","nickname":"root order success","millet":"12143240","avatar":"https://static.cnibx.cn/bingxin/admin/reg_avatar/thumb/9cea49ecf40a585e47e6df592c0522a525c0ab46.png?imageView2/1/w/200/h/200","gender":"1","birthday":"2019-03-26","city_id":0,"exp":"350090","level":"22","verified":"1","is_creation":"0","sign":"' . $rootMatches[1] . '","vip_expire":"0","like_num":"0","like_num_str":"0","film_num":"0","film_num_str":"0","fans_num":"0","fans_num_str":"0","vip_expire_str":"未开通","age":0,"city_name":"","is_black":"0","is_official":"0","is_follow":"0","rank_stealth":"0","vip_status":"0","credit_score":"108"}],"more_status":0},"film":{},"live":{}}';
            return $this->success(json_decode($json, true), '获取成功');
        }
        $type      = $params['type'];
        $offset    = isset($params['offset']) ? $params['offset'] : 0;
        $length    = isset($params['length']) ? ($params['length'] > 100 ? 100 : $params['length']) : PAGE_LIMIT;
        $filmWhere = [
            'mode'     => 'list',
            'keyword'  => $params['keyword'],
            'self_uid' => USERID
        ];
        $userWhere = [
            'offset'   => $offset,
            'length'   => $length,
            'keyword'  => $params['keyword'],
            'self_uid' => USERID
        ];
        $sdk       = new CoreSdk();
        if ($type == 'film') {
            $filmModel = new VideoModel();
            $result    = $filmModel->getSearchResultnew($filmWhere, $offset, $length);
            return $this->success($result['list'] ? $result['list'] : [], '获取成功');
        } else if ($type == 'user') {
            $result = $sdk->post('user/search', $userWhere);
            return $this->success($result ? $result : [], '获取成功');
        } else if ($type == 'live') {
            $liveDomain = new Lists();
            $result     = $liveDomain->setSearch($params['keyword'], $offset, $length)->getserchLiveList();
            $result     = $liveDomain->initializeLive($result ? $result : []);
            if (!empty($result)) {
                foreach ($result as &$value) {
                    if (isset($value['jump'])) {
                        $value['jump'] = getJump('enter_room', ['room_id' => $value['room_id'], 'from' => 'search']);
                    }
                }
            }
            return $this->success($result, '获取成功');
        } else if ($type == '' || $type == 'all') {
            $offset    = 0;
            $length    = 5;
            $com       = ['user' => [], 'film' => [], 'live' => []];
            $filmModel = new VideoModel();
            $result    = $filmModel->getSearchResult($filmWhere, $offset, $length);
            if ($result && !empty($result['list'])) {
                $com['film']['list']        = $result['list'];
                $com['film']['more_status'] = $result['total'] >= $length ? 1 : 0;
            } else {
                $com['film'] = (object)$com['film'];
            }
            $userWhere['length'] = $offset;
            $userWhere['length'] = $length;
            $result2             = $sdk->post('user/search', $userWhere);
            if (!empty($result2)) {
                $com['user']['list']        = $result2;
                $com['user']['more_status'] = count($result2) >= $length ? 1 : 0;
            } else {
                $com['user'] = (object)$com['user'];
            }
            $liveDomain = new Lists();
            $result3    = $liveDomain->setSearch($params['keyword'], $offset, $length)->getLiveList();
            if (!empty($result3)) {
                $com['live']['list'] = $liveDomain->initializeLive($result3);
                foreach ($com['live']['list'] as &$value) {
                    if (isset($value['jump'])) {
                        $value['jump'] = getJump('enter_room', ['room_id' => $value['room_id'], 'from' => 'search']);
                    }
                }
                $com['live']['more_status'] = count($result3) >= $length ? 1 : 0;
            } else {
                $com['live'] = (object)$com['live'];
            }
            return $this->success($com, '获取成功');
        }
    }

    /**
     *  综合搜索新
     * type：all;user;film;live
     * @return \think\response\Json
     */
    public function complexNew()
    {
        $params     = input();
        $redis      = RedisClient::getInstance();
        $type       = $params['type'];
        $page_index = isset($params['page_index']) ? $params['page_index'] : 1;
        $page_size  = isset($params['page_size']) ? $params['page_size'] : PAGE_LIMIT;
        if ($type == '' || $type == 'all') {
            $userWhere = [
                'keyword'  => $params['keyword'],
                'self_uid' => USERID
            ];
            $userModel = new \bxkj_module\service\User();
            $userfind  = $userModel->searchByname($params['keyword'], USERID);

            //搜索动态数据
            if (preg_match('/^\d+$/', $params['keyword'] )) {
                $where[]  = ['dynamic_title|content|uid', 'like', '%' . $params['keyword'] . '%'];
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
                $where[]  = ['dynamic_title|content|uid', 'like', '%' . $params['keyword'] . '%'];
            }

            $msgModel = new FriendCircleMessage();

            $where[]  = ['status', 'eq', 1];
            $msglist  = $msgModel->searchQuery($page_index, $page_size, $where, 'id desc', '*');
            $pagedata = $msglist['data'];
            foreach ($pagedata as $k => $v) {
                $pagedata[$k]['fcmid'] = $v['id'];
                $redisGet              = $redis->get("bx_friend_msg:" . $v['id']);
                if (!empty($redisGet)) {
                    $pagedata[$k]['msgdetail'] = json_decode($redisGet, true)[0];
                } else {
                    $friendMsg = new FriendCircleMessage();
                    $rest1     = $friendMsg->getQuery(['id' => $v['id']], '*', 'id');
                    $redis->set("bx_friend_msg:" . $v['id'], json_encode($rest1));
                    $redisGet                  = $redis->get("bx_friend_msg:" . $v['id']);
                    $pagedata[$k]['msgdetail'] = json_decode($redisGet, true)[0];
                }
                $pagedata[$k]['msgdetail']['usermsg'] = userMsg($pagedata[$k]['msgdetail']['uid'], 'user_id,avatar,nickname,gender');
                $pagedata[$k]['msgdetail']['icon']    = $this->imgRe($pagedata[$k]['msgdetail']['render_type']);
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
                $pagedata[$k]['msgdetail']['content'] = emoji_decode($pagedata[$k]['msgdetail']['content']);
                //查询我是否关注了该用户
                $followModel = new Follow();
                $followInfo  = $followModel->getFollowInfo(USERID, $pagedata[$k]['msgdetail']['uid']);
                if (empty($followInfo['is_follow'])) {
                    $pagedata[$k]['msgdetail']['extend_followed'] = 0;
                } else {
                    $pagedata[$k]['msgdetail']['extend_followed'] = 1;
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
            }
            $msglist['data'] = $pagedata;
            if ($page_index == 1) {
                if (!empty($userfind)) {
                    if (!empty($pagedata)) {
                        $datamark = [
                            "id"           => 0,
                            "uid"          => USERID,
                            "fcmid"        => 0,
                            "is_own"       => 0,
                            "create_time"  => time(),
                            "type"         => 0,
                            "msg_type"     => 0,
                            "is_recommend" => 0,
                            "extend_type"  => 0,
                            "status"       => 1,
                            "difftime"     => '未知',
                            "msgdetail"    => [
                                "id"                  => 0,
                                "uid"                 => 0,
                                "privateid"           => "0",
                                "title"               => [],
                                "content"             => "动态",
                                "picture"             => [],
                                "video"               => "",
                                "voice"               => "",
                                "location"            => "117.166457,31.846994",
                                "create_time"         => time(),
                                "type"                => 2,
                                "extend_type"         => '',
                                "msg_type"            => 3,
                                "is_recommend"        => 1,
                                "status"              => 1,
                                "comment_status"      => 1,
                                "comment_num"         => "0",
                                "like_num"            => "0",
                                "systemtype"          => 0,
                                "systemplus"          => (object)[],
                                "extend_talk"         => "",
                                "extend_circle"       => "",
                                "cover_url"           => "",
                                "dynamic_title"       => "",
                                "render_type"         => 15,
                                "address"             => "安徽省合肥市",
                                "lat"                 => "117.166457",
                                "lng"                 => "31.846980",
                                "usermsg"             => (object)[],
                                "privatemsg"          => [],
                                "extend_circledetail" => [],
                                "extend_already_live" => 0,
                                "extend_followed"     => 0,
                                "circle_recomed"      => [],
                                "difftime"            => "未知",
                                "song"                => [],
                                "icon"                => $this->imgRe(0),
                            ],
                        ];
                        array_unshift($msglist['data'], $datamark);
                    }
                    //查询正在直播的相关信息插入返回数据
                    $liveDomain = new Lists();
                    $result     = $liveDomain->setSearch($params['keyword'], 0, 100)->getLiveList();
                    $result     = $liveDomain->initializeLive($result ? $result : []);

                 if(!empty($result)){
                     $coreSdk = new CoreSdk();

                        foreach ($result as $k1=>$v1){
                            $audience = $coreSdk->post('zombie/getRoomAudience', ['room_id' => $v1["room_id"]]);
                            $audiencenum = $audience[$v1["room_id"]];
                            $dataLiveroom = [
                                "id"           => $v1["room_id"],
                                "uid"          => USERID,
                                "fcmid"        => 0,
                                "is_own"       => 0,
                                "create_time"  => time(),
                                "type"         => 0,
                                "msg_type"     => 0,
                                "is_recommend" => 0,
                                "extend_type"  => 0,
                                "status"       => 1,
                                "difftime"     => '未知',
                                "msgdetail"    => [
                                    "id"                  => 0,
                                    "uid"                 => 0,
                                    "privateid"           => "0",
                                    "title"               => [],
                                    "content"             => "",
                                    "picture"             => [],
                                    "video"               => "",
                                    "voice"               => "",
                                    "location"            => $v1['lat'].$v1['lng'],
                                    "create_time"         => time(),
                                    "type"                => 2,
                                    "extend_type"         => '',
                                    "msg_type"            => 3,
                                    "is_recommend"        => 1,
                                    "status"              => 1,
                                    "comment_status"      => 1,
                                    "comment_num"         => "0",
                                    "like_num"            => $audiencenum,      //这个数据用于直播展示中的多少人围观
                                    "systemtype"          => 0,
                                    "systemplus"          => (object)["roomid"=>$v1["room_id"],"jump"=>$v1["jump"],"title"=>$v1["title"]],
                                    "extend_talk"         => "",
                                    "extend_circle"       => "",
                                    "cover_url"           => $v1['cover_url'],
                                    "dynamic_title"       => "",
                                    "render_type"         => 16,
                                    "address"             => $v1['city'],
                                    "lat"                 => $v1['lat'],
                                    "lng"                 => $v1['lng'],
                                    "usermsg"             =>  userMsg($v1['user_id'], 'user_id,avatar,nickname,gender'),
                                    "privatemsg"          => [],
                                    "extend_circledetail" => [],
                                    "extend_already_live" => 0,
                                    "extend_followed"     => $v1["is_living"],
                                    "circle_recomed"      => [],
                                    "difftime"            => $v1['create_time'],
                                    "song"                => [],
                                    "icon"                => $this->imgRe(12),
                                ],
                            ];
                            array_unshift($msglist['data'], $dataLiveroom);
                        }

                     $livemark = [
                         "id"           => 0,
                         "uid"          => USERID,
                         "fcmid"        => 0,
                         "is_own"       => 0,
                         "create_time"  => time(),
                         "type"         => 0,
                         "msg_type"     => 0,
                         "is_recommend" => 0,
                         "extend_type"  => 0,
                         "status"       => 1,
                         "difftime"     => '未知',
                         "msgdetail"    => [
                             "id"                  => 0,
                             "uid"                 => 0,
                             "privateid"           => "0",
                             "title"               => [],
                             "content"             => "直播",
                             "picture"             => [],
                             "video"               => "",
                             "voice"               => "",
                             "location"            => "117.166457,31.846994",
                             "create_time"         => time(),
                             "type"                => 2,
                             "extend_type"         => '',
                             "msg_type"            => 3,
                             "is_recommend"        => 1,
                             "status"              => 1,
                             "comment_status"      => 1,
                             "comment_num"         => "0",
                             "like_num"            => "0",
                             "systemtype"          => 0,
                             "systemplus"          => (object)[],
                             "extend_talk"         => "",
                             "extend_circle"       => "",
                             "cover_url"           => "",
                             "dynamic_title"       => "",
                             "render_type"         => 15,
                             "address"             => "安徽省合肥市",
                             "lat"                 => "117.166457",
                             "lng"                 => "31.846980",
                             "usermsg"             => (object)[],
                             "privatemsg"          => [],
                             "extend_circledetail" => [],
                             "extend_already_live" => 0,
                             "extend_followed"     => 0,
                             "circle_recomed"      => [],
                             "difftime"            => "未知",
                             "song"                => [],
                             "icon"                => $this->imgRe(12),
                         ],
                     ];
                      array_unshift($msglist['data'],$livemark);

                    }

                    foreach ($userfind as $k=>$v){
                      $wherelive['user_id'] = $v['user_id'];

                     $room = Db::name('live')->field('*, id room_id')->where($wherelive)->find();

                    if(!empty($room)){
                        $livemsgdetail = ["is_live"=>1,"roomid"=>$room["room_id"], "jump"=>"bx://router.bxtv.com/enter_room?room_id=".$room["room_id"]."&from=hot"];
                    }else{
                        $livemsgdetail = ["is_live"=>0];
                    }
                        $dataUser = [
                            "id"           => 0,
                            "uid"          => USERID,
                            "fcmid"        => 0,
                            "is_own"       => 0,
                            "create_time"  => time(),
                            "type"         => 0,
                            "msg_type"     => 0,
                            "is_recommend" => 0,
                            "extend_type"  => 0,
                            "status"       => 1,
                            "difftime"     => '未知',
                            "msgdetail"    => [
                                "id"                  => 0,
                                "uid"                 => 0,
                                "privateid"           => "0",
                                "title"               => [],
                                "content"             => "",
                                "picture"             => [],
                                "video"               => "",
                                "voice"               => "",
                                "location"            => "117.166457,31.846994",
                                "create_time"         => time(),
                                "type"                => 2,
                                "extend_type"         => '',
                                "msg_type"            => 3,
                                "is_recommend"        => 1,
                                "status"              => 1,
                                "comment_status"      => 1,
                                "comment_num"         => "0",
                                "like_num"            => "0",
                                "systemtype"          => 0,
                                "systemplus"          => (object)$livemsgdetail,
                                "extend_talk"         => "",
                                "extend_circle"       => "",
                                "cover_url"           => "",
                                "dynamic_title"       => "",
                                "render_type"         => 14,
                                "address"             => "安徽省合肥市",
                                "lat"                 => "117.166457",
                                "lng"                 => "31.846980",
                                "usermsg"             => $v,
                                "privatemsg"          => [],
                                "extend_circledetail" => [],
                                "extend_already_live" => 0,
                                "extend_followed"     => 0,
                                "circle_recomed"      => [],
                                "difftime"            => "未知",
                                "song"                => [],
                                "icon"                => $this->imgRe(14),
                            ],
                        ];
                        array_unshift($msglist['data'], $dataUser);
                    }

                    $dataUhead = [
                        "id"           => 0,
                        "uid"          => USERID,
                        "fcmid"        => 0,
                        "is_own"       => 0,
                        "create_time"  => time(),
                        "type"         => 0,
                        "msg_type"     => 0,
                        "is_recommend" => 0,
                        "extend_type"  => 0,
                        "status"       => 1,
                        "difftime"     => '未知',
                        "msgdetail"    => [
                            "id"                  => 0,
                            "uid"                 => 0,
                            "privateid"           => "0",
                            "title"               => [],
                            "content"             => "用户",
                            "picture"             => [],
                            "video"               => "",
                            "voice"               => "",
                            "location"            => "117.166457,31.846994",
                            "create_time"         => time(),
                            "type"                => 2,
                            "extend_type"         => '',
                            "msg_type"            => 3,
                            "is_recommend"        => 1,
                            "status"              => 1,
                            "comment_status"      => 1,
                            "comment_num"         => "0",
                            "like_num"            => "0",
                            "systemtype"          => 0,
                            "systemplus"          => (object)[],
                            "extend_talk"         => "",
                            "extend_circle"       => "",
                            "cover_url"           => "",
                            "dynamic_title"       => "",
                            "render_type"         => 15,
                            "address"             => "安徽省合肥市",
                            "lat"                 => "117.166457",
                            "lng"                 => "31.846980",
                            "usermsg"             => (object)[],
                            "privatemsg"          => [],
                            "extend_circledetail" => [],
                            "extend_already_live" => 0,
                            "extend_followed"     => 0,
                            "circle_recomed"      => [],
                            "difftime"            => "未知",
                            "song"                => [],
                            "icon"                => $this->imgRe(14),
                        ],
                    ];
                    array_unshift($msglist['data'], $dataUhead);
                } else {
                    $datamark = [
                        "id"           => 0,
                        "uid"          => USERID,
                        "fcmid"        => 0,
                        "is_own"       => 0,
                        "create_time"  => time(),
                        "type"         => 0,
                        "msg_type"     => 0,
                        "is_recommend" => 0,
                        "extend_type"  => 0,
                        "status"       => 1,
                        "difftime"     => '未知',
                        "msgdetail"    => [
                            "id"                  => 0,
                            "uid"                 => 0,
                            "privateid"           => "0",
                            "title"               => [],
                            "content"             => "动态",
                            "picture"             => [],
                            "video"               => "",
                            "voice"               => "",
                            "location"            => "117.166457,31.846994",
                            "create_time"         => time(),
                            "type"                => 2,
                            "extend_type"         => '',
                            "msg_type"            => 3,
                            "is_recommend"        => 1,
                            "status"              => 1,
                            "comment_status"      => 1,
                            "comment_num"         => "0",
                            "like_num"            => "0",
                            "systemtype"          => 0,
                            "systemplus"          => (object)[],
                            "extend_talk"         => "",
                            "extend_circle"       => "",
                            "cover_url"           => "",
                            "dynamic_title"       => "",
                            "render_type"         => 15,
                            "address"             => "安徽省合肥市",
                            "lat"                 => "117.166457",
                            "lng"                 => "31.846980",
                            "usermsg"             => (object)[],
                            "privatemsg"          => [],
                            "extend_circledetail" => [],
                            "extend_already_live" => 0,
                            "extend_followed"     => 0,
                            "circle_recomed"      => [],
                            "difftime"            => "未知",
                            "song"                => [],
                            "icon"                => $this->imgRe(0),
                        ],
                    ];

                }
            }
            return $this->success($msglist, '获取成功');
        }
    }

    /**
     *  主播设置场控搜索用户
     * @return \think\response\Json
     */
    public function user()
    {
        $params    = input();
        $offset    = isset($params['offset']) ? $params['offset'] : 0;
        $length    = isset($params['length']) ? ($params['length'] > 100 ? 100 : $params['length']) : PAGE_LIMIT;
        $userWhere = [
            'offset'   => $offset,
            'length'   => $length,
            'keyword'  => $params['keyword'],
            'self_uid' => USERID
        ];
        $sdk       = new CoreSdk();
        $result    = $sdk->post('user/search', $userWhere);
        if (empty($result)) return $this->success([]);
        $redis = RedisClient::getInstance();
        foreach ($result as &$value) {
            $is_manage          = $redis->sismember('liveManage:' . USERID, $value['user_id']);
            $value['is_manage'] = (int)!empty($is_manage);
            $value['jump']      = getJump('personal', ['user_id' => $value['user_id']]);
        }
        return $this->success($result ? $result : [], '获取成功');
    }

    public function imgRe($render_type)
    {
        //返还对应图标链接 14用户 9,12直播 20小视频 其他都为动态
        if ($render_type == 14) {
            return $this->friendConfigRes['citcle_defaut_user'];
        }
        if ($render_type == 9 || $render_type == 12) {
            return $this->friendConfigRes['citcle_defaut_live'];
        }
        if ($render_type == 20) {
            return $this->friendConfigRes['citcle_defaut_video'];
        }
        return $this->friendConfigRes['citcle_defaut_dynamic'];
    }
}
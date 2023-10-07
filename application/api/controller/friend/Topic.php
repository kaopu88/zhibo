<?php
/**
 * Created by PhpStorm.
 * User: “崔鹏
 * Date: 2020/06/23
 * Time: 上午 9:24
 */

namespace app\api\controller\friend;

use app\admin\service\SysConfig;
use app\api\service\live\Enter;
use app\common\controller\UserController;
use app\friend\service\FriendCircleCircle;
use app\friend\service\FriendCircleCircleFollow;
use app\friend\service\FriendCircleLyric;
use app\friend\service\FriendCircleMessage;
use app\friend\service\FriendCircleMessageLive;
use app\friend\service\FriendCircleTopic;
use app\friend\service\FriendCircleTopicFollow;
use bxkj_common\HttpClient;
use bxkj_common\RedisClient;
use bxkj_module\exception\ApiException;
use bxkj_module\service\Follow;

class Topic extends UserController
{
    protected static $livePrefix = 'BG_LIVE:', $filmPrefix = 'BG_FILM:', $randomPrefix = 'BG_RAND:', $teenFilmPrefix = 'BG_TEEN:';//影片redis前缀

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
        if ($this->friendConfigRes['is_open'] == 0) {
            $errorMsg = '未开启交友功能';
            if (!empty($errorMsg)) {
                throw new ApiException((string)$errorMsg, 1);
            }
        }
    }

    /**
     * 获取话题
     * return  json
     */
    public function getTopic()
    {
        $params   = request()->param();
        $validate = new  \app\api\validate\Topic();
        $result   = $validate->scene('getTopic')->check($params);
        if (true !== $result) {
            return $this->jsonError($validate->getError());
        }
        $topic         = [];
        $friendTopic   = new FriendCircleTopic();
        $redis         = new RedisClient();
        $cacheTopichot = $redis->exists('cache:friend_msg_Topic');
        if (empty($cacheTopichot)) {
            $hotTopic        = $friendTopic->getTopic(['status' => 1, 'is_hot' => 1], '*', 'hot desc', $this->friendConfigRes['hot_topic_num']);
            $NewTopic        = $friendTopic->getTopic(['status' => 1], '*', 'ctime desc', $this->friendConfigRes['new_topic_num']);
            $recommendTopic  = $friendTopic->getTopic(['status' => 1, 'is_recom' => 1], '*', 'ctime desc', $this->friendConfigRes['new_topic_num']);
            $hotTopic1       = [
                'name' => "热门话题",
                'data' => $hotTopic
            ];
            $newTopic1       = [
                'name' => "最新话题",
                'data' => $NewTopic
            ];
            $recommendTopic1 = [
                'name' => "推荐话题",
                'data' => $recommendTopic
            ];
            $topic1          = [
                $hotTopic1,
                $newTopic1,
                $recommendTopic1,
            ];
            $redis->HSET('cache:friend_msg_Topic', 'Topic', json_encode($topic1));
            $redis->expire('cache:friend_msg_Topic', 86400);
            $topic = $redis->HGET('cache:friend_msg_Topic', 'Topic');
        } else {
            $topic = $redis->HGET('cache:friend_msg_Topic', 'Topic');
        }
        return $this->success(json_decode($topic, true), '获取成功');
    }

    /**
     * 添加话题
     * return  json
     */
    public function addTopic()
    {
        $submit = submit_verify('addTopic' . USERID);
        if (!$submit) return $this->jsonError('您提交太频繁了~~~');
        $params   = request()->param();
        $validate = new  \app\api\validate\Topic();
        $result   = $validate->scene('addTopic')->check($params);
        if (true !== $result) {
            return $this->jsonError($validate->getError());
        }
        $params['uid']  = USERID;
        $topic          = new FriendCircleTopic();
        $add            = $topic->add($params);
        $redis          = new RedisClient();
        $cacheTopicList = $redis->exists('cache:friend_msg_Topic_lists');
        if (empty($cacheTopicList)) {
            $allTopic = $topic->getQuery(['status' => 1], "*", 'topic_id');
            foreach ($allTopic as $k => $v) {
                $v['userMsg'] = userMsg($v['uid'], 'user_id,avatar,nickname,gender');
                $redis->HSET('cache:friend_msg_Topic_lists', $v['topic_id'], json_encode($v));
            }
        } else {
            $rest            = $topic->find(['topic_id' => $add]);
            $rest['userMsg'] = userMsg($rest['uid'], 'user_id,avatar,nickname,gender');
            $redis->HSET('cache:friend_msg_Topic_lists', $add, json_encode($rest));
        }
        if ($add == -1) return $this->jsonError('话题名称已经存在');
        return $this->success($rest, '添加成功');
    }

    /**
     * 话题广场(就是说有话题的列表)
     * return  json
     */
    public function TopicList()
    {
        $params      = request()->param();
        $friendTopic = new FriendCircleTopic();
        $page_index  = $params['page_index'] ? $params['page_index'] : 1;
        $page_size   = $params['page_size'] ? $params['page_size'] : 10;
        $redis       = new RedisClient();
        $allTopic    = $friendTopic->getQuery(['status' => 1], "*", 'topic_id desc ');
        $redis->del("cache:friend_msg_Topic_lists");
        foreach ($allTopic as $k => $v) {
            $v['userMsg'] = userMsg($v['uid'], 'user_id,avatar,nickname,gender');
            if (!empty($v['userMsg'])) {
                $avatars = config('upload.reg_avatar');
                $v['userMsg'] = ["user_id"  => $v['uid'],
                                 "avatar"  => $avatars[mt_rand(0, count($avatars) - 1)],
                                 "nickname" => "",
                                 "gender"   => "0"];
            }
            $redis->HSET('cache:friend_msg_Topic_lists', $v['topic_id'], json_encode($v));
        }
        $list = $redis->HGETALL('cache:friend_msg_Topic_lists');
        $msg  = new FriendCircleMessage();
        foreach ($list as $k => $v) {
            $where      = [];
            $where[]    = ['title', 'like', '%' . $k . '%'];
            $msgNumbers = $msg->countTatal($where);
            //判断用户是否关注了这个话题
            $topicFollow = new FriendCircleTopicFollow();
            $isfollow    = $topicFollow->countTotal(['uid' => USERID, 'is_follow' => 1, 'topic_id' => $k]);
            if (!empty($isfollow)) {
                $myfollowed = 1;
            } else {
                $myfollowed = 0;
            }
            $listr[] = [
                "topic_id"   => $k,
                "topic_name" => json_decode($v, true)['topic_name'],
                "userMsg"    => json_decode($v, true)['userMsg'],
                "hot"        => json_decode($v, true)['hot'],
                "dynamic"    => number_format2($msgNumbers),
                "myfollowed" => $myfollowed,
            ];
        }
        $rest = $this->page_array($page_size, $page_index, $listr, 1);
        if (empty($rest['data'])) {
            $rest['data'] = (object)[];
        }
        return $this->success($rest, '查询成功');
    }

    /**
     * 话题查询
     * return  json
     */
    public function queryTopic()
    {
        $params     = request()->param();
        $page_index = $params['page_index'] ? $params['page_index'] : 1;
        $page_size  = $params['page_size'] ? $params['page_size'] : 10;
        $where[]    = ['topic_name', 'like', '%' . $params['keyword'] . '%'];
        $where[]    = ['status', 'eq', 1];
        $friendMsg  = new FriendCircleTopic();
        $findTopic  = $friendMsg->pageQuery($page_index, $page_size, $where, 'topic_id', '*');
        $msg        = new FriendCircleMessage();
        if (!empty($findTopic['data'])) {
            foreach ($findTopic['data'] as $k => $v) {
                $where                            = [];
                $where[]                          = ['title', 'like', '%' . $v['topic_id'] . '%'];
                $msgNumbers                       = $msg->countTatal($where);
                $findTopic['data'][$k]['dynamic'] = number_format2($msgNumbers);
            }
        }
        return $this->success($findTopic, '查询成功');
    }

    /**
     * 崔鹏   2020/06/22
     * 数组分页函数  核心函数  array_slice
     * 用此函数之前要先将数据库里面的所有数据按一定的顺序查询出来存入数组中
     * $count   每页多少条数据
     * $page   当前第几页
     * $array   查询出来的所有数组，要进行分页的数据
     * order   0 不变     1 反序
     */
    function page_array($count, $page, $array, $order)
    {
        global $countpage; #定全局变量
        $page  = (empty($page)) ? '1' : $page; //判断当前页面是否为空 如果为空就表示为第一页面
        $start = ($page - 1) * $count; //计算每次分页的开始位置
        if ($order == 1) {
            if(!empty($array)) $array = array_reverse($array);
        }
        $totals    = count($array);
        $countpage = ceil($totals / $count); #计算总页面数
        $pagedata  = [];
        if(!empty($array)) $pagedata  = array_slice($array, $start, $count);
        $pagedata  = array_slice($array, $start, $count);
        $data      = [
            'total_count' => $totals,
            'page_count'  => $countpage,
            'data'        => $pagedata,
        ];
        return $data;  //返回查询数据
    }

    function pagearray($count, $page, $array, $order)
    {
        global $countpage; #定全局变量
        $page  = (empty($page)) ? '1' : $page; //判断当前页面是否为空 如果为空就表示为第一页面
        $start = ($page - 1) * $count; //计算每次分页的开始位置
        if ($order == 1) {
            if(!empty($array)) $array = array_reverse($array);
        }
        $totals    = count($array);
        $countpage = ceil($totals / $count); #计算总页面数
        $pagedata  = [];
        if(!empty($array)) $pagedata  = array_slice($array, $start, $count);
        $pagedata  = array_slice($array, $start, $count);
        foreach ($pagedata as $k => $v) {
            $redis                                = RedisClient::getInstance();
            $pagedata[$k]['msgdetail']            = $v;
            $v['fcmid']                           = $v['id'];
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
                }
                if ($pagedata[$k]['msgdetail']['render_type'] == 12) {
                    $livemember = $redis->smembers(self::$livePrefix . 'Living');
                    $templive   = $sysplus2Array;
                    if (in_array($templive['uid'], $livemember)) {
                        $Enter = new Enter();
                        $res   = $Enter->verifyClient($templive['id']);
                        if ($res->status == 1) {
                            $templive['islive'] = 0;
                        }
                        $pagedata[$k]['msgdetail']['systemplus'] = $templive;
                    } else {
                        $templive['islive']                      = 0;
                        $pagedata[$k]['msgdetail']['systemplus'] = $templive;
                    }
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
            //查询我是否关注了该用户
            $followModel = new Follow();
            $followInfo  = $followModel->getFollowInfo(USERID, $pagedata[$k]['msgdetail']['uid']);
            if (empty($followInfo['is_follow'])) {
                $pagedata[$k]['msgdetail']['extend_followed'] = 0;
            } else {
                $pagedata[$k]['msgdetail']['extend_followed'] = 1;
            }
            $pagedata[$k]['msgdetail']['difftime']          = time_before($pagedata[$k]['msgdetail']['create_time'], '前');
            $pagedata[$k]['msgdetail']['comment_img_limit'] = $this->friendConfigRes['comment_img_length'];
            $pagedata[$k]['msgdetail']['content']           = emoji_decode($pagedata[$k]['msgdetail']['content']);
            if ($pagedata[$k]['msgdetail']['uid'] == USERID) {
                $pagedata[$k]['ismysender'] = 1;
            } else {
                $pagedata[$k]['ismysender'] = 0;
            }
            $rdata[$k] = [
                'id'           => $pagedata[$k]['id'],
                'uid'          => $pagedata[$k]['uid'],
                'fcmid'        => $pagedata[$k]['id'],
                'is_own'       => $pagedata[$k]['uid'] == USERID ? 1 : 0,
                'create_time'  => $pagedata[$k]['create_time'],
                'type'         => $pagedata[$k]['type'],
                'msg_type'     => $pagedata[$k]['msg_type'],
                'is_recommend' => $pagedata[$k]['is_recommend'],
                'extend_type'  => (int)$pagedata[$k]['extend_type'],
                'status'       => $pagedata[$k]['status'],
                'difftime'     => time_before($pagedata[$k]['create_time'], '前'),
                'msgdetail'    => $pagedata[$k]['msgdetail'],
                'ismysender'   => $pagedata[$k]['ismysender'],
            ];
        }
        $data = [
            'total_count' => $totals,
            'page_count'  => $countpage,
            'data'        => $rdata,
        ];
        return $data;  //返回查询数据
    }

    /**
     * 话题跳转展示列表
     * return  json
     */
    public function tipicList()
    {
        $params      = request()->param();
        $page_index  = $params['page_index'] ? $params['page_index'] : 1;
        $page_size   = $params['page_size'] ? $params['page_size'] : 10;
        $extend_type = $params['extend_type'] ? $params['extend_type'] : 1;
        //先查询可用的话题id号
        $topic = new FriendCircleTopic;
        $count = $topic->countTotal(['topic_id' => $params['topic_id'], 'status' => 1]);
        if (!$count) {
            return $this->jsonError('此话题已经被关闭');
        }
        $msg     = new FriendCircleMessage();
        $where[] = ['title', 'like', '%' . $params['topic_id'] . '%'];
        $where[] = ['status', 'eq', 1];
        //   $where[] = ['type', 'eq', 2];
        $where[] = ['extend_type', 'eq', $extend_type];
        //列表展示
        $list = $msg->getQuery($where, "*", "id desc");
        //过滤
        $rest = $this->pagearray($page_size, $page_index, $list, 0);
        return $this->success($rest, '查询成功');
    }

    /**
     * 关注话题
     * return  json
     */
    public function followTopic()
    {
        $submit = submit_verify('followTopic' . USERID);
        if (!$submit) return $this->jsonError('您提交太频繁了~~~');
        $params   = request()->param();
        $validate = new \app\api\validate\Topic();
        $result   = $validate->scene('followTopic')->check($params);
        if (true !== $result) {
            return $this->jsonError($validate->getError());
        }
        $params['uid'] = USERID;
        $topicFollow   = new FriendCircleTopicFollow();
        $follow        = $topicFollow->follow($params);
        if (!$follow) return $this->jsonError('操作失败');
        $redis = new RedisClient();
        $data  = $redis->get('usertopic_follow:' . USERID);
        return $this->success($data, '关注成功');
    }

    /**
     * 根据id查询话题信息
     * return  json
     */
    public function getTopdetail()
    {
        $params  = request()->param();
        $topicId = $params['topic_id'];
        $redis   = new RedisClient();
        $list    = $redis->HGET('cache:friend_msg_Topic_lists', $topicId);
        if (empty(json_decode($list, true)['userMsg'])) {
            $topicModel      = new FriendCircleTopic();
            $rest            = $topicModel->find(['topic_id' => $topicId, 'status' => 1]);
            $rest['userMsg'] = userMsg($rest['uid'], 'user_id,avatar,nickname,gender');
            $redis->HSET('cache:friend_msg_Topic_lists', $topicId, json_encode($rest));
            $list = $redis->HGET('cache:friend_msg_Topic_lists', $topicId);
        }
        $msg        = new FriendCircleMessage();
        $where      = [];
        $where[]    = ['title', 'like', '%' . $topicId . '%'];
        $msgNumbers = $msg->countTatal($where);
        //判断用户是否关注了这个话题
        $topicFollow = new FriendCircleTopicFollow();
        $isfollow    = $topicFollow->countTotal(['uid' => USERID, 'is_follow' => 1, 'topic_id' => $topicId]);
        if (!empty($isfollow)) {
            $myfollowed = 1;
        } else {
            $myfollowed = 0;
        }
        $listr = [
            "topic_id"   => $topicId,
            "topic_name" => json_decode($list, true)['topic_name'],
            "userMsg"    => json_decode($list, true)['userMsg'],
            "hot"        => json_decode($list, true)['hot'],
            "dynamic"    => number_format2($msgNumbers),
            "myfollowed" => $myfollowed,
        ];
        return $this->success($listr, '获取成功');
    }

    public function changeopen()
    {
        $info = $this->getModel('sys_config')->where(['mark' => 'new_people_task'])->value('value');
        $info  = json_decode($info, true);
        $info['new_people_task_config']['is_open'] = input("is_open");
        $rest = $this->getModel('sys_config')->where(['mark' => 'new_people_task'])->update(["value"=>json_encode($info)]);
        if($rest==1){
            $this->success('切换成功');
        }

    }
}
<?php
/**
 * Created by PhpStorm.
 * User: “崔鹏
 * Date: 2020/06/28
 * Time: 上午 10:13
 */

namespace app\api\controller\friend;

use app\admin\service\SysConfig;
use app\api\service\live\Enter;
use app\common\controller\UserController;
use app\friend\service\FriendCircleCircle;
use app\friend\service\FriendCircleCircleFollow;
use app\friend\service\FriendCircleClassfiy;
use app\friend\service\FriendCircleMessage;
use app\friend\service\FriendCircleMessageLive;
use bxkj_common\HttpClient;
use bxkj_common\RedisClient;
use bxkj_module\exception\ApiException;
use bxkj_module\service\Follow;
use bxkj_recommend\model\Model;

class Circle extends UserController
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
     * 创建圈子(创建后就自动自己关注自己的圈子)
     * @return \think\response\Json
     */
    public function createCircle()
    {
        $submit = submit_verify('createCircle' . USERID);
        if (!$submit) return $this->jsonError('您提交太频繁了~~~');
        $params   = request()->param();
        $validate = new \app\api\validate\Circle();
        $result   = $validate->scene('createCircle')->check($params);
        if (true !== $result) {
            return $this->jsonError($validate->getError());
        }
        $params['uid']                   = USERID;
        $params['circle_cover_img']      = $params['circle_cover_img'] ? $params['circle_cover_img'] : $this->friendConfigRes['citcle_defaut_cover'];
        $params['circle_background_img'] = $params['circle_background_img'] ? $params['circle_background_img'] : $this->friendConfigRes['citcle_defaut_back'];
        $circle                          = new FriendCircleCircle();
        $chekcDayNum                     = $circle->checkDaySend(USERID, $this->friendConfigRes['create_circle_num']);
        if ($chekcDayNum == -1) {
            return $this->jsonError('今日创建的圈子数量超过系统设置');
        }
        $add = $circle->add($params);
        if ($add == -1) return $this->jsonError('圈子名称已经存在');
        return $this->success($add, '发布成功');
    }

    /**我发布的圈子  circle_type =2
     * 所有的圈子 circle_type =1
     * @return \think\response\Json
     */
    public function getCircleMsg()
    {
        $params   = request()->param();
        $validate = new \app\api\validate\Circle();
        $result   = $validate->scene('getCircleMsg')->check($params);
        if (true !== $result) {
            return $this->jsonError($validate->getError());
        }
        $circle     = new FriendCircleCircle();
        $page_index = $params['page_index'] ? $params['page_index'] : 1;
        $page_size  = $params['page_size'] ? $params['page_size'] : 10;
        if ($params['circle_type'] == 1) {
            $condition = ['status' => 1];
        } else {
            $myfollwocircel = new FriendCircleCircleFollow();
            $rest = $myfollwocircel->getQuery(['uid' => USERID], 'circle_id', 'id');
            if (!empty($rest)) {
                $ids = '';
                foreach ($rest as $k => $v) {
                    $ids = $ids . $v['circle_id'] . ',';
                }
                $condition[] = ['circle_id', 'in', $ids];
            }
            $mysend_circle = $circle->getQuery(['uid' => USERID,'status' => 1], 'circle_id', 'circle_id');
            if (!empty($mysend_circle)) {
                $ids_my = '';
                foreach ($rest as $k => $v) {
                    $ids_my = $ids_my . $v['circle_id'] . ',';
                }
                $condition[] = ['circle_id', 'in', $ids_my];
            }
            $condition[] = ['status', '=', 1];
        }
        $order = 'circle_id desc';
        $rest  = $circle->pageQuery($page_index, $page_size, $condition, $order, "*");
        foreach ($rest['data'] as $k => $v) {
            $rest['data'][$k]['follow'] = number_format2($v['follow']);
        }
        return $this->success($rest, '获取成功');
    }

    /**
     * 搜索圈子
     * @return \think\response\Json
     */
    public function searchCircle()
    {
        $params   = request()->param();
        $validate = new \app\api\validate\Circle();
        $result   = $validate->scene('searchCircle')->check($params);
        if (true !== $result) {
            return $this->jsonError($validate->getError());
        }
        $page_index = $params['page_index'] ? $params['page_index'] : 1;
        $page_size  = $params['page_size'] ? $params['page_size'] : 10;
        $circle     = new FriendCircleCircle();
        $where[]    = ['status', 'eq', 1];
        if (!empty($params['key_words'])) {
            $where[] = ['circle_name|uid', 'like', '%' . $params['key_words'] . '%'];
        }
        if ($params['circle_type'] == 2) {
            if (!empty($params['key_words'])) {
                $myfollwocircel = new FriendCircleCircleFollow();
                $rest = $myfollwocircel->getQuery(['uid' => USERID], 'circle_id', 'id');
                if (!empty($rest)) {
                    $ids = '';
                    foreach ($rest as $k => $v) {
                        $ids = $ids . $v['circle_id'] . ',';
                    }
                    $where[] = ['circle_id', 'in', $ids];
                }
            } else {
                $where[] = ['uid', 'eq', USERID];
            }
        }
        $order = 'circle_id desc';
        $rest  = $circle->pageQuery($page_index, $page_size, $where, $order, "*");
        return $this->success($rest, '获取成功');
    }

    /**
     * 我关注的圈子
     * @return \think\response\Json
     */
    public function circleMyFollowed()
    {
        $params   = request()->param();
        $validate = new \app\api\validate\Circle();
        $result   = $validate->scene('circleMyFollowed')->check($params);
        if (true !== $result) {
            return $this->jsonError($validate->getError());
        }
        $page_index   = $params['page_index'] ? $params['page_index'] : 1;
        $page_size    = $params['page_size'] ? $params['page_size'] : 10;
        $circleFollow = new FriendCircleCircleFollow();
        $where[]      = ['uid', 'eq', USERID];
        $where[]      = ['is_follow', 'eq', 1];
        $order        = 'id desc';
        $list         = $circleFollow->pageQuery($page_index, $page_size, $where, $order, "*");
        $circle       = new FriendCircleCircle();
        if ($list['data']) return $this->jsonError('没有发现相关信息');
        foreach ($list['data'] as $k => $v) {
            $list['data'][$k]['detail']           = $circle->find(['circle_id' => $v['circle_id']], 'circle_id');
            $list['data'][$k]['detail']['follow'] = number_format2($list['data'][$k]['detail']['follow']);
            if ($list['data'][$k]['detail']['uid'] == USERID) {
                $list['data'][$k]['detail']['avatar'] = userMsg(USERID, 'avatar')['avatar'];
            }
        }
        return $this->success($list, '获取成功');
    }

    /**
     * 关注圈子
     * @return \think\response\Json
     */
    public function followCircle()
    {
        $submit = submit_verify('followCircle' . USERID);
        if (!$submit) return $this->jsonError('您提交太频繁了~~~');
        $params   = request()->param();
        $validate = new \app\api\validate\Circle();
        $result   = $validate->scene('followCircle')->check($params);
        if (true !== $result) {
            return $this->jsonError($validate->getError());
        }
        $params['uid'] = USERID;
        $circleFollow  = new FriendCircleCircleFollow();
        $follow        = $circleFollow->follow($params);
        if (!$follow) return $this->jsonError('操作失败');
        $redis = new RedisClient();
        $data  = $redis->get('usercircle_follow:' . USERID);
        return $this->success($data, '关注成功');
    }

    /**
     * 圈子成员管理
     * @return \think\response\Json
     */
    public function circleMemeberManager()
    {
        $params   = request()->param();
        $validate = new \app\api\validate\Circle();
        $result   = $validate->scene('circleMemeberManager')->check($params);
        if (true !== $result) {
            return $this->jsonError($validate->getError());
        }
        $circleFollow = new FriendCircleCircleFollow();
        $totalSuper   = $circleFollow->memberManger($params['circle_id'], 1);  //0:普通成员1:超管2：管理员
        foreach ($totalSuper as $k => $v) {
            $totalSuper[$k]['umsg']     = userMsg($v['uid'], 'user_id,avatar,nickname,gender');
            $totalSuper[$k]['difftime'] = time_before($v['ctime'], '前');
        }
        return $this->success($totalSuper, '获取成功');
    }

    /**
     * 圈子成员管理_获取普通成员
     * @return \think\response\Json
     */
    public function getCommonMember()
    {
        $params   = request()->param();
        $validate = new \app\api\validate\Circle();
        $result   = $validate->scene('getCommonMember')->check($params);
        if (true !== $result) {
            return $this->jsonError($validate->getError());
        }
        $page_index   = $params['page_index'] ? $params['page_index'] : 1;
        $page_size    = $params['page_size'] ? $params['page_size'] : 10;
        $circleFollow = new FriendCircleCircleFollow();
        $commonMember = $circleFollow->pageQuery($page_index, $page_size, ['circle_id' => $params['circle_id'], 'status' => 0, 'power' => 0], 'id', '*');
        if (!empty($commonMember['data'])) {
            foreach ($commonMember['data'] as $k => $v) {
                $umsg                             = [];
                $umsg                             = userMsg($v['uid'], 'user_id,avatar,nickname,gender');
                $umsg['difftime']                 = time_before($v['ctime'], '前');
                $commonMember['data'][$k]['umsg'] = $umsg;
            }
        }
        return $this->success($commonMember, '获取成功');
    }

    /**
     * 圈子成员管理_获取禁言人员
     * @return \think\response\Json
     */
    public function getEstoppelMember()
    {
        $params   = request()->param();
        $validate = new \app\api\validate\Circle();
        $result   = $validate->scene('getEstoppelMember')->check($params);
        if (true !== $result) {
            return $this->jsonError($validate->getError());
        }
        $page_index     = $params['page_index'] ? $params['page_index'] : 1;
        $page_size      = $params['page_size'] ? $params['page_size'] : 10;
        $circleFollow   = new FriendCircleCircleFollow();
        $estoppelMember = $circleFollow->pageQuery($page_index, $page_size, ['circle_id' => $params['circle_id'], 'status' => 1], 'id', '*');
        if (!empty($estoppelMember['data'])) {
            foreach ($estoppelMember['data'] as $k => $v) {
                $umsg                               = [];
                $umsg                               = userMsg($v['uid'], 'user_id,avatar,nickname,gender');
                $umsg['difftime']                   = time_before($v['ctime'], '前');
                $estoppelMember['data'][$k]['umsg'] = $umsg;
            }
        }
        return $this->success($estoppelMember, '获取成功');
    }

    /**
     * 圈子_禁言:解除禁言
     * status 1:禁言 0：取消禁言
     * @return \think\response\Json
     */
    public function actEstoppel()
    {
        $params   = request()->param();
        $validate = new \app\api\validate\Circle();
        $result   = $validate->scene('actEstoppel')->check($params);
        if (true !== $result) {
            return $this->jsonError($validate->getError());
        }
        //权限验证
        $circleFollow = new FriendCircleCircleFollow();
        $checkPower   = $circleFollow->checkPower(USERID, $params['circle_id']);
        if (empty($checkPower)) {
            return $this->jsonError('权限不足');
        }
        if ($params['uid'] == USERID) {
            return $this->jsonError('亲，请勿操作自己');
        }
        $changestatus = $circleFollow->setPower($params['uid'], $params['circle_id'], ['status' => $params['status']]);
        if (!$changestatus) return $this->jsonError('更改失败');
        return $this->success($changestatus, '更改成功');
    }

    /**
     * 圈子_设为管理员
     * @return \think\response\Json
     */
    public function actSetAdmin()
    {
        $params   = request()->param();
        $validate = new \app\api\validate\Circle();
        $result   = $validate->scene('actSetAdmin')->check($params);
        if (true !== $result) {
            return $this->jsonError($validate->getError());
        }
        //权限验证
        $circleFollow = new FriendCircleCircleFollow();
        $checkPower   = $circleFollow->checkPower(USERID, $params['circle_id']);
        if ($checkPower[0] != 1) {
            return $this->jsonError('权限不足');
        }
        if ($params['uid'] == USERID) {
            return $this->jsonError('亲，请勿操作自己');
        }
        if ($params['power'] == 1) {
            return $this->jsonError('错误的权限值');
        }
        $changestatus = $circleFollow->setPower($params['uid'], $params['circle_id'], ['power' => $params['power']]);
        if (!$changestatus) return $this->jsonError('更改失败');
        return $this->success($changestatus, '更改成功');
    }

    /**
     * 圈子_驱逐
     * @return \think\response\Json
     */
    public function actsetexpel()
    {
        $params   = request()->param();
        $validate = new \app\api\validate\Circle();
        $result   = $validate->scene('actsetexpel')->check($params);
        if (true !== $result) {
            return $this->jsonError($validate->getError());
        }
        $circleFollow = new FriendCircleCircleFollow();
        $checkPower   = $circleFollow->checkPower(USERID, $params['circle_id']);
        if (empty($checkPower)) {
            return $this->jsonError('权限不足');
        }
        if ($params['uid'] == USERID) {
            return $this->jsonError('亲，请勿操作自己');
        }
        $changestatus = $circleFollow->setPower($params['uid'], $params['circle_id'], ['status' => 2]);
        if (!$changestatus) return $this->jsonError('更改失败');
        return $this->success($changestatus, '更改成功');
    }

    public function getReportClissfiy()
    {
        $classfiy = new FriendCircleClassfiy();
        $rest     = $classfiy->getQuery(['status' => 1, 'masterid' => 4], 'id,child_name', 'id');
        return $this->success($rest, '获取成功');
    }

    /**
     * 圈子_推荐
     * @return \think\response\Json
     */
    public function circleRecomed()
    {
        $params     = request()->param();
        $page_index = $params['page_index'] ? $params['page_index'] : 1;
        $page_size  = $params['page_size'] ? $params['page_size'] : 10;
        $circle     = new FriendCircleCircle();
        $rest       = $circle->pageQuery($page_index, $page_size, ['status' => 1, 'is_recom' => 1, 'dismiss' => 0], 'circle_id desc', '*');
        return $this->success($rest, '获取成功');
    }

    /**
     * 获取圈子详情
     * @return \think\response\Json
     */
    public function detailCircle()
    {
        $params   = request()->param();
        $validate = new \app\api\validate\Circle();
        $result   = $validate->scene('detailCircle')->check($params);
        if (true !== $result) {
            return $this->jsonError($validate->getError());
        }
        $circle                 = new FriendCircleCircle();
        $detailCircle           = $circle->find(['circle_id' => $params['circle_id']]);
        $detailCircle['follow'] = number_format2($detailCircle['follow']);
        if ($detailCircle['dismiss_time'] > 0) {
            $detailCircle['founded'] = floor(($detailCircle['dismiss_time'] - $detailCircle['ctime']) / 86400);
        } else {
            $detailCircle['founded'] = 0;
        }
        $detailCircle['ctime']             = time_format($detailCircle['ctime'], '', 'date');
        $detailCircle['circle_update_day'] = $this->friendConfigRes['circle_update_day']; //隔多长时间能修改
        //获取自己建立了多少条动态;是否为自己创建的圈子;用户是否关注了此圈子
        $circleFollow = new FriendCircleCircleFollow();
        $countFollow  = $circleFollow->countTotal(['uid' => USERID, 'circle_id' => $params['circle_id'], 'is_follow' => 1]);
        if ($countFollow > 0) {
            $detailCircle['myfollow'] = 1;
        } else {
            $detailCircle['myfollow'] = 0;
        }
        $msg = new FriendCircleMessage();
        if ($detailCircle['uid'] == USERID) {
            $detailCircle['ismy'] = 1;
        } else {
            $detailCircle['ismy'] = 0;
        }
        //用户在圈子内的权限
        $circleFollow = new FriendCircleCircleFollow();
        $find         = $circleFollow->find(['circle_id' => $params['circle_id'], 'uid' => USERID], 'id');
        $detailCircle['mycirclepower'] = $find['power'] ? $find['power'] : 0;
        $detailCircle['circilenums'] = $msg->countTatal(['extend_circle' => $params['circle_id']]);
        $detailCircle['userdetail']  = userMsg($detailCircle['uid'], 'user_id,avatar,nickname,gender');
        return $this->success((object)$detailCircle, '获取成功');
    }

    /**
     * 圈子名称修改
     * @return \think\response\Json
     */
    public function saveCircle()
    {
        $params        = request()->param();
        $params['uid'] = USERID;
        $params['id']  = $params['circle_id'];
        $validate      = new \app\api\validate\Circle();
        $result        = $validate->scene('saveCircle')->check($params);
        if (true !== $result) {
            return $this->jsonError($validate->getError());
        }
        $circle       = new FriendCircleCircle();
        $detailCircle = $circle->find(['circle_id' => $params['id'], 'uid' => $params['uid']]);
        if ($detailCircle) {
            $params['status']   = $detailCircle['status'];
            $params['is_recom'] = $detailCircle['is_recom'];
            if (strstr($params['circle_name'], " ")) {
                return $this->jsonError('圈子名称不能有空格');
            }
            $lastTime  = $detailCircle['utime']; //最后修改时间
            $limitTime = $this->friendConfigRes['circle_update_day']; //隔多长时间能修改
            $diff      = ($lastTime + $limitTime * 86400) - time();
            $diff      = $diff < 0 ? 0 : $diff;
            if ($diff > 0) {
                return $this->jsonError(time_str($diff, 'd') . '后才能修改一次圈子信息');
            }
            $result = $circle->backstageedit($params);
            if (!$result) return $this->jsonError('更改失败');
            return $this->success($result, '修改成功');
        } else {
            return $this->jsonError('非法操作');
        }
        return $this->setError('非法操作啦');
    }

    /**
     * 解散圈子
     * @return \think\response\Json
     */
    public function dismissCircle()
    {
        $params   = request()->param();
        $validate = new \app\api\validate\Circle();
        $result   = $validate->scene('detailCircle')->check($params);
        if (true !== $result) {
            return $this->jsonError($validate->getError());
        }
        $circle       = new FriendCircleCircle();
        $detailCircle = $circle->find(['circle_id' => $params['circle_id'], 'uid' => USERID]);
        if ($detailCircle) {
            $result = $circle->changeDismiss($params['circle_id']);
            if (!$result) return $this->jsonError('圈子解散失败');
            return $this->success($result, '圈子解散成功');
        } else {
            return $this->jsonError('非法操作');
        }
    }

    /**
     * 获取包含圈子的所有列表
     * return  json
     */
    function circleList()
    {
        $params      = request()->param();
        $page_index  = $params['page_index'] ? $params['page_index'] : 1;
        $page_size   = $params['page_size'] ? $params['page_size'] : 10;
        $extend_type = $params['extend_type'] ? $params['extend_type'] : 1;
        //先查询可用的话题id号
        $topic = new FriendCircleCircle;
        $count = $topic->countTotal(['circle_id' => $params['circle_id'], 'status' => 1]);
        if (!$count) {
            return $this->jsonError('此圈子已经被关闭');
        }
        $msg     = new FriendCircleMessage();
        $where[] = ['extend_circle', 'like', '%' . $params['circle_id'] . '%'];
        $where[] = ['status', 'eq', 1];
        if ($extend_type > 1) {
            $where[] = ['extend_type', 'eq', $extend_type];
        }

        //列表展示
        $list = $msg->getQuery($where, "*", "id desc");
        $list = filerMsg($list);
        $rest = $this->pagearray($page_size, $page_index, $list, 0);
        if (empty($rest['data'])) {
            $rest['data'] = [];
        }
        return $this->success($rest, '查询成功');
    }

    function pagearray($count, $page, $array, $order)
    {
        global $countpage; #定全局变量
        $page  = (empty($page)) ? '1' : $page; //判断当前页面是否为空 如果为空就表示为第一页面
        $start = ($page - 1) * $count; //计算每次分页的开始位置
        if ($order == 1) {
            $array = array_reverse($array);
        }
        $totals    = count($array);
        $countpage = ceil($totals / $count); #计算总页面数
        $pagedata  = [];
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
            $pagedata[$k]['msgdetail']['content'] = emoji_decode($pagedata[$k]['msgdetail']['content']);
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
                $pagedata[$k]['msgdetail']['picture']      = [];
                $pagedata[$k]['msgdetail']['smallpicture'] = [];
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
            if ($pagedata[$k]['msgdetail']['uid'] == USERID) {
                $pagedata[$k]['ismysender'] = 1;
            } else {
                $pagedata[$k]['ismysender'] = 0;
            }
            $rdata[$k] = [
                'id'           => $pagedata[$k]['id'],
                'uid'          => $pagedata[$k]['uid'],
                'fcmid'        => $pagedata[$k]['id'],
                'isown'        => $pagedata[$k]['uid'] == USERID ? 1 : 0,
                'create_time'  => $pagedata[$k]['create_time'],
                'type'         => $pagedata[$k]['type'],
                'msg_type'     => $pagedata[$k]['msg_type'],
                'is_recommend' => $pagedata[$k]['is_recommend'],
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
}
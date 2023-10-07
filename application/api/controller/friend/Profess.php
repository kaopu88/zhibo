<?php
/**
 * Created by PhpStorm.
 * User: “崔鹏
 * Date: 2020/06/22
 * Time: 下午 4:23
 */

namespace app\api\controller\friend;

use app\admin\service\SysConfig;
use app\api\service\Follow as FollowModel;
use app\common\controller\UserController;
use app\friend\service\FriendCircleClassfiy;
use app\friend\service\FriendCircleConfessionEvaluate;
use app\friend\service\FriendCircleMessage;
use app\friend\service\FriendCircleMessageExpress;
use app\friend\service\FriendCircleMessageFilter;
use app\friend\service\FriendCircleTimelin;
use bxkj_common\RedisClient;
use bxkj_module\exception\ApiException;
use think\Db;

class Profess extends UserController
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
        if ($this->friendConfigRes['is_open'] == 0) {
            $errorMsg = '未开启交友功能';
            if (!empty($errorMsg)) {
                throw new ApiException((string)$errorMsg, 1);
            }
        }
    }

    //获取告白词条分类信息
    public function getProfessClassfy()
    {
        $arr      = [];
        $classfiy = new FriendCircleClassfiy();
        $rest     = $classfiy->getQuery(['isdel' => 0, 'status' => 1, 'masterid' => 3], "*", 'id');
        if (empty($rest)) return $this->jsonError('没有发现相关信息');
        foreach ($rest as $k => $v) {
            $info[] = [
                'name'  => $v['child_name'],
                'value' => $v['id'],
            ];
        }
        if (empty($info)) return [];
        $msgExpressConfig = json_encode($info);
        $rest             = json_decode($msgExpressConfig, true);
        return $this->success($rest, '查询成功');
    }

    //获取告白词条分类信息
    public function getProfessClassfyList()
    {
        $params   = request()->param();
        $validate = new \app\api\validate\Profess();
        $result   = $validate->scene('getProfessClassfyList')->check($params);
        if (true !== $result) {
            return $this->jsonError($validate->getError());
        }
        $page_index = $params['page_index'] ? $params['page_index'] : 1;
        $page_size  = $params['page_size'] ? $params['page_size'] : 10;
        $arr                  = [];
        $fcme                 = new FriendCircleMessageExpress();
        $condition['classid'] = $params['classid'];
        $order                = "id desc";
        $field                = "*";
        $info                 = $fcme->pageQuery($page_index, $page_size, $condition, $order, $field);
        if (empty($info["data"])) {
            $info = [];
        }
        if (!empty($info['data'])) {
            $rest = $info;
            foreach ($rest['data'] as $k => $v) {
                if ($v['from']) {
                    $rest['data'][$k]['fromname'] = $v['from'];
                } else {
                    $rest['data'][$k]['fromname'] = "网络";
                }
            }
        } else {
            return $this->jsonError('没有发现相关词条');
        }
        return $this->success($rest, '查询成功');
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
            $array = array_reverse($array);
        }
        $totals    = count($array);
        $countpage = ceil($totals / $count); #计算总页面数
        $pagedata  = [];
        $pagedata  = array_slice($array, $start, $count);
        $data      = [
            'total_count' => $totals,
            'page_count'  => $countpage,
            'data'        => $pagedata,
        ];
        return $data;  //返回查询数据
    }

    /**
     *  话题首页表白信息
     *
     */
    public function lastProfess()
    {
        $params              = request()->param();
        $type                = $params['type'] ? $params['type'] : 6;
        $is_recommend        = $params['type'] ? $params['type'] : 0;
        $page_index          = $params['page_index'] ? $params['page_index'] : 1;
        $page_size           = $params['page_size'] ? $params['page_size'] : 10;
        $friendCircelTimelin = new FriendCircleTimelin();
        $condition['uid']    = USERID;
        $condition['type']   = $type;
        $condition['status'] = 1;
        $condition1          = "is_own = 1 and type = $type and status = 1";
        if ($is_recommend == 1) {
            $condition['is_recommend'] = 1;
            $condition1                = $condition1 . " and  is_recommend =  1";
        }
        $list            = $friendCircelTimelin->getQueryOr($condition, $condition1, '*', 'id desc');
        $mylist          = $friendCircelTimelin->column(['uid' => USERID], 'fcmid');
        $followModel     = new FollowModel();
        $myFriendsList   = $followModel->mutualArray(USERID);
        $filter          = new FriendCircleMessageFilter();
        $filterUserArray = $filter->filterUserArray(USERID, 6);
        if (empty($list)) return $this->success(['data' => []], '查询成功');
        foreach ($list as $k => $v) {
            //陌生人
            if ($v['msg_type'] == 3) {
                if (in_array($v['uid'], $myFriendsList)) {
                    unset($list[$k]);
                }
            }
            if ($v['is_own'] == 1 && $v['uid'] != USERID && in_array($v['fcmid'], $mylist)) {
                unset($list[$k]);
            }
            //关闭过滤
            if (!empty($filterUserArray)) {
                if (in_array($v['id'], $filterUserArray)) {
                    unset($list[$k]);
                }
            }
        }
        $rest        = $this->pageArray($page_size, $page_index, $list, 0);
        $friendMsg   = new FriendCircleMessage();
        $profressMsg = new FriendCircleConfessionEvaluate();
        if (empty($rest['data'])) return $this->success(['data' => []], '查询成功');
        foreach ($rest['data'] as $k1 => $v1) {
            if ($v1['is_own'] == 1) {
                $sendid = $v1['uid'];
            } else {
                $sendid = $friendMsg->getQuery(['id' => $v1['fcmid']], 'uid', 'id')[0]['uid'];
            }
            $rest['data'][$k1]['alreadysend']   = $friendMsg->countTatal(['uid' => $sendid, 'type' => 6]);
            $rest['data'][$k1]['difftime']      = time_before($v1['create_time'], '前');
            $rest['data'][$k1]['commentNumber'] = $profressMsg->countTotal(['fcmid' => $v1['fcmid']]);
        }
        return $this->success($rest, '查询成功');
    }

    //分页
    function pageArray($count, $page, $array, $order)
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
            if (!empty($redisGet)) {
                $pagedata[$k]['msgdetail'] = json_decode($redisGet, true)[0];
            } else {
                $friendmsg = new FriendCircleMessage();
                $rest      = $friendmsg->getQuery(['id' => $v['fcmid']], '*', 'id');
                if (!empty($rest)) {
                    $redis->set("bx_friend_msg:" . $v['fcmid'], json_encode($rest));
                    $redisGet                  = $redis->get("bx_friend_msg:" . $v['fcmid']);
                    $pagedata[$k]['msgdetail'] = json_decode($redisGet, true)[0];
                }
            }
            $pagedata[$k]['msgdetail']['usermsg']        = userMsg($pagedata[$k]['msgdetail']['uid'], 'user_id,avatar,nickname,gender')?:  userMsg(10000, 'user_id,avatar,nickname,gender');
            $pagedata[$k]['msgdetail']['privatemsg']     = userMsg($pagedata[$k]['msgdetail']['privateid'], 'user_id,avatar,nickname,gender')?:  userMsg(10000, 'user_id,avatar,nickname,gender');
            $pagedata[$k]['msgdetail']['smallcover_url'] = actPicture($pagedata[$k]['msgdetail']['cover_url'], 1);
        }
        $data = [
            'total_count' => $totals,
            'page_count'  => $countpage,
            'data'        => $pagedata,
        ];
        return $data;  //返回查询数据
    }

    /**
     *  告白留言
     *
     */
    public function leaveMsg()
    {
        $submit = submit_verify('LeaveMsg' . USERID);
        if (!$submit) return $this->jsonError('您提交太频繁了~~~');
        $userId   = USERID;
        $params   = request()->param();
        $validate = new \app\api\validate\Profess();
        $result   = $validate->scene('leaveMsg')->check($params);
        if (true !== $result) {
            return $this->jsonError($validate->getError());
        }
        $msg = new FriendCircleMessage();
        $res = $msg->countTatal(['id' => $params['fcmid'], 'type' => 6]);
        if (!$res) {
            return $this->jsonError('告白不存在');
        }
        if ($this->friendConfigRes['msg_commment_evaluate_examine'] == 1) {
            $params['status'] = 0;
        } else {
            $params['status'] = 1;
        }
        $params['uid']   = $userId;

        $confessEvaluate = new FriendCircleConfessionEvaluate();
        $rest            = $confessEvaluate->add($params);
        if (!$rest) return $this->jsonError('操作失败');
        return $this->success($rest, '发布成功');
    }

    /**
     *  告白列表
     *
     */
    public function confessionlist()
    {
        $userId   = USERID;
        $params   = request()->param();
        $validate = new \app\api\validate\Profess();
        $result   = $validate->scene('confessionlist')->check($params);
        if (true !== $result) {
            return $this->jsonError($validate->getError());
        }
        $page_index = $params['page_index'] ? $params['page_index'] : 1;
        $page_size  = $params['page_size'] ? $params['page_size'] : 10;
        $evaluste   = new FriendCircleConfessionEvaluate();
        $rest       = $evaluste->pageQuery($page_index, $page_size, ['fcmid' => $params['fcmid']], 'id desc', '*');
        if (!empty($rest['data'])) {
            foreach ($rest['data'] as $k => $v) {
                $rest['data'][$k]['imgs']       = empty($v['imgs']) ? '' : $v['imgs'];
                $rest['data'][$k]['userdetail'] = userMsg($v['uid'], 'user_id,nickname,avatar');
                if ($v['touid']) {
                    $rest['data'][$k]['touserdetail'] = userMsg($v['touid'], 'user_id,nickname,avatar');
                } else {
                    $rest['data'][$k]['touserdetail'] = (object)[];
                }
                $rest['data'][$k]['content'] =   emoji_decode($v['content']);

                $rest['data'][$k]['timediff'] = time_before($v['create_time'], '前');
            }
        }
        return $this->success($rest, '查询成功');
    }

    public function delConfessionEvaluate()
    {
        $submit = submit_verify('evaluateDel' . USERID);
        if (!$submit) return $this->jsonError('您提交太频繁了~~~');
        $userId   = USERID;
        $params   = request()->param();
        $validate = new \app\api\validate\Evaluate();
        $result   = $validate->scene('delConfessionEvaluate')->check($params);
        if (true !== $result) {
            return $this->jsonError($validate->getError());
        }
        $params['uid']         = $userId;
        $commentEvaluste       = new FriendCircleConfessionEvaluate();
        $commentEvalusteDeatil = $commentEvaluste->find(['id' => $params['evalid']], 'id desc');
        $actArray              = [];
        array_push($actArray, $commentEvalusteDeatil['uid']);
        if (!in_array($userId, $actArray)) {
            return $this->jsonError('您没有操作权限');
        };
        $ids = [];
        array_push($ids, $params['evalid']);
        $commentEvaluste = new FriendCircleConfessionEvaluate();
        $del             = $commentEvaluste->del($ids);
        if (!$del) return $this->jsonError('操作失败');
        return $this->success($del, '删除成功');
    }
}
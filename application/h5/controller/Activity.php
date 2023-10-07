<?php

namespace app\h5\controller;

use app\h5\service\activity\GiftScramble;
use app\h5\service\activity\LoveQixi;
use app\h5\service\activity\LoveRaise;
use app\h5\service\activity\TwistEgg;
use bxkj_common\RedisClient;
use bxkj_module\controller\Web;
use think\Db;
use think\Request;


/**
 * 活动入口
 * Class Activity
 * @package app\h5\controller
 */
class Activity extends Web
{

    /*********** 爱的供养 *************/
    //爱的供养-主页
    public function loveRaise(Request $request)
    {
        $params = $request->param();

        if (empty($params)) return $this->error('访问出错');

        $room = Db::name('live')->field('user_id, avatar')->where('id', $params['room_id'])->find();

        if (empty($room)) return $this->error('访问出错');

        //检查当前主播是否已生成罐子
        $redis = RedisClient::getInstance();

        $key = LoveRaise::$redis_key.LoveRaise::$act_name.LoveRaise::$container;

        $is_exists = $redis->zscore($key, $room['user_id']);

        $LoveRaise = new LoveRaise();

        if (empty($is_exists)) $LoveRaise->generateContainer($room['user_id']);

        if ($params['user_id'] != $room['user_id'] && $LoveRaise->checkRaiseRelation($params['user_id'], $room['user_id']))
        {
            $active_template = LoveRaise::$template[0];

            //获取自已的供养罐数据
            $list = $LoveRaise->getUserPot($params['user_id']);
        }
        else{
            $active_template = LoveRaise::$template[1];

            $list = $LoveRaise->getAnchorPot($room['user_id'], $params['user_id']);
        }

        $active = array_keys(LoveRaise::$template, $active_template);

        $menu = [
            'data' => LoveRaise::$menu,
            'active' => $active[0]+1,
            'active_template' => $active_template,
        ];

        if ($params['user_id'] == $room['user_id']) unset($menu['data'][0]);

        $this->assign('list', $list);

        $this->assign('user_info', [
            'user_id' => $params['user_id'],
            'anchor_id' => $room['user_id'],
            'type' => $params['user_id'] != $room['user_id'] ? 'user' : 'anchor',
        ]);

        $this->assign('menu', $menu);

        $this->assign('anchor', ['avatar' => $room['avatar'], 'uri' => getJump('personal', ['user_id' => $room['user_id']])]);

        $this->view->engine->layout('activity/love_raise/index');

        return $this->fetch('activity/love_raise/pot');
    }


    //爱的供养-tab切换ajax获取数据
    public function getLoveRaiseData(Request $request)
    {
        $params = $request->param();

        if (empty($params['menu_id'])) return json_error('缺少参数');

        $menus = array_column(LoveRaise::$menu, 'id');

        if (!in_array($params['menu_id'], $menus)) return json_error('参数错误');

        $LoveRaise = new LoveRaise();

        switch ($params['menu_id'])
        {
            case 1:
                if (empty($params['user_id'])) return json_error('缺少用户ID');
                $data = $LoveRaise->getUserPot($params['user_id']);
                break;
            case 2:
                if (empty($params['anchor_id'])) return json_error('缺少主播ID');
                $data = $LoveRaise->getAnchorPot($params['anchor_id'], $params['user_id']);
                break;
            case 3:
                $data = $LoveRaise->getSupportRank($params['anchor_id'], $params['user_id']);
                break;
        }

        return json_success(['list' => $data, 'template' => LoveRaise::$template[$params['menu_id']-1]]);
    }


    //爱的供养-规则
    public function loveRaiseRule()
    {
        $act = Db::name('activity')->where('mark', 'love_raise')->find();

        $this->assign('rule', $act);

        $this->view->engine->layout('activity/love_raise/index');

        return $this->fetch('activity/love_raise/rule');
    }


    //爱的供养-真爱榜
    public function loveRaiseLove(Request $request)
    {
        $LoveRaise = new LoveRaise();

        if ($request->post())
        {
            $params = $request->param();
            !isset($params['p']) && $params['p'] = 1;
            $list = $params['type'] == 'user' ? $LoveRaise->getAllRankByUser($params['p']) : $LoveRaise->getAllRankByAnchor($params['p']);

            return json_success($list);
        }

        $list = $LoveRaise->getAllRankByAnchor();

        $this->assign('ranks', $list);

        $this->view->engine->layout('activity/love_raise/index');

        return $this->fetch('activity/love_raise/love');
    }


    //爱的供养-申请列表
    public function replyList(Request $request)
    {
        $params = $request->param();

        if ($request->isAjax())
        {
            if (empty($params['user_id'])) return json_error('缺少参数');

            $LoveRaise = new LoveRaise();

            $methot = ['user' => 'userReplyList', 'anchor' => 'anchorReviewList'];

            $data = call_user_func_array([$LoveRaise, $methot[$params['type']]], [$params['user_id'], $params['p']]);

            return json_success($data);
        }

        $this->assign('user_info', $params);

        return $this->fetch('activity/love_raise/reply_list');
    }


    //爱的供养-用户申请
    public function reply(Request $request)
    {
        $params = $request->param();

        if (empty($params['pot_id']) || empty($params['user_id'])) return json_error('缺少参数');

        $pot_id = $params['pot_id'];

        $user_id = $params['user_id'];

        $LoveRaise = new LoveRaise();

        $rs = $LoveRaise->userReply($pot_id, $user_id);

        if (is_error($rs)) return json_error($rs);

        return json_success([], '申请成功,等待主播处理');
    }


    //爱的供养-用户撤回
    public function recall(Request $request)
    {
        $params = $request->param();

        if (empty($params['pot_id']) || empty($params['user_id'])) return json_error('缺少参数');

        $pot_id = $params['pot_id'];

        $user_id = $params['user_id'];

        $LoveRaise = new LoveRaise();

        $rs = $LoveRaise->userReply($pot_id, $user_id);

        if (is_error($rs)) return json_error($rs);

        return json_success([], '已撤回申请');
    }


    //爱的供养-主播同意
    public function agree(Request $request)
    {
        $params = $request->param();

        if (empty($params['reply_id']) || empty($params['user_id'])) return json_error('缺少参数');

        $reply_id = $params['reply_id'];

        $user_id = $params['user_id'];

        $LoveRaise = new LoveRaise();

        $rs = $LoveRaise->anchorHandle($reply_id, $user_id, 'agree');

        if (is_error($rs)) return json_error($rs->getMessage());

        return json_success(['container_id' => $rs], '处理完成');
    }


    //爱的供养-主播拒绝
    public function refuse(Request $request)
    {
        $params = $request->param();

        if (empty($params['reply_id']) || empty($params['user_id'])) return json_error('缺少参数');

        $reply_id = $params['reply_id'];

        $user_id = $params['user_id'];

        $LoveRaise = new LoveRaise();

        $rs = $LoveRaise->anchorHandle($reply_id, $user_id, 'refuse');

        if (is_error($rs)) return json_error($rs->getMessage());

        return json_success([], '处理完成');
    }


    /*********** 爱在七夕*************/


    public function loveQixi(Request $request)
    {
        $p = $request->param('p', 1);

        $LoveQixi = new LoveQixi();

        $list = $LoveQixi->getRank($p);

        if ($request->isAjax())
        {
            return json_success($list);
        }else{
            $this->assign('rank', json_encode($list));
        }

        $this->view->engine->layout('activity/love_qixi/index');

        return $this->fetch('activity/love_qixi/love');
    }


    public function loveQixiRule()
    {
        return $this->fetch('activity/love_qixi/rule');
    }


}
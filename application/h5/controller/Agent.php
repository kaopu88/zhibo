<?php
/**
 * Created by PhpStorm.
 * User: zack
 * Date: 2020/8/24 0024
 * Time: 下午 2:23
 */

namespace app\h5\controller;

use app\api\validate\Friend;
use bxkj_common\RedisClient;
use think\Db;
use think\Exception;
use think\facade\Request;

class Agent extends LoginController
{
    protected $agentName;

    public function __construct()
    {
        parent::__construct();
        try {
            if ($this->config['agent_apply'] != 1 && \think\facade\Request::action() != 'applycreateagent') throw new Exception('申请未开启~', 1);
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
        $this->agentName = config('app.agent_setting.agent_name') ? config('app.agent_setting.agent_name') : '公会';
    }

    public function index()
    {
        $this->assign('agent_name', $this->agentName);
        return $this->fetch();
    }

    public function get_list()
    {
        $params     = Request::post();
        $page       = $params['page'] ?: 1;
        $length     = empty($params['length']) ? PAGE_LIMIT : $params['length'];
        $keyword    = !empty($params['keyword']) ? $params['keyword'] : '';
        $userId     = $this->data['user']['user_id'];
        $agentModel = new \bxkj_module\service\Agent();
        $res        = $agentModel->getAllList($page, $length, ['keyword' => $keyword, 'user_id' => $userId]);
        return $this->success('获取成功', $res);
    }

    /**
     * 获取单个公会的详情
     */
    public function detail()
    {
        $param   = Request::param();
        $agentId = $param['id'];
        if (empty($agentId)) return $this->error('非法操作', 1);
        $agentModel = new \bxkj_module\service\Agent();
        $oneRes     = $agentModel->getInfo($agentId);
        if (empty($oneRes)) return $this->error( $this->agentName .'不存在', 1);
        if (!empty($oneRes['uid'])) {
            $info = Db::name('user')->field('username')->where(['user_id' => $oneRes['uid']])->find();
        } else {
            $info = $agentModel->getOneDetail($agentId);
        }

        if (empty($info)) return $this->error('该'.  $this->agentName .'暂不可用', 1);
        $userId             = $this->data['user']['user_id'];
        $resPromotionVerify = Db::name('promotion_relation_apply')->where(['user_id' => $userId, 'status' => 0, 'agent_id' => $agentId])->find();
        $applyStatus        = 0;
        if (!empty($resPromotionVerify)) {
            $applyStatus = 1; //申请中
        }
        $promotionRelation = Db::name('promotion_relation')->where(['user_id' => $userId])->find(); //说明已加入公会了
        if (!empty($promotionRelation)) {
            $applyStatus = 2; //已加入
        }

        $this->assign('agent_name', $this->agentName);
        $this->assign('apply_status', $applyStatus);
        $this->assign('oneRes', $oneRes);
        $this->assign('info', $info);
        return $this->fetch();
    }

    /**
     * 获取公会下面的主播
     */
    public function get_anchor_list()
    {
        $param   = Request::post();
        $redis   = new RedisClient();
        $agentId = $param['id'];
        $page    = $param['page'] ?: 1;
        $length  = empty($params['length']) ? PAGE_LIMIT : $params['length'];
        if (empty($agentId)) return $this->error('非法操作', 1);
        $agentModel = new \bxkj_module\service\Agent();
        $oneRes     = $agentModel->getInfo($agentId);
        if (empty($oneRes)) return $this->error( $this->agentName . '不存在', 1);
        $agentModel = new \bxkj_module\service\Anchor();
        $res        = $agentModel->getAllList($agentId, $page, $length);
        if (!empty($res)) {
            foreach ($res as $key => &$value) {
                $level               = Db::name('exp_level')->field('levelname,icon')->where(array('levelid' => $value['level']))->find();
                $live                = Db::name('live')->where(['user_id' => $value['user_id'], 'status' => 1])->field('id,user_id')->find();
                $value['level_icon'] = $level['icon'];
                $value['his_millet'] = $redis->zScore('rank:charm:history', $value['user_id']) ?: 0;
                $value['is_live']    = !empty($live) ? 1 : 0;
            }
        }
        return $this->success('获取成功', $res);
    }

    /**
     * 入会申请
     */
    public function applyAgent()
    {
        $param   = Request::post();
        $agentId = $param['agent_id'];
        $userId  = $this->data['user']['user_id'];
        $anchor  = Db::name('anchor')->where(['user_id' => $userId])->find();

        if (empty($anchor)) return $this->error('你不是主播暂不能申请' .  $this->agentName, 1);
        if (!empty($anchor['agent_id'])) return $this->error('你已经有'.  $this->agentName .'啦', 1);

        $promotionRelation = Db::name('promotion_relation')->where(['user_id' => $userId])->find();
        if (!empty($promotionRelation)) return $this->jsonError("你已经有" .  $this->agentName ."啦!请退出".  $this->agentName ."后申请");

        $agentModel = new \bxkj_module\service\Agent();
        $rest = Db::name('agent')->where(['uid' => $userId])->find();
        if (!empty($rest) && $rest['status'] == '0' && $rest['applystatus'] == 1) return $this->error(1, "您正在申请创建" . $this->agentName);
        if ($rest['status'] == '1') return $this->error(1, "您是" . $this->agentName . "创始人，不可加入其他". $this->agentName);

        $oneRes     = $agentModel->getInfo($agentId);
        if (empty($oneRes)) return $this->error( $this->agentName . '不存在', 1);
        $res = $agentModel->applyAgentAnchor($userId, $agentId);
        if ($res['code'] != 200) return $this->error($res['msg'], 1);
        return $this->success('申请成功');
    }

    /**
     * 用户所在公会情况校验
     */
    public function checkAgent()
    {
        $params = Request::request();
        $userId = $this->data['user']['user_id'];

        $agentModelA = new \bxkj_module\service\Agent();
        $agentApply  = $agentModelA->getAgentApply(['user_id' => $userId, 'status' => 0]);
        if (!empty($agentApply)) {
            return $this->jsonError("您已经申请了加入" . $this->agentName, 1, ['agent_id' => $agentApply['agent_id']]);
        }
        $agentModel = new \app\agent\service\Agent();
        $rest       = $agentModel->find(['uid' => $userId]);
        if ($rest) {
            if ($rest['status'] == 0 && $rest['applystatus'] == 1) {
                return $this->jsonSuccess(1, "您的申请正在审核中");
            } elseif ($rest['status'] == 0 && $rest['applystatus'] == 2) {
                $rest = $rest['handle_desc'] ? $rest['handle_desc'] : '请联系客服';
                return $this->jsonSuccess(2, "你的申请已经被拒绝,拒绝原因：" . $rest);
            } else {

                return $this->jsonSuccess($rest, "获取成功");
            }
        }else{
            $promotionRelation = Db::name('promotion_relation')->where(['user_id' => $userId])->find();
            if (!empty($promotionRelation)) {
                $agent      = $agentModel->find(['id' => $promotionRelation['agent_id']]);
                return $this->jsonError("你已加入 ". $agent['name']." " .  $this->agentName ."啦!", 1, ['agent_id' => $promotionRelation['agent_id']]);
            }
        }
    }

    /**
     * 1.用户主动申请的 如果未审核的情况下  直接退出
     * 2.用户主动申请 已经审核通过 退出 改变状态 由公会管理后台 去审核是否可以退出
     * 3.系统分配到公会的主播进行退出 需要重新生成退出记录
     */
    public function exitagent()
    {
        $userId  = $this->data['user']['user_id'];
        $anchor  = Db::name('anchor')->where(['user_id' => $userId])->find();
        if (empty($anchor)) return $this->jsonError('你不是主播暂不能申请退出' .  $this->agentName, 1);

        $promotionRelation = Db::name('promotion_relation')->where(['user_id' => $userId])->find();
        $resPromotionVerify = Db::name('promotion_relation_apply')->where(['user_id' => $userId])->find();
        if (empty($resPromotionVerify) && empty($promotionRelation['agent_id'])) return $this->jsonError('未知错误1');

        if (!empty($resPromotionVerify) && $resPromotionVerify['status'] == 0) {
            $res = Db::name('promotion_relation_apply')->where(['id' => $resPromotionVerify['id']])->delete();
            if (!$res) return $this->jsonError('退出失败');
            return $this->jsonSuccess([], '退出'. $this->agentName . '成功');
        }

        if ($resPromotionVerify['status'] == 1 || !empty($promotionRelation)) {
            $agentModel = new \bxkj_module\service\Agent();
            $agentId = $resPromotionVerify['agent_id'] ?: $promotionRelation['agent_id'];
            $oneRes     = $agentModel->getInfo($agentId);
            if (empty($oneRes)) return $this->jsonError( $this->agentName . '不存在', 1);
            $res = $agentModel->exitAgent($userId, $agentId);
            if ($res['code'] != 200) return $this->jsonError($res['msg'], 1);
            return $this->jsonSuccess([], '退出'. $this->agentName . '成功，等待'. $this->agentName . '审核');
        }
    }

    public function cancelexitagent()
    {
        $userId  = $this->data['user']['user_id'];
        $agentId = input('agent_id', 0);
        if (empty($agentId)) return $this->jsonError('您还没有加入任何' . $this->agentName);
        $res = Db::name('promotion_exit_apply')->where(['user_id' => $userId, 'agent_id' => $agentId, 'status' => 0])->order('id desc')->find();
        if (empty($res)) return $this->jsonError('您暂时没有申请记录');

        $resdel = Db::name('promotion_exit_apply')->where(['id' => $res['id']])->delete();
        if (!$resdel) return $this->jsonError('撤销失败');
        return $this->jsonSuccess([], '撤销退出'. $this->agentName . '成功');
    }

    /**
     * 申请建立工会
     */
    public function applyCreateAgent()
    {
        $params   = Request::post();
        $userId   = $this->data['user']['user_id'];
        $validate = new \app\api\validate\Agent();
        $result   = $validate->scene('applyCreateAgent')->check($params);
        if (true !== $result) {
            return $this->jsonError($validate->getError());
        }
        $promotionRelation = Db::name('promotion_relation')->where(['user_id' => $userId])->find();
        if (!empty($promotionRelation)) return $this->jsonError("你已经有" .  $this->agentName ."啦!请退出".  $this->agentName ."后申请");

        $agentModelA = new \bxkj_module\service\Agent();
        $agentApply  = $agentModelA->getAgentApply(['user_id' => $userId, 'status' => 0]);
        if (!empty($agentApply)) {
            return $this->jsonError("您已经申请了加入" . $this->agentName);
        }
        $agentModel = new \app\agent\service\Agent();
        $rest       = $agentModel->find(['uid' => $userId]);
        if (!empty($rest)) {
            if ($rest['applystatus'] == 0) {
                return $this->jsonError("您已经申请过工会");
            }
            if ($rest['applystatus'] == 1) {
                return $this->jsonError("您申请工会正在审核中，请不要重复提交");
            }

        }

        $logourl    = url_isactive($params['logo']);
        $idcardPurl = url_isactive($params['img_idcardP']);
        $idcardBurl = url_isactive($params['img_idcardB']);

        if ($logourl['status'] != 200) {
            return $this->jsonError( $this->agentName .'背景图' . $logourl['message']);
        }
        if ($idcardPurl['status'] != 200) {
            return $this->jsonError('身份证正面' . $idcardPurl['message']);
        }
        if ($idcardBurl['status'] != 200) {
            return $this->jsonError('身份证反面' . $idcardBurl['message']);
        }
        $userdetail               = Db::name('user')->where(['user_id' => $userId])->find();
        $data['pid']              = $userdetail['pid'];
        $data['logo']             = $params['logo'];
        $data['subject_type']     = 'personal';
        $data['name']             = $params['name'];
        $data['bus_license']      = 0;
        $data['legal_name']       = $params['legal_name'];
        $data['legal_id']         = $params['legal_id'];
        $data['contact_name']     = $userdetail['username'];
        $data['contact_phone']    = $userdetail['phone'];
        $data['contact_qq']       = 0;
        $data['contact_email']    = '';
        $data['aid']              = 1;
        $data['temppass']         = $params['temppass'] ? $params['temppass'] : '';
        $data['grade']            = 1;
        $data['bus_license']      = $params['bus_license'] ? $params['bus_license'] : '';
        $data['expire_time']      = time() + 3600 * 24 * 30;
        $data['add_sec']          = 0;
        $data['max_sec_num']      = 0;
        $data['add_promoter']     = 1;
        $data['max_promoter_num'] = 500;
        $data['add_anchor']       = 1;
        $data['max_anchor_num']   = 500;
        $data['add_virtual']      = 1;
        $data['max_virtual_num']  = 500;
        $data['cash_type']        = 0;
        $data['cash_proportion']  = 0;
        $data['status']           = 0;
        $data['province_id']      = $userdetail['province_id'];
        $data['city_id']          = $userdetail['city_id'];
        $data['district_id']      = $userdetail['district_id'];
        $data['create_time']      = time();
        $data['uid']              = $this->data['user']['user_id'];
        $data['cash_on']          = 0;
        $data['applystatus']      = 1;
        $data['img_idcardP']      = $params['img_idcardP'];
        $data['img_idcardB']      = $params['img_idcardB'];
        if ($rest['applystatus'] == 2) {
            $data['id'] = $rest['id'];
            $restc      = $agentModel->updataAgetn($data);
        } else {
            $restc = $agentModel->addAgent($data);
        }
        if ($restc) {
            return $this->jsonSuccess($result, '申请成功');
        }
    }
}
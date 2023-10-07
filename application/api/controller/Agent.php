<?php
/**
 * Created by PhpStorm.
 * User: zack
 * Date: 2020/8/20 0020
 * Time: 上午 10:28
 * 暂时不用
 */
namespace app\api\controller;

use app\common\controller\UserController;
use think\Db;
use think\Exception;
use bxkj_module\exception\ApiException;
use think\facade\Request;

class Agent extends UserController
{
    protected $config;

    public function __construct()
    {
        exit();
        parent::__construct();
        $this->config = config('app.live_setting.user_live');
        try {
            if ($this->config['agent_apply'] != 1) throw new Exception('申请未开启~', 1);
        } catch (Exception $e) {
            throw new ApiException((string)$e->getMessage(), 1);
        }
    }

    /**
     * 获取所有公会列表
     */
    public function index()
    {
        $param = Request::post();
        $page = $param['page'] ?: 1;
        $length = empty($params['length']) ? PAGE_LIMIT : $params['length'];
        $agentModel = new \bxkj_module\service\Agent();
        $res = $agentModel->getAllList($page, $length);
        return $this->success($res);
    }

    /**
     * 获取单个公会的详情
     */
    public function getAgentInfo()
    {
        $param = Request::post();
        $agentId = $param['id'];
        if (empty($agentId)) return $this->jsonError('非法操作', 1);
        $agentModel = new \bxkj_module\service\Agent();
        $oneRes = $agentModel->getInfo($agentId);
        if (empty($oneRes)) return $this->jsonError('公会不存在', 1);

        $info = $agentModel->getOneDetail($agentId);
        if (empty($info)) return $this->jsonError('该公会暂不可用', 1);

        return $this->success($oneRes);
    }

    /**
     * 获取公会下面的主播
     */
    public function getAnchorList()
    {
        $param = Request::post();
        $agentId = $param['id'];
        $page = $param['page'] ?: 1;
        $length = empty($params['length']) ? PAGE_LIMIT : $params['length'];

        if (empty($agentId)) return $this->jsonError('非法操作', 1);
        $agentModel = new \bxkj_module\service\Agent();
        $oneRes = $agentModel->getInfo($agentId);
        if (empty($oneRes)) return $this->jsonError('公会不存在', 1);

        $agentModel = new \bxkj_module\service\Anchor();
        $res = $agentModel->getAllList($agentId, $page, $length);
        return $this->success($res);
    }

    /**
     * 入会申请
     */
    public function applyAgent()
    {
        $param = Request::post();
        $userId = $param['user_id'] ?: USERID;
        $anchor= Db::name('anchor')->where(['user_id' => $userId])->find();
        if (empty($anchor)) return $this->jsonError('你不是主播暂不能申请公会', 1);
        if (!empty($anchor['agent_id']))  return $this->jsonError('你已经有公会啦', 1);

    }
}
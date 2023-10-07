<?php

namespace app\api\controller;
use app\common\controller\UserController;
use app\api\service\Feedback as FeedbackModel;
use bxkj_common\KeywordCheck;
use think\Db;

class Feedback extends UserController
{
    //举报内容
    public function reportList()
    {
        $params = request()->param();
        $target = $params['target'];
        return $this->success((new FeedbackModel())->reportList($target), '获取成功');
    }

    //意见反馈
    public function viewBack()
    {
        $params = request()->param();

        $keywordCheck = new KeywordCheck();

        $rs = $keywordCheck->check($params['content']);

        if ($rs) {
            return $this->jsonError('您反馈的内容含敏感词'.implode(',',$rs).',请修改重新提交');
        }

        $feedbackModel = new FeedbackModel();

        $res = $feedbackModel->viewBack($params['content'], $params['cover_url'], $params['contact']);

        if (!$res) return $this->jsonError($feedbackModel->getError());

        return $this->success($res,'非常感谢您的反馈,我们会尽最大的努力持续改进~');
    }

    //投诉
    public function complaint()
    {
        $params = request()->param();

        $id = $params['id'];

        $to_uid = $params['to_uid'];

        $target_id = $params['target_id'];

        $target_type = $params['target_type'];

        $content = $params['content'];

        $feedbackModel = new FeedbackModel();

        $res = $feedbackModel->report($id, $to_uid, $target_id, $target_type, $content);

        if (!$res) return $this->jsonError($feedbackModel->getError());

        return $this->success($res,'举报成功,等待处理');
    }

    //合作
    public function cooperation()
    {
    }
}

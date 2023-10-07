<?php

namespace app\api\service;
use think\Db;
use app\common\service\Service;
use bxkj_common\CoreSdk;
use bxkj_common\RabbitMqChannel;
use bxkj_module\service\Work;

class Feedback extends Service
{
    protected $reportData = [
        'cid' => 0,
        'user_id' => 0,
        'target_id' => 0,
        'content' => '',
        'notice_time' => '',
        'create_time' => '',
        'target_type' => 'film',
        'more' => '',
    ];

    protected static $reportChannel = 'cache:report';


    //用户评论被举报该评论次数加1
    protected function comment($report_id, $item_id, $type, $to_uid)
    {
        Db::name('video_comment')->where('id', $item_id)->setInc('report_count');
        //对接rabbitMQ
        $rabbitChannel = new RabbitMqChannel(['user.credit']);
        $rabbitChannel->exchange('main')->sendOnce('user.credit.complaint', ['user_id' => $to_uid, 'nickname'=>USERID, 'scene'=>'评论']);
    }

    protected function music($report_id, $item_id, $type, $to_uid)
    {
        //对接rabbitMQ
        $rabbitChannel = new RabbitMqChannel(['user.credit']);
        $rabbitChannel->exchange('main')->sendOnce('user.credit.complaint', ['user_id' => $to_uid, 'nickname'=>USERID, 'scene'=>'音乐']);
    }

    protected function film($report_id, $item_id, $type, $to_uid)
    {
        //对接rabbitMQ
        $rabbitChannel = new RabbitMqChannel(['user.credit']);
        $rabbitChannel->exchange('main')->sendOnce('user.credit.complaint', ['user_id' => $to_uid, 'nickname'=>USERID, 'scene'=>'视频']);
    }


    //用户被举报一次信用分减1
    protected function user($report_id, $report_uid, $type, $to_uid)
    {
        $is_manage = Db::name('live_manage')->where("anchor_uid=0 and manage_uid=". USERID)->count();

        $core = new CoreSdk();

        if ($is_manage) {
            $report_uid_info = $core->getUser($report_uid);

            switch ($report_id) {
                case 100:
                    Db::name('user')->where('user_id=' . $report_uid)->update(['nickname' => '已重置']);
                    $redis_update = ['user_id' => $report_uid, 'nickname' => '已重置'];
                    break;

                case 101:
                    Db::name('user')->where('user_id=' . $report_uid)->update(['sign' => '这家伙很懒，什么都没留下']);
                    $redis_update = ['user_id' => $report_uid, 'sign' => '这家伙很懒，什么都没留下'];
                    break;

                case 102:
                    $default_img = config('upload.image_defaults');
                    Db::name('user')->where('user_id=' . $report_uid)->update(['avatar' => $default_img['avatar']]);
                    $redis_update = ['user_id' => $report_uid, 'avatar' => $default_img['avatar']];
                    break;

                default:
                    $redis_update = ['user_id' => $report_uid, '_credit_score' => json_encode(['type' => 'super_exp', 'score' => $report_uid_info['credit_score'] - 30, 'remark' => '超级管理员举报(其它内容)'])];

                    /*$redis_update = ['user_id' => $report_uid, '_credit_score' => json_encode(['type' => 'super_exp', 'score' => $report_uid_info['credit_score'], 'remark' => '超级管理员举报(其它内容)'])];*/
                    break;

            }
        } else {
            //对接rabbitMQ
            $rabbitChannel = new RabbitMqChannel(['user.credit']);
            $rabbitChannel->exchange('main')->sendOnce('user.credit.complaint', ['user_id' => $report_uid, 'nickname'=>USERID, 'scene'=>'主页']);
        }

        $core->post('user/update_redis', $redis_update);
    }


    //获取举报内容列表
    public function reportList($target)
    {
        $key = 'reportlist:' . $target;
        $reportList = cache($key);
        if (empty($reportList)) {
            $reportList = Db::name('complaint_category')->field('id, `name`')->where('status=1 and target="' . $target . '"')->select();
            cache($key, $reportList ? $reportList : []);
        }
        return $reportList;
    }


    //举报处理
    public function report($id, $to_uid, $target_id, $type, $content)
    {
        if (!in_array($type, ['film','comment','user','music'])) {
            return $this->setError('举报对象类型有误');
        }

        $data = array();

        $reportCate = Db::name('complaint_category')->where("status=1 and id={$id}")->find();

        if (empty($reportCate)) return $this->setError('未找到对应的举报内容');

        if ($reportCate['level'] > $reportCate['handle_level']) {
            //通知
            $data['handle_time'] = time();
        }

        if (!$to_uid) {
            if ($type == 'user') {
                $to_uid = $target_id;
            } elseif ($type == 'film') {
                $res = Db::name('video')->where("id={$target_id}")->find();
                $to_uid = $res['user_id'];
            } elseif ($type == 'music') {
                $res = Db::name('music')->where("id={$target_id}")->find();
                $to_uid = $res['user_id'];
            } else {
                $res = Db::name('video_comment')->where("id={$target_id}")->find();
                $to_uid = $res['user_id'];
            }

            if (empty($to_uid)) return $this->setError('未找到对应的举报对象');
        }

        $data['cid'] = $reportCate['id'];

        $data['user_id'] = USERID;

        $data['to_uid'] = $to_uid;

        $data['target_id'] = $target_id;

        $data['target_type'] = $type;

        $data['create_time'] = time();

        $data['content'] = $content;

        $rs = $this->addToComplaint($data);

        if (!$rs) return false;
        if ($rs['id']) {
            $workService = new Work();

            $aid = $workService->allocation('complaint', USERID, $rs['id']);

            $this->updateToComplaint($rs['id'], ['aid' => $aid]);
        }
        if (method_exists($this, $type)) call_user_func_array([$this, $type], [$id, $target_id, $type, $to_uid]);

        return true;
    }


    //意见反馈处理
    public function viewBack($content, $picture, $contact)
    {
        $data = array();

        $data['user_id'] = USERID;

        $data['create_time'] = time();

        $data['content'] = $content;

        $data['cover_url'] = $picture;

        $data['contact'] = $contact;

        $rs = $this->addToViewBack($data);

        if ($rs['id']) {
            $workService = new Work();

            $aid = $workService->allocation('viewback', USERID, $rs['id']);

            $this->updateToViewBack($rs['id'], ['aid' => $aid]);
        }

        return $rs;
    }

    function addToComplaint($inputData){
        $toUid = $inputData['to_uid'];
        if ($toUid) {
            if (empty($toUid)) return $this->setError('请选择用户');
            if ($toUid == $inputData['user_id']) return $this->setError('不能将自己举报');
            $num2 = Db::name('user')->where(array('user_id' => $toUid))->count();
            if ($num2 <= 0) return $this->setError('用户不存在');
        }

        $where['user_id'] = $inputData['user_id'];
        $where['target_id'] = $inputData['target_id'];
        $where['target_type'] = $inputData['target_type'];
        $where['audit_status'] = '0';
        $num = Db::name('complaint')->where($where)->limit(1)->count();
        if ($num <= 0) {
            $result = Db::name('complaint')->insertGetId($inputData);
            $inputData['id'] = $result;
            return $inputData;
        }
        return true;
    }

    function updateToComplaint($rs,$data){
        return Db::name('complaint')->where(array('id'=>$rs))->update($data);
    }

    function addToViewBack($inputData){
        $result = Db::name('viewback')->insertGetId($inputData);
        if (!$result) return $this->setError('加入失败');
        $inputData['id'] = $result;
        return $inputData;
    }

    function updateToViewBack($rs,$data){
        return Db::name('viewback')->where(array('id'=>$rs))->update($data);
    }
}
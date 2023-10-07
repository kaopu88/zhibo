<?php

namespace app\admin\controller;

use app\admin\service\Work;
use app\friend\service\FriendCircleMessage;
use TencentCloud\Common\Credential;
use TencentCloud\Common\Exception\TencentCloudSDKException;
use TencentCloud\Common\Profile\ClientProfile;
use TencentCloud\Common\Profile\HttpProfile;
use TencentCloud\Vod\V20180717\Models\DeleteMediaRequest;
use TencentCloud\Vod\V20180717\Models\DescribeTaskDetailRequest;
use TencentCloud\Vod\V20180717\Models\MediaProcessTaskInput;
use TencentCloud\Vod\V20180717\Models\ProcessMediaRequest;
use TencentCloud\Vod\V20180717\VodClient;
use think\Db;
use think\facade\Request;
use bxkj_common\CoreSdk;
use bxkj_common\RabbitMqChannel;
use bxkj_common\RedisClient;

class Video extends Controller
{
    public function home()
    {
        return $this->fetch();
    }

    public function index()
    {
        $this->checkAuth('admin:film:select');
        $get = input();
        $this->assign('get', $get);
        $videoService = new \app\admin\service\Video();
        $total        = $videoService->getTotal($get);
        $page         = $this->pageshow($total);
        $list         = $videoService->getList($get, $page->firstRow, $page->listRows);
        $this->assign('_list', $list);
        return $this->fetch();
    }

    public function audit_list()
    {
        $this->checkAuth('admin:film:audit');
        $get        = input();
        $get['aid'] = AID;
        if ($get['audit_status'] == '1') {
            Work::read(AID, 'audit_film');
        }
        $videoService = new \app\admin\service\VideoAudit();
        $total        = $videoService->getTotal($get);
        $page         = $this->pageshow($total);
        $list         = $videoService->getList($get, $page->firstRow, $page->listRows);
        // var_dump($list);die;
        $this->assign('_list', $list);
        return $this->fetch();
    }

    public function audit()
    {
        $this->checkAuth('admin:film:audit');
        if (Request::isGet()) {
            $id = input('id');
            if (empty($id)) $this->error('请选择视频');
            $auditItem = $this->getAuditItem($id);
            if (empty($auditItem)) $this->error('小视频不存在');
            return json_success($auditItem, '获取成功');
        } else {
            $auditStatus = input('audit_status');
            $post        = Request::post();
            if (!in_array($auditStatus, ['2', '3', '13'])) $this->error('请选择审核状态');
            $auditService = new \app\admin\service\VideoAudit();
            if ($auditStatus == '2') {
                $act = '通过';
                $num = $auditService->pass($post, AID);
                if (!$num) $this->error($auditService->getError());
            } else if ($auditStatus == '3' || $auditStatus == '13') {
                $act = $auditStatus == '3' ? '驳回' : '驳回并删除';
                $num = $auditService->turnDown($post, AID);
                if (!$num) $this->error($auditService->getError());
            } else {
                $this->error('非法操作');
            }
            $isNext    = input('is_next', '0');
            $auditItem = [];
            if ($isNext == '1') {
                $get  = ['audit_status' => '1', 'aid' => AID];
                $list = $auditService->getList($get, 0, 1);
                if ($list && $list[0]) {
                    $auditItem = $this->getAuditItem($list[0]['id']);
                }
            }
            alog("user.video.edit", '审核视频 ID：' . $post['id'] . ' ' . $act);
            $this->success('审核' . $act . $num . '条记录', [
                'next'    => $auditItem ? $auditItem : [],
                'last_id' => $post['id']
            ]);
        }
    }

    public function audit_update()
    {
        $this->checkAuth('admin:film:audit');
        if (Request::isGet()) {
            $id = input('id');
            if (empty($id)) $this->error('请选择视频');
            $auditItem = $this->getAuditItem($id);
            if (empty($auditItem)) $this->error('小视频不存在');
            return json_success($auditItem, '获取成功');
        } else {
            $post         = Request::post();
            $auditService = new \app\admin\service\VideoAudit();
            $num          = $auditService->audit_update($post);
            if (!$num) $this->error($auditService->getError());
            alog("user.video.edit", '审核视频 ID：' . $post['id']);
            $this->success('操作成功', [
                'next'    => [],
                'last_id' => $post['id']
            ]);
        }
    }

    private function getAuditItem($id)
    {
        $video = Db::name('video_unpublished')->where('id', $id)->find();
        if ($video) {
            $user     = Db::name('user')->where(['user_id' => $video['user_id']])->find();
            $visibles = [
                '0' => '公开视频',
                '1' => '互关可见',
                '2' => '私密视频'
            ];
            $arr      = array();
            if ($video['tags'] && $video['tag_names']) {
                $tags      = explode(',', $video['tags']);
                $tag_names = explode(',', $video['tag_names']);
                foreach ($tags as $key => $value) {
                    $arr[$key] = array('id' => $value, 'name' => $tag_names[$key]);
                }
            }
            return [
                'id'         => $id,
                'describe'   => $video['describe'],
                'score'      => $video['score'],
                'weight'      => $video['weight'],
                'rating'     => round($video['rating'] / 10, 2),
                'copy_right' => $video['copy_right'],
                'tags'       => $video['tags'],
                'tag_names'  => $video['tag_names'],
                'arr'        => json_encode($arr),
                'topic'      => [],
                'tcplayer'   => url('video/tcplayer', ['id' => $id]),
                'source'     => $video['city_name'] . '/' . ($video['source'] == 'user' ? '用户' : '后台') . '上传/' . ($visibles[(string)$video['visible']]),
                'author'     => $user['user_id'] . '/' . $user['nickname'] . '/' . $user['phone'] . '/' . ($user['is_creation'] == '1' ? '创作号' : '非创号')
            ];
        }
        return false;
    }

    public function tcplayer()
    {
        $id   = input('id');
        $type = input('type');
        if ($type == 'film_ad') {
            $info = Db::name('live_film_ad')->field('ad_title title,video_cover cover_url,video_url')->where('id', $id)->find();
        } elseif ($type == 'teenager') {
            $info = Db::name('video_teenager')->where('id', $id)->find();
        } else {
            $info = Db::name('video_unpublished')->field('`describe` title,cover_url,video_url')->where('id', $id)->find();
        }
        if (empty($info)) $this->error('视频不存在');
        $this->assign('film', $info);
        return $this->fetch();
    }

    public function apple_audit_norm()
    {
        return $this->fetch();
    }

    public function detail()
    {
        return $this->fetch();
    }

    public function add()
    {
        return $this->fetch();
    }

    public function addpost()
    {
        $post                  = input();
        $data['describe']      = $post['describe'];
        $data['cover_url']     = $post['cover_url'];
        $data['location_lat']  = $post['location_lat'];
        $data['location_lng']  = $post['location_lng'];
        $data['location_name'] = $post['address'];
        $data['audit_status']  = $post['audit_status'];
        if ($data['audit_status'] == 1) {
            $data['audit_time'] = time();
        }
        $data['status']      = $post['status'];
        $data['rating']      = $post['rating'];
        $data['video_url']   = $post['video_url'];
        $data['aid']         = AID;
        $data['user_id']     = 1;
        $data['create_time'] = time();
        if ($post['area_id']) {
            $area              = explode('-', $post['area_id']);
            $data['city_id']   = $area[1];
            $data['city_name'] = Db::name('region')->where(array('id' => $data['city_id']))->value('name');
        }
        $result = Db::name('film')->insertGetId($data);
        $this->success('上传成功', $result);
    }

    public function batchadd()
    {
        $redis = RedisClient::getInstance();
        $AID   = AID;
        $data  = $redis->get("upload_temp:task:{$AID}");
        $data  = json_decode($data, true);
        if ($data) {
            $this->redirect('batchedit');
            exit;
        }
        return $this->fetch();
    }

    public function change_status()
    {
        $this->checkAuth('admin:film:update');
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择视频');
        $videoService = new \app\admin\service\Video();
        $num          = $videoService->changeStatus($ids, input('status'));
        $msgModel     = new FriendCircleMessage();
        foreach ($ids as $k => $v) {
            $where    = [];
            $where[]  = ['systemplus', 'like', '%"videoID":"' . $v . '%'];
            $findMsgq = $msgModel->getQuery($where, "*", "id desc");
            $findMsg  = $findMsgq[0];
            if ($findMsg['render_type'] == 20) {
                $redis = RedisClient::getInstance();
                $msgModel->changeStatus($findMsg['id'], input('status'));
                $rest = Db::name('friend_circle_message')->where(['id' => $findMsg['id']])->select();
                $redis->set("bx_friend_msg:" . $findMsg['id'], json_encode($rest));
            }
        }
        if (!$num) $this->error($videoService->getError());
        alog("user.video.edit", '编辑视频 ID:' . implode(",", $ids) . ' 修改状态：' . (input('status') == 1 ? "启用" : "禁用"));
        $this->success('切换成功');
    }

    public function del()
    {
        $this->checkAuth('admin:film:delete');
        $videoService = new \app\admin\service\Video();
        $ids          = get_request_ids();
        if (empty($ids)) $this->error('请选择视频');
        $num = $videoService->delete($ids);
        if (!$num) $this->error($videoService->getError());
        alog("user.video.del", '删除视频 ID:' . implode(",", $ids));
        $this->success('删除成功，共计删除' . $num . '条视频');
    }

    public function get_tags()
    {
        $tags = Db::name('video_tags')->field('id value,title name')->order(['create_time' => 'desc'])->select();
        return json_success($tags ? $tags : [], '获取成功');
    }

    public function get_topics()
    {
        $topics = Db::name('topic')->field('id value,title name')->order(['create_time' => 'desc'])->select();
        return json_success($topics ? $topics : [], '获取成功');
    }

    public function getsignature()
    {
        $sdk = new CoreSdk();
        echo $sdk->post('common/get_qcloud_token', []);
    }

    public function upfilm()
    {
        $post      = input();
        $videoname = $post['videoname'];
        if ($videoname) {
            $videoname         = preg_replace('/\.(mp4|avi|rmvb)$/i', '', trim($videoname));
            $videoname         = str_replace(array('抖音', '火山', '微视', '快手', '皮皮虾', '皮皮', '快影'), APP_PREFIX_NAME, $videoname);
            $post['videoname'] = preg_match('/^[a-zA-Z0-9_]{8,}$/', $videoname) ? '' : $videoname;
        }
        redis_lock('upload_temp_' . AID);
        $redis  = RedisClient::getInstance();
        $AID    = AID;
        $data   = $redis->get("upload_temp:task:{$AID}") ? json_decode($redis->get("upload_temp:task:{$AID}"), true) : array();
        $data[] = $post;
        $redis->set("upload_temp:task:{$AID}", json_encode($data));
        redis_unlock('upload_temp_' . AID);
        $this->success('上传成功');
    }

    public function upfilmdel()
    {
        $post = input();
        $AID  = AID;
        redis_lock('upload_temp_' . AID);
        $redis = RedisClient::getInstance();
        $data  = $redis->get("upload_temp:task:{$AID}");
        $data  = json_decode($data, true);
        unset($data[$post['id']]);
        $redis->set("upload_temp:task:{$AID}", json_encode($data));
        redis_unlock('upload_temp_' . AID);
        $this->initiateDeleteMedia($post['videoid']);
        $this->success('删除成功');
    }

    protected function initiateDeleteMedia($FileId)
    {
        $vod_config = config('app.vod');
        if ($vod_config['platform'] != 'tencent') $this->error('不支持的点播平台');
        $qcloud      = $vod_config['platform_config'];
        $cred        = new Credential($qcloud['secret_id'], $qcloud['secret_key']);
        $httpProfile = new HttpProfile();
        $httpProfile->setReqTimeout($qcloud['timeout'] ?: 60);// 请求超时时间，单位为秒(默认60秒)
        $clientProfile = new ClientProfile();
        $clientProfile->setSignMethod("HmacSHA256");  // 指定签名算法(默认为HmacSHA256)
        $clientProfile->setHttpProfile($httpProfile);
        $client      = new VodClient($cred, $qcloud['region'], $clientProfile);
        $req         = new DeleteMediaRequest();
        $req->FileId = $FileId;
        try {
            $resp = $client->DeleteMedia($req);
        } catch (TencentCloudSDKException $exception) {
            $errCode = $exception->getErrorCode();
            return false;
        }
        $RequestId = $resp->RequestId;
        return $RequestId;
    }

    public function batchedit()
    {
        $AID   = AID;
        $redis = RedisClient::getInstance();
        $data  = $redis->get("upload_temp:task:{$AID}");
        $data  = json_decode($data, true);
        if (!$data) {
            $this->redirect('batchadd');
            exit;
        }
        $this->assign('data', $data);
        return $this->fetch();
    }

    public function upfilmbatchdel()
    {
        $AID = AID;
        redis_lock('upload_temp_' . AID);
        $redis = RedisClient::getInstance();
        $data  = $redis->get("upload_temp:task:{$AID}");
        $data  = json_decode($data, true);
        if ($data) {
            foreach ($data as $key => $value) {
                $this->initiateDeleteMedia($value['videoid']);
            }
            $redis->set("upload_temp:task:{$AID}", '');
        }
        redis_unlock('upload_temp_' . AID);
        $this->success('清空成功');
    }

    public function user()
    {
        $this->checkAuth('admin:film_user:select');
        $userService = new \app\admin\service\User();
        $get         = input();
        $redis       = RedisClient::getInstance();
        $uids        = $redis->sUnion('video_user:one', 'video_user:another');
        $get['uids'] = $uids ? $uids : ['-1'];
        $total       = $userService->getTotal($get);
        $page        = $this->pageshow($total);
        $users       = $userService->getList($get, $page->firstRow, $page->listRows);
        $this->assign('_list', $users);
        return $this->fetch();
    }

    public function add_user()
    {
        $this->checkAuth('admin:film_user:add');
        if (Request::isGet()) {
            return json_success('获取成功');
        } else {
            $post = input();
            if ($post['uids'] == '') {
                $this->error('请选择用户');
            }
            $uids  = explode(',', $post['uids']);
            $redis = RedisClient::getInstance();
            if (count($uids) > 0 && is_array($uids)) {
                foreach ($uids as $key => $uid) {
                    if ($uid == '') {
                        continue;
                    }
                    if ($redis->sIsmember('video_user:one', $uid) || $redis->sIsmember('video_user:another', $uid)) {
                        continue;
                    }
                    $redis->sAdd('video_user:one', $uid);
                }
                alog("user.video.add_user", '新增虚拟创作人 UID:' . implode(",", $uids));
                $this->success('设置成功');
            } else {
                $this->error('请选择用户');
            }
        }
    }

    public function cancel()
    {
        $this->checkAuth('admin:film_user:cancel');
        $post    = input();
        $user_id = $post['user_id'];
        $redis   = RedisClient::getInstance();
        $redis->sRem('video_user:one', $user_id);
        $redis->sRem('video_user:another', $user_id);
        alog("user.video.cancel_user", '取消视频用户 UID:' . $user_id);
        $this->success('已取消视频用户');
    }

    public function get_user()
    {
        $redis  = RedisClient::getInstance();
        $status = $redis->get('video_user:status') ? $redis->get('video_user:status') : 1;
        if ($status == 1) {
            $uid = $redis->sPop('video_user:one');
            $redis->sAdd('video_user:another', $uid);
            $data = $redis->sMembers('video_user:one');
            if (count($data) == 0) {
                $redis->set('video_user:status', 2);
            }
            return $uid;
        } elseif ($status == 2) {
            $uid = $redis->sPop('video_user:another');
            $redis->sAdd('video_user:one', $uid);
            $data = $redis->sMembers('video_user:another');
            if (count($data) == 0) {
                $redis->set('video_user:status', 1);
            }
            return $uid;
        } else {
            return '';
        }
    }

    public function upfilmnext()
    {
        $post           = input();
        $publish_status = $post['publish_status'];
        if ($publish_status == 0) {
            if (empty($post['user_id'])) {
                $this->error('请选择发布用户');
            }
        }
        if ($publish_status == 1) {
            $post['user_id'] = $this->get_user();
            if (empty($post['user_id'])) {
                $this->error('请添加自定义视频用户');
            }
        }
        if (empty($post['area_id'])) {
            $this->error('请选择所在城市');
        }
        $data = $post;
        unset($data['key']);
        unset($data['publish_status']);
        if (isset($data['rating'])) {
            $data['rating'] = $data['rating'] * 10;
        }
        $data['audit_status'] = 0;
        $allConfig            = config('upload.image_defaults');
        $data['cover_url']    = !empty($data['cover_url']) ? $data['cover_url'] : $allConfig['film_cover'];
        $data['source']       = 'erp';
        $data['create_time']  = time();
        if ($data['area_id']) {
            $area            = explode('-', $data['area_id']);
            $data['city_id'] = $area[1];
            if ($data['city_id']) {
                $data['region_level'] = 2;
            }
            $data['city_name'] = Db::name('region')->where(array('id' => $data['city_id']))->value('name');
        }
        $data['basic_info'] = '';
        $data['ad_url'] = '';
        $data['short_title'] = '';
        unset($data['area_id']);
        $result = Db::name('video_unpublished')->insertGetId($data);
        $AID    = AID;
        redis_lock('upload_temp_' . AID);
        $redis        = RedisClient::getInstance();
        $session_data = $redis->get("upload_temp:task:{$AID}");
        $session_data = json_decode($session_data, true);
        unset($session_data[$post['key']]);
        $redis->set("upload_temp:task:{$AID}", json_encode($session_data));
        redis_unlock('upload_temp_' . AID);
        $rabbitChannel = new RabbitMqChannel(['video.create_before']);
        $rabbitChannel->exchange('main')->sendOnce('video.create.upload', ['id' => $result]);
        $this->success('上传成功', $result);
    }

    public function findCity()
    {
        $post     = input();
        $city     = Db::name('region')->where(array('name' => $post['city']))->find();
        $province = Db::name('region')->where(array('id' => $city['pid']))->find();
        $this->success('获取成功', array('name' => $province['name'] . '-' . $city['name'], 'id' => $province['id'] . '-' . $city['id'],));
    }

    public function re_review()
    {
        $this->checkAuth('admin:film:audit');
        $id = input('id');
        if (empty($id)) $this->error('请选择短视频');
        $video = Db::name('video_unpublished')->where(['id' => $id, 'audit_status' => '3'])->find();
        if (empty($video)) $this->error('短视频不存在');
        $date             = date('Y-m-d H:i:s');
        $update['reason'] = $video['reason'] . "[{$date}/重新更正为已通过]";
        if ($video['process_status'] != '4') {
            $update['default_audit_status'] = '2';
            $update['audit_status']         = '0';
            $update['ai_review']            = '';
            $update['process_status']       = '1';
            $num                            = Db::name('video_unpublished')->where(['id' => $video['id']])->update($update);
            if (!$num) return $this->error('通过失败');
            $rabbitChannel = new RabbitMqChannel(['video.create_after']);
            $rabbitChannel->exchange('main')->sendOnce('video.create.process', ['id' => $video['id'], 'not_ai_review' => '1']);
        } else {
            $update['audit_status'] = '2';
            $update['audit_time']   = time();
            $update['reason']       = $video['reason'] . "[{$date}/重新更正为已通过]";
            $num                    = Db::name('video_unpublished')->where(['id' => $video['id']])->update($update);
            if (!$num) return $this->error('通过失败');
            //非私密视频直接发布
            if ($video['visible'] != '2') {
                $rabbitChannel = new RabbitMqChannel(['video.create_publish']);
                $rabbitChannel->exchange('main')->sendOnce('video.create.publish', ['id' => $video['id']]);
            }
        }
        alog("user.video.edit", '审核视频 ID:' . $id . " 通过");
        $this->success('重新审核通过');
    }

    public function edit_zan_sum2()
    {
        $id = input('id');
        $auditItem = Db::name('video')->where(['id' => $id])->find();
        if (empty($auditItem))  return $this->error('小视频不存在或未上架');

        if (Request::isGet()) {
            return json_success(['zan_sum2' => $auditItem['zan_sum2'], 'id' => $id], '获取成功');
        } else {
            $num = input('zan_sum2');
            if (!is_numeric($num)) return $this->error('数量错误');
            Db::name('video')->where(['id' => $id])->update(['zan_sum2' => $num]);
            $this->success('操作成功');
        }
    }

    public function edit_play_sum()
    {
        $id = input('id');
        $auditItem = Db::name('video')->where(['id' => $id])->find();
        if (empty($auditItem))  return $this->error('小视频不存在或未上架');

        if (Request::isGet()) {
            return json_success(['play_sum' => $auditItem['play_sum'], 'id' => $id], '获取成功');
        } else {
            $num = input('play_sum');
            if (!is_numeric($num)) return $this->error('数量错误');
            Db::name('video')->where(['id' => $id])->update(['play_sum' => $num]);
            $this->success('操作成功');
        }
    }

    //废弃
    protected function initiateMediaProcessChangeCode($video,$processMedia)
    {
        $qcloudConfig = config('app.vod');
        if ($qcloudConfig['platform'] != 'tencent') return false;
        $qcloud = $qcloudConfig['platform_config'];
        if (empty($qcloud)) return false;
        $cred = new Credential($qcloud['secret_id'], $qcloud['secret_key']);
        $httpProfile = new HttpProfile();
        $httpProfile->setReqTimeout($qcloud['time_out']?:60);// 请求超时时间，单位为秒(默认60秒)
        $clientProfile = new ClientProfile();
        $clientProfile->setSignMethod($qcloud['sign_method']);  // 指定签名算法(默认为HmacSHA256)
        $clientProfile->setHttpProfile($httpProfile);
        $client = new VodClient($cred, $qcloud['region'], $clientProfile);
        $req = new ProcessMediaRequest();
        $req->FileId = $video['video_id'];
        $processTaskInput = new MediaProcessTaskInput();
        //转码处理
        if (!empty($processMedia)) $processTaskInput->TranscodeTaskSet = [["Definition" => $processMedia]];

        $req->MediaProcessTask = $processTaskInput;
        $req->TasksPriority = 0;
        $req->SessionContext = 'CK_VOD_APP';
        $req->SessionId = 'CK:' . RUNTIME_ENVIROMENT . ':' . $video['id'].rand(111111,999999);
        try {
            $resp = $client->ProcessMedia($req);
        } catch (TencentCloudSDKException $exception) {
            $errCode = $exception->getErrorCode();
            return false;
        }

        return true;
    }
}

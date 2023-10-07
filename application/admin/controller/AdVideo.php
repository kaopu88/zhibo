<?php

namespace app\admin\controller;

use app\admin\service\Work;
use TencentCloud\Common\Credential;
use TencentCloud\Common\Exception\TencentCloudSDKException;
use TencentCloud\Common\Profile\ClientProfile;
use TencentCloud\Common\Profile\HttpProfile;
use TencentCloud\Vod\V20180717\Models\DeleteMediaRequest;
use TencentCloud\Vod\V20180717\VodClient;
use think\Db;
use think\facade\Request;
use bxkj_common\CoreSdk;
use bxkj_common\RabbitMqChannel;
use bxkj_common\RedisClient;

class AdVideo extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $config = config('app.vod.audit_config');
        if ($config['isadvideo_status'] != 2) $this->error('短视频广告未开启');
    }

    public function index()
    {
        $this->checkAuth('admin:film:select');
        $get = input();
        $this->assign('get', $get);
        if (empty($get['is_ad'])) $get['is_ad'] = -1;
        $videoService = new \app\admin\service\Video();
        $total = $videoService->getTotal($get);
        $page = $this->pageshow($total);
        $list = $videoService->getList($get, $page->firstRow, $page->listRows);
        $this->assign('_list', $list);
        return $this->fetch();
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
            $post = Request::post();
            $auditService = new \app\admin\service\VideoAudit();
            $num = $auditService->audit_update($post);
            if (!$num) $this->error($auditService->getError());
            alog("video.ad_vodeo.edit", "编辑短视频广告 ID：".$post['id']);
            $this->success('操作成功', [
                'next' => [],
                'last_id' => $post['id']
            ]);
        }
    }

    private function getAuditItem($id)
    {
        $video = Db::name('video_unpublished')->where('id', $id)->find();
        if ($video) {
            $user = Db::name('user')->where(['user_id' => $video['user_id']])->find();
            $visibles = [
                '0' => '公开视频',
                '1' => '互关可见',
                '2' => '私密视频'
            ];
            $arr = array();
            if ($video['tags'] && $video['tag_names']) {
                $tags = explode(',', $video['tags']);
                $tag_names = explode(',', $video['tag_names']);

                foreach ($tags as $key => $value) {
                    $arr[$key] = array('id' => $value, 'name' => $tag_names[$key]);
                }
            }

            return [
                'id' => $id,
                'describe' => $video['describe'],
                'score' => $video['score'],
                'rating' => round($video['rating'] / 10, 2),
                'copy_right' => $video['copy_right'],
                'tags' => $video['tags'],
                'tag_names' => $video['tag_names'],
                'arr' => json_encode($arr),
                'topic' => [],
                'tcplayer' => url('video/tcplayer', ['id' => $id]),
                'source' => $video['city_name'] . '/' . ($video['source'] == 'user' ? '用户' : '后台') . '上传/' . ($visibles[(string)$video['visible']]),
                'author' => $user['user_id'] . '/' . $user['nickname'] . '/' . $user['phone'] . '/' . ($user['is_creation'] == '1' ? '创作号' : '非创号')
            ];
        }
        return false;
    }

    /**
     * 上下架
     */
    public function change_status()
    {
        $this->checkAuth('admin:film:update');
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择视频');
        $videoService = new \app\admin\service\Video();
        $num = $videoService->changeStatus($ids, input('status'));
        if (!$num) $this->error($videoService->getError());
        alog("video.ad_video.edit", "编辑短视频广告 ID：".implode(",", $ids)."<br>修改状态：".(input('status') == 1 ? "启用" : "禁用"));
        $this->success('切换成功');
    }

    /**
     * 视频删除
     */
    public function del()
    {
        $this->checkAuth('admin:film:delete');
        $videoService = new \app\admin\service\Video();
        $ids = get_request_ids();
        if (empty($ids)) $this->error('请选择视频');
        $num = $videoService->delete($ids);
        if (!$num) $this->error($videoService->getError());
        alog("video.ad_video.del", "删除短视频广告 ID：".implode(",", $ids));
        $this->success('删除成功，共计删除' . $num . '条视频');
    }

    /**
     * 上传视频
     * @return mixed
     */
    public function batchadd()
    {
        $redis = RedisClient::getInstance();
        $AID = AID;
        $data = $redis->get("upload_temp_ad:task:{$AID}");
        $data = json_decode($data, true);
        if ($data) {
            $this->redirect('batchedit');
            exit;
        }
        return $this->fetch();
    }

    /**
     * 视频上传完成下一步操作
     * @throws \Exception
     */
    public function upfilm()
    {
        $post = input();
        $videoname = $post['videoname'];
        if ($videoname) {
            $videoname = preg_replace('/\.(mp4|avi|rmvb)$/i', '', trim($videoname));
            $videoname = str_replace(array('抖音', '火山', '微视', '快手', '皮皮虾', '皮皮', '快影'), APP_PREFIX_NAME, $videoname);
            $post['videoname'] = preg_match('/^[a-zA-Z0-9_]{8,}$/', $videoname) ? '' : $videoname;
        }
        redis_lock('upload_temp_ad_' . AID);
        $redis = RedisClient::getInstance();
        $AID = AID;
        $data = $redis->get("upload_temp_ad:task:{$AID}") ? json_decode($redis->get("upload_temp_ad:task:{$AID}"), true) : array();
        $data[] = $post;
        $redis->set("upload_temp_ad:task:{$AID}", json_encode($data));
        redis_unlock('upload_temp_ad_' . AID);
        $this->success('上传成功');
    }

    public function batchedit()
    {
        $AID = AID;
        $redis = RedisClient::getInstance();
        $data = $redis->get("upload_temp_ad:task:{$AID}");
        $data = json_decode($data, true);

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
        redis_lock('upload_temp_ad_' . AID);
        $redis = RedisClient::getInstance();
        $data = $redis->get("upload_temp_ad:task:{$AID}");
        $data = json_decode($data, true);
        if ($data) {
            foreach ($data as $key => $value) {
                $this->initiateDeleteMedia($value['videoid']);
            }
            $redis->set("upload_temp_ad:task:{$AID}", '');
        }
        redis_unlock('upload_temp_ad_' . AID);
        alog("video.ad_video.flush", "清空短视频广告");
        $this->success('清空成功');
    }

    public function upfilmdel()
    {
        $post = input();
        $AID = AID;
        redis_lock('upload_temp_ad_' . AID);
        $redis = RedisClient::getInstance();
        $data = $redis->get("upload_temp_ad:task:{$AID}");
        $data = json_decode($data, true);
        unset($data[$post['id']]);
        $redis->set("upload_temp_ad:task:{$AID}", json_encode($data));
        redis_unlock('upload_temp_ad_' . AID);
        $this->initiateDeleteMedia($post['videoid']);
        alog("video.ad_video.del", "删除短视频广告 ID：".$post['videoid']);
        $this->success('删除成功');
    }

    protected function initiateDeleteMedia($FileId)
    {
        $vod_config = config('app.vod');
        if ($vod_config['platform'] != 'tencent') $this->error('不支持的点播平台');
        $qcloud = $vod_config['platform_config'];
        $cred = new Credential($qcloud['secret_id'], $qcloud['secret_key']);
        $httpProfile = new HttpProfile();
        $httpProfile->setReqTimeout($qcloud['timeout'] ?: 60);// 请求超时时间，单位为秒(默认60秒)
        $clientProfile = new ClientProfile();
        $clientProfile->setSignMethod("HmacSHA256");  // 指定签名算法(默认为HmacSHA256)
        $clientProfile->setHttpProfile($httpProfile);
        $client = new VodClient($cred, $qcloud['region'], $clientProfile);
        $req = new DeleteMediaRequest();
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

    public function upfilmnext()
    {
        $post = input();
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

        if (empty($post['ad_url'])) {
            $this->error('请填写广告链接');
        }

        $data = $post;
        unset($data['key']);
        unset($data['publish_status']);
        if (isset($data['rating'])) {
            $data['rating'] = $data['rating'] * 10;
        }
        $data['audit_status'] = 0;
        $allConfig = config('upload.image_defaults');
        $data['cover_url'] = $allConfig['film_cover'];
        $data['source'] = 'erp';
        $data['create_time'] = time();
        $data['create_time'] = time();
        if ($data['area_id']) {
            $area = explode('-', $data['area_id']);
            $data['city_id'] = $area[1];
            if ($data['city_id']) {
                $data['region_level'] = 2;
            }
            $data['city_name'] = Db::name('region')->where(array('id' => $data['city_id']))->value('name');
        }
        $data['basic_info'] = '';
        $data['short_title'] = '';
        unset($data['area_id']);
        $result = Db::name('video_unpublished')->insertGetId($data);
        $AID = AID;
        redis_lock('upload_temp_ad_' . AID);
        $redis = RedisClient::getInstance();
        $session_data = $redis->get("upload_temp_ad:task:{$AID}");
        $session_data = json_decode($session_data, true);
        unset($session_data[$post['key']]);
        $redis->set("upload_temp_ad:task:{$AID}", json_encode($session_data));
        redis_unlock('upload_temp_ad_' . AID);
        $rabbitChannel = new RabbitMqChannel(['video.create_before']);
        $rabbitChannel->exchange('main')->sendOnce('video.create.upload', ['id' => $result]);

        alog("video.ad_video.add", "新增短视频广告 ID：".$result);
        $this->success('上传成功', $result);
    }

    public function get_user()
    {
        $redis = RedisClient::getInstance();
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
}

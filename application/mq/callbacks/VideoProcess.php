<?php

namespace app\mq\callbacks;

use bxkj_common\RabbitMqChannel;
use bxkj_common\RedisClient;
use bxkj_module\service\Message;
use bxkj_module\service\Region;
use bxkj_module\service\TencentcloudVod;
use bxkj_module\service\UserRedis;
use bxkj_module\service\Video;
use bxkj_module\service\Work;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;
use function PHPSTORM_META\elementType;
use think\Db;

class VideoProcess extends ConsumerCallback
{

    //视频上传后的处理
    public function process(AMQPMessage $msg)
    {
        $data = json_decode($msg->body, true);
        if (!empty($data)) {
            $video = Db::name('video_unpublished')->where(['id' => $data['id']])->find();
            if ($video) {
                $user = Db::name('user')->field('user_id,nickname,is_creation,verified,credit_score,isvirtual,film_status')->where(['user_id' => $video['user_id']])->find();
                if (empty($user)) return $this->failed($msg, true);
                $processStatus = $video['process_status'];
                $res = true;
                if ($processStatus == '1') {
                    $not_ai_review = isset($data['not_ai_review']) ? $data['not_ai_review'] : '0';
                    $res = $this->step1($video, $user, $not_ai_review);
                } else if ($processStatus == '2') {
                    $res = $this->step2($video, $user);
                } else if ($processStatus == '3') {
                    $res = $this->step3($video, $user);
                }
                if (!$res) {
                    $this->log->info('video process error');
                    return $this->failed($msg, true);
                }
            }
        }
        $this->ack($msg);
    }

    protected function step1($video, $user, $notAiReview = '0')
    {
        //1、meta、智能审核、智能标签
        $videoService = new Video();
        $basicData = json_decode($video['basic_info'], true);
        $video = $videoService->asyncProcess($basicData['data'], $video, $user, $notAiReview);
        if (!$video) {
            $this->log->notice('asyncProcess error');
            return false;
        }

        //涉黄涉暴等直接驳回
        if ($video['audit_status'] == '3') return true;

        //2、地理位置处理
        $locRes = $this->location($video);
        if (!$locRes) {
            $this->log->notice('location error');
            return false;
        }

        //3、提交审核
        $subRes = $this->submit($video, $user);
        if (!$subRes) return false;
        return true;
    }

    protected function step2($video, $user)
    {
        if ($video['audit_status'] == '3') return true;
        //2、地理位置处理
        $locRes = $this->location($video);
        if (!$locRes) return false;
        //3、提交审核
        $subRes = $this->submit($video, $user);
        if (!$subRes) return false;
        return true;
    }

    protected function step3($video, $user)
    {
        if ($video['audit_status'] == '3') return true;
        //3、提交审核
        $subRes = $this->submit($video, $user);
        if (!$subRes) return false;
        return true;
    }

    //位置处理
    protected function location($video)
    {
        $update = ['process_status' => '3'];

        $Region = new Region();

        //解析视频发布者位置
        $location = get_position_Lng_lat($video['location_lng'], $video['location_lat']);

        if (!empty($location['regeocode']['addressComponent'])) {
            $position = $location['regeocode']['addressComponent'];

            $provinces = $citys = $districts = ['id' => 0];

            !empty($position['province']) && $provinces = $Region->likeRegion($position['province'], 1);

            !empty($position['city']) && $citys = $Region->likeRegion($position['city'], 2);

            !empty($position['district']) && $districts = $Region->likeRegion($position['district'], 3);

            //归属位置信息
            if (!empty($video['poi_id']) || !empty($video['location_name'])) {
                if ($video['region_level'] == 2) {
                    $where = ['city_id' => $citys['id']];

                    $location_res = Db::name('location')->where($where)->find();

                    if (empty($location_res)) {
                        $last_id = Db::name('location')->insertGetId([
                            'poi_id' => '',
                            'name' => $position['city'],
                            'lng' => $video['location_lng'],
                            'lat' => $video['location_lat'],
                            'level' => $video['region_level'],
                            'publish_num' => 1,
                            'street_address' => '',
                            'city_id' => $citys['id'],
                            'province_id' => $provinces['id'],
                            'district_id' => $districts['id'],
                            'tags' => '',
                            'photos' => '',
                            'create_time' => time(),
                        ]);

                        $update['location_id'] = $last_id;
                    } else {
                        //添加使用量
                        Db::name('location')->where(['id' => $location_res['id']])->setInc('publish_num');

                        $update['location_id'] = $location_res['id'];
                    }
                } else {
                    $where = ['poi_id' => $video['poi_id']];

                    $location_res = Db::name('location')->where($where)->find();

                    if (empty($location_res)) {
                        $key = config('app.map_setting.web_service_key');

                        $url = "http://restapi.amap.com/v3/place/detail?id={$video['poi_id']}&output=json&key={$key}";

                        $Http = new \bxkj_common\HttpClient();

                        $rst = $Http->get($url)->getData('json');

                        if ($rst['status'] == 1) {
                            $location_res = $rst['pois'][0];

                            @list($lng, $lat) = explode(',', $location_res['location']);

                            $photos = !empty($location_res['photos']) ? json_encode(array_column($location_res['photos'], 'url')) : '';

                            $last_id = Db::name('location')->insertGetId([
                                'poi_id' => $location_res['id'],
                                'name' => $location_res['name'],
                                'lng' => $lng,
                                'lat' => $lat,
                                'level' => $video['region_level'],
                                'publish_num' => 1,
                                'street_address' => $location_res['address'],
                                'city_id' => $citys['id'],
                                'province_id' => $provinces['id'],
                                'district_id' => $districts['id'],
                                'tags' => $location_res['type'],
                                'photos' => $photos,
                                'create_time' => time(),
                            ]);

                            $update['location_id'] = $last_id;
                        }
                    } else {
                        //添加使用量
                        Db::name('location')->where(['id' => $location_res['id']])->setInc('publish_num');

                        $update['location_id'] = $location_res['id'];
                    }
                }
            }

            //处理位置信息
            if (empty($video['city_id'])) {
                $update['city_id'] = !empty($citys) ? $citys['id'] : $provinces['id'];
                $update['city_name'] = $position['city'];
            }
        }

        if (empty($update)) return true;

        $num = Db::name('video_unpublished')->where(['id' => $video['id']])->update($update);

        if (!$num) return false;

        return true;
    }


    protected function submit($video, $user)
    {
        //$config = config('app.vod.audit_config');
        $info = Db::name('sys_config')->where(['mark'=>'video'])->value('value');
        $configAll = json_decode($info,true);
        $config = $configAll['vod']['audit_config'];
        bxkj_console($config);
        $auditStatus = $video['default_audit_status'];
        if (!in_array($auditStatus, ['1', '2'])) $auditStatus = '1';
        if ($config['status'] == '2') {
            $auditStatus = '2';
        }
        $freeUserIds = is_array($config['free_user']) ? $config['free_user'] : [];
        $user_id = $video['user_id'];
        /* $user = Db::name('user')->field('user_id,is_creation,verified,credit_score,isvirtual')->where(['user_id' => $user_id])->find();*/
        if ($user['is_creation'] == '1' && $config['creation_status'] == '2') {
            $auditStatus = '2';
        } else if ($user['verified'] == '1' && $config['verified_status'] == '2') {
            $auditStatus = '2';
        } else if ($user['isvirtual'] == '1' && $config['isvirtual_status'] == '2') {
            $auditStatus = '2';
        } else if (isset($config['credit_score']) && $user['credit_score'] >= $config['credit_score']) {
            $auditStatus = '2';
        } else if (in_array($user_id, $freeUserIds)) {
            $auditStatus = '2';
        }
        $update = ['audit_status' => $auditStatus, 'process_status' => '4'];
        if ($auditStatus == '2') {
            $update['audit_time'] = time();//审核通过时间
        } else {
            $update['app_time'] = time();//申请审核时间
            $workService = new Work();
            $aid = $workService->allocation('audit_film', $user_id, $video['id'], 1);
            $update['aid'] = $aid ? $aid : 1;
        }
        $num = Db::name('video_unpublished')->where(['id' => $video['id']])->update($update);
        if (!$num) return false;
        bxkj_console($auditStatus);
        if ($auditStatus == '2')
        {
            $rabbitMq = new RabbitMqChannel(['video.create_publish']);
            $rabbitMq->exchange('main')->sendOnce('video.create.publish', ['id' => $video['id']]);
            if ($video['source'] == 'user') {
                $message = new Message();
                $message->setSender('', 'helper')->setReceiver($user)->sendNotice([
                    'title' => '您发布的视频已经审核通过',
                    'summary' => '点击查看视频',
                    'url' => getJump('video_detail', ['id' => $video['id']])
                ]);
                if (!empty($user['user_id'])) {
                    $fansList = Db::name("follow")->field("user_id")->where(["follow_id" => $user['user_id']])->select();
                    if ($fansList) {
                        foreach ($fansList as $value) {
                            $message->setSender('', 'helper')->setReceiver($value['user_id'])->sendNotice([
                                'type' => 'follow_new',
                                'title' => '您关注的'. $user['nickname'] . '发布了短视频',
                                'summary' => '点击进入',
                                'url' => getJump('video_detail', ['id' => $video['id']])
                            ]);
                        }
                    }
                }
            }
        }
        return true;
    }

}

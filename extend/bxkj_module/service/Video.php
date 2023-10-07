<?php

namespace bxkj_module\service;

use bxkj_common\CoreSdk;
use bxkj_common\RedisClient;
use think\Db;
use bxkj_common\RabbitMqChannel;

class Video extends Service
{
    //异步处理完成
    public function asyncProcess($data, $video = null, $user = null, $notAiReview = '0')
    {
        if (empty($data) || empty($data['fileId'])) return false;
        $fileId = $data['fileId'];
        if (!$video) {
            $video = Db::name('video_unpublished')->where(['video_id' => $fileId])->find();
        }
        if (!$user) {
            $user = Db::name('user')->field('user_id,is_creation')->where(['user_id' => $video['user_id']])->find();
        }
        if (!$video) return false;
        if ($video['process_status'] == '2') return $video;
        $metaData = bxkj_lcfirst($data['metaData']);
        $update = ['id' => $video['id'], 'user_id' => $video['user_id']];
        $metaRes = $this->setMetaData($update, $metaData);
        if (!$metaRes) return false;
        $video = array_merge($video, $update);
        bxkj_console(['meta' => $metaData, 'video' => $video]);
        //检查视频长度是否符合要求
        $duration = $video['duration'];
        $maxDuration = $user['is_creation'] == '1' ? (5 * 60) : 15;
        if ($video['source'] == 'erp') {
            $maxDuration = 300;
        }
        if ($duration > $maxDuration || $duration < 5) {
            $update['audit_status'] = '3';
            $update['reason'] = '您的视频时间长度不符合要求';
            $update['audit_time'] = time();
            return $this->updateVideo($video, $update);
        }

        //智能审核 $user['is_creation'] != '1'
        if ($notAiReview != '1' && $video['audit_status'] != '2' && $video['audit_status'] != '3') {
            $contentReviewList = is_array($data['aiContentReviewResultSet']) ? bxkj_lcfirst($data['aiContentReviewResultSet']) : [];
            $reviewRes = $this->contentReview($update, $contentReviewList);
            if ($reviewRes === false) return false;
            if ($update['audit_status'] == '3') return $this->updateVideo($video, $update);
        }

        //智能分析
        $aiAnalysisList = is_array($data['aiAnalysisResultSet']) ? bxkj_lcfirst($data['aiAnalysisResultSet']) : [];
        $aiAnalysisRes = $this->aiAnalysis($update, $aiAnalysisList);
        if ($aiAnalysisRes === false) return false;

        //任务数据(转码、转动图)
        $processTaskList = is_array($data['mediaProcessResultSet']) ? $data['mediaProcessResultSet'] : [];
        $musicRes = $this->setTaskList($update, $processTaskList, $video);
        if ($musicRes === false) return false;

        $video = array_merge($video, $update);
        if (empty($video['cover_url'])) {
            $update['audit_status'] = '3';
            $update['reason'] = '视频缺少封面图片';
            $update['audit_time'] = time();
            return $this->updateVideo($video, $update);
        }
        return $this->updateVideo($video, $update);
    }

    protected function updateVideo($video, $update)
    {
        if (empty($update)) return $video;
        $update['process_status'] = '2';//标记为已处理
        $num = Db::name('video_unpublished')->where(['id' => $video['id']])->update($update);
        if (!$num) return false;
        if (!empty($update['tags'])) {
            $tagIds = explode(',', $update['tags']);
            Db::name('video_tags')->whereIn('id', $tagIds)->setInc('film_num', 1);
            foreach ($tagIds as $tagId) {
                try {
                    Db::name('video_tags_relation')->insertGetId([
                        'video_id' => $video['id'],
                        'tag_id' => $tagId,
                        'create_time' => time()
                    ]);
                } catch (\Exception $exception) {
                }
            }
        }
        $video = array_merge($video, $update);
        return $video;
    }

    //meta信息
    protected function setMetaData(&$update, $metaData)
    {
        if (!empty($metaData)) {
            $update['file_size'] = $metaData['size'];
            $update['duration'] = (int)$metaData['videoDuration'];
            $update['width'] = $metaData['width'];
            $update['height'] = $metaData['height'];
        }
        return true;
    }

    //内容审核
    protected function contentReview(&$update, $contentReviewList)
    {
        $taskTypeArr = [
            'Porn' => [
                'suggestion' => 'pass,review',//pass、review、block 三个建议级别
                'title' => '淫秽色情',
                'labels' => [
                    'porn' => '色情',
                    'sexy' => ['title' => '性感', 'suggestion' => 'pass,review,block'],
                    'vulgar' => ['title' => '低俗', 'suggestion' => 'pass,review,block'],
                    'intimacy' => ['title' => '亲密行为', 'suggestion' => 'pass,review,block']
                ]
            ],
            'Terrorism' => [
                'suggestion' => 'pass,review,block',
                'title' => '暴力恐怖',
                'labels' => [
                    'guns' => '武器枪支',
                    'crowd' => '人群聚集',
                    'police' => '警察部队',
                    'bloody' => '血腥画面',
                    'banners' => '暴恐旗帜',
                    'militant' => '武装分子',
                    'explosion' => '爆炸火灾',
                    'terrorists' => '暴恐人物'
                ]
            ],
            'Political' => [
                'suggestion' => 'pass,review',
                'title' => '政治敏感',
                'labels' => [
                    'politician' => '政治人物',
                    'violation_photo' => ['title' => '违规图标', 'suggestion' => 'pass,review,block'],
                ]
            ],
            'Porn.Asr' => [
                'suggestion' => 'pass,review',
                'title' => '淫秽色情'
            ],
            'Porn.Ocr' => [
                'suggestion' => 'pass',
                'title' => '淫秽色情'
            ],
            'Political.Asr' => [
                'suggestion' => 'pass',
                'title' => '政治敏感'
            ],
            'Political.Ocr' => [
                'suggestion' => 'pass',
                'title' => '政治敏感'
            ],
            'Default' => [
                'suggestion' => 'pass,review',
                'title' => '不良信息'
            ]
        ];
        $pass = true;
        $arr = [];
        $ai_review = '';
        foreach ($contentReviewList as $task) {
            $taskType = $task['taskType'];
            $output = $task['output'];
            $status = $task['status'];
            if ($status != 'SUCCESS') continue;
            $taskTmp = $taskTypeArr[$taskType] ? $taskTypeArr[$taskType] : $taskTypeArr['Default'];
            $labels = $taskTmp['labels'] ? $taskTmp['labels'] : [];
            $suggestionArr = str_to_fields($taskTmp['suggestion']);
            $label = $output['label'] ? $output['label'] : '';
            $labelConf = ['suggestion' => $suggestionArr, 'title' => ''];
            if (!empty($labels[$label])) {
                if (is_array($labels[$label])) {
                    $labelConf = $labels[$label];
                    $labelConf['suggestion'] = str_to_fields($labelConf['suggestion']);
                } else if (is_string($labels[$label])) {
                    $labelConf['title'] = $labels[$label];
                }
            }
            if (!in_array($output['suggestion'], $labelConf['suggestion'])) {
                $arr[] = $taskTmp['title'] . '-' . $labelConf['title'];
                $ai_review .= $taskType . '-' . $label . ',';
                $pass = false;
            }
        }
        if (!$pass) {
            $update['audit_status'] = '3';
            $update['reason'] = '您的视频或文字描述可能涉及如下违规内容：' . implode(';', $arr);
            $update['audit_time'] = time();
            $redis = RedisClient::getInstance();
            $key = "video_violation:{$user['user_id']}";
            $violationNum = $redis->get($key);
            //超出5次违规则禁止上传视频
            if ($violationNum > 5) {
                $num = Db::name('user')->where(['user_id' => $user['user_id']])->update(['film_status' => '0']);
                if ($num) UserRedis::updateData($user['user_id'], ['film_status' => '0']);
            }
            $redis->incrBy($key, 1);
            $redis->expire($key, 3 * 86400);
            //对接rabbitMQ
            $rabbitChannel = new RabbitMqChannel(['user.credit']);
            $rabbitChannel->exchange('main')->sendOnce('user.credit.video_turndown_sev', ['user_id' => $update['user_id'], 'video_id' => $update['id'], 'reason' => implode(';', $arr)]);
        }
        $update['ai_review'] = rtrim($ai_review, ',');
        return true;
    }

    //智能分析
    protected function aiAnalysis(&$update, $aiAnalysisList)
    {
        $tags = [];
        $tagNames = [];
        $aiCoverUrl = '';
        foreach ($aiAnalysisList as $task) {
            $taskType = $task['taskType'];
            if ($taskType == 'Tag') {
                $tagRes = $this->getTagIds($task['output']['tags']);
                if ($tagRes && $tagRes['ids']) {
                    $tags = $tagRes['ids'];
                    $tagNames = $tagRes['names'];
                }
            } else if ($taskType == 'Cover') {
                $aiCoverUrl = $this->getAiCover($task['output']['covers']);
            }
        }
        $update['ai_cover'] = $aiCoverUrl ? $aiCoverUrl : '';
        $update['tags'] = $tags ? implode(',', $tags) : '';
        $update['tag_names'] = $tagNames ? implode(',', $tagNames) : '';
        return true;
    }

    protected function getTagIds($tags)
    {
        $ids = [];
        $names = [];
        if (empty($tags)) return ['names' => $names, 'ids' => $ids];
        $confidenceThreshold = 0;//分数阈值 0-100 100为最精确
        $arr = [];
        foreach ($tags as $tag) {
            $name = str_replace(',', '、', $tag['tag']);
            $confidence = $tag['confidence'];
            if ($confidence >= $confidenceThreshold && count($arr) < 5) {
                $arr[] = $name;
            }
        }
        $list = [];
        if ($arr) {
            $list = Db::name('video_tags')->field('id,name')->whereIn('name', $arr)->select();
            $list = $list ? $list : [];
        }
        $pid = null;

        foreach ($arr as $value) {
            $item = self::getItemByList($value, $list, 'name');
            if (empty($item)) {
                if (!isset($pid)) {
                    $parent = Db::name('video_tags')->where(['name' => '未分类标签'])->find();
                    $pid = $parent['id'];
                }
                $item = ['name' => $value, 'pid' => $pid, 'create_time' => time()];
                $tmpId = Db::name('video_tags')->insertGetId($item);
                if (!$tmpId) continue;
                $item['id'] = $tmpId;
            }
            if ($item) {
                $ids[] = $item['id'];
                $names[] = $item['name'];
            }
        }
        return ['names' => $names, 'ids' => $ids];
    }

    protected function getAiCover($covers)
    {
        $confidenceThreshold = 0;//分数阈值 0-100 100为最精确
        if (empty($covers)) return '';
        foreach ($covers as $cover) {
            $confidence = $cover['confidence'];
            $coverUrl = $cover['coverUrl'];
            if ($confidence >= $confidenceThreshold) {
                return $coverUrl;
            }
        }
        return '';
    }

    //更新视频任务信息
    protected function setTaskList(&$update, $taskData, $video)
    {
        $taskData = $taskData ? $taskData : [];

        foreach ($taskData as $key => $value)
        {
            if ($value['Type'] == 'Transcode')
            {

                if (empty($video['music_id']) && !empty($value['TranscodeTask']) && $value['TranscodeTask']['Input']['Definition'] == 1010)
                {
                    $data = bxkj_lcfirst($value['TranscodeTask']['Output']);
                    $rs = $this->addMusic($update, $data, $video['user_id']);
                    if ($rs === false) return false;
                }
                if (!empty($value['TranscodeTask']) && $value['TranscodeTask']['Input']['Definition']!=1010) {
                    $update['video_url'] = $value['TranscodeTask']['Output']['Url'];
                }
            }
            else if ($value['Type'] == 'AnimatedGraphics') {
                $update['animate_url'] = $value['AnimatedGraphicTask']['Output']['Url'];
            }
            else if ($value['Type'] == 'CoverBySnapshot' && empty($update['cover_url'])) {
                $update['cover_url'] = $value['CoverBySnapshotTask']['Output']['CoverUrl'];
            }
        }
        return true;
    }


    //新增音乐
    protected function addMusic(&$update, $data, $user_id)
    {
        if (empty($data['url'])) return true;

        $coreSdk = new CoreSdk();

        $user_info = $coreSdk->getUser($user_id);

        //加入独立音乐库中
        $music_id = Db::name('music')->insertGetId([
            'title' => $user_info['nickname'] . '创作的原声',//某某原创,
            'user_id' => $user_id,
            'singer' => $user_info['nickname'],
            'image' => $user_info['avatar'],
            'link' => $data['url'],
            'use_num' => 1,
            'create_time' => time(),
            'release_time' => time(),
            'is_original' => 1,
            'ext' => $data['container'],
            'bitrate' => $data['bitrate'],
            'size' => $data['size'],
            'duration' => round($data['duration'],3),
            'category_id' => 103,//原创类
        ]);
        if (empty($music_id)) return true;

        $update['music_id'] = $music_id;

        return true;
    }
}
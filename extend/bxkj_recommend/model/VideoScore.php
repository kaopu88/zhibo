<?php

namespace bxkj_recommend\model;

use bxkj_module\service\Service;
use bxkj_recommend\Calc;
use bxkj_recommend\exception\Exception;
use bxkj_recommend\ProConf;
use bxkj_recommend\VideoUpdater;
use think\Db;

class VideoScore extends Model
{
    protected $video;
    protected $proportion = [];
    protected $fullScore = 0;

    public function __construct(Video $video)
    {
        parent::__construct();
        $this->proportion = ProConf::get('video_score_proportion');
        $this->fullScore = ProConf::get('video_full_score');
        $this->video = $video;
        foreach ($this->proportion as $range => $arr) {
            $totalPro = 0;
            foreach ($arr as $key => $conf) {
                $pro = $conf['pro'];
                if (bccomp($pro, 0, 5) === 1) $totalPro = bcadd($totalPro, $pro, 4);
            }
            if (bccomp($totalPro, 1, 5) !== 0) {
                throw  new Exception('proportion error');
            }
        }
    }

    //评估视频总分
    public function evaluate()
    {
        $total = 0;
        $videoUpdater = new VideoUpdater();
        $updaterConf = $videoUpdater->getFreConfigByVideo($this->video);
        if ($updaterConf['type'] == 'his') return $total;
        $now = time();
        $timeline = $now - $this->video->create_time;
        foreach ($this->proportion as $range => $arr) {
            if (Calc::validateRange($timeline, $range)) {
                foreach ($arr as $key => $tmpArr) {
                    $pro = $tmpArr['pro'];
                    $funName = 'get' . parse_name($key, 1, true) . 'Score';
                    $score = call_user_func_array([$this, $funName], [$tmpArr]);
                    $total += round($score * $pro);
                }
                break;
            }
        }
        //前端用户上传的额外加分
        if ($this->video->source == 'user') {
            $total += 2000;
            $total = $total > $this->fullScore ? $this->fullScore : $total;
        }
        return $total;
    }

    //获取发布时间分数 100000
    protected function getTimeScore($proportion = [])
    {
        $this->checkParameter($proportion, 'intervals,full');
        $intervals = $proportion['intervals'];
        $now = time();
        $create_time = $this->video->create_time;
        $len = $now - $create_time;
        return Calc::intervalCalc($len, $proportion['full'], $intervals);
    }

    //用户权重
    protected function getUserWeightScore($proportion = [])
    {
        $this->checkParameter($proportion, 'full');
        $user = $this->video->getUser();
        $userWeight = $user->getWeight();
        return round($userWeight * $proportion['full']);
    }

    //标签权重
    protected function getTagWeightScore($proportion = [])
    {
        $this->checkParameter($proportion, 'full');
        $tagWeight = 0;
        $tags = $this->video->getTags();
        foreach ($tags as $tag) {
            if (false) $tag = new VideoTag();
            $tagWeightTmp = $tag->getWeight();
            $tagWeight = max($tagWeight, $tagWeightTmp);
        }
        return round($tagWeight * $proportion['full']);
    }


    protected function getLikeRatioScore($proportion = [])
    {
        $this->checkParameter($proportion, 'thr,coe,full');
        $likeNum = $this->video->sco_zan_sum;
        $ratio = round($likeNum / $this->getWatchCoeNum($proportion['coe']), 6);
        $ratio = $this->thresholdRatio($ratio, $likeNum, $proportion['thr']);
        return round($ratio * $proportion['full']);
    }

    protected function getShareRatioScore($proportion = [])
    {
        $this->checkParameter($proportion, 'thr,coe,full');
        $shareNum = $this->video->sco_share_sum;
        $ratio = round($shareNum / $this->getWatchCoeNum($proportion['coe']), 6);
        $ratio = $this->thresholdRatio($ratio, $shareNum, $proportion['thr']);
        return round($ratio * $proportion['full']);
    }

    protected function getDownloadRatioScore($proportion = [])
    {
        $this->checkParameter($proportion, 'thr,coe,full');
        $downNum = $this->video->sco_down_sum;
        $ratio = round($downNum / $this->getWatchCoeNum($proportion['coe']), 6);
        $ratio = $this->thresholdRatio($ratio, $downNum, $proportion['thr']);
        return round($ratio * $proportion['full']);
    }

    //获取观看分数 100000
    protected function getWatchRatioScore($proportion = [])
    {
        $this->checkParameter($proportion, 'thr,coe,full');
        $watch_duration = $this->video->watch_duration;
        $duration = $this->video->duration > 0 ? $this->video->duration : 15;
        $ratio = round($watch_duration / $this->getWatchCoeNum($proportion['coe'], $duration), 6);
        $ratio = $this->thresholdRatio($ratio, $watch_duration, $proportion['thr']);
        return round($ratio * $proportion['full']);
    }

    //完播率
    protected function getPlayedOutRatioScore($proportion = [])
    {
        $this->checkParameter($proportion, 'thr,coe,full');
        $played_out_sum = $this->video->played_out_sum;
        $ratio = round($played_out_sum / $this->getWatchCoeNum($proportion['coe']), 6);
        $ratio = $this->thresholdRatio($ratio, $played_out_sum, $proportion['thr']);
        return round($ratio * $proportion['full']);
    }

    //切换率
    protected function getSwitchRatioScore($proportion = [])
    {
        $this->checkParameter($proportion, 'thr,coe,full');
        $switch_sum = $this->video->switch_sum;
        $ratio = round($switch_sum / $this->getWatchCoeNum($proportion['coe']), 6);
        $ratio = $this->thresholdRatio($ratio, $switch_sum, $proportion['thr']);
        return round($ratio * $proportion['full']);
    }

    //人工评分
    protected function getRatingScore($proportion = [])
    {
        $this->checkParameter($proportion, 'max,e');
        $rating = $this->video->rating > $proportion['max'] ? $proportion['max'] : $this->video->rating;
        return round($rating * $proportion['e']);
    }

    //评论评分
    protected function getCommentRatioScore($proportion = [])
    {
        $this->checkParameter($proportion, 'thr,coe,full');
        $comment_sum = $this->video->sco_comment_sum;//sco_comment_sum 有限制统计次数的
        $ratio = round($comment_sum / $this->getWatchCoeNum($proportion['coe']), 6);
        $ratio = $this->thresholdRatio($ratio, $comment_sum, $proportion['thr']);
        return round($ratio * $proportion['full']);
    }

    //时长评分
    protected function getDurationScore($proportion = [])
    {
        $this->checkParameter($proportion, 'max,full');
        $duration = $this->video->duration > $proportion['max'] ? $proportion['max'] : $this->video->duration;
        return round(($duration / $proportion['max']) * $proportion['full']);
    }

    protected function thresholdRatio($ratio, $value, $intervals)
    {
        return Calc::thresholdRatio($ratio, $value, $intervals);
    }

    protected function checkParameter($params, $fields)
    {
        $fieldArr = str_to_fields($fields);
        foreach ($fieldArr as $f) {
            if (!isset($params[$f]) || $params[$f] === '') {
                throw  new Exception('check parameter ' . $f . ' error');
            }
            if (($f == 'coe' || $f == 'thr') && !is_array($params[$f])) {
                throw  new Exception('check parameter ' . $f . ' error');
            }
        }
    }

    protected function getWatchCoeNum($coeArr, $e = 1)
    {
        $watch_sum = $this->video->watch_sum > 0 ? $this->video->watch_sum : 1;
        $watch_sum = $watch_sum * $e;
        $last = 0;
        foreach ($coeArr as $range => $coe) {
            if (Calc::validateRange($watch_sum, $range)) {
                return $coe * $watch_sum;
            }
            $last = $coe;
        }
        return $last * $watch_sum;
    }
}
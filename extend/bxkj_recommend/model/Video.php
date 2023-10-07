<?php

namespace bxkj_recommend\model;

use bxkj_module\service\Service;
use bxkj_recommend\exception\Exception;
use bxkj_recommend\ProRedis;
use think\Db;

class Video extends Model
{
    protected $id;
    protected $user;
    protected $tagInfos = [];
    protected $topicInfos = [];

    public function __construct($id, $autoQuery = true)
    {
        parent::__construct();
        if (is_array($id)) {
            $this->data = $id;
            $id = $id['id'];
            $this->id = $id;
        } else {
            $this->id = $id;
            if ($autoQuery) {
                $queryRes = $this->query();
                if (!$queryRes) throw new Exception('video not exist', 1000);
            } else {
                $this->data = ['id' => $this->id];
            }
        }
    }

    public static function getDetailKey($videoId)
    {
        return ProRedis::genKey("video:{$videoId}");
    }

    public function query($id = null)
    {
        $this->id = isset($id) ? $id : $this->id;
        $this->data = Db::name('video')->where(['id' => $this->id])->find();
        return $this->data;
    }

    //获取标签组
    public function getTags($useCache = true)
    {
        $tags = [];
        $tagNames = false;
        $vKey = self::getDetailKey($this->id);
        if ($useCache) {
            $tagNames = $this->redis->hGet($vKey, 'tag_names');
            if (!empty($tagNames)) {
                $tagArr = explode(',', $tagNames);
                foreach ($tagArr as $value) {
                    $tags[] = new VideoTag($value);
                }
            }
        }
        if ($tagNames === false) {
            $tagIds = $this->data['tags'] ? str_to_fields($this->data['tags']) : [];
            $topicIds = $this->data['topic'] ? str_to_fields($this->data['topic']) : [];
            if (!empty($tagIds)) {
                $tags = array_merge($tags, $this->getVideoTagByIds($tagIds));
            }
            if (!empty($topicIds)) {
                $tags = array_merge($tags, $this->getTopicByIds($topicIds));
            }
            if (!empty($this->data['city_name'])) {
                $tags[] = new VideoTag('城市:' . $this->data['city_name']);
                if (!empty($this->data['location_id'])) {
                    $tags[] = new VideoTag("{$this->data['city_name']}_商圈:{$this->data['location_id']}");
                }
            }
            if (!empty($this->data['music_id'])) {
                $tags[] = new VideoTag('音乐:' . $this->data['music_id']);
            }
            $tags[] = new VideoTag('用户:' . $this->data['user_id']);
            $gender = User::getUserGender('user', $this->data['user_id']);
            if (!empty($gender) && $gender !== '0') {
                $genderStr = $gender == '0' ? '未知' : ($gender == '1' ? '男' : '女');
                $tags[] = new VideoTag('性别:' . $genderStr);
            }
            if (!empty($tags)) {
                $tagNames = '';
                foreach ($tags as $tag) {
                    $tagNames .= $tag->name . ',';
                }
                $tagNames = rtrim($tagNames, ',');
                $this->redis->hSet($vKey, 'tag_names', $tagNames);
            }
        }
        return $tags;
    }

    //评分
    public function evaluate()
    {
        if (empty($this->data) || empty($this->data['user_id'])) {
            $queryRes = $this->query();
            if (!$queryRes) return $this;
        }
        $score = new VideoScore($this);
        $this->data['score'] = $score->evaluate();
        return $this;
    }

    //获取发布用户模型
    public function getUser($useCache = true)
    {
        $vKey = self::getDetailKey($this->id);
        $userId = false;
        if ($useCache) {
            $userId = $this->redis->hGet($vKey, 'user_id');
        }
        if ($userId === false) {
            if (empty($this->data['user_id'])) {
                $queryRes = $this->query();
                if (!$queryRes) return false;
            }
            $userId = $this->data['user_id'];
            $this->redis->hSet($vKey, 'user_id', $userId);
        }
        if (!isset($this->user) || $this->user->user_id != $userId) {
            $this->user = new User('user', $userId);
        }
        return $this->user;
    }

    public function getDuration($useCache = true)
    {
        $vKey = self::getDetailKey($this->id);
        $duration = false;
        if ($useCache) {
            $duration = $this->redis->hGet($vKey, 'duration');
        }
        if ($duration === false) {
            if (empty($this->data['duration'])) {
                $queryRes = $this->query();
                if (!$queryRes) return false;
            }
            $duration = $this->data['duration'];
            $this->redis->hSet($vKey, 'duration', $duration);
        }
        return $duration;
    }

    protected function getVideoTagByIds($tagIds)
    {
        $tags = [];
        $unknowIds = [];
        foreach ($tagIds as $tagId) {
            if (empty($this->tagInfos[(string)$tagId])) $unknowIds[] = $tagId;
        }
        if (!empty($unknowIds)) {
            $tagList = Db::name('video_tags')->whereIn('id', $unknowIds)->field('id,name,pid')->select();
            $pids = Service::getIdsByList($tagList, 'pid');
            $parents = [];
            if (!empty($pids)) {
                $parents = Db::name('video_tags')->whereIn('id', $pids)->field('id,name,pid')->select();
                $parents = $parents ? $parents : [];
            }
            foreach ($tagList as $tag) {
                $parent = Service::getItemByList($tag['pid'], $parents, 'id');
                $tag['parent'] = $parent ? $parent : ['name' => '未分类标签'];
                $this->tagInfos[(string)$tag['id']] = $tag;
            }
        }
        foreach ($tagIds as $tagId) {
            if (!empty($this->tagInfos[(string)$tagId])) {
                $tmp = $this->tagInfos[(string)$tagId];
                $tmpName = $tmp['parent']['name'] . ':' . $tmp['name'];
                $tags[] = new VideoTag($tmpName);
            }
        }
        return $tags;
    }

    protected function getTopicByIds($topicIds)
    {
        $tags = [];
        $unknowIds = [];
        foreach ($topicIds as $topicId) {
            if (empty($this->topicInfos[(string)$topicId])) $unknowIds[] = $topicId;
        }
        if (!empty($unknowIds)) {
            $topicList = Db::name('topic')->whereIn('id', $topicIds)->field('id,title')->select();
            foreach ($topicList as $topic) {
                $this->topicInfos[(string)$topic['id']] = $topic;
            }
        }
        foreach ($topicIds as $topicId) {
            if (!empty($this->topicInfos[(string)$topicId])) {
                $item = $this->topicInfos[(string)$topicId];
                $tags[] = new VideoTag("话题:{$item['title']}");
            }
        }
        return $tags;
    }

    public function save($fields = null)
    {
        $saveData = isset($fields) ? copy_array($this->data, $fields) : $this->data;
        $num = 0;
        if (!empty($saveData)) {
            $num = Db::name('video')->where(['id' => $this->data['id']])->update($saveData);
        }
        return $num;
    }

    public function exists()
    {
        $vKey = self::getDetailKey($this->id);
        return $this->redis->exists($vKey);
    }

    public function getUserId()
    {
        $vKey = self::getDetailKey($this->id);
        if (empty($this->data['user_id'])) {
            $this->data['user_id'] = $this->redis->hGet($vKey, 'user_id');
        }
        if (empty($this->data['user_id'])) {
            $queryRes = $this->query();
            if (!empty($this->data['user_id'])) {
                $this->redis->hSet($vKey, 'user_id', $this->data['user_id']);
            }
        }
        return $this->data['user_id'];
    }

    public function isOwn(User $user)
    {
        $aliasType = $user->getAliasType();
        $aliasId = $user->getAliasId();
        if ($aliasType != 'user') return false;
        $userId = $this->getUserId();
        if ($aliasId != $userId) return false;
        return true;
    }

    //记录观看时长（排除自己）
    public function watch(User $audience, $startTime, $maxDuration, $duration)
    {
        $aliasType = $audience->getAliasType();
        $userMark = $audience->getUserMark();
        //play_sum播放次数（不排除重复和自己）
        //Db::name('video')->where(['id' => $this->data['id']])->setInc('play_sum', 1);//目前前端直接加了20190522
        if ($this->isOwn($audience)) return true;
        $vDuration = $this->getDuration();
        $max = $vDuration * 3;//单个用户最多记录三遍
        $duration2 = round($duration / 1000); // ms=>s
        $maxDuration2 = round($maxDuration / 1000);// ms=>s
        //每个视频的累计观看时长
        if ($duration2 > 0) {
            $num2 = Db::name('video')->where(['id' => $this->data['id']])->setInc('watch_duration', $duration2);
        }
        $update = [];
        //独立观看用户
        $key2 = ProRedis::genKey("watch:iuser:{$this->data['id']}");
        $state = $this->getSwitchState($maxDuration);
        $pfNum = $this->redis->pfAdd($key2, [$userMark]);
        if ($pfNum) {
            $count = $this->redis->pfCount($key2);
            $update['watch_sum'] = (int)$count;
            //完播率/切换率/普通 一个用户只能评价一次
            $key3 = ProRedis::genKey("wat_{$state}:iuser:{$this->data['id']}");
            $pfNum2 = $this->redis->pfAdd($key3, [$userMark]);
            if ($pfNum2) {
                $count2 = $this->redis->pfCount($key3);
                $update["{$state}_sum"] = (int)$count2;
            }
        }
        $isUpdate = false;
        foreach ($update as $kk => $vv) {
            if ($this->data[$kk] != $vv) {
                $isUpdate = true;
                break;
            }
        }
        if ($isUpdate) {
            Db::name('video')->where(['id' => $this->data['id']])->update($update);
        }
        return true;
    }

    public function like(User $audience, $value)
    {
        $num = 0;
        if ($value > 0) {
            $num = Db::name('video')->where(['id' => $this->data['id']])->setInc('zan_sum', abs($value));
            $num2 = Db::name('video')->where(['id' => $this->data['id']])->setInc('sco_zan_sum', abs($value));
        } else if ($value < 0) {
            $num = Db::name('video')->where(['id' => $this->data['id']])->setDec('zan_sum', abs($value));
            $num2 = Db::name('video')->where(['id' => $this->data['id']])->setDec('sco_zan_sum', abs($value));
        }
        if ($num > 0) {
            $user = $this->getUser();
            $user->like($audience, $value);
            $user->updateChangeFields();
        }
    }

    public function share(User $audience, $value)
    {
        $num = 0;
        if ($value > 0) {
            $num = Db::name('video')->where(['id' => $this->data['id']])->setInc('share_sum', abs($value));
        } else if ($value < 0) {
            $num = Db::name('video')->where(['id' => $this->data['id']])->setDec('share_sum', abs($value));
        }
        $key = ProRedis::genKey("share_v:iuser:{$this->data['id']}");
        $this->redis->pfAdd($key, [$audience->getUserMark()]);
        $count = $this->redis->pfCount($key);
        Db::name('video')->where(['id' => $this->data['id']])->update(['sco_share_sum' => (int)$count]);
    }

    public function comment(VideoComment $comment, $num)
    {
        $authorUid = $comment->getUserId();
        if ($num > 0) {
            Db::name('video')->where(['id' => $this->data['id']])->setInc('comment_sum', abs($num));
        } else if ($num < 0) {
            Db::name('video')->where(['id' => $this->data['id']])->setDec('comment_sum', abs($num));
        }
        $key = ProRedis::genKey("comment:iuser:{$this->data['id']}");//视频评论独立用户
        $this->redis->pfAdd($key, ["user:{$authorUid}"]);
        $count = $this->redis->pfCount($key);
        Db::name('video')->where(['id' => $this->data['id']])->update(['sco_comment_sum' => (int)$count]);
    }

    public function reply(VideoComment $comment, $num)
    {
        $authorUid = $comment->getUserId();
        if ($num > 0) {
            Db::name('video')->where(['id' => $this->data['id']])->setInc('comment_sum', abs($num));
        } else if ($num < 0) {
            Db::name('video')->where(['id' => $this->data['id']])->setDec('comment_sum', abs($num));
        }
        $key = ProRedis::genKey("comment:iuser:{$this->data['id']}");//视频评论独立用户
        $this->redis->pfAdd($key, ["user:{$authorUid}"]);
        $count = $this->redis->pfCount($key);
        Db::name('video')->where(['id' => $this->data['id']])->update(['sco_comment_sum' => (int)$count]);
    }

    public function commentDelete($commentData, $incrMode = false)
    {
        if ($incrMode) {
            Db::name('video')->where(['id' => $this->data['id']])->setDec('comment_sum', abs($commentData['del_num']));
        } else {
            $comment_sum = Db::name('video_comment')->where(['video_id' => $this->data['id']])->count();
            Db::name('video')->where(['id' => $this->data['id']])->update(['comment_sum' => $comment_sum]);
        }
    }

    //获取切换状态 $max_duration 单位ms
    public function getSwitchState($maxDuration)
    {
        if (empty($this->data['duration'])) {
            $this->query();
        }
        $tmp = round($this->data['duration'] * 1000 * 0.8, 3);
        $tmp2 = round($this->data['duration'] * 1000 * 0.1, 3);
        if ($maxDuration >= $tmp) {
            return 'played_out';
        } else if ($maxDuration <= $tmp2) {
            return 'switch';
        }
        return 'general';
    }
}
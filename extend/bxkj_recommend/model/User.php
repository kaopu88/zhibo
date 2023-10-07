<?php

namespace bxkj_recommend\model;

use bxkj_common\RabbitMqChannel;
use bxkj_module\service\Task;
use bxkj_module\service\UserRedis;
use bxkj_recommend\behavior\BehaviorBridge;
use bxkj_recommend\exception\Exception;
use bxkj_recommend\ProRedis;
use think\Db;

class User extends Model
{
    protected $aliasType;
    protected $aliasId;
    public $behavior;
    protected $changeFollows = [];//关注变化情况
    protected $newPullBlacklist = [];//最新列入黑名单成员
    protected $changeTags = [];//兴趣标签变化情况
    protected $changeFields = [];
    protected $instockEarly = 200;//库存预警

    public function __construct($aliasType, $aliasId, $autoQuery = true)
    {
        parent::__construct();
        $this->aliasType = $aliasType;
        $this->aliasId = $aliasId;
        if ($autoQuery) {
            $queryRes = $this->query();
            if (!$queryRes) throw new Exception('user not exist', 2000);
        } else {
            if ($this->aliasType == 'user') {
                $this->data = ['user_id' => $this->aliasId];
            } else {
                $this->data = ['meid' => $this->aliasId];
            }
        }
        $this->behavior = new BehaviorBridge($this);
    }

    public static function getUserGender($aliasType, $aliasId)
    {
        $key = ProRedis::genKey("user:{$aliasType}:{$aliasId}");
        if ($aliasType == 'meid') return '0';
        $redis = ProRedis::getInstance();
        $gender = $redis->hGet($key, 'gender');
        if ($gender === false) {
            if ($aliasType == 'user') {
                $user = Db::name('user')->where(['user_id' => $aliasId])->field('user_id,gender')->find();
                $user = $user ? $user : ['gender' => 0, 'user_id' => $aliasId];
            }
            $redis->hMset($key, $user);
            $gender = $user['gender'];
        }
        return $gender;
    }

    public static function getFansUids($userId, $length)
    {
        $redis = ProRedis::getInstance();
        $fansKey = ProRedis::genKey("fans:{$userId}");
        $redis->setOption(\Redis::OPT_SCAN, \Redis::SCAN_RETRY);
        $il = $length > 50 ? 50 : $length;
        $fansUids = [];
        $iterator = null;
        while ($arr_mems = $redis->sScan($fansKey, $iterator, '*', $il)) {
            foreach ($arr_mems as $fansUid) {
                $fansUids[] = $fansUid;
            }
            if (count($fansUids) >= $length) break;
        }
        return $fansUids;
    }

    public function query()
    {
        if ($this->aliasType == 'user') {
            $this->data = Db::name('user')->where(['user_id' => $this->aliasId])->find();
        } else {
            $this->data = ['meid' => $this->aliasId];
        }
        return $this->data;
    }

    //获取用户权重
    public function getWeight()
    {
        $weight = 0;
        if ($this->aliasType != 'user') return $weight;
        if ($this->data['status'] == '0') return $weight;
        //手机号 0.05
        if (!empty($this->data['phone'])) $weight += 0.05;
        $now = time();
        //新用户
        if ($this->data['create_time'] >= ($now - (1 * 86400))) {
            $weight += 0.08;
        } else if ($this->data['create_time'] >= ($now - (3 * 86400))) {
            $weight += 0.05;
        }
        //实名认证 0.1
        if ($this->data['verified'] == '1') $weight += 0.1;
        //创作号 0.25
        if ($this->data['is_creation'] == '1') $weight += 0.25;
        //等级 0.1
        $level = $this->data['level'] > 80 ? 80 : $this->data['level'];
        $levelW = round(($level / 80) * 0.1, 4);
        $weight += $levelW;
        if ($this->data['is_anchor'] == '1') {
            //主播 0.2
            $weight += 0.2;
        } else if ($this->data['is_promoter'] == '1') {
            //推广员 0.1
            $weight += 0.1;
        } else if ($this->data['isvirtual'] == '1') {
            //虚拟号 0.05
            $weight += 0.05;
        }
        //粉丝 0.5
        $fans_num = $this->data['fans_num'] > 5000 ? 5000 : $this->data['fans_num'];
        $fansW = round(($fans_num / 5000) * 0.55, 4);
        $like_num = $this->data['like_num'];
        $film_num = $this->data['film_num'];
        $ratio = $like_num / ($film_num * 1000);
        $ratio = $ratio > 1 ? 1 : ($ratio < 0 ? 0 : $ratio);
        $filmW = round($ratio * 0.55, 4);
        $weight += max($filmW, $fansW);
        $weight = round($weight, 4);
        return $weight > 1 ? 1 : ($weight < 0 ? 0 : $weight);
    }

    public function getUserMark()
    {
        return "{$this->aliasType}:{$this->aliasId}";
    }

    public function getAliasType()
    {
        return $this->aliasType;
    }

    public function getAliasId()
    {
        return $this->aliasId;
    }

    //加入change标签组
    public function pushChangeTag(UserVideoTag &$tag)
    {
        $has = false;
        foreach ($this->changeTags as $ctag) {
            if ($ctag->key == $tag->key) {
                $has = true;
                break;
            }
        }
        if (!$has) {
            $this->changeTags[] = &$tag;
            return 1;
        }
        return 0;
    }

    //粉丝关注
    public function fans(User $fans, $value)
    {
        $num = 0;
        if ($value > 0) {
            $num = Db::name('user')->where(['user_id' => $this->data['user_id']])->setInc('fans_num', abs($value));
        } else if ($value < 0) {
            $num = Db::name('user')->where(['user_id' => $this->data['user_id']])->setDec('fans_num', abs($value));
        }
        if ($num) $this->changeFields[] = 'fans_num';
        return $num;
    }

    //关注偶像
    public function follow(User $idol, $value)
    {
        $num = 0;
        $key = ProRedis::genKey("follow:{$this->data['user_id']}");
        if ($value > 0) {
            $num = Db::name('user')->where(['user_id' => $this->data['user_id']])->setInc('follow_num', abs($value));
        } else if ($value < 0) {
            $num = Db::name('user')->where(['user_id' => $this->data['user_id']])->setDec('follow_num', abs($value));
        }
        if ($num) $this->changeFields[] = 'follow_num';
        return $num;
    }

    //拉黑用户
    public function black(User $person, $value)
    {
        $num = 0;
        $beNum = $person->beBlack($this, $value);
        $person->updateChangeFields();
        $this->newPullBlacklist[] = $person;
        return $num;
    }

    //被拉黑
    public function beBlack(User $host, $value)
    {
        $num = 0;
        if ($value > 0) {
            $num = Db::name('user')->where(['user_id' => $this->data['user_id']])->setInc('be_black_num', abs($value));
        } else if ($value < 0) {
            $num = Db::name('user')->where(['user_id' => $this->data['user_id']])->setDec('be_black_num', abs($value));
        }
        if ($num) $this->changeFields[] = 'be_black_num';
        return $num;
    }

    //被点赞
    public function like(User $fans, $value)
    {
        $num = 0;
        if ($value > 0) {
            $num = Db::name('user')->where(['user_id' => $this->data['user_id']])->setInc('like_num', abs($value));
        } else if ($value < 0) {
            $num = Db::name('user')->where(['user_id' => $this->data['user_id']])->setDec('like_num', abs($value));
        }
        if ($num) $this->changeFields[] = 'like_num';
        return $num;
    }

    //被分享
    public function share(User $fans, $value)
    {
        $num = 0;
        if ($value > 0) {
            $num = Db::name('user')->where(['user_id' => $this->data['user_id']])->setInc('share_num', abs($value));
        } else if ($value < 0) {
            $num = Db::name('user')->where(['user_id' => $this->data['user_id']])->setDec('share_num', abs($value));
        }
        if ($num) $this->changeFields[] = 'share_num';
        return $num;
    }

    //被查看
    public function beView(User $visitor, $value)
    {
        $num = 0;
        if ($value > 0) {
            $num = Db::name('user')->where(['user_id' => $this->data['user_id']])->setInc('pv', abs($value));
        } else {
            $num = Db::name('user')->where(['user_id' => $this->data['user_id']])->setDec('pv', abs($value));
        }
        if ($num) $this->changeFields[] = 'pv';
        return $num;
    }

    //观看视频
    public function watch(Video $video, $start_time, $max_duration, $duration)
    {
        $userMark = $this->getUserMark();
        if ($this->aliasType == 'user') {
            $taskMod = new Task();
            $data = [
                'user_id' => $this->data['user_id'],
                'task_type' => 'watchVideo',
                'task_value' => $max_duration
            ];
            $taskMod->subTask($data);
        }
    }

    //更新数据
    public function updateChangeFields()
    {
        if (empty($this->changeFields)) return 1;
        if ($this->aliasType != 'user') return 1;
        $fields = array_unique($this->changeFields);
        $updateData = Db::name('user')->where(['user_id' => $this->data['user_id']])->find();
        if (empty($updateData)) return 0;
        $this->data = array_merge($this->data, $updateData);
        UserRedis::updateData($this->data['user_id'], $this->data);
        $this->changeFields = [];
        return 1;
    }


    //训练模型
    public function training()
    {
        //更新兴趣标签评分
        $userMark = $this->getUserMark();
        if (!empty($this->changeTags)) {
            $compKey = ProRedis::genKey("interest:{$userMark}:complex");
            $this->redis->repairRedundancy($compKey, 2000, 1000);
            foreach ($this->changeTags as $ctag) {
                if (false) $ctag = new UserVideoTag();
                $score = $ctag->evaluate();
                $ctagId = $ctag->getTagId();
                $this->redis->zAdd($compKey, $score, $ctagId);
            }
        }
        $this->updateChangeFields();
        //召回与追加生产
        $recall = new UserIndexRecall($this);
        $recall->start();
        $recallNum = $recall->getRecallNum();//获取本次召回数量(预估)
        $this->changeTags = [];
        $this->changeFollows = [];
        $this->newPullBlacklist = [];
        //重新生成索引
        if ($recallNum > 0) {
            $indexKey = ProRedis::genKey("index:{$userMark}");
            $instock = $this->redis->zCount($indexKey, '-inf', '+inf');//索引库存量
            if ($instock < $this->instockEarly) {
                $bdingKey = ProRedis::genKey("bding:{$userMark}");
                $building = $this->redis->exists($bdingKey);//是否正在生产中
                if (!$building) {
                    $rabbitChannel = new RabbitMqChannel();
                    $rabbitChannel->exchange('main')->send('prophet.building', ['user_mark' => $userMark]);
                    $rabbitChannel->close();
                }
            }
        }
    }

    public function getOffset()
    {
        $key = ProRedis::genKey("goffset:index:{$this->aliasType}");
        $offset = $this->redis->zScore($key, $this->aliasId);
        if ($offset === false) {
            $gidKey = ProRedis::genKey("goffset:gid");
            $lockKey = ProRedis::genKey("goffset:lock");
            $totalSleep = 0;
            $now = time();
            $timeout = 6;
            while (!$this->redis->setnx($lockKey, $now)) {
                if ($totalSleep > 1000000 * $timeout) {
                    $this->redis->set(ProRedis::genKey("goffset:exception"), 'timeout');
                    throw new Exception("wait unlock user offset timeout", 1);
                }
                usleep(500);//休眠0.5毫秒
                $totalSleep += 500;
            }
            $offset = (int)$this->redis->get($gidKey);
            $int = 1;
            if ($offset == 0) {
                $int = 2;
                $offset++;
            }
            $this->redis->incrBy($gidKey, $int);
            $this->redis->del($lockKey);
            $this->redis->zAdd($key, $offset, $this->aliasId);
        }
        return $offset;
    }

}
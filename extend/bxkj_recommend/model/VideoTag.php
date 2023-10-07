<?php

namespace bxkj_recommend\model;

use bxkj_recommend\ProDb;
use bxkj_recommend\ProRedis;
use think\Db;
use bxkj_recommend\exception\Exception;

class VideoTag extends Model
{
    protected $name;

    public function __construct($name, $autoQuery = false)
    {
        parent::__construct();
        $this->name = $name;
        if ($autoQuery) {
            $queryRes = $this->query();
            if (!$queryRes) throw new Exception('tag not exist', 3000);
        } else {
            $this->data = [
                'id' => md5($this->name),
                'name' => $this->name
            ];
        }
    }

    public static function getUserTagKey($user_id)
    {
        return ProRedis::genKey("用户:{$user_id}");
    }

    public function query()
    {
        $id = md5($this->name);
        $this->data = ProDb::name('prophet_tags')->where(['id' => $id])->find();
        return $this->data;
    }

    public function getType()
    {
        list($type, $tmp) = explode(":", $this->name);
        if (preg_match('/_商圈$/', $type)) return '商圈';
        if ($type == '音乐') return '音乐';
        if ($type == '城市') return '城市';
        if ($type == '用户') return '用户';
        if ($type == '性别') return '性别';
        return '其他';
    }

    //获取标签权重
    public function getWeight()
    {
        $weight = 0;
        $type = $this->getType();
        list($type2, $tmp) = explode(":", $this->name);
        if ($type == '其他') {
            $tagInfo = Db::name('video_tags')->where(['name' => $tmp])->find();
            $weight = $tagInfo ? $tagInfo['weight'] : 0;
        }
        $weight = round($weight * 1, 5);
        return $weight > 1 ? 1 : ($weight < 0 ? 0 : $weight);
    }

    public function create()
    {
        $id = md5($this->name);
        $num = ProDb::name('prophet_tags')->where(['id' => $id])->count();
        if (!$num) {
            $data = [
                'id' => $id,
                'type' => $this->getType(),
                'name' => $this->name,
                'create_time' => time(),
                'last_push_time' => 0,
                'last_fetch_time' => 0
            ];
            try {
                $res = ProDb::name('prophet_tags')->insert($data);
            } catch (\Exception $e) {
            }
        }
        return true;
    }
}
<?php

namespace app\admin\service;

use bxkj_module\service\Service;
use bxkj_common\RedisClient;
use think\Db;

class DataVersion extends Service
{
    protected static $redis;
    protected $dbs = [];

    public function __construct()
    {
        parent::__construct();
        if (!self::$redis) {
            self::$redis = new RedisClient();
        }
        $this->dbs['production'] = require ROOT_PATH . 'config/pro/database.php';
        $this->dbs['testing'] = require ROOT_PATH . 'config/testing/database.php';
    }

    public function check($table)
    {
        $productionKey = "data_v:production:{$table}";
        $testingKey = "data_v:testing:{$table}";
        $pv = self::$redis->get($productionKey);
        $tv = self::$redis->get($testingKey);
        $type = $this->getType();
        if ($type == 'production') {
            $config = $this->dbs['testing'];
            return $tv > $pv ? $config['database'] . '#' . $tv : 'last';
        } else if ($type == 'testing') {
            $config = $this->dbs['production'];
            return $pv > $tv ? $config['database'] . '#' . $pv : 'last';
        }
        return false;
    }

    public function update($table)
    {
        $type = $this->getType();
        $key = "data_v:{$type}:{$table}";
        return self::$redis->incrBy($key, 1);
    }

    public function updateToLast($table)
    {
        $productionKey = "data_v:production:{$table}";
        $testingKey = "data_v:testing:{$table}";
        $pv = self::$redis->get($productionKey);
        $tv = self::$redis->get($testingKey);
        $type = $this->getType();
        $key = "data_v:{$type}:{$table}";
        self::$redis->set($key, max($pv, $tv));
    }

    protected function getType($db = null)
    {
        $db = isset($db) ? $db : config('database.database');
        $type = '';
        foreach ($this->dbs as $t => $v) {
            if ($v['database'] == $db) {
                $type = $t;
                break;
            }
        }
        return $type;
    }

    public function sync($targetStr, $table, $mode = 'mirror')
    {
        list($targetDb, $targetVersion) = explode('#', $targetStr);
        $current = $this->getType();
        $target = $this->getType($targetDb);
        if (empty($target)) return $this->setError('同步目标不存在');
        if ($current == $target) return $this->setError('同步目标不能相同');
        $targetSql = $this->getCreateSql($target, $table);
        $currentTrueTabName = $this->dbs[$current]['prefix'] . $table;
        //数据库结构不同
        $newTargetSql = str_replace('TABLE `' . $this->dbs[$target]['prefix'], 'TABLE `' . $this->dbs[$current]['prefix'], $targetSql);
        $existsSql = 'SELECT table_name FROM information_schema.TABLES WHERE table_name =\'' . $currentTrueTabName . '\';';
        $has = Db::connect($this->dbs[$current])->query($existsSql);
        if ($has) {
            $currentSql = $this->getCreateSql($current, $table);
            //同步数据结构
            if (sha1($newTargetSql) != sha1($currentSql)) {
                Db::connect($this->dbs[$current])->execute('DROP TABLE `' . $currentTrueTabName . '`');
                Db::connect($this->dbs[$current])->execute($newTargetSql);
            }
        } else {
            $num = Db::connect($this->dbs[$current])->execute($newTargetSql);
        }
        //清空表
        Db::connect($this->dbs[$current])->execute('TRUNCATE table ' . $currentTrueTabName);
        Db::connect($this->dbs[$target])->startTrans();
        $result = Db::connect($this->dbs[$target])->name($table)->select();
        if ($result) {
            foreach ($result as $item) {
                Db::connect($this->dbs[$current])->name($table)->insertGetId($item);
            }
        }
        return true;
    }

    public function getCreateSql($type, $table)
    {
        $trueTabName = $this->dbs[$type]['prefix'] . $table;
        $createRes = Db::connect($this->dbs[$type])->query('show create table ' . $trueTabName);
        $createSql = $createRes[0]['Create Table'];
        return $createSql;
    }


}
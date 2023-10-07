<?php

namespace bxkj_module\service;

use bxkj_common\DataFactory;
use think\Db;

class Service
{
    protected $db;
    public $df;//数据处理工厂
    protected $error;//错误对象
    public static $transNum = 0;//事务计数器

    public function __construct()
    {
        $this->df = new DataFactory($this);
        if (false) $this->db = new MyQuery();
    }

    public function setError($message = '', $code = 1, $data = [])
    {
        $this->error = is_error($message) ? $message : make_error($message, $code, $data);
        return false;
    }

    public function getError()
    {
        return $this->error;
    }

    //开启事务
    public static function startTrans()
    {
        if (self::$transNum <= 0) {
            Db::startTrans();
        }
        self::$transNum++;
    }

    //提交事务
    public static function commit()
    {
        self::$transNum--;
        if (self::$transNum <= 0) {
            Db::commit();
            self::$transNum = 0;
        }
    }

    public static function rollback()
    {
        self::$transNum--;
        if (self::$transNum <= 0) {
            Db::rollback();
            self::$transNum = 0;
        }
    }

    public function db($name = null)
    {
        $name = isset($name) ? $name : parse_name(class_basename($this));
        return Db::name($name);
    }

    public static function getItemByList($value, $list, $key = 'id', $field = null)
    {
        if (is_object($list) && method_exists($list, 'toArray')) {
            $list = $list->toArray();
        }
        if (!is_array($list)) return null;
        foreach ($list as $index => $item) {
            if ($item[$key] == $value) {
                return isset($field) ? $item[$field] : $item;
            }
        }
        return null;
    }

    //获取关联集合
    public static function getIdsByList($list, $fieldList = 'id', $multi = false)
    {
        $tmp = [];
        $map = [];
        $fieldGroups = explode('|', $fieldList);
        foreach ($fieldGroups as $index => $fieldGroupStr) {
            $fieldArr = array_unique(explode(',', $fieldGroupStr));
            foreach ($fieldArr as $field) {
                $map[$field] = $index;
            }
        }
        foreach ($list as $item) {
            foreach ($map as $field => $index) {
                if (!is_array($tmp[$index])) $tmp[$index] = [];
                if (!empty($item[$field]) && !in_array($item[$field], $tmp[$index])) {
                    $tmp[$index][] = $item[$field];
                }
            }
        }
        if ($multi) return $tmp;
        $tmp2 = [];
        foreach ($tmp as $arr) {
            $tmp2 = array_merge($tmp2, $arr);
        }
        return array_unique($tmp2);
    }

    public static function getRelList($result, $queryFun, $fieldList = 'id')
    {
        $list = [];
        $ids = self::getIdsByList($result, $fieldList);
        if (!empty($ids)) {
            $list = call_user_func_array($queryFun, [$ids]);
        }
        return $list ? $list : [];
    }


}
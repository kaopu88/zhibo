<?php

namespace app\service;


class Db
{
    protected static $transNum = 0;

    public static function connect()
    {
        global $db;
        $pdo = $db->getPDO();
        return $pdo;
    }

    //beginTrans别名
    public static function startTrans()
    {
        $pdo = self::connect();
        if (self::$transNum <= 0) {
            $pdo->beginTransaction();
        }
        self::$transNum++;
    }

    public static function rollback()
    {
        $pdo = self::connect();
        self::$transNum--;
        if (self::$transNum <= 0) {
            $result = $pdo->rollBack();
            self::$transNum = 0;
        }
    }

    public static function commit()
    {
        $pdo = self::connect();
        self::$transNum--;
        if (self::$transNum <= 0) {
            $pdo->commit();
            self::$transNum = 0;
        }
    }

    public static function findItem($table, $pk, $id = '', $hasDeleteTime = false)
    {
        global $db;
        $str = '';
        if (is_array($pk)) {
            foreach ($pk as $k1 => $v1) {
                $str .= "`{$k1}`='{$v1}' AND ";
            }
            $str = preg_replace('/AND$/', '', trim($str));
        } else {
            $str = "`{$pk}`='{$id}'";
        }
        $sql = "SELECT * FROM " . TABLE_PREFIX . "{$table} WHERE {$str}" . ($hasDeleteTime ? " AND delete_time is null" : "") . ' LIMIT 1';
        $result = $db->query($sql);
        if (!$result) return null;
        return $result[0];
    }

    public static function update($table, $pk, $id, $data)
    {
        global $db;
        $res = $db->update(TABLE_PREFIX . $table)->cols($data)->where("{$pk}=" . $id)->query();
        return $res;
    }

    public static function insert($table, $data)
    {
        global $db;
        $res = $db->insert(TABLE_PREFIX . $table)->cols($data)->query();
        return $res;
    }

    public static function getVal($table, $pk, $id, $name, $hasDeleteTime = false)
    {
        $info = self::findItem($table, $pk, $id, $hasDeleteTime);
        return empty($info) ? null : $info[$name];
    }

    public static function setInc($table, $pk, $id, $field, $value)
    {
        global $db;
        $sql = "UPDATE " . TABLE_PREFIX . "{$table} set {$field}={$field}+{$value} where {$pk}={$id}";
        $result = $db->query($sql);
        return $result;
    }

    public static function count($table, $sumPk, $pk, $id = '')
    {
        global $db;
        $str = '';
        if (is_array($pk)) {
            foreach ($pk as $k1 => $v1) {
                $str .= "`{$k1}`='{$v1}' AND ";
            }
            $str = preg_replace('/AND$/', '', trim($str));
        } else {
            $str = "`{$pk}`='{$id}'";
        }
        $sql = "SELECT COUNT($sumPk) as s_sum FROM " . TABLE_PREFIX . "{$table} WHERE {$str}";
        $result = $db->query($sql);
        if (!$result || !$result[0]) return 0;
        return (int)$result[0]['s_sum'];
    }

}
<?php

namespace bxkj_common;

use think\Db;

class DataFactoryCallback
{
    //通过正则验证
    public static function regex($value, $rule, $data = null, $more = null)
    {
        return validate_regex($value, $rule);
    }

    //不通过正则验证
    public static function not_regex($value, $rule, $data = null, $more = null)
    {
        return !self::regex($value, $rule, $data, $more);
    }

    //确认
    public static function confirm($value, $rule, $data = null, $more = null)
    {
        return $value === $data[$rule];
    }

    //比较运算
    public static function compare($value, $rule, $data = null, $more = null)
    {
        $result = false;
        preg_match('/^([\>|\<|\!]?\={0,2})(.*)$/', $rule, $arr);
        $arr[1] = empty($arr[1]) ? '==' : $arr[1];
        eval('$result=($value' . $arr[1] . '$arr[2]);');
        return $result;
    }

    //集合内
    public static function in($value, $rule, $data = null, $more = null)
    {
        $rule = is_array($rule) ? $rule : explode(',', $rule);
        return in_array($value, $rule);
    }

    //不在范围内
    public static function not_in($value, $rule, $data = null, $more = null)
    {
        return !self::in($value, $rule, $data, $more);
    }

    //在枚举值集合内
    public static function in_enum($value, $rule, $data = null, $more = null)
    {
        $list = config('enum.' . $rule);
        $enumArr = is_array($list) ? $list : array();
        foreach ($enumArr as $enum) {
            if ($enum['value'] == $value) return true;
        }
        return false;
    }

    //字符长度 mb_strlen
    public static function length($value, $rule, $data = null, $more = null)
    {
        return self::getRangeResult(mb_strlen($value, 'utf-8'), $rule);
    }

    //字符长度 strlen
    public static function strlen($value, $rule, $data = null, $more = null)
    {
        return self::getRangeResult(strlen($value), $rule);
    }

    //区间
    public static function between($value, $rule, $data = null, $more = null)
    {
        return self::getRangeResult($value, $rule);
    }

    //不在区间内
    public static function not_between($value, $rule, $data = null, $more = null)
    {
        return self::getRangeResult($value, $rule, true);
    }

    //过期时间验证 不能小于当前时间
    public static function expire($value, $rule, $data = null, $more = null)
    {
        $value = strtotime($value);
        $start = time();
        $end = '';
        if (!empty($rule)) {
            $end = self::later('', $rule);
        }
        return self::getRangeResult($value, $start . ',' . $end);
    }

    //时间区间
    public static function time_range($value, $rule, $data = null, $more = null)
    {
        $value = preg_match('/^\d+$/', $value) ? $value : strtotime($value);
        return self::getRangeResult($value, $rule);
    }

    public static function date_max($value, $rule, $data = null, $more = null)
    {
        $time = $rule == 'now' ? time() : ($rule == 'today' ? mktime(0, 0, 0) : strtotime($rule));
        $value = strtotime($value);
        return $value <= $time;
    }

    //开始时间结束时间验证
    public static function se_range($value, $rule, $data = null, $more = null)
    {
        $start = preg_match('/^\d+$/', $value) ? $value : strtotime($value);
        $end = preg_match('/^\d+$/', $data[$rule]) ? $data[$rule] : strtotime($data[$rule]);
        if ($start > $end) return false;
        return true;
    }

    //开始时间结束时间验证但是不能已经过期
    public static function se_range2($value, $rule, $data = null, $more = null)
    {
        $start = preg_match('/^\d+$/', $value) ? $value : strtotime($value);
        $end = preg_match('/^\d+$/', $data[$rule]) ? $data[$rule] : strtotime($data[$rule]);
        if ($start > $end) return false;
        if (time() >= $end) return false;
        return true;
    }

    //至少一项不为空
    public static function least($value, $rule, $data = null, $more = null)
    {
        $result = false;
        foreach ($value as $key => $val) {
            if (!empty($val)) $result = true;
        }
        return $result;
    }

    //校验货币值
    public static function currency($value, $rule, $data = null, $more = null)
    {
        return (validate_regex($value, 'currency') && bccomp($value, 0) === 1);
    }

    //用字段填充
    public static function field($value, $rule, $data = null, $more = null)
    {
        return $data[$rule];
    }

    //字符串填充
    public static function string($value, $rule, $data = null, $more = null)
    {
        return $rule;
    }

    //配置文件填充
    public static function config($value, $rule, $data = null, $more = null)
    {
        $tmp = config(parse_tpl($rule, array(
            'value' => $value
        )));
        $arr = [];
        $arr[$more['fields']] = $tmp;
        return $arr;
    }

    //时间戳填充
    public static function time($value, $rule, $data = null, $more = null)
    {
        return time();
    }

    //字符串转时间戳
    public static function strtotime($value, $rule, $data = null, $more = null)
    {
        return strtotime($value);
    }

    //填充日期
    public static function date($value, $rule, $data = null, $more = null)
    {
        $rule = $rule ? $rule : 'Y-m-d';
        return date($rule);
    }

    //哈希加密
    public static function hash($value, $rule, $data = null, $more = null)
    {
        if ($rule == 'md5') return md5($value);
        return sha1($value);
    }

    //array符号分隔
    public static function arr_implode($value, $rule, $data = null, $more = null)
    {
        $rule = isset($rule) ? $rule : ',';
        if (empty($value)) return '';
        return is_array($value) ? implode($rule, $value) : $value;
    }

    public static function not_empty($value, $rule, $data = null, $more = null)
    {
        return !empty($value);
    }

    //多长时间之后
    public static function later($value, $rule, $data = null, $more = null)
    {
        preg_match('/(\d+)([ymdhis]{1})$/', $rule, $result);
        $laterTime = 0;
        if (!empty($result)) {
            if (strtolower($result[2]) == 'y') $laterTime = ((int)$result[1] * 365 * 24 * 3600);
            if (strtolower($result[2]) == 'm') $laterTime = ((int)$result[1] * 30 * 24 * 3600);
            if (strtolower($result[2]) == 'd') $laterTime = ((int)$result[1] * 24 * 3600);
            if (strtolower($result[2]) == 'h') $laterTime = ((int)$result[1] * 3600);
            if (strtolower($result[2]) == 'i') $laterTime = ((int)$result[1] * 60);
            if (strtolower($result[2]) == 's') $laterTime = ((int)$result[1]);
        } else {
            $laterTime = (int)$rule;
        }
        $time = ($value == 'now' || empty($value)) ? time() : strtotime($value);
        return $time + $laterTime;
    }

    //记录是否唯一
    public static function unique($value, $rule, $data = null, $more = null)
    {
        if (empty($more['table'])) return false;
        $df =& $more['df'];
        $fields = $more['fields'];
        $value = is_array($value) ? $value : array($fields => $value);
        $field = is_array($fields) ? $fields : array($fields);
        for ($i = 0; $i < count($field); $i++) {
            $where[$field[$i]] = $value[$field[$i]];
        }
        $delete_time = $df->hasDeleteTime();
        $db = Db::name($more['table']['name']);
        if ($delete_time) {
            $db->where([['delete_time', 'null']]);
        }
        $num = $db->where($where)->count();
        return $num == 0;
    }

    //排除当前记录是否唯一
    public static function exc_unique($value, $rule, $data = null, $more = null)
    {
        if (empty($more['table'])) return false;
        $fields = $more['fields'];
        $value = is_array($value) ? $value : array($fields => $value);
        $field = is_array($fields) ? $fields : array($fields);
        for ($i = 0; $i < count($field); $i++) {
            $where[$field[$i]] = $value[$field[$i]];
        }
        $db = Db::name($more['table']['name']);
        $df =& $more['df'];
        $tmp = $df->getWhereByPks($data, $rule ? $rule : null);
        $delete_time = $df->hasDeleteTime();
        $where2 = [];
        if (is_array($tmp)) {
            foreach ($tmp as $key => $value) {
                $where2[] = [$key, 'neq', $value];
            }
        }
        if ($delete_time) {
            $where2[] = ['delete_time', 'null'];
        }
        $num = $db->where($where2)->where($where)->count();
        return $num == 0;
    }

    public static function has_row($value, $rule, $data = null, $more = null)
    {
        if (!empty($rule)) {
            if (is_string($rule)) $rule = explode(',', $rule);
            if (count($rule) == 1) array_unshift($rule, $more['table']['name']);
        } else {
            $rule = [$more['table']['name'], $more['fields'], ''];
        }
        if (empty($rule) || empty($rule[0]) || empty($rule[1])) return false;
        $db = Db::name($rule[0]);
        $df =& $more['df'];
        $delete_time = $df->hasDeleteTime();
        $where = [];
        $where[$rule[1]] = $value;
        $db->where($where);
        if (isset($rule[2]) && !empty($rule[2])) $db->where($rule[2]);
        if ($delete_time) {
            $db->where([['delete_time', 'null']]);
        }
        $num = $db->count();
        return $num > 0;
    }

    //检查地区ID
    public static function region($value, $rule, $data = null, $more = null)
    {
        $value = preg_split('/\D/', trim($value));
        $value = $value[count($value) - 1];
        $where = array('status' => '1', 'id' => $value);
        if ($rule != '') $where['level'] = $rule;
        $num = Db::name('region')->where($where)->count();
        return $num == 1;
    }

    //区县或者城市ID 扩展出城市、省份、国家ID
    public static function region_extend($value, $rule, $data = null, $more = null)
    {
        $value = preg_split('/\D/', trim($value));
        $value = $value[count($value) - 1];
        $rule = is_array($rule) ? $rule : explode(',', $rule);
        sort($rule, SORT_NUMERIC);
        $min = $rule[0];
        $map = array();
        $data = array();
        $region = Db::name('region')->where(array('id' => $value))->find();
        if (!empty($region)) {
            $map[(string)$region['level']] = $region['id'];
            $lv = $region['level'];
            while ($lv > $min) {
                $region = Db::name('region')->where(array('id' => $region['pid']))->find();
                if (!empty($region)) {
                    $map[(string)$region['level']] = $region['id'];
                }
                $lv--;
            }
            foreach ($rule as $r) {
                if ($r == 0 && !empty($map['0'])) {
                    $data['country_id'] = $map['0'];
                } else if ($r == 1 && !empty($map['1'])) {
                    $data['province_id'] = $map['1'];
                } else if ($r == 2 && !empty($map['2'])) {
                    $data['city_id'] = $map['2'];
                } else if ($r == 3 && !empty($map['3'])) {
                    $data['district_id'] = $map['3'];
                }
            }
        }
        return $data;
    }

    //比较范围
    protected static function getRangeResult($value, $range, $not = false)
    {
        if (is_array($range) || strpos($range, ',') !== false) {
            //取值范围
            list($min, $max) = is_array($range) ? $range : explode(',', $range);
            if ($not) {
                $result = false;
                if ($min !== '' && !is_null($min) && $value < $min) $result = true;
                if ($max !== '' && !is_null($max) && $value > $max) $result = true;
            } else {
                $result = true;
                if ($min !== '' && !is_null($min) && $value < $min) $result = false;
                if ($max !== '' && !is_null($max) && $value > $max) $result = false;
            }
        } else {
            $result = $not ? $value != $range : $value == $range;
        }
        return $result;
    }

}
<?php

namespace bxkj_recommend;


class Calc
{
    //区间计算
    public static function intervalCalc($value, $initValue, $intervals)
    {
        foreach ($intervals as $interval) {
            if ($value <= 0) break;
            $range = min($interval[0], $value);
            $initValue += round(($range / $interval[0]) * $interval[1], 6);
            $value -= $range;
        }
        return $initValue;
    }

    //验证区间
    public static function validateRange($value, $range, $not = false, $delimiter = ',')
    {
        if (is_array($range) || strpos($range, $delimiter) !== false) {
            //取值范围
            list($min, $max) = is_array($range) ? $range : explode($delimiter, $range);
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

    public static function thresholdRatio($ratio, $value, $intervals)
    {
        $ratio = $ratio > 1 ? 1 : ($ratio < 0 ? 0 : $ratio);
        if ($ratio == 0) return 0;
        $threshold = 0;
        foreach ($intervals as $interval) {
            $threshold += $interval[0];
        }
        if ($value < $threshold) {
            $use = Calc::intervalCalc($value, 0, $intervals);
            $ratio = round($ratio * $use, 6);
        }
        return $ratio;
    }

    public static function rebalance(&$compositions, $value, $key)
    {
        $total = 0;
        foreach ($compositions as $composition) {
            $total = bcadd($total, $composition[$key], 4);
        }
        foreach ($compositions as &$composition) {
            $div = bcdiv($composition[$key], $total, 4);
            $mul = bcmul($value, $div, 4);
            $composition[$key] = bcadd($composition[$key], $mul, 4);
        }
    }


}
<?php

namespace bxkj_common;

class DateTools
{
    const FIRST_TIME = 1535299200;//2018-08-27 00:00:00

    public function __construct()
    {
    }

    public static function dayLine($num = 0, $day = null, $month = null, $year = null)
    {
        $day = isset($day) ? $day : (int)date('d');
        $month = isset($month) ? $month : (int)date('m');
        $year = isset($year) ? $year : (int)date('Y');
        if ($num == 0)
            return array('y' => $year, 'm' => str_pad($month, 2, '0', STR_PAD_LEFT), 'd' => str_pad($day, 2, '0', STR_PAD_LEFT));
        $total = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        $tmp = $day + $num;
        if ($num < 0) {
            if ($tmp > 0)
                return array('y' => $year, 'm' => str_pad($month, 2, '0', STR_PAD_LEFT), 'd' => str_pad($tmp, 2, '0', STR_PAD_LEFT));
            $lastMonthArr = self::monthLine(-1, $month, $year);
            $lastTotal = cal_days_in_month(CAL_GREGORIAN, $lastMonthArr['m'], $lastMonthArr['y']);
            return self::dayLine($tmp, $lastTotal, $lastMonthArr['m'], $lastMonthArr['y']);
        } else {
            if ($tmp <= $total)
                return array('y' => $year, 'm' => str_pad($month, 2, '0', STR_PAD_LEFT), 'd' => str_pad($tmp, 2, '0', STR_PAD_LEFT));
            $nextMonthArr = self::monthLine(1, $month, $year);
            return self::dayLine($num - ($total - $day), 0, $nextMonthArr['m'], $nextMonthArr['y']);
        }
    }

    public static function monthLine($num = 0, $month = null, $year = null)
    {
        $month = isset($month) ? $month : (int)date('m');
        $year = isset($year) ? $year : (int)date('Y');
        if ($num == 0)
            return array('m' => str_pad($month, 2, '0', STR_PAD_LEFT), 'y' => $year);
        $tmp = $month + $num;
        if ($num < 0) {
            if ($tmp > 0)
                return array('m' => str_pad($tmp, 2, '0', STR_PAD_LEFT), 'y' => $year);
            $year--;
            return self::monthLine($tmp, 12, $year);
        } else {
            if ($tmp <= 12)
                return array('m' => str_pad($tmp, 2, '0', STR_PAD_LEFT), 'y' => $year);
            $year++;
            return self::monthLine($num - (12 - $month), 0, $year);
        }
    }

    public static function getWeekNum($time = null)
    {
        $time = isset($time) ? $time : time();
        $diff = $time - DateTools::FIRST_TIME;
        $dayNum = $diff / (3600 * 24);
        return floor($dayNum / 7);
    }

    public static function getDoubleWeekNum($time = null)
    {
        $time = isset($time) ? $time : time();
        $diff = $time - DateTools::FIRST_TIME;
        $dayNum = $diff / (3600 * 24);
        return floor($dayNum / 14);
    }

    //半月刊号
    public static function getFortNum($time = null)
    {
        $time = isset($time) ? $time : time();
        $month = date('Ym', $time);
        $day = date('d', $time);
        return $day <= 15 ? ($month . '1') : $month . '2';
    }

    public static function getFortTime($funm)
    {
        $matches = [];
        preg_match('/^(\d{4})(\d{2})(\d{1})/', $funm, $matches);
        if ($matches[3] == 1) {
            return mktime(0, 0, 0, $matches[2], 1, $matches[1]);
        } else {
            return mktime(0, 0, 0, $matches[2], 16, $matches[1]);
        }
    }

    public static function weekLine($num = 0, $time = null)
    {
        $time = isset($time) ? $time : time();
        $dayNum = self::getWeekNum($time);
        return $dayNum + $num;
    }

    public static function getWeekTime($week)
    {
        return self::FIRST_TIME + ($week * 3600 * 24 * 7);
    }

    public static function getDoubleWeekTime($week)
    {
        return self::FIRST_TIME + ($week * 3600 * 24 * 14);
    }

    public static function getRangeTitle($start, $end, $unit)
    {
        $startTime = self::strToTime($start);
        $endTime = self::strToTime($end);
        if ($unit == 'm') {
            $s = mktime(0, 0, 0, date('m', $startTime), 1, date('Y', $startTime));
            $e = mktime(0, 0, 0, date('m', $endTime), 1, date('Y', $endTime));
            return date('Y-m月', $s) . ' 至 ' . date('Y-m月', $e);
        } elseif ($unit == 'h') {
            $s = mktime(date('H', $startTime), 0, 0, date('m', $startTime), date('d', $startTime), date('Y', $startTime));
            $e = mktime(date('H', $endTime), 0, 0, date('m', $endTime), date('d', $endTime), date('Y', $endTime));
            return date('Y-m-d H时', $s) . ' 至 ' . date('Y-m-d H时', $e);
        } elseif ($unit == 'd') {
            $s = mktime(0, 0, 0, date('m', $startTime), date('d', $startTime), date('Y', $startTime));
            $e = mktime(0, 0, 0, date('m', $endTime), date('d', $endTime), date('Y', $endTime));
            return date('Y-m-d日', $s) . ' 至 ' . date('Y-m-d日', $e);
        } elseif ($unit == 'w') {
            $w = self::getWeekNum($startTime);
            $ew = self::getWeekNum($endTime);
            $s = self::getWeekTime($w);
            $e = self::getWeekTime($ew);
            $diff = 1 - $w;
            return date('Y-m-d日', $s) . ' 至 ' . date('Y-m-d日', $e);
        }
    }

    public static function rangeNodes($start, $end, $unit)
    {
        $startTime = self::strToTime($start);
        $endTime = self::strToTime($end);
        $nodes = array();
        //所有节点年份相同
        $ySame = true;
        $mSame = true;
        $dSame = true;
        $hSame = true;
        if ($unit == 'm') {
            $s = mktime(0, 0, 0, date('m', $startTime), 1, date('Y', $startTime));
            $e = mktime(0, 0, 0, date('m', $endTime), 1, date('Y', $endTime));
            $unitS = cal_days_in_month(CAL_GREGORIAN, date('m', $s), date('Y', $s)) * 3600 * 24;
            while ($s <= $e) {
                $tmp['y'] = date('Y', $s);
                $tmp['yy'] = date('y', $s);
                $tmp['m'] = date('m', $s);
                $tmp['d'] = date('d', $s);
                $tmp['h'] = date('H', $s);
                $tmp['w'] = self::getWeekNum($s);
                $tmp['ww'] = date('w', $s);
                if (isset($nodes[count($nodes) - 1])) {
                    $prev = $nodes[count($nodes) - 1];
                    if ($prev['y'] != $tmp['y'])
                        $ySame = false;
                    if ($prev['m'] != $tmp['m'])
                        $mSame = false;
                    if ($prev['d'] != $tmp['d'])
                        $dSame = false;
                    if ($prev['h'] != $tmp['h'])
                        $hSame = false;
                }
                $nodes[] = $tmp;
                $s += $unitS;
                $unitS = cal_days_in_month(CAL_GREGORIAN, date('m', $s), date('Y', $s)) * 3600 * 24;
            }
        } else {
            if ($unit == 'h') {
                $unitS = 3600;
                $s = mktime(date('H', $startTime), 0, 0, date('m', $startTime), date('d', $startTime), date('Y', $startTime));
                $e = mktime(date('H', $endTime), 0, 0, date('m', $endTime), date('d', $endTime), date('Y', $endTime));
            } elseif ($unit == 'd') {
                $unitS = 3600 * 24;
                $s = mktime(0, 0, 0, date('m', $startTime), date('d', $startTime), date('Y', $startTime));
                $e = mktime(0, 0, 0, date('m', $endTime), date('d', $endTime), date('Y', $endTime));
            } elseif ($unit == 'w') {
                $unitS = 3600 * 24 * 7;
                $s = self::getWeekTime(self::getWeekNum($startTime));
                $e = self::getWeekTime(self::getWeekNum($endTime));
            }
            while ($s <= $e) {
                $tmp['y'] = date('Y', $s);
                $tmp['yy'] = date('y', $s);
                $tmp['m'] = date('m', $s);
                $tmp['d'] = date('d', $s);
                $tmp['h'] = date('H', $s);
                $tmp['w'] = self::getWeekNum($s);
                $tmp['ww'] = date('w', $s);
                if (isset($nodes[count($nodes) - 1])) {
                    $prev = $nodes[count($nodes) - 1];
                    if ($prev['y'] != $tmp['y'])
                        $ySame = false;
                    if ($prev['m'] != $tmp['m'])
                        $mSame = false;
                    if ($prev['d'] != $tmp['d'])
                        $dSame = false;
                    if ($prev['h'] != $tmp['h'])
                        $hSame = false;
                }
                $nodes[] = $tmp;
                $s += $unitS;
            }
        }
        $weekArr = array("日", "一", "二", "三", "四", "五", "六");
        $diff = null;
        foreach ($nodes as $key => &$value) {
            if (!isset($diff))
                $diff = 1 - $value['w'];
            $name = '';
            if ((!$ySame && ($unit == 'm' || $unit == 'y')) || ($ySame && $unit == 'y' && count($nodes) == 1))
                $name .= $value['yy'] . '年';
            if ((!$mSame && ($unit == 'd' || $unit == 'm' || $unit == 'w')) || ($mSame && $unit == 'm' && count($nodes) == 1))
                $name .= $value['m'] . '月';
            if ((!$dSame && ($unit == 'h' || $unit == 'd' || $unit == 'w')) || ($dSame && ($unit == 'd' || $unit == 'w') && count($nodes) == 1))
                $name .= $value['d'] . '日';
            if ((!$hSame && ($unit == 'h')) || ($hSame && $unit == 'h' && count($nodes) == 1))
                $name .= $value['h'] . '时';
            $wN = $value['w'] + $diff;
            //如果是一周的数据的话则加上周
            if (count($nodes) == 7 && $nodes[0]['ww'] == 1 && $unit == 'd')
                $name .= "(周{$weekArr[$value['ww']]})";
            $name .= $unit == 'w' ? "(第{$wN}周)" : '';
            $value['name'] = $name;
        }
        return $nodes;
    }

    public static function strToTime($str)
    {
        $str = trim($str);
        preg_match('/([0-9\-]+)\s+([0-9\:]+)/', $str, $matches);
        if (empty($matches)) {
            preg_match('/^[0-9\-]+$/', $str, $matches);
            $date = $matches[0];
            $time = '';
        } else {
            $date = $matches[1];
            $time = $matches[2];
        }
        $dateArr = empty($date) ? array() : explode('-', $date);
        $timeArr = empty($time) ? array() : explode(':', $time);
        $h = isset($timeArr[0]) ? $timeArr[0] : 0;
        $i = isset($timeArr[1]) ? $timeArr[1] : 0;
        $s = isset($timeArr[2]) ? $timeArr[2] : 0;
        $y = isset($dateArr[0]) ? $dateArr[0] : 0;
        $m = isset($dateArr[1]) ? $dateArr[1] : 1;
        $d = isset($dateArr[2]) ? $dateArr[2] : 1;
        return mktime($h, $i, $s, $m, $d, $y);
    }

    //获取指定周的所有$unit(d天 h时 i分) $time指定时间戳 $format返回格式
    public static function getWeekNodes($unit = 'd', $time = null, $format = null)
    {
        $time = isset($time) ? $time : time();
        $week = date('w', $time);
        $week = $week == 0 ? 7 : $week;
        $todayTime = mktime(0, 0, 0, date('m', $time), date('d', $time), date('Y', $time));
        $startTime = $todayTime - (($week - 1) * 3600 * 24);
        $endTime = $todayTime + ((7 - $week + 1) * 3600 * 24);
        return self::getNodesBySETime($startTime, $endTime, $unit, $format);
    }

    //指定日期的当周和下一周
    public static function getDoubleWeekFNodes($unit = 'd', $time = null, $format = null)
    {
        $time = isset($time) ? $time : time();
        $week = date('w', $time);
        $week = $week == 0 ? 7 : $week;
        $todayTime = mktime(0, 0, 0, date('m', $time), date('d', $time), date('Y', $time));
        $startTime = $todayTime - (($week - 1) * 3600 * 24);
        $endTime = $todayTime + ((7 - $week + 1) * 3600 * 24) + (7 * 3600 * 24);
        return self::getNodesBySETime($startTime, $endTime, $unit, $format);
    }

    public static function getDoubleWeekNodesByNum($unit, $dw, $format)
    {
        $startTime = self::getDoubleWeekTime($dw);
        $endTime = $startTime + (14 * 24 * 3600);
        return self::getNodesBySETime($startTime, $endTime, $unit, $format);
    }

    public static function getFortNodesByNum($unit, $fnum, $format)
    {
        $arr = self::getFortStartTime($fnum);
        return self::getNodesBySETime($arr[0], $arr[1], $unit, $format);
    }

    protected static function getFortStartTime($fnum)
    {
        $matches = [];
        preg_match('/^(\d{4})(\d{2})(\d{1})$/', $fnum, $matches);
        $year = $matches[1];
        $month = $matches[2];
        $num = $matches[3];
        $startTime = $num == 1 ? mktime(0, 0, 0, $month, 1, $year) : mktime(0, 0, 0, $month, 16, $year);
        $firstday = date('Y-m-01', mktime(0, 0, 0, $month, 1, $year));
        $lastday = date('d', strtotime("$firstday +1 month -1 day"));
        $endTime = $num == 1 ? mktime(23, 59, 59, $month, 15, $year) : mktime(23, 59, 59, $month, $lastday, $year);
        return [$startTime, $endTime];
    }

    //获取指定月的所有$unit(d天 h时 i分) $time指定时间戳 $format返回格式
    public static function getMonthNodes($unit = 'd', $time = null, $format = null)
    {
        $time = isset($time) ? $time : time();
        $lastDay = date('t', $time);
        $startTime = mktime(0, 0, 0, date('m', $time), 1, date('Y', $time));
        $endTime = mktime(0, 0, 0, date('m', $time), $lastDay, date('Y', $time));
        $endTime += 86400;
        return self::getNodesBySETime($startTime, $endTime, $unit, $format);
    }

    public static function getNodesBySETime($startTime, $endTime, $unit = 'd', $format = null)
    {
        $nodes = array();
        $seconds = $unit == 'd' ? 86400 : ($unit == 'h' ? 3600 : 60);
        for ($i = $startTime; $i < $endTime; $i += $seconds) {
            $nodes[] = isset($format) ? date($format, $i) : $i;
        }
        return $nodes;
    }

    public static function getTimeRangerConfig($units = null)
    {
        if (!isset($units)) {
            $units = [
                'd' => 30,
                'w' => 20,
                'dw' => 10,
                'f' => 30,
                'm' => 15,
                'during' => 1,
                //'y' => 3
            ];
        }

        $config = [];
        $map = [
            'd' => [
                '0' => '今日',
                '1' => '昨日',
                '2' => '前日'
            ],
            'm' => [
                '0' => '本月',
                '1' => '上月'
            ],
            'w' => [
                '0' => '本周',
                '1' => '上周'
            ],
            'dw' => [
                '0' => '当前两周',
                '1' => '上两周'
            ],
            'y' => [
                '0' => '今年',
                '1' => '去年'
            ]
        ];
        $now = time();
        foreach ($units as $unit => $length) {
            $arr = [];

            for ($i = 0; $i < $length; $i++) {
                $tmp = [];
                $name = isset($map[$unit]) && isset($map[$unit][(string)$i]) ? $map[$unit][(string)$i] : null;
                $value = '';
                $w = self::getWeekNum();
                $dw = self::getDoubleWeekNum();
                $fnum = self::getFortNum();
                switch ($unit) {
                    case 'd':
                        $value = date('Y-m-d', strtotime("-{$i} day"));
                        break;
                    case 'w':
                        $value = $w - $i;
                        $start = self::getWeekTime($value);
                        $startD = date('m-d', $start);
                        $end = $start + 604800 - 1;//加一周
                        $endD = date('m-d', $end);
                        $name = isset($name) ? $name : ($value . '(' . $startD . '至' . $endD . ')');
                        break;
                    case 'dw':
                        $value = $dw - $i;
                        $start = self::getDoubleWeekTime($value);
                        $startD = date('m-d', $start);
                        $end = $start + 1209600 - 1;//加两周
                        $endD = date('m-d', $end);
                        $name = isset($name) ? $name : ($value . '(' . $startD . '至' . $endD . ')');
                        break;
                    case 'm':
                        $value = date('Y-m', strtotime("-{$i} month"));
                        break;
                    case 'y':
                        $lastM1 = date('n',strtotime(" -".$i." month", strtotime("first day of 0 month", $now)));
                        $lastM2 = date('n',strtotime(" -".$i." month", $now));
                        if ($lastM1 != $lastM2){
                            $value = date('Y-m',strtotime(" last day of -".$i." month", $now));
                        }else{
                            $value = date('Y-m',strtotime(" -".$i." month", $now));
                        }
                        //$value = date('Y', strtotime("-{$i} year"));
                        break;
                    case 'f':
                        $fnum = self::computeFortNum($fnum, 0 - $i);
                        preg_match('/^(\d{4})(\d{2})(\d{1})$/', $fnum, $matches);
                        $value = $fnum;
                        $name = $matches[1] . '-' . $matches[2] . '月' . ($matches[3] == 1 ? '上半旬' : '下半旬');
                        break;
                    case 'during':
                        $value = '20190716-20190728';
                        break;
                }
                $tmp['value'] = $value;
                $tmp['name'] = isset($name) ? $name : $value;
                $arr[] = $tmp;
            }
            $config[$unit] = $arr;
        }
        return $config;
    }

    public static function computeFortNum($fnum, $num)
    {
        $matches = [];
        preg_match('/^(\d{4})(\d{2})(\d{1})$/', $fnum, $matches);
        $matche3 = $matches[3];
        $now = mktime(0, 0, 0, $matches[2], 1, $matches[1]);
        $abs = abs($num);
        $month = $matches[1] . $matches[2];
        for ($i = 0; $i < $abs; $i++) {
            $matche3 += ($num >= 0 ? 1 : -1);
            if ($matche3 < 1) {
                $matche3 = 2;
                $now = strtotime("-1 month", $now);
            } else if ($matche3 > 2) {
                $matche3 = 1;
                $now = strtotime("+1 month", $now);
            }
            $month = date('Ym', $now);
        }
        return $month . $matche3;
    }

}
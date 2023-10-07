<?php

namespace bxkj_module\service;

class MyQuery extends \think\db\Query
{
    public function setKeywords($keyword, $fast = 'phone phone', $eq = '', $like = '')
    {
        if ($keyword == '') return $this;
        $length = mb_strlen($keyword);
        $isFast = false;
        //匹配到快捷格式项
        if (!empty($fast)) {
            $arr = explode(',', $fast);
            foreach ($arr as $item) {
                list($p, $n) = preg_split('/\s+/', $item);
                if (validate_regex_pattern($keyword, $p)) {
                    $this->where($n, '=', $keyword);
                    $isFast = true;
                    break;
                }
            }
        }
        if (!$isFast) {
            $where1 = array();
            $where2 = array();
            //等于项
            if (!empty($eq)) {
                $arr = explode(',', $eq);

                foreach ($arr as $item) {
                    list($p, $n) = preg_split('/\s+/', $item);
                    if (validate_regex_pattern($keyword, $p)) {
                        $where1[] = [$n, '=', $keyword];
                    }
                }
            }
            //like项
            if (!empty($like) && $length > 0) {
                $arr = explode(',', $like);
                foreach ($arr as $item) {
                    list($p, $n) = preg_split('/\s+/', $item);
                    if (empty($n)) {
                        $n = $p;
                        $p = null;
                    }
                    if (!isset($p) || (isset($p) && validate_regex_pattern($keyword, $p))) {
                        $tmpArr = explode('.', $n);
                        $lastName = strtolower($tmpArr[count($tmpArr) - 1]);
                        $phoneFields = array('mobile', 'phone', 'tel', 'contact_tel', 'order_no', 'trade_no',
                            'no', 'contact_phone', 'user_id', 'rel_no','third_trade_no');
                        $where2[] = array($n, 'like', in_array($lastName, $phoneFields) ? "{$keyword}%" : "%{$keyword}%");
                    }
                }
            }
            if (!empty($where1) || !empty($where2)) {
                $this->where(function ($query) use ($where1, $where2) {
                    if (!empty($where1)) $query->whereOr($where1);
                    if (!empty($where2)) $query->whereOr($where2);
                });
            }
        }
        return $this;
    }

    public function setLike($field, $value, $delimiter = ',')
    {
        $this->where(function ($query) use ($field, $value, $delimiter) {
            $query->whereOr($field, '=', $value);
            $query->whereOr($field, 'like', "%{$delimiter}{$value}");
            $query->whereOr($field, 'like', "{$value}{$delimiter}%");
            $query->whereOr($field, 'like', "%{$delimiter}{$value}{$delimiter}%");
        });
        return $this;
    }

    public function setLikeOr($field, $value, $delimiter = ',')
    {
        $this->whereOr(function ($query) use ($field, $value, $delimiter) {
            $query->whereOr($field, '=', $value);
            $query->whereOr($field, 'like', "%{$delimiter}{$value}");
            $query->whereOr($field, 'like', "{$value}{$delimiter}%");
            $query->whereOr($field, 'like', "%{$delimiter}{$value}{$delimiter}%");
        });
        return $this;
    }


}
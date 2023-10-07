<?php

namespace bxkj_common;

class KeywordCheck
{

    //是否合法 true/false
    public static function isLegal($keyword)
    {
        if (empty($keyword)) return true;
        $result = self::filter($keyword);
        if (empty($result)) return true;
        return false;
    }

    //检测内容(返回敏感词数组)
    public static function check($keyword)
    {
        if (empty($keyword)) return [];
        $result = self::filter($keyword);
        return is_array($result) ? $result : [];
    }


    protected static function filter($keyword)
    {
        $url = CORE_URL."/filter/check?content=" . urlencode($keyword);
        $http = new HttpClient();
        $result = $http->get($url, '', 5)->getData('json');
        return $result['data'];
    }
}
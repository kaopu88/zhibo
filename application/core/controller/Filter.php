<?php

namespace app\core\controller;

use think\facade\Request;
use app\core\service\filter\FilterHelper;


class Filter extends Controller
{
    public function check()
    {
        $content = Request::param('content', '');

        if (empty($content)) return json_error('请输入待检测的关键词~');

        // 获取最新trie-tree对象
        $resTrie = FilterHelper::getResTrie();

        // 执行过滤
        $arrRet = trie_filter_search_all($resTrie, $content);

        // 提取过滤出的敏感词
        $a_data = FilterHelper::getFilterWords($content, $arrRet);

        return json_success($a_data);
    }

}
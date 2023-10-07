<?php
/**
 * 过滤器助手
 *
 * getResTrie 提供trie-tree对象;
 * getFilterWords 提取过滤出的字符串
 *
 * @author W.Y.P (wangyupeng@jiayuan.com)
 */

namespace app\core\service\filter;

class FilterHelper
{

    // trie-tree对象
    private static $_resTrie = null;


    // 字典树的更新时间
    private static $_mtime = null;


    /**
     * 防止初始化
     */
    private function __construct() {}


    /**
     * 防止克隆对象
     */
    private function __clone() {}


    /**
     * 提供trie-tree对象
     *
     * @param $tree_file 字典树文件路径
     * @param $new_mtime 当前调用时字典树的更新时间
     * @return null
     */
    static public function getResTrie($tree_file='', $new_mtime='')
    {
    	 // 清除文件状态缓存
        clearstatcache();
        
        if (empty($tree_file)) $tree_file = ROOT_PATH.'application/core/service/filter/blackword.tree';
        
        if(!file_exists($tree_file)) self::loadFile();

        if (empty($new_mtime)) $new_mtime = filemtime($tree_file);
        
        if (is_null(self::$_mtime))
        {
            self::$_mtime = $new_mtime;
        }

        if (($new_mtime != self::$_mtime) || is_null(self::$_resTrie))
        {
            self::$_resTrie = trie_filter_load($tree_file);

            self::$_mtime = $new_mtime;

            // 输出字典文件重载时间
            //echo date('Y-m-d H:i:s') . "\tdictionary reload success!\n";
        }

        return self::$_resTrie;
    }


    /**
     * 从原字符串中提取过滤出的敏感词
     *
     * @param $str 原字符串
     * @param $res 1-3 表示 从位置1开始，3个字符长度
     * @return array
     */
    static public function getFilterWords($str, $res)
    {
        $result = array();
        foreach ($res as $k => $v) {
            $word = substr($str, $v[0], $v[1]);

            if (!in_array($word, $result)) {
                $result[] = $word;
            }
        }

        return $result;
    }


    public static function loadFile()
    {
        // 读取敏感词字典库
        $handle = fopen(ROOT_PATH.'application/core/service/filter/dict.txt', 'r');

        // 生成空的trie-tree-filter
        $resTrie = trie_filter_new();

        while(! feof($handle)) {
            $item = trim(fgets($handle));

            if (empty($item)) {
                continue;
            }

            // 把敏感词逐个加入trie-tree
            trie_filter_store($resTrie, $item);
        }

        // 生成trie-tree文件
        $blackword_tree = ROOT_PATH.'application/core/service/filter/blackword.tree';

        trie_filter_save($resTrie, $blackword_tree);
    }


}
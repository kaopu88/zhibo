<?php

namespace bxkj_module\service;

use think\Db;

class Tree extends Service
{
    protected $tabName;
    protected $parentKey;
    protected $primaryKey;
    protected $trueTableName;
    protected $options = [];

    public function __construct($tabName, $parentKey = 'pid', $primaryKey = 'id')
    {
        parent::__construct();
        $this->tabName = $tabName;
        $this->trueTableName = config('database.prefix') . $this->tabName;
        $this->parentKey = $parentKey;
        $this->primaryKey = $primaryKey;
    }

    public function setOrderOptions($order)
    {
        $this->options['order'] = $order;
        return $this;
    }

    public function setWhereOptions($where)
    {
        $this->options['where'] = $where;
        return $this;
    }

    public function setFieldOptions($field)
    {
        $this->options['field'] = $field;
        return $this;
    }

    private function resetOptions()
    {
        $this->options = [];
    }

    protected function withOptions($db)
    {
        if (!empty($this->options['order'])) {
            $db->order($this->options['order']);
        }
        if (!empty($this->options['where'])) {
            $db->where($this->options['where']);
        }
        if (!empty($this->options['field'])) {
            $db->field($this->options['field']);
        }
        return $db;
    }

    /**
     * 通过mark查询id
     * @param string $mark 'root/admin' 支持多级
     * @param bool $complete 返回完整的数组
     * @param bool $index 只查找id
     * @return array|null
     */
    public function getIdByMark($mark, $complete = false, $index = true)
    {
        $marks = explode("/", $mark);
        $arr = array();
        for ($i = 0; $i < count($marks); $i++) {
            $db = Db::name($this->tabName);
            $where = array("mark" => $marks[$i]);
            if (isset($arr[$i - 1])) $where[$this->parentKey] = $index ? $arr[$i - 1] : $arr[$i - 1][$this->primaryKey];
            if ($index) $db->field($this->primaryKey);
            $result = ($where['mark'] == 'root') ? $this->getRoot() : $db->where($where)->order(array("level" => "asc"))->find();
            if (empty($result)) return null;
            $arr[] = $index ? $result[$this->primaryKey] : $result;
        }
        return $complete ? $arr : $arr[count($arr) - 1];
    }

    /**
     * 通过id或mark查询子级分类树，优先使用缓存
     * @param $mark
     * @param array $ids 所以子级id集合
     * @param bool $pos 仅查询子级
     * @return array
     */
    public function getChildrenByMark($mark, &$ids = array(), $pos = false, $maxLevel = null)
    {
        $maxLevelStr = $this->sha1Key($maxLevel);
        $key = 'tree:type:' . $this->trueTableName . $mark . $maxLevelStr;
        $result = cache($key);
        // var_dump($this->tabName);die;
        // $result = null;
        $appDebug = false;
        //$appDebug = config('app.app_debug');
        if (empty($result) || $appDebug) {
            $ids = array();
            $list = $this->getChildren($mark, $ids, false, $maxLevel);
            $result = array('list' => $list, 'ids' => $ids);
            cache($key, $result);
            $key2 = 'tree:typelv:' . $this->trueTableName . $mark;
            $maxLevelStrArr = cache($key2);
            $maxLevelStrArr = $maxLevelStrArr ? explode(',', $maxLevelStrArr) : [];
            if (!in_array((string)$maxLevelStr, $maxLevelStrArr)) {
                $maxLevelStrArr[] = (string)$maxLevelStr;
                cache($key2, implode(',', $maxLevelStrArr));
            }
        } else {
            $ids = $result['ids'];
            $list = $result['list'];
        }
        if ($pos) array_splice($ids, array_search($list[$this->primaryKey], $ids), 1);
        $list = $pos ? $list['children'] : $list;
        // var_dump($list);die;
        return $list;
    }

    protected function sha1Key($maxLevel)
    {
        $arr = ['maxLevel' => isset($maxLevel) ? $maxLevel : ''];
        if (!empty($this->options['field'])) {
            $arr['field'] = is_array($this->options['field']) ? $this->options['field'] : explode(',', $this->options['field']);
            sort($arr['field'], SORT_STRING);
        }
        if (!empty($this->options['where'])) {
            $arr['where'] = $this->options['where'];
        }
        if (!empty($this->options['order'])) {
            $arr['order'] = is_array($this->options['order']) ? $this->options['order'] : explode(',', $this->options['order']);
        }
        return sha1_array($arr);
    }

    public function getCategoryByMark($mark)
    {
        $ids = [];
        return $this->getChildrenByMark($mark, $ids, true, 2);
    }

    /**
     * 与getChildrenByMark用法一致，但是不使用缓存
     * @param $id
     * @param array $ids
     * @param bool $pos
     * @return array
     */
    public function getChildren($id, &$ids = array(), $pos = false, $maxLevel = null)
    {
        $id = is_string($id) ? $this->getIdByMark($id) : $id;
        $level = 0;
        if ($pos) {
            $result = array();
            $result[$this->primaryKey] = $id;
        } else {
            $where = array();
            $where[$this->primaryKey] = $id;
            $result = ($id === 0) ? $this->getRoot() : $this->withOptions(Db::name($this->tabName))->where($where)->find();
            $level++;
            if (isset($maxLevel) && $maxLevel <= $level) {
                $this->resetOptions();
                return $result;
            }
        }
        $tmp = null;
        if (!empty($result)) {
            $tmp = array($result);
            $this->getInChildren($tmp, $ids, $maxLevel, $level);
            if (!$pos) $ids[] = $result[$this->primaryKey];
            $tmp = $pos ? $tmp[0]['children'] : $tmp[0];
        }
        $this->resetOptions();
        return $tmp;
    }

    /**
     * 通过子级列表数组查询其所有下一级别列表 使用sql条件查询in语句
     * @param $list
     * @param array $ids
     */
    protected function getInChildren(&$list, &$ids = array(), $maxLevel = null, $lv = 0)
    {
        $tmp = array();
        for ($i = 0; $i < count($list); $i++) {
            $tmp[] = $list[$i][$this->primaryKey];
        }
        if (!empty($tmp)) {
            $result = $this->withOptions(Db::name($this->tabName))->whereIn($this->parentKey, $tmp)->select();
            if (!empty($result)) {
                $lv++;
                if (!isset($maxLevel) || $maxLevel > $lv) {
                    $this->getInChildren($result, $ids, $maxLevel, $lv);
                }
                $this->matchParent($list, $result, $ids);
            }
        }
    }

    //对getInChildren的扩展，匹配父级
    private function matchParent(&$list, $children, &$ids = array())
    {
        for ($i = 0; $i < count($children); $i++) {
            for ($j = 0; $j < count($list); $j++) {
                if ($list[$j][$this->primaryKey] == $children[$i][$this->parentKey]) {
                    if (!isset($list[$j]['children']) || !is_array($list[$j]['children'])) {
                        $list[$j]['children'] = array();
                    }
                    $list[$j]['children'][] = $children[$i];
                    $ids[] = $children[$i][$this->primaryKey];
                }
            }
        }
    }

    /**
     * 获取mark=='root' 根节点的详情，支持field过滤字段
     * @return array
     */
    public function getRoot()
    {
        $root = array('level' => -1, 'name' => 'root', 'mark' => 'root', 'status' => '1');
        $root[$this->primaryKey] = 0;
        $root[$this->parentKey] = -1;
        $field = null;
        $tmp = array();
        if (isset($field)) {
            foreach ($field as $key => $value) {
                $tmp[$value] = $root[$value];
            }
        } else {
            $tmp = $root;
        }
        return $tmp;
    }

    /**
     * 以当前为起点向上获取祖辈路径
     * @param null $id
     * @param null $pause 在此停止继续向上查找
     * @param bool $index 只查询id
     * @return array
     */
    public function getParentPath($id = null, $pause = null, $index = false)
    {
        $arr = array();
        if ($index) $fieldStr = "{$this->primaryKey},{$this->parentKey}";
        if (is_string($pause)) {
            $pause = $this->getIdbyMark($pause);
            if (is_null($pause)) return $arr;
        }
        do {
            $db = Db::name($this->tabName);
            if ($fieldStr) $db->field($fieldStr);
            $where[$this->primaryKey] = $id;
            $result = ($where[$this->primaryKey] == 0) ? $this->getRoot() : $db->where($where)->find();
            array_unshift($arr, $index ? $result[$this->primaryKey] : $result);
            if (isset($pause) && $pause == $id) return $arr;//匹配则停止
            $id = $result[$this->parentKey];
        } while ($id >= 0);
        return isset($pause) ? array() : $arr;
    }

    /**
     * 从当前起点开始向上获取各级的子级列表
     * @param $path
     * @param null $pause 停止位置
     * @param bool $self 是否包含pause的上一级
     * @return array
     */
    public function getTreeByPath($path, $pause = null, $self = false)
    {
        $last_id = is_int($path) ? $path : $this->pathLastId($path);
        $arr = $this->getParentPath($last_id, $pause, false);
        if (!empty($arr) && $self) {
            $tmpArr = array();
            $tmpArr[$this->primaryKey] = $arr[0][$this->parentKey];
            array_unshift($arr, $tmpArr);
        }
        $list = array();
        for ($i = 0; $i < count($arr); $i++) {
            $pid = $arr[$i][$this->primaryKey];
            $where = array();
            $where[$this->parentKey] = $pid;
            $result = ($pid == -1) ? array($this->getRoot()) : $this->withOptions(Db::name($this->tabName))->where($where)->select();
            if (isset($arr[$i + 1][$this->primaryKey])) {
                for ($j = 0; $j < count($result); $j++) {
                    if ($result[$j][$this->primaryKey] == $arr[$i + 1][$this->primaryKey]) $result[$j]['current'] = 'current';
                }
            }
            if (!empty($result)) $list[] = $result;
        }
        $this->resetOptions();
        return $list;
    }

    //获取路径里面的最后一个不为空的ID
    public function pathLastId($path)
    {
        if (is_string($path)) $path = explode(',', $path);
        for ($i = count($path) - 1; $i > -1; $i--) {
            if ($path[$i] != '') return (int)$path[$i];
        }
        return '';
    }

    //获取所有兄弟节点
    public function getBrothers($mark)
    {
        $where = array();
        $where[$this->primaryKey] = $mark;
        if (is_string($mark)) {
            $result = $this->getIdByMark($mark, false, false);
        } else {
            if ($mark == 0) {
                $result = $this->getRoot();
            } else {
                $result = Db::name($this->tabName)->field($this->primaryKey . ',' . $this->parentKey)->where($where)->find();
            }
        }
        $where2 = array();
        $where2[$this->parentKey] = $result[$this->parentKey];
        $brothers = ($result[$this->parentKey] == -1) ? array($this->getRoot()) : $this->withOptions(Db::name($this->tabName))->where($where2)->select();
        $this->resetOptions();
        return $brothers;
    }

    //配合前端的分类控件使用
    public function typeControllerTree($get, $mark = 'root', $self = true, $maxRange = null)
    {
        $path = $get['path'];
        $pid = $get['pid'];
        if ($path != '') {
            $result = $this->getTreeByPath($path, $mark, $self);
        } elseif ($pid != '') {
            $where[$this->parentKey] = $pid;
            $db = Db::name($this->tabName);
            if (isset($maxRange)) {
                $root = $this->getIdByMark($mark, false, false);
                $db->where('level', '<=', $root['level'] + $maxRange);
            }
            $result = $this->withOptions($db)->where($where)->select();
            $this->resetOptions();
        } else {
            if ($self) {
                $result = $this->getBrothers($mark);
            } else {
                $pid = $this->getIdByMark($mark);
                $where2 = array();
                $where2[$this->parentKey] = $pid;
                $result = $this->withOptions(Db::name($this->tabName))->where($where2)->select();
                $this->resetOptions();
            }
        }
        return $result;
    }

    //删除同时删除其所有子级分类
    public function delete($ids, $recursion = true)
    {
        $ids = array_unique(is_array($ids) ? $ids : explode(',', $ids));
        if ($recursion) {
            foreach ($ids as $id) {
                $tmp = [];
                $this->getChildren((int)$id, $tmp);
                $ids = array_merge($ids, $tmp);
            }
        }
        $ids = array_unique($ids);
        $num = 0;
        if (!empty($ids)) {
            $clearIds = [];
            foreach ($ids as $id) {
                if (!in_array($id, $clearIds)) {
                    $tmpIds = $this->clearCache($id);
                    $clearIds = array_merge($clearIds, $tmpIds);
                }
            }
            $num = Db::name($this->tabName)->whereIn($this->primaryKey, $ids)->delete();
        }
        return $num;
    }

    //检查有没有子级
    public function checkChildren(&$tree)
    {
        if (is_array($tree) && !isset($tree[$this->primaryKey])) {
            foreach ($tree as &$value) {
                if (!empty($value)) $this->checkChildren($value);
            }
        } else {
            if (empty($tree['children'])) {
                $where = array();
                $where[$this->parentKey] = $tree[$this->primaryKey];
                if (Db::name($this->tabName)->where($where)->count() > 0) {
                    $tree['children'] = true;
                }
            } else {
                $this->checkChildren($tree['children']);
            }
        }
    }

    //清除相关的缓存
    public function clearCache($id)
    {
        $ids = [];
        $path = $this->getParentPath($id);
        if (!empty($path)) {
            for ($i = count($path) - 1; $i > -1; $i--) {
                if ($path[$i] && !empty($path[$i]['mark'])) {
                    $mark = $path[$i]['mark'];
                    $ids[] = $path[$i]['id'];
                    cache('tree:type:' . $this->trueTableName . $mark, null);
                    $key2 = 'tree:typelv:' . $this->trueTableName . $mark;
                    $lvs = cache($key2);
                    if (!empty($lvs) || $lvs === '0') {
                        $arr = explode(',', $lvs);
                        foreach ($arr as $maxLevel) {
                            $key = 'tree:type:' . $this->trueTableName . $mark . (string)$maxLevel;
                            cache($key, null);
                        }
                        cache($key2, null);
                    }
                }
            }
        }
        return $ids;
    }

}